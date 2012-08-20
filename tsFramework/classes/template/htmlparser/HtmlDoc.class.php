<?php

/**
 * HtmlDoc
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
class HtmlDoc {

	private $htmlContent;
	private $contentPos;
	private $nodeTree;
	private $pendingNode;
	private $namespace;
	private $selfClosingTags;

	public function __construct($htmlContent, $namespace = null) {
		$this->htmlContent = $htmlContent;

		$this->nodeTree = new DocumentNode($this);
		$this->pendingNode = $this->nodeTree;

		$this->selfClosingTags = array('br', 'hr', 'img', 'input', 'link', 'meta');

		$this->namespace = $namespace;
	}

	public function parse() {
		$this->contentPos = 0;

		while(self::findNextNode() !== false);

		/* while(($currNode = self::findNextNode()) !== false) {
		  if($currNode->nodeType === HtmlNode::ELEMENT_NODE) {
		  var_dump($currNode->tagName); var_dump($currNode->namespace);
		  echo'<br>';
		  }
		  } */

		if($this->contentPos !== strlen($this->htmlContent)) {
			$restNode = new TextNode($this);
			$restNode->content = substr($this->htmlContent, $this->contentPos);
			$restNode->parentNode = $this->nodeTree;

			$this->nodeTree->addChildNode($restNode);
		}
	}

	private function findNextNode() {
		$oldPendingNode = $this->pendingNode;
		$oldContentPos = $this->contentPos;
		$pattern = $res = null;

		if($this->namespace !== null) {
			$pattern = '/(?:<!--.+?-->|<![CDATA[.+?]]>|<(\/)?(' . $this->namespace . '\:.+?)(?:\\s+(.+?))?(\/)?\\s*>)/ims';
		} else {
			$pattern = '/(?:<!--.+?-->|<![CDATA[.+?]]>|<(\/)?(.+?)(?:\\s+(.+?))?(\/)?\\s*>)/ims';
		}

		preg_match($pattern, $this->htmlContent, $res, PREG_OFFSET_CAPTURE, $this->contentPos);

		// Wenn kein Tag mehr gefunden wird
		if(count($res) === 0)
			return false;

		$newPos = $res[0][1];

		if($oldContentPos !== $newPos) {
			// Control-Node
			$lostText = substr($this->htmlContent, $oldContentPos, ($newPos - $oldContentPos));

			$lostTextNode = null;

			if(preg_match('/^\\s*$/', $lostText) === true) {
				$lostTextNode = new TextNode($this);
			} else {
				$lostTextNode = new TextNode($this);
			}

			$lostTextNode->content = $lostText;
			$lostTextNode->parentNode = $oldPendingNode;

			if($oldPendingNode === null) {
				$this->nodeTree->addChildNode($lostTextNode);
			} else {
				$oldPendingNode->addChildNode($lostTextNode);
			}
		}

		$this->contentPos = $newPos + strlen($res[0][0]);

		$newNode = null;

		if(strpos($res[0][0], '<!--') === 0) {
			// Comment-node
			$newNode = new CommentNode($this);
			$newNode->content = $res[0][0];
		} elseif(stripos($res[0][0], '<![CDATA[') === 0) {
			// CDATA-node
			$newNode = new CDataSectionNode($this);
			$newNode->content = $res[0][0];
		} elseif(stripos($res[0][0], '<!DOCTYPE') === 0) {
			$newNode = new DocumentTypeNode($this);
			$newNode->content = $res[0][0];
		} else {
			$newNode = new ElementNode($this);

			// </...> (close only)
			if(array_key_exists(1, $res) && $res[1][1] !== -1) {
				$this->pendingNode = ($oldPendingNode !== null) ? $oldPendingNode->parentNode : null;
				return;
			}

			// Normal HTML-Tag-node
			$tagNParts = explode(':', $res[2][0]);

			if(count($tagNParts) > 1) {
				$newNode->namespace = $tagNParts[0];
				$newNode->tagName = $tagNParts[1];
			} else {
				$newNode->tagName = $tagNParts[0];
			}

			// <img ... /> (open and close)
			if((array_key_exists(4, $res) && $res[4][0] === '/') || (array_key_exists(3, $res) && $res[3][0] === '/') || in_array($res[2][0], $this->selfClosingTags)) {
				$newNode->tagType = ElementNode::TAG_SELFCLOSING;
			} else {
				// (open only)
				$this->pendingNode = $newNode;
				$newNode->tagType = ElementNode::TAG_OPEN;
			}

			// Attributes
			if(array_key_exists(3, $res) && $res[3][0] !== '/') {
				preg_match_all('/(.+?)="(.+?)"/', $res[3][0], $resAttrs, PREG_SET_ORDER);

				foreach($resAttrs as $attr) {
					$newNode->addAttribute(new HtmlAttribute(trim($attr[1]), trim($attr[2])));
				}
			}
		}

		$newNode->parentNode = $oldPendingNode;

		if($oldPendingNode === null) {
			$this->nodeTree->addChildNode($newNode);
		} else {
			$oldPendingNode->addChildNode($newNode);
		}

		return $newNode;
	}

	/**
	 *
	 */
	public function getNodesByNamespace($namespace, $entryNode = null) {
		$nodes = array();
		$nodeList = null;

		if($entryNode === null) {
			$nodeList = $this->nodeTree;
		} else {
			$nodeList = $entryNode->getChildNodes();
		}

		foreach($nodeList as $node) {
			if($node->getNamespace() === $namespace)
				$nodes[] = $node;

			if(!$node->hasChilds())
				continue;

			$nodes = array_merge($nodes, self::getNodesByNamespace($namespace, $node));
		}

		return $nodes;
	}

	/**
	 *
	 */
	public function getNodesByTagName($tagname, $entryNode = null) {
		$nodes = array();
		$nodeList = null;

		if($entryNode === null) {
			$nodeList = $this->nodeTree;
		} else {
			$nodeList = $entryNode->getChildNodes();
		}

		foreach($nodeList as $node) {
			if($node->getTagName() === $tagname)
				$nodes[] = $node;

			if(!$node->hasChilds())
				continue;

			$nodes = array_merge($nodes, self::getNodesByTagName($tagname, $node));
		}

		return $nodes;
	}

	public function getHtml($entryNode = null) {
		$html = '';
		$nodeList = null;

		if($entryNode === null) {
			$nodeList = $this->nodeTree->childNodes;
		} else {
			if($entryNode->hasChilds() === false)
				return $html;

			$nodeList = $entryNode->childNodes;
		}

		foreach($nodeList as $node) {
			if($node instanceof ElementNode === false) {
				$html .= $node->content;
				continue;
			}

			$tagStr = (($node->namespace !== null) ? $node->namespace . ':' : '') . $node->tagName;

			$attrs = array();
			foreach($node->attributesNamed as $key => $val) {
				$attrs[] = $key . '="' . $val->value . '"';
			}
			$attrStr = (count($attrs) > 0) ? ' ' . implode(' ', $attrs) : '';

			$html .= '<' . $tagStr . $attrStr . (($node->tagType === ElementNode::TAG_SELFCLOSING) ? ' /' : '') . '>' . $node->content;


			if($node->tagType === ElementNode::TAG_OPEN)
				$html .= self::getHtml($node) . '</' . $tagStr . '>';
		}

		return $html;
	}

	public function replaceNode(HtmlNode $nodeSearch, HtmlNode $nodeReplace) {
		$parentSrchNode = $nodeSearch->getParentNode();
		$nodeList = null;

		if($parentSrchNode === null) {
			$nodeList = $this->nodeTree;
		} else {
			$nodeList = $nodeSearch->getParentNode()->getChildNodes();
		}

		$countChilds = count($nodeList);

		for($i = 0; $i < $countChilds; $i++) {
			if($nodeList[$i] !== $nodeSearch)
				continue;

			$nodeList[$i] = $nodeReplace;
			break;
		}

		if($parentSrchNode === null) {
			$this->nodeTree = $nodeList;
		} else {
			$parentSrchNode->setChildNodes($nodeList);
		}
	}

	public function getNodeTree() {
		return $this->nodeTree;
	}

	public function addSelfClosingTag($tagName) {
		$this->selfClosingTags[] = $tagName;
	}

}

?>
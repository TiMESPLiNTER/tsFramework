<?php

namespace ch\timesplinter\htmlparser;

/**
 * HtmlDoc
 *
 * @author Pascal Münst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER Webdevelopment
 * @version 1.0.0
 */
class HtmlDoc {
	private $htmlContent;
	private $contentPos;
	private $nodeTree;
	private $pendingNode;
	private $namespace;
	private $selfClosingTags;
	private $currentLine;

	//private $logger;
	
	public function __construct($htmlContent = null, $namespace = null) {
		//$this->logger = LoggerFactory::getEnvLogger($this);
		$this->currentLine = 1; // Start at line 1 not 0, we are no nerds ;-)
		
		$this->htmlContent = $htmlContent;
		//$this->logger->debug('parse html: ' . $htmlContent);
		$this->nodeTree = new DocumentNode($this);
		$this->pendingNode = $this->nodeTree;

		$this->selfClosingTags = array('br', 'hr', 'img', 'input', 'link', 'meta');

		$this->namespace = $namespace;
	}

	public function parse() {
		if($this->htmlContent === null)
			return;

		$this->contentPos = 0;

		while($this->findNextNode() !== false);
		
		if($this->contentPos !== strlen($this->htmlContent)) {
			$restNode = new TextNode($this);
			$restNode->content = substr($this->htmlContent, $this->contentPos);
			$restNode->parentNode = $this->nodeTree;

			$this->nodeTree->addChildNode($restNode);
			
			$this->currentLine += substr_count($restNode->content, "\n");
		}
	}

	private function findNextNode() {
		$oldPendingNode = $this->pendingNode;
		$oldContentPos = $this->contentPos;
		$pattern = $res = null;

		if($this->namespace !== null) {
			$pattern = '/(?:<!--.+?-->|<!\[CDATA\[.+?\]\]>|<(\/)?(' . $this->namespace . '\:[\w]+?)(?:\\s+(.+?))?(\/)?\\s*(?<![-\?])>)/ims'; /* \\s*> => \\s*(?:[^(->)(\?>)]>)*/
		} else {
			$pattern = '/(?:<!--.+?-->|<!\[CDATA\[.+?\]\]>|<(\/)?([\w]+?)(?:\\s+(.+?))?(\/)?\\s*(?<![-\?])>)/ims';
		}
		
		preg_match($pattern, $this->htmlContent, $res, PREG_OFFSET_CAPTURE, $this->contentPos);

		// Wenn kein Tag mehr gefunden wird
		if(count($res) === 0)
			return false;

		$this->currentLine += substr_count($res[0][0], "\n");
		$newPos = $res[0][1];

		if($oldContentPos !== $newPos) {
			// Control-Node
			$lostText = substr($this->htmlContent, $oldContentPos, ($newPos - $oldContentPos));
			$this->currentLine += substr_count($lostText, "\n");
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
				$this->pendingNode->closed = true;
				$this->pendingNode = ($oldPendingNode !== null) ? $oldPendingNode->parentNode : null;
				
				/**
				 * @TODO That's dirty work here
				 */
				if($this->pendingNode === null) {
					//$this->logger->debug('--close manually: </' . $res[2][0] . '>');
					$node = new TextNode($this);
					$node->content = '</' . $res[2][0] . '>';
					
					$this->nodeTree->addChildNode($node);
				}
				
				//$this->logger->debug('</' . $res[2][0] . '>');
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
				
				//$this->logger->debug('<' . $res[2][0] . ' />');
			} else {
				// (open only)
				//$this->logger->debug('<' . $res[2][0] . '> (parent: ' . (($this->pendingNode instanceof ElementNode)?$this->pendingNode->tagName:'?') . ')');
				
				$this->pendingNode = $newNode;
				$newNode->tagType = ElementNode::TAG_OPEN;
			}

			// Attributes
			if(array_key_exists(3, $res) && $res[3][0] !== '/') {
				preg_match_all('/(.+?)="(.*?)"/', $res[3][0], $resAttrs, PREG_SET_ORDER);

				foreach($resAttrs as $attr) {
					$newNode->addAttribute(new HtmlAttribute(trim($attr[1]), trim($attr[2])));
				}
			}
		}

		$newNode->line = $this->currentLine;
		$newNode->parentNode = $oldPendingNode;

		if($oldPendingNode === null) {
			$this->nodeTree->addChildNode($newNode);
		} else {
			$oldPendingNode->addChildNode($newNode);
		}
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

			$nodes = array_merge($nodes, $this->getNodesByTagName($tagname, $node));
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
			if(($node instanceof ElementNode) === false) {
				$html .= $node->content;
				continue;
			}

			$tagStr = (($node->namespace !== null) ? $node->namespace . ':' : '') . $node->tagName;

			$attrs = array();
			foreach($node->attributesNamed as $key => $val) {
				$attrs[] = $key . '="' . $val->value . '"';
			}
			$attrStr = (count($attrs) > 0) ? ' ' . implode(' ', $attrs) : '';
			
			$html .= '<' . $tagStr . $attrStr . $node->tagExtension . (($node->tagType === ElementNode::TAG_SELFCLOSING)?' /':'') . '>' . $node->content;


			if(($node->tagType === ElementNode::TAG_OPEN && $node->closed === true) || $node->tagType === ElementNode::TAG_CLOSE)
				$html .= $this->getHtml($node) . '</' . $tagStr . '>';
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
	
	public function getCurrentLine() {
		return $this->currentLine;
	}
	
	public function setCurrentLine($currentLine) {
		$this->currentLine = $currentLine;
	}
}

?>
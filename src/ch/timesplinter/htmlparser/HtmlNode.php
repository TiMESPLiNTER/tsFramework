<?php

namespace ch\timesplinter\htmlparser;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER Webdevelopment
 */
abstract class HtmlNode {

	const ELEMENT_NODE = 1;
	const ATTRIBUTE_NODE = 2;
	const TEXT_NODE = 3;
	const CDATA_SECTION_NODE = 4;
	const ENTITY_REFERENCE_NODE = 5;
	const ENTITY_NODE = 6;
	const PROCESSING_INSTRUCTION_NODE = 7;
	const COMMENT_NODE = 8;
	const DOCUMENT_NODE = 9;
	const DOCUMENT_TYPE_NODE = 10;
	const DOCUMENT_FRAGMENT_NODE = 11;
	const NOTATION_NODE = 12;

	public $nodeType;
	public $line;
	public $childNodes;
	
	/** @var HtmlNode */
	public $parentNode;
	public $content;

	/** @var HtmlDoc */
	private $htmlDoc;

	public function __construct($nodeType, HtmlDoc $htmlDoc)
	{
		$this->htmlDoc = $htmlDoc;

		$this->parentNode = null;

		$this->content = '';
		$this->nodeType = $nodeType;
	}

	/**
	 *
	 * @return array All sub nodes
	 */
	public function getAllSubNodes() {
		$subNodes = array();

		foreach($this->childNodes as $cn) {
			$subNodes[] = $cn;

			if(count($cn->childNodes) > 0)
				$subNodes = array_merge($subNodes, $cn->getAllSubNodes());
		}

		return $subNodes;
	}

	/**
	 * Replaces a node with another one
	 *
	 * @param HtmlNode $nodeToReplace The node to replace
	 * @param HtmlNode $replacementNode The replacement node for the original one
	 *
	 * @throws \Exception
	 */
	public function replaceNode(HtmlNode $nodeToReplace, HtmlNode $replacementNode) {
		$pos = $this->findNodePosition($nodeToReplace);

		if($pos === null)
			throw new \Exception('Nix gut... Node for replacement nicht gefunden.');

		$this->childNodes[$pos] = $replacementNode;
	}

	/**
	 * Inserts a node before another one
	 * @param mixed $nodesToInsert A single HtmlNode object or an array of
	 * multiple HtmlNode objects
	 * @param HtmlNode $beforeNode HtmlNode object before the new nodes should
	 * be inserted
	 */
	public function insertBefore($nodesToInsert, HtmlNode $beforeNode) {
		$pos = $this->findNodePosition($beforeNode);

		if(!is_array($nodesToInsert))
			$nodesToInsert = array($nodesToInsert);

		array_splice($this->childNodes, $pos, 0, $nodesToInsert);

		$this->childNodes = array_values($this->childNodes);
	}

	private function findNodePosition(HtmlNode $findNode) {
		$countChilds = count($this->childNodes);

		for($i = 0; $i < $countChilds; $i++) {
			if($this->childNodes[$i] === $findNode)
				return $i;
		}

		return null;
	}

	/**
	 * Removes a node from the child nodes
	 * 
	 * @param HtmlNode $nodeToRemove
	 * 
	 * @return void
	 */
	public function removeNode(HtmlNode $nodeToRemove) {
		$countChilds = count($this->childNodes);

		for($i = 0; $i < $countChilds; $i++) {
			if($this->childNodes[$i] !== $nodeToRemove)
				continue;

			unset($this->childNodes[$i]);
			$this->childNodes = array_values($this->childNodes);

			return;
		}
	}

	/**
	 * Adds a child node to the list
	 * @param HtmlNode $childNode
	 */
	public function addChildNode(HtmlNode $childNode)
	{
		$this->childNodes[] = $childNode;
	}

	/**
	 * Returns the next sibling
	 * @return ElementNode|null The next sibling or NULL if no next sibling exists
	 */
	public function getNextSibling()
	{
		$cNodes = $this->parentNode->childNodes;
		$cNodesCount = count($cNodes);

		$nextPos = $this->parentNode->findNodePosition($this);
		$nextPos++;

		for($i = $nextPos; $i < $cNodesCount; $i++) {
			if($cNodes[$i] instanceof $this)
				return $cNodes[$i];
		}

		return null;
	}

	/**
	 * Returns the previous sibling
	 * @return HtmlNode The previous sibling or NULL of no previous sibling
	 * exists
	 */
	public function getPrevSibling() {
		$cNodes = $this->parentNode->childNodes;
		//$cNodesCount = count($cNodes);

		foreach($cNodes as $cn) {
			if($cn instanceof $this)
				echo $cn->tagName, ',';
		}

		echo $prevPos = $this->parentNode->findNodePosition($this);

		echo ':', $cNodes[$prevPos]->tagName, ',';

		$prevPos--;

		for($i = $prevPos; $i > 0; $i--) {
			//echo $i,':' , $cNodes[$i]->nodeType , ':', get_class($this), ',';
			if($cNodes[$i] instanceof $this)
				return $cNodes[$i];
		}

		return null;
	}

	public function findChildNodes($filter) {
		$childNotes = array();
		
		foreach($this->childNodes as $cn) {
			if(($cn->namespace !== null?$cn->namespace . ':':null) . $cn->tagName !== $filter)
				continue;
			
			$childNotes[] = $cn;
		}
		
		return $childNotes;
	}
	
	/**
	 * Sets a child node list for this node
	 * @param type $childNodes
	 */
	public function setChildNodes($childNodes) {
		$this->childNodes = $childNodes;
	}

	public function getParentNode() {
		return $this->parentNode;
	}

	public function setParentNode($parentNode) {
		$this->parentNode = $parentNode;
	}

	/**
	 * Checks if the node has child nodes or not
	 * @return boolean
	 */
	public function hasChilds() {
		if(count($this->childNodes) > 0)
			return true;

		return false;
	}

}

?>
<?php

namespace ch\timesplinter\customtags;

use ch\timesplinter\template\TemplateEngine;
use ch\timesplinter\template\TemplateTag;
use ch\timesplinter\template\TagInline;
use ch\timesplinter\template\TagNode;
use ch\timesplinter\htmlparser\ElementNode;
use ch\timesplinter\htmlparser\TextNode;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER Webdevelopment
 */
class PrintTag extends TemplateTag implements TagNode, TagInline
{
	public function __construct()
	{
		parent::__construct('print', false, true);
	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $node)
	{
		$replValue = $this->replace($tplEngine, $node->getAttribute('var')->value);

		$replNode = new TextNode($tplEngine->getDomReader());
		$replNode->content = $replValue;

		$node->parentNode->replaceNode($node, $replNode);
	}

	public function replaceInline(TemplateEngine $tplEngine, $params)
	{
		return $this->replace($tplEngine, $params['var']);
	}

	public function replace(TemplateEngine $tplEngine, $selector)
	{
		return '<?php echo ' . __CLASS__ .'::generateOutput($this, \'' . $selector . '\'); ?>';
	}
	
	public static function generateOutput(TemplateEngine $templateEngine, $selector)
	{
		$data = $templateEngine->getDataFromSelector($selector);
		
		if($data instanceof \DateTime)
			return $data->format('Y-m-d H:i:s');
		elseif(is_scalar($data) === false)
			return print_r($data, true);
		
		return $data;
	}
}

/* EOF */
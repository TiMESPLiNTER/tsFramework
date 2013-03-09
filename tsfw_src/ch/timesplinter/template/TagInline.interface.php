<?php
namespace ch\timesplinter\template;

/**
 * TagInline
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
interface TagInline {
	/**
	 * Replace the inline tag
	 * @param \ch\timesplinter\template\TemplateEngine $tplEngine
	 * @param string $tagStr
	 */
	public function replaceInline(TemplateEngine $tplEngine, $tagStr);
}

?>
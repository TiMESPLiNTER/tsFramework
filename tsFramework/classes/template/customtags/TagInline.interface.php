<?php

/**
 * TagInline
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
interface TagInline {

	public function replaceInline(TemplateEngine $tplEngine, $tagStr);
}

?>
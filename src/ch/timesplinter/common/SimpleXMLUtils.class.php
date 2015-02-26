<?php

namespace ch\timesplinter\common;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2014 by TiMESPLiNTER Webdevelopment
 */
class SimpleXMLUtils
{
	public static function getString(\SimpleXMLElement $xmlElement)
	{
		return (string)$xmlElement;
	}
	
	public static function getInteger(\SimpleXMLElement $xmlElement)
	{
		$strValue = (string)$xmlElement;
		
		return intval($strValue);
	}
	
	public static function getBoolean(\SimpleXMLElement $xmlElement)
	{
		$strValue = (string)$xmlElement;
		
		if($strValue === 'true')
			return true;
		elseif($strValue === 'false')
			return false;
		else
			throw new \Exception('\'' . $strValue . '\' is no valid boolean value');
		
	}
}

/* EOF */
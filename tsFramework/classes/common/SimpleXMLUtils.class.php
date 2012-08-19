<?php

/**
 * Description of SimpleXMLUtils
 *
 * @author pascal91
 */
class SimpleXMLUtils {
	//put your code here
	
	public static function getString($xmlElement) {
		return (string)$xmlElement;
	}
	
	public static function getInteger($xmlElement) {
		$strValue = (string)$xmlElement;
		
		return intval($strValue);
	}
	
	public static function getBoolean($xmlElement) {
		$strValue = (string)$xmlElement;
		
		if($strValue === 'true')
			return true;
		elseif($strValue === 'false')
			return false;
		else
			throw new Exception('\'' . $strValue . '\' is no valid boolean value');
		
	}
}

?>

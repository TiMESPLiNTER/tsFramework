<?php
namespace ch\timesplinter\common;

/**
 * Description of StringUtils
 *
 * @author pascal91
 */
class StringUtils {
    public static function between($str, $start, $end) {
        $posStart = strpos($str, $start) + strlen($start);
        $posEnd = strrpos($str, $end, $posStart);
        
		if($posEnd === false)
			return null;
		
        return substr($str, $posStart, $posEnd-$posStart);   
    }
    
	public static function beforeFirst($str, $before) {
        $posUntil = strpos($str, $before);
        
        if($posUntil === false)
            return $str;
        
        return substr($str, 0, $posUntil);
    }
	
    public static function beforeLast($str, $before) {
        $posUntil = strrpos($str, $before);
        
        if($posUntil === false)
            return $str;
        
        return substr($str, 0, $posUntil);
    }
    
    public static function afterLast($str, $after) {
        $posFrom = strrpos($str, $after);
        
        if($posFrom === false)
            return null;
        
        return substr($str, $posFrom+1);
    }
	
	public static function afterFirst($str, $after) {
        $posFrom = strpos($str, $after);
        
        if($posFrom === false)
            return null;
        
        return substr($str, $posFrom+strlen($after));
    }
	
	public static function startsWith($str, $startStr) {
		return (strpos($str, $startStr) === 0);
	}
	
	public static function endsWith($str, $endStr) {
		$endStrlen = strlen($endStr);
		
		return (strrpos($str, $endStr)+$endStrlen === strlen($str));
	}
}

?>

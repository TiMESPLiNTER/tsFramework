<?php

/**
 * Description of StringUtils
 *
 * @author pascal91
 */
class StringUtils {
    public static function between($str, $start, $end) {
        $posStart = strpos($str, $start);
        $posEnd = strrpos($str, $end);
        
        return substr($str, $posStart, $posEnd);   
    }
    
    public static function before($str, $before) {
        $posUntil = strrpos($str, $before);
        
        if($posUntil === false)
            return $str;
        
        return substr($str, 0, $posUntil);
    }
    
    public static function after($str, $after) {
        $posFrom = strrpos($str, $after);
        
        if($posFrom === false)
            return $str;
        
        return substr($str, $posFrom+1);
    }
}

?>

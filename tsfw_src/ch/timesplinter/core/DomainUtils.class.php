<?php
namespace ch\timesplinter\core;

/**
 * Description of DomainUtils
 *
 * @author pascal91
 */
class DomainUtils {
	public static function getDomainInfo($domains, $domain) {
		return isset($domains->$domain)?$domains->$domain:null;
	}
}

?>

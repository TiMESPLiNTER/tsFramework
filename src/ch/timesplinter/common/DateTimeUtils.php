<?php


namespace ch\timesplinter\common;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2013, TiMESPLiNTER Webdevelopment
 * @version 1.0.0
 */
class DateTimeUtils {
	/**
	 * @param \DateTime $dt The DateTime object to format
	 * @param string $format The format according to the strftime() options
	 * @param string|null $locale An other locale than set at the moment
	 * @return string The formated datetime string
	 */
	public static function formatLocalized(\DateTime $dt, $format, $locale = null) {
		$prevLocale = null;

		if($locale !== null) {
			$prevLocale = setlocale(LC_TIME, null);
			setlocale(LC_TIME, $locale);
		}

		$datetimeStr = strftime($format, $dt->getTimestamp());

		if($prevLocale !== null)
			setlocale(LC_TIME, $prevLocale);

		return $datetimeStr;
	}
}

/* EOF */
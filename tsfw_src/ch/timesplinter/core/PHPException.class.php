<?php

namespace ch\timesplinter\core;

use ch\timesplinter\logger\LoggerFactory;
use ch\timesplinter\mailer\MailFactory;

/**
 * Class PHPException
 * @package ch\timesplinter\core
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER Webdevelopment
 */
class PHPException extends FrameworkException {
	/**
	 * @param string $number
	 * @param int $message
	 * @param string $file
	 * @param int $line
	 */
	public function __construct($number, $message, $file, $line) {
		parent::__construct($message, $number);

		$this->file = $file;
		$this->line = $line;
	}
}

/* EOF */
<?php

namespace ch\timesplinter\db;

/**
 * @author Pascal Muenst <entwicklung@metanet.ch>
 * @copyright Copyright (c) 2014, METANET AG
 */
class DBQueryException extends DBException
{
	protected $queryString;
	protected $queryParams;

	public function __construct($message, $code, $queryString = '', $queryParams = array())
	{
		parent::__construct($message, $code);

		$this->queryString = $queryString;
		$this->queryParams = $queryParams;
	}

	public function getQueryString()
	{
		return $this->queryString;
	}

	public function getQueryParams()
	{
		return $this->queryParams;
	}
}

/* EOF */ 
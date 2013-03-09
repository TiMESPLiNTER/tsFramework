<?php
namespace ch\timesplinter\examplesite\popo;
/**
 * Description of UserPopo
 *
 * @author Pascal Münst <dev@timesplinter.ch>
 * @copyright (c) 2012, Pascal Münst
 * @version 1.0
 */

/**
 * @table(name='user')
 */
class UserPopo extends PHibernatePopo {
	/**
	 * @primarykey(column='ID', type='int', generated=true) 
	 */
	public $ID;
	/**
	 * @property(column='username', type='string')
	 */
	public $username;
	/**
	 * @property(column='email', type='string')
	 */
	public $email;
	/**
	 * @property(column='password', type='string')
	 */
	public $password;
	/**
	 * @property(column='lastlogin', type='datetime')
	 */
	public $lastlogin;
	/**
	 * @foreignkey(name='test', references='address.ID') 
	 */
	public $addressIDFK;
}

?>

<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* CodeIgniter Payments
*
* Make payments to multiple payment systems using a single interface
*
* @package CodeIgniter
* @subpackage Sparks
* @category Payments
* @author Calvin Froedge (www.calvinfroedge.com)
* @created 07/02/2011
* @alpha version 03/06/2012
* @license http://www.opensource.org/licenses/mit-license.php
* @link https://github.com/calvinfroedge/codeigniter-payments
* @whatsup Yea, so this is pretty simple.  We just load PHP_Payments and add a caller on top of it, and allow config to be set within the spark.
*/

class Payments
{
	/*
	* CI Instance
	*/
	public $ci;

	/*
	* Constructor
	*/
	public function __construct($config = array())
	{
		$this->ci =& get_instance();

		$this->ci->config->load('payments', true);

		$defaults = array(
			'mode' => $this->ci->config->item('mode', 'payments'),
			'force_secure_connection' => $this->ci->config->item('force_secure_connection', 'payments')
		);

		$config = array_merge($defaults, $config);

		require(dirname(__DIR__)."/src/php-payments/lib/payments.php");

		$this->php_payments = new PHP_Payments($config);

		//Ignore CI classes so our autoloader doesn't interfere
		Payment_Utility::$autoload_ignore = array(
			'CI_'
		);
	}

	/*
	* Caller Magic (Overloaded) Method
	*/
	public function __call($method, $params)
	{
		$gateway = $params[0];
		$args = $params[1];

		if(file_exists(dirname(__DIR__)."/config/drivers/{$gateway}.php"))
		{
			$this->ci->load->config("drivers/{$gateway}.php");
			$config = $this->ci->config->item($gateway);
		}
		else
		{
			$config = (isset($params[2])) ? $params[2] : null;
		}
		return $this->php_payments->$method($gateway, $args, $config);
	}
}
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Response Codes
 * 000 - Local Failure
 * 011 - Failure at Remote Server / Payment Gateway
*/
$config['response_codes'] = array (
	'not_a_module'		=> '000',
	'invalid_input' 	=> '001',
	'not_a_method'		=> '002',
);

/**
 * Response Messages
 * 
 * These messages return something that could be meaningful in a log message or to a user.
*/
$config['response_messages'] = array (
	'not_a_module'		=> 'The payment module provided is not valid.',
	'invalid_input' 	=> 'The input provided is not in the correct format',
	'not_a_method'		=> 'The method called does not exist',
);
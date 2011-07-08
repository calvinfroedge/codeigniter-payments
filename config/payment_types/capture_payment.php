<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['capture_payment'] = array
		(
			'identifier'			=>	'',  //Required. Unique identifier for the transaction, generated from a previous authorization.
			'amt'					=>	'', 
			'final'					=>	'',	//Whether or not this is the final charge.
			'inv_num'				=>	'',	//Matches some invoice in your own system.
			'note'					=>	'',
			'cc_statement_descrip'	=>	'',
			'cc_type'				=>	'',	//REQUIRED.  Visa, MasterCard, Discover, Amex
			'cc_number'				=>	'', //REQUIRED.  Credit card number
			'cc_exp'				=>	'', //REQUIRED.  Must be formatted MMYYYY
		);
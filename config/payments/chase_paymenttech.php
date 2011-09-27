<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Notes & Unsupported Parameters
|--------------------------------------------------------------------------
|
|The following parameters were omitted:
| - IndustryType
| - CurrencyExponent
| - AVSzip
| - AVSaddress1
| - AVSaddress2
| - AVScity
| - AVSstate
| - AVSphoneNum
| - AVSname
| - CustomerRefNum - Customer supplied identifier for the transaction
| - OrderID - Customer supplied OrderID
*/


/*
|--------------------------------------------------------------------------
| Payment Gateway Credentials
|--------------------------------------------------------------------------
|
| Fill out the following information with those provided 
| to you by your gateway.
| 
*/
$config['api_username']		= "HELLONEWORK27"; // The Username for the API
$config['api_password'] = "SJMHBVF67L9CW"; // The Password for the API
$config['api_merchant_id'] = '700000202915';
$config['api_terminal_id'] = '001';

$config['api_customer_ref_number_settings'] = 'A'; /* Possible Values:
A: Auto-generate the Customer Reference Number. In other words, the Orbital Gateway will assign the Customer Reference Number and return it in the response.
S: The Orbital Gateway will use the value passed in the <CustomerRefNum> element as the Customer Reference Number.
O: This option only relates to when a Profile is added as a part of an authorization request. In this circumstance, the value passed in the <OrderID> element is used as the Customer Reference Number. For example, this would be used in circumstances wherein the Order ID also represents your customer’s identification in your system, such as a Policy Number for an insurance company.
D: This option only relates to when a Profile is added as a part of an authorization request. In this circumstance, the value passed in the <Comments> element is used as the Customer Reference*/

$config['api_customer_profile_order_override'] = 'NO';/*
The Orbital Gateway has configuration options for the Profile setup to determine how the Customer Reference Number is leveraged to populate other data sets using the <CustomerProfileOrderOverrideInd> value.
The options are:
NO
No mapping to order data.
OI
Pre-populate <OrderID> with the Customer Reference Number.
OD
Pre-populate the <Comments> field (this field is called Order Description in the Virtual Terminal) with the Customer Reference Number.
The relevance of this feature is on the PNS platform (BIN 000002), where the <Comments> field populates the Customer-Definable Data. This data can then be made available on certain Resource Online Reports. Any questions about your reports should be directed to your Relationship Manager.
OA
Pre-populate the <OrderId> and <Comments> fields with the Customer Reference Number.*/


/*
|--------------------------------------------------------------------------
| Payment Gateway Settings
|--------------------------------------------------------------------------
|
| You should be able to leave these alone unless the API changes.
| 
*/
$config['api_endpoint_test'] = "https://orbitalvar1.paymentech.net/authorize";
$config['api_endpoint_production'] = "https://orbital1.paymentech.net/authorize";
$config['api_version'] = "PTI51";
$config['paymentech_BIN'] = '000002';//BIN 000001 (Salem Platform) or BIN 000002 (PNS Platform).  See section 3.3.1.1 in orbital_gateway_xml_specification.pdf
$config['test_mode'] = TRUE;


/*
|--------------------------------------------------------------------------
| Payment to Gateway Key Map
|--------------------------------------------------------------------------
|
| This allows you to setup a centralized key map conversion table.
| The array key is CodeIgniter Payments key and the value is the 
| current gateways (e.g. Chase PaymentsTech) key
| 
| NOTE: If the key doesn't exist for your gateway, remove the
| key or set it to FALSE to prevent it from being passed
| 
| This array will be used in the private function _map_params()
|
*/
$config['payment_to_gateway_key_map'] = array(
			'cc_type'			=> 'CardBrand',	//SW Switch/Solo, ED European Direct Debit, DP PINless Debit (Generic Value Used in Requests), IM International Maestro
			'cc_number'			=> 'AccountNum', //Credit card number
			'cc_exp'			=> 'Exp', //Must be formatted MMYYYY @todo - Must translate to MMYY
			'cc_code'			=> 'CardSecVal', //3 or 4 digit cvv code
			'street'			=> 'AVSaddress1', //street address of the purchaser @todo - Only 64 Char
			'street2'			=> 'AVSaddress2', //street address 2 of purchaser @todo - Only 64 Char
			'city'				=> 'AVScity', //city of the purchaser @todo - Only 32 Char
			'state'				=> 'AVSstate', //state of the purchaser @todo - Only 16 Char; 2 lttr abbr pref.
			'country'			=> 'AVScountryCode', // country of the purchaser (64 Char)
			'postal_code'		=> 'AVSzip', //zip code of the purchaser (16 Char)
			'amt'				=> 'Amount', //purchase amount (XXXXXXX.XX FORMAT) Includes Tax and Tip
			'phone'				=> 'AVSphoneNum', //phone num of customer shipped to @todo - Required for ACH; 16 Chars.
			'identifier' 		=> 'TxRefNum', //Merchant provided identifier for the transaction @todo - IS PREVIOUS TRANS_ID AND ONLY REQUIRED FOR CAPTURE OR REFUND.
			'currency_code'		=> 'CurrencyCode', //currency code to use for the transaction.
);

/*
|--------------------------------------------------------------------------
| Required Parameters
|--------------------------------------------------------------------------
|
| Avoid sending payments to the gateway unless param requirements are met
| 
| NOTE: You should setup form_validation to prevent these parameters
|  from being missed on the payment page.
|
*/
$config['required_params'] = array(
	'oneoff_payment'	=> array(
		'cc_number',
		'cc_code',
		'cc_exp',
		'amt'
	),
	'authorize_payment'	=> array(
		'cc_number',
		'cc_code',
		'cc_exp',
		'amt'	
	),
	'capture_payment'	=> array(
		'identifier'
	),
	'void_payment'		=> array(
		'identifier'
	),
	'refund_payment'	=> array(
		'identifier'
	)
);
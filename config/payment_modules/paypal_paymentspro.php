<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

$config['paypal_api_username'] = "iambrs_1298074268_biz_api1.gmail.com";
$config['paypal_api_password'] = "1298074286";
$config['paypal_api_signature'] = "Awe05O9DgD-XyAL3-HsFoqNs..1VAOncRYkwEN.LCh-94svEO5c0i0Ar";
$config['paypal_api_endpoint'] = "https://api-3t.sandbox.paypal.com/nvp?";
$config['paypal_api_version'] = "66.0";

//Default Method Params.  You can override these when you call any method.  

$config['method_params'] = 
array(
	'oneoff_payment' =>	array
	(
		'ip_address'		=>	'',	//REQUIRED.  IP address of purchaser
		'cc_type'			=>	'',	//REQUIRED.  Visa, MasterCard, Discover, Amex
		'cc_number'			=>	'', //REQUIRED.  Credit card number
		'cc_exp'			=>	'', //REQUIRED.  Must be formatted MMYYYY
		'cc_code'			=>	'', //RREQUIRED.  3 or 4 digit cvv code
		'email'				=>	'', //REQUIRED.  email associated with account being billed
		'first_name'		=>	'', //first name of the purchaser
		'last_name'			=>	'', //last name of the purchaser
		'street'			=>	'', //REQUIRED.  street address of the purchaser
		'street2'			=>	'', //street address 2 of purchaser
		'city'				=>	'', //REQUIRED.  city of the purchaser
		'state'				=>	'', //REQUIRED.  state of the purchaser
		'countrycode'		=>	'', //REQUIRED.  country of the purchaser
		'zip'				=>	'', //REQUIRED.  zip code of the purchaser
		'amt'				=>	'', //REQUIRED.  purchase amount
		'ship_to_phone_num'	=>	'', //phone num of customer shipped to
		'currency_code'		=>	'', //currency code to use for the transaction.
		'item_amt'			=>	'', //Amount for just the item being purchased.
		'shipping_amt'		=>	'', //Amount for just shipping.
		'insurance_amt'		=>	'', //Amount for just insurance.
		'shipping_disc_amt'	=>	'', //Amount for just shipping.
		'handling_amt'		=>	'', //Amount for just handling.
		'tax_amt'			=>	'', //Amount for just tax.
		'desc'				=>	'', //Description for the transaction
		'custom'			=>	'', //Free form text field
		'inv_num'			=>	'', //Invoice number
		'button_source'		=>	'', //An identification code for use by third-party applications to identify transactions.  Character length and limitations: 32 single-byte alphanumeric characters
		'notify_url'		=>	''	//Your URL for receiving Instant Payment Notification (IPN) about this transaction. If you do not specify this value in the request, the notification URL from your Merchant Profile is used, if one exists.

	),
);
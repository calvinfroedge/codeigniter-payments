<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

$config['paypal_api_username'] = "iambrs_1298074268_biz_api1.gmail.com";
$config['paypal_api_password'] = "1298074286";
$config['paypal_api_signature'] = "Awe05O9DgD-XyAL3-HsFoqNs..1VAOncRYkwEN.LCh-94svEO5c0i0Ar ";
$config['paypal_api_endpoint'] = "https://api-3t.sandbox.paypal.com/nvp?";
$config['paypal_api_version'] = "66.0";

/**
 * Direct Payment Settings
 *
 * @config['PAYMENTACTION']  This can be set to Authorization or Sale.  Authorization indicates that this payment is a basic authorization subject to settlement with PayPal Authorization & Capture.  Sale indicates that this is a final sale for which you are requesting payment.
 * @config['RETURNFMDETAILS'] Set to true or false.  Whether or not you want to receive fraud management details.
*/
$config['paypal_direct_payment'] = array(
	'RETURNFMFDETAILS' => '1'
);
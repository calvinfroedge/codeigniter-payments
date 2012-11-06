## This code has taken tons of work.  Donations highly appreciated.  [Make a Donation](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=TJMWX5E9GXS7S "Make a Donation to Codeigniter Payments")

# Codeigniter Payments

CodeIgniter Payments is an abstraction library for supporting multiple payments providers.  The project began in February 2011 as a CodeIgniter library and has since evolved into a framework agnostic payments engine with support for CodeIgniter.

At time of writing, the following gateways are supported, but it's very possible more gateways have been added since:

- PayPal Payments Pro
- Authorize.net (AIM)
- Psigate
- Beanstream
- QuickBooks Merchant Services
- Eway (Australia)
- Amazon SimplePay
- Stripe
- Google Checkout
- GoCardless
- Braintree Payments
- BluePay

## NOTICE - USING CODEIGNITER-PAYMENTS ALONE DOES NOT MAKE YOU PCI COMPLIANT

It is highly recommended that you attempt to architect your application to achieve some level of PCI compliance.  Without this, the applications you create can be vulnerable to fines for PCI compliance violations.  Using codeigniter-payments does not circumvent the need for you to do this.  You can check out the PCI compliance self assessment form here: https://www.pcisecuritystandards.org/merchants/self_assessment_form.php

## Installing

Available via Sparks.  For info about how to install sparks, go here: http://getsparks.org/install

You can then load the spark with this:

```php
$this->load->spark('codeigniter-payments/[version #]/');
```

## IMPORTANT!

1.  If you want to test locally (and you should), you need to set "force_secure_connection" to FALSE in config/payments.php

2.  By default, test api endpoints will be used.  To enable production endpoints, change the mode in /config/payments.php from 'test' to 'production'.  Note that if you are a Psigate customer, you must obtain your production endpoint from Psigate support.

3.  When you load gateways, the config can either be passed in the constructor or loaded from a config file.

## Gateway Support

The following gateways are supported:

## Method Support List

This is constantly changing.  Please visit http://payments.calvinfroedge.com or run php doxgen.php in the src/php-payments/documentation folder to see the latest documentation.

## Configuration

To create a config file, copy a config file from src/php-payments/config/drivers for a driver you want to use into the spark config/{driver_name} folder.  The name of the file should stay the same.  You will, however, need to make each param inside the config file reside inside a an array matching the gateway name.  For example:

```php
$config = array(
	'authorize_net' => array(
		'config1' => 'This is the config param'
	)
);
```

If you don't pass config in an array, and don't create a config file, the config in src/php-payments/config/drivers will be used - which is only for testing and is probably not what you want.

## Making Requests
 
A request is formatted thusly:

```php
$this->payments->payment_action('gateway_name', $params, $config);
```

Note that the third array for config is optional. 

## Responses

There are two types of responses returned, local responses and gateway responses.  If a method is not supported, required params are missing, a gateway does not exist, etc., a local response will be returned.  This prevents the transaction from being sent to the gateway and the gateway telling you 3 seconds later there is something wrong with your request.:

```php
'type'				=>	'local_response',  //Indicates failure was local
'status' 			=>	$status, //Either success or failure
'response_code' 	=>	$this->_response_codes[$response], 
'response_message' 	=>	$this->_response_messages[$response],
'details'			=>	$response_details
```
Access response properties by naming your call something like this:

```php
$response = $this->payments->payment_action('gateway_name', $params); 
```

Then you can do:

```php
$status = $response->status;
```

Gateway responses will usually have a full response from the gateway, and on failure a 'reason' property in the details object:

```php
'type'				=>	'gateway_response',
'status' 			=>	$status, 
'response_code' 	=>	$this->_response_codes[$response], 
'response_message' 	=>	$this->_response_messages[$response],
'details'			=>	$details
```

You can access this like $response->details->reason.  You may want to save the full gateway response (it's an array) in a database table, you can access it at $response->details->gateway_response

## Adding Drivers, More Documentation

More information on adding your own drivers (including a video walkthrough) and documentation are available online at http://payments.calvinfroedge.com

## LICENSE

Copyright (c) 2011-2012 Calvin Froedge

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

# Codeigniter Payments

## Installing

Available via Sparks.  For info about how to install sparks, go here: http://getsparks.org/install

You can then load the spark with this:

```php
$this->load->spark('codeigniter-payments/[version #]/');
```

There are config files for each gateway in the /config folder of the spark.  You need to enter your own API usernames and passwords (the ones in there are mine, used only for testing purposes) in the config of each gateway you would like to use.

## Gateway Support

The following gateways are supported:

- PayPal Payments Pro
- Authorize.net (AIM)
- Psigate
- Beanstream
- QuickBooks Merchant Services
- Eway (Australia)

## Methods Supported (note, not all methods are supported by all gateways.  For a full method support list visit http://payments.calvinfroedge.com/gateways ):

- oneoff_payment: Makes a one time charge
- authorize_payment: Authorizes a charge, which is essentially a hold.  This requires later capturing the funds you authorized (most gateways require you do this the same business day).
- capture_payment: Finalize an authorization, actually charges the customer.
- void_payment: Cancel a payment that has not been settled (transactions get settled at the end of the business day).
- refund_payment: Refund (or credit) a customer for a settled transaction.
- get_transaction_details: Get details from a particular transaction.
- change_transaction_status: Changes a transaction's status, ie to Accept or Decline.  Only available with PayPal.
- search_transactions: Query transactions by parameters you define.  Only available with PayPal.
- recurring_payment: Start a new recurring profile.
- get_recurring_profile: Get info about a profile.
- activate_recurring_profile: Activate a recurring profile that has been suspended.
- cancel_recurring_profile: Cancel a recurring profile.
- suspend_recurring_profile: Hold the recurring billing, but don't cancel.
- recurring_bill_outstanding: Charge outstanding payments to a customer.
- update_recurring_profile: Update amounts, names, addresses, etc.

If you use a method that is not supported, the gateway will return a local failure.

## Parameters Available

You can see the params available for each method in /config/payment_types/method_name

An important parameter, that you will use often, is 'identifier.'  'Identifier' is used to uniquely identify recurring transactions and payments.  After each payment (ie authorization, capture, recurring billing), an identifier will be returned in the response which you can use to make later calls with that affect that payment.

## Making Requests

Examples for all gateways are available in /examples.  A request is formatted thusly:

```php
$this->payments->payment_action('gateway_name', $params);
```

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
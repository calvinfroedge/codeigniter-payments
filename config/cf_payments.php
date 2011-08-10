<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
  * Supported Methods
*/
$config['supported_methods'] = array(
	'search_transactions',  //Search transactions based on criteria you define.
	'get_transaction_details', //Returns a summary of a particular transaction
	'authorize_payment',  //Authorize a payment.  Does not actually charge a customer.
	'capture_payment', //Capture a payment
	'oneoff_payment', //A one time payment
	'void_payment',  //Void a payment that has not yet been settled / finalized
	'change_transaction_status', //Change a particular transaction's status
	'refund_payment', //Refund a payment
	'recurring_payment',  //Create a recurring payment profile
	'get_recurring_profile',  //Returns details about a recurring payment profile
	'suspend_recurring_profile', //Suspends a recurring payment profile
	'activate_recurring_profile',  //Activates a recurring payment profile which is suspended
	'cancel_recurring_profile', //Cancels a recurring payment profile
	'recurring_bill_outstanding',  //Bill an outstanding amount owed by a particular recurring payment customer
	'update_recurring_profile'  //Update a particular recurring profile
);

/**
 * Response Codes
 * 000 - Local Failure
 * 011 - Failure at Payment Gateway
 * 100 - Success!
*/
$config['response_codes'] = array (
	'not_a_module'									=>	'000',
	'invalid_input' 								=>	'000',
	'not_a_method'									=>	'000',
	'required_params_missing'						=>	'000',
	
	//Payment Methods
	'authorize_payment_success'						=>	'100',
	'authorize_payment_local_failure'				=>	'000',	
	'authorize_payment_gateway_failure'				=>	'011',	
	'oneoff_payment_success'						=>	'100',
	'oneoff_payment_local_failure'					=>	'000',
	'oneoff_payment_gateway_failure'				=>	'011',
	'capture_payment_success'						=>	'100',
	'capture_payment_local_failure'					=>	'000',
	'capture_payment_gateway_failure'				=>	'011',
	'void_payment_success'							=>	'100',
	'void_payment_local_failure'					=>	'000',
	'void_payment_gateway_failure'					=>	'011',
	'get_transaction_details_success'				=>	'100',
	'get_transaction_details_local_failure'			=>	'000',
	'get_transaction_details_gateway_failure'		=>	'011',
	'change_transaction_status_success'				=>	'100',
	'change_transaction_status_local_failure'		=>	'000',	
	'change_transaction_status_gateway_failure'		=>	'011',
	'refund_payment_success'						=>	'100',
	'refund_payment_local_failure'					=>	'000',
	'refund_payment_gateway_failure'				=>	'011',	
	'search_transactions_success'					=>	'100',
	'search_transactions_local_failure'				=>	'000',
	'search_transactions_gateway_failure'			=>	'011',	
	'recurring_payment_success'						=>	'100',
	'recurring_payment_local_failure'				=>	'000',	
	'recurring_payment_gateway_failure'				=>	'011',		
	'get_recurring_profile_success'					=>	'100',
	'get_recurring_profile_local_failure'			=>	'000',
	'get_recurring_profile_gateway_failure'			=>	'011',		
	'suspend_recurring_profile_success'				=>	'100',
	'suspend_recurring_profile_local_failure'		=>	'000',
	'suspend_recurring_profile_gateway_failure'		=>	'011',		
	'activate_recurring_profile_success'			=>	'100',
	'activate_recurring_profile_local_failure'		=>	'000',
	'activate_recurring_profile_gateway_failure'	=>	'011',		
	'cancel_recurring_profile_success'				=>	'100',
	'cancel_recurring_profile_local_failure'		=>	'000',
	'cancel_recurring_profile_gateway_failure'		=>	'011',		
	'recurring_bill_outstanding_success'			=>	'100',
	'recurring_bill_outstanding_local_failure'		=>	'000',
	'recurring_bill_outstanding_gateway_failure'	=>	'011',		
	'update_recurring_profile_success'				=>	'100',
	'update_recurring_profile_local_failure'		=>	'000',
	'update_recurring_profile_gateway_failure'		=>	'011',		
);

/**
 * Response Messages
 * 
 * These messages return something that could be meaningful in a log message or to a user.
*/
$config['response_messages'] = array (
	'not_a_module'									=>	'The payment module provided is not valid.',
	'invalid_input' 								=>	'The input provided is not in the correct format',
	'invalid_date_params'							=>	'Units of time must be called "Year", "Month", or "Day"',
	'not_a_method'									=>	'The method called does not exist',
	'required_params_missing'						=>	'Some required parameters have been omitted.',
	
	//Payment Methods
	'authorize_payment_success'						=>	'The authorization was successful.',
	'authorize_payment_local_failure'				=>	'The authorization was not sent to the payment gateway because it failed validation.',	
	'authorize_payment_gateway_failure'				=>	'The authorization was declined by the payment gateway.',	
	'oneoff_payment_success'						=>	'The payment was successful.',
	'oneoff_payment_local_failure'					=>	'The payment could not be sent to the payment gateway because it failed local validation.',
	'oneoff_payment_gateway_failure'				=>	'The payment was declined by the payment gateway.',
	'capture_payment_success'						=>	'The payment capture was successful.',
	'capture_payment_local_failure'					=>	'The payment capture could not be sent to the payment gateway because it failed local validation.',
	'capture_payment_gateway_failure'				=>	'The payment capture was declined by the gateway.',
	'void_payment_success'							=>	'The payment was voided successfully.',
	'void_payment_local_failure'					=>	'The void request could not be sent to the payment gateway because it failed local validation.',
	'void_payment_gateway_failure'					=>	'The void request was rejected by the payment gateway.',
	'get_transaction_details_success'				=>	'Transaction details returned successfully.',
	'get_transaction_details_local_failure'			=>	'Transaction details were not requested from the payment gateway because local validation failed.',
	'get_transaction_details_gateway_failure'		=>	'Transaction details could not be retrieved by the payment gateway.',
	'change_transaction_status_success'				=>	'Transaction status was changed successfully.',
	'change_transaction_status_local_failure'		=>	'Transaction status could not be requested from the payment gateway because local validation failed.',	
	'change_transaction_status_gateway_failure'		=>	'Transaction status could not retrieved from the payment gateway.',
	'refund_payment_success'						=>	'Refund has been made.',
	'refund_payment_local_failure'					=>	'Refund request could not be sent to the payment gateway because local validation failed.',
	'refund_payment_gateway_failure'				=>	'Refund request was declined by the payment gateway.',	
	'search_transactions_success'					=>	'Transaction information successfully retrieved.',
	'search_transactions_local_failure'				=>	'Transaction search request could not be sent to the payment gateway because local validation failed',
	'search_transactions_gateway_failure'			=>	'Transaction search failed.',	
	'recurring_payment_success'						=>	'Recurring payments successfully initiated.',
	'recurring_payment_local_failure'				=>	'Recurring payment request could not be sent to the payment gateway because local validation failed.',	
	'recurring_payment_gateway_failure'				=>	'Recurring payment was declined by the payment gateway.',		
	'get_recurring_profile_success'					=>	'Recurring profile successfully retrieved.',
	'get_recurring_profile_local_failure'			=>	'Recurring profile could not be retrieved from the payment gateway because local validation failed.',
	'get_recurring_profile_gateway_failure'			=>	'Recurring profile could not be retrieved by the payment gateway.',		
	'suspend_recurring_profile_success'				=>	'Recurring profile successfully suspended.',
	'suspend_recurring_profile_local_failure'		=>	'Recurring profile suspension request could not be sent to the payment gateway because local validation failed.',
	'suspend_recurring_profile_gateway_failure'		=>	'Recurring profile could not be suspended by the payment gateway.',		
	'activate_recurring_profile_success'			=>	'Recurring profile successfully activated.',
	'activate_recurring_profile_local_failure'		=>	'Recurring profile activation request could not be sent to the payment gateway because local validation failed.',
	'activate_recurring_profile_gateway_failure'	=>	'Recurring profile could not be activated by the payment gateway.',		
	'cancel_recurring_profile_success'				=>	'Recurring profile cancelled successfully.',
	'cancel_recurring_profile_local_failure'		=>	'Recurring profile cancellation request could not be sent to the payment gateway because local validation failed.',
	'cancel_recurring_profile_gateway_failure'		=>	'Recurring profile could not be cancelled by the payment gateway.',		
	'recurring_bill_outstanding_success'			=>	'Outstanding bill amount successfully billed.',
	'recurring_bill_outstanding_local_failure'		=>	'Outstanding bill request could not be sent to the payment gateway because local validation failed.',
	'recurring_bill_outstanding_gateway_failure'	=>	'Outstanding bill request was rejected by the payment gateway.',		
	'update_recurring_profile_success'				=>	'Recurring profile updated successfully.',
	'update_recurring_profile_local_failure'		=>	'Recurring profile update request could not be sent to the payment gateway because local validation failed.',
	'update_recurring_profile_gateway_failure'		=>	'Recurring profile update was rejected by the payment gateway.',		
);

/**
 * Response Details
 * 
 * Additional details to help in debugging
*/
$config['response_details'] = array (
	'invalid_date_format'		=>	'Dates must be provided in MMYYYY format.',
	'missing_ip_address'		=>	'IP address is required but was not provided in the request',
	'missing_cc_type'			=>	'Credit Card Type is required but was not provided in the request',
	'missing_cc_number'			=>	'Credit Card Number is required but was not provided in the request',
	'missing_cc_details'		=>	'Full Credit Card details must be provided.',  
	'missing_cc_exp'			=>	'Credit Card Expiration is required but was not provided in the request',
	'missing_email'				=>	'Email is required but was not provided in the request',
	'missing_street'			=>	'Street is required but was not provided in the request',
	'missing_city'				=>	'City is required but was not provided in the request',
	'missing_state'				=>	'State is required but was not provided in the request',
	'missing_countrycode'		=>	'Country is required but was not provided in the request',
	'missing_postal_code'		=>	'Postal code is required but was not provided in the request',
	'missing_amt'				=>	'Amount is required but was not provided in the request',
	'missing_identifier'		=>	'An identifier (such as a previous transaction ID) is required but was not provided in the request',
	'missing_action'			=>	'An action to be taken by the payment gateway is required but was not provided in the request',
	'missing_refund_type'		=>	'The type of refund you are issuing is required but was not provided in the request',
	'missing_start_date'		=>	'Start date is required but was not provided in the request',
	'missing_profile_start_date'=>	'Profile start date is required but was not provided in the request',
	'missing_billing_period'	=>	'Billing period is required but was not provided in the request',
	'missing_billing_frequency'	=>	'Billing frequency is required but was not provided in the request',
);
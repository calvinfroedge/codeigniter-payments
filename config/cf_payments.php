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
	'not_a_method'									=>	'The method called does not exist',
	
	//Payment Methods
	'authorize_payment_success'						=>	'The authorization was successful.',
	'authorize_payment_local_failure'				=>	'The authorization was not sent to the payment gateway because it failed validation.',	
	'authorize_payment_gateway_failure'				=>	'The authorization was declined by the payment gateway.',	
	'oneoff_payment_success'						=>	'The payment was successful.',
	'oneoff_payment_local_failure'					=>	'011',
	'oneoff_payment_gateway_failure'				=>	'',
	'capture_payment_success'						=>	'100',
	'capture_payment_local_failure'					=>	'011',
	'capture_payment_gateway_failure'				=>	'011',
	'void_payment_success'							=>	'',
	'void_payment_local_failure'					=>	'',
	'void_payment_gateway_failure'					=>	'',
	'get_transaction_details_success'				=>	'',
	'get_transaction_details_local_failure'			=>	'',
	'get_transaction_details_gateway_failure'		=>	'',
	'change_transaction_status_success'				=>	'',
	'change_transaction_status_local_failure'		=>	'',	
	'change_transaction_status_gateway_failure'		=>	'',
	'refund_payment_success'						=>	'',
	'refund_payment_local_failure'					=>	'',
	'refund_payment_gateway_failure'				=>	'',	
	'search_transactions_success'					=>	'',
	'search_transactions_local_failure'				=>	'',
	'search_transactions_gateway_failure'			=>	'',	
	'recurring_payment_success'						=>	'',
	'recurring_payment_local_failure'				=>	'',	
	'recurring_payment_gateway_failure'				=>	'',		
	'get_recurring_profile_success'					=>	'',
	'get_recurring_profile_local_failure'			=>	'',
	'get_recurring_profile_gateway_failure'			=>	'',		
	'suspend_recurring_profile_success'				=>	'',
	'suspend_recurring_profile_local_failure'		=>	'',
	'suspend_recurring_profile_gateway_failure'		=>	'',		
	'activate_recurring_profile_success'			=>	'',
	'activate_recurring_profile_local_failure'		=>	'',
	'activate_recurring_profile_gateway_failure'	=>	'',		
	'cancel_recurring_profile_success'				=>	'',
	'cancel_recurring_profile_local_failure'		=>	'',
	'cancel_recurring_profile_gateway_failure'		=>	'',		
	'recurring_bill_outstanding_success'			=>	'',
	'recurring_bill_outstanding_local_failure'		=>	'',
	'recurring_bill_outstanding_gateway_failure'	=>	'',		
	'update_recurring_profile_success'				=>	'',
	'update_recurring_profile_local_failure'		=>	'',
	'update_recurring_profile_gateway_failure'		=>	'',		
);

/**
 * Response Details
 * 
 * Additional details to help in debugging
*/
$config['response_details'] = array (
	'invalid_date_format'		=> 'Dates must be provided in MMYYYY format.',
);
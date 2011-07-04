<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class PayPal_PaymentsPro extends CF_Payments
{	
	/**
	 * The use who wil make the call to the paypal gateway
	*/
	private $_api_user;

	/**
	 * The password for API user
	*/
	private $_pwd;	

	/**
	 * The version of the API to use
	*/	
	private $_api_version;

	/**
	 * The API signature to use
	*/	
	private $_api_signature;

	/**
	 * A description to use for the transaction
	*/
	private $_transaction_description;

	/**
	 * The API method currently being utilized
	*/
	private $_api_method;		

	/**
	 * The API method currently being utilized
	*/
	private $_api_endpoint;	

	/**
	 * An array for storing all settings
	*/	
	private $_settings = array();

	/**
	 * An array for storing all request data
	*/	
	private $_request = array();	

	/**
	 * The final string to be sent in the http query
	*/	
	private $_http_query;		

	/**
	 * Constructor method
	*/		
	public function __construct()
	{
		parent::__construct();	
		$this->_ci->load->config('payment_modules/paypal_paymentspro');
		$this->_api_endpoint = $this->_ci->config->item('paypal_api_endpoint');		
		$this->_api_settings = array(
			'USER'	=> $this->_ci->config->item('paypal_api_username'),
			'PWD'	=> $this->_ci->config->item('paypal_api_password'),
			'VERSION' => $this->_ci->config->item('paypal_api_version'),
			'SIGNATURE'	=> $this->_ci->config->item('paypal_api_signature'),		
		);
	}

	/**
	 * Search transactions for something specific
	 * @param	string	Search params to use
	 * @return	object	The response from the payment gateway
	 *
	 * Full method details found at https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_TransactionSearch
	*/	
	public function search_transactions($params)
	{
		$this->_api_method = array('METHOD' => 'GetTransactionDetails');
		$this->_api_settings = array_merge($this->_api_method, $this->_api_settings);
		$this->_api_settings = array_merge($this->_api_settings, $this->_ci->config->item('paypal_direct_payment'));	
		
		$this->_request = array(
			'STARTDATE'			=>	$params[''], //Required.  Earliest transaction at which to start the search.  No wildcards.  Value must be UTC / GMT format.
			'ENDDATE'			=>	$params[''], //Optional.  Latest transaction to be included.
			'EMAIL'				=>	$params[''], //Optional.  Search by buyer's email address.	
			'RECEIVER'			=>	$params[''], //Optional.  Seach by receiver's email address.
			'RECEIPTID'			=>	$params[''], //Optional.  Search by the PayPal account optional receipt id.
			'TRANSACTIONID'		=>	$params[''], //Optional.  Search by the transaction ID.
			'INVNUM'			=>	$params[''], //Optional.  Search by invoice number, as you previously submitted.
			'ACCT'				=>	$params[''], //Optional.  Search by credit card number.  NO wildcards.
			'AUCTIONITEMNUMBER'	=>	$params[''], //Optional.  Search by auction item number.
			'TRANSACTIONCLASS'	=>	$params[''], //Optional.  Many different types of classes, listed on API page. 
			'AMT'				=>	$params[''], //Optional.  Search by amount.
			'CURRENCYCODE'		=>	$params[''], //Optional.  Search by currency code.
			'STATUS'			=>	$params[''], //Optional.  Search by transaction status.
			'SALUTATION'		=>	$params[''], //Optional.  Search by Salutation.
			'FIRSTNAME'			=>	$params[''], //Optional.  Search by First Name.
			'MIDDLENAME'		=>	$params[''], //Optional.  Search by Middle Name.
			'LASTNAME'			=>	$params[''], //Optional.  Search by Last Name.
			'SUFFIX'			=>	$params[''], //Optional.  Search by Suffix.
		);
		
		return $this->_handle_query();	
	}
	
	/**
	 * Get a transaction's details
	 * @param	string	An array that contains your identifier
	 * @return	object	The response from the payment gateway
	*/	
	public function get_transaction_details($params)
	{
		$this->_api_method = array('METHOD' => 'GetTransactionDetails');
		$this->_api_settings = array_merge($this->_api_method, $this->_api_settings);
		$this->_api_settings = array_merge($this->_api_settings, $this->_ci->config->item('paypal_direct_payment'));	
		
		$this->_request = array(
			'TRANSACTIONID'	=>	$params['other_data']['identifier']
		);
				
		return $this->_handle_query();		
	}	
	
	/**
	 * Authorize a oneoff payment
	 * @param	array	An array of payment params, sent from your controller / library
	 * @return	object	The response from the payment gateway
	*/	
	public function authorize_payment($params)
	{
		$this->_api_method = array('METHOD' => 'DoDirectPayment');
		$this->_api_settings = array_merge($this->_api_method, $this->_api_settings);
		$this->_api_settings = array_merge($this->_api_settings, $this->_ci->config->item('paypal_direct_payment'));
		
		$this->_build_oneoff_request($params, 'Authorization');
				
		return $this->_handle_query();	
	}
	
	/**
	 * Capture a oneoff payment
	 * @param	array	An array of payment params, sent from your controller / library
	 * @return	object	The response from the payment gateway
	*/	
	public function capture_payment($params)
	{
		$this->_api_method = array('METHOD' => 'DoDirectPayment');
		$this->_api_settings = array_merge($this->_api_method, $this->_api_settings);
		$this->_api_settings = array_merge($this->_api_settings, $this->_ci->config->item('paypal_direct_payment'));
		
		($params['other_data']['final'] == TRUE)
		? $final = 'Complete'
		: $final = 'NotComplete';
		
		$this->_request = array(
			'AUTHORIZATIONID'	=>	$params['other_data']['identifier'],
			'AMT'			=>	$params['other_data']['capture_amount'],
			'COMPLETETYPE'		=>	$final,
			'INVOICEID'			=>	$params['other_data']['inv_num'],
			'NOTE'				=>	$params['other_data']['note'],
			'SOFTDESCRIPTOR'	=>	$params['other_data']['cc_statement_descrip'],
			'CREDITCARDTYPE'	=>	$params['billing_data']['cc_type'],
			'ACCT'				=> 	$params['billing_data']['cc_number'],
			'EXPDATE'			=> 	$params['billing_data']['cc_exp'],
		);
				
		return $this->_handle_query();	
	}

	/**
	 * Void a oneoff payment
	 * @param	array	An array of params, sent from your controller / library
	 * @return	object	The response from the payment gateway
	*/	
	public function void_payment($params)
	{
		$this->_api_method = array('METHOD' => 'DoVoid');
		$this->_api_settings = array_merge($this->_api_method, $this->_api_settings);
		$this->_api_settings = array_merge($this->_api_settings, $this->_ci->config->item('paypal_direct_payment'));
		
		$this->_request = array(
			'AUTHORIZATIONID'	=>	$params['other_data']['identifier'],
			'NOTE'			=>	$params['other_data']['note']
		);
				
		return $this->_handle_query();	
	}	
		
	/**
	 * Make a oneoff payment
	 * @param	array	An array of payment params, sent from your controller / library
	 * @return	object	The response from the payment gateway
	*/	
	public function oneoff_payment($params)
	{
		$this->_api_method = array('METHOD' => 'DoDirectPayment');
		$this->_api_settings = array_merge($this->_api_method, $this->_api_settings);
		$this->_api_settings = array_merge($this->_api_settings, $this->_ci->config->item('paypal_direct_payment'));
		
		$this->_build_oneoff_request($params, 'Sale');
				
		return $this->_handle_query();
	}

	/**
	 * Get a transaction's details
	 * @param	array	An array that contains your identifier
	 * @return	object	The response from the payment gateway
	*/	
	public function change_transaction_status($params)
	{
		$this->_api_method = array('METHOD' => 'ManagePendingTransactionStatus');
		$this->_api_settings = array_merge($this->_api_method, $this->_api_settings);
		$this->_api_settings = array_merge($this->_api_settings, $this->_ci->config->item('paypal_direct_payment'));	
		
		$this->_request = array(
			'TRANSACTIONID'	=>	$params['other_data']['identifier'],
			'ACTION'	=>	$params['other_data']['action']
		);
				
		return $this->_handle_query();		
	}

	/**
	 * Refund a transaction
	 * @param	array	An array that contains your identifier
	 * @return	object	The response from the payment gateway
	*/	
	public function refund_payment($params)
	{
		$this->_api_method = array('METHOD' => 'RefundTransaction');
		$this->_api_settings = array_merge($this->_api_method, $this->_api_settings);
		$this->_api_settings = array_merge($this->_api_settings, $this->_ci->config->item('paypal_direct_payment'));	
		
		$this->_request = array(
			'TRANSACTIONID'	=>	$params['identifier'],	//Required.  Should have been returned by previous transaction.
			'REFUNDTYPE'	=>	$params['refund_type'],	//Can be full or partial
			'AMT'			=>	$params['amt'],	//The amount for refund.  Should be empty if REFUNDTYPE is full
			'CURRENCYCODE'	=>	$params['currency_code'],	//Currency code you wish to use.  Required for partial refunds, do not use for full refunds.
			'INVOICEID'		=>  $params['inv_num'],	//An optional invoice number from your own tracking system.
			'NOTE'			=>	$params['note']	//A note to go along with the refund
		);
				
		return $this->_handle_query();		
	}	
	
	/**
	 * Build requests for making oneoff payments
	 * @param	array	An array of payment param
	 * @param	string	Should be set to 'Sale' or 'Authorization'
	 * @return	void
	*/	
	private function _build_oneoff_request($params, $payment_action)
	{
		$payment_array = array(
			'PAYMENTACTION'		=>	$payment_action,
			'IPADDRESS'			=>	$params['billing_data']['ip_address'],
			'CREDITCARDTYPE'	=>	$params['billing_data']['cc_type'],
			'ACCT'				=> 	$params['billing_data']['cc_number'],
			'EXPDATE'			=> 	$params['billing_data']['cc_exp'],
			'CVV2'				=> 	$params['billing_data']['cc_code'],
			'EMAIL'				=> 	$params['billing_data']['email'],
			'FIRSTNAME'			=> 	$params['billing_data']['first_name'],
			'LASTNAME'			=> 	$params['billing_data']['last_name'],
			'STREET'			=> 	$params['billing_data']['street'],
			'STREET2'			=> 	$params['billing_data']['street2'],
			'CITY'				=> 	$params['billing_data']['city'],
			'STATE'				=> 	$params['billing_data']['state'],
			'COUNTRYCODE'		=> 	$params['billing_data']['countrycode'],
			'ZIP'				=> 	$params['billing_data']['zip'],
			'AMT'				=> 	$params['billing_data']['amt'],
			'SHIPTOPHONENUM'	=> 	$params['other_data']['ship_to_phone_num'],
			'CURRENCYCODE'		=> 	$params['other_data']['currency_code'],
			'ITEMAMT'			=> 	$params['other_data']['item_amt'],
			'SHIPPINGAMT'		=> 	$params['other_data']['shipping_amt'],
			'INSURANCEAMT'		=> 	$params['other_data']['insurance_amt'],
			'SHIPDISCAMT'		=> 	$params['other_data']['shipping_disc_amt'],
			'HANDLINGAMT'		=> 	$params['other_data']['handling_amt'],
			'TAXAMT'			=> 	$params['other_data']['tax_amt'],
			'DESC'				=> 	$params['other_data']['desc'],
			'CUSTOM'			=> 	$params['other_data']['custom'],
			'INVNUM'			=> 	$params['other_data']['inv_num'],
			'BUTTONSOURCE'		=> 	$params['other_data']['button_source'],
			'NOTIFYURL'			=> 	$params['other_data']['notify_url']		
		);
		
		$this->_request = $payment_array;
	}	
	/**
	 * Get profile info for a particular profile id
	 *
	 * @param	array
	 * @return	object
	 */		
	public function get_recurring_profile_info($profile_id)
	{
		$function_settings = array(
		'DESC'	=> $this->_ci->config->item('paypal_api_service_description'),
		'METHOD'	=> 'GetRecurringPaymentsProfileDetails'
		);
		$data = array(
			'ProfileID' => $profile_id
		);
		$return_data = $this->_handle_query(array_merge($this->settings, $function_settings), $data, $this->endpoint);
		
		$return_array = array(
			'profile_id' => $return_data->response['PROFILEID'],
			'status' => $return_data->response['STATUS'],
			'next_billing_date' => $return_data->response['NEXTBILLINGDATE'],
			'amount' => $return_data->response['AMT'],
			'billing_period' => $return_data->response['BILLINGPERIOD'],
			'billing_frequency' => $return_data->response['BILLINGFREQUENCY'],
			'failedpayments'	=> $return_data->response['FAILEDPAYMENTCOUNT'],
			'billing_method' => $this->_ci->config->item('payment-system_paypal'),
			'billing_type' => $this->_ci->config->item('recurring_payment-type')
		);
		
		return (object) $return_array;
	}

	/**
	 * Create a new recurring payment
	 *
	 * @param	array
	 * @return	object
	 */		
	public function make_recurring_payment($billing_data, $trial = FALSE)
	{
		$billing_keys = array(
			'CREDITCARDTYPE',
			'ACCT',
			'EXPDATE',
			'FIRSTNAME',
			'LASTNAME',
			'PROFILESTARTDATE',
			'BILLINGPERIOD',
			'BILLINGFREQUENCY',
			'AMT',
			'MAXFAILEDPAYMENTS'
		);
		
		if($trial)
		{
			$billing_keys = array(
				'CREDITCARDTYPE',
				'ACCT',
				'EXPDATE',
				'FIRSTNAME',
				'LASTNAME',
				'PROFILESTARTDATE',
				'BILLINGPERIOD',
				'BILLINGFREQUENCY',
				'AMT',
				'MAXFAILEDPAYMENTS',
				'TRIALBILLINGPERIOD',
				'TRIALBILLINGFREQUENCY',
				'TRIALAMT',
				'TRIALTOTALBILLINGCYCLES'
			);			
		}
		
		$function_settings = array(
		'DESC'	=> $this->_ci->config->item('paypal_api_service_description'),
		'METHOD'	=> 'CreateRecurringPaymentsProfile'
		);
		
		return $this->_handle_query(array_merge($this->settings, $function_settings), array_combine($billing_keys, $billing_data), $this->endpoint);
	}

	/**
	 * Update an existing payments subscription
	 *
	 * @param	array
	 * @return	object
	 */		
	public function update_billing_info($billing_data)
	{
		$billing_keys = array(
			'PROFILEID',
			'CREDITCARDTYPE',
			'ACCT',
			'EXPDATE',
			'FIRSTNAME',
			'LASTNAME',
			'PROFILESTARTDATE',
			'BILLINGPERIOD',
			'BILLINGFREQUENCY',
			'AMT'
		);
		
		$function_settings = array(
		'DESC'	=> $this->_ci->config->item('paypal_api_service_description'),
		'METHOD'	=> 'UpdateRecurringPaymentsProfile'
		);
		
		return $this->_handle_query(array_merge($this->settings, $function_settings), array_combine($billing_keys, $billing_data), $this->endpoint);
	}

	/**
	 * Cancel a recurring subscription
	 *
	 * @param	array
	 * @return	object
	 */		
	public function cancel_subscription($profile_id)
	{
		$request_params = array(
			'PROFILEID',
			'ACTION'
		);
		
		$request_values = array(
			$profile_id,
			'Cancel'
		);
		
		$function_settings = array(
		'METHOD'	=> 'ManageRecurringPaymentsProfileStatus'
		);
		
		return $this->_handle_query(array_merge($this->settings, $function_settings), array_combine($request_params, $request_values), $this->endpoint);
	}

	/**
	 * Suspend a subscription
	 *
	 * @param	string
	 * @return	object
	 */		
	public function suspend_subscription($profile_id)
	{
		$request_params = array(
			'PROFILEID',
			'ACTION'
		);
		
		$request_values = array(
			$profile_id,
			'Suspend'
		);
		
		$function_settings = array(
		'METHOD'	=> 'ManageRecurringPaymentsProfileStatus'
		);
		
		return $this->_handle_query(array_merge($this->settings, $function_settings), array_combine($request_params, $request_values), $this->endpoint);
	}

	/**
	 * Activate a subscription
	 *
	 * @param	int
	 * @return	object
	 */		
	public function activate_subscription($profile_id)
	{
		$request_params = array(
			'PROFILEID',
			'ACTION'
		);
		
		$request_values = array(
			$profile_id,
			'Reactivate'
		);
		
		$function_settings = array(
		'METHOD'	=> 'ManageRecurringPaymentsProfileStatus'
		);
		
		return $this->_handle_query(array_merge($this->settings, $function_settings), array_combine($request_params, $request_values), $this->endpoint);
	}

	/**
	 * Build the query for the response and call the request function
	 *
	 * @param	array
	 * @param	array
	 * @param	string
	 * @return	array
	 */		
	private function _handle_query()
	{
		$this->_request = $this->filter_values($this->_request);
		$this->_request = http_build_query(array_merge($this->_api_settings, $this->_request));
		$this->_http_query = $this->_api_endpoint.$this->_request;
		
		return $this->_parse_response($this->_make_request());
	}
	
	/**
	 * Make a new request to PayPal API
	 *
	 * @param	array
	 * @return	array
	 */		
	private function _make_request()
	{
		// create a new cURL resource
		$curl = curl_init();
		
		// set URL
		curl_setopt($curl, CURLOPT_URL, $this->_http_query);
		
		// set to return the data as a string
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		
		// Run the query and get a response
		$response = curl_exec($curl);
		
		// close cURL resource, and free up system resources
		curl_close($curl);
		
		// Return the response
	
		return $response;	
	}

	/**
	 * Parse the response from the server
	 *
	 * @param	array
	 * @return	object
	 */		
	private function _parse_response($response)
	{
		
		$results = explode('&',urldecode($response));
		foreach($results as $result)
		{
			list($key, $value) = explode('=', $result);
			$response_array[$key]=$value;
		}
		
		$return_object = array();
		
		//Set the response status
		
		($response_array['ACK'] == 'Success') 
		? $success = TRUE 
		: $failure = TRUE ;

		if(isset($failure))
		{	
			$return_object = array('status'=>'failure', 'response'=>$response_array);
		}
		if(isset($success))
		{	
			$return_object = array('status'=>'success', 'response'=>$response_array);
		}
		
		return (object) $response_array;		
	}
	
}
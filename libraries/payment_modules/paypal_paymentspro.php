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
	 * The default params for this api
	*/	
	private	$_default_params;

	/**
	 * Constructor method
	*/		
	public function __construct()
	{
		parent::__construct();	
		$this->_ci->load->config('payment_modules/paypal_paymentspro');
		$this->_default_params = $this->_ci->config->item('method_params');
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
		$this->_api_method = array('METHOD' => 'TransactionSearch');
		$this->_api_settings = array_merge($this->_api_method, $this->_api_settings);
	

		$this->_request = array(
			'STARTDATE'			=>	$params['start_date'], //Required.  Earliest transaction at which to start the search.  No wildcards.  Value must be UTC / GMT format.
			'ENDDATE'			=>	$params['end_date'], //Optional.  Latest transaction to be included.
			'EMAIL'				=>	$params['email'], //Optional.  Search by buyer's email address.	
			'RECEIVER'			=>	$params['receiver'], //Optional.  Seach by receiver's email address.
			'RECEIPTID'			=>	$params['receipt_id'], //Optional.  Search by the PayPal account optional receipt id.
			'TRANSACTIONID'		=>	$params['transaction_id'], //Optional.  Search by the transaction ID.
			'INVNUM'			=>	$params['inv_num'], //Optional.  Search by invoice number, as you previously submitted.
			'ACCT'				=>	$params['cc_number'], //Optional.  Search by credit card number.  NO wildcards.
			'AUCTIONITEMNUMBER'	=>	$params['auction_item_number'], //Optional.  Search by auction item number.
			'TRANSACTIONCLASS'	=>	$params['transaction_class'], //Optional.  Many different types of classes, listed on API page. 
			'AMT'				=>	$params['amt'], //Optional.  Search by amount.
			'CURRENCYCODE'		=>	$params['currency_code'], //Optional.  Search by currency code.
			'STATUS'			=>	$params['status'], //Optional.  Search by transaction status.
			'SALUTATION'		=>	$params['salutation'], //Optional.  Search by Salutation.
			'FIRSTNAME'			=>	$params['first_name'], //Optional.  Search by First Name.
			'MIDDLENAME'		=>	$params['middle_name'], //Optional.  Search by Middle Name.
			'LASTNAME'			=>	$params['last_name'], //Optional.  Search by Last Name.
			'SUFFIX'			=>	$params['suffix'], //Optional.  Search by Suffix.
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
	
		
		$this->_request = array(
			'TRANSACTIONID'	=>	$params['transaction_data']['identifier']
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

		
		($params['transaction_data']['final'] == TRUE)
		? $final = 'Complete'
		: $final = 'NotComplete';
		
		$this->_request = array(
			'AUTHORIZATIONID'	=>	$params['transaction_data']['identifier'],
			'AMT'			=>	$params['transaction_data']['capture_amount'],
			'COMPLETETYPE'		=>	$final,
			'INVOICEID'			=>	$params['transaction_data']['inv_num'],
			'NOTE'				=>	$params['transaction_data']['note'],
			'SOFTDESCRIPTOR'	=>	$params['transaction_data']['cc_statement_descrip'],
			'CREDITCARDTYPE'	=>	$params['cc_type'],
			'ACCT'				=> 	$params['cc_number'],
			'EXPDATE'			=> 	$params['cc_exp'],
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

		
		$this->_request = array(
			'AUTHORIZATIONID'	=>	$params['transaction_data']['identifier'],
			'NOTE'			=>	$params['transaction_data']['note']
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
		
		$params = array_merge($this->_default_params['oneoff_payment'], $params);
		
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
	
		
		$this->_request = array(
			'TRANSACTIONID'	=>	$params['transaction_data']['identifier'],
			'ACTION'	=>	$params['transaction_data']['action']
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
			'IPADDRESS'			=>	$params['ip_address'],
			'CREDITCARDTYPE'	=>	$params['cc_type'],
			'ACCT'				=> 	$params['cc_number'],
			'EXPDATE'			=> 	$params['cc_exp'],
			'CVV2'				=> 	$params['cc_code'],
			'EMAIL'				=> 	$params['email'],
			'FIRSTNAME'			=> 	$params['first_name'],
			'LASTNAME'			=> 	$params['last_name'],
			'STREET'			=> 	$params['street'],
			'STREET2'			=> 	$params['street2'],
			'CITY'				=> 	$params['city'],
			'STATE'				=> 	$params['state'],
			'COUNTRYCODE'		=> 	$params['countrycode'],
			'ZIP'				=> 	$params['zip'],
			'AMT'				=> 	$params['amt'],
			'SHIPTOPHONENUM'	=> 	$params['ship_to_phone_num'],
			'CURRENCYCODE'		=> 	$params['currency_code'],
			'ITEMAMT'			=> 	$params['item_amt'],
			'SHIPPINGAMT'		=> 	$params['shipping_amt'],
			'INSURANCEAMT'		=> 	$params['insurance_amt'],
			'SHIPDISCAMT'		=> 	$params['shipping_disc_amt'],
			'HANDLINGAMT'		=> 	$params['handling_amt'],
			'TAXAMT'			=> 	$params['tax_amt'],
			'DESC'				=> 	$params['desc'],
			'CUSTOM'			=> 	$params['custom'],
			'INVNUM'			=> 	$params['inv_num'],
			'BUTTONSOURCE'		=> 	$params['button_source'],
			'NOTIFYURL'			=> 	$params['notify_url']		
		);
		
		$this->_request = $payment_array;
	}
		
	/**
	 * Get profile info for a particular profile id
	 *
	 * @param	array
	 * @return	object
	 */		
	public function get_recurring_profile($params)
	{
		$this->_api_method = array('METHOD'	=> 'GetRecurringPaymentsProfileDetails');
		$this->_api_settings = array_merge($this->_api_method, $this->_api_settings);

		
		$this->_request = array(
			'PROFILEID'	=>	$params['identifier']
		);
		
		return $this->_handle_query();
	}

	/**
	 * Create a new recurring payment
	 *
	 * @param	array
	 * @return	object
	 *
	 * Full documentation for this API found at https://cms.paypal.com/es/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_CreateRecurringPayments
	 */		
	public function recurring_payment($params)
	{
		$this->_api_method = array('METHOD' => 'CreateRecurringPaymentsProfile');
		$this->_api_settings = array_merge($this->_api_method, $this->_api_settings);

			

		$this->_request = array(
			'SUBSCRIBERNAME'		=>	$params['subscriber_name'],
			'PROFILESTARTDATE'		=>	$params['profile_start_date'],
			'PROFILEREFERENCE'		=>	$params['profile_reference'],
			'DESC'					=>	$params['desc'],
			'MAXFAILEDPAYMENTS'		=>	$params['max_failed_payments'],
			'AUTOBILLAMT'			=>  $params['auto_bill_amt'],
			'BILLINGPERIOD'			=>	$params['billing_period'],			
			'BILLINGFREQUENCY'		=>	$params['billing_frequency'],
			'TOTALBILLINGCYCLES'	=>	$params['total_billing_cycles'],
			'AMT'					=>	$params['amt'],
			'CURRENCYCODE'			=>	$params['currency_code'],
			'SHIPPINGAMT'			=>	$params['shipping_amt'],
			'TAXAMT'				=>	$params['tax_amt'],	
			'INITAMT'				=>	$params['initial_amt'],
			'FAILEDINITAMTACTION'	=>	$params['failed_init_action'],
			'SHIPTONAME'			=>	$params['ship_to_name'],
			'SHIPTOSTREET'			=>	$params['ship_to_street'],
			'SHIPTOSTREET2'			=>	$params['ship_to_street2'],
			'SHIPTOCITY'			=>	$params['ship_to_city'],
			'SHIPTOSTATE'			=>	$params['ship_to_state'],
			'SHIPTOZIP'				=>	$params['ship_to_zip'],
			'SHIPTOCOUNTRY'			=>	$params['ship_to_country'],
			'SHIPTOPHONENUM'		=>	$params['ship_to_phone_num'],									
			'CREDITCARDTYPE'		=>	$params['cc_type'],
			'ACCT'					=>	$params['cc_number'],	
			'EXPDATE'				=>	$params['exp_date'],
			'CVV2'					=>	$params['cc_code'],
			'STARTDATE'				=>	$params['start_date'],
			'ISSUENUMBER'			=>	$params['issue_number'],
			'EMAIL'					=>	$params['email'],
			'PAYERID'				=>	$params['identifier'],
			'PAYERSTATUS'			=>	$params['payer_status'],
			'COUNTRYCODE'			=>	$params['country_code'],
			'BUSINESS'				=>	$params['business_name'],
			'SALUTATION'			=>	$params['salutation'],
			'FIRSTNAME'				=>	$params['first_name'],
			'MIDDLENAME'			=>	$params['middle_name'],
			'LASTNAME'				=>	$params['last_name'],
			'SUFFIX'				=>	$params['suffix'],
			'STREET'				=>	$params['street'],
			'STREET2'				=>	$params['street2'],
			'CITY'					=>	$params['city'],
			'STATE'					=>	$params['state'],
			'ZIP'					=>	$params['postal_code'],
			'SHIPTOPHONENUM'		=>	$params['ship_to_phone_num']
		);		

		if($params['trial'] === TRUE)
		{
			array_merge($this->_request,
				array(
				'TRIALBILLINGPERIOD'	=>	$params['trial_data']['billing_cycles'],
				'TRIALBILLINGFREQUENCY'	=>	$params['trial_data']['billing_frequency'],
				'TRIALAMT'				=>	$params['trial_data']['amt']
				)
			);
		}
		
		return $this->_handle_query();
	}

	/**
	 * Activate a recurring profile which was previously suspended
	 *
	 * @param	array
	 * @return	object
	 */		
	public function activate_recurring_profile($params)
	{
		$this->_api_method = array('METHOD'	=> 'ManageRecurringPaymentsProfileStatus');
		$this->_api_settings = array_merge($this->_api_method, $this->_api_settings);

		
		$this->_request = array(
			'PROFILEID'	=>	$params['identifier'],
			'ACTION'	=>	'Reactivate',
			'NOTE'		=>	$params['note']
		);
		
		return $this->_handle_query();
	}

	/**
	 * Suspend a recurring profile
	 *
	 * @param	array
	 * @return	object
	 */		
	public function suspend_recurring_profile($params)
	{
		$this->_api_method = array('METHOD'	=> 'ManageRecurringPaymentsProfileStatus');
		$this->_api_settings = array_merge($this->_api_method, $this->_api_settings);

		
		$this->_request = array(
			'PROFILEID'	=>	$params['identifier'],
			'ACTION'	=>	'Suspend',
			'NOTE'		=>	$params['note']
		);
		
		return $this->_handle_query();
	}

	/**
	 * Cancel a recurring profile
	 *
	 * @param	array
	 * @return	object
	 */		
	public function cancel_recurring_profile($params)
	{
		$this->_api_method = array('METHOD'	=> 'ManageRecurringPaymentsProfileStatus');
		$this->_api_settings = array_merge($this->_api_method, $this->_api_settings);

		
		$this->_request = array(
			'PROFILEID'	=>	$params['identifier'],
			'ACTION'	=>	'Cancel',
			'NOTE'		=>	$params['note']			
		);
		
		return $this->_handle_query();
	}

	/**
	 * Bill amount outstanding owed by a particular recurring billing customer
	 *
	 * @param	array
	 * @return	object
	 */		
	public function recurring_bill_outstanding($params)
	{
		$this->_api_method = array('METHOD'	=> 'BillOutstandingAmount');
		$this->_api_settings = array_merge($this->_api_method, $this->_api_settings);

		
		$this->_request = array(
			'PROFILEID'	=>	$params['identifier'],
			'AMT'	=>	$params['amt'],
			'NOTE'		=>	$params['note']			
		);
		
		return $this->_handle_query();
	}			

	/**
	 * Update a recurring payments profile
	 *
	 * @param	array
	 * @return	object
	 */		
	public function update_recurring_profile($params)
	{
		$this->_api_method = array('METHOD'	=> 'UpdateRecurringPaymentsProfile');
		$this->_api_settings = array_merge($this->_api_method, $this->_api_settings);

		
		$this->_request = array(
			'PROFILEID'					=>	$params['identifier'],
			'NOTE'						=>	$params['note'],
			'DESC'						=>	$params['desc'],
			'SUBSCRIBERNAME'			=>	$params['subscriber_name'],
			'PROFILEREFERENCE'			=>	$params['profile_reference'],
			'ADDITIONALBILLINGCYCLES'	=>	$params['additional_billing_cycles'],
			'AMT'						=>	$params['amt'],
			'SHIPPINGAMT'				=>	$params['shipping_amt'],
			'TAXAMT'					=>	$params['tax_amt'],
			'OUTSTANDINGAMT'			=>	$params['outstanding_amt'],
			'AUTOBILLOUTAMT'			=>	$params['auto_bill_amt'],
			'MAXFAILEDPAYMENTS'			=>	$params['max_failed_payments'],
			'PROFILESTARTDATE'			=>	$params['profile_start_date'],
			'SHIPTONAME'				=>	$params['ship_to_name'],
			'SHIPTOSTREET'				=>	$params['ship_to_street'],
			'SHIPTOSTREET2'				=>	$params['ship_to_street2'],
			'SHIPTOCITY'				=>	$params['ship_to_city'],
			'SHIPTOSTATE'				=>	$params['ship_to_state'],
			'SHIPTOZIP'					=>	$params['ship_to_zip'],
			'SHIPTOCOUNTRY'				=>	$params['ship_to_country'],	
			'SHIPTOPHONENUM'			=>	$params['ship_to_phone_num'],	
			'TOTALBILLINGCYCLES'		=>	$params['total_billing_cycles'],	
			'CURRENCYCODE'				=>	$params['currency_code'],	
			'SHIPPINGAMT'				=>	$params['shipping_amt'],	
			'TAXAMT'					=>	$params['tax_amt'],	
			'CREDITCARDTYPE'			=>	$params['cc_type'],	
			'ACCT'						=>	$params['cc_number'],	
			'EXPDATE'					=>	$params['exp_date'],	
			'CVV2'						=>	$params['cc_code'],	
			'STARTDATE'					=>	$params['start_date'],	
			'ISSUENUMBER'				=>	$params['issue_number'],	
			'EMAIL'						=>	$params['email'],
			'FIRSTNAME'					=>	$params['first_name'],
			'LASTNAME'					=>	$params['last_name'],
			'STREET'					=>	$params['street'],
			'STREET2'					=>	$params['street2'],
			'CITY'						=>	$params['city'],
			'STATE'						=>	$params['state'],
			'COUNTRYCODE'				=>	$params['country_code'],
			'ZIP'						=>	$params['postal_code'],	
		);
		
		if($params['trial'] === TRUE)
		{
			array_merge($this->_request,
				array(		
					'TRIALTOTALBILLINGCYCLES'	=>	$params['trial_total_billing_cycles'],
					'TRIALAMT'	=>	$params['trial_amt']
				)
			);
		}
		
		return $this->_handle_query();
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
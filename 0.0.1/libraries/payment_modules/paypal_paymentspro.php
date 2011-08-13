<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class PayPal_PaymentsPro
{	
	/**
	 *	The payments object
	*/
	public $payments;
	
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
	protected $_api_method;		

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
	public function __construct($cf_payments)
	{
		$this->payments = $cf_payments;
		$this->_api_endpoint = $this->payments->ci->config->item('api_endpoint');		
		$this->_api_settings = array(
			'USER'	=> $this->payments->ci->config->item('api_username'),
			'PWD'	=> $this->payments->ci->config->item('api_password'),
			'VERSION' => $this->payments->ci->config->item('api_version'),
			'SIGNATURE'	=> $this->payments->ci->config->item('api_signature'),		
		);
	}

	/**
	 * Make a oneoff payment
	 * @param	array	An array of payment params, sent from your controller / library
	 * @return	object	The response from the payment gateway
	*/	
	public function paypal_paymentspro_oneoff_payment($params)
	{
		$this->_api_method = array('METHOD' => 'DoDirectPayment');
		$this->_build_oneoff_request($params, 'Sale');	
		return $this->_handle_query();
	}
		
	/**
	 * Authorize a oneoff payment
	 * @param	array	An array of payment params, sent from your controller / library
	 * @return	object	The response from the payment gateway
	*/	
	public function paypal_paymentspro_authorize_payment($params)
	{
		$this->_api_method = array('METHOD' => 'DoDirectPayment');
		$this->_build_oneoff_request($params, 'Authorization');		
		return $this->_handle_query();	
	}
	
	/**
	 * Capture a oneoff payment
	 * @param	array	An array of payment params, sent from your controller / library
	 * @return	object	The response from the payment gateway
	*/	
	public function paypal_paymentspro_capture_payment($params)
	{
		$this->_api_method = array('METHOD' => 'DoDirectPayment');

		($params['final'] == TRUE)
		? $final = 'Complete'
		: $final = 'NotComplete';
		
		$this->_request = array(
			'AUTHORIZATIONID'	=>	$params['identifier'],
			'AMT'				=>	$params['amt'],
			'COMPLETETYPE'		=>	$final,
			'INVOICEID'			=>	$params['inv_num'],
			'NOTE'				=>	$params['note'],
			'SOFTDESCRIPTOR'	=>	$params['cc_statement_descrip'],
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
	public function paypal_paymentspro_void_payment($params)
	{
		$this->_api_method = array('METHOD' => 'DoVoid');
		
		$this->_request = array(
			'AUTHORIZATIONID'	=>	$params['identifier'],
			'NOTE'			=>	$params['note']
		);
				
		return $this->_handle_query();	
	}	

	/**
	 * Get a transaction's details
	 * @param	array	An array that contains your identifier
	 * @return	object	The response from the payment gateway
	*/	
	public function paypal_paymentspro_change_transaction_status($params)
	{
		$this->_api_method = array('METHOD' => 'ManagePendingTransactionStatus');
		
		$this->_request = array(
			'TRANSACTIONID'	=>	$params['identifier'],
			'ACTION'	=>	$params['action']
		);
				
		return $this->_handle_query();		
	}

	/**
	 * Refund a transaction
	 * @param	array	An array that contains your identifier
	 * @return	object	The response from the payment gateway
	*/	
	public function paypal_paymentspro_refund_payment($params)
	{
		$this->_api_method = array('METHOD' => 'RefundTransaction');
		
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
			'country'		=> 	$params['country'],
			'ZIP'				=> 	$params['postal_code'],
			'AMT'				=> 	$params['amt'],
			'SHIPTOPHONENUM'	=> 	$params['phone'],
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
			'NOTIFYURL'			=> 	$params['notify_url'],
			'SHIPTONAME'		=>	$params['ship_to_first_name'].' '.$params['ship_to_last_name'],
			'SHIPTOSTREET'		=>	$params['ship_to_street'],
			'SHIPTOCITY'		=>	$params['ship_to_city'],
			'SHIPTOSTATE'		=>	$params['ship_to_state'],
			'SHIPTOZIP'			=>	$params['ship_to_postal_code'],
			'SHIPTOCOUNTRY'		=>	$params['ship_to_country'],	
			'SHIPTOPHONENUM'	=>	$params['phone'],	
		);
		
		$this->_request = $payment_array;
	}

	/**
	 * Get a transaction's details
	 * @param	string	An array that contains your identifier
	 * @return	object	The response from the payment gateway
	*/	
	public function paypal_paymentspro_get_transaction_details($params)
	{
		$this->_api_method = array('METHOD' => 'GetTransactionDetails');
		
		$this->_request = array(
			'TRANSACTIONID'	=>	$params['identifier']
		);
				
		return $this->_handle_query();		
	}
	
	/**
	 * Search transactions for something specific
	 * @param	string	Search params to use
	 * @return	object	The response from the payment gateway
	 *
	 * Full method details found at https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_TransactionSearch
	*/	
	public function paypal_paymentspro_search_transactions($params)
	{
		$this->_api_method = array('METHOD' => 'TransactionSearch');
			
		$this->_request = array(
			'STARTDATE'			=>	$params['start_date'],   
			'ENDDATE'			=>	$params['end_date'], 
			'EMAIL'				=>	$params['email'], 
			'RECEIVER'			=>	$params['receiver'], 
			'RECEIPTID'			=>	$params['receipt_id'], 
			'TRANSACTIONID'		=>	$params['transaction_id'], 
			'INVNUM'			=>	$params['inv_num'], 
			'ACCT'				=>	$params['cc_number'],
			'AUCTIONITEMNUMBER'	=>	$params['auction_item_number'],
			'TRANSACTIONCLASS'	=>	$params['transaction_class'],			
			'AMT'				=>	$params['amt'], 
			'CURRENCYCODE'		=>	$params['currency_code'], 
			'STATUS'			=>	$params['status'], 
			'SALUTATION'		=>	$params['salutation'], 
			'FIRSTNAME'			=>	$params['first_name'], 
			'MIDDLENAME'		=>	$params['middle_name'], 
			'LASTNAME'			=>	$params['last_name'],
			'SUFFIX'			=>	$params['suffix'],
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
	public function paypal_paymentspro_recurring_payment($params)
	{
		$this->_api_method = array('METHOD' => 'CreateRecurringPaymentsProfile');
			
		$this->_request = array(
			'SUBSCRIBERNAME'		=>	$params['first_name'].' '.$params['last_name'],
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
			'SHIPTONAME'			=>	$params['ship_to_first_name'].' '.$params['ship_to_last_name'],
			'SHIPTOSTREET'			=>	$params['ship_to_street'],
			'SHIPTOSTREET2'			=>	$params['ship_to_street2'],
			'SHIPTOCITY'			=>	$params['ship_to_city'],
			'SHIPTOSTATE'			=>	$params['ship_to_state'],
			'SHIPTOZIP'				=>	$params['ship_to_postal_code'],
			'SHIPTOCOUNTRY'			=>	$params['ship_to_country'],
			'SHIPTOPHONENUM'		=>	$params['phone'],
			'TRIALBILLINGPERIOD'	=>	$params['trial_billing_cycles'],
			'TRIALBILLINGFREQUENCY'	=>	$params['trial_billing_frequency'],
			'TRIALAMT'				=>	$params['trial_amt'],									
			'CREDITCARDTYPE'		=>	$params['cc_type'],
			'ACCT'					=>	$params['cc_number'],	
			'EXPDATE'				=>	$params['exp_date'],
			'CVV2'					=>	$params['cc_code'],
			'STARTDATE'				=>	$params['start_date'],
			'ISSUENUMBER'			=>	$params['issue_number'],
			'EMAIL'					=>	$params['email'],
			'PAYERID'				=>	$params['identifier'],
			'PAYERSTATUS'			=>	$params['payer_status'],
			'country'				=>	$params['country_code'],
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
			'SHIPTOPHONENUM'		=>	$params['phone']
		);		

		return $this->_handle_query();
	}

	/**
	 * Get profile info for a particular profile id
	 *
	 * @param	array
	 * @return	object
	 */		
	public function paypal_paymentspro_get_recurring_profile($params)
	{
		$this->_api_method = array('METHOD'	=> 'GetRecurringPaymentsProfileDetails');
		
		$this->_request = array(
			'PROFILEID'	=>	$params['identifier']
		);
		
		return $this->_handle_query();
	}
	
	/**
	 * Suspend a recurring profile
	 *
	 * @param	array
	 * @return	object
	 */		
	public function paypal_paymentspro_suspend_recurring_profile($params)
	{
		$this->_api_method = array('METHOD'	=> 'ManageRecurringPaymentsProfileStatus');
		
		$this->_request = array(
			'PROFILEID'	=>	$params['identifier'],
			'ACTION'	=>	'Suspend',
			'NOTE'		=>	$params['note']
		);
		
		return $this->_handle_query();
	}
		
	/**
	 * Activate a recurring profile which was previously suspended
	 *
	 * @param	array
	 * @return	object
	 */		
	public function paypal_paymentspro_activate_recurring_profile($params)
	{
		$this->_api_method = array('METHOD'	=> 'ManageRecurringPaymentsProfileStatus');
		
		$this->_request = array(
			'PROFILEID'	=>	$params['identifier'],
			'ACTION'	=>	'Reactivate',
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
	public function paypal_paymentspro_cancel_recurring_profile($params)
	{
		$this->_api_method = array('METHOD'	=> 'ManageRecurringPaymentsProfileStatus');
		
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
	public function paypal_paymentspro_recurring_bill_outstanding($params)
	{
		$this->_api_method = array('METHOD'	=> 'BillOutstandingAmount');
		
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
	public function paypal_paymentspro_update_recurring_profile($params)
	{
		$this->_api_method = array('METHOD'	=> 'UpdateRecurringPaymentsProfile');
	
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
			'SHIPTONAME'				=>	$params['ship_to_first_name'].' '.$params['ship_to_last_name'],
			'SHIPTOSTREET'				=>	$params['ship_to_street'],
			'SHIPTOSTREET2'				=>	$params['ship_to_street2'],
			'SHIPTOCITY'				=>	$params['ship_to_city'],
			'SHIPTOSTATE'				=>	$params['ship_to_state'],
			'SHIPTOZIP'					=>	$params['ship_to_postal_code'],
			'SHIPTOCOUNTRY'				=>	$params['ship_to_country'],	
			'SHIPTOPHONENUM'			=>	$params['phone'],	
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
			'country'				=>	$params['country_code'],
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
		$settings = array_merge($this->_api_method, $this->_api_settings);
		$merged = array_merge($settings, $this->_request);
		$request = $this->payments->filter_values($merged);	
		$this->_request = http_build_query($request);
		$this->_http_query = $this->_api_endpoint.$this->_request;
		
		include 'paypal_paymentspro/request.php';
		include 'paypal_paymentspro/response.php';
		
		$make_request = Paypal_PaymentsPro_Request::make_request($this->_http_query);
		$gateway_response = Paypal_PaymentsPro_Response::parse_response($make_request);
		
		return $gateway_response;
	}
	
	
}
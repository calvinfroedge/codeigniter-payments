<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Authorize_Net extends CF_Payments
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
	protected $_api_endpoint;	

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
	protected $_http_query;	
	
	/**
	 * The default params for this api
	*/	
	private	$_default_params;
	
	/**
	 * The delimiting character
	*/
	protected $_delimiter;
	
	/**
	 * Constructor method
	*/		
	public function __construct()
	{
		parent::__construct();	
						
		$this->_ci->load->config('payment_modules/authorize_net');
		$this->_default_params = $this->_ci->config->item('method_params');
		$this->_api_endpoint = $this->_ci->config->item('authorize_net_api_endpoint');
		$this->_xml_api_endpoint = $this->_ci->config->item('authorize_net_xml_api_endpoint');
		$this->_delimiter = $this->_ci->config->item('authorize_net_delimiter');		
		$this->_api_settings = array(
			'x_login'		=> $this->_ci->config->item('authorize_net_api_username'),
			'x_tran_key'	=> $this->_ci->config->item('authorize_net_api_password'),
			'x_delim_data' 	=> true,
			'x_delim_char'	=>	$this->_delimiter,
			'x_relay_response'	=> false,	
			'x_version'		=> '3.1',
			
		);
	}

	/**
	 * Make a oneoff payment
	 * @param	array	An array of payment params, sent from your controller / library
	 * @return	object	The response from the payment gateway
	*/	
	public function authorize_net_oneoff_payment($params)
	{
		$this->_api_method = array('x_type' => 'AUTH_CAPTURE');	
		$this->_build_standard_request_fields($params);
		
		return $this->_handle_query();
	}

	/**
	 * Authorize a oneoff payment
	 * @param	array	An array of payment params, sent from your controller / library
	 * @return	object	The response from the payment gateway
	*/	
	public function authorize_net_authorize_payment($params)
	{
		$this->_api_method = array('x_type' => 'AUTH_ONLY');
		$this->_build_standard_request_fields($params);		
		return $this->_handle_query();	
	}

	private function _build_standard_request_fields($params)
	{
		$this->_request = array(
			'x_method'				=>	'CC',
			'x_customer_ip'			=>	$params['ip_address'],
			'x_card_num'			=>	$params['cc_number'],
			'x_exp_date'			=>	$params['cc_exp'],
			'x_card_code'			=>	$params['cc_code'],	
			'x_email'				=>	$params['email'],
			'x_first_name'			=>  $params['first_name'],
			'x_last_name'			=>  $params['last_name'],
			'x_company'				=>	$params['business_name'],
			'x_address'				=>  $params['street'],
			'x_city'				=>  $params['city'],
			'x_state'				=>  $params['state'],
			'x_country'				=>  $params['countrycode'],
			'x_zip'					=>	$params['postal_code'],
			'x_amount'				=>	$params['amt'],
			'x_phone'				=>	$params['phone'],
			'x_fax'					=>  $params['fax'],
			'x_cust_id'				=>	$params['identifier'],	
			'x_description'			=>	$params['desc'],			
			'x_test_request'		=>	$params['run_as_test'],
			'x_invoice_num'			=>  $params['inv_num'],
			'x_duplicate_window'	=>	$params['duplicate_window'],
			'x_ship_to_first_name'	=>	$params['ship_to_first_name'],
			'x_ship_to_last_name'	=>	$params['ship_to_last_name'],
			'x_ship_to_address'		=>	$params['ship_to_street'],
			'x_ship_to_city'		=>	$params['ship_to_city'],
			'x_ship_to_state'		=>	$params['ship_to_state'],
			'x_ship_to_zip'			=>	$params['ship_to_zip'],
			'x_ship_to_country'		=>	$params['ship_to_country'],
			'x_ship_to_company'		=>	$params['ship_to_company'],
			'x_tax'					=>	$params['tax_amt'],
			'x_freight'				=>	$params['shipping_amt'],
			'x_duty'				=>	$params['duty_amt'],
			'x_tax_exempt'			=>	$params['tax_exempt']
		);	
	}

	/**
	 * Capture a oneoff payment
	 * @param	array	An array of payment params, sent from your controller / library
	 * @return	object	The response from the payment gateway
	*/	
	public function authorize_net_capture_payment($params)
	{
		$this->_api_method = array('x_type' => 'PRIOR_AUTH_CAPTURE');
		
		$this->_request = array(
			'x_trans_id'		=>	$params['identifier'],
			'x_amount'			=>	$params['amt'], //Only required if amount is less than that which was originally authorized.
		);
				
		return $this->_handle_query();	
	}

	/**
	 * Void a oneoff payment
	 * @param	array	An array of params, sent from your controller / library
	 * @return	object	The response from the payment gateway
	 * NOTE: This transaction type can be used to cancel either an original transaction that is not yet settled, or an entire order composed of more than one transaction.  A void prevents the transaction or order from being sent for settlement. A Void can be submitted against any other transaction type.
	 * NOTE: This will ONLY work for unsettled transactions.
	*/	
	public function authorize_net_void_payment($params)
	{
		$this->_api_method = array('x_type' => 'VOID');
		
		$this->_request = array(
			'x_trans_id'	=>	$params['identifier'],  //This should have been returned to you when you authorized or captured the payment.
		);
				
		return $this->_handle_query();	
	}	
	
	/**
	 * Refund a transaction
	 * @param	array	An array that contains your identifier
	 * @return	object	The response from the payment gateway
	 *
	 * NOTE:  This submits a LINKED credit.  Authorize.net supports both linked credits and unlinked credits.  Linked credit refunds must be submitted wthin 120 days of original settlement, and must be associated with a particular transaction.  Unlinked credits allow you to submit refunds for payments not submitted through the gateway, or beyond the 120 day period.  If you want to do unlinked credits, check this out: http://www.authorize.net/files/ecc.pdf
	*/	
	public function authorize_net_refund_payment($params)
	{
		$this->_api_method = array('x_type' => 'CREDIT');
		
		$this->_request = array(
			'x_trans_id'	=>	$params['identifier'],	//Required.  Should have been returned by previous transaction.
			'x_card_num'	=>	$params['last_4_digits'],	//Can be full or partial
		);
				
		return $this->_handle_query();		
	}	
		
	/**
	 * Create a new recurring payment
	 *
	 * @param	array
	 * @return	object
	 *
	 * Note:  Thanks to John Conde for His ARB sample class which I used as my starting point: http://www.merchant-account-services.org/blog/authorizenet-launches-recurring-billing-api/
	 */		
	public function authorize_net_recurring_payment($params)
	{
		if($params['billing_period'] != 'Month' && $params['billing_period'] != 'Day') 
		{
			return (object) array('status' => 'Failure', 'response' => 'Valid billing_period values for Authorize.net are "days" and "months"');
		}
		
		if($params['billing_period'] == 'Month')
			$params['billing_period'] = 'months';
		
		if($params['billing_period'] == 'Day')
			$params['billing_period'] = 'days';
		
		$trial_params = $this->build_nodes(
			array(
				'trialOccurrences' 	=>  $params['trial_billing_cycles'],
				'trialAmount'		=> 	$params['trial_amt'],				
			)
		);
		
		$order_params = $this->build_nodes(
			array(
				'invoiceNumber'		=> 	$params['inv_num'],
				'description'		=> 	$params['desc'],				
			)
		);
		
		$customer_params = $this->build_nodes(
			array(
				'id'				=> 	$params['identifier'],
				'email'				=> 	$params['email'],
				'phoneNumber'		=>	$params['phone'],
				'faxNumber'			=>	$params['fax'],				
			)
		);
		
		$bill_params = $this->build_nodes(
			array(
				'company'			=>	$params['business_name'],
				'address'			=>	$params['street'],
				'city'				=>	$params['city'],
				'state'				=>	$params['state'],
				'zip'				=>	$params['postal_code'],
				'country'			=>	$params['countrycode'],
			)
		);
		
		$shipping_params = $this->build_nodes(
			array(
				'firstName'			=>	$params['ship_to_first_name'],
				'lastName'			=>	$params['ship_to_last_name'],
				'company'			=>	$params['ship_to_company'],
				'address'			=>	$params['ship_to_street'],
				'city'				=>	$params['ship_to_city'],
				'state'				=>	$params['ship_to_state'],
				'zip'				=>	$params['ship_to_zip'],
				'country'			=>	$params['ship_to_country']
			)
		);
		
		//NOTE.  SOME OF THE NODES WHICH HAVE BEEN BUILT HAVE EMPTY VALUES.  IN THIS CASE, NOTHING WILL BE INSERTED INTO THE XML, EVEN IF A REFERENCE IS THERE.
		
		$this->_request =  "<?xml version='1.0' encoding='utf-8'?>
        	<ARBCreateSubscriptionRequest xmlns='AnetApi/xml/v1/schema/AnetApiSchema.xsd'>
        		<merchantAuthentication>
                	<name>" . $this->_api_settings['x_login'] . "</name>
                    <transactionKey>" . $this->_api_settings['x_tran_key'] . "</transactionKey>
                </merchantAuthentication>
                <refId>" . $params['identifier'] ."</refId>
                <subscription>
                	<name>". $params['first_name'] . ' ' . $params['last_name'] ."</name>
                    <paymentSchedule>
                    	<interval>
                        	<length>". $params['billing_frequency'] ."</length>
                            	<unit>". $params['billing_period'] ."</unit>
                            </interval>
                            <startDate>" . $params['profile_start_date'] . "</startDate>
                            <totalOccurrences>". $params['total_billing_cycles'] . "</totalOccurrences>
                            ". $trial_params['trialOccurrences'] ."
                    </paymentSchedule>
                    <amount>". $params['amt'] ."</amount>
                    ". $trial_params['trialAmount'] ."
                    <payment>
                    	<creditCard>
                        	<cardNumber>" . $params['cc_number'] . "</cardNumber>
                            <expirationDate>" . $params['exp_date'] . "</expirationDate>
                        </creditCard>
                    </payment>
                    <order>
						". $order_params['invoiceNumber'] ."
						". $order_params['description'] ."
                    </order>
                    <customer>
                    	". $customer_params['id'] ."
                        ". $customer_params['email']."
                        ". $customer_params['phoneNumber']."
                        ". $customer_params['faxNumber']."
                    </customer>
                    <billTo>
                    	<firstName>". $params['first_name'] . "</firstName>
                        <lastName>" . $params['last_name'] . "</lastName>
                        ". $bill_params["company"] ."
                        ". $bill_params["address"] ."
                        ". $bill_params['city'] ."
                        ". $bill_params['state'] ."
                        ". $bill_params['zip'] ."
                        ". $bill_params['country'] ."
                    </billTo>
                    <shipTo>
                    	". $shipping_params['firstName'] ."
                        ". $shipping_params['lastName'] ."
                        ". $shipping_params['company'] ."
                        ". $shipping_params['address'] ."
                        ". $shipping_params['city'] ."
                        ". $shipping_params['state'] ."
                        ". $shipping_params['zip'] ."
                        ". $shipping_params['country'] ."
                    </shipTo>
				</subscription>
		</ARBCreateSubscriptionRequest>";
				
		return $this->_handle_query(true);
	}	

	/**
	 * Get profile info for a particular profile id
	 *
	 * @param	array
	 * @return	object
	 */		
	public function authorize_net_get_recurring_profile($params)
	{	
		$this->_request = "<?xml version='1.0' encoding='utf-8'?>
			<ARBGetSubscriptionStatusRequest xmlns='AnetApi/xml/v1/schema/AnetApiSchema.xsd'>
				<merchantAuthentication>
					<name>". $this->_api_settings['x_login'] ."</name>
					<transactionKey>". $this->_api_settings['x_tran_key'] ."</transactionKey>
				</merchantAuthentication>
				<subscriptionId>". $params['identifier'] ."</subscriptionId>
			</ARBGetSubscriptionStatusRequest>";
		
		return $this->_handle_query(true);
	}

	/**
	 * Cancel a recurring profile
	 *
	 * @param	array
	 * @return	object
	 */		
	public function authorize_net_cancel_recurring_profile($params)
	{	
		$this->_request = "<?xml version='1.0' encoding='utf-8'?>
			<ARBCancelSubscriptionRequest xmlns='AnetApi/xml/v1/schema/AnetApiSchema.xsd'>
				<merchantAuthentication>
					<name>". $this->_api_settings['x_login'] ."</name>
					<transactionKey>". $this->_api_settings['x_tran_key'] ."</transactionKey>
				</merchantAuthentication>
				<subscriptionId>". $params['identifier'] ."</subscriptionId>
			</ARBCancelSubscriptionRequest>		
		";
		
		return $this->_handle_query(true);
	}			

	/**
	 * Update a recurring payments profile
	 *
	 * @param	array
	 * @return	object
	 * NOTE:
		* The subscription start date (subscription.paymentSchedule.startDate) may only be updated in the event that no successful payments have been completed.
		¥ The subscription interval information (subscription.paymentSchedule.interval.length and subscription.paymentSchedule.interval.unit) may not be updated.
		¥ The number of trial occurrences (subscription.paymentSchedule.trialOccurrences) may only be updated if the subscription has not yet begun or is still in the trial period.
		¥ All other fields are optional.	 
	 */		
	public function authorize_net_update_recurring_profile($params)
	{		
		$trial_params = $this->build_nodes(
			array(
				'trialOccurrences' 	=>  $params['trial_billing_cycles'],
				'trialAmount'		=> 	$params['trial_amt'],				
			)
		);
		
		$order_params = $this->build_nodes(
			array(
				'invoiceNumber'		=> 	$params['inv_num'],
				'description'		=> 	$params['desc'],				
			)
		);
		
		$customer_params = $this->build_nodes(
			array(
				'id'				=> 	$params['identifier'],
				'email'				=> 	$params['email'],
				'phoneNumber'		=>	$params['phone'],
				'faxNumber'			=>	$params['fax'],				
			)
		);
		
		$bill_params = $this->build_nodes(
			array(
				'firstName'			=>	$params['first_name'],
				'lastName'			=>	$params['last_name'],
				'company'			=>	$params['business_name'],
				'address'			=>	$params['street'],
				'city'				=>	$params['city'],
				'state'				=>	$params['state'],
				'zip'				=>	$params['postal_code'],
				'country'			=>	$params['countrycode'],
			)
		);
		
		$shipping_params = $this->build_nodes(
			array(
				'firstName'			=>	$params['ship_to_first_name'],
				'lastName'			=>	$params['ship_to_last_name'],
				'company'			=>	$params['ship_to_company'],
				'address'			=>	$params['ship_to_street'],
				'city'				=>	$params['ship_to_city'],
				'state'				=>	$params['ship_to_state'],
				'zip'				=>	$params['ship_to_zip'],
				'country'			=>	$params['ship_to_country']
			)
		);
		
		$interval_params = $this->build_nodes(
			array(
				'length'	=>	$params['billing_frequency'],
				'unit'		=>	$params['billing_period']
			)
		);
			
		$this->_request =  "<?xml version='1.0' encoding='utf-8'?>
        	<ARBUpdateSubscriptionRequest xmlns='AnetApi/xml/v1/schema/AnetApiSchema.xsd'>
        		<merchantAuthentication>
                	<name>" . $this->_api_settings['x_login'] . "</name>
                    <transactionKey>" . $this->_api_settings['x_tran_key'] . "</transactionKey>
                </merchantAuthentication>
                <subscriptionId>" . $params['identifier'] ."</subscriptionId>
                <subscription>
                	<name>". $params['first_name'] . ' ' . $params['last_name'] ."</name>
                    <paymentSchedule>";
        
        if(!empty($interval_params['length']) || !empty($interval_params['unit']))
        {
        	$this->_request .= "<interval>
                    		".	$interval_params['length']	."
                    		".	$interval_params['unit'] ."
                        </interval>";
        }
        
        if(!empty($params['profile_start_date']))
        {
        	$this->_request .= "<startDate>" . $params['profile_start_date'] . "</startDate>";
        }
        
        if(!empty($params['total_billing_cycles']))
        {
        	$this->_request .=  "<totalOccurrences>". $params['total_billing_cycles'] . "</totalOccurrences>
                            ". $trial_params['trialOccurrences'] ."";
        
        }
        $this->_request .= "
                    </paymentSchedule>";
        
        if(!empty($params['amt']))
        {
        	$this->_request .= "<amount>". $params['amt'] ."</amount>";
        }
                    
        $this->_request .=   "". $trial_params['trialAmount'] ."";
        
        if(!empty($params['cc_number']) || !empty($params['exp_date']))
        {
        	$this->_request .= "
                    <payment>
                    	<creditCard>
                        	<cardNumber>" . $params['cc_number'] . "</cardNumber>
                            <expirationDate>" . $params['exp_date'] . "</expirationDate>
                        </creditCard>
                    </payment>        	
        	";
        }
        
        $this->_request .= "            <order>
						". $order_params['invoiceNumber'] ."
						". $order_params['description'] ."
                    </order>
                    <customer>
                    	". $customer_params['id'] ."
                        ". $customer_params['email']."
                        ". $customer_params['phoneNumber']."
                        ". $customer_params['faxNumber']."
                    </customer>
                    <billTo>
                    	". $params['first_name'] . "
                        " . $params['last_name'] . "
                        ". $bill_params["company"] ."
                        ". $bill_params["address"] ."
                        ". $bill_params['city'] ."
                        ". $bill_params['state'] ."
                        ". $bill_params['zip'] ."
                        ". $bill_params['country'] ."
                    </billTo>
                    <shipTo>
                    	". $shipping_params['firstName'] ."
                        ". $shipping_params['lastName'] ."
                        ". $shipping_params['company'] ."
                        ". $shipping_params['address'] ."
                        ". $shipping_params['city'] ."
                        ". $shipping_params['state'] ."
                        ". $shipping_params['zip'] ."
                        ". $shipping_params['country'] ."
                    </shipTo>
				</subscription>
		</ARBUpdateSubscriptionRequest>";
		
		return $this->_handle_query(true);
	}

	/**
	 * Build the query for the response and call the request function
	 *
	 * @param	array
	 * @param	array
	 * @param	string
	 * @return	array
	 */		
	private function _handle_query($xml = false)
	{
		if(!$xml)
		{
			$settings = array_merge($this->_api_method, $this->_api_settings);
			$this->_request = $this->filter_values(array_merge($settings, $this->_request));	
			$this->_request = http_build_query($this->_request);	
		}
		
		$this->_http_query = $this->_request;

		include_once 'authorize_net/request.php';
		include_once 'authorize_net/response.php';
		
		$request = Authorize_Net_Request::make_request($xml);
		
		return Authorize_Net_Response::parse_response($this->_delimiter, $request, $xml);
	}		
		
}
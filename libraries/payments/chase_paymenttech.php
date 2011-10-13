<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Chase_PaymentTech
{	
	/**
	 * Default params for a given method
	*/	
	private $_default_params;
	
	/**
	 * The endpoint the request is to use
	*/
	private $_api_endpoint;

	/**
	 * An array containing settings
	*/
	private $_api_settings;	
	
	/**
	 * An array containing a key map of all transaction params
	*/
	private $_key_map;
		
	/**
	 * Constructor method
	*/		
	public function __construct($payments)
	{
		$this->payments = $payments;				
		$this->_default_params = $this->payments->ci->config->item('method_params');
		$this->_api_endpoint = $this->payments->ci->config->item('api_endpoint'.'_'.$this->payments->mode);
		$this->_key_map = $this->payments->ci->config->item('payment_to_gateway_key_map');

		$this->_api_settings = array(
			'xml_version'	=> '1.0',
			'encoding'		=> 'utf-8',
		);
	}

	/**
	 * Builds a request
	 * @param	array	array of params
	 * @param	string	the api call to use
	 * @param	string	the type of transaction
	 * @return	array	Array of transaction settings
	*/	
	private function _build_request($params, $transaction_type = NULL)
	{
		//Start the request with common fields
		$nodes[$this->_api_method] = $this->_common_request_fields($transaction_type);

		$nodes[$this->_api_method]['MessageType'] = $transaction_type;
		
		foreach($params as $k=>$v)
		{
			//print $k.':'.$v;
			if($k == 'cc_exp')
			{
				$mm = substr($v, 0, 2);
				$yy = substr($v, -2);
				$nodes[$this->_api_method][$this->_key_map[$k]] = $mm.$yy;
				unset($k);
				continue;
			}
			
			if($k == 'amt')
			{
				if(strpos($v, '.'))
				{
					$v = str_replace('.', '', $v);
				}
				else
				{
					$v .= '00';
				}
				$nodes[$this->_api_method][$this->_key_map[$k]] = $v;
				unset($k);
				continue;
			}
						
			if(!is_null($v) && strlen($v) > 0)
			{
				$nodes[$this->_api_method][$this->_key_map[$k]] = $v;
			}
		}
										
		$request = $this->payments->build_xml_request(
			$this->_api_settings['xml_version'],
			$this->_api_settings['encoding'],
			$nodes,					
			'Request'
		);
		
		/*If recurring transaction types, use Status param - A Active, I Inactive, MS Manual Suspend
		
			nodes MBType = R
			
			nodes MBOrderIdGenerationMethod = DI
				Valid values:
				IO Use the Customer Reference Number (Profile ID). This value is made up of the capital letters I and O, not numbers.
				DI Dynamically generate the Order ID. This value is made up of the capital letters D and I, no numbers.			
			
			nodes MBRecurringStartDate = MMDDYYYY

				To allow the Managed Billing engine to properly calculate and schedule all billings, this date must be at least one day after the request date (a recurring billing cycle can never begin on the date that the request message is sent to the Orbital system).
				
			nodes MBRecurringEndDate = MMDDYYYY
			
			if no start date / end date supplied, MBRecurringNoEndDateFlag	= Y, else = N
			
			nodes MBRecurringMaxBillings = 1-999999
			
			nodes MBRecurringFrequency = 
				Field
				Allowed Values
				Allowed Special Chars
				Day-of-month
				1Ğ31
				, - * ? / L W
				Month
				1Ğ12 or JANĞDEC
				, - * /
				Day-of-week
				1Ğ7 or SUNĞSAT
				, - * ? / L #					
			
			nodes MBDeferredBillDate			
		*/
		
		/* If refund or credit
			nodes TxRefNum	=	identifier
		*/
		
		var_dump($request);exit;
		
		return $request;	
	}
	
	private function _common_request_fields($transaction_type)
	{
		return array(
			'OrbitalConnectionUsername' 		=> $this->payments->ci->config->item('api_username'),
			'OrbitalConnectionPassword'		 	=> $this->payments->ci->config->item('api_password'),
			'MessageType'						=> $transaction_type,
			'BIN'								=> $this->payments->ci->config->item('paymentech_BIN'),
			'MerchantID'						=> $this->payments->ci->config->item('api_merchant_id'),
			'TerminalID'						=> $this->payments->ci->config->item('api_terminal_id'),
			'CustomerProfileFromOrderInd' 		=> $this->payments->ci->config->item('api_customer_ref_number_settings'),
			'CustomerProfileOrderOverrideInd'	=> $this->payments->ci->config->item('api_customer_profile_order_override')
		);
	}
		
	/**
	 * Make a oneoff payment
	 * @param	array	An array of payment params, sent from your controller / library
	 * @return	object	The response from the payment gateway
	*/	
	public function chase_paymenttech_oneoff_payment($params)
	{
		$this->_api_method = 'NewOrder';
		$this->_request = $this->_build_request($params, 'AC');			
		return $this->_handle_query();
	}

	/**
	 * Authorize a oneoff payment
	 * @param	array	An array of payment params, sent from your controller / library
	 * @return	object	The response from the payment gateway
	*/	
	public function chase_paymenttech_authorize_payment($params)
	{
		$this->_api_method = 'NewOrder';
		$this->_request = $this->_build_request($params, 'A');			
		return $this->_handle_query();
	}

	/**
	 * Capture a oneoff payment
	 * @param	array	An array of payment params, sent from your controller / library
	 * @return	object	The response from the payment gateway
	*/	
	public function chase_paymenttech_capture_payment($params)
	{
		$this->_api_method = 'FC';
		$this->_request = $this->_build_request($params, 'FC');			
		return $this->_handle_query();
	}
	
	/**
	 * Refund a transaction
	 * @param	array	An array that contains your identifier
	 * @return	object	The response from the payment gateway
	 *
	 * NOTE:  This submits a LINKED credit.  Authorize.net supports both linked credits and unlinked credits.  Linked credit refunds must be submitted wthin 120 days of original settlement, and must be associated with a particular transaction.  Unlinked credits allow you to submit refunds for payments not submitted through the gateway, or beyond the 120 day period.  If you want to do unlinked credits, check this out: http://www.authorize.net/files/ecc.pdf
	*/	
	public function chase_paymenttech_refund_payment($params)
	{
		$this->_api_method = 'NewOrder';
		$this->_request = $this->_build_request($params, 'R');		
		return $this->_handle_query();	
	}	
		
	/**
	 * Create a new recurring payment
	 *
	 * @param	array
	 * @return	object
	 *
	 */		
	public function chase_paymenttech_recurring_payment($params)
	{
		$this->_api_method = 'ARBCreateSubscriptionRequest';
		$this->_request = $this->_build_request($params);	
		return $this->_handle_query();
	}	

	/**
	 * Get profile info for a particular profile id
	 *
	 * @param	array
	 * @return	object
	 */		
	public function chase_paymenttech_get_recurring_profile($params)
	{	
		$this->_api_method = 'ARBGetSubscriptionStatusRequest';
		$this->_request = $this->_build_request($params);	
		return $this->_handle_query();
	}

	/**
	 * Update a recurring payments profile
	 *
	 * @param	array
	 * @return	object
	 * NOTE:
		* The subscription start date (subscription.paymentSchedule.startDate) may only be updated in the event that no successful payments have been completed.
		´ The subscription interval information (subscription.paymentSchedule.interval.length and subscription.paymentSchedule.interval.unit) may not be updated.
		´ The number of trial occurrences (subscription.paymentSchedule.trialOccurrences) may only be updated if the subscription has not yet begun or is still in the trial period.
		´ All other fields are optional.	 
	 */		
	public function chase_paymenttech_update_recurring_profile($params)
	{		
		$this->_api_method = 'ARBUpdateSubscriptionRequest';
		$this->_request = $this->_build_request($params);		
		return $this->_handle_query();
	}
	
	/**
	 * Cancel a recurring profile
	 *
	 * @param	array
	 * @return	object
	 */		
	public function chase_paymenttech_cancel_recurring_profile($params)
	{	
		$this->_api_method = 'ARBCancelSubscriptionRequest';
		$this->_request = $this->_build_request($params);
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
		$this->_http_query = $this->_request;
		
		$response_object = $this->payments->gateway_request($this->_api_endpoint, $this->_http_query);
		$response = $this->_parse_response($response_object);
		
		return $response;
	}

	/**
	 * Parse the response from the server
	 *
	 * @param	array
	 * @return	object
	 */		
	private function _parse_response($xml)
	{	
		$details = (object) array();

		$as_array = $this->payments->arrayize_object($xml);

		$result = $as_array['messages']['resultCode'];
		
		if(isset($as_array['transactionResponse']))
		{
			$identifier = $as_array['transactionResponse']['transId'];
		}
		
		if(isset($as_array['subscriptionId']))
		{
			$identifier = $as_array['subscriptionId'];
		}
		
		$timestamp = gmdate('c');
		$details->timestamp = $timestamp;
		$details->gateway_response = $as_array;
		
		if(isset($identifier) AND strlen($identifier) > 1)
		{
			$details->identifier = $identifier;
		}
		
		if($result == 'Ok')
		{
			return $this->payments->return_response(
				'Success',
				$this->payments->payment_type.'_success',
				'gateway_response',
				$details
			);
		}
		
		if($result == 'Error')
		{
			if(isset($as_array['transactionResponse']['errors']['error']['errorText']))
			{
				$message = $as_array['transactionResponse']['errors']['error']['errorText'];
			}
			
			if(isset($as_array['messages']['message']['text']))
			{
				$message = $as_array['messages']['message']['text'];
			}
			
			if(isset($message))
			{
				$details->reason = $message;
			}	

			return $this->payments->return_response(
				'Failure',
				$this->payments->payment_type.'_gateway_failure',
				'gateway_response',
				$details
			);				
		}
	}
			
		
}
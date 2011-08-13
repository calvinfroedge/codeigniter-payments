<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Psigate
{		
	/**
	 * The use who wil make the call to the paypal gateway
	*/
	private $_api_user;
	
	/**
	 * Constructor method
	*/		
	public function __construct($cf_payments)
	{
		$this->payments = $cf_payments;				
		$this->_default_params = $this->payments->ci->config->item('method_params');
	}
	
	protected function _set_messenger_settings()
	{
		$this->_api_endpoint = $this->payments->ci->config->item('api_endpoint');	
		
		$this->_api_settings = array(
			'cid'			=> $this->payments->ci->config->item('api_cid'),
			'store_id'		=> $this->payments->ci->config->item('api_username'),
			'pass_phrase'	=> $this->payments->ci->config->item('api_password'),
			'xml_version'	=> '1.0',
			'encoding'		=> 'utf-8',
			'xml_schema'	=> '',
		);		
	}
	
	protected function _set_account_manager_settings()
	{
		$this->_api_endpoint = $this->payments->ci->config->item('api_recurring_endpoint');	
		
		$this->_api_settings = array(
			'cid'			=> $this->payments->ci->config->item('api_cid'),
			'store_id'		=> $this->payments->ci->config->item('api_username'),
			'pass_phrase'	=> $this->payments->ci->config->item('api_recurring_password'),
			'xml_version'	=> '1.0',
			'encoding'		=> 'utf-8',
			'xml_schema'	=> '',
		);		
	}

	/**
	 * Builds a request
	 * @param	array	array of params
	 * @param	string	the type of transaction
	 * @return	array	Array of transaction settings
	*/	
	protected function _build_request($params, $transaction_type = NULL)
	{	
		//var_dump($params);exit;
		$nodes = array();	
		$nodes[$this->_api_method] = array(
			'StoreID' => $this->_api_settings['store_id'],
			'Passphrase' => $this->_api_settings['pass_phrase']
		);
		
		if(isset($params['amt']))
		{
			$nodes[$this->_api_method]['Subtotal'] = $params['amt'];
		}
		
		$nodes[$this->_api_method] = array_merge($nodes[$this->_api_method], $this->_build_bill_to_fields($params));
		
		$nodes[$this->_api_method] = array_merge($nodes[$this->_api_method], $this->_build_ship_to_fields($params));
				
		if(isset($params['phone']))
		{
			$nodes[$this->_api_method]['Phone'] = $params['phone'];
		}
		
		if(isset($params['fax']))
		{
			$nodes[$this->_api_method]['Fax'] = $params['fax'];
		}
		
		if(isset($params['email']))
		{
			$nodes[$this->_api_method]['Email'] = $params['email'];
		}
		
		if(isset($params['note']))
		{
			$nodes[$this->_api_method]['Comments'] = $params['note'];
		}
		
		if(isset($params['tax_amt']))
		{
			$nodes[$this->_api_method]['Tax1'] = $params['tax_amt'];
		}
		
		if(isset($params['shipping_amt']))
		{
			$nodes[$this->_api_method]['ShippingTotal'] = $params['shipping_amt'];
		}
		
		if(isset($params['ip_address']))
		{
			$nodes[$this->_api_method]['CustomerIP'] = $params['ip_address'];
		}
		
		if($transaction_type === '2' OR $transaction_type === '3' OR $transaction_type === '9')
		{
			$nodes[$this->_api_method]['PaymentType'] = 'CC';
			$nodes[$this->_api_method]['CardAction'] = $transaction_type;
		}
		
		if(isset($params['cc_number']) AND isset($params['cc_exp']) AND isset($params['cc_code']))
		{
			$nodes[$this->_api_method]['PaymentType'] = 'CC';
			$nodes[$this->_api_method]['CardAction'] = $transaction_type;
			$nodes[$this->_api_method]['CardNumber'] = $params['cc_number'];
			
			if(strlen($params['cc_exp']) > 0)
			{
				$month = substr($params['cc_exp'], 0, 2);
				$year = substr($params['cc_exp'], -2, 2);
				$nodes[$this->_api_method]['CardExpMonth'] = $month;
				$nodes[$this->_api_method]['CardExpYear'] = $year;				
			}
			
			$nodes[$this->_api_method]['CardIDNumber'] = $params['cc_code'];		
		}
		
		if($transaction_type === '2' OR $transaction_type === '3' OR $transaction_type === '9')
		{
			$nodes[$this->_api_method]['OrderID'] = $params['identifier'];
		}
		
		if($transaction_type === '9')
		{
			$nodes[$this->_api_method]['TransRefNumber'] = $params['identifier_2'];
		}

		$request = $this->payments->build_xml_request(
			$this->_api_settings['xml_version'],
			$this->_api_settings['encoding'],
			$nodes
		);	
		
		
		return $request;	
	}

	/**
	 * Builds bill to fields
	 * @param	array	array of params
	 * @return	array	Array of fields
	*/		
	protected function _build_bill_to_fields($params)
	{	
		$billing = array();
		
		if(isset($params['first_name']))
		{
			$billing['Bname'] = $params['first_name'];
		}
		
		if(isset($params['last_name']))
		{
			if(!empty($params['first_name']))
			{
				$billing['Bname'] .= ' '.$params['last_name'];
			}
			else
			{
				$billing['Bname'] = $params['last_name'];
			}
		}
		
		if(isset($params['company']))
		{
			$billing['Bcompany'] = $params['company'];
		}
		
		if(isset($params['street']))
		{
			$billing['Baddress1'] = $params['street'];
		}
		
		if(isset($params['city']))
		{
			$billing['Bcity'] = $params['city'];
		}
		
		if(isset($params['state']))
		{
			$billing['Bprovince'] = $params['state'];
		}
		
		if(isset($params['country']))
		{
			$billing['Bcountry'] = $params['country'];
		}
		
		return $billing;
	}
	
	/**
	 * Builds ship to fields
	 * @param	array	array of params
	 * @return	array	Array of fields
	*/	
	protected function _build_ship_to_fields($params)
	{
		$shipping = array();
		
		if(isset($params['ship_to_first_name']))
		{
			$shipping['Sname'] = $params['ship_to_first_name'];
		}
		
		if(isset($params['ship_to_last_name']))
		{
			if(!empty($params['ship_to_first_name']))
			{
				$shipping['Sname'] .= ' '.$params['ship_to_last_name'];
			}
			else
			{
				$shipping[$this->_api_method]['Sname'] = $params['ship_to_last_name'];
			}
		}
		
		if(isset($params['ship_to_company']))
		{
			$shipping['Scompany'] = $params['ship_to_company'];
		}
		
		if(isset($params['ship_to_street']))
		{
			$shipping['Saddress1'] = $params['ship_to_street'];
		}
		
		if(isset($params['ship_to_city']))
		{
			$shipping['Scity'] = $params['ship_to_city'];
		}
		
		if(isset($params['ship_to_state']))
		{
			$shipping['Sprovince'] = $params['ship_to_state'];
		}
		
		if(isset($params['ship_to_country']))
		{
			$shipping['Scountry'] = $params['ship_to_country'];
		}
		
		return $shipping;	
	}
	
	/**
	 * Make a oneoff payment
	 * @param	array	An array of payment params, sent from your controller / library
	 * @return	object	The response from the payment gateway
	*/	
	public function psigate_oneoff_payment($params)
	{
		$this->_api_method = 'Order';
		$this->_set_messenger_settings();
		$this->_request = $this->_build_request($params, '0');			
		return $this->_handle_query();
	}

	/**
	 * Authorize a oneoff payment
	 * @param	array	An array of payment params, sent from your controller / library
	 * @return	object	The response from the payment gateway
	*/	
	public function psigate_authorize_payment($params)
	{
		$this->_api_method = 'Order';
		$this->_set_messenger_settings();		
		$this->_request = $this->_build_request($params, '1');			
		return $this->_handle_query();
	}

	/**
	 * Authorize a oneoff payment
	 * @param	array	An array of payment params, sent from your controller / library
	 * @return	object	The response from the payment gateway
	*/	
	public function psigate_capture_payment($params)
	{
		$this->_api_method = 'Order';
		$this->_set_messenger_settings();		
		$this->_request = $this->_build_request($params, '2');			
		return $this->_handle_query();
	}	

	/**
	 * Void a transaction
	 * @param	array	An array that contains your identifier
	 * @return	object	The response from the payment gateway
	*/	
	public function psigate_void_payment($params)
	{
		$this->_api_method = 'Order';
		$this->_set_messenger_settings();		
		$this->_request = $this->_build_request($params, '9');		
		return $this->_handle_query();	
	}
	
	/**
	 * Refund a transaction
	 * @param	array	An array that contains your identifier
	 * @return	object	The response from the payment gateway
	 *
	 * NOTE:  You must include the original transaction amount for this payment gateway!  You can issue a refund amount less than or equal the original order amount.
	*/	
	public function psigate_refund_payment($params)
	{
		$this->_api_method = 'Order';
		$this->_set_messenger_settings();		
		$this->_request = $this->_build_request($params, '3');		
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
		//var_dump($this->_request);exit;
		$this->_http_query = $this->_request;

		include_once 'psigate/request.php';
		include_once 'psigate/response.php';
		
		$request = Psigate_Request::make_request();
		$response_object = $this->payments->parse_xml($request);
		$response = Psigate_Response::parse_response($response_object);
		
		return $response;
	}			
}
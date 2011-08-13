<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Paypal_PaymentsPro_Response extends Paypal_PaymentsPro
{

	public function __construct()
	{
		
	}
	
	/**
	 * Parse the response from the server
	 *
	 * @param	array
	 * @return	object
	 */		
	public function parse_response($response)
	{	
		$results = explode('&',urldecode($response));
		foreach($results as $result)
		{
			list($key, $value) = explode('=', $result);
			$gateway_response[$key]=$value;
		}
	
		$details = (object) array();
		
		foreach($gateway_response as $k=>$v)
		{
				$details->gateway_response->$k = $v;
		}

		if(isset($gateway_response['L_LONGMESSAGE0']))
		{
			$details->reason  =	$gateway_response['L_LONGMESSAGE0'];
		}

		if(isset($gateway_response['TIMESTAMP']))
		{
			$details->timestamp = $gateway_response['TIMESTAMP'];
		}
			
		if(isset($gateway_response['TRANSACTIONID']))
		{
			$details->identifier = $gateway_response['TRANSACTIONID'];
		}
			
		if(isset($gateway_response['PROFILEID']))
		{
			$details->identifier = $gateway_response['PROFILEID'];
		}				
			
		if($gateway_response['ACK'] == 'Success')
		{	
			return $this->payments->return_response(
				'Success',
				$this->payments->payment_type.'_success',
				'gateway_response',
				$details
			);
		}
		else
		{
			return $this->payments->return_response(
				'Failure',
				$this->payments->payment_type.'_gateway_failure',
				'gateway_response',
				$details
			);		
		}	
	}

}
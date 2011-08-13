<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Psigate_Response extends Psigate
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
	public function parse_response($xml)
	{	
		$details = (object) array();

		$as_array = $this->payments->arrayize_object($xml);

		$result = $as_array['Approved'];
		
		if(isset($as_array['OrderID']))
		{
			$identifier = $as_array['OrderID'];
		}
		
		if(isset($as_array['subscriptionId']))
		{
			$identifier = $as_array['subscriptionId'];
		}
		
		if(isset($as_array['TransRefNumber']))
		{
			$identifier2 = $as_array['TransRefNumber'];
		}
		
		$details->timestamp = $as_array['TransTime'];
		$details->gateway_response = $as_array;
		
		if(isset($identifier))
		{
			$identifier = (string) $identifier; 
			if(strlen($identifier) > 1)
			{
				$details->identifier = $identifier;
			}
		}
		
		if(isset($identifier2))
		{
			$identifier2 = (string) $identifier2; 
			if(strlen($identifier2) > 1)
			{		
				$details->identifier2 = $identifier2;
			}
		}
		
		if($result == 'APPROVED')
		{
			return $this->payments->return_response(
				'Success',
				$this->payments->payment_type.'_success',
				'gateway_response',
				$details
			);
		}
		
		if($result == 'ERROR' OR $result == 'DECLINED')
		{
			if(isset($as_array['ErrMsg']))
			{
				$message = $as_array['ErrMsg'];
				$message = explode(':', $message);
				$message = $message[1];
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
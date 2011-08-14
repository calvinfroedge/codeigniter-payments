<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Beanstream_Response extends Beanstream
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
		$details = (object) array();
			
		if(strstr($response, '<response>'))
		{
			$response = $this->payments->parse_xml($response);
			$response = $this->payments->arrayize_object($response);
			$details->gateway_response = $response;
							
			if($response['code'] == '1')
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
				$details->reason = $response['message'];
				return $this->payments->return_response(
					'Failure',
					$this->payments->payment_type.'_gateway_failure',
					'gateway_response',
					$details
				);				
			}
		}
		else
		{
		//var_dump($response);exit;
			$results = explode('&',urldecode($response));
			foreach($results as $result)
			{
				list($key, $value) = explode('=', $result);
				$gateway_response[$key]=$value;
			}
			
			$details->gateway_response = $gateway_response;	
			$details->timestamp = $gateway_response['trnDate'];		
				
			if($gateway_response['trnApproved'] == '1')
			{	
				$details->identifier = $gateway_response['trnId'];
				
				if(isset($gateway_response['rbAccountId']))
				{
					$details->identifier = $gateway_response['rbAccountId'];
				}
				
				return $this->payments->return_response(
					'Success',
					$this->payments->payment_type.'_success',
					'gateway_response',
					$details
				);
			}
			else
			{
				$details->reason = $gateway_response['messageText'];
				
				return $this->payments->return_response(
					'Failure',
					$this->payments->payment_type.'_gateway_failure',
					'gateway_response',
					$details
				);		
			}	
		}
	}

}
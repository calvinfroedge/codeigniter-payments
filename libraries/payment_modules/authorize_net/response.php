<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Authorize_Net_Response extends Authorize_Net
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
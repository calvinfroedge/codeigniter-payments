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
		print_r($response);exit;	
		$results = explode('&',urldecode($response));
		foreach($results as $result)
		{
			list($key, $value) = explode('=', $result);
			$gateway_response[$key]=$value;
		}
		
		if($gateway_response['ACK'] == 'Success')
		{
			return $this->payments->return_response(
				'Success',
				$this->payments->payment_type.'_success',
				'gateway_response',
				(object) array(
					'identifier'	=>	$gateway_response['TRANSACTIONID'],
					'timestamp'		=>	$gateway_response['TIMESTAMP']
				)
			);
		}
		else
		{
			return $this->payments->return_response(
				'Failure',
				$this->payments->payment_type.'_failure',
				'gateway_response',
				(object) array(
					'timestamp'		=>	$gateway_response['TIMESTAMP'],
					'reason'		=>	$gateway_response['L_LONGMESSAGE0']
				)
			);		
		}	
	}

}
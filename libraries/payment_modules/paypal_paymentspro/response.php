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
			$response_array[$key]=$value;
		}
		
		$return_object = array();
		
		//Set the response status
		
		($response_array['ACK'] == 'Success') 
		? $success = TRUE 
		: $failure = TRUE ;

		if(isset($failure))
		{	
			$return_object = array('status'=>'failure', 'message'=>$response_array);
		}
		if(isset($success))
		{	
			$return_object = array('status'=>'success', 'message'=>$response_array);
		}
		
		return (object) $response_array;		
	}

}
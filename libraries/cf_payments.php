<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* CodeIgniter Payments
*
* Make payments to multiple payment systems using a single interface
*
* @package CodeIgniter
* @subpackage Sparks
* @category Payments
* @author Calvin Froedge (www.calvinfroedge.com)
* @created 07/02/2011
* @license http://www.opensource.org/licenses/mit-license.php
* @link https://github.com/calvinfroedge/Codeigniter-Payments-Spark
*/

class CF_Payments
{
	/**
	 * The CodeIgniter instance
	*/		
	public $ci; 

	/**
	 * The version
	*/	
	private $_version = '0.0.1';	//The version

	/**
	 * The payment module to use
	*/	
	public $payment_module;  

	/**
	 * The payment type to make
	*/		
	public $payment_type;	

	/**
	 * The params to use
	*/		
	private	$_params;
	
	/**
	 * Error codes in the response object
	*/		
	private $_response_codes;

	/**
	 * Response messages that can be returned to the user or logged in the application
	*/		
	private $_response_messages;
	
	/**
	 * The default params for the method
	*/	
	private	$_default_params;
		
	/**
	 * The constructor function.
	 */	
	public function __construct()
	{
		$this->ci =& get_instance();
		$this->_response_codes = $this->ci->config->item('response_codes');
		$this->_response_messages = $this->ci->config->item('response_messages');	
		$this->_response_details = $this->ci->config->item('response_details');	
	}

	/**
	 * Make a call to a gateway. Uses other helper methods to make the request.
	 *
	 * @param	string	The payment method to use
	 * @param	array	$params[0] is the gateway, $params[1] are the params for the request
	 * @return	object	Should return a success or failure, along with a response.
	 */		
	public function __call($method, $params)
	{
		$supported = $this->_check_method_supported($method);
		
		if($supported)
		{
			$response = $this->_make_gateway_call($params[0], $method, $params[1]);
		}
		else
		{
			$response = $this->_return_response(
				'failure', 
				'not_a_method', 
				'local_response'
			);
		}
		return $response;
	}

	/**
	 * Checks to ensure that a method is actually supported by cf_payments before continuing
	 *
	 * @param	string	The payment method to use
	 * @return	object	Should return a success or failure, along with a response.
	 */	
	private function _check_method_supported($method)
	{
		$supported_methods = $this->ci->config->item('supported_methods');
		if(in_array($method, $supported_methods))
		{
			return true;
		}
		else
		{
			return false;		
		}
	}

	/**
	 * Make a call to a gateway. Uses other helper methods to make the request.
	 *
	 * @param	string	The payment module to use
	 * @param	string	The type of method being used.
	 * @param	array	Params to submit to the payment module
	 * @return	object	Should return a success or failure, along with a response.
	 */	
	private function _make_gateway_call($payment_module, $payment_type, $params)
	{	
		$valid_inputs = $this->_check_inputs($payment_module, $params);
		if($valid_inputs === TRUE)
		{
			$this->payment_type = $payment_type;
			$this->_params = $params;	
			$response = $this->_do_method($payment_module);
			return $response;		
		}
		else
		{
			return $inputs;	
		}	
	}
	
	/**
	 * Try use a method from a particular gateway
	 *
	 * @param	string	The payment module to use
	 * @param	string	The type of method being used.  Can be for making payments or getting statuses / profiles.
	 * @param	array	Params to submit to the payment module
	 * @return	object	Should return a success or failure, along with a response
	 */		
	private function _do_method($payment_module)
	{
		$module = $this->_load_module($payment_module);

		if($module === FALSE)
		{
			return $this->_return_response(
				'failure', 
				'not_a_module', 
				'local_response'
			);
		}
					
		$object = new $payment_module($this);
		
		$method = $payment_module.'_'.$this->payment_type;
		
		if(!method_exists($payment_module, $method))
		{
			return $this->_return_response(
				'failure', 
				'not_a_method', 
				'local_response'
			);	
		}
		else
		{
			$this->ci->load->config('payment_types/'.$this->payment_type);
			$this->_default_params = $this->ci->config->item($this->payment_type);
			
			return $object->$method(
				array_merge(
					$this->_default_params, 
					$this->_params
				)
			);
		}
	}

	/**
	 * Try to load a payment module
	 *
	 * @param	string	The payment module to load
	 * @return	mixed	Will return bool if file is not found.  Will return file as object if found.
	 */		
	private function _load_module($payment_module)
	{
		$module = dirname(__FILE__).'/payment_modules/'.$payment_module.'.php';
		if (!is_file($module))
		{
			$response = FALSE;
		}
		ob_start();
		include $module;
		$response = ob_get_clean();
		
		return $response;
	}

	/**
	 * Check user inputs to make sure they're good
	 *
	 * @param	string	The payment module
	 * @param	array	An array of params
	 * @return	mixed	Will return bool if file is not found.  Will return file as object if found.
	 */
	private function _check_inputs($payment_module, $params)
	{
		$expected_datatypes = array(
			'string'	=> $payment_module,
			'arrays'	=> array($params)
		);
		
		$expected_datatypes = $this->_check_datatypes($expected_datatypes);
		if ($expected_datatypes === FALSE)
		{
			return $this->_return_response(
				'failure', 
				'invalid_input', 
				'local_response'
			);		
		}

		$expected_params = $this->_check_params($params);
		
		if($expected_params !== TRUE)
		{
			return $expected_params;
		}
		
		return TRUE;
	}

	/**
	 * Make sure data types are as expected
	 *
	 * @param	array	array of params to check to ensure proper datatype
	 * @return	mixed	Will return TRUE if all pass.  Will return an object if datatypes are bad.
	 */		
	private function _check_datatypes($datatypes)
	{
		$invalids = 0;
		
		foreach($datatypes as $key=>$value)
		{
			if($key == 'arrays')
			{
				foreach($value as $array)
				{
					if(!is_array($array))
					{
						++$invalids;
					}			
				}
			}
			else
			{
				$check = 'is_'.$key;
				
				if(!$check($value))
				{
					++$invalids;
				}
			}
		}
		
		if($invalids > 0)
		{
			return FALSE;
		}
		
		return TRUE;
	}

	/**
	 * Make sure params are as expected
	 *
	 * @param	array	array of params to check to ensure proper formatting
	 * @return	mixed	Will return TRUE if all pass.  Will return an object if a param is bad.
	 */			
	private function _check_params($params)
	{
		//Ensure dates match MMYYYY format
		if(array_key_exists('cc_exp', $params))
		{
			$exp_date = $params['cc_exp'];
			$m1 = $exp_date[0];
			
			if(strlen($exp_date) != 6 OR !is_numeric($exp_date) OR $m1 > 1)
			{
				return $this->_return_response(
					'failure', 
					'invalid_input', 
					'local_response', 
					$this->_response_details['invalid_date_format']
				);
			}
		}
		
		return TRUE;
	}

	/**
	 * Remove key=>value pairs with empty values
	 *
	 * @param	array	array of key=>value pairs to check
	 * @return	array	Will return filtered array
	 */
	public function filter_values($array)
	{	
		foreach($array as $key=>$value)
		{
			if($value === NULL)
			{
				unset($array[$key]);
			}
		}
		return $array;
	}
	
	/**
	 * Returns an xml node if key=>value pair is not empty
	 *
	 * @param array	array of key=>value pairs to check
	 * @return	string	string value
	*/
	protected function _build_nodes($array)
	{
		$nodes = array();
		foreach($array as $key=>$value)
		{
			if(!empty($value))
			{
				$nodes[$key] = "<$key>$value</$key>";
			}
			else
			{
				$nodes[$key] = "";
			}
		}
		return $nodes;
	}

	/**
	 * Returns the response
	 *
	 * @param 	string	can be either 'Success' or 'Failure'
	 * @param	string	the response used to grab the code / message
	 * @param	string	whether the response is coming from the application or the gateway
	 * @param	mixed	can be an object, string or null.  Depends on whether local or gateway.
	 * @return	object	response object
	*/	
	public function return_response($status, $response, $response_type, $response_details = null)
	{
		$status = strtolower($status);
		
		($status == 'success')
		? $message_type = 'info'
		: $message_type = 'error';
		
		log_message($message_type, $this->_response_messages[$response]);
		
		if($response_type == 'local_response')
		{
			$local_response = $this->_return_local_response($status, $response, $response_details);
			return $local_response;
		}
		else if($response_type == 'gateway_response')
		{
			$gateway_response = $this->_return_gateway_response($status, $response, $response_details);
			return $gateway_response;			
		}

	}

	/**
	 * Returns a local response
	 *
	 * @param 	string	can be either 'Success' or 'Failure'
	 * @param	string	the response used to grab the code / message
	 * @param	mixed	can be string or null. 
	 * @return	object	
	*/	
	private function _return_local_response($status, $response, $response_details = null)
	{
		log_message($message_type, $this->_response_messages[$response]);
		
		if(is_null($response_details))
		{
			return (object) array
			(
				'type'				=>	'local_response',
				'status' 			=>	$status, 
				'response_code' 	=>	$this->_response_codes[$response], 
				'response_message' 	=>	$this->_response_messages[$response]
			);			
		}
		else
		{
			return (object) array
			(
				'type'				=>	'local_response',
				'status' 			=>	$status, 
				'response_code' 	=>	$this->_response_codes[$response], 
				'response_message' 	=>	$this->_response_messages[$response],
				'details'			=>	$response_details
			);				
		}	
	}

	/**
	 * Returns a gateway response
	 *
	 * @param 	string	can be either 'Success' or 'Failure'
	 * @param	string	the response used to grab the code / message
	 * @param	mixed	can be string or null. 
	 * @return	object	
	*/	
	private function _return_gateway_response($status, $response, $details)
	{	
		return (object) array
		(
			'type'				=>	'gateway_response',
			'status' 			=>	$status, 
			'response_code' 	=>	$this->_response_codes[$response], 
			'response_message' 	=>	$this->_response_messages[$response],
			'details'			=>	$details
		);		
	}
}
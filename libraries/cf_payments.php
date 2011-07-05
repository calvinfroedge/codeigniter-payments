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
	
	public $_ci;  //The CodeIgniter instance
	
	private $_version = '1.0.0.';	//The version
	
	private $_payment_module;  //The payment module to use
	
	private $_payment_type;	//The payment type
	
	private	$_params; //The params to use for the payment call
	
	private	$_response_codes;	//Error codes in the response object
	
	private $_response_messages;	//Response messages that can be returned to the user or logged in the application

	/**
	 * The constructor function.
	 */	
	public function __construct()
	{
		$this->_ci =& get_instance();
		$this->_response_codes = $this->_ci->config->item('response_codes');
		$this->_response_messages = $this->_ci->config->item('response_messages');		
	}

	/**
	 * Search transactions based on criteria you define.
	 *
	 * @param	string	The name of the payment module to use
	 * @param	array	The billing data to submit with the request
	 * @param	array	Other data to submit with the request (such as data required for particular payment modules)
	 * @return	object	Should return a success or failure, along with a response
	 */
	public function search_transactions($payment_module, $params)
	{
		if($response = $this->_check_inputs($payment_module, $params))
		{
			$this->_payment_type = 'search_transactions';
			$this->_params = $params;	
			$response = $this->_do_method($payment_module);		
		}	
		return $response;			
	}
	
	/**
	 * Authorize a payment.  Does not actually charge a customer.
	 *
	 * @param	string	The name of the payment module to use
	 * @param	array	The billing data to submit with the request
	 * @param	array	Other data to submit with the request (such as data required for particular payment modules)
	 * @return	object	Should return a success or failure, along with a response
	 */
	public function get_transaction_details($payment_module, $params)
	{
		if($response = $this->_check_inputs($payment_module, $params))
		{
			$this->_payment_type = 'get_transaction_details';
			$this->_params = $params;
			$response = $this->_do_method($payment_module);		
		}	
		return $response;				
	}
	 
	/**
	 * Authorize a payment.  Does not actually charge a customer.
	 *
	 * @param	string	The name of the payment module to use
	 * @param	array	The billing data to submit with the request
	 * @param	array	Other data to submit with the request (such as data required for particular payment modules)
	 * @return	object	Should return a success or failure, along with a response
	 */	
	public function authorize_payment($payment_module, $params)
	{
		if($response = $this->_check_inputs($payment_module, $params))
		{
			$this->_payment_type = 'authorize_payment';
			$this->_params = $params;
			$response = $this->_do_method($payment_module);		
		}	
		return $response;	
	}

	/**
	 * Capture a payment.  Charges a customer for an authorized transaction.
	 *
	 * @param	string	The name of the payment module to use
	 * @param	array	The billing data to submit with the request
	 * @param	string	A unique identifier given by a previously authorized payment
	 * @param	array	Other data to submit with the request (such as data required for particular payment modules)
	 * @return	object	Should return a success or failure, along with a response
	 */	
	public function capture_payment($payment_module, $params)	
	{
		if($response = $this->_check_inputs($payment_module, $params))
		{
			$this->_payment_type = 'capture_payment';
			$this->_params = $params;
			$response = $this->_do_method($payment_module);		
		}	
		return $response;	
	}
	
	/**
	 * A direct, final sale.
	 *
	 * @param	string	The name of the payment module to use
	 * @param	array	The billing data to submit with the request
	 * @param	array	Other data to submit with the request (such as data required for particular payment modules)
	 * @return	object	Should return a success or failure, along with a response
	 */			
	public function oneoff_payment($payment_module, $params)
	{
		if($response = $this->_check_inputs($payment_module, $params))
		{
			$this->_payment_type = 'oneoff_payment';
			$this->_params = $params;	
			$response = $this->_do_method($payment_module);		
		}	
		return $response;
	}

	/**
	 * Voids a previously authorized transaction
	 *
	 * @param	string	The name of the payment module to use
	 * @param	array	Identifying details for the transaction
	 * @return	object	Should return a success or failure, along with a response
	 */	
	public function void_payment($payment_module, $params)
	{
		if($response = $this->_check_inputs($payment_module, $params))
		{
			$this->_payment_type = 'void_payment';
			$this->_params = $params;
			$response = $this->_do_method($payment_module);		
		}	
		return $response;	
	}

	/**
	 * Change a particular transaction's status
	 * @param	string	The name of the payment module to use
	 * @param	array	Identifying details for the transaction & action to perform
	 * @return	object	Should return a success or failure, along with a response
	*/	
	public function change_transaction_status($payment_module, $params)
	{
		if($response = $this->_check_inputs($payment_module, $params))
		{
			$this->_payment_type = 'change_transaction_status';
			$this->_params = $params;		
			$response = $this->_do_method($payment_module);		
		}	
		return $response;	
	}

	/**
	 * Refund a particular payment
	 *
	 * @param	string	The payment module to use
	 * @param	array	Params to submit to the payment module
	 * @return	object	Should return a success or failure, along with a response
	 */		
	public function refund_payment($payment_module, $params)
	{
		if($response = $this->_check_inputs($payment_module, $params))
		{
			$this->_payment_type = 'refund_payment';
			$this->_params = $params;	
			$response = $this->_do_method($payment_module);		
		}	
		return $response;			
	}

	/**
	 * Create a recurring payment
	 *
	 * @param	string	The payment module to use
	 * @param	array	Params to submit to the payment module
	 * @return	object	Should return a success or failure, along with a response
	 */	
	public function recurring_payment($payment_module, $params)
	{
		if($response = $this->_check_inputs($payment_module, $params))
		{
			$this->_payment_type = 'recurring_payment';
			$this->_params = $params;
			$response = $this->_do_method($payment_module);		
		}	
		return $response;	
	}

	/**
	 * Get a recurring payment
	 *
	 * @param	string	The payment module to use
	 * @param	array	Params to submit to the payment module
	 * @return	object	Should return a success or failure, along with a response
	 */	
	public function get_recurring_profile($payment_module, $params)
	{
		if($response = $this->_check_inputs($payment_module, $params))
		{
			$this->_payment_type = 'get_recurring_profile';
			$this->_params = $params;	
			$response = $this->_do_method($payment_module);		
		}	
		return $response;		
	}

	/**
	 * Suspend a recurring profile.
	 *
	 * @param	string	The payment module to use
	 * @param	array	Params to submit to the payment module
	 * @return	object	Should return a success or failure, along with a response
	 */	
	public function suspend_recurring_profile($payment_module, $params)
	{
		if($response = $this->_check_inputs($payment_module, $params))
		{
			$this->_payment_type = 'suspend_recurring_profile';
			$this->_params = $params;		
			$response = $this->_do_method($payment_module);		
		}	
		return $response;	
	}	

	/**
	 * Activate a recurring profile.
	 *
	 * @param	string	The payment module to use
	 * @param	array	Params to submit to the payment module
	 * @return	object	Should return a success or failure, along with a response
	 */	
	public function activate_recurring_profile($payment_module, $params)
	{
		if($response = $this->_check_inputs($payment_module, $params))
		{
			$this->_payment_type = 'activate_recurring_profile';
			$this->_params = $params;		
			$response = $this->_do_method($payment_module);		
		}	
		return $response;	
	}

	/**
	 * Cancel a recurring profile.
	 *
	 * @param	string	The payment module to use
	 * @param	array	Params to submit to the payment module
	 * @return	object	Should return a success or failure, along with a response
	 */	
	public function cancel_recurring_profile($payment_module, $params)
	{
		if($response = $this->_check_inputs($payment_module, $params))
		{
			$this->_payment_type = 'cancel_recurring_profile';
			$this->_params = $params;	
			$response = $this->_do_method($payment_module);		
		}	
		return $response;		
	}	

	/**
	 * Bill an outstanding amount for a recurring customer.
	 *
	 * @param	string	The payment module to use
	 * @param	array	Params to submit to the payment module
	 * @return	object	Should return a success or failure, along with a response
	 */		
	public function recurring_bill_outstanding($payment_module, $params)
	{
		if($response = $this->_check_inputs($payment_module, $params))
		{
			$this->_payment_type = 'recurring_bill_outstanding';
			$this->_params = $params;	
			$response = $this->_do_method($payment_module);		
		}	
		return $response;		
	}

	/**
	 * Update a recurring billing customer
	 *
	 * @param	string	The payment module to use
	 * @param	array	Params to submit to the payment module
	 * @return	object	Should return a success or failure, along with a response
	 */	
	public function update_recurring_profile($payment_module, $params)
	{
		if($response = $this->_check_inputs($payment_module, $params))
		{
			$this->_payment_type = 'update_recurring_profile';
			$this->_params = $params;
			$response = $this->_do_method($payment_module);		
		}	
		return $response;	
	}

	/**
	 * Try a method
	 *
	 * @param	string	The payment module to use
	 * @param	string	The type of method being used.  Can be for making payments or getting statuses / profiles.
	 * @param	array	Params to submit to the payment module
	 * @return	object	Should return a success or failure, along with a response
	 */		
	private function _do_method($payment_module)
	{
		if(! $module = $this->_load_module($payment_module))
		{
			log_message('error', $this->_response_messages['not_a_module']);
			$response = (object) array('status' => 'failure', 'response_code' => $this->_response_codes['not_a_module'], 'response_message' => $this->_response_messages['not_a_module']);
		}
					
		$do = new $payment_module;
		
		$method = $this->_payment_type;
		
		$exists = $this->_method_exists($payment_module, $method);
		
		($exists !== TRUE)
		? $response = $exists 
		: $response = $response = $do->$method($this->_params);
		
		return $response;
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
			$response = false;
		}
		ob_start();
		include $module;
		$response = ob_get_clean();
		
		return $response;
	}

	/**
	 * Check to see if a given method exists
	 *
	 * @param	string	The payment module to load
	 * @return	mixed	Will return bool if file is not found.  Will return file as object if found.
	 */			
	private function _method_exists($object, $method)
	{
		if(!method_exists($object, $method))
		{
			$response = (object) array('status' => 'failure', 'response_code' => $this->_response_codes['not_a_method'], 'response_message' => $this->_response_messages['not_a_method'].' '.$object.'->'.$method);
			log_message('error', $this->_response_messages['not_a_method'].' '.$object.'->'.$method);
		}	
		else
		{
			$response = TRUE;
		}
		
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
		
		if ($this->_check_datatypes($expected_datatypes) === FALSE)
		{
			log_message('error', $this->_response_messages['invalid_input']);		
			$response = (object) array('status' => 'failure', 'response_code' => $this->_response_codes['invalid_input'], 'response_message' => $this->_response_messages['invalid_input']);
		}
		else
		{
			$response = true;
		}
		
		return $response;	
	}

	/**
	 * Make sure data types are as expected
	 *
	 * @param	array	array of params to check to ensure proper datatype
	 * @return	mixed	Will return true if all pass.  Will return an object if datatypes are bad.
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
		
		if(count($invalids) > 0)

			return false;
		
		return true;
	}

	/**
	 * Remove key=>value pairs with empty values
	 *
	 * @param	array	array of key=>value pairs to check
	 * @return	array	Will return filtered array
	 */
	protected function filter_values($array)
	{	
		foreach($array as $key=>$value)
		{
			if($value == null)
				unset($array[$key]);
		}
		return $array;
	}	

}
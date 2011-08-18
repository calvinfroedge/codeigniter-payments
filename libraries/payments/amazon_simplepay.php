<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Amazon_SimplePay
{	
	/**
	 *	The payments object
	*/
	public $payments;
	
	/**
	 * The button to use
	*/
	private $_button;
	
	/**
	 * An array for storing all settings
	*/	
	private $_settings = array();

	/**
	 * The HTML form to return
	*/	
	private $_form_string = array();	

	/**
	 * The final string to be sent in the http query
	*/	
	private $_http_query;	

	private $_http_method = "POST";
	
	private $_algoritm;
	
	/**
	 * Constructor method
	*/		
	public function __construct($payments)
	{
		$this->payments = $payments;
		$this->_api_endpoint = $this->payments->ci->config->item('api_endpoint');		
		$this->_api_settings = array(
			'immediateReturn'	=> $this->payments->ci->config->item('immediate_return'),
			'collectShippingAddress'	=> $this->payments->ci->config->item('collect_shipping_address'),
			'signatureVersion' => $this->payments->ci->config->item('api_signature_version'),
			'signatureMethod'	=> $this->payments->ci->config->item('api_signature_method'),	
			'accessKey' => $this->payments->ci->config->item('api_access_key'),
			'isDonationWidget' => $this->payments->ci->config->item('donation_widget'),
			'amazonPaymentsAccountId' => $this->payments->ci->config->item('api_account_id'),
			'ipnUrl' => $this->payments->ci->config->item('ipn_url'),
			'returnUrl' => $this->payments->ci->config->item('return_url'),
			'cobrandingStyle' => 'logo',
			'abandonUrl' => $this->payments->ci->config->item('abandon_url'),
		);
		$this->_secret_key = $this->payments->ci->config->item('api_access_secret_key');
		
		$this->_algorithm = $this->_api_settings['signatureMethod'];
		
		$custom_button = $this->payments->ci->config->item('custom_button');
		if(!empty($custom_button))
		{
			$this->_button = $custom_button;
		}
		else
		{	
			$choices = explode('.', $this->payments->ci->config->item('button_choice'));
			$style = $choices[0];
			$color = $choices[1];
			$size = $choices[2];
			
			$button = $this->payments->ci->config->item('button_choices');
			$this->_button = $button[$style][$color][$size];
		}
		
	}

	/**
	 * Build the button
	 * @param	array	An array of payment params, sent from your controller / library
	 * @param	string	The capture method should be set to 1 or 0 (for true or false)
	 * @param	bool	Either true or false.  If true, transaction is recurring.
	 * @return	object	The response from the payment gateway
	*/
	private function _build_button($params, $capture_method, $recurring = FALSE)
	{
		$fields = array();
		if(isset($params['amt']))
		{
			$fields['amount'] = "USD " . $params['amt'];
		}
		
		if(isset($params['desc']))
		{
			$fields['description'] = $params['desc'];
		}
		
		if($recurring)
		{
			if(!empty($params['trial_amt']) AND !empty($params['trial_billing_cycles']))
			{
				$fields['noOfPromotionTransactions'] = $params['trial_billing_cycles'];
				$fields['promotionAmount'] = $params['trial_amt'];
			}
			
			if(!empty($params['billing_frequency']) AND !empty($params['billing_period']))
			{
				$params['billing_period'] = strtolower($params['billing_period']);
				$fields['recurringFrequency'] = $params['billing_frequency'] .' '. $params['billing_period'];
			}		
						
			if(!empty($params['profile_start_date']))
			{
				$fields['recurringStartDate'] = $params['profile_start_date'];
			}
			
			if(!empty($params['total_billing_cycles']))
			{
				$fields['subscriptionPeriod'] = $params['total_billing_cycles'].' '.$params['billing_period'];
			}		
		}
		
		$this->_api_settings['processImmediate'] = $capture_method;
		
		$this->payments->ci->load->helper('form');
		
		$fields = array_merge($fields, $this->_api_settings);
		
		$submit = array(
        	'type'        => 'image',
        	'src'         => $this->_button,
        	'border'      => '0'
        );
        
        $to_sign = parse_url($this->_api_endpoint);
        $signature = SignatureUtils::signParameters($fields, $this->_secret_key, $this->_http_method, $to_sign['host'], $to_sign['path'], $this->_algorithm);
        
		$string = "";
		$string .= form_open($this->_api_endpoint);
		$string .= form_hidden($fields);
		$string .= form_hidden(array('signature' => $signature));
		$string .= form_input($submit);
		$string .= form_close();
		
		return $string;
	}


	/**
	 * Make a oneoff payment
	 * @param	array	An array of payment params, sent from your controller / library
	 * @return	object	The response from the payment gateway
	*/	
	public function amazon_simplepay_oneoff_payment_button($params)
	{
		$form = $this->_build_button($params, '1');	
		return $form;
	}

	/**
	 * Authorize a oneoff payment
	 * @param	array	An array of payment params, sent from your controller / library
	 * @return	object	The response from the payment gateway
	*/	
	public function amazon_simplepay_authorize_payment_button($params)
	{
		$form = $this->_build_button($params, '0');	
		return $form;
	}

	/**
	 * Authorize a oneoff payment
	 * @param	array	An array of payment params, sent from your controller / library
	 * @return	object	The response from the payment gateway
	*/	
	public function amazon_simplepay_recurring_payment_button($params)
	{
		$form = $this->_build_button($params, '0', TRUE);	
		return $form;
	}	
	
	public function amazon_simplepay_cancel_recurring_profile($params)
	{
		/*Working on it!
		$string = 'https://fps.sandbox.amazonaws.com?Action=CancelSubscriptionAndRefund&AWSAccessKeyId=AKIAIIFXJCFIHITREP4Q&CallerReference=CallerReference07&CancelReason=MyWish&RefundAmount.CurrencyCode=USD&RefundAmount.Value=1&SignatureMethod=HmacSHA256&SignatureVersion=2&Signature=44dEwwa6P7C9iI94U/ra33Pn2TS9ie8MkfcvIRyLh7M=&SubscriptionId=17d62772-c53e-4bdb-9667-65d7b7841cfc&Timestamp=2009-10-06T08%3A05%3A13.296Z&Version=2008-09-17';

		//$request = $this->payments->gateway_request($string);var_dump($request);exit;
//$qry_str = "$string";
$ch = curl_init();

// Set query data here with the URL
curl_setopt($ch, CURLOPT_URL, $string); 

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, '3');
$content = trim(curl_exec($ch));
curl_close($ch);
var_dump($content);exit;	
	
		//$request = $this->payments->gateway_request($string);
		//echo '<hr /><h2>Response:</h2>';
		//var_dump($this->payments->gateway_request($string));exit;*/
	}
}

/** 
 *  PHP Version 5
 *
 *  @category    Amazon
 *  @package     Amazon_FPS
 *  @copyright   Copyright 2008-2010 Amazon Technologies, Inc.
 *  @link        http://aws.amazon.com
 *  @license     http://aws.amazon.com/apache2.0  Apache License, Version 2.0
 *  @version     2008-09-17
 */

class SignatureUtils
{ 

    /**
     * Computes RFC 2104-compliant HMAC signature for request parameters
     * Implements AWS Signature, as per following spec:
     *
     * In Signature Version 2, string to sign is based on following:
     *
     *    1. The HTTP Request Method followed by an ASCII newline (%0A)
     *    2. The HTTP Host header in the form of lowercase host, followed by an ASCII newline.
     *    3. The URL encoded HTTP absolute path component of the URI
     *       (up to but not including the query string parameters);
     *       if this is empty use a forward '/'. This parameter is followed by an ASCII newline.
     *    4. The concatenation of all query string components (names and values)
     *       as UTF-8 characters which are URL encoded as per RFC 3986
     *       (hex characters MUST be uppercase), sorted using lexicographic byte ordering.
     *       Parameter names are separated from their values by the '=' character
     *       (ASCII character 61), even if the value is empty.
     *       Pairs of parameter and values are separated by the '&' character (ASCII code 38).
     *
     */
    /**
	* This function call appropriate functions for calculating signature
	* @param array $parameters request parameters
	* @param key - Secret key 
	* @param httpMethod - httpMethos used
	* @param host - Host 
	* @requestURi -  Path
		
     */		

    public static function signParameters(array $parameters, $key, $httpMethod, $host, $requestURI,$algorithm) {
        $stringToSign = null;
        $stringToSign = self::_calculateStringToSignV2($parameters, $httpMethod, $host, $requestURI);
        return self::_sign($stringToSign, $key, $algorithm);
    }

    /**
     * Calculate String to Sign for SignatureVersion 2
     * @param array $parameters request parameters
     * @return String to Sign
     */
    private static function _calculateStringToSignV2(array $parameters, $httpMethod, $hostHeader, $requestURI) {
        if ($httpMethod == null) {
        	throw new Exception("HttpMethod cannot be null");
        }
        $data = $httpMethod;
        $data .= "\n";
        
        if ($hostHeader == null) {
        	$hostHeader = "";
        } 
        $data .= $hostHeader;
        $data .= "\n";
        
        if (!isset ($requestURI)) {
        	$requestURI = "/";
        }
		$uriencoded = implode("/", array_map(array("SignatureUtils", "_urlencode"), explode("/", $requestURI)));
        $data .= $uriencoded;
        $data .= "\n";
        
        uksort($parameters, 'strcmp');
        $data .= self::_getParametersAsString($parameters);
        return $data;
    }

    private static function _urlencode($value) {
		return str_replace('%7E', '~', rawurlencode($value));
    }

    /**
     * Convert paremeters to Url encoded query string
     */
    public static function _getParametersAsString(array $parameters) {
        $queryParameters = array();
        foreach ($parameters as $key => $value) {
            $queryParameters[] = $key . '=' . self::_urlencode($value);
        }
        return implode('&', $queryParameters);
    }

    /**
     * Computes RFC 2104-compliant HMAC signature.
     */
    private static function _sign($data, $key, $algorithm) {
        if ($algorithm === 'HmacSHA1') {
            $hash = 'sha1';
        } else if ($algorithm === 'HmacSHA256') {
            $hash = 'sha256';
        } else {
            throw new Exception ("Non-supported signing method specified");
        }
        return base64_encode(
            hash_hmac($hash, $data, $key, true)
        );
    }
}
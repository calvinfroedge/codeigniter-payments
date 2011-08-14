<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class QuickBooksMS_Response extends QuickBooksMS
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
		
		$signon = $as_array['SignonMsgsRs']['SignonDesktopRs'];
		$response = $as_array['QBMSXMLMsgsRs'];
		$result = '';
		$message = '';
		$identifier = '';
		
		if(isset($response['CustomerCreditCardChargeRs']))
		{
			$result = $response['CustomerCreditCardChargeRs']['@attributes']['statusCode'];
			$message = $response['CustomerCreditCardChargeRs']['@attributes']['statusMessage'];	
			$identifier = $response['CustomerCreditCardChargeRs']['CreditCardTransID'];	
		}

		if(isset($response['CustomerCreditCardAuthRs']))
		{
			$result = $response['CustomerCreditCardAuthRs']['@attributes']['statusCode'];
			$message = $response['CustomerCreditCardAuthRs']['@attributes']['statusMessage'];	
			$identifier = $response['CustomerCreditCardAuthRs']['CreditCardTransID'];	
		}
	
		if(isset($response['CustomerCreditCardCaptureRs']))
		{
			$result = $response['CustomerCreditCardCaptureRs']['@attributes']['statusCode'];
			$message = $response['CustomerCreditCardCaptureRs']['@attributes']['statusMessage'];	
			$identifier = $response['CustomerCreditCardCaptureRs']['CreditCardTransID'];	
		}
		
		if(isset($response['CustomerCreditCardTxnVoidRs']))
		{
			$result = $response['CustomerCreditCardTxnVoidRs']['@attributes']['statusCode'];
			$message = $response['CustomerCreditCardTxnVoidRs']['@attributes']['statusMessage'];	
			$identifier = $response['CustomerCreditCardTxnVoidRs']['CreditCardTransID'];		
		}
		
		if(isset($response['CustomerCreditCardTxnVoidOrRefundRs']))
		{
			$result = $response['CustomerCreditCardTxnVoidOrRefundRs']['@attributes']['statusCode'];
			$message = $response['CustomerCreditCardTxnVoidOrRefundRs']['@attributes']['statusMessage'];
			if(isset($response['CustomerCreditCardTxnVoidOrRefundRs']['CreditCardTransID']))
			{	
				$identifier = $response['CustomerCreditCardTxnVoidOrRefundRs']['CreditCardTransID'];		
			}
		}		
			
		$details->gateway_response = $as_array;
		
		if($result === '0')
		{ //Transaction was successful
			$details->identifier = $identifier;
			
			$details->timestamp = $signon['ServerDateTime'];
			
			return $this->payments->return_response(
				'Success',
				$this->payments->payment_type.'_success',
				'gateway_response',
				$details
			);			
		}
		else
		{ //Transaction failed
			$details->reason = $message;

			return $this->payments->return_response(
				'Failure',
				$this->payments->payment_type.'_gateway_failure',
				'gateway_response',
				$details
			);				
		}
	}
	
}
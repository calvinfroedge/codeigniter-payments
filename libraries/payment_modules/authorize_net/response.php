<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Authorize_Net_Response extends Authorize_Net
{

	private $_raw_response;
	
	private static $_processed_response;
	
	private $_response_codes;
	
	private static $_xml_response;

	private static $_status_code_messages = array(
          '1' => 'This transaction has been approved.',
          '2' => 'This transaction has been declined.',
          '3' => 'This transaction has been declined.',
          '4' => 'This transaction has been declined.',
          '5' => 'A valid amount is required.',
          '6' => 'The credit card number is invalid.',
          '7' => 'The credit card expiration date is invalid.',
          '8' => 'The credit card has expired.',
          '9' => 'The ABA code is invalid.',
          '10' => 'The account number is invalid.',
          '11' => 'A duplicate transaction has been submitted.',
          '12' => 'An authorization code is required but not present.',
          '13' => 'The merchant Login ID is invalid or the account is inactive.',
          '14' => 'The Referrer or Relay Response URL is invalid.',
          '15' => 'The transaction ID is invalid.',
          '16' => 'The transaction was not found.',
          '17' => 'The merchant does not accept this type of credit card.',
          '18' => 'ACH transactions are not accepted by this merchant.',
          '19' => 'An error occurred during processing. Please try again in 5 minutes.',
          '20' => 'An error occurred during processing. Please try again in 5 minutes.',
          '21' => 'An error occurred during processing. Please try again in 5 minutes.',
          '22' => 'An error occurred during processing. Please try again in 5 minutes.',
          '23' => 'An error occurred during processing. Please try again in 5 minutes.',
          '24' => 'The Nova Bank Number or Terminal ID is incorrect. Call Merchant Service Provider.',
          '25' => 'An error occurred during processing. Please try again in 5 minutes.',
          '26' => 'An error occurred during processing. Please try again in 5 minutes.',
          '27' => 'The transaction resulted in an AVS mismatch. The address provided does not match billing address of cardholder.',
          '28' => 'The merchant does not accept this type of credit card.',
          '29' => 'The PaymentTech identification numbers are incorrect. Call Merchant Service Provider.',
          '30' => 'The configuration with the processor is invalid. Call Merchant Service Provider.',
          '31' => 'The FDC Merchant ID or Terminal ID is incorrect. Call Merchant Service Provider.',
          '32' => 'The merchant password is invalid or not present.',
          '33' => 'Missing required field',
          '34' => 'The VITAL identification numbers are incorrect. Call Merchant Service Provider.',
          '35' => 'An error occurred during processing. Call Merchant Service Provider.',
          '36' => 'The authorization was approved, but settlement failed.',
          '37' => 'The credit card number is invalid.',
          '38' => 'The Global Payment System identification numbers are incorrect. Call Merchant Service Provider.',
          '39' => 'The supplied currency code is either invalid, not supported, not allowed for this merchant or doesn\'t have an exchange rate.',
          '40' => 'This transaction must be encrypted.',
          '41' => 'FraudScreen.net fraud score is higher than threshold set by merchant',
          '42' => 'There is missing or invalid information in a required field.',
          '43' => 'The merchant was incorrectly set up at the processor. Call your Merchant Service Provider.',
          '44' => 'This transaction has been declined. Card Code filter error!',
          '45' => 'This transaction has been declined. Card Code / AVS filter error!',
          '46' => 'Your session has expired or does not exist. You must log in to continue working.',
          '47' => 'The amount requested for settlement may not be greater than the original amount authorized.',
          '48' => 'This processor does not accept partial reversals.',
          '49' => 'A transaction amount greater than $99,999 will not be accepted.',
          '50' => 'This transaction is awaiting settlement and cannot be refunded.',
          '51' => 'The sum of all credits against this transaction is greater than the original transaction amount.',
          '52' => 'The transaction was authorized, but the client could not be notified; the transaction will not be settled.',
          '53' => 'The transaction type was invalid for ACH transactions.',
          '54' => 'The referenced transaction does not meet the criteria for issuing a credit.',
          '55' => 'The sum of credits against the referenced transaction would exceed the original debit amount.',
          '56' => 'This merchant accepts ACH transactions only; no credit card transactions are accepted.',
          '57' => 'An error occurred in processing. Please try again in 5 minutes.',
          '58' => 'An error occurred in processing. Please try again in 5 minutes.',
          '59' => 'An error occurred in processing. Please try again in 5 minutes.',
          '60' => 'An error occurred in processing. Please try again in 5 minutes.',
          '61' => 'An error occurred in processing. Please try again in 5 minutes.',
          '62' => 'An error occurred in processing. Please try again in 5 minutes.',
          '63' => 'An error occurred in processing. Please try again in 5 minutes.',
          '64' => 'The referenced transaction was not approved.',
          '65' => 'This transaction has been declined.',
          '66' => 'The transaction did not meet gateway security guidelines.',
          '67' => 'The given transaction type is not supported for this merchant.',
          '68' => 'The version parameter is invalid.',
          '69' => 'The transaction type is invalid. The value submitted in x_type was invalid.',
          '70' => 'The transaction method is invalid.',
          '71' => 'The bank account type is invalid.',
          '72' => 'The authorization code is invalid.',
          '73' => 'The driver\'s license date of birth is invalid.',
          '74' => 'The duty amount is invalid.',
          '75' => 'The freight amount is invalid.',
          '76' => 'The tax amount is invalid.',
          '77' => 'The SSN or tax ID is invalid.',
          '78' => 'The Card Code (CVV2/CVC2/CID) is invalid.',
          '79' => 'The driver\'s license number is invalid.',
          '80' => 'The driver\'s license state is invalid.',
          '81' => 'The merchant requested an integration method not compatible with the AIM API.',
          '82' => 'The system no longer supports version 2.5; requests cannot be posted to scripts.',
          '83' => 'The requested script is either invalid or no longer supported.',
          '84' => 'This reason code is reserved or not applicable to this API.',
          '85' => 'This reason code is reserved or not applicable to this API.',
          '86' => 'This reason code is reserved or not applicable to this API.',
          '87' => 'This reason code is reserved or not applicable to this API.',
          '88' => 'This reason code is reserved or not applicable to this API.',
          '89' => 'This reason code is reserved or not applicable to this API.',
          '90' => 'This reason code is reserved or not applicable to this API.',
          '91' => 'Version 2.5 is no longer supported.',
          '92' => 'The gateway no longer supports the requested method of integration.',
          '93' => 'A valid country is required.',
          '94' => 'The shipping state or country is invalid.',
          '95' => 'A valid state is required.',
          '96' => 'This country is not authorized for buyers.',
          '97' => 'This transaction cannot be accepted.',
          '98' => 'This transaction cannot be accepted.',
          '99' => 'This transaction cannot be accepted.',
          '100' => 'The eCheck type is invalid.',
          '101' => 'The given name on the account and/or the account type does not match the actual account.',
          '102' => 'This request cannot be accepted.',
          '103' => 'This transaction cannot be accepted.',
          '104' => 'This transaction is currently under review.',
          '105' => 'This transaction is currently under review.',
          '106' => 'This transaction is currently under review.',
          '107' => 'This transaction is currently under review.',
          '108' => 'This transaction is currently under review.',
          '109' => 'This transaction is currently under review.',
          '110' => 'This transaction is currently under review.',
          '111' => 'A valid billing country is required.',
          '112' => 'A valid billing state/provice is required.',
          '116' => 'The authentication indicator is invalid.',
          '117' => 'The cardholder authentication value is invalid.',
          '118' => 'The combination of authentication indicator and cardholder authentication value is invalid.',
          '119' => 'Transactions having cardholder authentication values cannot be marked as recurring.',
          '120' => 'An error occurred during processing. Please try again.',
          '121' => 'An error occurred during processing. Please try again.',
          '122' => 'An error occurred during processing. Please try again.',
          '127' => 'The transaction resulted in an AVS mismatch. The address provided does not match billing address of cardholder.',
          '141' => 'This transaction has been declined.',
          '145' => 'This transaction has been declined.',
          '152' => 'The transaction was authorized, but the client could not be notified; the transaction will not be settled.',
          '165' => 'This transaction has been declined.',
          '170' => 'An error occurred during processing. Please contact the merchant.',
          '171' => 'An error occurred during processing. Please contact the merchant.',
          '172' => 'An error occurred during processing. Please contact the merchant.',
          '173' => 'An error occurred during processing. Please contact the merchant.',
          '174' => 'The transaction type is invalid. Please contact the merchant.',
          '175' => 'The processor does not allow voiding of credits.',
          '180' => 'An error occurred during processing. Please try again.',
          '181' => 'An error occurred during processing. Please try again.',
          '200' => 'This transaction has been declined.',
          '201' => 'This transaction has been declined.',
          '202' => 'This transaction has been declined.',
          '203' => 'This transaction has been declined.',
          '204' => 'This transaction has been declined.',
          '205' => 'This transaction has been declined.',
          '206' => 'This transaction has been declined.',
          '207' => 'This transaction has been declined.',
          '208' => 'This transaction has been declined.',
          '209' => 'This transaction has been declined.',
          '210' => 'This transaction has been declined.',
          '211' => 'This transaction has been declined.',
          '212' => 'This transaction has been declined.',
          '213' => 'This transaction has been declined.',
          '214' => 'This transaction has been declined.',
          '215' => 'This transaction has been declined.',
          '216' => 'This transaction has been declined.',
          '217' => 'This transaction has been declined.',
          '218' => 'This transaction has been declined.',
          '219' => 'This transaction has been declined.',
          '220' => 'This transaction has been declined.',
          '221' => 'This transaction has been declined.',
          '222' => 'This transaction has been declined.',
          '223' => 'This transaction has been declined.',
          '224' => 'This transaction has been declined.',
          '243' => 'Recurring billing is not allowed for this eCheck.Net type',
          '244' => 'This eCheck.Net type is not allowed for this Bank Account Type.',
          '245' => 'This eCheck.Net type is not allowed when using the payment gateway hosted payment form.',
          '246' => 'This eCheck.Net type is not allowed.',
          '247' => 'This eCheck.Net type is not allowed.',
          '250' => 'This transaction has been declined.',
          '251' => 'This transaction has been declined.',
          '252' => 'Your order has been received. Thank you for your business!',
          '253' => 'Your order has been received. Thank you for your business!',
          '254' => 'This transaction has been declined.',
          '261' => 'An error occurred during processing. Please try again'
    );
    
	public function __construct()
	{
	
	}
	
	/**
	 * Parse the response from the server
	 *
	 * @param	array
	 * @return	object
	 */		
	public function parse_response($delimiter, $response, $xml)
	{
		if($xml)
		{
			self::_handle_xml($response);
			return self::_xml_response();
		}
		else
		{
			$this->_raw_response = explode($delimiter,urldecode($response));
			self::_map_response();
			return self::_reg_response();						
		}
	}
	
	private function _map_response()
	{
	
		self::$_processed_response = array(
			'response_code' => $this->_raw_response[0], 
			'response_subcode'	=> $this->_raw_response[1],
			'response_reason_code' => $this->_raw_response[2],
			'response_reason_text' => $this->_raw_response[3], 
			'authorization_code' => $this->_raw_response[4], 
			'avs_response' => $this->_raw_response[5], 
			'transaction_id' => $this->_raw_response[6],
			'invoice_number' => $this->_raw_response[7], 
			'description' => $this->_raw_response[8], 
			'amount' => $this->_raw_response[9], 
			'method' => $this->_raw_response[10], 
			'transaction_type' => $this->_raw_response[11],
			'customer_id' => $this->_raw_response[12],
			'first_name' => $this->_raw_response[13], 
			'last_name' => $this->_raw_response[14], 
			'company' => $this->_raw_response[15], 
			'address' => $this->_raw_response[16],
			'city' => $this->_raw_response[17], 
			'state' => $this->_raw_response[18], 
			'zip' => $this->_raw_response[19], 
			'code' => $this->_raw_response[20], 
			'country' => $this->_raw_response[21], 
			'phone' => $this->_raw_response[22], 
			'fax' => $this->_raw_response[23], 
			'email_address' => $this->_raw_response[24],
			'ship_to_first_name' => $this->_raw_response[25], 
			'ship_to_last_name' => $this->_raw_response[26], 
			'ship_to_company' => $this->_raw_response[27], 
			'ship_to_address' => $this->_raw_response[28], 
			'ship_to_city' => $this->_raw_response[29], 
			'ship_to_state' => $this->_raw_response[30],
			'ship_to_zip_code' => $this->_raw_response[31], 
			'ship_to_country' => $this->_raw_response[32], 
			'tax' => $this->_raw_response[33], 
			'duty' => $this->_raw_response[34], 
			'freight' => $this->_raw_response[35], 
			'tax_exempt' => $this->_raw_response[36],
			'purchase_order_number' => $this->_raw_response[37],
			'md5_hash' => $this->_raw_response[38],
			'card_code_response' => $this->_raw_response[39], 
			'cardholder_authentication_verification_response' => $this->_raw_response[40],
			'account_number' => $this->_raw_response[41],
			'card_type' => $this->_raw_response[42], 
			'split_tender_id' => $this->_raw_response[43], 
			'requested_amount' => $this->_raw_response[44], 
			'balance_on_card' => $this->_raw_response[45]		
		);
	
	}
	
	private function _handle_xml($response)
	{
		self::$_xml_response = $response;
		self::$_processed_response = array();
        self::$_processed_response['response_code'] = self::_parse_xml('<resultCode>', '</resultCode>');
        self::$_processed_response['code'] = self::_parse_xml('<code>', '</code>');
        self::$_processed_response['message'] = self::_parse_xml('<text>', '</text>');
		self::$_processed_response['identifier'] = self::_parse_xml('<subscriptionId>', '</subscriptionId>');
	}
	
	private function _parse_xml($start, $end)
	{		
        $start_position = strpos(self::$_xml_response, $start) + strlen($start);
        $end_position   = strpos(self::$_xml_response, $end);
        return substr(self::$_xml_response, $start_position, ($end_position - $start_position));		
	}
	
	private function _reg_response()
	{
		if (self::$_processed_response['response_code'] == 1)
		
			$status = 'Success';
			
		if (self::$_processed_response['response_code'] == 2)
		
			$status = 'Failure';

		if (self::$_processed_response['response_code'] == 3)
		
			$status = 'Failure';

		if (self::$_processed_response['response_code'] == 4)
		
			$status = 'Failure';						
		
		return (object) array('status' => $status, 'response' => self::$_status_code_messages[self::$_processed_response['response_code']]);
	}

	private function _xml_response()
	{
		if(self::$_processed_response['response_code'] == 'Ok')
		{
			$status = 'Success';
		}
		else
		{
			$status = 'Failure';
		}
		
		if($status == 'Success')
		{
			return (object) array('status' => $status, 'response' => self::$_processed_response['message'], 'identifier' => self::$_processed_response['identifier']);
		}
		if($status == 'Failure')
		{
			return (object) array('status' => $status, 'response' => self::$_processed_response['message']);
		}
	}
}
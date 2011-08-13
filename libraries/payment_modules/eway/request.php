<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Eway_Request extends Eway
{

	public function __construct()
	{
		
	}
	
	public function make_request()
	{
		// create a new cURL resource
		$curl = curl_init();
		
		// set URL
		curl_setopt($curl, CURLOPT_URL, $this->_api_endpoint);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
		curl_setopt($curl, CURLOPT_HEADER, 1);
		
		curl_setopt($curl, CURLOPT_POSTFIELDS, $this->_http_query);
		//curl_setopt($curl, CURLOPT_PORT, 8645);
		// set to return the data as a string
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        		
		// Run the query and get a response
		$response = curl_exec($curl);
		
		// close cURL resource, and free up system resources
		curl_close($curl);
		
		// Return the response
		return $response;			
	}

}
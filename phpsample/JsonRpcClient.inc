<?php

class JsonRpcClient {
	private $endpoint;
	private $cookies;
	private $hostHeader;
	private $Header;

	public function __construct($endpoint, $cookies, $hostHeader = NULL) {
		$this->endpoint = $endpoint;
		$this->cookies = $cookies;
		$this->hostHeader = $hostHeader;
	}

	public function __setHeader($h) {
		$this->Header = $h;
  }

	public function __call($method, $arguments) {
		// Set up the JSON-RPC request payload.
		$request = new stdclass();
		$request->method = $method;
		$request->params = $arguments;

		// Init the cURL handler.
		$ch = curl_init($this->endpoint);
		curl_setopt($ch, CURLOPT_POST, 1);

		// Comment out to debug JSON request
/*
		echo "BEGIN JSON REQUEST\n";
		echo json_encode($request) . "\n";
		echo "END JSON REQUEST\n";
*/

/*
		// An example to override any request
		$json_request = json_encode($request);
		if(strpos($json_request, "SetExtensionData") === FALSE) {
		        // Do nothing...
		} else {
		        // Replace request;
		        $json_request = '{"method":"SetExtensionData","params":[{"setdata":{"extensionId":"ext1","instanceId":"i1","version":"1","expiration":"0","key":"san",value:"1"}}]}';
		}
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $json_request);
*/

 	//	curl_setopt($ch, CURLOPT_HEADER,true);

		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
		curl_setopt($ch, CURLOPT_COOKIE, $this->cookies);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if(!is_null($this->hostHeader)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host: $this->hostHeader"));
		}
		if(!is_null($this->Header)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->Header);
		}

		// Execute the request.
		$rawresponse = curl_exec($ch);

		$errH["raw"] = $rawresponse;

		// Get the response information.
		$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$responseContentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
/***
		$h = explode("\n",$rawresponse);
                
		$errH["http_code"] = $responseCode;

                if ($responseCode == 401) {
                        foreach ($h as $str) {
                           if (stripos($str,"oauth_problem=")) {
                              
				$errH["err_msg"] = $str;
                                break;
                           }
                        }
                } else {

			$errH["err_msg"] = $rawresponse;
		}
**/		
                return $errH;

		if($responseContentType === "application/json") {

			if($rawresponse == "") {
				throw new JsonRpcException("Empty response", 0, "");
			}

			// Decode the response.
			$response = json_decode($rawresponse);

			// Close the cURL handler.
			curl_close($ch);

			// Make sure the call was successful.
			if(property_exists($response, "error") && !is_null($response->error)) {
				throw new JsonRpcException($response->error->message, 
						$response->error->code, $response->error->detail);
			}

			return $response->result;
		}
		else {
			throw new JsonRpcException("Bad response from Cascade: HTTP $responseCode, Content-Type: $responseContentType, Endpoint: $this->endpoint", "BadCascadeResponse", NULL);
		}
	}

	public function getHostHeader()
	{
		return $this->hostHeader;
	}
	
	public function getCookies()
	{
		return $this->cookies;
	}
}

class JsonRpcException extends Exception {
	private $errorCode;
	private $detail;

	public function __construct($message, $code, $detail) {
		parent::__construct($message);
		$this->errorCode = $code;
		$this->detail = $detail;
	}

	public function getErrorCode() {
		return $this->errorCode;
	}

	public function getDetail() {
		return $this->detail;
	}
}

?>

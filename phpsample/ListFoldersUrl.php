<?php

//Libraries used for accessing YMail api's.
require_once 'OAuth.php';
require_once 'JsonRpcClient.inc';

//Endpoint for Yahoo mail WSDL
$endPoint = 'http://mail.yahooapis.com/ws/mail/v1.1';
//OAuth Endpoint
$OAuthEndPoint = 'https://api.login.yahoo.com/oauth/v2';
//ConsumerKey and Secret keys are both given as command line Input and 
//You can get these keys from YDN.
$OAuthConsumerKey = $argv[2];
$OAuthConsumerSecret = $argv[3];

// see http://developer.yahoo.com/oauth/guide/oauth-auth-flow.html
$signature = new OAuthSignatureMethod_HMAC_SHA1();

// 1) Get Request Token
$request = new OAuthRequest('GET', "$OAuthEndPoint/get_request_token", array(
	'oauth_nonce'=>mt_rand(),
	'oauth_timestamp'=>time(),
	'oauth_version'=>'1.0',
	'oauth_signature_method'=>'PLAINTEXT', //'HMAC-SHA1'
	'oauth_consumer_key'=>$OAuthConsumerKey,
	'oauth_callback'=>'oob'));
$url = $request->to_url()."&oauth_signature=$OAuthConsumerSecret%26";
//$url = $request->to_url()."&oauth_signature=".$signature->build_signature( $request, new OAuthConsumer('', $OAuthConsumerSecret), NULL);
$ch = curl_init();
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt( $ch, CURLOPT_URL, $url );
$resp = curl_exec( $ch );
curl_close($ch);
parse_str($resp,$tokens);
$oauth_token = $tokens['oauth_token'];
$oauth_token_secret = $tokens['oauth_token_secret'];

if (!$oauth_token || !$oauth_token_secret) {
	throw new Exception($resp);	
}

// 2) Get User Auth
echo " Open this Url in your browser ->> $OAuthEndPoint/request_auth?oauth_token=$oauth_token \n";
echo " This should be provided to end users of your application.End users should provide their 
'Username' and 'Password' and sign-in which means they authorize your app. On successful login the end users will see a code in the page \n";
echo " This code is the oauth_token which Yahoo returns to your app \n";
echo " Enter the code here: ";
$oauth_verifier = trim(fgets(STDIN));

// 3) Get Access Token
$request = new OAuthRequest('GET', "$OAuthEndPoint/get_token", array(
	'oauth_nonce'=>mt_rand(),
	'oauth_timestamp'=>time(),
	'oauth_version'=>'1.0',
	'oauth_signature_method'=>'PLAINTEXT', //'HMAC-SHA1'
	'oauth_consumer_key'=>$OAuthConsumerKey,
	'oauth_token'=>$oauth_token,
	'oauth_verifier'=>$oauth_verifier));
$url = $request->to_url()."&oauth_signature=$OAuthConsumerSecret%26$oauth_token_secret";
	//.$signature->build_signature( $request, new OAuthConsumer('', $OAuthConsumerSecret), new OAuthToken('', $oauth_token_secret));
$ch = curl_init();
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt( $ch, CURLOPT_URL, $url );
$resp = curl_exec( $ch );
curl_close($ch);
unset($oauth_token);
unset($oauth_token_secret);
parse_str($resp);
if (!$oauth_token || !$oauth_token_secret) {
	throw new Exception($resp);	
}


// 4) Get Authorization header

// 4a) for YMWS SOAP endpoint
$request = new OAuthRequest('POST', "$endPoint/soap", array(
	'oauth_nonce'=>mt_rand(),
	'oauth_timestamp'=>time(),
	'oauth_version'=>'1.0',
	'oauth_signature_method'=>'HMAC-SHA1',
	'oauth_consumer_key'=>$OAuthConsumerKey,
	'oauth_token'=>$oauth_token
	));
$request->sign_request($signature, new OAuthConsumer('', $OAuthConsumerSecret), new OAuthToken('', $oauth_token_secret));
$oauthURLForSoap = $request->to_url();

// 4b) for YMWS JSONRPC endpoint
$request = new OAuthRequest('POST', "$endPoint/jsonrpc", array(
	'oauth_nonce'=>mt_rand(),
	'oauth_timestamp'=>time(),
	'oauth_version'=>'1.0',
	'oauth_signature_method'=>'HMAC-SHA1',
	'oauth_consumer_key'=>$OAuthConsumerKey,
	'oauth_token'=>$oauth_token
	));
$request->sign_request($signature, new OAuthConsumer('', $OAuthConsumerSecret), new OAuthToken('', $oauth_token_secret));
$oauthURLForJson = $request->to_url();

// 5) Call YMWS

if(strtoupper($argv[1]) == "JSON")
{
	$jsonClient = new JsonRpcClient($oauthURLForJson, NULL);
	$jsonClient->__setHeader(array('Content-Type: application/json'));
	var_dump($jsonClient->ListFolders(new stdclass()));
}
else if(strtoupper($argv[1]) == "SOAP")
{
	try{
		$soapClient = new SOAP_Client('http://mail.yahooapis.com/ws/mail/v1.1/wsdl', array(
		'trace'=>1,
		'location'=>"$oauthURLForSoap"));
	}
	catch (Exception $e){
		var_dump($e);
	}
	var_dump($soapClient->ListFolders(new stdclass()));
}
?>

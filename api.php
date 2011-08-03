<?php

	// BOSS APIs
	// This will allow you to view errors in the browser     
  	// Note: set display_errors to 0 in production

	ini_set('display_errors',1);

	// Report all PHP errors (notices, errors, warnings, etc.)
	error_reporting(E_ALL);

	// // Substitute this application ID with your own application ID provided by Yahoo!.`
   	$appID = "xu7sd23IkYjPZu.wCemJxYxLosNzFOk2";
	
   	$query='microsoft_deal';
   	
	// URI used for making REST call, Each Web Service uses a unique URL.
	$request = "http://boss.yahooapis.com/ysearch/web/v1/$query?appid=$appID&format=xml&count=50";

	// Initialize the session by passing the request as a parameter
	$session = curl_init($request);

	// Set curl options by passing session and flags
	// CURLOPT_HEADER allows us to receive the HTTP header
	curl_setopt($session, CURLOPT_HEADER, true);

	// CURLOPT_RETURNTRANSFER will return the response 
	curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

	// Make the request
	$response = curl_exec($session);
	
	// Close the curl session
	curl_close($session);

	// Confirm that the request was transmitted to the Yahoo! Image Search Service
	if(!$response) {
   		die('Request to Yahoo! BOSS search service failed and no response was returned.');
	}

	// Create an array to store the HTTP response codes
	$status_code = array();

	// Use regular expressions to extract the code from the header
	preg_match('/\d\d\d/', $response, $status_code);
	
	// Check the HTTP Response code and display message if status code is not 200 (OK)
	switch( $status_code[0] ) {
	        case 200:
	                // Success
	                break;
	        case 503:
	                die('Your call to Yahoo Web Services failed and returned an HTTP status of 503. 
	                     That means: Service unavailable. An internal problem prevented us from returningÕ.
	                     Ô data to you.');
	                break;
	        case 403:
	                die('Your call to Yahoo Web Services failed and returned an HTTP status of 403. 
	                     That means: Forbidden. You do not have permission to access this resource, or are overÕ.
	                     Ô your rate limit.');
	                break;
	        case 400:
	                // You may want to fall through here and read the specific XML error
	                die('Your call to Yahoo Web Services failed and returned an HTTP status of 400. 
	                     That means:  Bad request. The parameters passed to the service did not match as expected. 
	                     The exact error is returned in the XML response.');
	                break;
	        default:
	                die('Your call to Yahoo Web Services returned an unexpected HTTP status of:' . $status_code[0]);
	}
	
	//  Get the XML from the response, bypassing the header
	if (!($xml = strstr($response, '<?xml'))) {
      	$xml = null;
	}

	print "Response :: $xml";
	$doc = new DOMDocument();
  	$doc->loadXML($xml);

  	// dataset = results
  	$dataset = $doc->getElementsByTagName("result");
    
  	$rowIdx=0;
  	// row = result
    foreach($dataset as $row ){
      	 $arrRecs[$rowIdx] = new rsltRec();
   		 $arrRecs[$rowIdx]->url = $row->getElementsByTagName( "url" )->item(0)->nodeValue;
         $arrRecs[$rowIdx]->title = $row->getElementsByTagName( "title" )->item(0)->nodeValue;
    	 $arrRecs[$rowIdx]->size = $row->getElementsByTagName( "size" )->item(0)->nodeValue;
    	 $arrRecs[$rowIdx]->clickurl = $row->getElementsByTagName( "clickurl" )->item(0)->nodeValue;
    	 $arrRecs[$rowIdx]->abstract = $row->getElementsByTagName( "abstract" )->item(0)->nodeValue;
         $arrRecs[$rowIdx]->date = $row->getElementsByTagName( "date" )->item(0)->nodeValue;
         $rowIdx++;
    }
    
    
    $highIdx = $rowIdx;
  	$ui = new UI("S");
 	$ui->display($ui->render($arrRecs,$query,$highIdx));
	
 /*
  *    
 	// BOSS API returned XML snippet
 	<result>
      <abstract><![CDATA[Official site of U.S. President Barack <b>Obama</b> and Vice President Joe Biden, featuring news, blogs, store, and photos.]]></abstract>
      <clickurl>http://lrd.yahooapis.com/_ylc=X3oDMTQ4bmtqMHN0BF9TAzIwMjMxNTI3MDIEYXBwaWQDeHU3c2QyM0lrWWpQWnUud0NlbUp4WXhMb3NOekZPazIEY2xpZW50A2Jvc3MEc2VydmljZQNCT1NTBHNsawN0aXRsZQRzcmNwdmlkA04zWUFyVWdlQXUxamZHckxDcUdLVHpYODJKRTJua3Fobmc4QUNXUnM-/SIG=110ppil7b/**http%3A//www.barackobama.com/</clickurl>
      <date>2009/09/03</date>
	      <dispurl><![CDATA[www.<b>barackobama.com</b>]]></dispurl>
      <size>36530</size>
      <title><![CDATA[Barack <b>Obama</b> and Joe Biden: The Change We Need]]></title>
      <url>http://www.barackobama.com/</url></result>
    <result>
  */

	
class rsltRec{

	public $url;	
	public $title;
	public $size;
	public $clickurl;
	public $abstract;
	public $date;
	
	function __construct(){ 
			$this->score=0; 
	}

}

 class UI{
	private $displayType;
	function __construct($dt){
	  $this->displayType = $dt;		
	}
	
	function display($content){
		$strHTML="<HTML><HEAD></HEAD><BODY><TABLE>";
		$strHTML.=$content;		
		$strHTML.="</TABLE></BODY></HTML>";

		print $strHTML;
		
	}
	
	
	function render($ar, $query, $highIdx){		
		$idx=1;
		$strHTML = "";
		$strHTML.= "<TR><TD>";
		$strHTML.= "<div align='center'>Search Results for '<b>".  $query . "'</b>";
		$strHTML.= "<div align='right'>$highIdx results found</div></div>";
		$strHTML.= "</TR></TD>";

		// Looping over all the records and displaying the attributes and generating the RP
		foreach($ar as $k){
				if($idx >= ($highIdx+1))
					break; 

				$temp = wordwrap($k->abstract,80,'<br>',true);
				
				$strHTML.= "<TR><TD><br><br></TD></TR><TR><TD>";
				$strHTML.= "[ $idx ] ";
				$strHTML.= "$k->title";
				$strHTML.= "</TR></TD>";
				
				$strHTML.= "<TR><TD>";
				$strHTML.= "$temp";
				$strHTML.= "</TR></TD>";

				$strHTML.= "<TR><TD>";
				$strHTML.= "<a href='$k->url'>". $k->url . "</a>";
				$strHTML.= "</TR></TD>";				
				
				$idx++;
		
		}
		
		if ($strHTML == "")
			return "<br><br>No results found";
		
		return $strHTML;

	}

 }

  
/* crap below */
/* One could use the DOMDocument version of the same */
// Create a SimpleXML object with XML response
//	$simple_xml = simplexml_load_string($xml);
//	// Traverse XML tree and save desired values from child nodes
//	foreach($simple_xml->Result as $result)
//	{
//	   $output .= "<tr><td align=left>".ucfirst(wordwrap($result->Summary,60,"<br />"))."</td>";
//	$output .= "<td><a href='{$result->Url}'><img src='{$result->Url}' height=100Õ.  Òwidth=100></a></td></tr>";
//	}
//	$output .= "</table>";
//	print($heading . $output);

?>
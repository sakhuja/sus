<?php
    // testing the curl PHP APIs
    // header ("Content-Type:text/xml");
    $request =  'http://search.yahooapis.com/ImageSearchService/V1/imageSearch?appid=YahooDemo&query='.urlencode('Barack Obama').'&results=10&output=json';
    $request = "http://social.yahooapis.com/v1/user/BOWJ76SOVS6XYLMNPOKA7ANO5Q/contacts";
    $session = curl_init($request);
    curl_setopt($session, CURLOPT_HEADER, false);
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($session);
    curl_close($session);
    echo $response;
?>

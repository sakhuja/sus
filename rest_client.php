<?php

	include 'RESTful.php';
	
	$request = new RestRequest("http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20flickr.photosets.photos%20where
	%20photoset_id%3D'72157612249760312'&format=json&diagnostics=true&callback=cbfunc",'GET');

	$request->execute();
	
	$string1 = '{"name":"Aditya Sakhuja"}';
	$json_o = json_decode($string1, true);
	print_r($json_o);
	
 	//	print_r($request);	
	// echo '<pre>' . print_r($request, true) . '</pre>';

?>
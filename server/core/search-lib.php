<?php

/** 
 * Algorithm:
 *  0. START 
 *  1. Read search query
 *  2. Tokenize query and store in memory
 *  3. Open and Read sitemap.xml
 *  4. Find all the urls which are {RELEVANT} to the query tokens
 *  5. Rank the urls
 *  6. Fetch metadata for the urls and display the results on the UI
 *  7. END
 */

// global variables
$file;
$glblTag; 	
$arrRecs = array();
 	
 class finder{  
 	
   function __construct(){
   	echo "hello in constr..";
   }

   function tokenizeQuery($query){
   		return $query;
   }
   
   function find($query){
		global $file;
		global $arrRecs;
		echo $query."_1";
		$arrTokens = $this->tokenizeQuery($query);
		echo $query."_1.5";
	 	// $hndl_sitemap = fopen("sitemap.xml", "r");
	 	$file = file_get_contents('./sitemap.xml', true);

	 	/** find algorithm
	 	 * 1. Read the records in memory calculating the scores and updating it in the record
	 	 * 2. After processing all the records, sort the records as per the scores
	 	 * 3. Pass the records to the UI
 	 	 */
		//	echo $query."_2";	 		 			
		 $dd = new DOMDocument;
	 	 if(! $dd->load('./sitemap.xml')){
	 	 	echo "Loading error";
	 	 }
 	     // echo $query."_3";
 	     // fetching all the sitemap nodes
	 	 $lstRecords = $dd->getElementsByTagName('url');
		 // print( "sizeof : " . sizeof($lstRecords));	
	 	 // foreach($lstRecords as $key=>$value){
		 //	echo "val : ". $value;
     	 // }
     	 
   	  	 // echo "lstRecords : length ::".$lstRecords->length; 
	 	 // looping over all the sitemap nodes
	 	 for($i=0; $i<$lstRecords->length; $i++){
	 	 	$arrRecs[$i] = new rsltRec;
	 	 	$recNode = $lstRecords->item($i)->childNodes;
  	   	  	// $ch_nodes = $lstRecords->item($i)->childNodes;
			echo ": nodeName :";
	 	 	foreach($recNode as $k=>$value){
	 	 		printf("\n" . " $k : " . $value->nodeName);
	 	 	}
	 	 	
	 	 	echo $query."_6.5";
  	 	 	
	 	 	// echo " " . $lstRecords->item($i)->nodeValue . " ";
	 	 	// looping over all the fields of each sitemap node, 
	 	 	// populating the record stv`ructure for find-ing
	 	 	
	 	 	echo "length of each recNode : " . $recNode->length;
 	 		
	 	 	foreach($recNode as $k=>$v){
 	    	// for( $j=0; $j<$recNode.length; $j++ ){
	 	    	if($v->nodeName=="loc")
	 	    		$arrRecs[$i]->$url = $v->nodeValue;
	 	    	if($v->nodeName=="tags")
	 	    		$arrRecs[$i]->$tags = $v->nodeValue;	 
	 	    	if($v->nodeName=="abstract")
	 	    		$arrRecs[$i]->$abstract = $v->nodeValue;			    		
	 	    }	 	    
	 	    
	 	    echo $query."_6.7";
	 	    
	 	    $arrTags = explode($arrRecs[$i]->tags," ");
	 	    
	 	    // need a better scoring function here
	 	    // ideas: make use of synonynms dictionary
	 	    foreach($arrTags as $key=>$value){
	 	    	if(strcmp($value,$arrTokens)==0)
	 	    	  $arrRecs[$i]->$score++;
	 	    }
	 	 }

	 	 echo $query."_6";
	 	 sort(&$arrRecs,SORT_NUMERIC);	 	 
	 	 $ui = new UI("S");
	 	 $ui->display($ui->render($arrRecs));
 	  	 echo $query."_7";
   }

}

class UI{
	private $displayType;
	
	function __construct($dt){
	  $this->$displayType = $dt;		
	}
	
	function display($content){
		$strHTML="<HTML><HEAD>Search Results</HEAD><BODY><TABLE>";
		$strHTML.=$content;		
		$strHTML.="</TABLE></BODY></HTML>";
		
		print $strHTML;
	}
	
	function render($ar){		
		// Looping over all the records and diaplying the attributes and generating the RP
		foreach($ar as $k){
			$strHTML.= "<TR><TD>";
			$strHTML.= "[" . $k->$timestamp. "]" . $k->$url . " :: " . $k->$abstract;
			$strHTML.= "</TR></TD>";
		}
					
		return $strHTML;
	}
}

/**
 * 
 * @author Aditya Sakhuja
 * Class: 
 *
 */
class rsltRec{
	public $url;
	public $timestamp;
	public $abstract;
	public $tags = array();
	public $score;
}

/*	
class rsltRec{
	public $url;
	public $timestamp;
	public $abstract;
	public $tags = array();
	public $score;

	function __construct($url){
		$this->$url = $url;		
		$this->$score = 0;	
	}
	
	function __construct($url, $abstract, $tags){
		foreach($tags as $k=>$v){
			$this->$tags[$k] = $v;
		}
		$this->$abstract = $abstract;
		$this->$url = $url;
		$this->$score = 0;	
	}
}
*/

?>
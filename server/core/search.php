<?php

/** 
 * Algorithm:
 *  0. START 
 *  1. Read search query
 *  2. Tokenize query and store in memory
 *  3. Open and Read sitemap.xml
 *  4. Find all the urls which are { RELEVANT} to the query tokens
 *  5. Rank the urls
 *  6. Fetch metadata for the urls and display the results on the UI
 *  7. END   
 **/

$file;
$glblTag; 	


// $arrRecs = array();
class finder{
      var $arrRecs = array();

      function tokenizeQuery($query){
      	 return explode(" ", $query);
      	 // return $query;            
	  }
	  
      /** find algorithm
       *  1. Read the records in memory calculating the scores and updating it in the record
 	   *  2. After processing all the records, sort the records as per the scores
 	   *  3. Pass the records to the UI
       */
      function find($query){
		global $file;
		// global $arrRecs;
	 	$arrTokens = $this->tokenizeQuery($query);
	    // print "Array Tokens :: $arrTokens"; 
		  $doc = new DOMDocument();
		  $doc->load('sitemap.xml');
	      $rowIdx = 0; 
		  $dataset = $doc->getElementsByTagName( "url" );
	
		  foreach( $dataset as $row )
	      {

	      	 $arrRecs[$rowIdx] = new rsltRec();
	   		 $arrRecs[$rowIdx]->loc = $row->getElementsByTagName( "loc" )->item(0)->nodeValue;
	         $arrRecs[$rowIdx]->lastMod = $row->getElementsByTagName( "lastmod" )->item(0)->nodeValue;
	    	 $arrRecs[$rowIdx]->changefreq = $row->getElementsByTagName( "changefreq" )->item(0)->nodeValue;
	    	 $arrRecs[$rowIdx]->priority = $row->getElementsByTagName( "priority" )->item(0)->nodeValue;
	    	 $arrRecs[$rowIdx]->tags = $row->getElementsByTagName( "tags" )->item(0)->nodeValue;
	    	 $arrRecs[$rowIdx]->tagElements = explode(" ", $arrRecs[$rowIdx]->tags);    	 

	    	 // $this->scorer($idx,$arrRecs,$arrTokens);
	    	 //  foreach($arrRecs[$rowIdx]->tagElements as $tag){
	    	 //	  foreach($arrTokens as $token){
	    	 //		if(!strcmp($tag,$token))
	    	 //		  $arrRecs[$rowIdx]->score++;		
	    	 //   }
	    	 // }
	    	 
	    	 foreach($arrRecs[$rowIdx]->tagElements as $tag){
	    	 	foreach($arrTokens as $token){
	 	   	      if(!strcmp($tag,$token))
		    	  	$arrRecs[$rowIdx]->score++;
	    	    }
	    	     
	    	    // `score` is a float value
	    	   //  $arrRecs[$rowIdx]->score++;
	    	    // = $arrRecs[$rowIdx]->count * rankingConstants::$weight_count;
	    	    // print "score : ". $arrRecs[$rowIdx]->score;
	    	    
	    	 }

	    	 // print ":: BEFORE :: ";
	      	 // print_r($arrRecs);

	    	 $arrRecs = $this->sortObject( $arrRecs);
	    	 
	         $idx=0;	// local counter 
		 	 $highIdx = count($arrRecs) - 1;
		 	 foreach($arrRecs as $rec){
		 	 	if($rec->score == 0){
		 	 	  $highIdx = $idx;
		 	 	  break;
		 	 	}
		 	 	$idx++;
		 	 }
	
		 	 // print "highIdx : $highIdx";
		 	 // print ":: AFTER :: ";
			 // print_r($arrRecs);
			   
		 	  $rowIdx++;
		 	  
	      } 
		 	 $ui = new UI("S");
			 // print "query : " . $query;
		 	 $ui->display($ui->render($arrRecs,$query,$highIdx));
   }
   
 // scoring function
 // need a better scoring function here :: Ideas : make use of synonynms dictionary
 function scorer($idx, $arrRecs, $arrTokens){
   foreach ($arrRecs[$idx]->tagElements as $value){
   // 	foreach ($arrRecs[$idx]->tagElements as $value){
	   	if(strcmp($value,$arrTokens[$idxer])==0)
    	  	$arrRecs[$idx]->score++;
   }
   
 }
      
 function sortObject($data) {
	for ($i = count($data) - 1; $i >= 0; $i--) {
		$swapped = false;
		for ($j = 0; $j < $i; $j++) {
			if ( $data[$j]->score < $data[$j + 1]->score ) {
				$tmp = $data[$j];
                $data[$j] = $data[$j + 1];
                $data[$j + 1] = $tmp;
                $swapped = true;
			}
		}
		
		// if(!$swapped){
		//	return $data;
		// }
		
	}

	 // print "SORT LIST >>>";
	 // print_r($data);
	 return($data);
   }
   
}

class rankingConstants{
	static private $weight_count = 0.2;
}

 /**
 * @author Lucas Dom‡nico
 */
class util {
    
	static private $sortfield = null;
    
    static private $sortorder = 1;
    
    static private function sort_callback(&$a, &$b) {
        if($a[self::$sortfield] == $b[self::$sortfield]) return 0;
        return ($a[self::$sortfield] < $b[self::$sortfield])? -self::$sortorder : self::$sortorder;
    }
    
    static function sort(&$v, $field, $asc=true) {
        self::$sortfield = $field;
        self::$sortorder = $asc? 1 : -1;
        usort($v, array('util', 'sort_callback'));
    }
    
}

# Usage:
// for($i=0; $i<1000 ;$i++) {
//   $v[] = array('score'=>rand(1,10000));
// }

// util::sort($v, 'score');

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
				$strHTML.= "<TR><TD>";
				// $strHTML.= "[" . $k->priority . "]" . "<a href='$k->loc'>". $k->loc . "</a> :: " . $k->tags;
				$strHTML.= "[$idx] ";
				$strHTML.= "<a href='$k->loc'>". $k->loc . "</a>";
				$strHTML.= "</TR></TD>";
				$idx++;
		}
		
		if ($strHTML == "")
			return "<br><br>No results found";
		
		return $strHTML;
	}
 }

/**
 * @author :: Aditya Sakhuja
 */

class rsltRec{
	public $loc;	
	public $title;
	public $timestamp;
	public $lastmod;
	public $changefreq;
	public $priority;
	public $abstract;
	public $tags;
	public $tagElements;
	public $score;
	
	// score factors
	public $count;
	/*
	 *  function __construct($url){
	 *	  $this->$url = $url;		
 	 *	  $this->$score = 0;	
	 *  }
	 *
	 */
	
	// function __construct(){}
	
	/*function __construct($u, $a, $t){
		print "rsltRec constructor";
		$this->tags = array();
		print "chkpoint 1 ";		
		foreach($t as $k=>$v){
			$this->tags[$k] = $v;
		}
		print "chkpoint 2 ";
		$this->abstract = $a;
		print "chkpoint 3 ";
		$this->url = $u;
		print "chkpoint 4 ";
		$this->score = 0;
		print "chkpoint 5 ";	
	}
	
	*/
	
	function __construct(){$this->score=0;}
  }

// main
$objfind = new finder();
// print $_REQUEST['query'];
$objfind->find($_REQUEST['query']);

?>


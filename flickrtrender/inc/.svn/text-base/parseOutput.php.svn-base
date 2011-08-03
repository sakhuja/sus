<?php
require_once ('configHelper.php');
require_once ('CommonUtil.php');
define('MIN_NUMBER_URL', 100);
define('REVIEW_ATTRIBUTE_URL', 100);
define('REVIEW_URL', 20);

/**
 * Utility class for all the function for the Output file. It parses the output file and build a table out of the raw format
 * It can also generate the CSV and TSV format from the raw output file.
 * @author anupg
 */
class parseOutput{
	
	/**
	 * Update the template type
	 * @param unknown_type $siefId
	 */
	public function updateTemplateType($siefId){
		$params['siefId'] = $siefId;
		$params['requestedAttributes'] = "xslName,template,type,siefId";
		$siefRule = CommonUtil::getSiefRules($params,"getSiefRule");
		$outFilename ="";
		if(isset($siefRule[$siefId])){
			$row=$siefRule[$siefId];
			$logDir = get_cfg_var("LOGDIR");
			$xslname = $row['xslName'];
			$template = $row['template'];
			$outFilename = "$logDir/$siefId/$xslname.out";
			$objectType = $row['type'];
			if(file_exists($outFilename) && $objectType=="unknown"){
				self::generatecsvtsv($outFilename,$siefId,$objectType);
			}
			if($objectType != "unknown" && $template!="none"){
				$xslFilename = "$logDir/$siefId/$xslname";
				$xslt = file_get_contents($xslFilename);
				$startStr = "<!-- DO NOT MODIFY ANYTHING ABOVE THIS LINE -->";
				$endStr = "<!-- DO NOT MODIFY ANYTHING BELOW THIS LINE -->";
				$startPos = strpos($xslt, $startStr);
				$endPos = strpos($xslt, $endStr);
				if($startPos!==FALSE && $endPos!==FALSE){ 
					$output = CommonUtil::executeSiefUpdate(array("template" => $objectType),$siefId);
					if ( array_key_exists("error", $output)) die (print json_encode($output));
				}
			}
		}
	}
	
	/**
	 * Function generate the CSV and TSV file from the raw output file
	 * @param unknown_type $filename
	 * @param unknown_type $siefId
	 * @param unknown_type $objectType
	 */
	public function generatecsvtsv($filename,$siefId,&$objectType="unknown"){
		$urlnum = 0;
		$attribs = array();
		$urlarray = array();
		//$objectType="unknown";
		self::generateArray($filename,"",$attribs,$urlnum,$urlarray,$objectType);
		self::generateCsvFile($filename,$attribs,$urlnum,$urlarray,$objectType);
		if($objectType != "unknown"){
			$output = CommonUtil::executeSiefUpdate(array("type" => $objectType),$siefId);
			if ( array_key_exists("error", $output)) die (print json_encode($output));
		}
	}
	
	public function checkMinimumUrls($siefId,$filename=""){
		if(self::getNumberOfUrls($siefId,$filename) >= MIN_NUMBER_URL )return true;
		else return false;
	}
	
	
	/**
     * Get the Number of Urls from the output file
	 * @param unknown_type $siefId
	 * @param unknown_type $filename
	 */
	public function getNumberOfUrls($siefId,$filename=""){
		
		$siefout = self::getRawOutputData($siefId,$filename);
		
		$delimiter = "</feed> ";
		$pos = strpos($siefout, $delimiter);  // No extraction found 
		if($pos===FALSE) $delimiter = "\n";
		$splitFeed = explode($delimiter, $siefout);
		$urlnum = 0;
		foreach($splitFeed as $lineNum => $feed){
			$feed = trim($feed);
			if(empty($feed)) continue;
			$URLsNFeed = explode("<feed>",$feed,2);
			$urls = trim($URLsNFeed[0]);
			$splitUrls = explode("\n",$urls);
			$urlnum += count($splitUrls);
		}
		return $urlnum;
	}
	
	/**
     * Get the raw output data either from the disk or from the database
	 * @param unknown_type $siefId
	 * @param unknown_type $filename
	 */
	public function getRawOutputData($siefId,$filename=""){
		$siefout="";
		if(empty($filename)){
			$siefRule = CommonUtil::getSiefRules(array('siefId'=>$siefId,'requestedAttributes'=>'xslName'),"getSiefRule");
			if(isset($siefRule[$siefId])){
				$filename = get_cfg_var("LOGDIR")."/$siefId/{$siefRule[$siefId]['xslName']}.out";
			}
		}
		if(file_exists($filename)){
			$siefout = file_get_contents($filename);
		}
		else{
			$siefRule = CommonUtil::getSiefRules(array('siefId'=>$siefId,'requestedAttributes'=>'outputxml'),"getSiefRule");
			if(isset($siefRule[$siefId])) $siefout = $siefRule[$siefId]['outputxml'];
		}
		
		return $siefout;
	}
	
	
	/**
	 * Generate the in memory representation of the raw output file. It can be used for either fill the cells of the display table or 
	 * generate the CSV and TSV files.
	 * @param unknown_type $filename
	 * @param unknown_type $output
	 * @param unknown_type $attribs
	 * @param unknown_type $urlnum
	 * @param unknown_type $urlarray
	 * @param unknown_type $objectType
	 */
	public function generateArray($filename,$output="",&$attribs,&$urlnum,&$urlarray,&$objectType="unknown"){
		
		
		$whitespaces = array("\r\n", "\n", "\r", "\t", "  "," & ","&#x20;");
		$replaceStr  = array ("","","",""," "," &amp; "," ");
		if(!empty($filename))
			$siefout = file_get_contents($filename);
		else
			$siefout = $output;
	
		$delimiter = "</feed> ";
		//No feed is found in the output of superdex
		$pos = strpos($siefout, $delimiter);
		if($pos===FALSE) $delimiter = "\n";
		
		$splitFeed = explode($delimiter, $siefout);
		$urlPassed = 0;
		$len = count($splitFeed);
		$avglenDocfeed = 0;
		$maxlenDocfeed = 0;
		$docCount = 0;
		foreach($splitFeed as $lineNum => $feed){
			
			$feed = trim($feed);
			if(empty($feed)) continue;
			$URLsNFeed = explode("<feed>",$feed,2);
			$urls = trim($URLsNFeed[0]);
			//echo $urls;
			$splitUrls = explode("\n",$urls);
			//echo count($splitUrls)."<br/>";
			foreach($splitUrls as $url){
				$urlarray[$urlnum++]=trim($url);
			}
			//echo $URLsNFeed[1];
			if(count($URLsNFeed)==2){
				
				$xmldoxfeedLength = strlen($URLsNFeed[1]);
				$docCount += 1;
				$avglenDocfeed += $xmldoxfeedLength;
				if($xmldoxfeedLength > $maxlenDocfeed) $maxlenDocfeed = $xmldoxfeedLength;
				
				$xslt = "<feed>".trim($URLsNFeed[1])."</feed>";
				$xslt=trim(str_replace($whitespaces,$replaceStr,$xslt));
				
				$position = strpos($xslt, "gr:isListPrice");
				if($position!==FALSE){
					$xslt = preg_replace('/gr:isListPrice(.*?)true(.*?)gr:hasCurrencyValue/msi','gr:isListPrice\1true\2gr:listCurrencyValue',$xslt);
					$xslt = preg_replace('/gr:isListPrice(.*?)true(.*?)gr:hasCurrency/msi','gr:isListPrice\1true\2gr:listCurrency',$xslt);
					$xslt = preg_replace('/gr:isListPrice(.*?)false/msi','gr:isPrice\1true\2',$xslt);
				}
				//print $xslt;
				self::populateTable($xslt,$urlnum - 1,$attribs,$objectType);
			}
				
		}
		
		//html_show_array($attribs,$urlnum,$urlarray);
		//print_r($attribs);
	}
	
	/**
	 * Generate the CSV file from the in memory array representation of raw data
	 * @param unknown_type $filename
	 * @param unknown_type $attribs
	 * @param unknown_type $urlnum
	 * @param unknown_type $urlarray
	 */
	public function generateCsvFile($filename,&$attribs,$urlnum,&$urlarray){
		
		$fpcsv = fopen($filename.".csv", 'w');
		$fptsv = fopen($filename.".tsv", 'w');
		$inputline = array();
		$inputline[] = "URLS";
  	  	foreach($attribs as $key => $val){
  			$inputline[] = $key;
  		}
		fputcsv($fptsv, $inputline, "\t");
		fputcsv($fpcsv, $inputline);
		for ($i=0;$i<$urlnum;$i++){
			$inputline = array();
		 	if (isset($urlarray[$i])) $inputline[] = $urlarray[$i];
		 	else $inputline[] = $i+1;
		 	
		 	foreach($attribs as $key => $val){
    	      if(isset($attribs[$key][$i])) $inputline[] = $attribs[$key][$i];
    	      else $inputline[] = "";
		 	}
		 	fputcsv($fptsv, $inputline, "\t");
		 	fputcsv($fpcsv, $inputline);
		}
		fclose($fptsv);
		fclose($fpcsv);
	}
	
	/**
     * Xpath queries for determining the type of the object
	 * @param unknown_type $xpath
	 * @param unknown_type $type
	 */
	public function getObjectType(&$xpath,&$type="unknown"){
		$objectXpathQry = array();
		
		$objectXpathQry['video']= "//*[contains(concat(' ',@rel,' '),' media:video ')]";
		$objectXpathQry['audio']= "//*[contains(concat(' ',@rel,' '),' media:audio ')]";
		$objectXpathQry['document']= "//*[contains(concat(' ',@rel,' '),' media:document ') or contains(concat(' ',@typeof,' '),' media:document ')  or contains(concat(' ',@typeof,' '),' media:presentation ') or contains(concat(' ',@typeof,' '),' media:spreadsheet ')]";
		$objectXpathQry['game']= "//*[contains(concat(' ',@rel,' '),' media:game ') or contains(concat(' ',@typeof,' '),' media:Game ')]";
		$objectXpathQry['person']= "//*[contains(concat(' ',@typeof,' '),' extraction:Social ')]";
		$objectXpathQry['product']= "//*[contains(concat(' ',@typeof,' '),' product:Product ') or contains(concat(' ',@typeof,' '),' gr:Offering ')]";
		$objectXpathQry['event']= "//*[contains(concat(' ',@typeof,' '),' vcal:Vevent ')]";
		$objectXpathQry['news']= "//*[contains(concat(' ',@typeof,' '),' dcmitype:Text ') or contains(concat(' ',@typeof,' '),' news:NewsItem ') or contains(concat(' ',@typeof,' '),' media:Text ')]";
		$objectXpathQry['discussion']= "//*[(contains(concat(' ',@typeof,' '),' sioc:Post ') or contains(concat(' ',@typeof,' '),' sioc:Thread ')) and
                                         not(ancestor-or-self::*[contains(concat(' ',@typeof,' '),' news:NewsItem ')
                                         or contains(concat(' ',@typeof,' '),' dcmitype:Text ')])]";
		$objectXpathQry['local']= "//*[contains(concat(' ',@typeof,' '),' vcard:VCard ') or contains(concat(' ',@typeof,' '),' commerce:Business ') and  
                                          not(ancestor-or-self::*[contains(concat(' ',@typeof,' '),' vcal:Vevent ')
                                          or contains(concat(' ',@class,' '),' vevent ') 
                                          or contains(concat(' ',@typeof,' '),' gr:Offering ')
                                          or contains(concat(' ',@typeof,' '),' product:Product ')
                                          or contains(concat(' ',@typeof,' '),' sioc:Post ')
                                          or contains(concat(' ',@typeof,' '),' news:NewsItem ')
                                          ])]";
		


		foreach($objectXpathQry as $object=>$qry){
			$smxmlItem = $xpath->query($qry);
			$len = $smxmlItem->length;
			if($len>=1) {$type=$object; return;}
		}
		
	}
	
	/**
	 * Populate a row in the array elements by parsing the raw XML output. 
	 * @param unknown_type $feedXML
	 * @param unknown_type $urlnum
	 * @param unknown_type $attribs
	 * @param unknown_type $type
	 */
	public function populateTable($feedXML,$urlnum,&$attribs,&$type="unknown"){
		
		//echo htmlentities($feedXML);
		if(empty($feedXML)) return;
		
		$xmlDoc = new DOMDocument();
		$xmlDoc->loadXML($feedXML);
		
		$xpath = new DomXPath($xmlDoc);
		$smxmlItem = $xpath->query("//*[local-name()='item' and @rel and @resource]");
		$len = $smxmlItem->length;
	
		if ($len > 0){
			foreach($smxmlItem as $item){
				$rel = $item->getAttribute('rel');
				$relValue = $item->getAttribute('resource');
				$relValue = trim($relValue);
				if(!array_key_exists($rel, $attribs)){
					$values = array();
					$values[$urlnum] = $relValue;
					$attribs[$rel] = $values;
				}
				else {
					$attribs[$rel][$urlnum]= $relValue;
				}
			}
		}
		
		$smxmlMeta = $xpath->query("//*[local-name()='meta']");
		$len = $smxmlMeta->length;
	
		if ($len > 0){
			foreach($smxmlMeta as $meta){
				$rel = $meta->getAttribute('property');
				$relValue = $meta->textContent;
				$relValue = trim($relValue);
				if(!array_key_exists($rel, $attribs)){
					$values = array();
					$values[$urlnum] = $relValue;
					$attribs[$rel] = $values;
				}
				else {
					$attribs[$rel][$urlnum]= $relValue;
				}			
			}
		}
		//For determining the type of object
		if($type=="unknown"){
			self::getObjectType($xpath,$type);
		}
	}
	
	/**
	 * Display the raw output in the table
	 * @param unknown_type $array
	 * @param unknown_type $urlnum
	 * @param unknown_type $urlarray
	 * @param unknown_type $tableNum
	 */
	public function html_show_array(&$array,&$urlnum=3,&$urlarray=array(),$tableNum=""){
	  self::getYUIdataTable($array,$urlnum,$urlarray,$tableNum);
	  global $siefId;
	  echo "<div id=\"feedDataTable$tableNum\">\n";
	  echo "<table id=\"outputTable$tableNum\" class=\"border\" cellspacing=\"0\" border=\"2\" align=\"center\" cellpadding=\"3\">\n";
	  echo "<thead>";
	  echo "<tr><th>No.</th><th>URLS</th>";
	  foreach($array as $key => $val){
	  	echo "<th>".$key."</th>";
	  }
	  echo "</tr></thead><tbody>\n";
	  
	  for ($i=0;$i<$urlnum;$i++){
	  	echo "<tr><td>".($i+1)."</td>";
	  	if (isset($urlarray[$i])){
				$currentUrl=yiv_get_url($urlarray[$i]);
	  			echo "<td><a href=\"".$currentUrl."\">".$currentUrl."</a>";
	  			echo "&nbsp;&nbsp;&nbsp;<a style=\"color:Red;\" href=\"quickCheck.php?siefId=$siefId&url=".urlencode($currentUrl)."\" TITLE=\"Quick Check the RULE run on this URL\">Q!</a>";
	  			echo "</td>";
	  	}
	  	else echo "<td>".$i."</td>";
	  	
	    foreach($array as $key => $val){
	    	if(isset($array[$key][$i]) && (!empty($array[$key][$i]) || $array[$key][$i]=="0")){
	    		$image="";
	    		if((@strpos($key,"media:image")) !== FALSE  || (@strpos($key,"media:thumbnail")) !== FALSE || (@strpos(strtolower($key),"photo")) !== FALSE){
				$imageUrl=yiv_get_url($array[$key][$i]);
	    			$image='<a href="'.$imageUrl.'" style="float:right;" target="_blank"><img src="'.$imageUrl.'" /></a>';
	    		}
			if($image!=""){
                                echo "<td style=\"background-color: rgb(241, 245, 236);\">$image".htmlspecialchars(yiv_get_url($array[$key][$i]))."</td>";
                        }
                        else{
                                echo "<td style=\"background-color: rgb(241, 245, 236);\">".htmlspecialchars($array[$key][$i])."</td>";
                        }

	    	}
	  		else echo "<td/>";
	  	}
	  	echo "</tr>"; 
	  }  
	  echo "</tbody></table></div>\n";
   }
	
   
   public function getYUIdataTable(&$array,&$urlnum=3,&$urlarray=array(),$tableNum=""){
	?>
	<script type="text/javascript">
	YAHOO.util.Event.addListener(window, "load", function() {
	    YAHOO.example.EnhanceFromMarkup = function() {
	        var myColumnDefs = [
	        <?php 
	        	echo "{key:\"No\",label:\"No.\", formatter:YAHOO.widget.DataTable.formatNumber, sortable:true},";
				  echo "{key:\"URLs\",label:\"URLs\", sortable:true},";
				  foreach($array as $key => $val){
				  	echo "{key:\"$key\",label:\"$key\", sortable:true},";
				  }
	        ?>
	        ];
	
	        var myDataSource = new YAHOO.util.DataSource(YAHOO.util.Dom.get("<?php echo "outputTable$tableNum";?>"));
	        myDataSource.responseType = YAHOO.util.DataSource.TYPE_HTMLTABLE;
	        myDataSource.responseSchema = {
	            fields: [
	                    <?php 
	                      echo "{key:\"No\",parser:\"number\"},";
						  echo "{key:\"URLs\"},";
						  foreach($array as $key => $val){
						  	echo "{key:\"$key\"},";
						  }
	        			?>
	            ]
	        };
	
	        var myDataTable = new YAHOO.widget.DataTable("<?php echo "feedDataTable$tableNum";?>", myColumnDefs, myDataSource,{}
	        );
	        
	        return {
	            oDS: myDataSource,
	            oDT: myDataTable
	        };
	    }();
	});
	</script>
	<?php
		
	}

	public function html_show_SMarray(&$array,&$urlnum=3,&$urlarray=array(),&$smarray=array()){
	  echo "<table cellspacing=\"0\" border=\"2\" align=\"center\" cellpadding=\"3\"  style=\"border-color: rgb(241, 245, 236);\">\n";
	  echo "<tr><th>URLS</th>";
	  foreach($array as $key => $val){
	  	echo "<th>".$key."</th>";
	  }
	  echo "</tr>";
	  
	  for ($i=0;$i<$urlnum;$i++){
	  	if (isset($urlarray[$i])){
			$currentUrl=yiv_get_url($urlarray[$i]);
	  		echo "<tr><td rowspan=\"2\"><a href=\"".$currentUrl."\">".$currentUrl."</a></th>";
	  	}
	  	else echo "<tr><td>".$i."</td>";
	  	
	   foreach($array as $key => $val){
	    	if(isset($array[$key][$i]))
	  			echo "<td style=\"background-color: rgb(241, 245, 236);\">".$array[$key][$i]."</td>";
	  		else echo "<td/>";
	  	}
	  	echo "</tr><tr>";
	  	
	   foreach($array as $key => $val){
	    	if(isset($smarray[$key][$i]))
	  			echo "<td style=\"background-color: #CAD4E6;\">".$smarray[$key][$i]."</td>";
	  		else echo "<td/>";
	  	}
	  	
		/*
		 * //#E0ECFF  #EEEEFF #CAD4E6
	    foreach($array as $key => $val){
	    	echo "<td><table cellspacing=\"0\" border=\"1\" align=\"center\" cellpadding=\"0\">";
	    	if(isset($array[$key][$i])){
	    		echo "<tr><td>".$array[$key][$i]."</td></tr>";
	    	}
	    	else echo "<tr><pre></pre></tr>";
	    	
	    	if (isset($smarray[$key][$i]))
	    			echo "<tr><td>".$smarray[$key][$i]."</td></tr>";
	    	else echo "</tr><pre></pre></tr>";
	  		echo "</table></td>";
	  	}
		*/
	  	
	  	
	  	echo "</tr>"; 
	  }  
	  echo "</table>\n";
	}
	
	/**
	 * Function to generate the table for the 2nd Phase of the QA. 2nd phase contains 20 random url selected fron top 100
	 * http://twiki.corp.yahoo.com/view/Yst/SIEFRuleValidationOnline#Reviewer_Role
	 * @param unknown_type $outFilename
	 * @param unknown_type $siefId
	 */
	public function generateUrlEditorial($outFilename,$siefId){
		include('editorialHeader.html');
		$urlnum = 0;
		$attribs = array();
		$urlarray = array();
		$objectType=CommonUtil::getObjectType($siefId);
		self::generateArray($outFilename,"",$attribs,$urlnum,$urlarray,$objectType);
		$toturl = $urlnum;
		if($urlnum > REVIEW_URL) $urlnum = REVIEW_URL;
		echo '<script>var buttonArray = new Array();
					  var buttonGroup = new Array();
		              var buttoncount = 0;
		              var totaltabs = '.$urlnum.' ;
		      </script>';
		echo '<form action="judgeEditorial.php?crumb='.getenv('.bycrumb').'" enctype="multipart/form-data" method="post" style="left" >';
		echo '<input type="hidden" name="siefId" value="'.$siefId.'" />';
		echo '<div id="tvcontainer" class="yui-navset" style="padding: 0px 0px 0px 450px;">
	    <ul class="yui-nav" style="width:450px;">';
		$i=0;
		
		$n = REVIEW_ATTRIBUTE_URL;
		$m = REVIEW_URL;
		if($toturl < REVIEW_ATTRIBUTE_URL) $n = $toturl;
		if($urlnum < REVIEW_URL) $m = $urlnum;
		
		$data = range(0, $n - 1);
		$rand = array_rand($data,$m);
		
		for($i=1;$i<($urlnum+1);$i++){
			$class ="";
			if($i==1) $class='class="selected"';
			echo "<li $class><a href=\"#$i\" style=\"display:block; text-align:left;\"><em><span style=\"font-weight:bold;\">$i.</span> ".substr($urlarray[$rand[$i-1]],0,60)."<span id=\"CHECK".($i-1)."\" style=\"display:none; float: right; color: green;\">&#x2713</span></em></a></li>";
		}
		echo '</ul> <div class="yui-content">';		
		$j=0;
		for ($i=0;$i<$urlnum;$i++){
			
			echo '<div id="'.$i.'" class="yui-dt-hd" style="background-color: rgb(242, 242, 242);">';
			$currentUrl=yiv_get_url($urlarray[$rand[$i]]);
			if (isset($currentUrl)) {				
				echo '<br/>';
				echo "<a style=\"color:Red;\" href=\"/cachePage.php?siefId=$siefId&url=".urlencode($currentUrl)."\"  target=\"_blank\" TITLE=\"Cached page from the catalog for this URL\">CACHED PAGE</a>";
				echo '&nbsp;&nbsp;&nbsp;LIVE PAGE : ( <a href="'.$currentUrl.'" target="_blank" style="color:blue;font-weight:bold;">'.$currentUrl.'</a> )';
				echo '<br/><br/>';
			}
					
			echo '<table id="rulesTable'.$i.'"  class="border" border="1" cellpadding="5" align="center" style="background-color: #EEEEEE; text-align: center; border: 1px solid navy;" >
				<thead>
				<tr>
					<th rowspan="1" colspan="1" class="yui-dt-first">Key</th>
					<th rowspan="1" colspan="1">Value</th>
					<th rowspan="1" colspan="1" class="yui-dt-last">Editorial Judgement</th>
				</tr>
			    </thead>
			    <tbody>';
			echo '<script>var buttonArray'.$i.' = new Array();</script>';
			$j=0;
			foreach($attribs as $key=>$value){ 
				if($j%2 == 0)
				echo '<tr class="yui-dt-first yui-dt-even">';
				else echo '<tr class="yui-dt-first yui-dt-odd">';
				$class = '';
				$accurate = "checked";
				//$accurate = "";
				$empty = "";
				$val = "";
				$descArray = configHelper::getDescriptionArray($key,$objectType,$attribType);
				$isimage = false;
				if(!empty($descArray['FORMAT_TYPE'])){
					if($descArray['FORMAT_TYPE']=="image") $isimage = true; 
				}
				if(isset($attribs[$key][$rand[$i]]) && (!empty($attribs[$key][$rand[$i]])  || $attribs[$key][$rand[$i]]=="0")){ 
					$class="";
					$val = $attribs[$key][$rand[$i]];
				}
				else{
					$class='style="color:red;"';
					$accurate = "";
					$val = "EMPTY_FIELD";
					$empty = "checked";
				}
				echo "<td>".$key."</td>";
	    	    echo "<td $class>";
				if($isimage && $val != "EMPTY_FIELD"){ echo '&nbsp;<a href="'.$val.'" style="float:right;" target="_blank">
					<img src="'.$val.'" title="Click to view" width="60"/></a>';
				}
				echo "$val</td>";
	    	    echo '
	    	    <td style="width:250px;">
	    	    <div id="buttongroup_'.$key.'_'.$i.'" class="yui-buttongroup"> 
					<input id="radio1'.$key.$i.'" type="radio" name="radio_'.$key.'_'.$i.'" value="Accurate" '.$accurate.'/>
					<input id="radio2'.$key.$i.'" type="radio" name="radio_'.$key.'_'.$i.'" value="In-Accurate"  />
					<input id="radio3'.$key.$i.'" type="radio" name="radio_'.$key.'_'.$i.'" value="Empty" '.$empty.'/>
				<div>
				</td>
				<script>var oButtonGroup_'.$j.'_'.$i.' = new YAHOO.widget.ButtonGroup("buttongroup_'.$key.'_'.$i.'");
				buttonArray[buttoncount++]=oButtonGroup_'.$j.'_'.$i.' ;
				buttonArray'.$i.'['.$j.']=oButtonGroup_'.$j.'_'.$i.' ;
				buttonGroup['.($i).']=buttonArray'.$i.';
				</script>
				</tr>';
				$j++;
			}
			echo '</tbody></table><br/><a href="javascript:ChangeTabs('.(($i!=$urlnum-1)?($i+1):'0').');">Next ></a></div>';
		}
				
	    echo '</div></div>';	    
	    echo '<p><input type="hidden" name="type" value="individual"/><input type="hidden" name="objectType" value="'.$objectType.'"/>
	    <input type="hidden" name="urlnum" value="'.$urlnum.'"/>
	    <input class="editorialsubmit" type="submit" name="submit" value="Reject"  onClick="return confirmReject()"/>&nbsp;&nbsp;&nbsp;';
	   $logDir = get_cfg_var("LOGDIR");
	   $reviewfull = "$logDir/$siefId/review.ini.tmp";
	   if(!file_exists($reviewfull)){
	   	echo '<input class="editorialsubmit" type="submit" name="submit" value="Add Comment"  onClick="return confirmSubmit(\'add comment\')"/>';
	    echo '<input class="editorialsubmit highlightedButtton" type="submit" name="submit" value="Proceed to full Review"  onClick="return confirmSubmit(\'proceed\')"/>';
	   }
	   else echo '<input class="editorialsubmit highlightedButtton" type="submit" name="submit" value="Submit Review"  onClick="return confirmSubmit()"/>';
	    echo '</p></form>';
	    ?>
	    <script>
	       
	       var checkedTabs = new Array();
			checkedTabs[0]=true;
			document.getElementById('CHECK0').style.display = 'block';
			
			function confirmSubmit(action) {
				for (var i = 0; i< buttonArray.length; i++) {
					var button = buttonArray[i];
					var name = button.toString().split("_");
					name[2]++;
					if(button.get('checkedButton') == null) { alert('You have not chekced the Field in Tab URL '+name[2]+' and Row '+name[1]+'. Please check and resubmit'); return false; }
				}
				//var name = oButtonGroup_4_0.toString().split("_");
			    if(checkedTabs.length < totaltabs){
					alert('You have not chekced all the Tabs. Please check and resubmit'); return false; 
				}
				if (action=='proceed'){
					if (confirm('Are you sure to submit the Editorial Judgement and proceed to URL review?')) return true;
					return false; 
				}
				else {
					if (confirm('Are you sure to submit the Editorial Judgement and add comment?')) return true;
				    return false; 
				} 
			}
			
						
			function confirmReject(){
				if (confirm('Are you sure to Reject the Editorial Judgement in the middle? You have still not completed your review')) return true;
				return false; 
			}
			
			var handleActiveTabChange = function(e) {
				var newtabindex = tabView.getTabIndex(e.newValue);
				//var oldtabindex = tabView.get("activeIndex");
				var oldtabindex = tabView.getTabIndex(e.prevValue);
				var tmpbuttons = buttonGroup[oldtabindex];
				var isAllChecked = true;
				if(checkedTabs[oldtabindex]==null){
					for (var i = 0; i< tmpbuttons.length; i++) {
						var button = tmpbuttons[i];
						if(button.get('checkedButton') == null){
							//alert("one of the checked button is not cheked");
							isAllChecked = false;
							break;
						}
					}
					if(isAllChecked) {
						document.getElementById('CHECK'+oldtabindex).style.display = 'block';
						checkedTabs[oldtabindex]=true;
					}
				}
				//confirm('Your tab index is '+ oldtabindex);
			}
			
			function ChangeTabs(nTabIndex)
			{
			    tabView.set('activeIndex', nTabIndex);
			    document.location.href='#tvcontainer';
			}
			
			
			var oConfigs = { "orientation":"left" };
        	var tabView = new YAHOO.widget.TabView('tvcontainer',oConfigs);
			//tabView.set("orientation","right");
			tabView.on ('beforeActiveTabChange', handleActiveTabChange);
			
		</script>
	    <?php
		include('footer.html');
	}
	
	/**
     * Function to generate the table for the 1st Phase of the QA. 1st phase contains top 100 extractions. 
     * This table is generated from the Raw output table. As soon as user submit the rule, we are expected to see atleast 100 extraction
     * This is because system shouldn't allow you to submit rule which has less than 100 valid extraction.
     * This function contains mix of HTML, JAVASCRIPT and PHP. Can be factored out but haven't tried as it works beautofylly as is.
     * http://twiki.corp.yahoo.com/view/Yst/SIEFRuleValidationOnline#Reviewer_Role
	 * @param unknown_type $outFilename
	 * @param unknown_type $siefId
	 * @param unknown_type $reviewstatus
	 */
	public function generateEditorial($outFilename,$siefId,$reviewstatus=""){
		
		include('editorialHeader.html');
		$urlnum = 0;
		$attribs = array();
		$urlarray = array();
		$objectType=CommonUtil::getObjectType($siefId);
		self::generateArray($outFilename,"",$attribs,$urlnum,$urlarray,$objectType);
		$total=count($attribs);
		
		if($reviewstatus=="Being_Reviewed"){
			$siefRule = CommonUtil::getSiefRules(array("siefId"=>$siefId,"limit"=>1,"msgkey"=>'reviewstatus','requestedAttributes'=>'email'),"getRuleLog");
			$emailaddr = "";
			if(isset($siefRule[$siefId][0]['email'])) $emailaddr = "by ".$siefRule[$siefId][0]['email'];
			echo '<span style="color:red;">This Rule is previously Being Reviewed '.$emailaddr.' and currently by you. Enjoy!!!</span><br/><br/>';
		}
		CommonUtil::executeSiefUpdate(array("siefId"=>$siefId,"reviewstatus"=>'Being_Reviewed' ),$siefId);
		echo '<script>var buttonArray = new Array();
					  var buttonGroup = new Array();
		              var buttoncount = 0;
		              var totaltabs = '.$total.' ;
		      </script>';
		echo '<form action="judgeEditorial.php?crumb='.getenv('.bycrumb').'" enctype="multipart/form-data" method="post" style="left" >';
		echo '<input type="hidden" name="siefId" value="'.$siefId.'" />';
		echo '<div id="tvcontainer" class="yui-navset">
	    <ul class="yui-nav">';
		$i=0;
		if($urlnum > REVIEW_ATTRIBUTE_URL) $urlnum = REVIEW_ATTRIBUTE_URL;
		
		$count=0;
		foreach($attribs as $key=>$value){ $i++;
			$class ="";
			if($i==1) $class='class="selected"'; 
			echo "<li $class >".configHelper::getHelpString($key,$count,$total,$objectType)."</li>";
			$count++;
		}
		echo '</ul><div class="yui-content">';		
		$j=0;
		foreach($attribs as $key=>$value){ $j++;
			//echo '<script>var buttonArray'.$j.' = new Array();</script>';
			
			echo '<div id="'.$key.'"  style="background-color: rgb(242, 242, 242); "><table id="rulesTable'.$key.'"  class="border"  border="1" cellpadding="5" align="center" style="background-color: #EEEEEE; text-align: center; border: 1px solid navy;" >
				<thead>
				<tr>
					<th rowspan="1" colspan="1" class="yui-dt-first">'.$key.'</th>
					<th rowspan="1" colspan="1">Count</th>
					<th rowspan="1" colspan="1" class="yui-dt-last">Editorial Judgement</th>
				</tr>
			    </thead>
			    <tbody>';
			echo '<script>var buttonArray'.$j.' = new Array();</script>';
			$col = array();
			for ($i=0;$i<$urlnum;$i++){	
				if(isset($attribs[$key][$i]) && (!empty($attribs[$key][$i]) || $attribs[$key][$i]=="0")){ if(!isset($col[$attribs[$key][$i]])) $col[$attribs[$key][$i]] = 1;
													else $col[$attribs[$key][$i]] += 1; }
				else {
					if(!isset($col['EMPTY_FIELD'])) $col['EMPTY_FIELD'] = 1;
					else $col['EMPTY_FIELD'] += 1;
				}
				
			}
			arsort($col,SORT_NUMERIC);
			$descArray = configHelper::getDescriptionArray($key,$objectType,$attribType);
			$isimage = false;
			if(!empty($descArray['FORMAT_TYPE'])){
				if($descArray['FORMAT_TYPE']=="image") $isimage = true; 
			}
			$i=0;
			foreach($col as $val=>$count){
				if($i%2 == 0)
				echo '<tr class="yui-dt-first yui-dt-even">';
				else echo '<tr class="yui-dt-first yui-dt-odd">';
				$class = '';
				$accurate = "checked";
				//$accurate = "";
				$empty = "";
				if($val == "EMPTY_FIELD") {
					$class='style="color:red;"';
					$accurate = "";
					$empty = "checked";
				}
				echo "<td $class>";
				if($isimage && $val != "EMPTY_FIELD"){ 
					$val=yiv_get_url($val);
					echo '&nbsp;<a href="'.$val.'" style="float:right;" target="_blank">
					<img src="'.$val.'" /></a>';
				}
				echo "$val</td>";
	    	    echo "<td><input type='hidden' name='count_$key"."_"."$i' value='$count'/>$count</td>";
	    	    echo '
	    	    <td style="width:250px;">
	    	    <div id="buttongroup_'.$key.'_'.$i.'" class="yui-buttongroup"> 
					<input id="radio1'.$key.$i.'" type="radio" name="radio_'.$key.'_'.$i.'" value="Accurate" '.$accurate.'/>
					<input id="radio2'.$key.$i.'" type="radio" name="radio_'.$key.'_'.$i.'" value="In-Accurate"  />
					<input id="radio3'.$key.$i.'" type="radio" name="radio_'.$key.'_'.$i.'" value="Empty" '.$empty.'/>
				<div>
				
				<script>var oButtonGroup_'.$j.'_'.$i.' = new YAHOO.widget.ButtonGroup("buttongroup_'.$key.'_'.$i.'");
				buttonArray[buttoncount++]=oButtonGroup_'.$j.'_'.$i.' ;
				buttonArray'.$j.'['.$i.']=oButtonGroup_'.$j.'_'.$i.' ;
				buttonGroup['.($j-1).']=buttonArray'.$j.';
				</script>
				</td>
				</tr>';
				$i++;
			}
			echo '</tbody></table><br/><a href="javascript:ChangeTabs('.(($j!=$total)?($j):'0').','.$total.');">Next ></a></div>';
		}
	    echo '</div>
	   </div>';
	    
	    echo '<p><input type="hidden" name="type" value="full"/><input type="hidden" name="objectType" value="'.$objectType.'"/>
	    <input type="hidden" name="urlnum" value="'.$urlnum.'"/>
	    <input class="editorialsubmit" type="submit" name="submit" value="Reject"  onClick="return confirmReject()"/>
		&nbsp;&nbsp;&nbsp;<input class="editorialsubmit" type="submit" name="submit" value="Add Comment"  onClick="return confirmSubmit(\'add comment\')"/>';
	   $logDir = get_cfg_var("LOGDIR");
	   $reviewindi = "$logDir/$siefId/review20.ini.tmp";
	   //if(!file_exists($reviewindi))
	    echo '&nbsp;&nbsp;&nbsp;<input class="editorialsubmit highlightedButtton" type="submit" name="submit" value="Proceed to Url Review"  onClick="return confirmSubmit(\'proceed\')"/>';
	   
	   echo '</p></form>';
	    ?>
	    <script>
			var checkedTabs = new Array();
			checkedTabs[0]=true;
			document.getElementById('CHECK0').style.display = 'block';
			document.getElementById('HELP0').style.display = 'block';
			
			function confirmSubmit(action) {
				for (var i = 0; i< buttonArray.length; i++) {
					var button = buttonArray[i];
					var name = button.toString().split("_");
					name[2]++;
					if(button.get('checkedButton') == null) { alert('You have not chekced the Field in Tab '+name[1]+' and Row '+name[2]+'. Please check and resubmit'); return false; }
				}
				//var name = oButtonGroup_4_0.toString().split("_");
				if(checkedTabs.length < totaltabs){
					alert('You have not chekced all the Tabs. Please check and resubmit'); return false; 
				}
				
				if (action=='proceed'){
					if (confirm('Are you sure to submit the Editorial Judgement and proceed to URL review?')) return true;
					return false; 
				}
				else {
					if (confirm('Are you sure to submit the Editorial Judgement and add comment?')) return true;
				    return false; 
				}
			}
			
			function confirmReject(){
				if (confirm('Are you sure to Reject the Editorial Judgement in the middle? You have still not completed your review')) return true;
				return false; 
			}
			
			
			
			var handleActiveTabChange = function(e) {
				var newtabindex = tabView.getTabIndex(e.newValue);
				//var oldtabindex = tabView.get("activeIndex");
				var oldtabindex = tabView.getTabIndex(e.prevValue);
				var tmpbuttons = buttonGroup[oldtabindex];
				var isAllChecked = true;
				if(checkedTabs[oldtabindex]==null){
					for (var i = 0; i< tmpbuttons.length; i++) {
						var button = tmpbuttons[i];
						if(button.get('checkedButton') == null){
							//alert("one of the checked button is not cheked");
							isAllChecked = false;
							break;
						}
					}
					if(isAllChecked) {
						document.getElementById('CHECK'+oldtabindex).style.display = 'block';
						checkedTabs[oldtabindex]=true;
					}
				}
				//confirm('Your tab index is '+ oldtabindex);
			}
			
			function ChangeTabs(nTabIndex,total)
			{
			    tabView.set('activeIndex', nTabIndex);
			    Toggle(nTabIndex,total);
			    document.location.href='#tvcontainer';
			}
			
			var oConfigs = { "orientation":"left" };
        	var tabView = new YAHOO.widget.TabView('tvcontainer',oConfigs);
			//tabView.set("orientation","right");
			tabView.on ('beforeActiveTabChange', handleActiveTabChange); 
			
			
			function displayhelp(){
			  	if(document.getElementById("anup").style.display == 'none'){
								document.getElementById("anup").style.display = '';
							}
							else {
								document.getElementById("anup").style.display = 'none';
							}
			}
			
			function Toggle(IDS,n) {
  				for (i=0; i<n; i++) { document.getElementById('HELP'+i).style.display='none'; }
  				document.getElementById('HELP'+IDS).style.display = 'block';
  			} 
		</script>
	    <?php
		include('footer.html');
	}
	
}
?>

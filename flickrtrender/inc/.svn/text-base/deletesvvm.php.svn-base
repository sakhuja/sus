<?php
/**
 * This function delete the SVVM rule from production database given the siefId and action.
 * @author anupg@yahoo-inc.com
 */
	require_once('../inc/CommonUtil.php');
	$siefId="";
	if(!getenv('.bycrumbvalid')){
                header("Location: /error.php");
                exit;
        }
	if (!empty($_REQUEST["siefId"])){
				$siefId = $_REQUEST["siefId"];
	}else die("<span style=\"color: red;\">siefId param is missing. Please enter a valid siefId</span>");

	$action="";
	if (!empty($_REQUEST["action"])){
				$action = $_REQUEST["action"];
	}else die("<span style=\"color: red;\">action param is missing. Please enter a valid action on $siefId</span>");
	
	$svvmurl = get_cfg_var("svvmurl");	
	$message = "";
	
			
	$params = array();
	$params['siefId'] = $siefId;
	$params['requestedAttributes'] = "xslName,domain,xslt,siefId,svvmRuleId,svvmstate";
	$siefRule = CommonUtil::getSiefRules($params,"getSiefRule");
	if($action="remove"){
		if(isset($siefRule[$siefId]["xslName"])){
			$row=$siefRule[$siefId];
				$svvmQry = array();
				$url = "";
				if($action=="remove"){
					$svvmQry['ruleId'] = $row['svvmRuleId'];
					$svvmQry['state']="Inactive";
					$url = "$svvmurl/svvm/datastore/v2/updateRule";
					//$url = "$svvmurl/svvm/datastore/v2/deleteRule";
				}
				
	    		$ridArray = updateSvvm($svvmQry,$url,$message);
	    		
	    		if (!empty($ridArray['ruleId'])){
	    			$ruleId = $ridArray['ruleId'];
	    			$state = $svvmQry['state'];
	    			$valid =0;
	    			$output = CommonUtil::executeSiefUpdate(array("svvmstate" => $state ,'svvmRuleId' => $ruleId, "validationcount" => $valid),$siefId);
					if ( array_key_exists("error", $output)) {die (print json_encode($output));}
	    		}
		  }
		}
		
		$params = array();
		$params['siefId'] = $siefId;
		$params['referrer'] = "submitsvvm";
		if(!empty($message)) $params['msg'] = $message;
		$strParameters = http_build_query($params);
		if (isset($_REQUEST["referrer"]) && $_REQUEST["referrer"]=="editor")
			header("Location: /editor/editor.php?".http_build_query($params));
		else
			header("Location: /review/review.php?".http_build_query($params));

		
function updateSvvm(&$svvmQry,$url,&$message){
	$strParameters = http_build_query($svvmQry);
	//echo $strParameters;
	$ch = curl_init($url);
	// Set options
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $strParameters);
	
	$data = curl_exec($ch);
    curl_close($ch);
    
    $ridArray = json_decode($data,true);
	//echo $data;
    if (!is_array($ridArray)){
    	//echo "<span style=\"color: red;\">Webservice is not working properly</span>";
    	$message = "Webservice is not working properly";
    }
    else if(!empty($ridArray['error'])){
    	//echo "<span style=\"color: red;\">".$ridArray['error']."</span>";
    	$message = $ridArray['error'];
    }
    else return $ridArray;
}

?>

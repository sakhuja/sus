<?php
/**
 * This function reset the validation count to 0. This is called when editor press the re-validate button meaning the 
 * rule is revalidated and is good.
 * @author anupg
 */
	require_once('CommonUtil.php');
	$siefId="";
	if(!getenv('.bycrumbvalid')){
                header("Location: error.php");
                exit;
        }
	if (!empty($_REQUEST["siefId"])){ $siefId = $_REQUEST["siefId"];}
	else die("<span style=\"color: red;\">siefId param is missing. Please enter a valid siefId</span>");
	
	$type="editor";
	if(!empty($_REQUEST["type"])){ $type = $_REQUEST["type"];}
	
	$msg="";
	$params = array();
	$params['siefId'] = $siefId;
	$params['requestedAttributes'] = "validationcount,svvmstate";
	$siefRule = CommonUtil::getSiefRules($params,"getSiefRule");
			
	if(isset($siefRule[$siefId])){
		$valid = $siefRule[$siefId]['validationcount'];
		$svvmstate = $siefRule[$siefId]['svvmstate'];
		if($svvmstate == "Not_Submitted"){
			$msg = "Please submit the rule in SVVM production First and then Validate.";
		}
		elseif($svvmstate != "Valid"){
			$msg="This is not a current Valid rule SVVM production.  Wait for it to be valid.";
		}
		else{
			$valid = 0;
			$output = CommonUtil::executeSiefUpdate(array("siefId"=>$siefId, "validationcount" => $valid ),$siefId);
			if ( array_key_exists("error", $output)) {die (print json_encode($output));}
		}
	}
	
	else{
		$msg="Update is unsuccessful. Please try againg later.";
	}
	$params = array();
	$params['siefId'] = $siefId;
	$params['referrer'] = "validate";
	if(!empty($message)) $params['msg'] = $message;
	$strParameters = http_build_query($params);
	
	if($type == "review")
		header("Location: /review/review.php?".$strParameters);
	else
		header("Location: /editor/editor.php?".$strParameters);
	
?>

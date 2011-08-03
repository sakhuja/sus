<?php
/**
 * File for adding comments to the individual rule. It takes siefId and comment string as parameter
 * @author anupg
 */
	require_once('CommonUtil.php');
	$comment="";$siefId="";$fmeasure=0;
	if(!empty($_POST['comment'])) {
		$comment = $_POST['comment'];
		$comment = yahoo_get_data(YIV_REQUEST, 'comment', YIV_FILTER_UNSAFE_RAW);
	
	}
	if (!empty($_REQUEST["siefId"])){ $siefId = $_REQUEST["siefId"];}
	else die("<span style=\"color: red;\">siefId param is missing. Please enter a valid siefId</span>");
	
	$comment = trim($comment);
	$type="";
	if(!empty($_POST['type'])) $type = $_POST['type'];
	
	if(!empty($type) && !empty($comment)){ 
		$commentType = "review";  // For combining reviewer and editor comment logs
		$logDir = get_cfg_var("LOGDIR");
	
		$reviewComment = "$logDir/$siefId/$commentType.comment.txt";
		$params = array();
		$params['siefId'] = $siefId;
		$params['requestedAttributes'] = $commentType."log";
		$siefRule = CommonUtil::getSiefRules($params,"getSiefRule");
				
		if(isset($siefRule[$siefId])){
			$commentlog = json_decode($siefRule[$siefId][$commentType.'log'],true);
		}
		if(!is_array($commentlog)) $commentlog = array();
		$email = getenv('_byuser')."@yahoo-inc.com";
		$time = time();
		$commentlog[$time]['email'] = $email;
		$commentlog[$time]['log'] = $comment;
		$commString = json_encode($commentlog);
		file_put_contents($reviewComment,$commString);
				$output = CommonUtil::executeSiefUpdate(array("siefId"=>$siefId, $commentType."log" => $commString ),$siefId);
		if ( array_key_exists("error", $output)) {die (print json_encode($output));}
		if($type == "review")
			header("Location: /review/review.php?siefId=$siefId&referrer=comment");
		else
			header("Location: /editor/editor.php?siefId=$siefId&referrer=comment");
	}
	
	elseif($type == "review")
		header("Location: /review/review.php?siefId=$siefId&referrer=comment");
	else
		header("Location: /editor/editor.php?siefId=$siefId&referrer=comment");

?>

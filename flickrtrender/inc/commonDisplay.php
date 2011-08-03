<?php
require_once('MySQLConnectionFactory.php');
require_once('util.php');
require_once('CommonUtil.php');
require_once('constants.php');
/**
 * Class contains all the public utility diplay function. It contains a mix of PHP and HTML code.
 * @author anupg
 */
class commonDisplay {
	
	/**
	 * Utility function for displaying the XSLT page. Refactored because used in multiple places.
	 * @param unknown_type $siefId
	 * @param unknown_type $url
	 * @param unknown_type $redirpage
	 */
	public static function displayXsltPage($siefId,$url="",$redirpage="/quickCheck.php"){
		$siefRules = CommonUtil::getSiefRules(array('siefId'=>$siefId));
		if(isset($siefRules['siefRules'])){
			self::displayXsltTable($siefRules['siefRules'][0],$redirpage,$url);
		}
	}
	
	public static function displayCompleteXslt($siefId){
		$siefRules = CommonUtil::getSiefRules(array('siefId'=>$siefId));
		if(isset($siefRules['siefRules'][0])){
			$row=$siefRules['siefRules'][0];
			$loadcontent = "";
                	$xsltFile = $row["xslName"];
                	$svvmstate = $row["svvmstate"];
                	$logDir = get_cfg_var("LOGDIR");
                	$template = $row["template"];
                	$Filename = "$logDir/$siefId/$xsltFile";
                	if(file_exists($Filename)){
                        	$loadcontent = file_get_contents($Filename);
                	}
			else{
				$params = array();
                        	$params['siefId'] = $siefId;
                        	$params['requestedAttributes'] = 'xslt,xslName';
                        	$siefRule = CommonUtil::getSiefRules($params,"getSiefRule");
                        	if(empty($loadcontent) && isset($siefRule[$siefId]['xslt']))
                        		$loadcontent = $siefRule[$siefId]['xslt'];
                        		file_put_contents($Filename,$loadcontent);
			}
                	$lines = explode("\n", $loadcontent);
                	$count = count($lines);
                	//$loadcontent = htmlspecialchars($loadcontent); 
                	$line = "";
                	for ($a = 1; $a < $count+1; $a++) {
                        	$line .= "$a\n";
                	}
			?>
			<table align="center" order="0" cellspacing="1" cellpadding="1" >
                  	<tr>
                    		<td width="2%"  valign="top" ><pre style="text-align: right; padding: 4px; overflow: auto; border: 0px groove; font-size: 12px; padding-top: 0px; margin-top: 4px" name="lines" cols="4" rows="<?php echo $count+3;?>"><?php echo $line;?></pre></td>
                    <td width="96%"  valign="top"><textarea style="text-align: left; padding: 0px; overflow: auto; border: 3px groove; font-size: 12px; width:100%;" name="savecontent" rows="<?php echo $count;?>" wrap="OFF"><?php echo htmlspecialchars($loadcontent)?></textarea></td>
                    <td width="2%"  valign="top" ><pre style="text-align: right; padding: 1px; overflow: auto; border: 0px groove; font-size: 12px; padding-top: 0px; margin-top: 4px" name="lines" cols="3" rows="<?php echo $count+3;?>"></pre></td>
                  </tr>
                </table>

			<?php
				
		}
	}
	
	 /**
     * Utility function for displaying the XSLT table with row number along side. Refactored because used in multiple places.
     * @param unknown_type $siefId
     * @param unknown_type $url
     * @param unknown_type $redirpage
     */
	public static function displayXsltTable(&$row,$redirpage="editor.php",$url=""){
		$loadcontent = "";
		$siefId = $row['siefId'];
		$xsltFile = $row["xslName"];
		$svvmstate = $row["svvmstate"];
		$logDir = get_cfg_var("LOGDIR");
		$template = $row["template"];
		$Filename = "$logDir/$siefId/$xsltFile";
		if(file_exists($Filename)){
			$loadcontent = file_get_contents($Filename);
		}
		else{
			$params = array();
			$params['siefId'] = $siefId;
			$params['requestedAttributes'] = 'xslt,xslName';
			$siefRule = CommonUtil::getSiefRules($params,"getSiefRule");
			if(empty($loadcontent) && isset($siefRule[$siefId]['xslt']))
			$loadcontent = $siefRule[$siefId]['xslt'];
			file_put_contents($Filename,$loadcontent);
		}
		$loadcontent = CommonUtil::getStub($loadcontent,$template);
	
		$lines = explode("\n", $loadcontent);
		$count = count($lines);
		//$loadcontent = htmlspecialchars($loadcontent); 
		global $line_offset;
	 	$offset = @$line_offset["$template"];
		$line = "";
		for ($a = $offset; $a < $count+$offset; $a++) {
			$line .= "$a\n";
		}
		
		echo '<br/><table border="0" cellpadding="0" align="center" class="border"
		style="text-align: left; border: 1px solid navy; width:100%;min-width:700px; ">
		<thead style="text-align: center; "><tr>
		<th class="thHighlight" onclick="showXslt()"><div id="xsltRA" class="rightarrow">&raquo;</div><div id="xsltDA" class="downarrow" style="display: none;">&laquo;</div>Edit XSLT - <span style="color: red;"><a style="text-decoration:none;" href="/view.php?fileType=xslt&siefId='.$siefId.'">'.$siefId.'</a></span></th>
		</tr></thead><tbody id="xsltTable" ><tr><td>';
		?>
		<script>
		function confirmSave() {
		  if (confirm("Are you sure want to save the file? It will overwrite previous file and Your test become Obsolete. You have to re-run the rule.")) return true;
		  return false; 
		}
		</script>
		<form method=post action="/editor/savexslt.php?crumb=<?php echo getenv('.bycrumb'); ?>"> 
		<p style="text-align: center;"><input  <?php if($svvmstate=="Submitted" || $svvmstate=="Valid" || $svvmstate=="New") echo "disabled"; ?>  class="editorialsubmit highlightedButtton"  type="submit" name="save_file" value="Save" onClick="return confirmSave()"/></p>    
		<table align="center" order="0" cellspacing="1" cellpadding="1" >
		  <tr>
		    <td width="2%"  valign="top" ><pre style="text-align: right; padding: 4px; overflow: auto; border: 0px groove; font-size: 12px; padding-top: 0px; margin-top: 4px" name="lines" cols="4" rows="<?php echo $count+3;?>"><?php echo $line;?></pre></td>
		    <td width="96%"  valign="top"><textarea style="text-align: left; padding: 0px; overflow: auto; border: 3px groove; font-size: 12px; width:100%;" name="savecontent" rows="<?php echo $count;?>" wrap="OFF" <?php if($svvmstate=="Submitted" || $svvmstate=="Valid" || $svvmstate=="New") echo "readonly"?>><?php echo htmlspecialchars($loadcontent)?></textarea></td>
		    <td width="2%"  valign="top" ><pre style="text-align: right; padding: 1px; overflow: auto; border: 0px groove; font-size: 12px; padding-top: 0px; margin-top: 4px" name="lines" cols="3" rows="<?php echo $count+3;?>"></pre></td>
		  </tr>
		</table>
		<input type="hidden" name="siefId" value="<?php echo $siefId; ?>"/>
		<input type="hidden" name="page" value="<?php echo $redirpage; ?>"/>
		<?php if(!empty($url)){ echo '<input type="hidden" name="url" value="'.$url.'"/>';} ?>
		<p style="text-align: center;"><input class="editorialsubmit highlightedButtton"  type="submit" name="save_file" value="Save" onClick="return confirmSave()"  <?php if($svvmstate=="Submitted" || $svvmstate=="Valid" || $svvmstate=="New") echo "disabled"; ?> /></p>    
		</form>
		<?php 
		echo '</td></tr></tbody></table>';
	}
	
	/**
	 * Input table displayed in multiple places
	 * @param unknown_type $row
	 * @param unknown_type $tablewidth
	 */
	public static function displayInputTable(&$row,$tablewidth=true){
		$siefId = $row['siefId'];
		$xsltFile = $row["xslName"];
		$svvmstate = $row["svvmstate"];
		$rulemarket = $row['market'];
		$domain = $row['domain'];
		$logDir = get_cfg_var("LOGDIR");
		$inputFilename = "$logDir/$siefId/$xsltFile.input";
		if(!file_exists($inputFilename)){
			CommonUtil::createInputFile($siefId,$xsltFile);
		}
		$inputArr = file($inputFilename);
		$database = trim($inputArr[1]);
		$numUrl = trim($inputArr[5]);
		$email = trim($inputArr[7]);
		$cdxcore = trim($inputArr[0]);
		$scope = trim($inputArr[10]);
		$wildcardPattern = trim($inputArr[9]);
		$fetchPattern = trim($inputArr[4]);
		$random = trim($inputArr[6]);
		
		$markets=array('US','IN','UK','FR','MX','ES','IT','DE','BR','AR','PH','MY','ID','SG','VN','AU','NZ','CA','JP','TW','KR','CN','E1','QC','TH','HK');
		$width = "";$twidth="";
		if($tablewidth) $twidth="width: 100%;";
		
		$isReadOnly = false;
		if($svvmstate=="Submitted" || $svvmstate=="Valid" || $svvmstate=="New") $isReadOnly = true;
		
		$reasonMsg = "";
		$showReasonTable = false;
		if($svvmstate=="Inactive" || $svvmstate=="Invalid")
		{
			$showReasonTable = true;
			$reasonMsg=CommonUtil::getRuleBreakReason($siefId,'reason');
		}

		echo '<br/><table border="0" cellpadding="0" align="center" class="border"
		style="background-color: #F8F8F8; text-align: left; border: 1px solid navy; '.$twidth.'">';
		if($showReasonTable){
		echo '<thead style="text-align: center; "><tr>
                <th class="thHighlight" onclick="showReason()"><div id="reasonRA" class="rightarrow">&raquo;</div><div id="reasonDA" class="downarrow" style="display: none;">&laquo;</div>Rule Breakage Reason</th>             </tr></thead><tbody id="reasonTable" ><tr><td><textarea id="reason" name="reason"  rows="3" style="width: 98%;" readonly="readonly">'.$reasonMsg.'
		</textarea></td></tr>'; }
		echo '<thead style="text-align: center; "><tr>
		<th class="thHighlight" onclick="showInput()"><div id="inputRA" class="rightarrow">&raquo;</div><div id="inputDA" class="downarrow" style="display: none;">&laquo;</div>Edit Input - <span style="color: red;"><a style="text-decoration:none;" href="/view.php?fileType=input&siefId='.$siefId.'">'.$siefId.'</a></span></th>
		</tr></thead><tbody id="inputTable" ><tr><td>';
		?>
		
		<form action="/editor/saveinput.php?crumb=<?php echo getenv('.bycrumb');?>" enctype="multipart/form-data" method="post" style="left" >
		<table id="innerInputTable" style="width:100%" cellpadding="5" align="center" class="border">
		<tr><td> Database </td> <td> 
		<select name="db" <?php if($svvmstate=="Submitted") echo "disabled";?>>
		    <option <?php if($database=="fconly") echo "selected";?> >
		    fconly
		    </option>
		    <option <?php if($database=="konly") echo "selected";?> >
		    konly
		    </option>
		    <option <?php if($database=="m10nonly") echo "selected";?> >
		    m10nonly
		    </option>
		    <option <?php if($database=="wowonly") echo "selected";?> >
		    wowonly
		    </option>
		    <option <?php if($database=="deonlynrm") echo "selected";?> >
		    deonlynrm
		    </option>
		    <option <?php if($database=="extonly") echo "selected";?> >
		    extonly
		    </option>
		</select>   <a href="http://ws-ops.inktomi.com/blender-table.html" target="UnagiHelp">Database</a>
		<br/> </td></tr>
		<tr><td>CDXCORE Version (Default: latest_debug)</td> <td> 
		<select name="cdxcore" <?php if($svvmstate=="Submitted") echo "disabled";?>>
		<?php 
			$cdxcoreDir = get_cfg_var("SIEF_VALIDATOR_PEAR_DIR")."/scripts/cdxcore/";
			if (!is_dir($cdxcoreDir)){
				echo "<option selected>cdx95_debug</option>";
		    }
		    else{
		    	$filelist=array();
				if ($handle = opendir($cdxcoreDir)) {
				    while (false !== ($file = readdir($handle))) {
				        if ($file != "." && $file != "..") {
				        	//echo linkinfo(readlink($cdxcoreDir.$file));
				        	if( file_exists(realpath ($cdxcoreDir.$file))){
				        		$filelist[]=$file;
				            	//echo "<option >$file</option>";
				        	}
				        }
				    }
				    closedir($handle);
				}
				$reverseFlist = array_reverse($filelist);
				foreach($reverseFlist as $file){ 
					if(strpos($cdxcore,$file)===FALSE)  echo "<option >$file</option>";
					else echo "<option selected >$file</option>";
				}
		    }
		?>
		</select> 
		<br/> </td></tr>
		<tr><td>Domain</td> <td> <input <?php if($isReadOnly) echo "readonly";?> type="text" name="domain" value="<?php echo $domain; ?>"/> <br/> </td></tr>
		<tr><td>Wildcard Pattern ( e.g. *.yahoo.com/*) </td> <td>  <input <?php if($isReadOnly) echo "readonly";?> type="text" name="wdpat" value="<?php echo $wildcardPattern; ?>" SIZE="50" <?php if($svvmstate!="Not_Submitted") echo "";?> />&nbsp;&nbsp;<a href="#" onclick="advanceFetchPattern(this)" TITLE="Click Here to Enter Fetch Pattern">Fetch Pattern</a> <br/> </td></tr>
		<!-- Fetch Pattern ( e.g. site:local.yahoo.com path:details): <input type="text" name="fcpat" value="" /> <br/>  -->
		<tr id="advanceSearchBar" style="display:none; background-color: #D6D6D6;"><td>Fetch Pattern (For Advanced User) (optional)</td> <td> <input <?php if($isReadOnly) echo "readonly"; ?> type="text" name="fetchpat" value="<?php echo $fetchPattern; ?>" SIZE="50" <?php if($svvmstate!="Not_Submitted") echo "";?> /> </td></tr>
		<tr><td>Number of URL: (Default: Random Pick)</td> <td> <input <?php if($svvmstate=="Submitted") echo "readonly";?> type="text" name="numUrl" value="<?php echo $numUrl; ?>"/>    Sort By Relevence:<input <?php if($isReadOnly) echo "readonly";?> type="checkbox" value="not_random" name="random" <?php if($random=="not_random") echo "checked"; ?>> <br/> </td></tr>
		<tr><td>Email Address (Default: your Yahoo ID login)</td> <td> <input <?php if($svvmstate=="Submitted") echo "readonly";?> type="text" name="email" value="<?php echo $email; ?>"   <?php if($svvmstate!="Not_Submitted") echo "";?>/> <br/> </td></tr>

		<tr><td>Market : </td><td>
		<select name="market" <?php if($svvmstate=="Submitted" || $svvmstate=="New") echo "disabled";?>>

		<?php foreach($markets as $market){echo "<option value=$market "; if (!empty($rulemarket) && $market==$rulemarket) echo " selected "; echo" >$market</option>\n";} ?>
		</select><br/></td></tr>
		<tr><td>Progress : </td><td><select name="progress" id="progress" <?php if($svvmstate=="Submitted") echo "disabled";?>  onChange="javascript:changeState(this.value);">
		<?php 
			//Added new progress states Manual Push, Bug victim, Low coverage, In-Extractable, Archive, Revalidate
			$progresss=array('Unassigned','Working','Escalate','Quit','QA','Done','Manual Push','Bug victim','Low coverage','In-Extractable','Archive','Revalidate','Audit');
			$siefRule = CommonUtil::getSiefRules(array('siefId'=>$siefId,'requestedAttributes'=>"progress"),"getSiefRule");
			$ruleprogress ="";
			if(isset ($siefRule[$siefId]['progress'])){
				$ruleprogress =$siefRule[$siefId]['progress']; 
			}
			 foreach($progresss as $progress){ echo '<option value="'.$progress.'"'; if (!empty($ruleprogress) && $progress==$ruleprogress) echo"'selected"; echo' >'.$progress.'</option>\n'; } ?>
		</select><br/></td></tr>
		<tr><td> Scope (Default: private) </td> <td> 
		<select disabled name="scope" >
		    <option  <?php if($scope=="private") echo "selected";?> >
		    private
		    </option>
		    <option  <?php if($scope=="public") echo "selected";?> >
		    public
		    </option>
		</select>
		<br/> </td></tr>
		</table>
		<input type="hidden" name="siefId" value="<?php echo $siefId ?>" /> 
		<p style="text-align: center;"><input <?php if($svvmstate=="Submitted") echo "disabled";?> class="editorialsubmit" type="submit" name="submit" value="Save" />&nbsp;&nbsp;&nbsp;&nbsp; 
		<input <?php if($svvmstate=="Submitted") echo "disabled";?> class="editorialsubmit highlightedButtton" type="submit" name="submit" value="Save and Re-Run"  onClick="return confirmSaveAndRerun()"/>
		</p>
		</form>
	<?php 
		echo '</td></tr></tbody></table>';
		echo '<script>
			oldState = document.getElementById("progress").selectedIndex;
                        function changeState(val)
                                {
                                if (val=="Unassigned"|| val=="Quit" || val=="Working" || val=="QA" || val=="Done" || val=="Revalidate")
                                        {
					alert("It\'s an automated progress state. You can not change it manually.");
                                        document.getElementById("progress").selectedIndex=oldState;
                                        }else{
                                                return true;
                                                }
                                }

			function showInput() {
				if(document.getElementById("inputTable").style.display == \'none\'){
					document.getElementById("inputTable").style.display = \'\';
					document.getElementById("inputRA").style.display = \'none\';
					document.getElementById("inputDA").style.display = \'\';
				}
				else {
					document.getElementById("inputTable").style.display = \'none\';
					document.getElementById("inputRA").style.display = \'\';
					document.getElementById("inputDA").style.display = \'none\';
				}
			}
			 function showReason() {
	                                if(document.getElementById("reasonTable").style.display == \'none\'){
                                        document.getElementById("reasonTable").style.display = \'\';
                                        document.getElementById("reasonRA").style.display = \'none\';
                                        document.getElementById("reasonDA").style.display = \'\';
                                }
                                else {
                                        document.getElementById("reasonTable").style.display = \'none\';
                                        document.getElementById("reasonRA").style.display = \'\';
                                        document.getElementById("reasonDA").style.display = \'none\';
                                }
                        }

			function confirmSaveAndRerun() {
				  if (confirm("Are you sure to save input and re-run Sief Rule? It will overwrite your previous run")) return true;
				  return false; 
			}
		</script>';
	}
	
	/**
	 * Comment area used by reviewer and editor both.
	 * @param unknown_type $siefId
	 * @param unknown_type $type
	 */
	public static function displayCommentArea($siefId,$type="editor"){
		echo '<form action="../inc/addcomment.php?crumb='.getenv('.bycrumb').'" enctype="multipart/form-data" method="post" style="left" >';
		echo '<br/><table class="border tableheader" border="1">
			  <thead><tr>
				<th  class="thHighlight"   onclick="commentToggle(this)"><div id="commentRA" class="rightarrow">&raquo;</div><div id="commentDA" class="downarrow" style="display: none;">&laquo;</div>Add Comment Here</th>
			  </tr></thead><tbody  id="commenttable">
			  <tr><td align="center">';
		echo '<textarea id="comment" name="comment"  rows="3" style="width: 98%;"></textarea>';
		echo '<input type="hidden" name="type" value="'.$type.'"/>';
		echo '<input type="hidden" name="siefId" value="'.$siefId.'" />';
		echo '<br/><input class="editorialsubmit highlightedButtton" type="submit" name="submit" id="submitComment" value="Add Comment" />';
		echo "</td></tr></tbody></table>";
		echo '</form>';
		
		echo "<script>
			(function() {
			    var Dom = YAHOO.util.Dom, Event = YAHOO.util.Event;
			    var myConfig = {
			        height: '100px',
			        width: '800px',
			        animate: true,
			        dompath: true,
			        focusAtStart: true,
			        autoHeight: true,
			        handleSubmit: true
			    };
			    var myEditor = new YAHOO.widget.Editor('comment', myConfig);
			    myEditor.render();
			    
			})();
			</script>
		";
	}
	
	/**
	 * Utility function for getting the XSLT string
	 * @param unknown_type $row
	 */
	public static function getXslt(&$row){
		$loadcontent = "";
		$siefId = $row['siefId'];
		$xsltFile = $row["xslName"];
		$svvmstate = $row["svvmstate"];
		$logDir = get_cfg_var("LOGDIR");
		$template = $row["template"];
		$Filename = "$logDir/$siefId/$xsltFile";
		if(file_exists($Filename)){
			$loadcontent = file_get_contents($Filename);
		}
		else{
			$params = array();
			$params['siefId'] = $siefId;
			$params['requestedAttributes'] = 'xslt,xslName';
			$siefRule = CommonUtil::getSiefRules($params,"getSiefRule");
			if(empty($loadcontent) && isset($siefRule[$siefId]['xslt']))
			$loadcontent = $siefRule[$siefId]['xslt'];
			file_put_contents($Filename,$loadcontent);
		}
		return $loadcontent;
	}
	
	/**
	 * Function display the link bar on the right hand side of the editor and review rule pages.
	 * @param unknown_type $row
	 */
	public static function displayLinkBar(&$row){
	
		echo '<table id="linkTable" border="0" cellpadding="10" align="center"
		style="background:#ccc; text-align: center;border: 1px solid navy;">
		<thead><tr>';
		echo '<th style="border: 1px solid #999b9a;">LINKS</th>
		</tr></thead><tbody border="0"><tr><td>';
		//TODO : subsitute Ready with Not_submitted
	
		echo '<br/><a class="linkButtton" style="padding: 4px 40px;" href="/view.php?fileType=log&siefId='.$row["siefId"].'" TITLE="View Log"  target="_blank">Log</a><p/>';
		
		echo '<br/><a class="linkButtton" style="padding: 4px 26px;" href="/viewXSL.php?siefId='.$row["siefId"].'" target="_blank" TITLE="View Input">Xslt Rule</a><p/>';
		
		echo '<br/><a   class="linkButtton" style="padding: 4px 30px;" href="/output.php?siefId='.$row["siefId"].'" TITLE="View Output">Output</a><p/>';
		
		echo '<br/><a   class="linkButtton" style="padding: 4px 30px;" href="/ruleHistory.php?siefId='.$row["siefId"].'" TITLE="View Output">History</a><p/>';
	
		
		$loadcontent = self::getXslt($row);
		echo '<form action="/testXSL.php" enctype="multipart/form-data" method="POST">
    		<input type="hidden" name="xslt" value="'.htmlspecialchars($loadcontent).'"/>
    		<br/><input class="linkButtton" value="test XSLT" style="color: red;" type="submit" TITLE="test Input XSLT"/><p/>
   			</form>
		';
		
		echo '</td></tr></tbody></table>';
		
	}
	
	/**
	 * Display the Action on the left hand side of the rule homepage
	 * @param unknown_type $row
	 */
	public static function displayActionBar(&$row){
	
		echo '<table id="actionTable" border="0" cellpadding="10" align="center"
		style="background:#ccc; text-align: center; border: 1px solid navy;">
		<thead><tr>';
		echo '<th style="border: 1px solid #999b9a;">ACTIONS</th>
		</tr></thead><tbody border="0"><tr><td>';
		//TODO : subsitute Ready with Not_submitted
		
		if($row['validationcount']>0 && $row['svvmstate']=="Valid" ) //REvalidation check
		{echo '<br/><a  class="highlightedButtton" style="padding: 6px 24px;" href="/output.php?siefId='.$row['siefId'].'&type=editor" onClick="return confirmRevalidate()" TITLE="Re-Validate the rule after seeing the output" >Revalidate</a><p/>';}
		
		
		if($row['svvmstate']=="Not_Submitted" && $row['state']=="Finished"){
			echo '<br/><a class="highlightedButtton" style="padding: 6px 36px;" href="submitreview.php?siefId='.$row['siefId'].'&action=submit&crumb='.getenv('.bycrumb').'"  onclick="return confirmSubmit()" TITLE="Submit the rule for review" >Submit</a><p/>';
		}
		elseif( ($row['svvmstate']!="Not_Submitted" && $row['svvmstate']!="Submitted" && $row['svvmstate']!="New" && $row['svvmstate']!="Valid" ) && $row['state']=="Finished" ){
			echo '<br/><a class="highlightedButtton" style="padding: 6px 30px;" href="submitreview.php?siefId='.$row['siefId'].'&action=resubmit&crumb='.getenv('.bycrumb').'"  onclick="return confirmSubmit()" TITLE="ReSubmit the rule for review" >ReSubmit</a><p/>';
		}

		if($row['state']!="Testing" && $row['svvmstate']!="Submitted")
		echo '<br/><a class="highlightedButtton" style="padding: 6px 39px;" href="rerun.php?siefId='.$row['siefId'].'&crumb='.getenv('.bycrumb').'"  onclick="return confirmRerun()" TITLE="Rerun Your Rule" >Rerun</a><p/>';
		
		echo '<br/><a class="normalButtton" style="padding: 6px 30px;" href="#"  onclick="showInput()" TITLE="Edit your input" >Edit Input</a><p/>';
		echo '<br/><a class="normalButtton" style="padding: 6px 30px;" href="#"  onclick="showXslt()" TITLE="Edit your XSLT" >Edit XSLT</a><p/>';

		if($row['reviewed']=="Y")
		echo '<br/><a class="normalButtton" style="padding: 6px 20px;" href="#"  onclick="showReview()" TITLE="View the detailed Review results" >View Review</a><p/>';
		
		echo '<br/><a class="normalButtton" style="padding: 6px 16px;" href=\'#\' onclick="commentToggle(this)" TITLE="Add Comment">Add Comment</a></li><p/>';
		
		echo '<br/><a class="normalButtton" style="padding: 6px 13px;" href=\'#\' onclick="showcommentlog(this)" TITLE="View Reviewer Comment" >View Comment</a></li><p/>';
		
		//Specail cloning function For Cloning the rule [bug 3681739]
		$templateType = $row['template'];
		if(empty($templateType) || $templateType=="none" || $templateType=="Do Not Apply" ) {
			$templateType = $row['type'];
			if($templateType == "unknown") $templateType = "none";
		}
		
		echo '<br/><a class="normalButtton" style="padding: 6px 27px;" href="/selectTemplate.php?siefId='.$row['siefId'].'&template='.$templateType.'&crumb='.getenv('.bycrumb').'" onclick="return confirmClone()" TITLE="Clone and Rerun the current rule" >Clone Rule</a></li><p/>';
		
		//echo '<br/><a class="normalButtton" style="padding: 6px 27px;" href="/clonerule.php?siefId='.$row['siefId'].'" onclick="return confirmClone()" TITLE="Clone and Rerun the current rule" >Clone Rule</a></li><p/>';
		
		echo '<br/><a class="normalButtton" style="padding: 6px 49px;" href="/signup/signup.php?siefId='.$row['siefId'].'&action=quit&crumb='.getenv('.bycrumb').'" onclick="return confirmQuit()" TITLE="Gave Up/Quit from this rule" >Quit</a></li><p/>';
		
		
		if($row['state']!="Testing" && $row['svvmstate']!="Valid" && $row['svvmstate']!="New" && $row['reviewstatus']!="Being_Reviewed")
		echo '<br/><a class="normalButtton" style="padding: 6px 40px;" href="delete.php?siefId='.$row['siefId'].'&crumb='.getenv('.bycrumb').'" onclick="return confirmDelete()" TITLE="Delete the Rule from Sief test Tool">Delete</a></li>';
		
		echo '</td></tr></tbody></table>';
			
		echo '<script>
			function confirmClone() {
			  if (confirm("Are you sure to Clone Rule? It will clone this Rule with your email address.")) return true;
			  return false; 
			}
			function confirmQuit() {
			  if (confirm("Are you sure to Quit the rule? It will remove it from your queue and put it in the sing-up queue.")) return true;
			  return false; 
			}
			</script>';
		
	}
	
	/**
	 * All the javascript put together
	 * @param unknown_type $referrer
	 */
	public static function addJavascript($referrer=""){
		$inputtable="none";$xslttable="none";$reviewtable="";$commenttable="none";$commentlog="";
		if($referrer=="saveinput" || $referrer=="saveinputandrerun" || $referrer=="editinput" || $referrer=="signup" || $referrer=="") $inputtable="";
		elseif($referrer=="savexslt" || $referrer=="editxslt") $xslttable="";
		elseif($referrer=="comment") $commentlog="";
		elseif($referrer=="submitreview" || $referrer=="reviewsubmit") $reviewtable="";
		elseif($referrer=="addcomment") $commenttable="";

		echo '<script>
			if(document.getElementById("inputTable")){
				document.getElementById("inputTable").style.display = \''.$inputtable.'\';
			}
			if(document.getElementById("xsltTable")){
				document.getElementById("xsltTable").style.display = \''.$xslttable.'\';
			}
			if(document.getElementById("reviewTable")){
				document.getElementById("reviewTable").style.display = \''.$reviewtable.'\';
			}
			if(document.getElementById("commenttable")){
				document.getElementById("commenttable").style.display = \''.$commenttable.'\';
			}
			if(document.getElementById("commentlogbody")){
				document.getElementById("commentlogbody").style.display = \''.$commentlog.'\';
			}


			
			function showXslt() {
				if(document.getElementById("xsltTable").style.display == \'none\'){
					document.getElementById("xsltTable").style.display = \'\';
					document.getElementById("xsltRA").style.display = \'none\';
					document.getElementById("xsltDA").style.display = \'\';
				}
				else {
					document.getElementById("xsltTable").style.display = \'none\';
					document.getElementById("xsltRA").style.display = \'\';
					document.getElementById("xsltDA").style.display = \'none\';
				}
			}
					
			
			function displayhelp(){
			  	if(document.getElementById("anup").style.display == \'none\'){
								document.getElementById("anup").style.display = \'\';
							}
							else {
								document.getElementById("anup").style.display = \'none\';
							}
			}


			function showReview() {
				if(document.getElementById("reviewTable").style.display == \'none\'){
					document.getElementById("reviewTable").style.display = \'\';
					document.getElementById("reviewRA").style.display = \'none\';
					document.getElementById("reviewDA").style.display = \'\';
				}
				else {
					document.getElementById("reviewTable").style.display = \'none\';
					document.getElementById("reviewRA").style.display = \'\';
					document.getElementById("reviewDA").style.display = \'none\';
				}
			}
			

			function commentToggle(obj) {
				if(document.getElementById("commenttable").style.display == \'none\'){
					document.getElementById("commenttable").style.display = \'\';
					document.getElementById("commentRA").style.display = \'none\';
					document.getElementById("commentDA").style.display = \'\';
				}
				else {
					document.getElementById("commenttable").style.display = \'none\';
					document.getElementById("commentRA").style.display = \'\';
					document.getElementById("commentDA").style.display = \'none\';
				}
			}
			
			
			function advanceFetchPattern(obj) {
				if(document.getElementById("advanceSearchBar").style.display == \'none\')
					document.getElementById("advanceSearchBar").style.display = \'\';
				else {
					document.getElementById("advanceSearchBar").style.display = \'none\';
				}
			}
			function confirmDelete(){
				  if (confirm("Are you sure want to delete this SIEF rule? Once deleted You are not able to recover the rule.")) return true;
				  return false; 
			}
		  </script>';
	}
		
	/**
	 * Display the sign up home page.
	 * @param unknown_type $rows
	 */
	public static function displaySignupMetadataTable(&$rows){
		echo '<table id="rulesTable" border="0" cellpadding="5" align="center" class="border">
		<thead><tr>';
		echo '<th>ID</th>
			<th>Domain</th>
			<th>Submitted TS</th>
			<th>Market</th>
			<th>Type</th>
			<th>Test</th>
			<th>Progress</th>
			<th>Action</th>
		</tr></thead><tbody>';
		$i=1;
		foreach($rows as $row){ $i++;
			if($i%2 == 0) echo '<tr >';
			else echo '<tr style="background-color:white;">';
			
			echo '<td><a href="/editor/editor.php?siefId='.$row['siefId'].'"  TITLE="See Rule Homepage">'.$row["siefId"].'</a></td>';
			echo '<td>'.$row["domain"].'</td>';
			echo '<td>'.$row["executionTs"].'</td>';
			echo '<td>'.$row["market"].'</td>';
			echo '<td>'.$row["type"].'</td>';
			
			$color = "Red";
			if($row['state']=="Finished") $color="Green";
			echo '<td><span style="color: '.$color.';">';
			echo $row["state"].'</span></td>';
			
			echo '<td>'.$row["progress"].'</td>';
			
			
			$value = '<a  class="highlightedButtton" href="/signup/signup.php?siefId='.$row['siefId'].'&action=assign&crumb='.getenv('.bycrumb').'" onClick="return confirmSignup()" TITLE="Sign up for this domain" >Assign to Me</a>';
			
			echo '<td>'.$value.'</td>';
			
			
			echo '</tr>';
			
		}
		echo '</tbody></table>';
		echo '<script>
			function confirmSignup() {
			  if (confirm("Are you sure to Sign up for this rule? This rule will be assigned to you")) return true;
			  return false; 
			}
		  </script>';
		
	}
	
	/**
	 * Display the rule row 
	 * @param unknown_type $siefId
	 */
	public static function dispalyEditorMetadataRow($siefId){
		$siefRules = CommonUtil::getSiefRules(array('siefId'=>$siefId));
		if(isset($siefRules['siefRules'])){
			commonDisplay::displayEditorMetadataTable($siefRules['siefRules']);
		}
	}
	
	/**
	 * Table display for editor home page. It display all the editor rules.
	 * @param unknown_type $rows
	 * @param unknown_type $isPattern
	 * @param unknown_type $isIdFormat
	 */
	public static function displayEditorMetadataTable(&$rows,$isPattern=false,$isIdFormat=true){
		$upperLimit = UPPER_LIMIT;
		$lowerLimit = LOWER_LIMIT;
		echo '<table id="rulesTable" border="0" cellpadding="5" align="center" class="border">
		<thead><tr>';
		
		echo '<th>ID</th>
			<th>Domain</th>
			<th>Submitted TS</th>
			<th>Market</th>
			<th>Type</th>
			<th>Test</th>
			<th>Progress</th>
			<th>State</th>
			<th>Coverage</th>
			<th>Accuracy</th>
			<th>Action</th>';
		if($isPattern) echo '<th>Pattern</th>';
		echo '</tr></thead><tbody>';
		$i=1;
		foreach($rows as $row){ $i++;
			if($i%2 == 0) echo '<tr >';
			else echo '<tr style="background-color:white;">';
			if ($isIdFormat) echo '<td><a href="/editor/editor.php?siefId='.$row['siefId'].'"  TITLE="See Rule Homepage">'.$row["siefId"].'</a></td>';
			else echo '<td>'.$row["siefId"].'</td>';
			echo '<td>'.$row["domain"].'</td>';
			echo '<td>'.$row["executionTs"].'</td>';
			echo '<td>'.$row["market"].'</td>';
			echo '<td>'.$row["type"].'</td>';
			
			$color = "Red";
			if($row['state']=="Finished") $color="Green";
			echo '<td><span style="color: '.$color.';">';
			echo $row["state"].'</span></td>';
			
			echo '<td>'.$row["progress"].'</td>';
			
			if($row['svvmstate']=="Valid") $color = "Green";
			elseif($row['svvmstate']=="Submitted") $color = "Blue";
			elseif($row['svvmstate']=="Not_Submitted") $color = "Black";
			else $color = "Red";
			echo '<td><span style="color: '.$color.'; ">';
			if($row['svvmstate']!="Not_Submitted" && $row['svvmstate']!="Submitted" && $row['svvmstate']!="Rejected") {
				//$svvmurl = get_cfg_var("svvmurl")."/svvm/datastore/v2/getRule?ruleId=".$row['svvmRuleId'];
				$svvmurl = "../svvmoutput.php?svvmRuleId=".$row['svvmRuleId'];
				echo "<a href='$svvmurl'>";
			}
			if($row['svvmstate']!="Submitted") echo $row["svvmstate"]; 
			elseif($row['reviewstatus']=="Being_Reviewed") echo "Being Reviewed";
			else echo "Ready for Review";
			if($row['svvmstate']!="Not_Submitted" && $row['svvmstate']!="Submitted" && $row['svvmstate']!="Rejected") echo "</a>";
			//if($row['svvmstate']=="Valid") echo "&nbsp;&nbsp;&nbsp;<a style=\"color:Red;\" href=\"http://search.yahoo.com/search?p=sief:".$row['svvmRuleId'].":execution_successful\" TITLE=\"See Result in Yahoo! Search.\">Y!</a>";
			
			echo '</span></td>';
			echo '<td>'.$row["coverageNum"].'</td>';
			
			if($row['fmeasure'] == 0) $color="black";
			elseif($row['fmeasure'] > $upperLimit) $color="Green";
			elseif($row['fmeasure'] < $lowerLimit) $color="Red";
			else $color="Blue";
			echo '<td><span style="color: '.$color.'">';
			if($row['reviewed']=="N" && $row['svvmstate']=="Submitted") echo "Awaiting";
			elseif($row['reviewed']=="N") echo "n/a";
			else echo $row["fmeasure"];
			echo '</span></td>';
			
			$value = '<a class="normalButtton" style="padding: 1px 23px" disabled>n/a</a>';
			if($row['validationcount']>0 && $row['svvmstate']=="Valid" ) //REvalidation check
			{$value = '<a  class="highlightedButtton" href="/output.php?siefId='.$row['siefId'].'&type=editor" onClick="return confirmRevalidate()" TITLE="Re-Validate the rule after seeing the output" >Revalidate</a>';}
			elseif($row['state']=="Finished" && $row['svvmstate']=="Not_Submitted" ) 
			{$value = '<a  class="highlightedButtton" href="/editor/submitreview.php?siefId='.$row['siefId'].'&action=submit&crumb='.getenv('.bycrumb').'" onclick="return confirmSubmit()" TITLE="Submit the rule for review" >Submit</a>';}
			elseif($row['svvmstate']=="Rejected" && $row['state']=="Finished" )
			{$value = '<a  class="highlightedButtton" href="/editor/submitreview.php?siefId='.$row['siefId'].'&action=resubmit&crumb='.getenv('.bycrumb').'" onclick="return confirmSubmit()" TITLE="Re-Submit the rule for review" >ReSubmit</a>';}
			elseif($row['state']=="Obsolete" )
			{$value = '<a class="highlightedButtton" href="/editor/rerun.php?siefId='.$row['siefId'].'&crumb='.getenv('.bycrumb').'"  onclick="return confirmRerun()" TITLE="Rerun Your Rule" >Rerun</a>';}
			
			echo '<td>'.$value.'</td>';
			if($isPattern) echo '<td>'.$row['urlPattern'].'</td>';
			
			
			echo '</tr>';
			
		}
		echo '</tbody></table>';
		echo '<script>
			function confirmSubmit() {
			  if (confirm("Are you sure to Submit the Sief Rule? It will submit this Rule for Review")) return true;
			  return false; 
			}
			function confirmRevalidate() {
			  if (confirm("Are you sure to Re-validate the Sief Rule? Please check the output before re-validating")) return true;
			  return false; 
			}
			function confirmRerun() {
				  if (confirm("Are you sure to re-run Sief Rule? It will overwrite your previous run")) return true;
				  return false; 
			}
			
		  </script>';
		
	}
    
	public static function displayReviews($siefId,$svvmstate="Not_Submitted"){
		$logDir = get_cfg_var("LOGDIR");
		$review = "$logDir/$siefId/review.ini";
		if(file_exists($review)){
			$reviews = json_decode(file_get_contents($review),true);
			if(!empty($reviews)){
				$reviewArray = $reviews["full"];
				$review20Array = $reviews["individual"];
				echo '<br/><table border="0" class="border tableheader" >
					<thead><tr>
					   <th style="width: 50%;"  class="thHighlight"  onclick="showReview()"><div id="reviewRA" class="rightarrow">&raquo;</div><div id="reviewDA" class="downarrow" style="display: none;">&laquo;</div>Full Review Sore</th>
					   <th style="width: 50%;"  class="thHighlight"  onclick="showReview()">Individual (20 url) Review Score</th>
					</tr>
					</thead>
					<tbody  id="reviewTable">';
				echo "<tr><td>";
				self::reviewTable($siefId,$reviewArray);
				echo "</td><td>";
				self::reviewTable($siefId,$review20Array);
				echo "</td></tr></tbody></table>";
			}
		}
		elseif($svvmstate=="Submitted") echo "<div id=\"reviewTable\"><br/><span style=\"color: red;\">Reviews is not yet complete. <br/>Please wait till the review get submitted.</span></div>";
		
	}

	public static function reviewTable($siefId,&$reviewArray) {
	
		echo '<div id="statistics">	
		      <table id="Statistics_Table"  class="border tableheader"  border="1" cellpadding="5" align="center" style="background-color: #EEEEEE; text-align: center;border: 1px solid #999b9a;" >
				<thead>
				<tr>
					<th rowspan="1" colspan="1" class="yui-dt-first">Key</th>
					<th rowspan="1" colspan="1" class="yui-dt-last">Value</th>
				</tr>
			    </thead>
			    <tbody style="text-align:left;">';
	
		echo '<tr class="yui-dt-first yui-dt-even"><td>Precision</td><td>'.$reviewArray['precision'].'</td></tr>';
		echo '<tr class="yui-dt-odd"><td>Recall</td><td>'.$reviewArray['recall'].'</td></tr>';
		echo '<tr class="yui-dt-even"><td>F-Measure</td><td>'.$reviewArray['fmeasure'].'</td></tr>';
		echo '<tr class="yui-dt-odd"><td>Accurate</td><td>'.$reviewArray['accurate'].'</td></tr>';
		echo '<tr class="yui-dt-even"><td>In-Accurate</td><td>'.$reviewArray['inaccurate'].'</td></tr>';
		echo '<tr class="yui-dt-odd"><td>Empty</td><td>'.$reviewArray['empty'].'</td></tr>';
		//if(isset($reviewArray['emptyoptional']) ) echo '<tr class="yui-dt-odd"><td>Empty (Optional)</td><td>'.$reviewArray['emptyoptional'].'</td></tr>';
		echo '<tr class="yui-dt-last yui-dt-even"><td><b>Total</b></td><td><b>'.$reviewArray['totalEditorial'].'</b></td></tr>';
		echo '</tbody></table></div>';
	}
	
	/**
	 * Display the comments table in the rule homepage
	 * @param unknown_type $siefId
	 * @param unknown_type $tablewidth
	 */
	public static function commentLogTable($siefId,$tablewidth=true){
		$params = array();
		$params['siefId'] = $siefId;
		$params['requestedAttributes'] = "reviewlog,editorlog";
		$siefRule = CommonUtil::getSiefRules($params,"getSiefRule");
		$reviewcommentArray=array();$editorcommentArray=array();
		if(isset($siefRule[$siefId])){
			$editorcommentArray = json_decode($siefRule[$siefId]['editorlog'],true);
			$reviewcommentArray = json_decode($siefRule[$siefId]['reviewlog'],true);
		}
		$isreview = true; $iseditor = true;
		if(!is_array($reviewcommentArray) || empty($reviewcommentArray)) $isreview = false;
		if(!is_array($editorcommentArray) || empty($editorcommentArray)) $iseditor = false;
		
		if(!$isreview && !$iseditor ) return;
		$width = "";$twidth="width: 75%;";
		if($tablewidth) $twidth="width: 100%;";
		if($isreview && $iseditor ) $width = 'style="width: 50%;"';
		echo '<br/><table id="Comment_Table"  class="border" border="1" cellpadding="5" style="'.$twidth.' border: 1px solid navy; max-width:800px;" >
			<thead>
			<tr >';
		if ($isreview) echo '<th  class="thHighlight" '.$width.' onclick="showcommentlog()"><div id="reviewcommentlogRA" class="rightarrow">&raquo;</div><div id="reviewcommentlogDA" class="downarrow" style="display: none;">&laquo;</div>Comment Logs</th>';
		//if ($iseditor) echo	'<th  class="thHighlight" '.$width.' onclick="showcommentlog()"><div id="editorcommentlogRA" class="rightarrow">&raquo;</div><div id="editorcommentlogDA" class="downarrow" style="display: none;">&laquo;</div>Editor Comment Logs</th>';
			
		echo '</tr></thead><tbody id="commentlogbody">';
		echo '<tr VALIGN=TOP>';
		if($isreview){
			echo '<td >';
			self::renderComments($reviewcommentArray);
			echo '</td>';
		}
		/* // Combine editor and reviewer comment into one long conversation.
		if($iseditor){
			echo '<td >';
			self::renderComments($editorcommentArray);
			echo '</td>';
		}
		*/
		echo '</tr></tbody></table>';
		
		echo '<script>
			function showcommentlog(){
				if(document.getElementById("commentlogbody").style.display == \'none\'){
					document.getElementById("commentlogbody").style.display = \'\';
					document.getElementById("reviewcommentlogRA").style.display = \'none\';
					document.getElementById("reviewcommentlogDA").style.display = \'\';
					document.getElementById("editorcommentlogRA").style.display = \'none\';
					document.getElementById("editorcommentlogDA").style.display = \'\';
				}
				else {
					document.getElementById("commentlogbody").style.display = \'none\';
					document.getElementById("reviewcommentlogRA").style.display = \'\';
					document.getElementById("reviewcommentlogDA").style.display = \'none\';
					document.getElementById("editorcommentlogRA").style.display = \'\';
					document.getElementById("editorcommentlogDA").style.display = \'none\';
				}
			}
		</script>';
		
	}
	
	public static function renderComments(&$commentArray){
		
		$commentArray = array_reverse($commentArray, true);
		foreach ($commentArray as $time=>$value){
			
			echo '<div style="padding: 0px 0px 3px;"><table style="background-color: #E1E4E2;" width="100%" cellspacing="1" cellpadding="6"><tbody>';
			echo '<tr style="background-color: #5C7099; color: #FFFFFF"><td>';
			echo '<div style="float: right"> Comment By : '.$value['email'].'</div>';
			echo '<div>'.date("M d Y H:i:s", $time).'<div>';
			echo '</td></tr>';
			echo '<tr><td>'.$value['log'].'</td></tr>';
			echo '</tbody></table></div>';
			
		}
	}
	
	/**
	 * Display the table for review home page
	 * @param unknown_type $rows
	 * @param unknown_type $isIdFormat
	 */
	public static function displayReviewMetadataTable(&$rows,$isIdFormat=true){
		$upperLimit = UPPER_LIMIT;
		$lowerLimit = LOWER_LIMIT;
		echo '<table id="rulesTable" border="2" cellpadding="5" align="center" class="border">
		<thead><tr>';
		
		echo '<th>ID</th>
			<th>Domain</th>
			<th>Submitted TS</th>
			<th>Market</th>
			<th>Type</th>
			<th>State</th>
			<th>Coverage</th>
			<th>F-measure</th>
			<th>Action</th>
			<th>Cap</th>
		</tr></thead><tbody>';
		
		foreach($rows as $row){
			echo '<tr>';
			if($isIdFormat) echo '<td><a href="review.php?siefId='.$row['siefId'].'&crumb='.getenv('.bycrumb').'"   TITLE="See Rule Homepage" >'.$row["siefId"].'</a></td>';
			else echo '<td>'.$row["siefId"].'</td>';
			echo '<td>'.$row["domain"].'</td>';
			echo '<td>'.$row["executionTs"].'</td>';
			echo '<td>'.$row["market"].'</td>';
			echo '<td>'.$row["type"].'</td>';
			
			if($row['svvmstate']=="Valid") $color = "Green";
			elseif($row['svvmstate']=="Submitted" && $row['reviewstatus']!="Being_Reviewed") $color = "Blue";
			else $color = "Red";
			echo '<td><span style="color: '.$color.'; ">';
			if($row['svvmstate']!="Not_Submitted" && $row['svvmstate']!="Submitted" && $row['svvmstate']!="Rejected") {
				//$svvmurl = get_cfg_var("svvmurl")."/svvm/datastore/v2/getRule?ruleId=".$row['svvmRuleId'];
				$svvmurl = "../svvmoutput.php?svvmRuleId=".$row['svvmRuleId'];
				echo "<a href='$svvmurl'>";
			}
			if($row['svvmstate']!="Submitted") echo $row["svvmstate"]; 
			elseif($row['reviewstatus']=="Being_Reviewed") echo "Being Reviewed";
			else echo "Ready for Review";
			
			if($row['svvmstate']!="Not_Submitted" && $row['svvmstate']!="Submitted" && $row['svvmstate']!="Rejected") echo "</a>";
			//if($row['svvmstate']=="Valid") echo "&nbsp;&nbsp;&nbsp;<a style=\"color:Red;\" href=\"http://search.yahoo.com/search?p=sief:".$row['svvmRuleId'].":execution_successful\" TITLE=\"See Result in Yahoo! Search.\">Y!</a>";
			
			echo '</span></td>';
			echo '<td>'.$row["coverageNum"].'</td>';
			
			if($row['fmeasure'] == 0) $color="black";
			elseif($row['fmeasure'] > $upperLimit) $color="Green";
			elseif($row['fmeasure'] < $lowerLimit) $color="Red";
			else $color="Blue";
			echo '<td><span style="color: '.$color.'">';
			if($row['reviewed']=="N") echo "n/a";
			else echo $row["fmeasure"];
			echo '</span></td>';
			
			//$value = '<a class="normalButtton" href="review.php?siefId='.$row['siefId'].'" >View</a>';
			$value = '<a class="normalButtton" style="padding:1px 23px;" disabled >n/a</a>';
			if($row['validationcount']>0 && $row['svvmstate']=="Valid" ) //REvalidation check
			{$value = '<a  class="highlightedButtton" href="/output.php?siefId='.$row['siefId'].'&type=review" onClick="return confirmRevalidate()" TITLE="Re-Validate the rule after seeing the output" >Revalidate</a>';}
			elseif($row['svvmstate']=="Submitted" || $row['reviewed']=="N")
			{
				if($row['reviewstatus']=="Being_Reviewed")
				$value = '<a  class="highlightedButtton" href="editorial.php?siefId='.$row['siefId'].'&accept=1&crumb='.getenv('.bycrumb').'" onClick="return confirmBeingReview()" TITLE="Review the results">Review</a>';
				else $value = '<a  class="highlightedButtton" href="editorial.php?siefId='.$row['siefId'].'&crumb='.getenv('.bycrumb').'" onClick="return confirmReview()" TITLE="Review the results">Review</a>';
			}
			
		
			
			echo '<td>'.$value.'</td>';
			
			echo '<td>'.$row['capacityFlag'].'</td>';
			
			echo '</tr>';
			
		}
		echo '</tbody></table>';
		echo '<script>
			function confirmRevalidate() {
			  if (confirm("Are you sure to Re-validate the Sief Rule? Please check the output before re-validating")) return true;
			  return false; 
			}
			function confirmRemove() {
			  if (confirm("Are you sure to Remove the Sief Rule from production?")) return true;
			  return false; 
			}
			function confirmBeingReview() {
			  if (confirm("This rule is already being reviewed by someone else. Do you still want to review it and overwrite the previous owner.")) return true;
			  return false; 
			}
		    function confirmReview() {
			  if (confirm("Are you sure to Review the Sief Rule? This rule will be assigned to you and state will change to Being Reviewed.")) return true;
			  return false; 
			}
		</script>';
	}
	
	/**
	 * Displays Validate button in the output page
	 * @param $siefId
	 */
	public static function displayValidateButton($siefId){
		
		$siefRule = CommonUtil::getSiefRules(array('siefId'=>$siefId,'requestedAttributes'=>"validationcount,fmeasure,svvmstate,reviewed"), "getSiefRule");
			
		if(isset($siefRule[$siefId])){
			$row = $siefRule[$siefId];
			if($row['validationcount']>0 && $row['svvmstate']=="Valid" ) {//REvalidation check
				echo '<br/><br/><a  class="highlightedButtton" style="padding: 6px 24px;" href="../inc/validate.php?siefId='.$siefId.'&type=editori&crumb='.getenv('.bycrumb').'" onClick="return confirmRevalidate()" TITLE="Re-Validate the rule after seeing the output" >Revalidate</a>';
				echo '&nbsp;&nbsp;&nbsp;&nbsp;<a class="normalButtton"   style="padding: 6px 33px;"  href="/inc/deletesvvm.php?siefId='.$siefId.'&action=remove&referrer=editor&crumb='.getenv('.bycrumb').'"  onClick="return confirmRemove()" TITLE="Remove/De-activate this rule from Production">Remove</a>';
				echo '<script>
					function confirmRevalidate() {
					  if (confirm("Are you sure to Re-validate the Sief Rule? Please check the output before re-validating")) return true;
					  return false; 
					}
					function confirmRemove() {
			  			if (confirm("Are you sure to Remove the Sief Rule from production?")) return true;
			  			return false; 
					}
				</script>';
			}
		}
		
		
	}
}
?>

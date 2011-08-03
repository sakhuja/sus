<?php
/**
 * Utility class for parsing and loading the config file in memory. 
 * It is in memory representation of config file and persisted across session in the apc cache.
 * @author anupg
 *
 */
class configHelper {
    private static $descArray;
    private static $messageArray;
    
    public static function init() {
	     
    	//$descFile =  get_cfg_var("SIEF_VALIDATOR_PEAR_DIR")."/config/market.ini";
    	$descFile = get_cfg_var("SIEF_VALIDATOR_DOC_ROOT")."/config/";
    	//apc_delete($descFile);
	    self::$descArray = apc_fetch($descFile);
        $desc_codes = array();
        if(self::$descArray === FALSE){
        	//$desc_codes = parse_ini_file($descFile,true);
        	
        	$config_dir = get_cfg_var("SIEF_VALIDATOR_DOC_ROOT")."/config/";   
  			$files = scandir($config_dir);       
 
  			foreach ($files as $file)          
		    {
		    	$parts = explode(".", $file);                   
		    	if (is_array($parts) && count($parts) > 3) {    // does the dissected array have more than one part
		        	$desc_codes[$parts[0]][$parts[1]]=parse_ini_file($config_dir.$file,true);
		        	$desc_codes = array_merge($desc_codes,parse_ini_file($config_dir.$file,true));
		    	}
		    }
    		//print_r($desc_codes);
        	apc_store($descFile, $desc_codes);
        	self::$descArray = $desc_codes;
        }
        
        
        $messageFile = get_cfg_var("SIEF_VALIDATOR_DOC_ROOT")."/config/obmessage.ini";
        //apc_delete($messageFile);
	    self::$messageArray = apc_fetch($messageFile);
        $msg_codes = array();
        if(self::$messageArray === FALSE){
        	$msg_codes = parse_ini_file($messageFile,true);
        	//print_r($msg_codes);
        	apc_store($messageFile, $msg_codes);
        	self::$messageArray = $msg_codes;
        }
    }
    
    public static function getdesc() {
        return self::$descArray;
    }
    
    public static function getMessages() {
        return self::$messageArray;
    }
    
    public static function getMessageString($key){
    	if(isset(self::$messageArray[$key])){
    		return self::$messageArray[$key];
    	}
    	else return "";
    }
    
    /**
     * Function for getting the help string given the key. It tries to exhaustively find the key 
     * @param unknown_type $key
     * @param unknown_type $count
     * @param unknown_type $total
     * @param unknown_type $objectType
     */
    public static function getHelpString($key,$count=0,$total=0,$objectType="none"){
    	
    	$attribType = "optional";
    	$desc=self::getDescriptionArray($key,$objectType,$attribType);
    	if(!empty($desc) && isset($desc["DESCRIPTION"])){ 

                    $divDisplay = '<div>
                   
				<a onclick="Toggle('.$count.','.$total.')" style="cursor: help;  font-weight:bold;" href="#'.$key.'">';
                $divDisplay .= '<em style="text-align: left;"><span>'.$key;
                $divDisplay .= '<span id="CHECK'.$count.'" style="display:none; float: right; color: green;">&#x2713</span>';
                if(!empty($desc['FORMAT_TYPE'])){
					$divDisplay .= '</span><span style="float: right; font-size: 11px; color:#999999;">('.$desc["FORMAT_TYPE"].')</span>';
				
                }
				$divDisplay .= '</em>';
				
				$divDisplay .= '</a><a id="HELP'.$count.'" style="display:none; border-color: white; border-width: 1px 0px 0px 0px;">
				<span style="padding: 5px 5px 3px 4px; display: block; text-align: left;">'.$desc["DESCRIPTION"].'</span>';
                if(!empty($desc["FORMAT"]))
                      $divDisplay .= '<span style="padding: 5px 2px 3px 5px; display: block; text-align: left;"><strong>Format</strong>: '.$desc["FORMAT"].'</span>';
                           
				$divDisplay .= '</a>
				
				</div>';
    		
    		return $divDisplay;
    	}
    	
    	return "<a href=\"#$key\"><em onclick=\"Toggle($count,$total)\" style='font-weight: bold; text-align:left;'>$key".'<span id="CHECK'.$count.'" style="display:none; float: right; color: green;">&#x2713</span></em>'."<div id='HELP".$count."'/></a>";
    	
    }
    
    /**
     * Get the description array given the key and object type
     * @param unknown_type $key
     * @param unknown_type $objectType
     * @param unknown_type $attribType
     * @param unknown_type $delim
     * @param unknown_type $recokey
     */
    public static function getDescriptionArray(&$key,&$objectType,&$attribType="optional",$delim=" ",&$recokey=""){
    	$desc = &self::$descArray;
    	$keys = explode($delim,$key);
        if ( $delim == "_" ){
                $attribs = explode(":",$key);
                if(count($attribs) == 2){
                        $pos = strrpos($key,$delim);
                        if ($pos !== false){
                                $keys[0]=substr($key,0,$pos);
                        }
                }
        }


    	foreach($keys as $k){
    		$k = strtoupper(str_replace(":","_",$k));
    		if(isset($desc[$objectType])){
    			foreach($desc[$objectType] as $role=>$attribs){
    				if(isset($attribs[$k])) {
    					$attribType=$role;
    					$recokey = $k;
    					return $attribs[$k];
    				}
    			}
    		}	
    	}
    	foreach($keys as $k){
    		$k = strtoupper(str_replace(":","_",$k));
    		if(isset($desc[$k])){
    			return $desc[$k];
    		}
    	}
    	return "";
    }
    
}

configHelper::init();
?>

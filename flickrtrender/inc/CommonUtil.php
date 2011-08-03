<?php

require_once('MySQLConnectionFactory.php');
require_once('util.php');
require_once('constants.php');
define('UPPER_LIMIT', 0.9);
define('LOWER_LIMIT', 0.6);
define('PRECISION_LOWER_LIMIT', 0.8);
define('PRECISION_UPPER_LIMIT', 0.9);

/**
 * This class contains all the public utility functions. It is included by almost every file to talk to database
 * @author anupg
 */
class CommonUtil {

    /**
     * Function to compate SearchMonkey and SVVM UI output. It is used for testing purpose only.
     * @param unknown_type $filename
     * @param unknown_type $smid
     * @param unknown_type $isVerbose
     */
    public function compareSMandSIEF($filename, $smid="02i", $isVerbose=false) {

        $siefout = file_get_contents($filename);
        $delimiter = "</feed> ";
        $whitespaces = array("\r\n", "\n", "\r", "\t", "  ");
        $replaceStr = array("", "", "", "", " ");
        if (strpos($siefout, "Your Sief Rule is Currently Running") === 0) {
            if ($isVerbose)
                echo "<p> your Sief Ruls is currently running. Check Later </p>";
            return;
        }

        $smVsSiefLogFile = "$filename.SmVsSief.log";
        if (file_exists($smVsSiefLogFile)) {
            if ($isVerbose)
                echo str_replace("\n", "<br/>", htmlspecialchars(file_get_contents($smVsSiefLogFile)));
        }
        else {
            $fh = fopen($smVsSiefLogFile, "w+")
                    or die("couldn't open file <i>" . $smVsSiefLogFile . "</i>");
            fwrite($fh, "smid=$smid\n");

            $pos = strpos($siefout, $delimiter);
            if ($pos === FALSE)
                $delimiter = "\n";

            $splitFeed = explode($delimiter, $siefout);
            $urlPassed = 0;
            $countUrl = count($splitFeed);
            $countUrl -= 1;
            fwrite($fh, "Numer of url = $countUrl\n\n====================\n");

            foreach ($splitFeed as $lineNum => $feed) {
                if ($pos !== FALSE)
                    $feed .= "</feed>";
                //print htmlspecialchars($feed);
                //print $feed;
                $urlFeed = explode(" ", $feed, 2);
                $len = count($urlFeed);
                if ($len == 2) {
                    $url = trim($urlFeed[0]);
                    $urlencode = urlencode($url);
                    $monkeyUrl = "http://monkey.corp.yahoo.com:6665/common/runDataPlugin?smid=$smid&url=$urlencode";
                    fwrite($fh, "SM_URL $lineNum = $monkeyUrl\n");
                    fwrite($fh, "SIEF_URL $lineNum = $url\n");
                    $xslt = $urlFeed[1];
                    //echo "<a href=\"$monkeyUrl\">$monkeyUrl</a><br>";
                    //echo "<a href=\"$url\">$url</a><br>";
                    $xslt = trim(str_replace($whitespaces, $replaceStr, $xslt));
                    //print $xslt;
                    //echo "<br/>";
                    $smResult = CommonUtil::curlURL($monkeyUrl, FALSE);
                    //echo "$smResult<br/>";
                    $smResult = trim(str_replace($whitespaces, $replaceStr, $smResult));
                    $smFeed = CommonUtil::convertFeedDocument($smResult);
                    fwrite($fh, "<!-- SM FEED -->\n");
                    fwrite($fh, "$smFeed\n");
                    file_put_contents("$filename.smFeed_$lineNum.out", "$smFeed");
                    //echo "<br/>";
                    $siefFeed = CommonUtil::convertFeedDocument($xslt);
                    fwrite($fh, "<!-- SIEF FEED -->\n");
                    fwrite($fh, "$siefFeed\n");
                    file_put_contents("$filename.siefFeed_$lineNum.out", "$siefFeed");
                    if ($smFeed == $siefFeed) {
                        //echo "<b><p>URL $lineNum: Yahoo! SM and SIEF test result are same</p></b><br/>";
                        fwrite($fh, "URL $lineNum: PASSED - SM and SIEF test result are same\n");
                        $urlPassed += 1;
                    } else {
                        fwrite($fh, "URL $lineNum: FAILED - SM and SIEF are different\n");
                    }
                    fwrite($fh, "\n===============\n");
                }
            }
            $passPercent = 0;
            if ($countUrl > 0)
                $passPercent = $urlPassed / $countUrl;
            fwrite($fh, "Number URLS Passed TEST = $urlPassed  Total = $countUrl  percent = $passPercent smid = $smid\n");
            fclose($fh);
            if ($isVerbose)
                echo str_replace("\n", "<br/>", htmlspecialchars(file_get_contents($smVsSiefLogFile)));
        }
    }

    /**
     * fucntion to convert any Feed xml to proper format that SVVM UI can use.
     * @param $feedXslt
     */
    public function convertFeedDocument(&$feedXslt) {

        if (empty($feedXslt)) {
            $smitemdoc = new DOMDocument();
            return $smitemdoc->saveXML();
        }
        $smxmlDoc = new DOMDocument();
        $smxmlDoc->loadXML($feedXslt);

        $xpath = new DomXPath($smxmlDoc);
        $smxmladjunct = $xpath->query("//adjunct");
        $len = $smxmladjunct->length;
        $smitemdoc = new DOMDocument();
        if ($len > 0) {
            foreach ($smxmladjunct as $adjunct) {
                //$items = $adjunct->getElementsByTagName("*");
                $items = $adjunct->childNodes;

                foreach ($items as $item) {
                    $domNodeitem = $smitemdoc->importNode($item, true);
                    $smitemdoc->appendChild($domNodeitem);

                    //print $item;
                }
            }
        }
        $xmldata = $smitemdoc->saveXML();
        //print $xmldata;
        return $xmldata;
    }

    /**
     * Function to fetch an external url using CURL
     * @param unknown_type $url
     * @param unknown_type $timeout
     * @param unknown_type $proxy
     */
    public function curlURL($url, $timeout = 5, $proxy = false) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, 'http://' . Config::$hlfProxy . ":" . Config::$hlfProxyPort);
            curl_setopt($ch, CURLOPT_USERAGENT, 'OnlyProcessed HLFS_Param AnnLevel=0;XMLClean=true;');
        }

        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /*
     * Not implemented
     */

    public static function rerunAllrule() {

    }

    /**
     * Rerun the rule given the siefId and options
     * @param unknown_type $siefId
     * @param unknown_type $revaliation
     * @param unknown_type $deleteChunk
     */
    public static function rerunrule($siefId, $revaliation=false, $deleteChunk=false) {

        $params = array();
        $params['siefId'] = $siefId;
        $params['requestedAttributes'] = "xslName";
        $siefRule = self::getSiefRules($params, "getSiefRule");

        if (isset($siefRule[$siefId]["xslName"])) {
            $xsltFile = $siefRule[$siefId]["xslName"];
            return self::reRun($siefId, $xsltFile, $revaliation, $deleteChunk);
        }
        return false;
    }

    /**
     * Rerun the rule with given input file and siefId.
     * @param unknown_type $siefId
     * @param unknown_type $xsltFile
     * @param unknown_type $revaliation - boolean for rerunning for revalidation. If this is true than
     *                                    revalidation count is incremented and email send to appropriate groups
     *                                    http://twiki.corp.yahoo.com/view/Yst/SearchMonkeyRuleMonitoring
     * @param unknown_type $deleteChunk - boolean for rerunning the rule with deleting chunk
     * @param unknown_type $grpemailAddress
     * @param unknown_type $globalEmailAdd
     */
    public static function reRun($siefId, $xsltFile, $revaliation=false, $deleteChunk=false, $grpemailAddress="", $globalEmailAdd="") {
        $logDir = get_cfg_var("LOGDIR");
        $inputFilename = "$logDir/$siefId/$xsltFile.input";
        $logFilename = "$logDir/$siefId/$xsltFile.log";
        $outFilename = "$logDir/$siefId/$xsltFile.out";

        if (!file_exists($inputFilename))
            self::createDirectory($siefId);

        global $databases;

        if ($deleteChunk) {
            foreach ($databases as $database) {
                $chunkfile = "$logDir/$siefId/input.chunks.$database";
                $fetchurlfile = "$logDir/$siefId/s3output.tmp.$database";
                if (file_exists($chunkfile))
                    unlink($chunkfile);
                if (file_exists($fetchurlfile))
                    unlink($fetchurlfile);
            }
        }

        self::writeEmail($siefId);
        $param = "";
        if ($revaliation)
            $param = "1";
        elseif (self::isMaxRuleLimit($siefId)) {
            $output = CommonUtil::executeSiefUpdate(array("siefId" => $siefId, "state" => "Queued"), $siefId);
            if (array_key_exists("error", $output)) {
                die(print json_encode($output));
            }
            $outFilename = "$logDir/$siefId/$xsltFile.out";
            $cmd = 'echo "Your Sief Rule is in Queue. Please wait to see results." > ' . $outFilename . ' &';
            //echo "<p>$cmd</p>";
            $tch = shell_exec($cmd);
            return false;
        }



        $output = CommonUtil::executeSiefUpdate(array("state" => 'Testing'), $siefId);
        if (array_key_exists("error", $output))
            die(print json_encode($output));

        $executableDir = get_cfg_var("SIEF_VALIDATOR_PEAR_DIR") . "/scripts";
        $command = 'cd ' . $executableDir . ' ; ./testSief.sh ' . $param . ' ' . $grpemailAddress . ' ' . $globalEmailAdd . ' < ' . $inputFilename . ' > ' . $logFilename . ' 2>&1 &';
        //echo "<p>$command</p>";
        $dir = shell_exec($command);

        //Write the output file with a currently running status
        $cmd = 'echo "Your Sief Rule is Currently Running" > ' . $outFilename . ' &';
        //echo "<p>$cmd</p>";
        $tch = shell_exec($cmd);
        return true;
    }

    /**
     * Run the rule for the first time. Can be facored out with rerunRule function
     * @param unknown_type $siefId
     * @param unknown_type $xsltFile
     */
    public static function runRule($siefId, $xsltFile) {

        $logDir = get_cfg_var("LOGDIR");
        $inputFilename = "$logDir/$siefId/$xsltFile.input";
        $logFilename = "$logDir/$siefId/$xsltFile.log";
        $outFilename = "$logDir/$siefId/$xsltFile.out";

        if (!file_exists($inputFilename))
            self::createDirectory($siefId);

        $output = CommonUtil::executeSiefUpdate(array("state" => 'Testing'), $siefId);
        if (array_key_exists("error", $output))
            die(print json_encode($output));

        $executableDir = get_cfg_var("SIEF_VALIDATOR_PEAR_DIR") . "/scripts";
        $command = 'cd ' . $executableDir . ' ; ./testSief.sh < ' . $inputFilename . ' > ' . $logFilename . ' 2>&1 &';
        //echo "<p>$command</p>";
        $dir = shell_exec($command);

        //Write the output file with a currently running status
        $cmd = 'echo "Your Sief Rule is Currently Running" > ' . $outFilename . ' &';
        //echo "<p>$cmd</p>";
        $tch = shell_exec($cmd);
    }

    /**
     * Function for checking the max rule limit. Curretly is set to 10
     * @param $siefId
     */
    public static function isMaxRuleLimit($siefId) {
        $procount = 0;
        $processes = self::psgrep("testSief.sh");
        if (is_array($processes))
            $procount = count($processes);
        if ($procount >= get_cfg_var("MAXPROCESS")) {
            return true;
        }
        return false;
    }

    /**
     * Write email in the file to be sent after the rule execution completes
     * @param unknown_type $siefId
     * @param unknown_type $emailaddress
     */
    public static function writeEmail($siefId, $emailaddress="") {
        $emailFile = get_cfg_var("LOGDIR") . "/$siefId/mail.out";
        //echo $_SERVER['HTTP_HOST'];
        $http_host = get_cfg_var("SIEF_VALIDATOR_HTTP_HOST");
        if (!file_exists($emailFile)) {
            if (empty($emailaddress)) {
                $params = array();
                $params['siefId'] = $siefId;
                $params['requestedAttributes'] = "email";
                $siefRule = self::getSiefRules($params, "getSiefRule");
                if (isset($siefRule[$siefId]["email"])) {
                    $emailaddress = $siefRule[$siefId]["email"];
                }
            }
            $email = "Hi,\nYour SIEF RULE ID - $siefId has produced output.\n\n";
            $email .= "SEE OUTPUT         - $http_host/output.php?siefId=$siefId\n";
            $email .= "SEE YOUR RULE      - $http_host/editor/editor.php?siefId=$siefId\n";
            $email .= "SEE YOUR HOMEPAGE  - $http_host/editor/index.php?email=$emailaddress\n\n";

            $email .= "Please see the output file attached.\n";
            $email .= "Thanks,\nSVVM TEAM\n";
            file_put_contents($emailFile, $email);
        }
    }

    /** From http://us2.php.net/posix_kill * */
    public static function psgrep($match) {
        if ($match == '')
            return 'no pattern specified';
        $match = escapeshellarg($match);
        exec("ps x|grep $match|grep -v grep|awk '{print $1}'", $output, $ret);
        if ($ret)
            return 'you need ps, grep, and awk installed for this to work';

        $ret = array();
        while (list(, $t) = each($output)) {
            if (preg_match('/^([0-9]+)/', $t, $r)) {
                $ret[] = $r[1];
                //system('kill '. $r[1], $k);
                //if(!$k) $killed = 1;
            }
        }
        return $ret;
    }

    /**
     * Function converts a wildcard pattern into the IDPD queriable fetch Pattern
     * @param unknown_type $wildcard
     */
    public static function getFetchPattern(&$wildcard) {

        if (empty($wildcard))
            die('{"error" : "Empty wildcard"}');
        //echo $wildcard;
        $wildcard = trim(str_replace(array(" ", "*."), array("", "*"), $wildcard));

        if (strpos($wildcard, "//") === FALSE)
            $wildcard = "http://$wildcard";

        $search = "";
        $url = @parse_url($wildcard);
        if (isset($url['host'])) {
            $pos = strpos($url['host'], "*");
            if ($pos === 0) {
                $url['host'] = substr($url['host'], 1);
            } else if ($pos !== FALSE && $pos !== 0 && $pos != (strlen($url['host']) - 1)) {
                die('{"error" : "Unable to parse wildcard Pattern. It contains * in the domain"}');
            }
            $url['host'] = trim(str_replace(array("*"), array(""), $url['host']));
            if (!empty($url['host']))
                $search .= "site:{$url['host']}";
            else
                die('{"error" : "Unable to parse wildcard Pattern. Found empty domain"}');
        }

        if (isset($url['path'])) {
            if ($url['path'] == '/')
                $url['path'] = '';
            elseif ($url['path'] == '/*')
                $url['path'] = '';
            else {
                $url['path'] = trim(str_replace(array("/", "*."), array("", "*"), $url['path']));
                $inurls = explode("*", $url['path']);
                foreach ($inurls as $inurl) {
                    $search .= ( !empty($inurl) ? " inurl:{$inurl}" : "");
                }
                $url['path'] = str_replace("*", "", $url['path']);
            }
        }

        if (!empty($url['path'])) {
            $pos = strrpos($wildcard, "/*");
            if ($pos === strlen($wildcard) - 2) {
                $wildcard = substr($wildcard, 0, -2);
                $wildcard .= "*";
            }
        }
        //echo " -- ".$wildcard ." -- ". $search ."<br/>";
        return $search;
    }

    /**
     * Function returns the domain of a given wildcard if exist.
     * @param unknown_type $wildcard
     */
    public static function getDomain($wildcard) {
        $wildcard = trim(str_replace(array(" ", "*."), array("", "*"), $wildcard));
        if (strpos($wildcard, "//") === FALSE)
            $wildcard = "http://$wildcard";
        $url = @parse_url($wildcard);
        if (isset($url['host'])) {
            $pos = strpos($url['host'], "*");
            if ($pos === 0) {
                $url['host'] = substr($url['host'], 1);
            } else if ($pos !== FALSE && $pos !== 0 && $pos != (strlen($url['host']) - 1)) {
                die('{"error" : "Unable to parse wildcard Pattern. It contains * in the domain"}');
            }
            $url['host'] = trim(str_replace(array("*"), array(""), $url['host']));
            if (!empty($url['host']))
                return $url['host'];
        }
        return "";
    }

    /**
     * Fucntion converts the wildcard into annotation wildcard to be send and used in annotation file.
     * @param unknown_type $wildcard
     */
    public static function getAnnotationWildcard($wildcard) {

        $wildcard = trim(str_replace(" ", "", $wildcard));
        $pattern = array(".", "*\.", "*");
        $replacePattern = array("\.", "(.+\.)?", "(.*)");

        $annotationWildcard = str_replace($pattern, $replacePattern, $wildcard);

        if (strpos($annotationWildcard, "//") === FALSE)
            $annotationWildcard = "http://$annotationWildcard";
        if (strpos($annotationWildcard, "http://www") === FALSE)
            $annotationWildcard = str_replace("http://", "http://(www\.)?", $annotationWildcard);
        if (strpos($annotationWildcard, "(.*)", strlen($annotationWildcard) - 4) === FALSE)
            $annotationWildcard = $annotationWildcard . "(.*)";
        return $annotationWildcard;
    }

    /**
     * Fucntion converts the fetchPattern (IDPD query) into annotation wildcard to be send and used in annotation file.
     * @param unknown_type $fetchPat
     * @param unknown_type $isSanePat
     * @param unknown_type $annotateWdcd
     * @param unknown_type $annoFetchPat
     */
    public static function sanitizeFetchPattern($fetchPat, &$isSanePat=true, &$annotateWdcd="", &$annoFetchPat="") {
        $saneFetchPat = "";
        $actualfetchPat = "";
        $keywords = array('site' => "", 'path' => "", 'inurl' => "");
        $patterns = explode(" ", $fetchPat);
        $isSanePat = true;
        //echo count($patterns);
        foreach ($patterns as $pattern) {
            $pattern = trim($pattern);
            if (empty($pattern))
                continue;
            $actualfetchPat .= $pattern . " ";
            $attribs = explode(":", $pattern, 2);
            if (count($attribs) < 2) {
                $isSanePat = false;
                continue;
            }
            if (isset($keywords[$attribs[0]]))
                $keywords[$attribs[0]] .= $attribs[1] . " ";
            else {
                $isSanePat = false;
                continue;
            }
            $saneFetchPat .= $pattern . " ";
        }
        $saneFetchPat = trim($saneFetchPat);
        $actualfetchPat = trim($actualfetchPat);
        $annoFetchPat = "";
        $annotateWdcd = "";
        $wildcardPat = "";
        if (!empty($keywords['site'])) {
            $site = trim($keywords['site']);
            $site = str_replace(".", "\.", $site);
            if (strpos($site, " ") === FALSE) {
                $annotateWdcd .= "http://(www\.)?(.+\.)?$site";
                $annoFetchPat .= "site:$site ";
                $wildcardPat .= "http://*$site";
                if (strpos($site, "/", strlen($site) - 1) === FALSE) {
                    $annotateWdcd .= "/";
                    $wildcardPat .= "/";
                }
            } else {
                $isSanePat = false;
            }
        } else {
            $isSanePat = false;
        }

        if (!empty($keywords['path'])) {
            $path = trim($keywords['path']);
            $path = str_replace(".", "\.", $path);
            if (strpos($path, " ") === FALSE) {
                if (strpos($path, "/") === 0) {
                    $annotateWdcd .= substr($path, 1);
                    $wildcardPat .= substr($path, 1);
                } else {
                    $annotateWdcd .= "$path";
                }
                $annoFetchPat .= "path:$path ";
            } else {
                $isSanePat = false;
            }
        }
        if (!empty($keywords['inurl'])) {
            $totalinurl = trim($keywords['inurl']);
            $inurls = explode(" ", $totalinurl);
            foreach ($inurls as $inurl) {
                $inurl = str_replace(".", "\.", $inurl);
                $annotateWdcd .= "(.*)$inurl";
                $annoFetchPat .= "inurl:$inurl ";
            }
        }
        $annotateWdcd .= "(.*)";
        $annoFetchPat = trim($annoFetchPat);

        //echo "$actualfetchPat -- $isSanePat -- $annotateWdcd -- $saneFetchPat -- $annoFetchPat<br/>";
    }
  /*** Parse the xml string to give the Rule break reason ***/	
public static function getRuleBreakReason($siefId,$msgtype='')
        {
        $params = array();
        $xmlStr='';
        $params['siefId'] = $siefId; 
        $params['msgtype'] = "REASON";
        $params['limit'] = 1;
        $params['requestedAttributes'] = "msgtype,msgkey,msgvalue";
        $reasonMsg='';
        $siefLogMsg =CommonUtil::getSiefRules($params, "getRuleLog");
        if(!empty($siefLogMsg[$siefId]))
        {
         if(array_key_exists('msgkey',$siefLogMsg[$siefId][0]))
                {       
                if($msgtype=='reason')
                  {     
                     $xmlStr=trim($siefLogMsg[$siefId][0]['msgvalue']);
                     $xml_object = @simplexml_load_string($xmlStr);
		    	 if(!empty($xml_object->ErrorMessage))	
        	          {   $reasonMsg=$xml_object->ErrorMessage; }
                  }else{
                      $xmlStr=trim($siefLogMsg[$siefId][0]['msgvalue']);
                      $xml_object = @simplexml_load_string($xmlStr);
			   if(!empty($xml_object->ErrorCode))
		              {  $reasonMsg=$xml_object->ErrorCode; }
                        }
                }
        }
        return $reasonMsg;      
        } 

    /**
     * Fucntion converts the wildcard into annotation wildcard to be send and used in annotation file.
     * @param $fetchPattern
     * @param $annotationWildcard
     * @param $msg
     */
    public static function getAnnotationWdCd($fetchPattern, &$annotationWildcard, &$msg="") {
        $isSanePat = true;
        $annotateWdcd = "";
        $annoFetchPat = "";
        self::sanitizeFetchPattern($fetchPattern, $isSanePat, $annotateWdcd, $annoFetchPat);
        $annotationWildcard = $annotateWdcd;
        if (!$isSanePat) {
            $msg = 'We are not able to parse your FetchPattern. This is OK for testing purpose, Please fix that before submission. We only supprot site: and inurl: keywords. You can try this "' . $annoFetchPat . '" in your pattern.';
            $type = "error";
        }
    }

    /**
     * Function for creating a new rule (siefId) in the database with appropriate params
     * @param unknown_type $params
     * @param unknown_type $siefId
     */
    public static function executeSiefInsert($params, &$siefId) {

        $siefRules = CommonUtil::getSiefRules($params, "updateSiefRule");
        if (!is_array($siefRules))
            $output["error"] = htmlentities($siefRules);
        else if (isset($siefRules['siefId'])) {
            $siefId = $siefRules['siefId'];
            $output["siefId"] = $siefId;
            self::logInsertEvent($siefId);
        } else if (!empty($siefRules['error']))
            $output["error"] = $siefRules['error'];
        else
            $output["error"] = htmlentities($siefRules);

        return $output;
    }

    /**
     * Function for updating the database with appropriate parameters
     * @param unknown_type $params
     * @param unknown_type $siefId
     */
    public static function executeSiefUpdate($params, $siefId) {

        $params['siefId'] = $siefId;

        $siefRules = CommonUtil::getSiefRules($params, "updateSiefRule");

        if (is_array($siefRules)) {
            self::logEvent($params);
            return $siefRules;
        }
        else
            $output["error"] = htmlentities($siefRules);
        //echo json_encode($output);
        return $output;
    }

    /**
     * Updating the log database when rule changes its state
     * @param unknown_type $siefId
     */
    public static function logInsertEvent($siefId) {
        $logparam = array();
        $logparam['siefId'] = $siefId;
        self::populateLogData('STATECHAGNE', 'state', 'Not_Submitted', $logparam);
        CommonUtil::getSiefRules($logparam, "updateLog");
    }

    /**
     * Utility function for updating the log whenever there is a change in the rule.
     * @param unknown_type $params
     * @param unknown_type $siefId
     */
    public static function logEvent($params, $siefId="") {
        $logparam = array();
        if (empty($siefId)

            )$logparam['siefId'] = $params['siefId'];
        else
            $logparam['siefId'] = $siefId;
        if (isset($params['svvmstate'])) {
            self::populateLogData('STATECHANGE', 'state', $params['svvmstate'], $logparam);
            $ridarray = CommonUtil::getSiefRules($logparam, "updateLog");
            //print_r($ridarray);
        }

        if (isset($params['action']) && $params['action'] == SIGNUP_OR_QUIT) {
            if (isset($params['email'])) {
                $type = "SIGNUP";
                if ($params['email'] == UNASSIGNED_EMAIL)
                    $type = "QUIT";
                self::populateLogData($type, 'email', $params['email'], $logparam);
                CommonUtil::getSiefRules($logparam, "updateLog");
            }
        }
        else if (isset($params['email'])) {
            self::populateLogData(CHANGE_EMAIL, 'email', $params['email'], $logparam);
            CommonUtil::getSiefRules($logparam, "updateLog");
        }

        if (isset($params['fmeasure'])) {
            self::populateLogData('REVIEW', 'fmeasure', $params['fmeasure'], $logparam);
            CommonUtil::getSiefRules($logparam, "updateLog");
        }

        if (isset($params['reviewinput'])) {
            self::populateLogData('REVIEW', 'score', $params['reviewinput'], $logparam);
            CommonUtil::getSiefRules($logparam, "updateLog");
        }

        if (isset($params['accuracy'])) {
            self::populateLogData('REVIEW', 'precision', $params['accuracy'], $logparam);
            CommonUtil::getSiefRules($logparam, "updateLog");
        }

        if (isset($params['reviewstatus'])) {
            self::populateLogData('STATECHANGE', 'reviewstatus', $params['reviewstatus'], $logparam);
            CommonUtil::getSiefRules($logparam, "updateLog");
        }

        if (isset($params['svvmRuleId'])) {
            self::populateLogData('SVVMCHANGE', 'svvmRuleId', $params['svvmRuleId'], $logparam);
            CommonUtil::getSiefRules($logparam, "updateLog");
        }

        if (isset($params['validationcount'])) {
            self::populateLogData('REVALIDATE', 'validationcount', $params['validationcount'], $logparam);
            CommonUtil::getSiefRules($logparam, "updateLog");
        }
    }

    /**
     * Utility function for populating the log params to its default value
     * @param $msgtype
     * @param $msgkey
     * @param $msgvalue
     * @param $logparam
     */
    public static function populateLogData($msgtype, $msgkey, $msgvalue, &$logparam=array()) {
        $user = getenv('_byuser');
        if (empty($user))
            $user = "yst-svvm";
        $logparam['email'] = $user . "@yahoo-inc.com";
        $logparam['msgtype'] = $msgtype;
        $logparam['msgkey'] = $msgkey;
        $logparam['msgvalue'] = $msgvalue;
    }

    /**
     * Utility function to get the rules from database based on different params
     * @param unknown_type $params
     * @param unknown_type $webservice
     */
    public static function getSiefRules($params, $webservice="getSiefRules") {

        //echo getenv('HTTP_HOST');
        //echo get_cfg_var('Http_Host');
        //$_SERVER['HTTP_HOST'];
        $url = get_cfg_var('Http_Host') . "/webservice/$webservice.php?";
        $strParameters = http_build_query($params);
        //echo $url.$strParameters;

        $ch = curl_init($url);

        $cookie = 'YBY=' . (yax_cj_full_cookie('YBY'));

        // Set options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $strParameters);


        $data = curl_exec($ch);
        //print_r(curl_getinfo($ch));
        curl_close($ch);

        $ridArray = json_decode(trim($data), true);

        return $ridArray;
    }

    /**
     * Function for upgrading the template. It will apply the current template to the stub
     * @param $siefId
     */
    public static function upgradeTemplate($siefId) {
        $params['siefId'] = $siefId;
        $params['requestedAttributes'] = "xslName,template,type,siefId";
        $siefRule = self::getSiefRules($params, "getSiefRule");
        if (isset($siefRule[$siefId])) {
            $row = $siefRule[$siefId];
            $logDir = get_cfg_var("LOGDIR");
            $xslname = $row['xslName'];
            $template = $row['template'];
            $xslFilename = "$logDir/$siefId/$xslname";
            $xslt = file_get_contents($xslFilename);
            $stub = "";
            //echo "ME HEREERERE";
            $startStr = "<!-- DO NOT MODIFY ANYTHING ABOVE THIS LINE -->";
            $endStr = "<!-- DO NOT MODIFY ANYTHING BELOW THIS LINE -->";
            //echo $xslt;
            $startPos = strpos($xslt, $startStr);
            $endPos = strpos($xslt, $endStr);
            if ($startPos !== FALSE && $endPos !== FALSE) {
                file_put_contents("$xslFilename.old.xsl", $xslt);
                $offset = strlen("<!-- DO NOT MODIFY ANYTHING ABOVE THIS LINE -->");
                $stub = trim(substr($xslt, $startPos + $offset, $endPos - ($startPos + $offset)));
                $finalXslt = self::applyTemplate("", $stub, $template);
                if (!empty($finalXslt))
                    file_put_contents($xslFilename, $finalXslt);
                //echo $finalXslt;
            }
        }
    }

    /**
     * Get the type of object given the siefId
     * @param unknown_type $siefId
     */
    public static function getObjectType($siefId) {
        $params['siefId'] = $siefId;
        $params['requestedAttributes'] = "type,siefId";
        $siefRule = CommonUtil::getSiefRules($params, "getSiefRule");
        $type = "unknown";
        if (isset($siefRule[$siefId]['type'])) {
            $type = $siefRule[$siefId]['type'];
        }
        return $type;
    }

    /**
     * Builds a review table for a given siefId
     * @param unknown_type $siefId
     */
    public static function reviews($siefId) {
        $logDir = get_cfg_var("LOGDIR");
        $review = "$logDir/$siefId/review.ini";
        if (file_exists($review)) {
            $reviews = json_decode(file_get_contents($review), true);
            $reviewArray = $reviews["full"];
            $review20Array = $reviews["individual"];
            echo '<div>
			      <table border="0" class="border tableheader" >
				<thead><tr>
				   <th style="padding: 5px; width: 50%; border: 1px solid #999b9a; text-align:center; cursor: pointer;"  onclick="showReview()"><div id="reviewRA" class="rightarrow">&raquo;</div><div id="reviewDA" class="downarrow" style="display: none;">&laquo;</div>Full Review Sore</th>
				   <th style="padding: 5px; width: 50%; border: 1px solid #999b9a; text-align: center; cursor: pointer;"  onclick="showReview()">Individual (20 url) Review Score</th>
				</tr>
				</thead>
				<tbody  id="reviewTable">';
            echo "<tr><td>";
            self::displayReviewTable($siefId, $reviewArray);
            echo "</td><td>";
            self::displayReviewTable($siefId, $review20Array);
            echo "</td></tr></tbody></table></div>";
        }
        else
            echo "<div id=\"reviewTable\"><br/><span style=\"color: red;\">Reviews is not yet complete. <br/>Please wait till the review get submitted.</span></div>";
    }

    /**
     * Build a review table for a particular review Array
     * @param $siefId
     * @param $reviewArray
     */
    public static function displayReviewTable($siefId, &$reviewArray) {

        echo '<div id="statistics">
		      <table id="Statistics_Table"  class="border tableheader"  border="1" cellpadding="5" align="center" style="background-color: #EEEEEE; text-align: center;border: 1px solid #999b9a;" >
				<thead>
				<tr>
					<th rowspan="1" colspan="1" class="yui-dt-first">Key</th>
					<th rowspan="1" colspan="1" class="yui-dt-last">Value</th>
				</tr>
			    </thead>
			    <tbody style="text-align:left;">';

        echo '<tr class="yui-dt-first yui-dt-even"><td>Precision</td><td>' . $reviewArray['precision'] . '</td></tr>';
        echo '<tr class="yui-dt-odd"><td>Recall</td><td>' . $reviewArray['recall'] . '</td></tr>';
        echo '<tr class="yui-dt-even"><td>F-Measure</td><td>' . $reviewArray['fmeasure'] . '</td></tr>';
        echo '<tr class="yui-dt-odd"><td>Accurate</td><td>' . $reviewArray['accurate'] . '</td></tr>';
        echo '<tr class="yui-dt-even"><td>In-Accurate</td><td>' . $reviewArray['inaccurate'] . '</td></tr>';
        echo '<tr class="yui-dt-odd"><td>Empty</td><td>' . $reviewArray['empty'] . '</td></tr>';
        echo '<tr class="yui-dt-last yui-dt-even"><td><b>Total</b></td><td><b>' . $reviewArray['totalEditorial'] . '</b></td></tr>';
        echo '</tbody></table></div>';
    }

    /**
     * builds comment table
     * @param unknown_type $siefId
     * @param unknown_type $type
     */
    public static function commentTable($siefId, $type="review") {

        $params = array();
        $params['siefId'] = $siefId;
        $params['requestedAttributes'] = $type . "log";
        $siefRule = self::getSiefRules($params, "getSiefRule");

        if (isset($siefRule[$siefId])) {
            $commentArray = json_decode($siefRule[$siefId][$type . 'log'], true);
        }
        if (!is_array($commentArray) || empty($commentArray))
            return;

        echo '<div class="yui-dt-hd">
	      <table id="Comment_Table' . $type . '"  class="border tableheader"    width="100%" border="1" cellpadding="5" style="background-color: #EEEEEE; border: 1px solid navy;" >
			<thead>
			<tr>
				<th style="padding: 5px;border: 1px solid #999b9a; text-align:center; cursor: pointer;"  onclick="show' . $type . 'commentlog()"><div id="' . $type . 'commentlogRA" class="rightarrow">&raquo;</div><div id="' . $type . 'commentlogDA" class="downarrow" style="display: none;">&laquo;</div>' . $type . ' Comment Logs</th>
			</tr></thead><tbody id="comment' . $type . '" >';
        echo '<tr><td>';
        $commentArray = array_reverse($commentArray, true);
        foreach ($commentArray as $time => $value) {

            echo '<div style="padding: 0px 0px 3px;"><table style="background-color: #E1E4E2;" width="100%" cellspacing="1" cellpadding="6"><tbody>';
            echo '<tr style="background-color: #5C7099; color: #FFFFFF"><td>';
            echo '<div style="float: right"> Comment By : ' . $value['email'] . '</div>';
            echo '<div>' . date("M d Y H:i:s", $time) . '<div>';
            echo '</td></tr>';
            echo '<tr><td>' . $value['log'] . '</td></tr>';
            echo '</tbody></table></div>';
        }
        echo '</td></tr>';
        echo '</tbody></table></div>';
    }

    /**
     * Create local cache directory if not already exist
     * @param unknown_type $siefId
     */
    public static function createDirectory($siefId) {

        $params = array();
        $params['siefId'] = $siefId;
        $params['requestedAttributes'] = 'xslName';
        $siefRule = CommonUtil::getSiefRules($params, "getSiefRule");
        if (isset($siefRule[$siefId])) {
            $xsltFile = $siefRule[$siefId]["xslName"];
            self::createInputFile($siefId, $xsltFile);
            self::createOutputFile($siefId, $xsltFile);
            self::createLogFile($siefId, $xsltFile);
        }
    }

    /**
     * Create a local cache of the log file from the database, if not already exists
     * @param $siefId
     * @param $xsltFile
     */
    public static function createLogFile($siefId, $xsltFile) {
        $logDir = get_cfg_var("LOGDIR");
        $logFilename = "$logDir/$siefId/$xsltFile.log";
        if (!file_exists($logFilename)) {
            $params = array();
            $params['siefId'] = $siefId;
            $params['requestedAttributes'] = 'xslName,log';
            $siefRule = CommonUtil::getSiefRules($params, "getSiefRule");
            if (isset($siefRule[$siefId])) {
                $row = $siefRule[$siefId];
                file_put_contents($logFilename, $row['log']);
            }
        }
    }

    /**
     * Create a local cache of the output file from the database, if not already exists
     * @param unknown_type $siefId
     * @param unknown_type $xsltFile
     */
    public static function createOutputFile($siefId, $xsltFile) {
        $logDir = get_cfg_var("LOGDIR");
        $outputFilename = "$logDir/$siefId/$xsltFile.out";
        if (!file_exists($outputFilename)) {
            $params = array();
            $params['siefId'] = $siefId;
            $params['requestedAttributes'] = 'xslName,outputxml';
            $siefRule = CommonUtil::getSiefRules($params, "getSiefRule");
            if (isset($siefRule[$siefId])) {
                $row = $siefRule[$siefId];
                file_put_contents($outputFilename, $row['outputxml']);
            }
        }
    }

    /**
     * Create a local cache of the input file from the database, if not already exists
     * @param unknown_type $siefId
     * @param unknown_type $xsltFile
     */
    public static function createInputFile($siefId, $xsltFile) {
        $logDir = get_cfg_var("LOGDIR");
        $xsltFileName = "$logDir/$siefId/$xsltFile";
        if (!file_exists($xsltFileName)) {
            self::createLogDirectory($siefId);
            $params = array();
            $params['siefId'] = $siefId;
            $params['requestedAttributes'] = 'xslt,siefId,type,xslName,email,domain,inputParam,reviewinput';
            $siefRule = CommonUtil::getSiefRules($params, "getSiefRule");
            if (isset($siefRule[$siefId])) {
                $row = $siefRule[$siefId];
                $xsltFile = $row["xslName"];
                $inputFilename = "$logDir/$siefId/$xsltFile.input";
                $xsltFileName = "$logDir/$siefId/$xsltFile";
                $reviewFileName = "$logDir/$siefId/review.ini";
                if (empty($row['inputParam']))
                    self::createDefaultInput($xsltFileName, $row);
                else
                    file_put_contents($inputFilename, $row['inputParam']);
                if (empty($row['xslt']))
                    self::createDefaultXslt($xsltFileName, $row);
                else
                    file_put_contents($xsltFileName, $row['xslt']);
                file_put_contents($reviewFileName, $row['reviewinput']);
            }
        }
    }

    /**
     * Create a default XSLT based on the template type. It is used when dumping a sign-up sheet with no XSLT.
     * @param unknown_type $xsltFileName
     * @param unknown_type $row
     */
    public static function createDefaultXslt($xsltFileName, &$row) {
        $xslt = "";
        $templatedir = get_cfg_var("LOGDIR") . "/../templates/";
        if ($row['type'] == "unknown") {
            $xslt = file_get_contents($templatedir . "default.xsl");
        } else {
            $stub = file_get_contents($templatedir . "{$row['type']}.stub.txt");
            $xslt = self::applyTemplate("", $stub, $row['type']);
        }
        $output = CommonUtil::executeSiefUpdate(array("xslt" => $xslt), $row['siefId']);
        if (array_key_exists("error", $output))
            die(print json_encode($output));
        file_put_contents($xsltFileName, $xslt);
    }

    /**
     * Create a default XSLT based on the params. It is used when dumping a sign-up sheet with no input file.
     * @param unknown_type $xsltFileName
     * @param unknown_type $row
     */
    public static function createDefaultInput($xsltFileName, &$row) {
        $inputString = "";
        $cdxcoreversion = "./cdxcore/" . self::getLatestCdxCore();
        $inputString .= "$cdxcoreversion\n";

        $database = "konly\n";
        $inputString .= $database;
        $inputString .= "$xsltFileName\n";
        $inputString .= ".*\n";

        if (!empty($row['urlPattern']))
            $fetchPattern = $row['urlPattern'];
        else
            $fetchPattern = "site:" . $row['domain'];

        $inputString .= "$fetchPattern\n";
        $inputString .= "480\n";
        $inputString .= "not_random\n";
        $inputString .= $row['email'] . "\n";
        $inputString .= $row['siefId'] . "\n";
        $inputString .= "http://*{$row['domain']}/*\n";
        $inputString .= "private\n";

        $annotationWdCd = "";
        self::getAnnotationWdCd($fetchPattern, $annotationWdCd);
        $inputString .= "$annotationWdCd\n";
        $output = CommonUtil::executeSiefUpdate(array("inputParam" => $inputString), $row['siefId']);
        if (array_key_exists("error", $output))
            die(print json_encode($output));
        file_put_contents($xsltFileName . ".input", $inputString);
    }

    public static function createLogDirectory($siefId) {
        $logDir = get_cfg_var("LOGDIR");
        // Make the directory for each unique id
        if (!is_dir($logDir . "/$siefId")) {
            if (!mkdir($logDir . "/$siefId", 0755, true)) {
                return;
            }
        }
    }

    /**
     * Returns the latest CDXCORE binaires active in the system
     */
    public static function getLatestCdxCore() {
        $cdxcoreDir = get_cfg_var("SIEF_VALIDATOR_PEAR_DIR") . "/scripts/cdxcore/";
        if (!is_dir($cdxcoreDir)) {
            return "";
        } else {
            $filelist = array();
            if ($handle = opendir($cdxcoreDir)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        //echo linkinfo(readlink($cdxcoreDir.$file));
                        if (file_exists(realpath($cdxcoreDir . $file))) {
                            $split = explode("_", $file);
                            $version = substr($split[0], 3);
                            $filelist[$version] = $file;
                            //echo "<option >$file</option>";
                        }
                    }
                }
                closedir($handle);
            }
            krsort($filelist, SORT_NUMERIC);
            $count = 0;
            foreach ($filelist as $file) {
                $count++;
                if ($count == 1)
                    return $file;
            }
        }
        return;
    }

    /**
     * Apply the template on the given input
     * @param unknown_type $fname
     * @param unknown_type $input
     * @param unknown_type $templateType
     */
    public static function applyTemplate($fname="", $input="", $templateType="none") {
        if ($templateType == "none" || $templateType == "Do Not Apply")
            return "";

        if (empty($input)) {
            if (file_exists($fname))
                $input = file_get_contents($fname);
            else
                return "";
        }
        $template = "";
        $output = "";
        $templateFile = get_cfg_var("LOGDIR") . "/../templates/$templateType.xsl";
        if (file_exists($templateFile)) {
            //echo "GOT THERE";
            $template = file_get_contents($templateFile);
        }
        if (!empty($template)) {
            //$output = str_replace("<!--@@@INSERT@@@-->",$input,$template);
            $startStr = "<!-- DO NOT MODIFY ANYTHING ABOVE THIS LINE -->";
            $endStr = "<!-- DO NOT MODIFY ANYTHING BELOW THIS LINE -->";
            $startPos = strpos($template, $startStr);
            $endPos = strpos($template, $endStr);
            if ($startPos !== FALSE && $endPos !== FALSE) {
                $len = strlen($startStr);
                $output = substr($template, 0, $startPos + $len) . $input . substr($template, $endPos);
            }
        }
        //echo $output;
        return $output;
    }

    /**
     * Get the template from a given xslt string
     * @param $xslt
     * @param $templateType
     */
    public static function getTemplate($xslt, $templateType) {
        if ($templateType == "none" || $templateType == "Do Not Apply")
            return "";
        $startStr = "<!-- DO NOT MODIFY ANYTHING ABOVE THIS LINE -->";
        $endStr = "<!-- DO NOT MODIFY ANYTHING BELOW THIS LINE -->";
        $startPos = strpos($xslt, $startStr);
        $endPos = strpos($xslt, $endStr);
        $template = "";
        if ($startPos !== FALSE && $endPos !== FALSE) {
            $len = strlen($startStr);
            $template = substr($xslt, 0, $startPos + $len) . "\n<!--@@@INSERT@@@-->\n" . substr($xslt, $endPos);
        }
        return $template;
    }

    /**
     * Get the stub from the given XSLT string
     * @param $xslt
     * @param $templateType
     */
    public static function getStub($xslt, $templateType) {
        if ($templateType == "none" || $templateType == "Do Not Apply")
            return $xslt;

        $startStr = "<!-- DO NOT MODIFY ANYTHING ABOVE THIS LINE -->";
        $endStr = "<!-- DO NOT MODIFY ANYTHING BELOW THIS LINE -->";
        $startPos = strpos($xslt, $startStr);
        $endPos = strpos($xslt, $endStr);
        $stub = "";
        if ($startPos !== FALSE && $endPos !== FALSE) {
            $len = strlen($startStr);
            $stub = substr($xslt, $startPos + $len, $endPos - ($startPos + $len));
        }
        if (!empty($stub))
            return $stub;
        return $xslt;
    }

    /**
     * Function to run the synchronization sript ( it synchronize the SVVM UI And DATASTORE database )
     */
    public static function updateSiefAndSvvmState() {
        $logDir = get_cfg_var("LOGDIR");
        $docroot = get_cfg_var("SIEF_VALIDATOR_DOC_ROOT");
        //synchronize the svvm and sief database on every job query.
        $command = "/home/y/bin/php $docroot/updateSiefState.php >>" . $logDir . "/siefUpdateState.out 2>&1 &";
        //echo "<p>$command</p>";
        $dir = shell_exec($command);
    }

    /**
     * Function for diplaying the editor home table
     * @param unknown_type $params
     * @param unknown_type $logrepository
     * @param unknown_type $database
     * @param unknown_type $isdisplay
     */
    public static function executeQuery($params, $logrepository="sieflog", $database="siefMetadata", $isdisplay=true) {

        if ($isdisplay)
            CommonUtil::displayHeader($logrepository);

        self::updateSiefAndSvvmState();

        $siefRules = CommonUtil::getSiefRules($params);
        //print_r($siefRules);
        if (isset($siefRules['siefRules']))
            foreach ($siefRules['siefRules'] as $row)
                if ($isdisplay)
                    CommonUtil::displayOneColumn($database, $row, $logrepository);

        if ($isdisplay)
            CommonUtil::displayFooter($logrepository);

        //print json_encode($output);
    }

// end of function execute query

    public static function displayHeader($logrepository) {
?>
        <script>
            function confirmSubmit() {
                if (confirm("Are you sure to re-run Sief Rule? It will overwrite your previous run")) return true;
                return false;
            }

            function confirmReSubmit(){
                if (confirm("Are you sure to resubmit in SVVM datastore? It will deactivate your pervious submission.")) return true;
                return false;
            }
            function confirmRerun() {
                if (confirm("Are you sure to re-run Sief Rule? It will overwrite your previous run")) return true;
                return false;
            }
            function confirmAddSubmit(){
                if (confirm('Are you sure to add in SVVM datastore?' )) return true;
                return false;
            }
            function confirmRevalidate() {
                if (confirm("Are you sure to Re-validate the Sief Rule? Please check the output before re-validating")) return true;
                return false;
            }
            function confirmRemoveSubmit(){
                if (confirm('Are you sure to Remove this rule from SVVM datastore? Changes wiil deactivate the rule.' )) return true;
                return false;
            }

            function confirmDeleteSubmit(){
                if (confirm('Are you sure want to delete this SIEF rule? Once deleted You are not able to recover the rule.' )) return true;
                return false;
            }

            function confirmApprove() {
                if (confirm("Are you sure to approve the Sief Rule? It will submit this Rule to SVVM Production")) return true;
                return false;
            }
            function confirmReject() {
                if (confirm("Are you sure to Reject the Sief Rule? It will Remove this Rule from your review list")) return true;
                return false;
            }
            function confirmRemove() {
                if (confirm("Are you sure to Remove the Sief Rule from production?")) return true;
                return false;
            }

        </script>
        <table id="rulesTable" class="border" border="2" cellpadding="5" align="center"
               style="background-color: white; text-align: center;">
            <thead>
                <tr style="background-color: #D6D6D6;">
                    <th>ID</th>
                    <th>Domain</th>
                    <th>Submitted TS</th>
                    <th>Market</th>
                    <th>Type</th>
                    <th>Test</th>
                    <th>Progress</th>
                    <th>Coverage</th>
                    <th>Accuracy</th>
                    <th>SvvmState</th>
                    <th>Action</th>
                    <th>Approve</th>
                    <th>Reject</th>
                    <th>Logs</th>
                    <th>Input</th>
                    <th>Output</th>
                    <th>DeleteSief</th>
                    <th>Cap</th>
                </tr>
            </thead>
            <tbody>
        <?php

    }

    public static function displayFooter($logrepository) {

        echo "</tbody></table>";
    }

    public static function displayOneColumn(&$database, &$row, &$logrepository) {
        $upperLimit = UPPER_LIMIT;
        $lowerLimit = LOWER_LIMIT;
        $logDir = get_cfg_var("LOGDIR");
        $siefId = $row["siefId"];
        $repository = $logDir . "/" . $row["siefId"] . "/";
        $logFilename = $logDir . "/" . $row["siefId"] . "/" . $row["xslName"] . ".log";
        $smVsSiefLogFile = $repository . $row["xslName"] . ".out.SmVsSief.log";
        $outputFile = $repository . $row["xslName"] . ".out";
        $smidFile = $repository . "smid.out";

        echo '<tr><td>';
        echo '<a href="/editor/editor.php?siefId=' . $row["siefId"] . '"  TITLE="See Rule Homepage" target="_blank">' . $row["siefId"] . '</a>';
        echo '</td><td>';
        echo $row["domain"];
        echo '</td><td>';
        echo $row['executionTs'];
        echo '</td><td>';
        echo $row['market'];
        echo '</td><td>';
        echo $row['type'];

        echo '</td><td>';
        if ($row['state'] != "Finished")
            $color = "red"; else
            $color = "#2A5DB0;";
        echo '<span style="color: ' . $color . '">' . $row["state"] . '</span>';

        echo '</td><td>' . $row["progress"];

        echo '</td><td>';
        echo $row["coverageNum"];
        echo '</td>';

        if ($row['reviewed'] == "N")
            $color = "Black";
        elseif ($row['fmeasure'] > $upperLimit)
            $color = "Green";
        elseif ($row['fmeasure'] < $lowerLimit)
            $color = "Red";

        else
            $color="Blue";
        echo '<td><span style="color: ' . $color . '">';
        if ($row['reviewed'] == "N")
            echo "n/a";
        else
            echo $row["fmeasure"];
        echo '</span></td>';

        if ($row['svvmstate'] == "Valid")
            $color = "Green";
        elseif ($row['svvmstate'] == "Submitted")
            $color = "Blue";
        elseif ($row['svvmstate'] == "Not_Submitted")
            $color = "Black";
        else
            $color = "Red";
        echo '<td><span style="color: ' . $color . '; ">';

        if ($row['svvmstate'] != "Not_Submitted" && $row['svvmstate'] != "Submitted" && $row['svvmstate'] != "Rejected") {
            //$svvmurl = get_cfg_var("svvmurl")."/svvm/datastore/v2/getRule?ruleId=".$row['svvmRuleId'];
            $svvmurl = "/svvmoutput.php?svvmRuleId=" . $row['svvmRuleId'];
            echo "<a href='$svvmurl'>";
        }
        if ($row['svvmstate'] != "Submitted")
            echo $row["svvmstate"];
        elseif ($row['reviewstatus'] == "Being_Reviewed")
            echo "Being Reviewed";
        else
            echo "Ready for Review";
        if ($row['svvmstate'] != "Not_Submitted" && $row['svvmstate'] != "Submitted" && $row['svvmstate'] != "Rejected")
            echo "</a>";
        //if($row['svvmstate']=="Valid") echo "&nbsp;&nbsp;&nbsp;<a style=\"color:Red;\" href=\"http://search.yahoo.com/search?p=sief:".$row['svvmRuleId'].":execution_successful\" TITLE=\"See Result in Yahoo! Search.\">Y!</a>";

        echo '</td><td>';

        $value = '<a class="normalButtton" style="padding:1px 23px;" href="/editor/editor.php?siefId=' . $row['siefId'] . '" >n/a</a>';
        if ($row['validationcount'] > 0 && $row['svvmstate'] == "Valid") { //REvalidation check
            $value = '<a  class="highlightedButtton" href="/inc/validate.php?siefId=' . $row['siefId'] . '&type=review" onClick="return confirmRevalidate()" TITLE="Re-Validate the rule after seeing the output" >Revalidate</a>';
        } elseif ($row['svvmstate'] == "Submitted" && $row['reviewed'] == "N") {
            $value = '<a  class="highlightedButtton" href="/review/editorial.php?siefId=' . $row['siefId'] . '" TITLE="Review the results">Review</a>';
        } elseif ($row['state'] == "Finished" && $row['svvmstate'] == "Not_Submitted") {
            $value = '<a  class="highlightedButtton" href="/editor/submitreview.php?siefId=' . $row['siefId'] . '&action=submit" onclick="return confirmSubmit()" TITLE="Submit the rule for review" >Submit</a>';
        } elseif ($row['svvmstate'] == "Rejected" && $row['state'] == "Finished") {
            $value = '<a  class="highlightedButtton" href="/editor/submitreview.php?siefId=' . $row['siefId'] . '&action=resubmit" onclick="return confirmSubmit()" TITLE="Re-Submit the rule for review" >ReSubmit</a>';
        } elseif ($row['svvmstate'] != "Not_Submitted" || $row['svvmstate'] != "Rejected") {
            $value = '<a class="normalButtton"  style="padding:1px 23px;"  href="/review/review.php?siefId=' . $row['siefId'] . '" >n/a</a>';
        } elseif ($row['state'] == "Obsolete") {
            $value = '<a class="highlightedButtton" href="/editor/rerun.php?siefId=' . $row['siefId'] . '"  onclick="return confirmRerun()" TITLE="Rerun Your Rule" >Rerun</a>';
        }

        echo $value;
        echo '</td><td>';

        if (( $row['svvmstate'] == "Submitted" || $row['svvmstate'] == "Rejected" ) && $row["capacityFlag"] == "N" && $row['reviewed'] == "Y") {
            echo '<a class="highlightedButtton"  href="/review/submitsvvm.php?siefId=' . $row['siefId'] . '&action=approve"  onClick="return confirmApprove()" TITLE="Approve/Submit this rule to Production">Approve</a>';
        } else
            echo "<button disabled>Approve</button>";

        echo '</td><td>';
        if ($row['svvmstate'] != "Not_Submitted" && $row['svvmstate'] != "Inactive" && $row['svvmstate'] != "Submitted" && $row['svvmstate'] != "Rejected") {
            echo '<a class="highlightedButtton"  href="/review/submitsvvm.php?siefId=' . $row['siefId'] . '&action=remove"  onClick="return confirmRemove()" TITLE="Remove/De-activate this rule from Production">Remove</a>';
        } else if ($row['svvmstate'] == "Submitted" && $row["capacityFlag"] == "N" && $row['reviewed'] == "Y") {

            echo '<a class="normalButtton"  href="/review/submitsvvm.php?siefId=' . $row['siefId'] . '&action=reject"  onClick="return confirmReject()" TITLE="Reject this rule">Reject</a>';
        } else
            echo "<button disabled>Remove</button>";
        echo '</td><td>';
        echo '<a href="/view.php?fileType=log&siefId=' . $row["siefId"] . '" TITLE="View Log" target="_blank">Log</a>';
        echo '</td><td>';
        echo '<a href="/view.php?fileType=xslt&siefId=' . $row["siefId"] . '" TITLE="View Input" target="_blank">Input</a>';
        echo '</td><td>';
        echo '<a href="/output.php?siefId=' . $row["siefId"] . '" TITLE="View Output" target="_blank">Output</a>';
        echo '</td><td>';
        if ($row['svvmstate'] == "Not_Submitted" || $row['svvmstate'] == "Broken") {
            echo '<a class="normalButtton" style="padding:1px 10px;" href="/editor/delete.php?siefId=' . $row['siefId'] . '&referrer=admin" onclick="return confirmDeleteSubmit()" TITLE="Delete the Rule from Sief test Tool">Delete</a>';
        } else
            echo "<button disabled>Delete</button>";
        echo '</td><td>';
        if ($row["capacityFlag"] == "Y")
            echo '<span style="font-weight:bold;color:Red;">' . $row["capacityFlag"] . '</span>'; else
            echo $row["capacityFlag"];
        echo '</td></tr>' . "\n";
    }

    public static function getPropName($xsltText) {
        if (preg_match('/.*PROP *name= *\"(xml.*)\".*/', $xsltText, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public static function getAllManualRulesFromDS() {
        $svvmQry = array();
        $svvmQry['source'] = "manual";
        $url = get_cfg_var("svvmurl") . "/svvm/datastore/v2/getRules";
        $strParameters = http_build_query($svvmQry);
        $data = CommonUtil::callWebService($strParameters, $url);
        //error_log("Web service response  \n\n") . $data . "\n\n response end";
        $ridArray = json_decode($data, true);
        if (!is_array($ridArray)) {
            echo time() . ": Webservice is not working properly\n";
            return FALSE;
        } else if (!empty($ridArray['error'])) {
            echo time() . ": Returned Error " . $ridArray['error'] . "\n";
            return FALSE;
        }
        return $ridArray;
    }

    public static function callWebService($strParameters, $url) {
        $ch = curl_init($url);
        // Set options
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $strParameters);
        $data = curl_exec($ch);
        curl_close($ch);
        //error_log("Web service response  \n\n" . $data . "\n\n response end");
        $ridArray = json_decode($data, true);
        $err_msg = "";
        if (!is_array($ridArray)) {
            $err_msg = "Webservice is not working properly";
            error_log($err_msg);
        } else if (!empty($ridArray['error'])) {
            $err_msg = $ridArray['error'];
            error_log("Error returned by the web service");
        }
        else {
            error_log("returning data");
            return $data;
        }

        print $err_msg;
        return $err_msg;

    }

    public static function readSchemaFile($type) {
        //TODO: Create deployment task list to create the folders and files on all machines or check out from svn
        //TODO: How to read this conf variable in php
        // All schema types 'person','news','product','video','discussion','local','event','document','game','recipe','shopping','unknown'
        $inputFilename = get_cfg_var("SCHEMADIR") . "/" . $type . ".json";
        $content = file_get_contents($inputFilename);
        if($content){
            $content = trim($content);
        }
        return $content;
    }

    public static function readInitialDocFeed($row, $prop_name) {
        $feedfile = get_cfg_var("LOGDIR") . "/" . $row["siefId"] . "/" . $row["xslName"] . ".out";
        return CommonUtil::format_feed($feedfile, $prop_name);
    }

    public static function getSiefRulesBySVVMRuleId($svvm_rule_id) {
        $params = array();
        $params['svvmRuleId'] = $svvm_rule_id;
        $params['requestedAttributes'] = "xslName,domain,xslt,siefId,svvmRuleId,svvmstate,type";
        $siefRule = CommonUtil::getSiefRules($params, "getSiefRule");
        return $siefRule;
    }

    public static function updateSVVMDSRuleSchemaAndBlobBySVVMRuleIds($ridArray,$skip_till_id="") {
        error_log("Started processing");
        $ruleIds = $ridArray['ruleIds'];
        
        foreach ($ruleIds as $value) {
            $ruleId = $value['ruleId'];
            error_log("Rule id :"  . $ruleId);
            if (!empty($skip_till_id) && strcmp($ruleId,$skip_till_id) <  0) {
                error_log("Skipping Rule id :"  . $ruleId); 
		continue;
            }
            $siefRules = CommonUtil::getSiefRulesBySVVMRuleId($ruleId);
            //print_r ($siefRules);
            //if (!empty($siefRules)) {
            //@ubhay default key is very hacky and is added only in getSiefRule when no siefid is specified
            foreach ($siefRules as $row) {
                error_log("Got sief rule " . $row['siefId'] . " for svvm rule id : " . $ruleId );
                CommonUtil::updateSVVMDSRuleSchemaAndBlob($row);
            }
            //}
        }
    }

    public static function updateSVVMDSRuleSchemaAndBlob($sief_row) {
        $svvmurl = get_cfg_var("svvmurl");
        $message = "Upgrading all DS rules schema and inital doc feed";
        $siefId = $sief_row['siefId'];
        error_log("Sief rule id : " . $siefId);

        

        $propName = CommonUtil::getPropName($sief_row['xslt']);
        error_log("Property found : " . $propName);
        $schema = "";
        if (!($schema = CommonUtil::readSchemaFile($sief_row["type"]))) {
            error_log("No schema file found for this rule " . $siefId);
            return FALSE;
        }
        $init_doc_feed = "";
        if (!($init_doc_feed = CommonUtil::readInitialDocFeed($sief_row, $propName))) {
            error_log("No inital doc feed found for rule: " . $siefId . " State: " . $sief_row['svvmstate']);
            return FALSE;
        }

        error_log("Found intial doc feed. Finding bellwethers now ");
        $feedfile = get_cfg_var("LOGDIR") . "/" . $sief_row["siefId"] . "/" . $sief_row["xslName"] . ".out";
        $command = "grep '<feed>' $feedfile | awk '{print $1}' | head -40";
        error_log($command);
        $bellwetherUrls = shell_exec($command);

        $svvmQry = array();
        $svvmQry['ruleId'] = $sief_row['svvmRuleId'];
        $svvmQry['initialDocFeed'] = $init_doc_feed;  //TODO:: find out what to fill here
        $svvmQry['ruleSchemaBlob'] = $schema;  //TODO:: find out what to fill here
        $svvmQry['bellwetherurls'] = $bellwetherUrls;
        $url = "$svvmurl/svvm/datastore/v2/updateRule";
        $strParameters = http_build_query($svvmQry);
        $ridArray = CommonUtil::callWebService($strParameters, $url);
        //$ridArray = updateSvvm($svvmQry, $url, $message);
    }

    public static function format_feed($feedfile, $prop_name) {
        $mystr = file_get_contents($feedfile);
        if (!$mystr) {
            return false;
        }
        $feedout = "<InitialDocfeedsBlob>\n";
        $feeds = explode("</feed>", $mystr);
        $i = 1;
        $urlout = "";
        foreach ($feeds as $feed) {
            if ($i >= 40)
                break;
            $url = CommonUtil::get_url_from_feed($feed);
            $urlout.=$url . "\n";
            $docfeed = trim(substr($feed, strpos($feed, "<feed>")));
            if (!empty($docfeed)) {
                $docfeed = htmlspecialchars("\n<PROP xmlns:external=\"http://yst.corp.yahoo.com/\" xmlns:myns=\"uri.my.namespace\" xmlns:regex=\"*** Regexes ***\" name=\"" . $prop_name . "\"><![CDATA[" . $docfeed . "</feed>]]></PROP>", ENT_NOQUOTES);
                $url = htmlspecialchars($url, ENT_NOQUOTES);
                $feedout.="<Docfeed url=\"$url\">" . $docfeed . "</Docfeed>\n";
            }
            $i++;
        }
        $feedout.= "</InitialDocfeedsBlob>";
        return $feedout;
    }

    public static function get_url_from_feed($feed) {
        $url = substr($feed, 0, strpos($feed, "<feed>") - 1);
        $url = trim($url);
        //To handle url with no feeds
        $x = explode("\n", $url);
        return end($x);
    }

}

// end of class common util
        ?>

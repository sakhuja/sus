<?php
/**
 * Class for various util functions, not neccesarily required
 * @author anupg
 *
 */
class Util {
    public static function is_vector( &$array ) {
        if ( !is_array($array) || empty($array) ) {
            return false;
        }
        $next = 0;
        foreach ( $array as $k => $v ) {
            if ( $k !== $next ) return false;
            $next++;
        }
        return true;
    }

    public static function xml_encode_node ($string) {
        if ($string === "") return "_";
        return str_replace(array(" ", "/", ":", "`", "?", "=", "'", "@", "!", "&", ";","*"), array("_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_","_"), $string);
    }

    public static function xmlspecialchars($text) {
        return str_replace('&#039;', '&apos;', htmlspecialchars($text, ENT_QUOTES));
    }

    public static function xml_encode($array, $i=0, $root = "_") {
        if(!$i) {
            if(is_numeric($root)) {
                $root = "_$root";
            }
            $data = "<" . self::xml_encode_node($root) . ">";
        } else {
            $data = '';
        }
        if (!is_array($array)) $data .= self::xmlspecialchars($array);
        else {
            foreach($array as $k=>$v) {
                if(is_numeric($k)) {
                    $k = "_$k";
                }
                $data .= '<' . self::xml_encode_node($k) . '>';
                if(is_object($v) || is_array($v)) {
                    $data .= self::xml_encode($v, ($i+1));
                } else {
                    $dom = @DomDocument::loadXML($v);
                    if ($dom){
                        $xml = $dom->saveXML();
                        $xml = preg_replace("/<\?xml[^>]*>/", "", $xml);
                        $data .= $xml;
                    } else {
                        $data .= self::xmlspecialchars($v);
                    }
                }
                $data .= '</' . self::xml_encode_node($k) . '>';
            }
        }
        if(!$i) {
            $data .= '</' . self::xml_encode_node($root) . '>';
        }
        return $data;
    }

    public static function dieEncode($output) {
        if (!isset($_REQUEST['format'])) {
            $_REQUEST['format'] = "xml";
        }

        switch (strtolower($_REQUEST['format'])) {
            case 'xml' :
            default :
                header("Content-type: application/xml");
                if (is_array($output) && count($output) == 1) {
                    foreach ($output as $key => $value) {
                        //if (is_numeric($key)) break;
                        if ($key === 0) die(self::xml_encode($value, 0)); // This is to make the root _ if it is a numeric array
                        die(self::xml_encode($value, 0, $key));
                    }
                }
                die(self::xml_encode($output));
                break;
            case "pjson" :
                die(json_encode($output));
                break;
            case "json" :
                header("Content-type: application/json");
                die(json_encode($output));
                break;
            case "printr" :
            case "print_r" :
                print_r($output);
                die();
                break;
        }
    }

    public static function dieError($type, $msg) {
        self::dieEncode (array("error" => array ($type => $msg) ) );
    }

    public static function requiredParam($params) {
        if (!is_array($params)) $params = array($params);
        foreach ($params as $param) {
            if (!isset($_REQUEST[$param])) self::dieError("missingParam", $param);
        }
    }

    public static function fetch($url, $proxy = NULL, $timeout = NULL, $headOnly = NULL) {
        /*
         $dest_file = '/tmp/searchmonkey_feedValidator/' . md5($url);
         $cacheTimeout=8640000;
         if(!file_exists($dest_file) || filemtime($dest_file) < (time()-$cacheTimeout) || (($size=filesize($dest_file))<7)) {
         */
        if ($proxy === NULL) $proxy = FALSE;
        if ($timeout === NULL) $timeout = 15;
        if ($headOnly === NULL) $headOnly = FALSE;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, 'http://hlf510000.crawl.yahoo.net:8080');
            curl_setopt($ch, CURLOPT_USERAGENT, 'OnlyProcessed OriginalHeader NoJS HLFS_Param AnnLevel=0;XMLClean=true;');
            //curl_setopt($ch, CURLOPT_USERAGENT, 'NoJS OriginalHeader HLFS_Param AnnLevel=0;XMLClean=true;');
        } else {
            curl_setopt($ch, CURLOPT_USERAGENT, 'Yahoo! Feed Validator');
        }
        if ($headOnly) {
            curl_setopt($ch, CURLOPT_NOBODY, TRUE);
        }
        //$data = curl_exec($ch);
		$data = self::curl_redir_exec($ch);
        $info = curl_getinfo($ch);
		//echo $data;	
        if ($info['http_code'] != 200 ) { //&& $info['http_code'] != 0) {
            throw new Exception($info['http_code']);
        }
        
        curl_close($ch);
   
        return $data;
    }

    
    function fetchXML($url, $proxy = NULL, $timeout = NULL, &$errors = NULL) {
        $data = self::fetch($url, $proxy, $timeout);
        
        $d = self::gzdecode($data);
        if (strlen($d) > 0) {
            $data = $d;
        }

        if ($data == "") throw new Exception("No Data Fetched from : $url");
        libxml_use_internal_errors(TRUE);
        
        $data = preg_replace(',(<[^>]* )xmlns="http://www.inktomi.com/",', "$1",$data);
        $data = preg_replace(",(<[^>]* )xmlns='http://www.inktomi.com/',", "$1",$data);
        
        
        $dataDoc = DomDocument::loadXML($data);

        if (!$dataDoc) {
            $xmlerrors = libxml_get_errors();
            $num = 0;
            $errors = array();
            foreach ($xmlerrors as $key => $value) {
                //if ($num++ >= 10) break;
                $errors[$key] = $value;
            }
            libxml_clear_errors();
            throw new Exception("Bad XML");
        }
        libxml_use_internal_errors(FALSE);

        return $dataDoc;
    }

	
    public function curl_redir_exec(&$ch)
    {
        static $curl_loops = 0;
        static $curl_max_loops = 10;
        if ($curl_loops++ >= $curl_max_loops)
        {
            $curl_loops = 0;
            return FALSE;
        }
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        $header="";
        //echo $data."\n";
        $dataArray = explode("\r\n\r\n", $data, 2);
        $header = $dataArray[0];
        if (count($dataArray) > 1) $data = trim($dataArray[1]);
        else $data = "";
        //echo $header."\n";
        //list($header, $data) = explode("\n\n", $data, 2);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code == 301 || $http_code == 302)
        {
            $matches = array();
            preg_match('/Location:(.*?)\n/', $header, $matches);
            $url = @parse_url(trim(array_pop($matches)));
            if (!$url)
            {
                //couldn't process the url to redirect to
                $curl_loops = 0;
                return $data;
            }
            $last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
            if (!isset($url['scheme']))
                $url['scheme'] = $last_url['scheme'];
            if (!isset($url['host']))
                $url['host'] = $last_url['host'];
            if (!isset($url['port']) && isset($last_url['port']))
                $url['port'] = $last_url['port'];
            if (!isset($url['path']))
                $url['path'] = $last_url['path'];
            if (!isset($url['query']))
                if (isset($last_url['query'])) $url['query'] = $last_url['query'];
                else $url['query'] = "";
                
            $new_url = $url['scheme'] . '://' . $url['host'] . (isset($url['port'])?':'.$url['port']:'') . $url['path'] . ($url['query']?'?'.$url['query']:'');
            //echo $new_url."\n";
            $new_url = yiv_get_url($new_url);
            if($new_url == "") return "";
            curl_setopt($ch, CURLOPT_URL, $new_url);
            //debug('Redirecting to', $new_url);
            return self::curl_redir_exec($ch);
        } else {
            $curl_loops=0;
            return $data;
        }
    }

    function gzdecode($data){
        $g=tempnam('/tmp','util');
        @file_put_contents($g,$data);
        ob_start();
        readgzfile($g);
        $d=ob_get_clean();
        unlink($g);
        return $d;
    }

    function randomString($l = 10, $c = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxwz"){
        $s = "";
        for(;$l > 0;$l--) $s .= $c{rand(0,strlen($c)-1)};
        return str_shuffle($s);
    }
    
    function getCachedDocFeed($url){
    	$qryUrl = "http://eagle-west-proxy.idp.inktomisearch.com:55556/search?"; 
    	$docFeedQry = array();
    	$docFeedQry['Fields']="url,xml.docfeed";
    	$docFeedQry['Client']="yahoous2";
    	$docFeedQry['Database']="wow~YHOO-en-us";
    	$docFeedQry['Query']="ALLWORDS(wwwurl:$url)";
    	
    	$finalUrl = $qryUrl . http_build_query($docFeedQry);
    	//echo $finalUrl;
	    try {
		    $dataDoc = Util::fetchXML($finalUrl, TRUE, 25);
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		    //Util::dieError("BadURL", "Direct CURL: " . $e->getMessage());
		}
		return $dataDoc; 
    }
}
?>

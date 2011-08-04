<?php

class AminoAcid {
    var $name;  // aa name
    var $symbol;    // three letter symbol
    var $code;  // one letter code
    var $type;  // hydrophobic, charged or neutral
    
    function AminoAcid ($aa) 
    {
        foreach ($aa as $k=>$v)
            $this->$k = $aa[$k];
    }
}

function readDatabase($filename) 
{
    // read the XML database of aminoacids
    $data = implode("", file($filename));
    $parser = xml_parser_create();
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, $data, $values, $tags);
    xml_parser_free($parser);
	
    echo "** Values :\n";
    print_r($values);
 	echo "** Tags :\n";
    print_r($tags);
    // return $tags;
    
 // loop through the structures
 foreach ($tags as $key=>$val) {
        if ($key == "tags") {
            $arrTags = $val;
            // each contiguous pair of array entries are the 
            // lower and upper range for each molecule definition
            foreach ($arrTags as $k=>$v){
             echo $values[$v]["value"]."\n"; 	
            }
        } else {
            continue;
        }
    }
    return $tdb;  
}

function parseMol($mvalues) 
{
    for ($i=0; $i < count($mvalues); $i++) {
        $mol[$mvalues[$i]["tag"]] = $mvalues[$i]["value"];
    }
    return new AminoAcid($mol);
}

$db = readDatabase("./sitemap.xml");
echo "** Database of AminoAcid objects:\n";
//print_r($db);

?>
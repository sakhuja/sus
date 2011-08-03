<?php

$doc = new DOMDocument;
$doc->load('sitemap.xml');

$items = $doc->getElementsByTagName('url');

for ($i = 0; $i < $items->length; $i++) {
   echo $items->item($i)->nodeValue . "\n";
}

?> 
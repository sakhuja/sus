<h1>Brain Teasers </h1>

<?php
// read from the file here 
// a single file will contain all the puzzles 
// solutions handling not incuded in this version

$fh  = fopen("logic-puzzles.ads","r");

while(!feof($fh)){
	$line  = fgets($fh);
	 print ($line . "<BR>");
	 
}

fclose($fh);
?>
<?php

 	include("search-lib.php");
	
	// main entry point
	if($_POST["selected"] == "y" )  {
		 $q = $_POST["query"];
		 $obj_f = new finder;
		 $obj_f->find($q);
	}
	
?>
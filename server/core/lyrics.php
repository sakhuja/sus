<script language="JavaScript">
	function validate(){
		<!-- validate and submit -->
		alert("validation");
		<!-- document.getElementById("frm_search").submit(); -->
		alert ("done");
	} 
</script>

<h1> What matters are the words ... </h1>
	<form name ="frm_search" action="index.php?pgid=5" method="post">
			<input type="hidden" name="selected" value="y">
			<input type="text" name="artist" value="-- ARTIST --" size="20" class="txtbox">
			<button  type="submit" name="btn_search" value="Search" class="btn">Search</button>
	</form>
	
 <?php 
 
	if	($_POST["selected"] == "y" )  {
		// search for $_POST("artist") in the filesystem
		// display the files in the matching folders
		// echo "display contents here"; 
		
		$list_result  = array();
		$dir = 'lyrics';
		$list_dirs = scandir($dir);
		
		foreach($list_dirs as $value){			
			if(ereg($_POST["artist"], $value)){
				array_push($list_result,$value);
			}
	  	}

		foreach($list_result as $dir){
			$list_files = scandir("lyrics/" . $dir);
			// display  files
			// print_r($list_files);	
			foreach($list_files as $value){
				if($value != '.' and $value != ".."){
					// print("<hr><br><br>");		
					//print("<h2>Songs for " . $dir . "</h2><br>");	
						print($dir . " : " . $value);
					 print("<hr><br>");				
						
						// reading and displaying a file
						$fh  = fopen("lyrics/". $dir ."/" . $value ,"r");
						while(!feof($fh)){
							$line  = fgets($fh);
							 print ($line . "<BR>");
						 }
						fclose($fh);						
						
					// include("lyrics/". $dir ."/" . $value );			
					// echo "<br>" . "<a href ='" . "lyrics/". $dir ."/" . $value . "'>" . $value . "</a>"; 
					// echo "<br> <a href = lyrics.php?+song=". $value . ">" . $value . "</a>"; ;	 			
		                 }	 			
	                 }
	          }   
	
	 }
	else
	{
		print "<img src ='images/dd.jpg'>";
		// display an image
 	} 

 ?>

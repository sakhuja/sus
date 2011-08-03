<script language="JavaScript">
	function validate(){
		<!-- validate and submit -->
		alert("validation");
		<!-- document.getElementById("frm_search").submit(); -->
		alert ("done");
		alert("done");
	}
	
</script>

<h1> -- Resume Checker -- </h1>
	<form name ="frm_search" action="main.php?pgid=3" method="post">
			<input type="hidden" name="selected" value="y">
			<input type="text" name="txt_resume_search" value="keywords" size="20" class="txtbox">
			<button  type="submit" name="btn_search" value="resume_checker" class="btn">Resume_Checker</button>
	</form>
	
 <?php 
      if($_POST["selected"] == "y" ){
                $last_line = system('/homes/asakhuja/TET-3.0p1-Linux/bin/tet --outfile ./out.out --text ~/
                Resume_Hrishikesh_Mantri.pdf > /dev/null',$retval);
		$myFile = "out.out";
		$fh = fopen($myFile, 'r');
		$theData = fread($fh, filesize($myFile));
		// echo $theData;
		fclose($fh);
                // $last_line = system('cat ./out.out',$retval);
                $wordChunks = explode(",", $theData );
		// print(substr_compare(strtolower($theData),strtolower($_POST["txt_resume_search"],0)==0));	
	        print_r($wordChunks); 	
		$flg_found = 0;
		foreach($wordChunks as $k){
		  // substr_compare("Hello world","world",6);
		  // // if(substr_compare(strtolower($k),strtolower($_POST["txt_resume_search"],0))) 	
		    if(strtolower(trim($k)) == strtolower(trim($_POST["txt_resume_search"]))){ 	
		      $flg_found = 1;
                      print(" <BR>" . $_POST["txt_resume_search"] . "  ::  A match ... !! :-) ");
		    }
		}
		if (!$flg_found) 
		 print "oops, no match.";

	}
	else{
		print "<img src ='images/dd.jpg'>";
		// display an image
 	} 

 ?>

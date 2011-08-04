<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>FEEDBACK</title>
</head>

<body>

<?php 
	if($_GET["submit"] == "1" )
		echo "<h2><font color='#6699FF'> Thank you. Your feedback is important to us.</font></h2>"
?>

<h1>Please provide your feedback</h1>
<form name="frmFeedback" action="index.php?pgid=11&submit=1" method="post">
<table width="100%">
  <tr>
    <td>name</td>
    <td><input type="Text" name="Vname" size="25"></td>
  </tr>
  <tr>
    <td>email</td>
    <td><input type="Text" name="Vemail" size="25"></td>
  </tr>
  <tr>
    <td>comments</td>
    <td><textarea cols="50" rows="5" name="Comms"></textarea></td>
  </tr>
  <tr>
    <td><input type="reset" name="R1" value="Reset"></td>
    <td><input type="submit" name="S1" value="Submit"></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td></td>
  </tr>
</table>
</form>

</body>
</html>

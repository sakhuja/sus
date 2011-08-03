<?php

 // Outputs all the result of shellcommand "ls", and returns
 // the last output line into $last_line. Stores the return value
 // of the shell command in $retval.
 $last_line = system('/homes/asakhuja/TET-3.0p1-Linux/bin/tet --outfile ./out.out --text ~/Resume_Hrishikesh_Mantri.pdf',$retval);

 // Printing additional infoi
 $last_line = system('cat ./out.out',$retval);
 echo $retval;

?>

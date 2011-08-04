<?php

	/*** mysql hostname ***/
	$hostname = 'localhost';
	
	/*** mysql username ***/
	$username = 'root';	
	
	/*** mysql password ***/
	$password = '';	
	echo "PDO practice";
	
	try{
		    $dbh = new PDO("mysql:host=$hostname;dbname=warzone", $username, $password);
		    /*** echo a message saying we have connected ***/
		    
		    echo 'Connected to database';
		    $obj_db = new db();
		    
		    // $obj_db->insert($dbh);
			// $obj_db->select($dbh);
			// $obj_db->update($dbh);  
			$obj_db->fetch_assoc($dbh);
			
    		/*** close the database connection ***/
    		$dbh = null;
	}
	
	catch(PDOException $e){
	    	echo $e->getMessage();
	}

	class person{
		public $id;
		public $name;
		public $age;
		public $addr;
		public $dob;
		
	}
	
	// class db
	class db{	

		function insert($dbh){
		    $date = date("Y-m-d",'06/01/1984');
		    
		    /*** INSERT data ***/
	    	$count = $dbh->exec("INSERT INTO person ( name , age, addr, dob ) VALUES ('Aditya', 25 , '655 South Fairoaks, Sunnyvale, CA - 94086',$date)");
	    	
	    	/*** echo the number of affected rows ***/
	    	echo $count;
		}

		function select($dbh){
			$sql = "SELECT * FROM person";
    		foreach ($dbh->query($sql) as $row){
        		print $row['name'] .' - '. $row['age'] . '<br />';
        	}
		}
		
		function update($dbh){
			
			/*** UPDATE data ***/
		    $count = $dbh->exec("UPDATE person SET age=23 WHERE id=1");
		    
    		/*** echo the number of affected rows ***/
    		echo $count;
		}
		
		function fetch_assoc($dbh){
		    /*** The SQL SELECT statement ***/
		    $sql = "SELECT * FROM person";
		
		    /*** fetch into an PDOStatement object ***/
	    	$stmt = $dbh->query($sql);

		    /*** set the error reporting attribute ***/
    		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    	
		    // first record
		    // $result = $stmt->fetch(PDO::FETCH_ASSOC);
    	    // $result = $stmt->fetch(PDO::FETCH_NUM);
            // $result = $stmt->fetch(PDO::FETCH_BOTH);
            $objects = $stmt->fetchALL(PDO::FETCH_OBJ);
		            
		    /*** loop over the object directly ***/
			 foreach($result as $key=>$val){
			 	  echo $key.' - '.$val.'<br />';
			 }
			
			// $objects = $stmt->fetchALL(PDO::FETCH_CLASS,'person');
            foreach($objects as $obj){			
	    		echo $obj->name.'<br />';
	    		echo $obj->addr.'<br />';
	    		echo $obj->age;
	    		echo $obj->id.'<br />';
	    		echo $obj->dob;
            }

            // second record
    		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		
		    /*** loop over the object directly ***/
		    
		    foreach($result as $key=>$val){
		   		 echo $key.' - '.$val.'<br />';
    		}

		}	

	}  	// end class
?>

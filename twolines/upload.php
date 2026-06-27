<?php
session_start();
require_once __DIR__ . '/../includes/turnstile.php';
verify_turnstile_or_die('index.php');

#1) File preliminaries
	#1.1 Get info
		#Get file info
			$user_file = basename($_FILES["fileToUpload"]["name"]);
		#Get filetype 
			$file_type = pathinfo($user_file,PATHINFO_EXTENSION);
		#Get filesize 
			$file_size=$_FILES["fileToUpload"]["size"]; 
	#1.2 Validate
		#Check right type of file
			#if ($file_type != "csv" && $file_type != "R" && $file_type != "dta") die ("Sorry, that file type is not allowed.<BR>Only .txt, .R or .dta");
		#Check if too heavy
#			if ($file_size>500000) die ("too heavy, sorry.");
			
	#1.3 Save it
		#Set name of file to be the time
			$time=time();
			$dir_data = '/home/urisoh5/uploaded_data/webstimate.org/twolines/temp/';   //Folder where uploaded files are stored, outside of public_html
			$dir =      '/home/urisoh5/public_html/webstimate.org/twolines/temp/';   //Folder where uploaded files are stored, outside of public_html
			$file= $time.".".$file_type;                                               //Name of file is the time
			
			

#2) Upload the file
	
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $dir_data.$file)) {
      #IF valid extension, go to next step
			#  echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
			$_SESSION['dir_data'] =$dir_data;
			$_SESSION['dir'] =$dir;
			$_SESSION['file']=$file;
			$_SESSION['time']=$time;
			$_SESSION['extension']=$file_type;

			echo ('<meta http-equiv="refresh" content="0; url=preview.php">');
		
    } else {
        echo "Sorry, there was an error uploading your file.";
    }

	
#3 Create session variable for it
	
	?>

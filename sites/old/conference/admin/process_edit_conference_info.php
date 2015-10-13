<?php

	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");	
	session_start();	
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	
	//Code added  to address security problem reported by Sebastian Held 14-Nov-2006
	$header = "Edit Conference Info" ;
	$accepted_privilegeID_arr = array ( 3 => "" ) ;
	$accepted_phaseID_arr = array ( 1 => "" , 2 => "" , 3 => "" , 4 => "" ) ;
	authentication( $header , $accepted_privilegeID_arr , $accepted_phaseID_arr , $homepage , $php_root_path , $GLOBALS["DB_PREFIX"] , &$err_message ) ;		
	
	if($_POST["Submit"] == "Submit")
	{
		
		// If conference is already set up
		// call the function to edit the conference information
		// otherwise set up conference.
		if ($_POST["conferenceID"])
		{
			$result = edit_conference_info($_POST,$_FILES);
		} else {
			$result = setup_conference($_POST,$_FILES) ;	
		}
		
		//echo $result;
		//exit;
		
		if($result === true){
			header("Location: view_conference_info.php");
			exit;
		}
		else{
			do_html_header("Process Edit Conference Info Failed" , &$err_message );
			$err_message .= $result;
		}
	}
	else
	{
		do_html_header("Edit Conference Information");	
	}	
	
?>
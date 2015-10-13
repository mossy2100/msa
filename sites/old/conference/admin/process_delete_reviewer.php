<?php 

	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");	
	session_start();	
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	
	//Code added  to address security problem reported by Sebastian Held 14-Nov-2006
	$header = "Delete Reviewer" ;
	$accepted_privilegeID_arr = array ( 3 => "" ) ;
	$accepted_phaseID_arr = array ( 1 => "" , 2 => "" , 3 => "" , 4 => "" ) ;
	authentication( $header , $accepted_privilegeID_arr , $accepted_phaseID_arr , $homepage , $php_root_path , $GLOBALS["DB_PREFIX"] , &$err_message ) ;		
	
	$registerID = $_POST["registerID"];
	
	if($_POST["Submit"] == "Cancel"){
		header("Location: view_all_reviewers.php");
		exit;
	}
	//Establish connection with database
	$db = adodb_connect();

	if(delete_registration($registerID, &$err_message)){
		header("Location: view_all_reviewers.php");
		exit;
	}
	else{
		do_html_header("Problem");
		echo "<font color='#FF0000'> Could not delete the reviewer information - please try again later <br> <br>";
		echo "$err_message </font>";
		do_html_footer();
	}
		
?>

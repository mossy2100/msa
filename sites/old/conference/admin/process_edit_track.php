<?php 

	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");	
	session_start();	
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	
	//Code added  to address security problem reported by Sebastian Held 14-Nov-2006
	$header = "Edit Track" ;
	$accepted_privilegeID_arr = array ( 3 => "" ) ;
	$accepted_phaseID_arr = array ( 1 => "" , 2 => "" , 3 => "" , 4 => "" ) ;
	authentication( $header , $accepted_privilegeID_arr , $accepted_phaseID_arr , $homepage , $php_root_path , $GLOBALS["DB_PREFIX"] , &$err_message ) ;		
	
	
	if($_POST["Submit"] == "Undo Changes"){
		$str = "Location: edit_track.php?catID=".$_POST["catID"];
		header($str);
		exit;
	}

	if(update_Track($_POST["catID"],$_POST["catName"])){
		header("Location: view_tracks.php");
		exit;
	}
	else{
		do_html_header("Problem");
		echo "Could not update the track information - please try again later";
		do_html_footer();
	}
		
?>
<?php

	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");	
	session_start();	
	$arrPhaseInfo = &$_SESSION["arrPhaseInfo"];	
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	
	//Code added  to address security problem reported by Sebastian Held 14-Nov-2006
	$header = "Change Phase" ;
	$accepted_privilegeID_arr = array ( 3 => "" ) ;
	$accepted_phaseID_arr = array ( 1 => "" , 2 => "" , 3 => "" , 4 => "" ) ;
	authentication( $header , $accepted_privilegeID_arr , $accepted_phaseID_arr , $homepage , $php_root_path , $GLOBALS["DB_PREFIX"] , &$err_message ) ;		
	
	if($_POST["Submit"] == "Back"){
		header("Location: change_phase.php");
		exit;
	}
	
	//Check whether no phase has been setup yet
	if($arrPhaseInfo["currentPhaseID"] == ""){
		//Setup a new phase
		$result = activate_Phase($arrPhaseInfo["newPhaseID"]);
	}
	else{
		//Change the current phase
		if(deactivate_Phase($arrPhaseInfo["currentPhaseID"]))
			$result = activate_Phase($arrPhaseInfo["newPhaseID"]);
	}
	
	//Check the return result
	if ($result === true){
		session_unregister_register_global_off ("arrPhaseInfo");
		header("Location: view_phases.php");
		exit;
	}
	else{
		do_html_header("Problem");
		echo $result;
		do_html_footer();	
	}



?>

<?php 

	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");	
	session_start();
	// extract ( $_SESSION , EXTR_REFS ) ;	
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	
	//Code added  to address security problem reported by Sebastian Held 14-Nov-2006
	$header = "Accept Reject Paper" ;
	$accepted_privilegeID_arr = array ( 3 => "" ) ;
	$accepted_phaseID_arr = array ( 1 => "" , 2 => "" , 3 => "" , 4 => "" ) ;
	authentication( $header , $accepted_privilegeID_arr , $accepted_phaseID_arr , $homepage , $php_root_path , $GLOBALS["DB_PREFIX"] , &$err_message ) ;		
	
	//Establish database connection
  	$db = adodb_connect();
  
  	if (!$db){
   		echo "Could not connect to database server - please try later.";
		exit;
	}	
	
	//Check whether the user needs to reset the from
	if($_POST["Submit"] == "Cancel"){
		unset($_SESSION["arrPostInfo"]);	
		header("Location: view_all_papers.php");
		exit;
	}
	
	//Check whether the user needs to reset the from
	if($_POST["Submit"] == "Back"){
		unset($_SESSION["arrPostInfo"]);	
		$url = "Location: evaluate_paper_status.php?paperID=".$_POST["paperID"];
		$url .= "&status=".$_POST["status"];
		header($url);
		exit;
	}
		
	
	//Check whether the user wish to inform user immediately
	if($_POST["informuser"] == "yes"){
		$url = "Location: compose_accept_reject_mail.php?paperID=".$_POST["paperID"];
		$url .= "&status=".$_POST["status"].'&type='.$_POST["type"];
		if (array_key_exists( "SessionTrackID", $_POST ))
			$url .= "&SessionTrackID=".$_POST["SessionTrackID"];
		header($url);
		exit;		
	}
	
	//Get the paper information
	$paperInfo = get_paper_info($_POST["paperID"]);
	$prevtype = get_presentation_info( get_presentation_type_for_paper($paperInfo -> PaperID) );
	$curtype = get_presentation_info( $_POST["type"] );

	// if SessionTrackID is given, assign paper to this SessionTrack
	if (array_key_exists( "SessionTrackID", $_POST ))
		paper_updateSessionTrack( $_POST["paperID"], $_POST["SessionTrackID"] );

	// Either add or remove presentation from scheduling system
	if ($_POST["status"]=="Accepted")
	{
		// Remove any previous record
		remove_paper_presentation($_POST["paperID"]);
		// Add to scheduling system as an unscheduled paper
		assign_paper_presentation_type($_POST["paperID"], $_POST["type"]);
		// If all is well, then it will get autoscheduled
		autoschedule_waiting_papers();
	} else {
		remove_paper_presentation($_POST["paperID"]);
	}
	
	//The admin does not wish to inform the user
	//Update the paper status to accepted or rejected
	if( update_PaperStatus($_POST["paperID"],$_POST["status"]) )
	{
		$title = "Paper is ".$_POST["status"];
		do_html_header($title);
	?>
		<p>The following paper is <?php echo $_POST["status"] ?><br><br>
		<h3> #<?php echo $paperInfo -> PaperID." ".stripslashes($paperInfo -> Title) ?></h3>
		<table>
		<tr><td><strong>Previous Status:</strong>&nbsp;&nbsp;</td><td><?php echo $paperInfo -> PaperStatusName . ($paperInfo -> PaperStatusName =="Accepted" ? " as ".$prevtype -> PresentationTypeName : "") ?></td></tr>			
		<tr><td><strong>New Status:</strong></td><td><?php echo $_POST["status"].($_POST["status"]=="Accepted" ? " as ".$curtype -> PresentationTypeName : "") ?></td></tr>
		</table><br><br>
		<br>Go back to <a href='/conference/admin/view_all_papers.php?sort=<'>View All Papers</a> page.<br><br>
	<?php
		//echo "Go back to <a href=\"view_all_papers.php\">view all papers</a></p>";
		do_html_footer();
	}	
?>

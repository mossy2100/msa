<?php 

	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");	
	session_start();
	// extract ( $_SESSION , EXTR_REFS ) ;	
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	
	//Code added  to address security problem reported by Sebastian Held 14-Nov-2006
	$header = "Letter" ;
	$accepted_privilegeID_arr = array ( 3 => "" ) ;
	$accepted_phaseID_arr = array ( 1 => "" , 2 => "" , 3 => "" , 4 => "" ) ;
	authentication( $header , $accepted_privilegeID_arr , $accepted_phaseID_arr , $homepage , $php_root_path , $GLOBALS["DB_PREFIX"] , &$err_message ) ;		
	
	
	$url = evaluate_Lettertype_BackURL($_POST["lettertype"]);
	
	//The user click on Back, according to different lettertype go back to differnt page
	if($_POST["Submit"] == "Back"){
		header($url);
		exit;
	}
	
	$arrLetterInfo = & $_SESSION["arrLetterInfo"];
	$letterInfo = get_Letter_Info($arrLetterInfo["letterID"]);	
	
	//The user click Submit button,update the letter information
	$result = update_LetterInfo($_SESSION["arrLetterInfo"]);
	
	if($result === true){

		do_html_header("Successful Update");
		echo "<p>The following letter type has been updated successfully.<br><br>";

		echo "Title: <strong>".$letterInfo -> Title."</strong><br>";
		echo "Subject: <strong>".stripslashes($arrLetterInfo["subject"])."</strong><br>";		
		echo "Recipientgroup: <strong>".$letterInfo -> RecipientGroupName."</strong><br><br>";
		echo "Go to <a href=\"view_letters.php\">View Letters</a> to view the letters.</p>";
		
	}
	else{
		do_html_header("Error Information");
		echo $result;
	}
	
	//Unset the session array
	unset($_SESSION["arrLetterInfo"]);	

	do_html_footer();		




?>

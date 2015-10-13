<?php
	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");	
    ini_set('include_path', "$php_root_path/includes/pear/");
    require_once("$php_root_path"."/includes/pear/Mail.php");
   

	session_start();
	// extract ( $_SESSION , EXTR_REFS ) ;	
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	
	//Code added  to address security problem reported by Sebastian Held 14-Nov-2006
	$header = "Process Accept Reject Mail" ;
	$accepted_privilegeID_arr = array ( 3 => "" ) ;
	$accepted_phaseID_arr = array ( 1 => "" , 2 => "" , 3 => "" , 4 => "" ) ;
	authentication( $header , $accepted_privilegeID_arr , $accepted_phaseID_arr , $homepage , $php_root_path , $GLOBALS["DB_PREFIX"] , &$err_message ) ;		
	
	if($_POST["Submit"] == "Back"){
		//Redirect back to the previous page
		unset($_SESSION["content"]);	
		$url = "Location: compose_accept_reject_mail.php?paperID=".$_POST["paperID"];
		$url .= "&status=".$_POST["status"].'&type='.$_POST['type'];
		header($url);
		exit;
	}
	
	//Establish connection with database
	$db = adodb_connect();
	
	if (!$db){
		do_html_header("Database Conntection Fail");
		$err_message .= "Could not connect to database server - please try later.";		
		do_html_footer( &$err_message );
		exit;		
	}
	
	//Read the session variables
	$arrPostInfo =& $_SESSION["arrPostInfo"];
	$arrAttachmentInfo = & $_SESSION["arrAttachmentInfo"];	
	$arrContent = & $_SESSION["content"];
	
	//get the conference info to get the contact mail
	$conferenceInfo = get_conference_info();
	//Get the paper information
	$paperInfo = get_paper_info($_POST["paperID"]);				
	
	if(update_PaperStatus($arrPostInfo["paperID"],$arrPostInfo["status"])){	
	
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

        //Now using pear SMTP mail instead of libmail
		//Send Email to user
		//~ $mail = new Mail();
			
		//~ $mail -> Organization($conferenceInfo -> ConferenceCodeName);
		//~ $mail -> ReplyTo($conferenceInfo -> ConferenceContact);
			
		//~ $mail -> From($conferenceInfo -> ConferenceContact);
		//~ $mail -> To($arrPostInfo["email"]);	
		//~ $mail -> Subject($arrContent["subject"]);
		//~ $mail -> Body($arrContent["content"]);
			
		//~ if ($arrPostInfo["cc"] != "")	
			//~ $mail -> Cc($arrPostInfo["cc"]);
			
		//~ if(!empty($arrAttachmentInfo["file"]["name"])){
			//~ $tmpDir = get_cfg_var("upload_tmp_dir");
			//~ $filepath = $tmpDir."/".$arrAttachmentInfo["file"]["name"];
			//~ $mail -> Attach($filepath,$arrAttachmentInfo["file"]["name"],$arrAttachmentInfo["file"]["type"]);
		//~ }		
			
		//~ $mail -> Priority(1);		
		//~ $mail -> Send();
        
        $sender = $conferenceInfo -> ConferenceContact;
        $recipient = $arrPostInfo["email"];
        $headers = array(
                'From'          => $conferenceInfo -> ConferenceContact,
                'To'            => $arrPostInfo["email"],
                'Subject'       => $arrContent["subject"],
                'Organization'  => $conferenceInfo -> ConferenceCodeName,
                'Reply-To'      => $conferenceInfo -> ConferenceContact,
                'Cc'            => $arrPostInfo["cc"]
        );

        $message = $arrContent["content"];

        $mailer =& Mail::factory('smtp');
        if (!$mailer->send($recipient, $headers, $message)) {
                $err_message = "Unable to send mail in process_accept_reject_mail.php.";
                return false;
        }

		
		//Call the function to log the information
		$result = updateMailLog($paperInfo -> PaperID,$arrPostInfo["letterID"]);
		
		if($result === true){
			do_html_header("Successful Update");		
			echo "<p>The following paper has been ".$_POST["status"];            
            echo ". ";
            echo "An email has been sent to inform the user.<br><br>";
			echo "<strong>PaperID#".$_POST["paperID"]."</strong><br>";
			echo "<strong>PaperID:</strong> ".stripslashes($paperInfo -> Title)."<br>";
			echo "<strong>Status:</strong> ".$_POST["status"];
            if ($_POST["status"] == "Accepted") echo " as ".$_POST["presType"];
            echo "<br><br>";
			//echo "Go back to <a href=\"view_all_papers.php\">view all papers</a>.</p>";
			echo "Go back to <a href='view_all_papers.php?sort=".$_SESSION["sort"]."&showing=".$_SESSION["showing"].">View All Papers</a>.</p>";
            do_html_footer();		
		}else {
			do_html_header("Error Information");
			echo "<p>$result</p>";
			do_html_footer();
			exit;		
		
		}

	}
	else{
		do_html_header("Error Information");
		echo "<p>Could not update the paper information - please try again</p>";
		do_html_footer();
		exit;
	
	}
	
	//Unregister the session
	unset($_SESSION["arrPostInfo"]);
	unset($_SESSION["arrAttachmentInfo"]);
		
?>

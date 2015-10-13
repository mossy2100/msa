<?php 
	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
    require_once("includes/include_all_fns.inc");
    ini_set('include_path', "$php_root_path/includes/pear/");	
    require_once("$php_root_path"."/includes/pear/Mail.php");
    

	
	session_start() ;
	// extract ( $_SESSION , EXTR_REFS ) ;	
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	
	//Code added  to address security problem reported by Sebastian Held 14-Nov-2006
	$header = "Send Paper Info" ;
	$accepted_privilegeID_arr = array ( 3 => "" ) ;
	$accepted_phaseID_arr = array ( 1 => "" , 2 => "" , 3 => "" , 4 => "" ) ;
	authentication( $header , $accepted_privilegeID_arr , $accepted_phaseID_arr , $homepage , $php_root_path , $GLOBALS["DB_PREFIX"] , &$err_message ) ;		
	
	
	//Get the session variable
	$arrLetterInfo = & $_SESSION["arrLetterInfo"];
	
	if($_POST["Submit"] == "Back"){
		unset($_SESSION["arrContent"]);
		$url = "Location: send_paper_info.php?lettertype=".$arrLetterInfo["lettertype"];	
		header($url);
		exit;
	}
	
	//Get two array from session to process sending
	$arrEmails = $_SESSION["arrUpdateEmails"];
	$arrContent = $_SESSION["arrContent"];
	
	//get the conference info to get the contact mail
	$conferenceInfo = get_conference_info();
	
	//Get the letter info and its constant
	$letterInfo = get_Letter_Info($arrLetterInfo["letterID"]);

	
	while(list($paperID,$email) = each($arrEmails)){	
	
		//Update the mail log	
		$result = updateMailLog($paperID,$arrLetterInfo["letterID"]);	
		
		//If can log the email
		if($result === true){		
			//Send Email to user
			//~ $mail = new Mail();
				
			//~ $mail -> Organization($conferenceInfo -> ConferenceCodeName);
			//~ $mail -> ReplyTo($conferenceInfo -> ConferenceContact);
				
			//~ $mail -> From($conferenceInfo -> ConferenceContact);
			//~ $mail -> To($email);	
			//~ $mail -> Subject(stripslashes($arrLetterInfo["subject"]));
			//~ $mail -> Body($arrContent[$paperID]);
				
			//~ if ($arrLetterInfo["cc"] != "")	
				//~ $mail -> Cc($arrLetterInfo["cc"]);	
				
			//~ $mail -> Priority(1);		
			//~ $mail -> Send();
            
        $sender = $conferenceInfo -> ConferenceContact;
        $recipient = $email;
        $headers = array(
                'From'          => $conferenceInfo -> ConferenceContact,
                'To'            => $email,
                'Subject'       => stripslashes($arrLetterInfo["subject"]),
                'Organization'  => $conferenceInfo -> ConferenceCodeName,
                'Reply-To'       => $conferenceInfo -> ConferenceContact
        );
        $message = $arrContent[$paperID];

        $mailer =& Mail::factory('smtp');
        
        if (!$mailer->send($recipient, $headers, $message)) {
                $err_message = "Unable to send mail in process_send_paper_info.php.";
                return false;
        }

			
            
			//Log the successful send email
			$arrSuccessfulEmails[$paperID] = $email;
		}
		else {
			do_html_header("Error Information");	
			echo "<p>$result</p>";
			do_html_footer();
			
			//Log the unsuccessful email
			$arrFaliureEmails[$paperID] = $email;

			exit;
		}
		
	}
	

	do_html_header("Successful Send");			
	echo "Letter Title: <strong>".$letterInfo -> Title."</strong><br><br>";
	echo "<br>Go back to <a href=\"view_letters.php\">View Letters</a> page.</p>";
	//Display the successful send emails list
	echo display_Letter_Recipients_Per_Paper("The letter was successfully sent to the following recipients.",$arrSuccessfulEmails);
	//Display unsucessful email list
	echo display_Letter_Recipients_Per_Paper("The letter was <strong>NOT</strong> sent to the following recipients.",$arrFaliureEmails);	
	do_html_footer();


	//Unset the session
	unset($_SESSION["arrLetterInfo"]);
	unset($_SESSION["arrUpdateEmails"]);
	unset($_SESSION["arrContent"]);
	unset($_SESSION["arrCurrentRecords"]);	

?>

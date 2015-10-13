<?php 

	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");	
	session_start();
	// extract ( $_SESSION , EXTR_REFS ) ;	
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	
	//Check whether the user needs to reset the from
	if($_POST["Submit"] == "Cancel"){
	
		unset($_SESSION["arrPostInfo"]);	
		header("Location: view_all_papers.php");
		exit;
		
	}else if($_POST["Submit"] == "Back"){
		
		$url = "Location: accept_reject_paper.php?paperID=".$_POST["paperID"];
		$url .= "&status=".$_POST["status"]."&back=true";
		header($url);
		exit;
	}
	
	//Arrange the appropriate letter type
	switch($_POST["status"]){
		case "Accepted":
			$letterTitle = "Paper Acceptance";
			$letterType = "paperacceptance";			
			break;
		case "Rejected":
			$letterTitle = "Paper Rejection";
			$letterType = "paperrejection";						
			break;
	}	
	
	//Update the session variables
	$_SESSION["arrPostInfo"] = $_POST;
	$_SESSION["arrAttachmentInfo"] = $_FILES;
   	
	//Check whether the uploaded file is valid
	if(!empty($_FILES["file"]["name"])){	
		if(is_uploaded_file($_FILES["file"]["tmp_name"])){
			$realname = $_FILES["file"]["name"];
			$tmpDir = get_cfg_var("upload_tmp_dir");			
			copy($_FILES["file"]["tmp_name"],"$tmpDir/$realname");
			//rename($_FILES["file"]["tmp_name"],$_FILES["file"]["name"]);
		}
		else{
			echo "There is an error in attaching file- try again";
			exit;
		}
	}
	
	//Establish connection with database
	$db = adodb_connect();
	
	if (!$db){
		do_html_header("Database Conntection Fail");
		$err_message .= "Could not connect to database server - please try later.";		
		do_html_footer( &$err_message );
		exit;		
	}
	
	//Get the paper information
	$paperID = $_POST["paperID"];
	$paperInfo = get_paper_info($paperID);
    $type = $_POST["type"];
    $presType = $_POST["presType"];
    	
	//Retrieve the setting information
	$settingInfo = get_Conference_Settings();
	$conferenceInfo = get_conference_info();			
	
	//Format the subject of the letter
	$strSubject = format_Letter_Subject(stripslashes($_POST["subject"]));				
	
	//Format the content string and Store the content into the session
	$strContent = stripslashes(wordwrap($_POST["bodycontent"]))."\n\n";	
	$strContent .= $settingInfo -> EmailSignature."\n\n";

	//Get the constant of the letter and highlight the constants
	$arrConstants = evaluate_Letter_Constants($letterType);

	//Get the paper info
	$strAuthors = retrieve_authors($paperID, &$err_message);
	$strCat = getSelectedCategoryCommaSeparated($paperID, &$err_message);
	
	//Get the full name of the user according to his user name
	$strFullName = getMemberFullName($paperInfo -> MemberName);
	$arrReplaceInfo = array(
						"fullname" => $strFullName,
						"paperID" => $paperInfo -> PaperID,
						"papertitle" => $paperInfo -> Title,
						"authors" => $strAuthors,
						"papercat" => $strCat,
						"url" => $settingInfo -> HomePage,
						"confname" => $conferenceInfo -> ConferenceName,
						"confcode" => $conferenceInfo -> ConferenceCodeName,
						"contact" => $conferenceInfo -> ConferenceContact,
                        "presType" => $presType);							
						
	//Replace the dynamice constants with real values
	$strContent = replace_Dynamic_Values($arrConstants,$arrReplaceInfo,$strContent);
	
	//Store the content into the session
	$arrContent["subject"] = $strSubject;
	$arrContent["content"] = $strContent;
	$_SESSION["content"] = $arrContent;
	
	do_html_header($letterTitle);

?>
<form name="form1" method="post" action="process_accept_reject_mail.php">
  <table width="100%" border="0" cellspacing="0" cellpadding="1">
    <tr> 
      <td colspan="2"><strong>Below is the information on the paper you have <?php echo $_POST["status"]; ?> and the preview of your letter. An email will send out to 
        inform the user immediately. 
	<br><br>
	Please click Send to confirm.</strong></td>
    </tr>
    <tr> 
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="2"><?php echo stripslashes("<h3>".stripslashes($paperInfo -> Title)."</h3>"); ?></td>
    </tr>
    <input type="hidden" name="paperID" value="<?php echo $paperInfo->PaperID; ?>">
    <tr> 
      <td colspan="2"><strong>Paper #</strong><strong><?php echo $paperInfo->PaperID; ?></strong></td>
    </tr>
    <tr> 
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr> 
      <td width="15%"><strong>Authors:</strong> </td>
      <td width="85%"><?php echo $authors; ?></td>
    </tr>
    <tr> 
      <td><strong>Keywords:</strong> </td>
      <td><?php echo $catcomsep; ?></td>
    </tr>
    <tr> 
      <td><strong>Status:</strong></td>
      <td><?php echo $_POST["status"]; ?>
            <?php if ($_POST["status"] == "Accepted") { ?>
	  as <?php echo $presType; ?>
      <?php } ?>
      </td>
    </tr>
    <input type="hidden" name="status" value="<?php echo $_POST["status"]; ?>">
    <input type="hidden" name="type" value="<?php echo $_POST['type']; ?>">
    <input type="hidden" name="presType" value="<?php echo $_POST['presType']; ?>">
    <tr> 
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr> 
      <td><strong>To:</strong></td>
      <td><?php echo $_POST["email"]; ?></td>
    </tr>
    <tr> 
      <td><strong>Subject:</strong></td>
      <td><?php echo $strSubject; ?></td>
    </tr>
	<?php if ($_POST["cc"] != ""){ ?>
    <tr> 
      <td><strong>Cc:</strong></td>
      <td><?php echo $_POST["cc"]; ?></td>
    </tr>
	<?php } ?>
    <?php if(!empty($_FILES["file"]["name"])){?>
    <tr> 
      <td><strong>Review File:</strong></td>
      <td><?php echo $_FILES["file"]["name"]; ?></td>
    </tr>
    <?php }?>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr align="center"> 
      <td colspan="2"><strong>----- Letter Starts Here -----</strong></td>
    </tr>
    <tr> 
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="2"><?php echo nl2br($strContent); ?></td>
    </tr>
    <tr> 
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr align="center"> 
      <td colspan="2"><strong>----- Letter Ends Here -----</strong></td>
    </tr>
    <tr> 
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="2"><input name="Submit" type="submit" id="Submit" value="Send"> 
        <input name="Submit" type="submit" id="Submit" value="Back"> </td>
    </tr>
  </table>
</form>
<?php 
	
	do_html_footer();

?>

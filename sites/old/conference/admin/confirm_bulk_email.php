<?php 

	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");	
	session_start();
	// extract ( $_SESSION , EXTR_REFS ) ;	
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	
	//Check whether the user needs to reset the from
	if($_POST["Submit"] == "Reset"){
		
		if(session_is_registered_register_global_off ("arrEmailInfo"))
			session_unregister_register_global_off ("arrEmailInfo");
			
		if(session_is_registered_register_global_off ("arrAttachmentInfo"))
			session_unregister_register_global_off ("arrAttachmentInfo");			
	
		header("Location: bulk_email.php");
		exit;
	}
	
	do_html_header("Mass Email");
	
	//Insert the array variables
	$arrEmailInfo = $_POST;
	$arrAttachmentInfo = $HTTP_POST_FILES;
	
	//Register the array arrEmailInfo
	if (!session_is_registered_register_global_off ( "arrEmailInfo" ))				
		session_register_register_global_off ("arrEmailInfo");
	else
		$_SESSION["arrEmailInfo"] = & $_POST;
	
	//Register the array arrAttachmentInfo
	if (!session_is_registered_register_global_off ( "arrAttachmentInfo" ) && !empty($HTTP_POST_FILES["file"]["name"]))				
		session_register_register_global_off ("arrAttachmentInfo");
	else if(!empty($HTTP_POST_FILES["file"]["name"]))
		$_SESSION["arrAttachmentInfo"] = & $HTTP_POST_FILES;		

	//Check whether the uploaded file is valid
	if(!empty($HTTP_POST_FILES["file"]["name"])){	
		if(is_uploaded_file($HTTP_POST_FILES["file"]["tmp_name"])){
			$realname = $HTTP_POST_FILES["file"]["name"];
			$tmpDir = get_cfg_var("upload_tmp_dir");			
			copy($HTTP_POST_FILES["file"]["tmp_name"],"$tmpDir/$realname");
			//rename($HTTP_POST_FILES["file"]["tmp_name"],$HTTP_POST_FILES["file"]["name"]);
		}
		else{
			echo "There is an error in processing attachment file- try again";
			exit;
		}
	}
		
?>
<form name="form1" method="post" action="process_bulk_email.php">
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr> 
      <td colspan="2"><strong>Below is the information about email. Please confirm 
        to deliver your message to the receipents mentioned below.</strong></td>
    </tr>
    <tr> 
      <td width="15%">&nbsp;</td>
      <td width="85%">&nbsp;</td>
    </tr>
    <tr> 
      <td valign="top">Receipient Type:</td>
      <td><?php echo $_POST["to"]; ?></td>
    </tr>
    <tr> 
      <td valign="top">&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="2" valign="top"><a href="#" onClick="JavaScript: window.open('mailing_list.php?to=<?php echo $_POST["to"]; ?>',null,'height=700,width=700,status=yes,toolbar=no,menubar=no,scrollbars=yes,location=no');"><!--<a href="/conference/admin/mailing_list.php?to=<"to"]; ?>" target="_blank">-->Customize 
        Recipients Addresses</a></td>
    </tr>
    <tr> 
      <td valign="top">&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td valign="top">Recipients Emails:</td>
      <td> 
        <?php  
	  		$arrEmails = get_emails($_POST["to"]);
			for($i = 0;$i < count($arrEmails);$i++){
				if($i == (count($arrEmails) - 1))
					echo $arrEmails[$i];
				else
					echo $arrEmails[$i].", ";
			}
	  ?>
      </td>
    </tr>
    <?php if ($_POST["cc"] != "") { ?>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td>Cc:</td>
      <td><?php echo $_POST["cc"]; ?></td>
    </tr>
    <?php }
	
	if($_POST["bcc"] != ""){
	?>
    <tr> 
      <td>Bcc:</td>
      <td><?php echo $_POST["bcc"]; ?></td>
    </tr>
    <?php }?>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td>Subject:</td>
      <td><?php echo stripslashes(trim($_POST["subject"])); ?></td>
    </tr>
    <?php if(!empty($HTTP_POST_FILES["file"]["name"])){?>
    <tr> 
      <td>Attachment:</td>
      <td><?php echo $HTTP_POST_FILES["file"]["name"]; ?> </td>
    </tr>
    <?php }?>
    <tr> 
      <td>Priority:</td>
      <td> 
        <?php if($_POST["priority"] == 1) echo "Urgent"; else echo "Normal"; ?>
      </td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="2"><strong>Message:</strong><br> <br> <?php echo stripslashes(nl2br(trim($_POST["content"]))); ?></td>
    </tr>
    <tr> 
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="2"><input type="submit" name="Submit" value="Confirm"> <input name="Submit" type="submit" id="Submit" value="Back"></td>
    </tr>
  </table>
</form>
<?php 	do_html_footer(); ?>

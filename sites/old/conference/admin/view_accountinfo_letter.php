<?php 

	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");	
	session_start();
	// extract ( $_SESSION , EXTR_REFS ) ;	
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	
	
	switch($_POST["lettertype"]){
		case "useraccount":
			$title = "Preview User Account Info Letter";
			break;
		case "revieweraccount":
			$title = "Preview Reviewer Account Info Letter";			
			break;
		case "adminaccount":
			$title = "Preview Admin Account Info Letter";			
			break;
	}
	
	
	if($_POST["Submit"] == "Cancel"){
		unset($_SESSION["arrLetterInfo"]);
		header("Location: view_letters.php");
		exit;
	}
	
	//Register the session
	$_SESSION["arrLetterInfo"] = $_POST;
	
	do_html_header($title);
	
?>
<form name="form1" method="post" action="process_letter.php">
  <table width="100%" border="0" cellspacing="0" cellpadding="2">
    <tr> 
      <td><input type="hidden" name="lettertype" value="<?php echo $_POST["lettertype"]; ?>"></td>
    </tr>
    <tr> 
      <td><strong>To:</strong> useremail@domain.com</td>
    </tr>
    <tr> 
      <td><strong>Subject:</strong>&nbsp;<?php echo stripslashes($_POST["subject"]); ?></td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td><p><?php echo $_POST["salutation"]; ?> 
          <?php if ($_POST["username"] == "append") echo " <strong>UserName</strong>"; ?>
        </p></td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td><p><?php echo nl2br(stripslashes($_POST["beforecontent"])); ?></p></td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td> <p>Below is Login information:</p>
        <p>User Name: loginName<br>
          Password: ******</p></td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td><p> <?php echo nl2br(stripslashes($_POST["aftercontent"])); ?> 
        </p></td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td><input type="submit" name="Submit" value="Confrim"> <input name="Submit" type="submit" id="Submit" value="Back"></td>
    </tr>
  </table>
</form>
<?php do_html_footer(); ?>

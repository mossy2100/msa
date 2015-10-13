<?php 
	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;

	require_once("includes/include_all_fns.inc");	
	session_start() ;
	// extract ( $_SESSION , EXTR_REFS ) ;	
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
		
	//User click on Cancel button
	if($_POST["Submit"] == "Cancel"){
		unset($_SESSION["arrLoginInfo"]);
		header("Location: admin_home.php");
		exit;	
	}
	
	//Register the session	
	$_SESSION["arrLoginInfo"] = $_POST;
	
	switch($_POST["accountType"]){
		case "Reviewer":
			$letterType = "revieweraccount";
			$title = "Reviewer Account Setup";
			break;
		case "Administrator":
			$letterType = "adminaccount";
			$title = "Admin Account Setup";
			break;
	}
	
	//User Click on submit button
	if($_POST["informreviewer"] == "yes"){
	
		$url = "Location: compose_setup_account_mail.php?lettertype=".$letterType;
		header($url);
		exit;
	}
	
	do_html_header($title);
	
	//Check whether the username is already taken
	if(check_User_Account_Exist($_POST["loginname"])){
		$url = "setup_new_account.php?accountType=".$_POST["accountType"];
		echo "<form method=\"post\" action=\"".$url."\">";
		echo "<p> The login name you have selected is already taken. <br> Go back and select another name. <br><br><input type=\"submit\" name=\"submit\" value=\"Back\">";
		echo "</p>";
		do_html_footer();
		exit;
	
	}
	
?>
		
<!--Display the information to confirm-->
<form name="form1" method="post" action="process_setup_account.php">
  <table width="100%" border="0" cellspacing="0" cellpadding="2">
    <tr> 
      <td colspan="2">Below is the information that you have entered for Reviewer 
        Account. Click Confirm to confirm your request.</td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="2"><strong>Login Information</strong></td>
    </tr>
    <tr> 
      <td colspan="2"><input type="hidden" name="accountType" value="<?php echo $_POST["accountType"]; ?>"></td>
    </tr>
    <tr> 
      <td width="25%">Login Name:</td>
      <td width="75%"><strong><?php echo $_POST["loginname"]; ?></strong></td>
      <input type="hidden" name="loginname" value="<?php echo $_POST["loginname"]; ?>">
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>First Name:</td>
      <td><?php echo $_POST["firstname"]; ?></td>
	  <input type="hidden" name="firstname" value="<?php echo $_POST["firstname"]; ?>">
    </tr>
	<?php if (!empty($_POST["middlename"])) { ?>
    <tr>
      <td>Middle Name:</td>
      <td><?php echo $_POST["middlename"]; ?></td>
	  <input type="hidden" name="middlename" value="<?php echo $_POST["middlename"]; ?>">
    </tr>
	<?php } ?>
    <tr>
      <td>Last Name:</td>
      <td><?php echo $_POST["lastname"]; ?></td>
	  <input type="hidden" name="lastname" value="<?php echo $_POST["lastname"]; ?>">
    </tr>
    <tr> 
      <td>Email Address:</td>
      <td><?php echo $_POST["email"]; ?></td>
      <input type="hidden" name="email" value="<?php echo $_POST["email"]; ?>">
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
<?php
$org = trim( $_POST["org"] );
if (!empty($org)) {
?>
    <tr> 
      <td colspan="2">The new reviewer will not have to register himself.</td>
    </tr>
    <tr> 
      <td>Organization:</td>
      <td><?php echo $_POST["org"]; ?></td>
      <input type="hidden" name="org" value="<?php echo $_POST["org"]; ?>">
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
<?php
}
?>
    <tr> 
      <td colspan="2"><input name="Submit" type="submit" id="Submit" value="Confirm"> 
        <input type="submit" name="Submit" value="Back"></td>
    </tr>
  </table>
</form>	
	
<?php do_html_footer();?>

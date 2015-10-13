<?php //////////// REVIEWER PHASE 2 ///////////////	

$php_root_path = ".." ;
$privilege_root_path = "/reviewer" ;

require_once("includes/include_all_fns.inc");
require_once("$php_root_path/includes/page_includes/page_fns.php");
session_start() ;
// extract ( $_SESSION , EXTR_REFS ) ;
$err_message = " Unable to process your request due to the following problems: <br>\n" ;
$header = "Registration" ;
$accepted_privilegeID_arr = array ( 2 => "" ) ;
$accepted_phaseID_arr = array ( 1 => "" , 2 => "" , 3 => "" , 4 => "" ) ;
authentication( $header , $accepted_privilegeID_arr , $accepted_phaseID_arr , $homepage , $php_root_path , $GLOBALS["DB_PREFIX"] , &$err_message ) ;		

global $homepage ;
$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	//Check whether the user has login to view this page.
	

if ( ( $reg = verify_Registration_Exist( &$err_message ) ) !== NULL )
{
	if ( $reg )
	{
		do_html_header("Personal Details Already Filled" , &$err_message );	
		$err_message .= " Your personal details are already filled. <br>\n" ;
		$err = $err_message . "<br><br> You may update them <a href='/conference/reviewer/edit_details.php'>here</a> instead." ;
		do_html_footer(&$err);
		exit;
	}
}
else
{
	$homepage->showmenu = 0 ;
	do_html_header("Fill in Personal Details Failed" , &$err_message );	
	$err_message .= " Cannot retrieve information from database. <br>\n" ;
	$err = $err_message . "<br><br> Try <a href='/conference/reviewer/fillin_reviewer_info.php'>again</a>?" ;
	do_html_footer(&$err);
	exit ;
}
	
$error_array = array() ;
$exempt_array = array ( "faxno" , "address2" , "middlename" , "emailHome" , "phonenoHome" ) ;
$err_message = " Unable to process your request due to the following problems: <br>\n" ;
$homepage->showmenu = 0 ;

check_form ( $_POST , $error_array , &$exempt_array ) ;

if ( count ( $error_array ) == 0 && count ( $_POST ) > 0 )
{
	$reg_result = register_Reviewer($_POST["firstname"],$_POST["middlename"],$_POST["lastname"],$_POST["org"],$_POST["address1"],$_POST["address2"],$_POST["city"],$_POST["state"],$_POST["postalcode"],
							$_POST["country"], $_POST["email"],$_POST["emailHome"],$_POST["phoneno"],$_POST["phonenoHome"],$_POST["faxno"] , &$err_message );

	if ( $reg_result )
	{	
		global $homepage ;		
		$homepage -> SetMetaHeader ( "<META HTTP-EQUIV=\"REFRESH\" CONTENT = \"3; URL=reviewer_home.php\">" ) ;		 
		do_html_header("Registration Successful" , &$err_message );		
		echo " <p>Your information was successfully recorded. <br> You should automatically be redirected to the reviewer page, otherwise you may click <a href='/conference/reviewer/reviewer_home.php'>here</a> to go now. </p>";
		do_html_footer( &$err_message );
		exit ;
	}
	else
	{
		 // otherwise provide link back, tell them to try again
		$homepage->showmenu = 0 ;
		do_html_header("Registration Failed" , &$err_message );
		$err = $err_message . "<br><br> Try <a href='/conference/reviewer/fillin_reviewer_info.php'>again</a>?" ; 
		do_html_footer( &$err );
		exit;
	}
}
else
{
	$homepage->showmenu = 0 ;
	do_html_header("Registration" , &$err_message );
}

//Get the reviewer information
$reviewerInfo = getMemberInfo($_SESSION["valid_user"]);
	
?>

<form name="frmRegister" method="post" action="fillin_reviewer_info.php">
<!--<form name="frmRegister" method="post" action="phpinfo.php">-->        
  <table width="100%" border="0" cellspacing="3" cellpadding="0">
    <tr> 
      <td colspan="2"><p>Any fields that have an astrix * are mandatory.<br>
          You must register before you can proceed.</p></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td width="17%"><strong>User Name</strong> </td>
      <td width="83%"><strong><?php echo $_SESSION["valid_user"] ; ?></strong>         
      </td>
    </tr>
    <tr> 
      <td height="24">First Name *</td>
      <td><input name="firstname" type="text" id="firstname" value="<?php echo isset($_POST["firstname"]) ? $_POST["firstname"] : $reviewerInfo -> FirstName ; ?>" size="20" maxlength="30"> 
        <?php echo "<font color=\"#FF0000\">" . $error_array["firstname"][0] . "</font>" ; ?>	
      </td>
    </tr>
    <tr> 
      <td>Middle Name</td>
      <td><input name="middlename" type="text" id="middlename" value="<?php echo isset($_POST["middlename"]) ? $_POST["middlename"] : $reviewerInfo -> MiddleName; ?>" size="20" maxlength="30"> 
        <?php echo "<font color=\"#FF0000\">" . $error_array["middlename"][0] . "</font>" ; ?>	
      </td>
    </tr>
    <tr> 
      <td>Last Name *</td>
      <td><input name="lastname" type="text" id="lastname" value="<?php echo isset($_POST["lastname"]) ? $_POST["lastname"] : $reviewerInfo -> LastName ; ?>" size="20" maxlength="30"> 
        <?php echo "<font color=\"#FF0000\">" . $error_array["lastname"][0] . "</font>" ; ?>	
      </td>
    </tr>
    <tr> 
      <td>Organisation *</td>
      <td><input name="org" type="text" id="org" value="<?php echo $_POST["org"]; ?>" size="30" maxlength="50"> 
        <?php echo "<font color=\"#FF0000\">" . $error_array["org"][0] . "</font>" ; ?>	
      </td>
    </tr>
    <tr> 
      <td valign="top">Address 1 * </td>
      <td><input name="address1" type="text" id="address1" value="<?php echo $_POST["address1"]; ?>" size="50" maxlength="100">	
        <?php echo "<font color=\"#FF0000\">" . $error_array["address1"][0] . "</font>" ; ?> 
      </td>
    </tr>
    <tr> 
      <td valign="top">Address 2</td>
      <td><input name="address2" type="text" id="address2" value="<?php echo $_POST["address2"]; ?>" size="50" maxlength="100">	
        <?php echo "<font color=\"#FF0000\">" . $error_array["address2"][0] . "</font>" ; ?> 
      </td>
    </tr>
    <tr> 
      <td valign="top">City *</td>
      <td><input name="city" type="text" id="city" value="<?php echo $_POST["city"] ; ?>" size="20" maxlength="30"> 
        <?php echo "<font color=\"#FF0000\">" . $error_array["city"][0] . "</font>" ; ?>	
      </td>
    </tr>
    <tr> 
      <td valign="top">State/Province *</td>
      <td><input name="state" type="text" id="state" value="<?php echo $_POST["state"] ; ?>" size="20" maxlength="30"> 
        <?php echo "<font color=\"#FF0000\">" . $error_array["state"][0] . "</font>" ; ?>	
      </td>
    </tr>
    <tr> 
      <td valign="top">Postal Code *</td>
      <td><input name="postalcode" type="text" id="postalcode" value="<?php echo $_POST["postalcode"] ; ?>" size="15" maxlength="15"> 
        <?php echo "<font color=\"#FF0000\">" . $error_array["postalcode"][0] . "</font>" ; ?>	
      </td>
    </tr>
    <tr> 
      <td height="28" valign="top">Country *</td>
      <td> <?php
			  	echo GetCountryDropDownBox( $_POST["country"] ) ;
			  	echo "<font color=\"#FF0000\">" . $error_array["country"][0] . "</font>" ; 
			  ?> </td>
    </tr>
    <tr> 
      <td valign="top">Email (work)*</td>
      <td><input name="email" type="text" id="email" value="<?php echo $_POST["email"] ; ?>" size="30" maxlength="50"> 
        <?php echo "<font color=\"#FF0000\">" . $error_array["email"][0] . "</font>" ; ?>	
      </td>
    </tr>
    <tr> 
      <td valign="top">Email (home)</td>
      <td><input name="emailHome" type="text" id="emailHome" value="<?php echo $_POST["emailHome"] ; ?>" size="30" maxlength="50"> 
        <?php echo "<font color=\"#FF0000\">" . $error_array["emailHome"][0] . "</font>" ; ?>	
      </td>
    </tr>
    <tr> 
      <td valign="top">Phone (work) *</td>
      <td><input name="phoneno" type="text" id="phoneno" value="<?php echo $_POST["phoneno"] ; ?>" size="25" maxlength="25"> 
        <?php echo "<font color=\"#FF0000\">" . $error_array["phoneno"][0] . "</font>" ; ?>	
      </td>
    </tr>
    <tr> 
      <td valign="top">Phone (home)</td>
      <td><input name="phonenoHome" type="text" id="phonenoHome" value="<?php echo $_POST["phonenoHome"] ; ?>" size="25" maxlength="25"> 
        <?php echo "<font color=\"#FF0000\">" . $error_array["phonenoHome"][0] . "</font>" ; ?>	
      </td>
    </tr>
    <tr> 
      <td valign="top">Fax No </td>
      <td><input name="faxno" type="text" id="faxno" value="<?php echo $_POST["faxno"] ; ?>" size="25" maxlength="25"> 
        <?php echo "<font color=\"#FF0000\">" . $error_array["faxno"][0] . "</font>" ; ?>	
      </td>
    </tr>
    <tr> 
      <td valign="top">&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td valign="top">&nbsp;</td>
      <td><input type="submit" name="submit" value="Submit"> &nbsp; <input type="reset" name="reset" value="Reset"></td>
    </tr>
  </table>
     </form>
<?php 

do_html_footer( &$err_message );

?>

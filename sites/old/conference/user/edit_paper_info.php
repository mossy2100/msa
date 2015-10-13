<?php
$php_root_path = ".." ;
require_once("$php_root_path/includes/include_all_fns.inc");
require_once("$php_root_path/includes/page_includes/page_fns.php");
session_start();
$err_message = " Unable to process your request due to the following problems: <br>\n" ;		
$header = "Edit Paper Details" ;
$accepted_privilegeID_arr = array ( 1 => "" ) ;
$accepted_phaseID_arr = array ( 1 => "" , 2 => "" , 3 => "" , 4 => "" ) ;
authentication( $header , $accepted_privilegeID_arr , $accepted_phaseID_arr , $homepage , $php_root_path , $GLOBALS["DB_PREFIX"] , &$err_message ) ;
$error_array = array() ;

//Retrieve the setting information
$settingInfo = get_Conference_Settings();
$trackStr = $settingInfo->TrackName; //Name for Track
$topicStr = $settingInfo->TopicName; //Name for Topic
$levelStr = $settingInfo->LevelName; //Name for Level

$exempt_array = array ( "email" , "middlename", "presenterbio", 
						"keyword1" , "keyword2" , "keyword3", "userfile"  ) ;

if ( count ( $_POST ) > 0 )
{	
	if ( $_POST["submit"] == "Update number of Authors" )
	{
		if ( isIntegerMoreThanZero ( $_POST["numauthors"] , &$error_array["numauthors"] ) || !empty ( $_POST["numauthors"] ) )
		{

		}
		else
		{
			if ( trim ( $_POST["numauthors"] ) == "" )
			{
				$error_array["numauthors"][0] = " This entry cannot be empty. <br>\n" ;
			}
		}
	}
	//Can only change category in Phase 1
	else if ( ($_SESSION["phase"]->phaseID == 1) && ($_POST["submit"] == "Update") ) 
	{						
		if ( $settingInfo -> SESUG ) {
            if ( !$_POST["level"] )
            {
                $error_array["level"][0] = "You must choose at least one $levelStr.<br>\n" ;
            }
        }
		if ( !$_POST["track"] )
		{
			$error_array["track"][0] = "You must choose a $trackStr.<br>\n" ;
		}		
		if ( !$_POST["category"] )
		{
			if (numCategories( &$err_message ) > 0) // allow conferences with only Tracks, but no Topics
				$error_array["category"][0] = "You must choose at least one $topicStr.<br>\n" ;
		}		
	
		$vars = array_merge ( $_POST , $_FILES ) ;
		check_form ( $vars , $error_array , &$exempt_array ) ;		
	}		
}

if ( count ( $error_array ) == 0 && count ( $_POST ) > 0 )
{
	
	if ( $_POST["submit"] == "Update number of Authors" )
	{	
		do_html_header("Edit Paper Details" , &$err_message );			
	}	
	else if ( $_POST["submit"] == "Update" )
	{
		//Submit to update the paper
		if ( $fileID = update_paper_details ( $_GET["paperid"] , 		$_POST["title"] , $_POST["abstract"] , $_POST["presenterbio"] , $_POST["numpages"] , 		$_FILES["userfile"]["tmp_name"] , $_FILES["userfile"]["name"] , 		$_FILES["userfile"]["size"] , $_FILES["userfile"]["type"] ,
		$_POST["firstname"] , $_POST["middlename"] , 		$_POST["lastname"] , $_POST["email"] , $_POST["level"] , $_POST["track"] , $_POST["category"] , $_POST["attended"] ,$_POST["presented"] ,$_POST["keyword1"] ,$_POST["keyword2"] ,$_POST["keyword3"] , &$err_message ) )
		{
			do_html_header("Paper Updating Successful..." , &$err_message );
			echo " The paper information has been updated <br><br> View your updated paper at <a href='/conference/user/view_paper_details.php?fileid=" . $fileID . "'>View Papers Details</a> page." ;
			do_html_footer( &$err_message );
			exit ;				
		}
		else
		{
			do_html_header("Paper Updating Failed..." , &$err_message );				
			$err_message .= "<br><br> Try <a href='/conference/user/edit_paper_info.php?paperid=" . $_GET["paperid"] . "'>again</a>?" ; 
		}			
	}
	else if($_POST["submit"] == "Withdraw")
	{		
		//Withdraw the paper here
		if ( withdraw_paper( $_GET["paperid"] , &$err_message ) )
		{
			do_html_header("Withdrawing Paper Successful" , &$err_message );
			echo " The paper has been withdrawn successfully.<br><br>\n" ;
				do_html_footer( &$err_message );
				exit ;					
		}
		else
		{
			do_html_header("Withdrawing Paper Failed..." , &$err_message );		
			$err_message .= "<br><br> <a href='/conference/user/edit_paper_info.php?paperid=" . $_GET["paperid"] . "'>Reload</a> this page.";
		}			
	}
	else if($_POST["submit"] == "Undo Changes")
	{		
		//Refresh the same page
		$str = "Location: edit_paper_info.php?paperid=" . $_GET["paperid"] ;
		header( $str ); /* Redirect browser */
		exit; /* Make sure that code below does not get executed when we redirect. */	
	}
}
else 
{
	if ( count ( $_POST ) == 0 )
	{
		$_SESSION["phase"]->set_edit_paper_info( $_GET["paperid"] , $_POST , &$err_message ) ;
	}
	do_html_header("Edit Paper Details" , &$err_message );		
}

$maxfilesize = $settingInfo->MaxUploadSize ;
?>

<form enctype="multipart/form-data" name="frmupload" method="post" action="edit_paper_info.php?paperid=<?php echo $_GET["paperid"] ?> ">
<!--  <form enctype="multipart/form-data" name="frmupload" method="post" action="phpinfo.php">  -->
  <table width="100%" border="0" cellspacing="0" cellpadding="3">
    <tr> 
      <td width="20%"><strong>Title:</strong></td>
      <td width="80%"> </td>
    </tr>
    <tr>
        <td colspan="2"><input name="title" type="text" value="<?php echo $_POST["title"] ?>" id="title" size="75" maxlength="255"> 
        <font color="#FF0000"><?php echo $error_array["title"][0] ?></font> </td>
    </tr>
    <tr> 
      <td><strong>Number of Pages:</strong></td>
      <td><input name="numpages" type="text" id="numpages" size="5" maxlength="4" value="<?php echo $_POST["numpages"] ; ?>"> 
        <?php echo "<font color=\"#FF0000\">" . $error_array["numpages"][0] . "</font>" ; ?> 
      </td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp; </td>
    </tr>
    <tr> 
      <td> <strong>Number of Authors: </strong></td>
      <td> <input name="numauthors" type="text" value="<?php echo $_POST["numauthors"] ; ?>" id="numauthorsid" size="3" maxlength="2"> 
        <input type="submit" name="submit" value="Update number of Authors"> <?php echo "<font color=\"#FF0000\">" . $error_array["numauthors"][0] . "</font>" ; ?>	
      </td>
    </tr>
    <tr> 
      <td colspan="2"> <?php 
	$firstname = $_POST["firstname"] ;
	$middlename = $_POST["middlename"] ;
	$lastname = $_POST["lastname"];
	$email = $_POST["email"];		

	$firstname_error_array = $error_array["firstname"] ;
	$middlename_error_array = $error_array["middlename"] ;
	$lastname_error_array = $error_array["lastname"] ;
	$email_error_array = $error_array["email"] ;

  	echo GenerateAuthorInputTable( $_POST["numauthors"] ) ;
?> </td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="2"><strong>File: </strong>(leave blank unless submitting revision) <font color="#FF0000">
      <?php $maxMbytes=$maxfilesize/pow(2,20); echo " (max file size = $maxMbytes Mb)" ; ?> 
        </font></td>
    </tr>
    <tr> 
      <td colspan="2"><input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $maxfilesize; ?>"> 
        <input name="userfile" type="file" size="50"> <font color="#FF0000"> 
        <?php 
		  $err_mess = ( $error_array["userfile"][0] ? $error_array["userfile"][0] : $error_array["userfile"][4] ) ;
		  $err_mess = ( $error_array["userfile"][3] ? $error_array["userfile"][3] : $error_array["userfile"][4] ) ;		  
		  echo $err_mess ;
		?>
		</font>
      </td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="2"><strong>Abstract:</strong><font color="#FF0000"><?php echo $error_array["abstract"][0] ?></font></td>
    </tr>
    <tr> 
      <td colspan="2"> <textarea name="abstract" cols="75" rows="10" id="textarea4"><?php echo $_POST["abstract"] ?></textarea></td>
    </tr>
    <tr> 
      <td colspan="2"><strong>Author/Presenter Biography:</strong><font color="#FF0000"><?php echo $error_array["presenterbio"][0] ?></font></td>
    </tr>
    <tr> 
      <td colspan="2"> <textarea name="presenterbio" cols="75" rows="10" id="textarea5"><?php echo $_POST["presenterbio"] ?></textarea></td>
    </tr>

	<tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <?php if ( $settingInfo -> SESUG ) { ?>
	<tr> 
      <td><strong><?php echo $attended ?> :</strong></td>
      <td> <input name="attended" type="text" value="<?php echo $_POST["attended"] ?>" id="attended" size="3" maxlength="3"> 
        <font color="#FF0000"><?php echo $error_array["attended"][0] ?></font> 
      </td>
	 </tr>

	 <tr>
	  <td><strong><?php echo $presented ?> :</strong></td>
      <td> <input name="presented" type="text" value="<?php echo $_POST["presented"] ?>" id="presented" size="3" maxlength="3"> 
        <font color="#FF0000"><?php echo $error_array["presented"][0] ?></font> 
      </td>
    </tr>
    <?php } ?>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="2"> 
        <?php
	
	$db = adodb_connect( &$err_message );
	
	if (!$db)
	{
    	$err_message .= " Could not connect to database server - please try later. <br>\n ";
		$err_message .= "<br><br> Try <a href='/conference/user/edit_paper_info.php?paperid=" . $_POST["paperid"] . "'>again</a>?";
	}
	else if ( $_SESSION["phase"]->phaseID == 1)  //Level, Track and Category radio boxes only enabled in phase 1
	{			
		if ($settingInfo -> SESUG) {
			echo "<strong>$levelStr(s):</strong><br>\n " ;
			echo "<font color=\"#FF0000\">" . $error_array["level"][0] . "</font>" ;
			
			if ( $result = GenerateSelectedCategoryInputTable( $_POST["level"] , &$err_message , 0 , "Level" ) )
			{
				echo $result ;
			}
			else
			{
				$err_message .= "<br><br> Try <a href='/conference/user/edit_paper_info.php?paperid=" . $_POST["paperid"] . "'>again</a>?" ;
			}
			echo "<br>" ;
		}
		echo "<strong>".$settingInfo -> TrackName.":</strong><br>\n " ;
		echo "<font color=\"#FF0000\">" . $error_array["track"][0] . "</font>" ;
		
		if ( $result = GenerateSelectedCategoryInputTable( $_POST["track"] , &$err_message , 0 , "Track" ) )
		{
			echo $result ;
		}
		else
		{
			$err_message .= "<br><br> Try <a href='/conference/user/edit_paper_info.php?paperid=" . $_POST["paperid"] . "'>again</a>?" ;
		}
		
		echo "<br>" ;
		if (numCategories( &$err_message ) > 0) // allow conferences with only Tracks, but no Topics
		{
			echo "<strong>".$settingInfo -> TopicName."(s):</strong><br>\n " ;
			echo "<font color=\"#FF0000\">" . $error_array["category"][0] . "</font>" ;	
			if ( $result = GenerateSelectedCategoryInputTable( $_POST["category"] , &$err_message ) )
			{
				echo $result ;
			}
			else
			{
				$err_message .= "<br><br> Try <a href='/conference/user/edit_paper_info.php?paperid=" . $_POST["paperid"] . "'>again</a>?" ;
			}
		}
	}
	else
	{
        if ($settingInfo -> SESUG) {
            echo "<strong>$levelstr:</strong><br>\n " ;
            echo "<font color=\"#FF0000\">" . $error_array["level"][0] . "</font>" ;
            
            if ( $result = GenerateSelectedCategoryInputTable( $_POST["level"] , &$err_message , 1 , "Level" ) )
            {
                echo $result ;
            }
            else
            {
                $err_message .= "<br><br> Try <a href='/conference/user/edit_paper_info.php?paperid=" . $_POST["paperid"] . "'>again</a>?" ;
            }
            echo "<br>" ;
		}
        echo "<strong>".$settingInfo -> TrackName.":</strong><br>\n " ;
		echo "<font color=\"#FF0000\">" . $error_array["track"][0] . "</font>" ;
		
		if ( $result = GenerateSelectedCategoryInputTable( $_POST["track"] , &$err_message , 1 , "Track" ) )
		{
			echo $result ;
		}
		else
		{
			$err_message .= "<br><br> Try <a href='/conference/user/edit_paper_info.php?paperid=" . $_POST["paperid"] . "'>again</a>?" ;
		}
		
		if (numCategories( &$err_message ) > 0) // allow conferences with only Tracks, but no Topics
		{
			echo "<br>" ;
			echo "<strong>".$settingInfo -> TopicName."(s):</strong><br>\n " ;
			echo "<font color=\"#FF0000\">" . $error_array["category"][0] . "</font>" ;		
			if ( $result = GenerateSelectedCategoryInputTable( $_POST["category"] , &$err_message , 1) )
			{
				echo $result ;
			}
			else
			{
				$err_message .= "<br><br> Try <a href='/conference/user/edit_paper_info.php?paperid=" . $_POST["paperid"] . "'>again</a>?" ;
			}
		}
	}
?>
      </td>
    </tr>
    <?php if ($settingInfo -> SESUG) { ?>
	<tr>
	  <td><strong><?php echo $keyword ?> :</strong></td>
      <td> <input name="keyword1" type="text" value="<?php echo $_POST["keyword1"] ?>" id="keyword1" size="20" maxlength="50"> 
        <font color="#FF0000"><?php echo $error_array["keyword1"][0] ?></font> 
      </td>
    </tr>
	<tr>
	  <td><strong><?php echo $keyword ?> :</strong></td>
      <td> <input name="keyword2" type="text" value="<?php echo $_POST["keyword2"] ?>" id="keyword2" size="20" maxlength="50"> 
        <font color="#FF0000"><?php echo $error_array["keyword2"][0] ?></font> 
      </td>
    </tr>
	<tr>
	  <td><strong><?php echo $keyword ?> :</strong></td>
      <td> <input name="keyword3" type="text" value="<?php echo $_POST["keyword3"] ?>" id="keyword3" size="20" maxlength="50"> 
        <font color="#FF0000"><?php echo $error_array["keyword3"][0] ?></font> 
      </td>
    </tr>
    <?php } ?>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="2"> <input type="submit" name="submit" value="Update"> &nbsp; 
        <input name="submit" type="submit" id="undo" value="Undo Changes"> &nbsp; 
        <?php		
		if ( $_SESSION["phase"]->phaseID == 1)
		{ 		
?>
        <input name="submit" type="submit" value="Withdraw"> 
        <?php		
		}
?>
      </td>
    </tr>
  </table>
</form>
<?php

do_html_footer( &$err_message );

?>

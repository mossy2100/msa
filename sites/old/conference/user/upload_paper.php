<?php 
	$php_root_path = ".." ;
	require_once("$php_root_path/includes/include_all_fns.inc");
  	require_once("$php_root_path/includes/page_includes/page_fns.php");	
	session_start();
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;		
	$header = "Upload Paper" ;
	$accepted_privilegeID_arr = array ( 1 => "" ) ;
	$accepted_phaseID_arr = array ( 1 => "" ) ;
	authentication( $header , $accepted_privilegeID_arr , $accepted_phaseID_arr , $homepage , $php_root_path , $GLOBALS["DB_PREFIX"] , &$err_message ) ;	
	
	$error_array = array() ;
    
    // Define a few page vars
    $settingInfo = get_Conference_Settings();
	$trackStr = $settingInfo->TrackName; //Name for Track
	$topicStr = $settingInfo->TopicName; //Name for Topic
	$levelStr = $settingInfo->LevelName; //Name for Level
    
    if ($settingInfo->AbstractOnlySubmissions || $settingInfo->SESUG) //Abstract submission only for SESUG
    {
		$exempt_array = array ( "email" , "middlename" , "presenterbio"  , "keyword1" , "keyword2" , "keyword3" , "userfile" ) ;
		$fullPaper = false;
	}
	else{
		$exempt_array = array ( "email" , "middlename", "presenterbio", "keyword1" , "keyword2" , "keyword3" ) ;
		$fullPaper = true;
	}

	if ( count ( $_POST ) > 0 )
	{	
		if ( $_POST["Submit"] == "Update Authors" )
		{
			if ( isIntegerMoreThanZero ( $_POST["numauthors"] , &$error_array["numauthors"] ) || !empty ( $_POST["numauthors"] ) )
			{

			}
			else if ( trim ( $_POST["numauthors"] ) == "" )
			{
				$error_array["numauthors"][0] = " This entry cannot be empty. <br>\n" ;

			}
		}
		else 
		{	
			if ( $settingInfo->SESUG && !$_POST["level"] )
			{
			$error_array["level"][0] = "You must choose at least one $levelStr.<br>\n" ;
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
		    //display( $vars ) ;
            
			check_form( $vars , $error_array , &$exempt_array ) ;
		}		
	}	
    
	if ( count ( $error_array ) == 0 && count ( $_POST ) > 0 )
	{
        if ( $_POST["Submit"] === "Submit" ) 
		{		
			//Everything is fine, then upload the file
			if ( $fileID = upload_file( $_POST["title"] , $_POST["abstract"] , $_POST["presenterbio"] , $_POST["numpages"] , $_FILES["userfile"]["tmp_name"] , $_FILES["userfile"]["name"] , $_FILES["userfile"]["size"] , $_FILES["userfile"]["type"] ,
								   $_POST["firstname"] , $_POST["middlename"] , $_POST["lastname"] , $_POST["email"] , $_POST["attended"] ,$_POST["presented"] ,$_POST["keyword1"] ,$_POST["keyword2"] ,$_POST["keyword3"] ,$_POST["level"] , $_POST["track"] , $_POST["category"] , &$err_message ) )
			{		
				do_html_header("Successful Uploading..." , &$err_message );
				echo " The file is uploaded successfully to the database. <br><br> View your new paper at <a href='/conference/user/view_paper_details.php?fileid=" .  $fileID . "'>View Paper Details</a> page. <br>" ;
				do_html_footer( &$err_message );
				exit ;				
			}
			else
			{
				do_html_header("Problem Uploading..." , &$err_message );
				$err_message .= "<br><br> Go to the <a href='/conference/user/upload_paper.php'>Upload Paper</a> page. " ;
			}
		}
		else
		{
			do_html_header("Upload Paper" , &$err_message ) ;
		}
	}
	else 
	{
		if ( count ( $_POST ) == 0 )
		{	
//			echo "<br>\n POST = 0 <br>\n" ;

		}
		do_html_header("Upload Paper" , &$err_message ) ;		
	}
	
	$maxfilesize = $settingInfo->MaxUploadSize ;
?>

<form enctype="multipart/form-data" name="frmupload" method="post" action="upload_paper.php"> 
<!-- <form enctype="multipart/form-data" name="frmupload" method="post" action="phpinfo.php"> -->
(* indicates mandatory field)<br><br>
	
  <table width="100%" border="0" cellpadding="3" cellspacing="0">
    <tr> 
      <td width="20%"><strong>Title *:</strong></td>
      <td width="80%"> </td>
    </tr>
    <tr>
        <td colspan="2"><input name="title" type="text" value="<?php echo stripslashes($_POST["title"]) ?>" id="title" size="75" maxlength="255"> 
        <font color="#FF0000"><?php echo $error_array["title"][0] ?></font> </td>
    </tr>
    <tr> 
      <td><strong>Number of Pages *:</strong></td>
      <td> <input name="numpages" type="text" value="<?php echo $_POST["numpages"] ?>" id="numpages" size="3" maxlength="3"> 
        <font color="#FF0000"><?php echo $error_array["numpages"][0] ?></font> 
      </td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td> <font color="#FF0000">&nbsp; </font> </td>
    </tr>
    <tr> 
      <td><strong>Number of Authors *:</strong></td>
        <?php 	// show at least one author field
        if (isset( $_POST["numauthors"] )){			
	  		$numauthors =  $_POST["numauthors"];
	  	}
	  	else{
	  		$numauthors =  1 ; 
	  	}
	  	?>
      <td><input name="numauthors" type="text" value="<?php echo $numauthors ?>" id="numauthors" size="3" maxlength="2"> 
        <input type="submit" name="Submit" value="Update Authors"> <font color="#FF0000"><?php echo $error_array["numauthors"][0] ?></font></td>
    </tr>
    <tr> 
      <td colspan="2"> <?php 
		$firstname = $_POST["firstname"] ;
		$middlename = $_POST["middlename"] ;
		$lastname = $_POST["lastname"] ;
		$email = $_POST["email"] ;

		$firstname_error_array = $error_array["firstname"] ;
		$middlename_error_array = $error_array["middlename"] ;
		$lastname_error_array = $error_array["lastname"] ;
		$email_error_array = $error_array["email"] ;
						
	  	echo GenerateAuthorInputTable($numauthors) ;
	  ?> </td>
    </tr>
    <tr> 
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="2"><strong>File<?php if ($fullPaper) echo " *"; ?>:</strong> 
      <?php $maxMbytes=$maxfilesize/pow(2,20); echo " (maximum file size is $maxMbytes Mb)" ; ?> 
        </font></td>
    </tr>
    <tr> 
      <td colspan="2"><input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $maxfilesize; ?>"> 
        <input name="userfile" type="file" size="50"> <font color="#FF0000"> 
        <?php 
		  $err_mess = ( $error_array["userfile"][0] ? $error_array["userfile"][0] : $error_array["userfile"][4] ) ;
		  echo $err_mess ;
		?>
        </font></td>
    </tr>
    <tr> 
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="2"><strong>Abstract *:</strong><font color="#FF0000"><?php echo $error_array["abstract"][0] ?></font></td>
    </tr>
    <tr> 
      <td colspan="2"> <textarea name="abstract" cols="75" rows="10" id="textarea4"><?php echo stripslashes($_POST["abstract"]) ?></textarea></td>
    </tr>
    <tr> 
      <td colspan="2"><strong>Author/Presenter Biography:</strong><font color="#FF0000"><?php echo $error_array["presenterbio"][0] ?></font></td>
    </tr>
    <tr> 
      <td colspan="2"> <textarea name="presenterbio" cols="75" rows="10" id="textarea5"><?php echo stripslashes($_POST["presenterbio"]) ?></textarea></td>
    </tr>

	<tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <?php if ($settingInfo->SESUG) { ?>
	<tr> 
      <td><strong><?php echo $attended ?> *:</strong></td>
      <td> <input name="attended" type="text" value="<?php echo $_POST["attended"] ?>" id="attended" size="3" maxlength="3"> 
        <font color="#FF0000"><?php echo $error_array["attended"][0] ?></font> 
      </td>
	 </tr>

	 <tr>
	  <td><strong><?php echo $presented ?> *:</strong></td>
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
    	$err_message .= "Could not connect to database server - please try later. <br>\n ";
		$err_message .= "<br><br> Try <a href='/conference/user/upload_paper.php'>again</a>?" ;
		exit ;
	}	
	if ($settingInfo->SESUG) {
	echo "<strong>$levelStr (select all that apply) *:</strong>\n" ;	
	echo "<font color=\"#FF0000\">" . $error_array["level"][0] . "</font>" ;
		if ( $result = GenerateSelectedCategoryInputTable ( $_POST["level"] , &$err_message, 0 , "Level" ) )
		{
			echo $result ;
		}
		else
		{
			$err_message .= "<br><br> Try <a href='/conference/user/upload_paper.php'>again</a>?" ;
		}
	echo "<br>" ;
	}
    echo "<strong>$trackStr *:</strong>\n " ;
	echo "<font color=\"#FF0000\">" . $error_array["track"][0] . "</font>" ;
		
		if ( $result = GenerateSelectedCategoryInputTable( $_POST["track"] , &$err_message , 0 , "Track" ) )
		{
			echo $result ;
		}
		else
		{
			$err_message .= "<br><br> Try <a href='upload_paper_info.php?paperid=" . $_POST["paperid"] . "'>again</a>?" ;
		}
		
	if (numCategories( &$err_message ) > 0) // allow conferences with only Tracks, but no Topics
	{
		echo "<br>" ;
		echo "<STRONG>$topicStr(s) *:</STRONG>\n" ;	
		echo "<font color=\"#FF0000\">" . $error_array["category"][0] . "</font>" ;
		if ( $result = GenerateSelectedCategoryInputTable ( $_POST["category"] , &$err_message ) )
		{
			echo $result ;
		}
		else
		{
			$err_message .= "<br><br> Try <a href='/conference/user/upload_paper.php'>again</a>?" ;
		}
	}

?>
      </td>
    </tr>
    <?php if ($settingInfo->SESUG) { ?> 
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
      <td>&nbsp;</td>
      <td><input type="submit" name="Submit" value="Submit">

</td>
    </tr>
  </table>
</form>

<?php

do_html_footer( &$err_message );

?>

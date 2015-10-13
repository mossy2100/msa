<?php //////////// REVIEWER PHASE 3 ///////////////
	$php_root_path = ".." ;
	$privilege_root_path = "/reviewer" ;
//	extract ( $_GET , EXTR_REFS ) ;
//	extract ( $_POST , EXTR_REFS ) ;

	require_once("includes/include_all_fns.inc");
	session_start() ;

	// Define a few page vars
    $settingInfo = get_Conference_Settings();

//	extract ( $_SESSION , EXTR_REFS ) ;
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	$header = "Edit Review Form" ;
	$accepted_privilegeID_arr = array ( 2 => "" ) ;
	$accepted_phaseID_arr = array ( 3 => "" ) ;
	authentication( $header , $accepted_privilegeID_arr , $accepted_phaseID_arr , $homepage , $php_root_path , $GLOBALS["DB_PREFIX"] , &$err_message ) ;

	if ( count ( $_POST ) > 0 )
	{
		if ( $_POST["Submit"] == "Reset" )
		{
			header("Location: edit_review_form.php?paperid=" . $_POST["paperid"] ) ;
			exit ;
		}
		$error_array = array() ;
		$exempt_array = array ( "commentfile", "commentsadmin" ) ;

		$vars = array_merge ( $_POST , $_FILES ) ;
		check_form ( $vars , $error_array , &$exempt_array ) ;
		if($_POST["submitType"] == "file" && !$error_array["commentfile"] )
		{
			//Read the file contents and display
			$_POST["comments"] = addslashes(fread(fopen($_FILES["commentfile"]["tmp_name"],"r"),filesize($_FILES["commentfile"]["tmp_name"])));
		}
	}
//	display ( $error_array ) ;
	if ( count ( $error_array ) == 0 && count ( $_POST ) > 0 )
	{
		if ( $_POST["Submit"] == "Submit" )
		{
			//Process the normal form submission here
			if ( $returnResult = insert_review($_POST["paperid"],$_POST["appropriateness"],$_POST["originality"],$_POST["tech_strength"],$_POST["presentation"],$_POST["overall"],addslashes($_POST["comments"]),addslashes($_POST["commentsadmin"]) , &$err_message ) )
			{
				do_html_header("Edit Review Form Successful" , &$err_message );
				echo $returnResult ;
				do_html_footer(&$err_message);
				exit;
			}
			else
			{
				do_html_header("Edit Review Form Failed" , &$err_message );
				$err_message .= " Could not execute \"insert_review\" in \"review_form.php\". <br>\n" ;
				$err_message .= "<br><br> Try <a href='/conference/reviewer/review_form.php?paperid=".$_POST["paperid"]."'>again</a>?" ;
			}
		}
		else
		{
			do_html_header("Edit Review Form" , &$err_message );
		}
	}
	else
	{
		if ( count ( $_POST ) == 0 )
		{
			$_POST["paperid"] = $_GET["paperid"] ;
			//Call the function to retrieve the Review of the paper
			if ( ( $reviewInfo = get_review($_POST["paperid"] , &$err_message ) ) === false )
			{
				do_html_header("Edit Review Form Failed" , &$err_message );
				$err_message .= " Could not execute \"get_review\" in \"edit_review_form.php\". <br>\n" ;
				$err = $err_message . "<br><br> Try <a href='/conference/reviewer/edit_review_form.php?paperid=".$_GET["paperid"]."'>again</a>?" ;
				do_html_footer(&$err);
				exit;
			}

			//Assign the values to the variables
			$_POST["appropriateness"] = $reviewInfo -> AppropriatenessToConference ;
			$_POST["originality"] = $reviewInfo -> Originality;
			$_POST["tech_strength"] = $reviewInfo -> TechnicalStrength;
			$_POST["presentation"] = $reviewInfo -> Presentation;
			$_POST["overall"] = $reviewInfo -> OverallEvaluation ;
			$_POST["comments"] = stripslashes ( $reviewInfo -> Comments ) ;
			$_POST["commentsadmin"] = stripslashes ( $reviewInfo -> CommentsAdmin ) ;
		}
		do_html_header("Edit Review Form" , &$err_message );
	}

	//Retrieve the paper info
	if ( ( $paperInfo = get_paper_info( $_POST["paperid"] , &$err_message ) ) === false )
	{
		do_html_header("Edit Review Form Failed" , &$err_message );
		$err_message .= " Cannot retrieve information from database. <br>\n" ;
		$err_message .= "<br><br> Try <a href='/conference/reviewer/review_form.php?paperid=".$_POST["paperid"]."'>again</a>?" ;
		do_html_footer(&$err_message);
		exit;
	}
?>
<script language="JavaScript">
function loadFileContent(){

	//Change the form submition type
	document.frmReview.submitType.value = "file";

	//Retrieve the value of file path
	var str = document.frmReview.commentfile.value;
	//Extract the file type to vefiry
	var fileType = str.substring(str.length - 3);

	//Check whether the file is text file
	if(fileType != "txt"){
		alert ("Sorry,You can select only plain text file.");
		document.frmReview.commentfile.focus();
		document.frmReview.commentfile.select();
	}
	else{
		//alert ("File Type is valid");
		document.frmReview.submit();
	}

}

</script>
<form enctype="multipart/form-data" action="edit_review_form.php" method="post" name="frmReview">
  <table width="100%" border="0" cellspacing="0" cellpadding="1">
    <tr> &nbsp;
    </tr>
    <tr>
      <td align="center"><h3>#<?php echo $paperInfo -> PaperID; ?>&nbsp;&nbsp;<?php echo stripslashes($paperInfo -> Title); ?></h3></td>
    </tr>
<?php //if DoubleBindReview is set to false The author will not show
	if(!$settingInfo -> DoubleBlindReview)
	{
	?>
    <tr>
      <td align="center"><h4>Authors:</strong><?php
    if ( $authors = retrieve_authors( $paperInfo -> PaperID , &$err_message ) )
	{
		echo $authors ;
	}
	else
	{
		echo " <font color=\"#FF0000\"> Could not read author table. Try <a href='/conference/reviewer/edit_review_form.php?paperid=".$_GET["paperid"]."'>again</a>?</font>" ;
	}
	   ?></h4></td>
    </tr>
    <?php
    }
    ?>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><strong>Reviewer</strong> : <?php echo $_SESSION["valid_user"] ; ?></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><hr></td>
    </tr>
    <tr>
      <td><strong>Numerical Ranking</strong></td>
    </tr>
    <tr>
      <td><table width="90%" border="1" cellpadding="0" cellspacing="2">
          <tr>
            <td rowspan="2">&nbsp;<strong>Ranking Criteria</strong></td>
            <td align="center">&nbsp;<strong>Strong Reject</strong></td>
            <td align="center">&nbsp;<strong>Weak Reject</strong></td>
            <td align="center">&nbsp;<strong>Marginal</strong></td>
            <td align="center">&nbsp;<strong>Weak Accept</strong></td>
            <td align="center">&nbsp;<strong>Strong Accept</strong></td>
          </tr>
          <tr>
            <td align="center">1</td>
            <td align="center">2</td>
            <td align="center">3</td>
            <td align="center">4</td>
            <td align="center">5</td>
          </tr>
            <tr>
            <td>Appropriateness to the Conference</td>
            <td align="center"><input name="appropriateness" type="radio" value="1" <?php if ($_POST["appropriateness"] == 1) echo "checked";?> ></td>
            <td align="center"><input type="radio" name="appropriateness" value="2" <?php if ($_POST["appropriateness"] == 2) echo "checked";?>></td>
            <td align="center"><input type="radio" name="appropriateness" value="3" <?php if ($_POST["appropriateness"] == 3) echo "checked";?>></td>
            <td align="center"><input type="radio" name="appropriateness" value="4" <?php if ($_POST["appropriateness"] == 4) echo "checked";?>></td>
            <td align="center"><input type="radio" name="appropriateness" value="5" <?php if ($_POST["appropriateness"] == 5) echo "checked";?>></td>
          </tr>
          <tr>
            <td>Originality</td>
            <td align="center"><input name="originality" type="radio" value="1" <?php if ($_POST["originality"] == 1) echo "checked";?>></td>
            <td align="center"><input type="radio" name="originality" value="2" <?php if ($_POST["originality"] == 2) echo "checked";?>></td>
            <td align="center"><input type="radio" name="originality" value="3" <?php if ($_POST["originality"] == 3) echo "checked";?>></td>
            <td align="center"><input type="radio" name="originality" value="4" <?php if ($_POST["originality"] == 4) echo "checked";?>></td>
            <td align="center"><input type="radio" name="originality" value="5" <?php if ($_POST["originality"] == 5) echo "checked";?>></td>
          </tr>
          <tr>
            <td>Technical Strength</td>
            <td align="center"><input name="tech_strength" type="radio" value="1" <?php if ($_POST["tech_strength"] == 1) echo "checked";?>></td>
            <td align="center"><input type="radio" name="tech_strength" value="2" <?php if ($_POST["tech_strength"] == 2) echo "checked";?>></td>
            <td align="center"><input type="radio" name="tech_strength" value="3" <?php if ($_POST["tech_strength"] == 3) echo "checked";?>></td>
            <td align="center"><input type="radio" name="tech_strength" value="4" <?php if ($_POST["tech_strength"] == 4) echo "checked";?>></td>
            <td align="center"><input type="radio" name="tech_strength" value="5" <?php if ($_POST["tech_strength"] == 5) echo "checked";?>></td>
          </tr>
          <tr>
            <td>Presentation</td>
            <td align="center"><input name="presentation" type="radio" value="1" <?php if ($_POST["presentation"] == 1) echo "checked";?>></td>
            <td align="center"><input type="radio" name="presentation" value="2" <?php if ($_POST["presentation"] == 2) echo "checked";?>></td>
            <td align="center"><input type="radio" name="presentation" value="3" <?php if ($_POST["presentation"] == 3) echo "checked";?>></td>
            <td align="center"><input type="radio" name="presentation" value="4" <?php if ($_POST["presentation"] == 4) echo "checked";?>></td>
            <td align="center"><input type="radio" name="presentation" value="5" <?php if ($_POST["presentation"] == 5) echo "checked";?>></td>
          </tr>
          <tr>
            <td>Overall Evaluation</td>
            <td align="center"><input name="overall" type="radio" value="1" <?php if ($_POST["overall"] == 1) echo "checked";?>></td>
            <td align="center"><input type="radio" name="overall" value="2" <?php if ($_POST["overall"] == 2) echo "checked";?>></td>
            <td align="center"><input type="radio" name="overall" value="3" <?php if ($_POST["overall"] == 3) echo "checked";?>></td>
            <td align="center"><input type="radio" name="overall" value="4" <?php if ($_POST["overall"] == 4) echo "checked";?>></td>
            <td align="center"><input type="radio" name="overall" value="5" <?php if ($_POST["overall"] == 5) echo "checked";?>></td>
          </tr>
        </table> </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><hr></td>
    </tr>
    <tr>
      <td><p><strong>Comments</strong></p>
        <p>Please supply detailed comments to back up your rankings (the more
          detailed your comments, the better). To do this, pick one of the following
          two options: </p>
        <ul>
          <li>You can pre-edit your comments in a separate text file, and then
            just upload the contents into the textbox below. (The file should contain plain ascii text,
            i.e., a .txt file, NOT .doc, .ps, .pdf, .wp, .tex, etc.). </li>
          <li>Or you can type (or paste) your comments directly in the textbox.</li>
        </ul></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><strong>Upload Comments from File</strong><input type="hidden" name="submitType" value="normal"> <input type="file" name="commentfile" onChange="loadFileContent()" ><font color="#FF0000"><?php echo ( $error_array["commentfile"][0] ? $error_array["commentfile"][0] : $error_array["commentfile"][4] ) ; ?></font></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><p><strong>Comments for Authors *</strong></p>
        <p>
          <textarea name="comments" cols="100" rows="15">
<?php
echo stripslashes ( $_POST["comments"] ) ;
?>
</textarea>
        </p></td>
    </tr>
            <tr>
		      <td>&nbsp;</td>
	    </tr>
	    <tr>
		      <td><p><strong>Comments for Chair (not seen by authors)</strong><font color="#FF0000"><?php echo $error_array["commentsadmin"][0] ?></font></p>
	        <p>
	          <textarea name="commentsadmin" cols="80" rows="7"><?php
		echo stripslashes ( $_POST["commentsadmin"] ) ;
			  ?></textarea>
	        </p></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>
	  <input type="hidden" name="paperid" value="<?php echo $paperInfo -> PaperID; ?>">
	  <input type="submit" name="Submit" value="Submit"> <input type="submit" name="Submit" value="Reset"></td>
    </tr>
  </table>
</form>
<?php

do_html_footer( &$err_message );

?>

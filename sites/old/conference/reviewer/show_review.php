<?php //////////// REVIEWER PHASE 3 ///////////////
	$php_root_path = ".." ;
	$privilege_root_path = "/reviewer" ;
//	extract ( $_GET , EXTR_REFS ) ;
//	extract ( $_POST , EXTR_REFS ) ;
//	extract ( $_FILES , EXTR_REFS ) ;

	require_once("includes/include_all_fns.inc");
	require_once("$php_root_path/includes/page_includes/page_fns.php"); // numCategories()
	session_start() ;

	// Define a few page vars
    $settingInfo = get_Conference_Settings();

//	extract ( $_SESSION , EXTR_REFS ) ;
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	$header = "Show Review" ;
	$accepted_privilegeID_arr = array ( 2 => "" ) ;
	$accepted_phaseID_arr = array ( 3 => "" ) ;
	authentication( $header , $accepted_privilegeID_arr , $accepted_phaseID_arr , $homepage , $php_root_path , $GLOBALS["DB_PREFIX"] , &$err_message ) ;

	//Get the paper information
	if ( ( $paperInfo = get_paper_info($_GET["paperid"] , &$err_message ) ) === false )
	{
		do_html_header("Show Review Failed" , &$err_message );
		$err_message .= " Cannot retrieve information from database. <br>\n" ;
		$err = $err_message . "<br><br> Try <a href='/conference/reviewer/show_review.php?paperid=" .$_GET["paperid"]."'>again</a>?" ;
		do_html_footer(&$err);
		exit;
	}

	//Get the lastest file of the paper
	if ( ( $FileIDData = get_latestFile($_GET["paperid"] , &$err_message ) ) === false )
	{
		do_html_header("Show Review Failed" , &$err_message );
		$err_message .= " Could not execute \"get_latestFile\" in \"show_review.php\". <br>\n" ;
		$err = $err_message . "<br><br> Try <a href='/conference/reviewer/show_review.php?paperid=".$_GET["paperid"]."'>again</a>?" ;
		do_html_footer(&$err);
		exit;
	}

	//Call the function to retrieve the Review of the paper
	if ( ( $reviewInfo = get_review($_GET["paperid"] , &$err_message ) ) === false )
	{
		do_html_header("Show Review Failed" , &$err_message );
		$err_message .= " Could not execute \"get_review\" in \"show_review.php\". <br>\n" ;
		$err = $err_message . "<br><br> Try <a href='/conference/reviewer/show_review.php?paperid=".$_GET["paperid"]."'>again</a>?" ;
		do_html_footer(&$err);
		exit;
	}

	//Assign the values to the variables
	$appropriateness = $reviewInfo -> AppropriatenessToConference;
	$originality = $reviewInfo -> Originality;
	$tech_strength = $reviewInfo -> TechnicalStrength;
	$presentation = $reviewInfo -> Presentation;
	$overall = $reviewInfo -> OverallEvaluation;

	do_html_header("Review" , &$err_message );
?>
<table width="100%" border="0" cellspacing="0" cellpadding="1">
  <tr>
    <td><h4><?php echo stripslashes($paperInfo -> Title); ?></h4></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td><strong>PaperID: </strong> <?php echo $paperInfo -> PaperID; ?></td>
  </tr>
<?php //if DoubleBindReview is set to false The author will not show
	if(!$settingInfo -> DoubleBlindReview)
	{
  ?>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td><strong>Authors: </strong><?php if ( $authors = retrieve_authors( $paperInfo -> PaperID , &$err_message ) )
	{
		echo $authors ;
	}
	else
	{
		echo " <font color=\"#FF0000\"> Could not read author table. Try <a href='/conference/reviewer/show_review.php?paperid=".$_GET["paperid"]."'>again</a>?</font>" ;
	}
	?></td>
  </tr>
  <?php
  }
  ?>
  <tr>
    <td>&nbsp;</td>
  </tr>
<?php if (numCategories( &$err_message ) > 0) { ?>
  <tr>
    <td><p><strong>Keywords: </strong><?php if ( $catcomsep = getSelectedCategoryCommaSeparated($paperInfo->PaperID , &$err_message ) )
	{
		echo $catcomsep ;
	}
	else
	{
		$err_message .= "Could not execute \"getSelectedCategoryCommaSeperated\" in \"show_review.php\". <br>\n" ;
		echo " <font color=\"#FF0000\"> Could not read Paper Category table. Try <a href='/conference/reviewer/show_review.php?paperid=".$_GET["paperid"]."'>again</a>?</font>" ;
	}
	?></p>
      </td>
  </tr>
<?php } ?>
  <tr>
    <td><hr></td>
  </tr>
  <tr>
    <td><p><strong>Ranking Criteria<br>
        </strong></p>
      <table width="70%" border="1" cellspacing="2" cellpadding="1">
        <tr>
          <td width="70%"><strong>Name</strong></td>
          <td width="30%"><strong>Rank</strong></td>
        </tr>
        <tr>
          <td>Appropriateness to the Conference:</td>
          <td><?php echo get_ranking($appropriateness); ?></td>
        </tr>
        <tr>
          <td>Originality:</td>
          <td><?php echo get_ranking($originality); ?></td>
        </tr>
        <tr>
          <td>Technical Strength:</td>
          <td><?php echo get_ranking($tech_strength); ?></td>
        </tr>
        <tr>
          <td>Presentation:</td>
          <td><?php echo get_ranking($presentation); ?></td>
        </tr>
        <tr>
          <td>Overall Evaluation:</td>
          <td><?php echo get_ranking($overall); ?></td>
        </tr>
      </table></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td><p><strong>Comments for Authors:<br>
        </strong><?php if ( ( $comment = get_comment($paperInfo -> PaperID , $reviewInfo->MemberName , &$err_message ) ) === false )
		{
			$err_message .= "Could not execute \"getSelectedCategoryCommaSeperated\" in \"show_review.php\". <br>\n" ;
			echo " <font color=\"#FF0000\"> Could not retrieve Comments. Try <a href='/conference/reviewer/show_review.php?paperid=".$_GET["paperid"]."'>again</a>?</font>" ;
		}
		else
		{
		 	echo nl2br( ereg_replace( "  " , "&nbsp;&nbsp;" , stripslashes( $comment )));
		}
		 ?></p></td>
  </tr>
  <?php if(stripslashes(get_commentsadmin($paperInfo -> PaperID,$reviewInfo -> MemberName , &$err_message ))!=""){?>
  <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><p><strong>Comments for Chair (not sent to authors):<br>
          </strong><?php if ( ( $comment = get_commentsadmin($paperInfo -> PaperID , $reviewInfo->MemberName , &$err_message ) ) === false )
  		{
  			$err_message .= "Could not execute \"getSelectedCategoryCommaSeperated\" in \"show_review.php\". <br>\n" ;
  			echo " <font color=\"#FF0000\"> Could not retrieve Comments. Try <a href='/conference/reviewer/show_review.php?paperid=".$_GET["paperid"]."'>again</a>?</font>" ;
  		}
  		else
  		{
  		 	echo nl2br( ereg_replace( "  " , "&nbsp;&nbsp;" , stripslashes( $comment )));
  		}
  		 ?></p></td>
  </tr>
  <?php } ?>
  <tr>
    <td>&nbsp;</td>
  </tr>
</table>
<br>
<hr>
<strong><a href=view_assigned_papers.php?sort=<?php echo $_SESSION["sort"]; ?>&showing=<?php echo $_SESSION["showing"]; ?>>Back</a></strong>
<?php do_html_footer(&$err_message);

?>

<?php //////////// REVIEWER PHASE 2 ///////////////
//////////// REVIEWER PHASE 3 ///////////////

	$php_root_path = ".." ;
	$privilege_root_path = "/reviewer" ;
//	extract ( $_GET , EXTR_REFS ) ;
//	extract ( $_POST , EXTR_REFS ) ;
//	extract ( $_FILES , EXTR_REFS ) ;

	require_once("includes/include_all_fns.inc");
	require_once("$php_root_path/includes/page_includes/page_fns.php");	// only for numCategories()
//	session_cache_limiter('private') ;
	session_start() ;
	header("Cache-control: private");
//	extract ( $_SESSION , EXTR_REFS ) ;
	// Define a few page vars
    $settingInfo = get_Conference_Settings();
	$trackStr = $settingInfo->TrackName; //Name for Track
	$topicStr = $settingInfo->TopicName; //Name for Topic
	$levelStr = $settingInfo->LevelName; //Name for Level

	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	$header = "View Abstract" ;
	$accepted_privilegeID_arr = array ( 2 => "" ) ;
	$accepted_phaseID_arr = array ( 2 => "" , 3 => "" ) ;
	authentication( $header , $accepted_privilegeID_arr , $accepted_phaseID_arr , $homepage , $php_root_path , $GLOBALS["DB_PREFIX"] , &$err_message ) ;

	//Get the paper information
	if ( ( $paperInfo = get_paper_info($_GET["paperid"] , &$err_message ) ) === false )
	{
		do_html_header("View Abstract Failed" , &$err_message );
		$err_message .= " Cannot retrieve information from database. <br>\n" ;
		$err_message .= "<br><br> Try <a href='/conference/reviewer/view_abstract.php?paperid=".$_GET["paperid"]."'>again</a>?" ;
		do_html_footer(&$err_message);
		exit;
	}

	//Get the lastest file of the paper
	if ( ( $FileIDData = get_latestFile($_GET["paperid"] , &$err_message ) ) === false )
	{
		do_html_header("View Abstract Failed" , &$err_message );
		$err_message .= " Could not execute \"get_latestFile\" in \"view_abstract.php\". <br>\n" ;
		$err_message .= "<br><br> Try <a href='/conference/reviewer/view_abstract.php?paperid=".$_GET["paperid"]."'>again</a>?" ;
		do_html_footer(&$err_message);
		exit;
	}

	if ( $_POST["showing"] )
	{
		$_GET["showing"] = $_POST["showing"] ;
	}
	if ( $_POST["sort"] )
	{
		$_GET["sort"] = $_POST["sort"] ;
	}
	if ( !$_POST["referer"] )
	{
//		echo $_SERVER["HTTP_REFERER"] ;
		$_POST["referer"] = $_SERVER["HTTP_REFERER"] ;
	}

	$papers_str = "" ;
	if ( $_POST["papers"] )
	{
		foreach ( $_POST["papers"] as $some => $postpaperid )
		{
			$papers_str .= ( "<input type=\"hidden\" value=\"" . $postpaperid . "\" name=\"papers[]\">\n" ) ;
		}
	}

	$storepapers_str = "" ;
	if ( $_POST["storepapers"] )
	{
		foreach ( $_POST["storepapers"] as $some => $id )
		{
			$storepapers_str .= ( "<input type=\"hidden\" value=\"" . $id . "\" name=\"storepapers[]\">\n" ) ;
		}
	}

	$settingInfo = get_Conference_Settings();

	do_html_header("View Abstract" , &$err_message );
?>
<br>
<form name="frmPaper" method="post" action="<?php echo $_POST["referer"] ; ?>">
<table width="100%" border="0" cellspacing="0" cellpadding="1">
  <tr>
    <td align="center"><h3>#<?php echo $paperInfo -> PaperID; ?>&nbsp;&nbsp;<?php echo stripslashes($paperInfo -> Title); ?></h3></td>
  </tr>

<?php //if DoubleBindReview is set to false The author will not show
	if(!$settingInfo -> DoubleBlindReview)
	{
	?>
	<tr>
    <td align="center"><h4>
    <?php
		if ( $authors = retrieve_authors( $paperInfo -> PaperID , &$err_message ) )
		{
			echo $authors;
		}
		else
		{
			echo " <font color=\"#FF0000\"> Could not read author table. Try <a href='/conference/reviewer/view_abstract.php?paperid=".$_GET["paperid"]."'>again</a>?</font>" ;
		}
	?>
	</h4>
	 </td>
  </tr>
  <tr>
      <td>&nbsp;</td>
  </tr>
  <?php
	}

?>

  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td><hr></td>
  </tr>
  <tr>
    <td><p><strong>Abstract:</strong></p>
      <p><?php echo nl2br( ereg_replace( "  " , "&nbsp;&nbsp;" , stripslashes( $paperInfo -> PaperAbstract )));
	  ?></p></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td><p><strong><?php echo "$trackStr:" ?></strong>
    <?php if ( $catcomsep = GetSelectedTrackText( $paperInfo -> PaperID , &$err_message ) )
	{
		echo $catcomsep ;
	}
	else
	{
		echo " <font color=\"#FF0000\"> Could not read Paper Track table. Try <a href='/conference/reviewer/view_abstract.php?paperid=".$_GET["paperid"]."'>again</a>?</font>" ;
	}
	?></td>
  </tr>

<?php
	if (numCategories( &$err_message ) > 0) // allow conferences with only Tracks, but no Topics
	{
		echo '<tr><td><p><strong>';
		echo "$topicStr(s):";
		echo '</strong>';
		if ( $catcomsep = getSelectedCategoryCommaSeparated( $paperInfo -> PaperID , &$err_message ) )
		{
			echo $catcomsep ;
		}
		else
		{
			echo " <font color=\"#FF0000\"> Could not read Paper Category table. Try <a href='/conference/reviewer/view_abstract.php?paperid=".$_GET["paperid"]."'>again</a>?</font>" ;
		}
		echo '</td></tr>';
	}
?>

  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td><a href='/conference/reviewer/download_file.php?fileid=<'>Download Paper</a></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
<?php

	echo $papers_str ;

	echo $storepapers_str ;

?>
  <input type="hidden" value="<?php echo $_GET["sort"] ; ?>" name="sort">
  <input type="hidden" value="<?php echo $_GET["showing"] ; ?>" name="showing">
  <input type="hidden" value="<?php echo $_POST["process"] ; ?>" name="process">
  <input type="hidden" value="<?php echo $_POST["myreferer"] ; ?>" name="myreferer">
  	<td><input type="submit" name="Submit" value="Back"></td>
  </tr>
</table>
</form>
<?php

do_html_footer( &$err_message );

?>

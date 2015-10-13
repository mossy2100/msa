<?php //////////// REVIEWER PHASE 3 ///////////////
	$php_root_path = ".." ;
	$privilege_root_path = "/reviewer" ;
//	extract ( $_GET , EXTR_REFS ) ;
//	extract ( $_POST , EXTR_REFS ) ;
//	extract ( $_FILES , EXTR_REFS ) ;

	require_once("includes/include_all_fns.inc");
	require_once("$php_root_path/includes/page_includes/page_fns.php");	// for numCategories() only
	session_start() ;
//	extract ( $_SESSION , EXTR_REFS ) ;

   // Define a few page vars
    $settingInfo = get_Conference_Settings();
	$trackStr = $settingInfo->TrackName; //Name for Track
	$topicStr = $settingInfo->TopicName; //Name for Topic
	$levelStr = $settingInfo->LevelName; //Name for Level

	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	$header = "View Assigned Papers" ;
	$accepted_privilegeID_arr = array ( 2 => "" ) ;
	$accepted_phaseID_arr = array ( 3 => "" ) ;
	authentication( $header , $accepted_privilegeID_arr , $accepted_phaseID_arr , $homepage , $php_root_path , $GLOBALS["DB_PREFIX"] , &$err_message ) ;

	//Establish connection with database
	$db = adodb_connect( &$err_message );

	$showing = $_GET["showing"];
	$sort = $_GET["sort"];

	//Call function to evaluate showing
	$showing = evaluate_showing($showing);
	$_SESSION["sort"] = $sort ;
	$_SESSION["showing"] = $showing ;

	//Retrive the preferences on the papers
	$reviewSQL = "SELECT * FROM " . $GLOBALS["DB_PREFIX"] . "Review R , " . $GLOBALS["DB_PREFIX"] . "Paper P";
	$reviewSQL .= " WHERE R.PaperID = P.PaperID";
	$reviewSQL .= " AND R.Membername = '".$_SESSION["valid_user"] ."'";
	$reviewSQL .= " AND P.Withdraw = 'false'";
	$reviewResult = $db -> Execute($reviewSQL);
	$totalPapers = $reviewResult -> RecordCount();

	$countResult = $db -> Execute($reviewSQL);
	$num_rows = $countResult -> RecordCount();
	////////////////////////////////////////////////
	
	if(is_null($_SESSION["view"]) && is_null($_POST["view"])){
	define("MAX_TAGS",5);}
	else{	
			 if(!is_null($_POST["view"]))
			   $_SESSION['view']=$_POST["view"]; //subsequent accesses
			  
			 if($_SESSION['view']=="all")
			  define("MAX_TAGS",$num_rows);
			 else
			  define("MAX_TAGS",$_SESSION['view']);	
		}
	
	////////////////////////////////
	
	//Check the sorting by Title

	switch($sort){
			case 1:
				$reviewSQL .= " ORDER BY R.AppropriatenessToConference ASC";
				$strSort = "Status - Ascending";
				break;
			case 2:
				$reviewSQL .= " ORDER BY R.AppropriatenessToConference DESC";
				$strSort = "Status - Descending";
				break;
			case 3:
				$reviewSQL .= " ORDER BY P.Title ASC";
				$strSort = "Title - Ascending";
				break;
			case 4:
				$reviewSQL .= " ORDER BY P.Title DESC";
				$strSort = "Title - Descending";
				break;
			case 5:
				$reviewSQL .= " ORDER BY P.PaperID ASC";
				$strSort = "PaperID - Ascending";
				break;
			case 6:
				$reviewSQL .= " ORDER BY P.PaperID DESC";
				$strSort = "PaperID - Descending";
				break;
			default:
				$reviewSQL .= " ORDER BY P.PaperID";
				$strSort = "PaperID - Ascending";
				break;
	}

	$reviewSQL .= " LIMIT ".$showing.",".MAX_TAGS;

	$reviewResult = $db -> Execute($reviewSQL);
	$total_rows = $reviewResult -> RecordCount();

	do_html_header("Assigned Papers" , &$err_message );

	if ($totalPapers <= 0)
	{
		echo "There are no papers assigned to you. Please check again later.";
		do_html_footer(&$err_message);
		exit;
	}

	//Call the function to display the range of records
	$from = evaluate_records_range($showing,$totalPapers,MAX_TAGS);

	//Call the function to evaluate prev
	$prev = evaluate_prev($sort,$showing,$totalPapers,MAX_TAGS);
	//Call the function to evaluate next
	$next = evaluate_next($sort,$showing,$totalPapers,MAX_TAGS);
	//Call the function to evaluate page links
	$pagesLinks = evaluate_pages_links($sort,$showing,$totalPapers,MAX_TAGS);

?>
<form name="frmPaper" method="post" action="update_biddings.php">
  <table width="100%" border="0" cellspacing="2" cellpadding="0">
	<tr>
	  <td>From: <?php echo "<strong>$from</strong>";	?></td>
      <td align="right">Ordered by:&nbsp;<strong><?php echo $strSort;  ?></strong>&nbsp;</td>
	<td align="right" ><?php echo $prev; ?> | <?php echo $pagesLinks; ?>| <?php echo $next; ?></td></tr>
  </table>
  <table width="100%" border="0" cellpadding="0" cellspacing="2">
    <tr>
      <td width="5%"><strong>ID<strong></td>
      <td width="70%">Order by:&nbsp;<strong>
	  <a href="/conference/reviewer/view_assigned_papers.php?sort=3&showing=<"><img src="<?php echo $php_root_path ; ?>/images/up.gif" border=0></a>&nbsp;Title&nbsp;<a href="/conference/reviewer/view_assigned_papers.php?sort=4&showing=<"><img src="<?php echo $php_root_path ; ?>/images/down.gif" border=0></a></strong>
        | <strong><a href="/conference/reviewer/view_assigned_papers.php?sort=1&showing=<"><img src="<?php echo $php_root_path ; ?>/images/up.gif" border=0></a>&nbsp;Status&nbsp;<a href="/conference/reviewer/view_assigned_papers.php?sort=2&showing=<"><img src="<?php echo $php_root_path ; ?>/images/down.gif" border=0></a></strong>| <strong><a href="/conference/reviewer/view_assigned_papers.php?sort=5&showing=<"><img src="<?php echo $php_root_path ; ?>/images/up.gif" border=0></a>&nbsp;PaperID&nbsp;<a href="/conference/reviewer/view_assigned_papers.php?sort=6&showing=<"><img src="<?php echo $php_root_path ; ?>/images/down.gif" border=0></a></strong></td>
      </form><td width="25%" align="right" rowspan="2%">
    <form name="view"  method="post" action="view_assigned_papers.php">
View papers per page	
	<select name="view">	
		<option value ="5" <?php if($_SESSION['view']==5) echo "SELECTED"; ?> >5 </option>
		<option value ="20" <?php if($_SESSION['view']==20) echo "SELECTED"; ?> >20 </option>
		<option value ="100" <?php if($_SESSION['view']==100) echo "SELECTED"; ?> >100 </option>
		<option value ="200" <?php if($_SESSION['view']==200) echo "SELECTED"; ?> >200 </option>
		<option value ="all" <?php if($_SESSION['view']=="all") echo "SELECTED"; ?> >All </option>
	</select>		
<input type="submit" value="Go"></form></td><hr>
    </tr>
	<tr><td colspan="3"><hr></td></tr>
    <?php
	
	$loop=MAX_TAGS;     
  	$quotient = ceil($totalPapers/MAX_TAGS);
  	$remainder=$totalPapers % MAX_TAGS;  
  	if($remainder > 0 && ($showing/MAX_TAGS)+1 == $quotient )
   	{$loop = $remainder;}
		
       for($i = 0; $i<$loop;$i++)
	{
		//Get the information
		$paperInfo = $reviewResult -> FetchNextObj();

		//Get the lastest file of the paper
		if ( ( $FileIDData = get_latestFile( $paperInfo->PaperID , &$err_message ) ) === false )
		{
			$err_message .= " Could not execute \"get_latestFile\" in \"view_assigned_papers.php\". <br>\n" ;
		}

		//Check whether the paper is already reviewed
		if ( ( $reviewExist = check_review_exist($paperInfo->PaperID) ) === NULL )
		{
			$err_message .= " Could not execute \"check_review_exist\" in \"view_assigned_papers.php\". <br>\n" ;
		}
?>
    <tr>
      <td valign="top"> <strong>#<?php echo $paperInfo -> PaperID . " "; ?></strong> </td>
      <td valign="top">
        <p><a href='/conference/reviewer/view_file.php?fileid=<'><?php echo stripslashes($paperInfo -> Title); ?></a><br/>

	<?php //if DoubleBindReview is set to false The author will not show
	if(!$settingInfo -> DoubleBlindReview)
 	{
 		if ( $authors = retrieve_authors($paperInfo->PaperID , &$err_message ) )
		{
			echo $authors;
		}
		else
			{
			echo " <font color=\"#FF0000\"> Could not read author table. Try <a 		href='/conference/reviewer/view_abstract.php?id=$id'>again</a>?</font>" ;
		}
		?>
		</p>


        <p>
    <?php
    }
    ?>
        <?php echo "$trackStr:"?>
          <?php if ( $catcomsep = GetSelectedTrackText($paperInfo->PaperID , &$err_message ) )
		{
			echo $catcomsep ;
		}
		else
		{
			$err_message .= " Could not execute \"GetSelectedTrackText\" in \"view_assigned_papers.php\". <br>\n" ;
			echo " <font color=\"#FF0000\"> Could not read Paper Track table. Try <a href='/conference/reviewer/view_assigned_papers.php'>again</a>?</font>" ;
		}
?>

<?php
	if (numCategories( &$err_message ) > 0) // allow conferences with only Tracks, but no Topics
	{
		echo "$topicStr(s):";
        if ( $catcomsep = getSelectedCategoryCommaSeparated($paperInfo->PaperID , &$err_message ) )
		{
			echo $catcomsep ;
		}
		else
		{
			$err_message .= " Could not execute \"getSelectedCategoryCommaSeparated\" in \"view_assigned_papers.php\". <br>\n" ;
			echo " <font color=\"#FF0000\"> Could not read Paper Category table. Try <a href='/conference/reviewer/view_assigned_papers.php'>again</a>?</font>" ;
		}
	}
?>

	<br> Status:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php if($reviewExist == true) echo "Reviewed"; else echo "<font 			color=\"#FF0000\">Not Reviewed</font>" ; ?></strong></p>
        </td>
      <td valign="top">
	  	<ul>
			<li><a href="/conference/reviewer/view_abstract.php?paperid=<">View Abstract</a></li>
        <?php if($reviewExist == true) {?>
        	<li><a href='/conference/reviewer/show_review.php?paperid=<'>Show Review</a></li><br>
        	<li><a href='/conference/reviewer/edit_review_form.php?paperid=<'>Edit Review</a></li>
        <?php } else {?>
        	<li><a href='/conference/reviewer/review_form.php?paperid=<'>Make Review</a></li><br/>
        <?php } ?>
		</ul>
      </td>
    </tr>
	<tr><td colspan="3"><hr></td></tr>
    <?php } //End of for loop
	?>
  </table>
  <table width="100%" border="0" cellspacing="2" cellpadding="5">
  	<tr>
      <td>Total Papers : <strong><?php echo $totalPapers; ?></strong></td>
      <td align="right"><?php echo $prev; ?> | <?php echo $pagesLinks; ?>| <?php echo $next; ?></td>
 	</tr>
 </table>
 </form>
<?php do_html_footer(&$err_message);
?>

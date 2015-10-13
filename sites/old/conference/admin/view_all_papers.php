<?php
	
	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");	
	require_once("$php_root_path/includes/page_includes/page_fns.php");
	session_start();
	// extract ( $_SESSION , EXTR_REFS ) ;	
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	

	do_html_header("Admin View All Papers" , &$err_message );
	

	 //Establish database connection
 	$db = adodb_connect();
  
	if (!$db){
    	echo "Could not connect to database server - please try later.";
		exit;		
	}
	//Retrieve all the papers
	$papersSQL = "SELECT *";
	$papersSQL .= " From " . $GLOBALS["DB_PREFIX"] . "Paper P," . $GLOBALS["DB_PREFIX"] . "PaperStatus PS";
	$papersSQL .= " WHERE P.PaperStatusID = PS.PaperStatusID AND Withdraw = 'false'";
	$countResult = $db -> Execute($papersSQL);
	$num_rows = $countResult -> RecordCount();	
	
	if(is_null($_SESSION["view"]) && is_null($_POST["view"])){ // first access
	define("MAX_TAGS",5);}
	else{	

			 if(!is_null($_POST["view"]))
			    $_SESSION['view']=$_POST["view"]; //subsequent accesses
			  
			 if($_SESSION['view']=="all")
			  define("MAX_TAGS",$num_rows);
			 else
			  define("MAX_TAGS",$_SESSION['view']);					
		}
	
	
	$showing = $_GET["showing"];
	$sort = $_GET["sort"];
	
	//Call function to evaluate showing
	$showing = evaluate_showing($showing);
	$_SESSION["sort"] = $sort ;	
	$_SESSION["showing"] = $showing ;

	

	

		
	//Check the sorting by Title
	switch($sort){
			case 1:
				$papersSQL .= " ORDER BY PaperID ASC";
				$strSort = "PaperID - Ascending";
				break;
			case 2:
				$papersSQL .= " ORDER BY PaperID DESC";
				$strSort = "PaperID - Descending";
				break;	
			case 3:
				$papersSQL .= " ORDER BY Title ASC, PaperID ASC";
				$strSort = "Title - Ascending";
				break;
			case 4:
				$papersSQL .= " ORDER BY Title DESC, PaperID ASC";
				$strSort = "Title - Descending";
				break;
			case 5:
				$papersSQL .= " ORDER BY PS.PaperStatusID ASC, PaperID ASC";
				$strSort = "PaperStatus - Ascending";
				break;
			case 6:
				$papersSQL .= " ORDER BY PS.PaperStatusID DESC, PaperID ASC";
				$strSort = "PaperStatus - Descending";
				break;	
			case 7:
				$papersSQL .= " ORDER BY P.TrackID ASC, PaperID ASC";
				$strSort = "TrackID - Ascending";
				break;
			case 8:
				$papersSQL .= " ORDER BY P.TrackID DESC, PaperID ASC";
				$strSort = "TrackID - Descending";
				break;				
			case 9:
				$papersSQL .= " ORDER BY OverallRating ASC, PaperID ASC";
				$strSort = "Evaluation - Ascending";
				break;				
			case 10:
				$papersSQL .= " ORDER BY OverallRating DESC, PaperID ASC";
				$strSort = "Evaluation - Descending";
				break;					
			default:
				$papersSQL .= " ORDER BY PaperID";
				$strSort = "PaperID - Ascending";
				break;							
	}	
	
	$papersSQL .= " LIMIT ".$showing.",".MAX_TAGS;
		
	$papersResult = $db -> Execute($papersSQL);
	
	if ($num_rows <= 0){
		echo "Sorry, there are no papers in the database.";
		// echo MAX_TAGS; 
		 
		exit;
	}
		
	//Call the function to display the range of records
	$from = evaluate_records_range($showing,$num_rows,MAX_TAGS);		
			
	//Call the function to evaluate prev
	$prev = evaluate_prev($sort,$showing,$num_rows,MAX_TAGS);
	//Call the function to evaluate next
	$next = evaluate_next($sort,$showing,$num_rows,MAX_TAGS);
	//Call the function to evaluate page links
	$pagesLinks = evaluate_pages_links($sort,$showing,$num_rows,MAX_TAGS);
		
?>	
<form name="frmPaper" method="post" action="display_assign_papers.php">
<br>
  <table width="100%" border="0" cellspacing="2" cellpadding="0">
    <tr> 
      <td width="30%">From: <?php echo "<strong>$from</strong>";	?></td>
      <td width="40%" align="left">Ordered by:&nbsp;<strong><?php echo $strSort;  ?></strong></td>
      <td width="30%" align="right">Total Papers : <strong><?php echo $num_rows; ?></strong></td>
    </tr>
    <tr>
	<td>&nbsp;</td>		
      	<td colspan=2  align="right"><br><?php echo $prev; ?>&nbsp;|<?php echo $pagesLinks; ?> |&nbsp;<?php echo $next; ?></td>
    </tr>
  </table>				
  <table width="100%" border="0" cellpadding="1" cellspacing="2">
    <tr> 
      <td width="5%" align="center">&nbsp;</td>
      <td width="5%" align="center"><strong> ID</strong></td>
      <td width="65%">
	  	Order by: <a href="/conference/admin/view_all_papers.php?sort=1&showing=<"><img src="<?php echo $php_root_path ; ?>/images/up.gif" border=0></a>&nbsp;<strong>PaperID</strong>&nbsp;<a href="/conference/admin/view_all_papers.php?sort=2&showing=<"><img src="<?php echo $php_root_path ; ?>/images/down.gif" border=0></a>
	    &nbsp;|&nbsp;
        <a href="/conference/admin/view_all_papers.php?sort=3&showing=<"><img src="<?php echo $php_root_path ; ?>/images/up.gif" border=0></a>&nbsp;<strong>Title</strong>&nbsp;<a href="/conference/admin/view_all_papers.php?sort=4&showing=<"><img src="<?php echo $php_root_path ; ?>/images/down.gif" border=0></a>
        &nbsp;|&nbsp;
		<a href="/conference/admin/view_all_papers.php?sort=5&showing=<"><img src="<?php echo $php_root_path ; ?>/images/up.gif" border=0></a>&nbsp;<strong>Status</strong>&nbsp;<a href="/conference/admin/view_all_papers.php?sort=6&showing=<"><img src="<?php echo $php_root_path ; ?>/images/down.gif" border=0></a>
		&nbsp;|&nbsp;
			<a href="/conference/admin/view_all_papers.php?sort=7&showing=<"><img src="<?php echo $php_root_path ; ?>/images/up.gif" border=0></a>&nbsp;<strong>TrackID</strong>&nbsp;<a href="/conference/admin/view_all_papers.php?sort=8&showing=<"><img src="<?php echo $php_root_path ; ?>/images/down.gif" border=0></a>
		&nbsp;|&nbsp;
			<a href="/conference/admin/view_all_papers.php?sort=9&showing=<"><img src="<?php echo $php_root_path ; ?>/images/up.gif" border=0></a>&nbsp;<strong>Evaluation</strong>&nbsp;<a href="/conference/admin/view_all_papers.php?sort=10&showing=<"><img src="<?php echo $php_root_path ; ?>/images/down.gif" border=0></a>
		</td></form>
	
<td width="25%" align="center" rowspan="2%">
<form name="view"  method="post" action="view_all_papers.php">
 Papers per Page	
	<select name="view">	
		<option value ="5" <?php if($_SESSION['view']==5) echo "SELECTED"; ?> >5 </option>
		<option value ="20" <?php if($_SESSION['view']==20) echo "SELECTED"; ?> >20 </option>
		<option value ="100" <?php if($_SESSION['view']==100) echo "SELECTED"; ?> >100 </option>
		<option value ="200" <?php if($_SESSION['view']==200) echo "SELECTED"; ?> >200 </option>
		<option value ="all" <?php if($_SESSION['view']=="all") echo "SELECTED"; ?> >All </option>
	</select>	
<input type="submit" value="Go"></form>
</td>
</tr>
<hr>
    </tr>
    <?php
       	$quotient = ceil($num_rows/MAX_TAGS);
	$remainder=$num_rows % MAX_TAGS;
	
	if($num_rows==MAX_TAGS)  //showing all papers
	{$loop = MAX_TAGS;}
	elseif(($showing/MAX_TAGS)+1 == $quotient)  //showing last page
	{$loop = $remainder;}
	else
	{$loop = MAX_TAGS;} //not showing last page

	for($j = 0; $j<$loop;$j++)
	{		
// $t=getdate(); echo "Time 1 " . $t['seconds'] . "<br>"; //Debug BL		
	        
	        //Get the information
	     $paperInfo = $papersResult -> FetchNextObj();
	      //Get the lastest file of the paper  				
		//$FileIDData = get_latestFile($paperInfo->PaperID , &$err_message );
		$FileIDData = get_latestFileID($paperInfo->PaperID , &$err_message );
		//Get reviewer of the paper and format into string
		$arrReviewers = get_Reviewers_Of_Paper($paperInfo->PaperID);
		$strReviewers = "";
 		for($i=0;$i<count($arrReviewers);$i++){
			if($i == count($arrReviewers) -1)
				$strReviewers .= "<a href=\"#\" onClick=\"JavaScript:window.open('view_reviewer_info.php?name=".$arrReviewers[$i]."',null,'height=200,width=500,status=yes,toolbar=no,menubar=no,scrollbars=yes,location=no')\">".getMemberFullName($arrReviewers[$i])."</a>";
			else
				$strReviewers .= "<a href=\"#\" onClick=\"JavaScript:window.open('view_reviewer_info.php?name=".$arrReviewers[$i]."',null,'height=200,width=500,status=yes,toolbar=no,menubar=no,scrollbars=yes,location=no')\">".getMemberFullName($arrReviewers[$i])."</a>, ";		
	}
	
?>
      
      <tr>
      <td colspan=4><hr> </td>
      </tr>
      <tr> 
      <td align="center" valign="top">&nbsp;</td>
      <td height="31" align="center" valign="top">#<?php echo $paperInfo->PaperID; ?></td>
      <td valign="top"><a href="/conference/admin/view_file.php?fileid=<"><strong><?php echo stripslashes($paperInfo -> Title); ?></strong></a><br>
          
	<?php
 	if ( $authors = retrieve_authors($paperInfo->PaperID , &$err_message ) )
	{
		echo $authors ;
	}
	else
	{
		echo " <font color=\"#FF0000\"> Could not read author table. Try <a 	href='/conference/admin/view_abstract.php?id=$id'>again</a>?</font>" ;
	}		
	?><br><br>

	<strong>Track:</strong>&nbsp;<?php echo  getSelectedTrackText($paperInfo->PaperID , &$err_message );?>
	<br>
<?php
	if (areSessionTracksEnabled()) {
		$text = getSelectedSessionTrackText($paperInfo->PaperID , &$err_message );
		if ($text === "")
			$color = ' style="color:red;"';
		echo "<strong".$color.">SessionTrack:</strong>&nbsp;$text";
		echo '<br>' . "\n";
	}
	if (numCategories( &$err_message ) > 0) // allow conferences with only Tracks, but no Topics
	{
		echo '<strong>Topic(s):</strong>&nbsp;';
		echo  getSelectedCategoryCommaSeparated($paperInfo->PaperID , &$err_message );
		echo '<br>';
	}
?>
          <?php if($paperInfo -> PaperStatusName != "Not Reviewed"){  ?>
          <?php if (count($arrReviewers) < 3) echo "<font color=\"#FF0000\"><strong>Reviewers:</strong></font>"; else echo "<strong>Reviewers:</strong>";?>&nbsp;<?php echo $strReviewers; ?>
          <br>
		  <?php }?>
		  	  
		   <strong>Status:</strong> <?php echo $paperInfo -> PaperStatusName;  
          		  	  $curtype = get_presentation_info( get_presentation_type_for_paper($paperInfo -> PaperID) );
          		  	  if ($paperInfo -> PaperStatusName == "Accepted") { ?>
	  				  as <?php echo $curtype -> PresentationTypeName; ?>
	  	  			  <?php } ?>
          	  
           <?php 
           		// if(get_Number_Of_Reviews($paperInfo->PaperID) > 0){
		  		//	echo "(".get_Number_Of_Reviews($paperInfo->PaperID).")";
		   		//	}
		   ?>
      <?php 
	  if ($paperInfo -> OverallRating > 0 )
	  {
	  	echo "&nbsp; <strong>Evaluation: </strong> ";
	  	echo sprintf("%01.1f", $paperInfo -> OverallRating);
	  	echo "/10";
	  	echo " from ".get_Number_Of_Reviews($paperInfo->PaperID)." review(s)";
	  }
	  ?>
	  &nbsp;
	  <strong>User: </strong> <?php echo $paperInfo -> MemberName ?>
	  <br>
          </td>
      <td valign="middle"> <ul>
          <li><a href="/conference/admin/view_abstract.php?id=<">View 
            Abstract</a></li>
		  <?php //Check whether Reviewer Bidding Phase is set to true

 			if((get_Num_Preferences($paperInfo->PaperID) != 0) && (checkPhase("Reviewer Bidding"))){
		  ?>
		  <li><a href="/conference/admin/show_all_selections.php?id=<">View Preferences</a>&nbsp;<?php echo "(".get_Num_Preferences($paperInfo->PaperID).")"; ?></li>
          <?php
		  }//end of if statement
		  ?>
		  
        <?php 
		//if the paper status is not reviewed yet, then display the link to assign to reviewer
		if ($paperInfo -> PaperStatusName == "Not Reviewed"){ ?>
		<li><a href="/conference/admin/display_assign_paper.php?paperID=<">Assign Reviewers</a></li>
        <?php } else { ?>		  
    	<li><a href="/conference/admin/edit_assign_reviewers.php?paperID=<">Edit Reviewers</a></li>
		  <?php
		  }
		  
		  //Check whether paper status is reviewing,if it is display show review link
		  if(($paperInfo -> PaperStatusName == "Reviewing") && (get_Number_Of_Reviews($paperInfo->PaperID) != 0)){
		  ?>
          <li><a href="/conference/admin/show_all_reviews.php?id=<">View 
            Reviews</a></li>
          <?php
		  }//end of if
		
		  //Check whether the paper status is Reviewed,then display view all reviews link
		  if ($paperInfo -> PaperStatusName == "Reviewed" || $paperInfo -> PaperStatusName == "Accepted" || $paperInfo -> PaperStatusName == "Rejected" || $paperInfo -> PaperStatusName == "Marginal"){ ?>
          <li><a href="/conference/admin/show_all_reviews.php?id=<">View 
            All Reviews</a></li>
          <?php
		  }//end of if
			
			//When the paper has been reviewed, display accept and reject paper link
			if(($paperInfo -> PaperStatusName != "Not Reviewed") && (checkPhase("Reviewing"))||(checkPhase("Final Paper Submission"))){ ?>
          <li><a href="/conference/admin/evaluate_paper_status.php?paperID=<">Decide on Paper#<?php echo $paperInfo->PaperID; ?></a></li>
          <?php }//end of if?>
		  
        </ul></td>
    </tr>
    
    <?php  }//End of for loop
?>
  </table>
  <table width="100%" border="0" cellspacing="2" cellpadding="5">
	<tr>
	<td colspan = 2> <hr> </td>
	</tr>
  	<tr>				
      <td>Total Papers : <strong><?php echo $num_rows; ?></strong></td>
      <td align="right"><?php echo $prev; ?> | <?php echo $pagesLinks; ?> | <?php echo $next; ?></td>
 	</tr>
 </table>

	
<?php			
	do_html_footer( &$err_message );
?>

	  


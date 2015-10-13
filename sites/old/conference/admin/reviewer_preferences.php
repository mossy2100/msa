<?php 
	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;

	require_once("includes/include_all_fns.inc");	
	session_start() ;
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	
	do_html_header("Reviewer Preferences");

	//Establish connection with database
	$db = adodb_connect();
	if (!$db) {
		echo "Could not connect to database server - please try later.";
		exit;
	}	

	// collect all tracks in array Track
	$Track = array();
	$categorySQL = "SELECT * FROM " . $GLOBALS["DB_PREFIX"] . "Track";
	$categoryResult = $db -> Execute($categorySQL);
	if(!$categoryResult) {
		return "Could not retrieve the track information - please try again later";
		exit;
	}
	while($categoryInfo = $categoryResult->FetchNextObj())
		$Track[$categoryInfo->TrackID] = $categoryInfo->TrackName;

	// collect all reviewers
	$memberSQL = "SELECT * FROM " . $GLOBALS["DB_PREFIX"] . "Member M," . $GLOBALS["DB_PREFIX"] . "PrivilegeType P," . $GLOBALS["DB_PREFIX"]."Registration R";
	$memberSQL .= " WHERE M.RegisterID <> 0";
	$memberSQL .= " AND M.PrivilegeTypeID = P.PrivilegeTypeID";
	$memberSQL .= " AND M.RegisterID = R.RegisterID";
	$memberSQL .= " AND P.PrivilegeTypeName = 'Reviewer'";		
	$memberSQL .= " ORDER BY R.FirstName ASC";

	$memberResult = $db -> Execute($memberSQL);
	if(!$memberResult) {
		echo "Could not retrieve the members' information - please try again later";
		exit;
	}
?>

<p><strong>Total Reviewers:</strong>&nbsp;<?php echo $memberResult -> RecordCount(); ?></p>
<hr>
<form name="form1" method="post" action="process_reviewer_preferences.inc">
	<?php while( $memberInfo = $memberResult->FetchNextObj()) { $memberID = $memberInfo->RegisterID; ?>
		<input type="hidden" name="reviewer[]" value="<?php echo $memberID; ?>">
		<p>Preferences for: <strong><?php echo $memberInfo->MemberName; ?></strong> &nbsp; 
		<strong><?php echo ($memberInfo->FirstName != "") ? $memberInfo->FirstName." ".$memberInfo->MiddleName . " " . $memberInfo->LastName : "N/A"; ?></strong> &nbsp; 
		<strong><?php echo ($memberInfo->Organisation != "") ? $memberInfo->Organisation : "N/A"; ?></strong></p>
		<table border="0" cellspacing="0" cellpadding="1">
		<tr><th>Track</th><th>1st</th><th>2nd</th><th>none</th></tr>
		<?php foreach ($Track as $id => $value) {?>
			<tr>
				<td><?php echo $value ?></td>
				<td><input type="radio" name="<?php echo "sel${memberID}_$id" ?>" value="1"></td>
				<td><input type="radio" name="<?php echo "sel${memberID}_$id" ?>" value="2"></td>
				<td><input type="radio" name="<?php echo "sel${memberID}_$id" ?>" value="0" checked="checked"></td>
			</tr>
		<?php } // end of for loop ?>
		</table><br><br><br>
	<?php } // end of while loop ?>

	<hr>

	<input type="checkbox" name="addReviewer2Paper" value="true"> Add reviewer to paper, if paper is in 1st preference track

	<hr>

	<input type="submit" name="Submit" value="Submit"> <input name="Submit" type="submit" id="Submit" value="Cancel">
</form>

<br>
<p>This function updates all biddings from a particular reviewer. That helps a lot if the conference chair wants to assign papers to reviewers (instead of reviewers bidding for papers themselfes).</p>


<?php do_html_footer();
?>

<?php
	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");	
	require_once("$php_root_path/includes/page_includes/page_fns.php");
	session_start();
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;

	do_html_header("Admin Bulk Accept Papers", &$err_message );

	 //Establish database connection
	$db = adodb_connect();
	if (!$db){
		echo "Could not connect to database server - please try later.";
		exit;
	}

	if ($_POST["test"] === "Test") {
		$min = floatval( $_POST["min"] );
		$max = floatval( $_POST["max"] );
		Test( $db, $min, $max );
	} else if ($_POST["submit"] === "Accept/Reject these papers") {
		$min = floatval( $_POST["min"] );
		$max = floatval( $_POST["max"] );
		Process( $db, $min, $max );
		echo '<p style="color:lime;font-weight:bold;">Bulk Accept/Reject done.</p>';
	} else {
		$min = 10;
		$max = 0;
	}
	
	//Get the total number of submitted papers
	$countPapersSQL = "SELECT COUNT(*) AS totalPapers FROM " . $GLOBALS["DB_PREFIX"] . "Paper WHERE Withdraw = 'false'";	
	$countPapersResult = $db -> Execute($countPapersSQL);
	$countPapersInfo = $countPapersResult -> FetchNextObj();

	//Get the total number of accepted papers
	$countAcceptedSQL = "SELECT COUNT(*) AS totalAcceptedPapers FROM " . $GLOBALS["DB_PREFIX"] . "Paper WHERE PaperStatusID= '3' AND Withdraw = 'false'";
	$countAcceptedResult = $db -> Execute($countAcceptedSQL);
	$countAcceptedInfo = $countAcceptedResult -> FetchNextObj();

	//Get the total number of rejected papers
	$countRejectedSQL = "SELECT COUNT(*) AS totalRejectedPapers FROM " . $GLOBALS["DB_PREFIX"] . "Paper WHERE PaperStatusID= '4'AND Withdraw = 'false'";
	$countRejectedResult = $db -> Execute($countRejectedSQL);
	$countRejectedInfo = $countRejectedResult -> FetchNextObj();

	//Get the total number of marginal
	$countMarginalSQL = "SELECT COUNT(*) AS totalMarginalPapers FROM " . $GLOBALS["DB_PREFIX"] . "Paper WHERE PaperStatusID= '6' AND Withdraw = 'false'";
	$countMarginalResult = $db -> Execute($countMarginalSQL);
	$countMarginalInfo = $countMarginalResult -> FetchNextObj();

	//Get the total number of papers in review
	$countReviewingSQL = "SELECT COUNT(*) AS totalReviewingPapers FROM " . $GLOBALS["DB_PREFIX"] . "Paper WHERE PaperStatusID in (1,2,5) AND Withdraw = 'false'";
	$countReviewingResult = $db -> Execute($countReviewingSQL);
	$countReviewingInfo = $countReviewingResult -> FetchNextObj();
?>

<p>This function lets you accept papers by evaluation score.</p>
<p>Only use this function, after reviewing is completed!</p>

<strong>Current Decision Status</strong><br>
<br>
<table width="60%" border="1" cellpadding="3" cellspacing="0" bordercolor="#999999">
<tr> 
	<td width="70%">Total Papers Submitted:</td><td><?php echo $countPapersInfo -> totalPapers; ?></td>
</tr>
<tr>
	<td width ="70%"> Accepted </td><td> <?php echo $countAcceptedInfo->totalAcceptedPapers ; ?> </td>
</tr>
<tr>
	<td width ="70%"> Rejected </td><td> <?php echo $countRejectedInfo->totalRejectedPapers ; ?> </td>
</tr>
<tr>
	<td width ="70%"> Marginal </td><td> <?php echo $countMarginalInfo->totalMarginalPapers ; ?> </td>
</tr>
<tr>
	<td width ="70%"> Pending </td><td> <?php echo $countReviewingInfo->totalReviewingPapers ; ?> </td>
</tr>
</table>

<?php
	if ($_POST["test"] === "Test") {
?>
<br><br>
<p>This will be the status:</p>
<table width="60%" border="1" cellpadding="3" cellspacing="0" bordercolor="#999999">
<tr>
	<td width ="70%"> Accepted </td><td><?php echo $GLOBALS["accepted"]; ?></td>
</tr>
<tr>
	<td width ="70%"> Rejected </td><td><?php echo $GLOBALS["rejected"]; ?></td>
</tr>
</table>
<?php
	}
?>

<br><br>
<p>The score is a value in the range of 0 to 10. If you don't want rejects to happen, use -1 as score.</p>
<form action="bulk_accept.php" method="POST">
<table width="auto" border="0" cellpadding="0" cellspacing="0" >
<tr>
	<td>
		<table width="auto" border="1" cellpadding="0" cellspacing="2">
			<tr>
				<td>&nbsp;<strong>Ranking Criteria</strong></td>
				<td>Percentage</td>
			</tr>
			<tr>
				<td>Minimum Score for Acceptance</td>
				<td><input type="text" value="<?php echo $min; ?>" name="min" style="text-align:center"></td>
			</tr>
			<tr>
				<td>Maximum Score for Rejection</td>
				<td><input type="text" value="<?php echo $max; ?>" name="max" style="text-align:center"></td>
			</tr>
		</table>
	</td>
</tr>
<tr>
	<td align="center"><input type="submit" name="test" value="Test"> <input type="submit" name="submit" value="Accept/Reject these papers"></td>
</tr>
</table>
</form>
<p><strong>Attention:</strong> The users are <strong>NOT</strong> informed about the choice!</p>

<?php
	do_html_footer( &$err_message );

	function Test( $db, $min, $max )
	{
		$min = floatval( $min );
		$max = floatval( $max );

		$sql = "SELECT COUNT(*) AS accepted FROM " . $GLOBALS["DB_PREFIX"] . "Paper WHERE Withdraw = 'false' AND OverallRating >= $min";
		$result = $db -> Execute($sql);
		$info = $result -> FetchNextObj();
		$GLOBALS["accepted"] = $info->accepted;

		$sql = "SELECT COUNT(*) AS rejected FROM " . $GLOBALS["DB_PREFIX"] . "Paper WHERE Withdraw = 'false' AND OverallRating <= $max";
		$result = $db -> Execute($sql);
		$info = $result -> FetchNextObj();
		$GLOBALS["rejected"] = $info->rejected;
	}

	function Process( $db, $min, $max )
	{
/*		echo '<br><strong style="color:red">Accept/Reject these papers NOT YET IMPLEMENTED</strong><br>';
		return;*/
		
		$papersSQL = "SELECT * FROM " . $GLOBALS["DB_PREFIX"] . "Paper";
		$papersSQL .= " WHERE Withdraw = 'false'";
		$papers = $db -> Execute($papersSQL);

		while ($papers && ($paperInfo = $papers->FetchNextObj())) {
			if ($paperInfo->OverallRating <= $max)
				reject( $paperInfo->PaperID );
			if ($paperInfo->OverallRating >= $min)
				accept( $paperInfo->PaperID );
		}
	}

	function accept( $PaperID )
	{
// 		echo "accepting $PaperID<br>"; return;

		remove_paper_presentation( $PaperID );
		// Add to scheduling system as an unscheduled paper
		assign_paper_presentation_type( $PaperID, 1 ); // oral
		// If all is well, then it will get autoscheduled
		autoschedule_waiting_papers();
		update_PaperStatus( $PaperID, "Accepted" );
	}

	function reject( $PaperID )
	{
// 		echo "rejecting $PaperID<br>"; return;

		remove_paper_presentation( $PaperID );
		update_PaperStatus( $PaperID, "Rejected" );
	}

?>


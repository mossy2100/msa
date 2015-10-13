<?php
	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");	
	require_once("$php_root_path/includes/page_includes/page_fns.php");
	session_start();
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;

	do_html_header("Admin Recalculate Evaluation Score", &$err_message );

	 //Establish database connection
	$db = adodb_connect();
	if (!$db){
		echo "Could not connect to database server - please try later.";
		exit;
	}

	if ($_POST["submit"] === "Recalculate all Papers") {
		// user wants recalculation to happen
		$appropriate = intval( $_POST["appropriate"] );
		$originality = intval( $_POST["originality"] );
		$technical = intval( $_POST["technical"] );
		$presentation = intval( $_POST["presentation"] );
		$overall = intval( $_POST["overall"] );

		recalculate( $db, $appropriate, $originality, $technical, $presentation, $overall );
	} else {
		$appropriate = 5;
		$originality = 5;
		$technical = 5;
		$presentation = 5;
		$overall = 80;
	}
?>

<p>The standard evaluation is computed as: <strong>80% weighting for Overall Evaluation + 5% weighting for each of the other criteria</strong></p>
<p>This can be changed here. Pay attention to the fact, that this function will change the entry 
<i>OverallRating</i> in the <i>Paper</i> table, but not the calculation procedure itself. 
Thus, if a paper is reviewed again, the standard calculation scheme applies for that paper.</p>
<p>Therefore use this function only after all reviewers are finished.</p>

<form action="recalc_evaluation.php" method="POST">
<table width="auto" border="0">
<tr>
	<td>
		<table width="auto" border="1" cellpadding="0" cellspacing="2">
			<tr>
				<td>&nbsp;<strong>Ranking Criteria</strong></td>
				<td>Percentage</td>
			</tr>
			<tr>
				<td>Appropriateness to the Conference</td>
				<td><input type="text" value="<?php echo $appropriate; ?>" name="appropriate" style="text-align:center"></td>
			</tr>
			<tr>
				<td>Originality</td>
				<td><input type="text" value="<?php echo $originality; ?>" name="originality" style="text-align:center"></td>
			</tr>
			<tr>
				<td>Technical Strength</td>
				<td><input type="text" value="<?php echo $technical; ?>" name="technical" style="text-align:center"></td>
			</tr>
			<tr>
				<td>Presentation</td>
				<td><input type="text" value="<?php echo $presentation; ?>" name="presentation" style="text-align:center"></td>
			</tr>
			<tr>
				<td>Overall Evaluation</td>
				<td><input type="text" value="<?php echo $overall; ?>" name="overall" style="text-align:center"></td>
			</tr>
		</table>
	</td>
</tr>
<tr>
	<td align="center"><input type="submit" name="submit" value="Recalculate all Papers"></td>
</tr>
</table>
</form>

<?php
	do_html_footer( &$err_message );

	function recalculate( $db, $appropriate, $originality, $technical, $presentation, $overall )
	{
		echo "<strong>Recalculation in progress ...</strong>";ob_flush();flush();

		$papersSQL = "SELECT PaperID";
		$papersSQL .= " From " . $GLOBALS["DB_PREFIX"] . "Paper P," . $GLOBALS["DB_PREFIX"] . "PaperStatus PS";
		$papersSQL .= " WHERE P.PaperStatusID = PS.PaperStatusID AND Withdraw = 'false'";
		$papers = $db -> Execute($papersSQL);

		while ($papers && ($paperInfo = $papers->FetchNextObj())) {
			$avgSQL = "SELECT AVG(OverallEvaluation) as OverallEvaluation,";
			$avgSQL .= " AVG(AppropriatenessToConference) as AppropriatenessToConference,";
			$avgSQL .= " AVG(Originality)as Originality,";
			$avgSQL .= " AVG(TechnicalStrength) as TechnicalStrength,";
			$avgSQL .= " AVG(Presentation) as Presentation";
			$avgSQL .= " FROM " . $GLOBALS["DB_PREFIX"] . "Review";
			$avgSQL .= " WHERE PaperID = $paperInfo->PaperID";
			$avgSQL .= " AND AppropriatenessToConference <> 0";
		
			$resultAvg = $db -> Execute($avgSQL);
		
			$evaluation = -1;
			if ($resultAvg){
				echo '+';
				$rowAvg = $resultAvg -> FetchNextObj();
				$evaluation = $overall/100.0 * ($rowAvg -> OverallEvaluation - 1)/4.0;
				$evaluation += $appropriate/100.0 * ($rowAvg -> AppropriatenessToConference - 1)/4.0;
				$evaluation += $technical/100.0 * ($rowAvg -> TechnicalStrength - 1)/4.0;
				$evaluation += $presentation/100.0 * ($rowAvg -> Presentation - 1)/4.0;
				$evaluation += $originality/100.0 * ($rowAvg -> Originality - 1)/4.0;
				$evaluation *= 10;
			}
		
			$updateSQL = "UPDATE " . $GLOBALS["DB_PREFIX"] . "Paper";
			$updateSQL .= " SET OverallRating = $evaluation";
			$updateSQL .= " WHERE PaperID = $paperInfo->PaperID";
		
			if ($evaluation >= 0) {
				echo '*';
				if(!$db -> Execute($updateSQL)) {
					$err_message .= " Could not Update the \"Paper\" table in the database by \"recalculate()\" in recalc_evaluation.php. <br>\n" ;
					return false ;
				}
			}
		}

		echo "<strong> done</strong>";
	}
?>


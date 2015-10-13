<?php 
	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");	
	session_start() ;
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	
	//Code added  to address security problem reported by Sebastian Held 14-Nov-2006
	$header = "Reviewer Preferences" ;
	$accepted_privilegeID_arr = array ( 3 => "" ) ;
	$accepted_phaseID_arr = array ( 1 => "" , 2 => "" , 3 => "" , 4 => "" ) ;
	authentication( $header , $accepted_privilegeID_arr , $accepted_phaseID_arr , $homepage , $php_root_path , $GLOBALS["DB_PREFIX"] , &$err_message ) ;		
	
	do_html_header("Reviewer Preferences");

	// check if coming from reviewer_preferences.inc
	if (($_POST["Submit"] !== "Submit") || !isset($_POST["reviewer"])) {
		echo '<p>Nothing to do.</p>';
		do_html_footer();
		exit;
	}

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
	while($memberInfo = $memberResult->FetchNextObj())
		$Reviewer[$memberInfo->RegisterID] = $memberInfo->MemberName;

	foreach( $_POST["reviewer"] as $r_id => $r_value ) {
		echo "<p>Processing reviewer: <strong>$Reviewer[$r_value]</strong></p>";
		foreach( $Track as $t_id => $t_value ) {
			if (isset($_POST["sel".$r_value."_".$t_id]) && (($pref = $_POST["sel".$r_value."_".$t_id]) != 0)) {
				echo "$t_value ... ";
				$sql = "REPLACE " . $GLOBALS["DB_PREFIX"] . "Selection (PaperID, MemberName, PreferenceID)";
				$sql .= " SELECT P.PaperID,\"" . $Reviewer[$r_value] . "\"," . $pref . " FROM " . $GLOBALS["DB_PREFIX"] . "Paper P," . $GLOBALS["DB_PREFIX"] . "PaperStatus PS WHERE P.PaperStatusID = PS.PaperStatusID AND Withdraw = 'false' AND P.TrackID = $t_id";
				//echo "<pre>$sql</pre>";
				if ($db->Execute($sql))
					echo 'done.<br>';
				else
					echo 'FAILED<br>';
			}
			if (($_POST["addReviewer2Paper"] == "true") && ($pref == 1)) {
				// add the current reviewer to all papers which are in his 1st preference
				$sql = " SELECT P.PaperID FROM " . $GLOBALS["DB_PREFIX"] . "Paper P," . $GLOBALS["DB_PREFIX"] . "PaperStatus PS WHERE P.PaperStatusID = PS.PaperStatusID AND Withdraw = 'false' AND P.TrackID = $t_id";
				//echo "<pre>$sql</pre>";
				$paperResult = $db->Execute( $sql );
				if (!$paperResult) {
					echo "Could not retrieve the papers' information - please try again later";
					exit;
				}
				while( $paperInfo = $paperResult->FetchNextObj() ) {
					$paperID = $paperInfo->PaperID;
					$reviewers = get_Reviewers_Of_Paper( $paperID );
					if (!is_array($reviewers)) {
						// up to now, no reviewers present for this paper
						assign_paper( $paperID, array($Reviewer[$r_value]) );
					} else {
						// add current reviewer
						$reviewers[] = $Reviewer[$r_value];
						edit_Assigned_Reviewers( $paperID, $reviewers );
					}
				}
			}
		}
	}
?>



<?php do_html_footer();
?>

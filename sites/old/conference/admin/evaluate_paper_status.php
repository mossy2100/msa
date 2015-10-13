<?php
	
	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");	
	require_once("$php_root_path/includes/page_includes/page_fns.php");	// only for numCategories()
	session_start();
	// extract ( $_SESSION , EXTR_REFS ) ;	
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	
	// Define a few page vars
    $settingInfo = get_Conference_Settings();
	$trackStr = $settingInfo->TrackName; //Name for Track
	$topicStr = $settingInfo->TopicName; //Name for Topic
	$levelStr = $settingInfo->LevelName; //Name for Level
	
	//Establish database connection
  	$db = adodb_connect();
  
  	if (!$db){
   		echo "Could not connect to database server - please try later.";
		exit;
	}

	//Get the GET VARS
	$paperID = & $_GET["paperID"];
	
	//Get the paper information
	$paperInfo = get_paper_info($paperID);	
	
	do_html_header("Evaluate Paper Status" , &$err_message );
	
	$curtype = get_presentation_info( get_presentation_type_for_paper($paperInfo -> PaperID) );
?>
<form name="form1" method="post" action="process_evaluate_paper_status.php">
  <table width="100%" border="0" cellspacing="0" cellpadding="2">
    <tr> 
      <td colspan="2"><input type="hidden" name="paperID" value="<?php echo $paperInfo->PaperID; ?>"> <h3>#<?php echo $paperInfo->PaperID; ?>&nbsp;<?php echo stripslashes($paperInfo -> Title); ?></h3></td>
    </tr>
    <tr> 
      <td width="20%"><strong>Authors:</strong> </td>
      <td width="80%"><?php echo retrieve_authors($paperInfo -> PaperID);?></td>
    </tr>
    <tr> 
      <td><strong><?php echo "$trackStr:"?></strong> </td>
      <td><?php echo  GetSelectedTrackText($paperInfo -> PaperID , &$err_message );?></td>
    </tr>
<?php
	if (areSessionTracksEnabled()) {
		$SessionTrack = getSelectedSessionTrackText($paperInfo->PaperID , &$err_message );
		if ($SessionTrack === "")
			$color = ' style="color:red;"';
		echo "<tr><td><strong".$color.">SessionTrack:</strong></td>";
		echo "<td>$SessionTrack</td></tr>\n";
	}
	if (numCategories( &$err_message ) > 0) // allow conferences with only Tracks, but no Topics
	{
		echo '<tr><td><strong>';
		echo "$topicStr(s):";
		echo '</strong></td><td>';
		echo  getSelectedCategoryCommaSeparated($paperInfo -> PaperID , &$err_message );
		echo '</td></tr>';
	}
?>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td><strong>Current Paper Status:</strong></td>
      <td><?php echo $paperInfo -> PaperStatusName; ?> 
	  <?php if ($paperInfo -> PaperStatusName == "Accepted") { ?>
	  as <?php echo $curtype -> PresentationTypeName; ?>
	  <?php } ?>
	  </td>
    </tr>
	<tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td><strong>Change To:</strong></td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="2"><table width="40%">
	  	  <?php if ($paperInfo -> PaperStatusName != "Marginal") { ?>
          <tr> 
            <td><label> 
              <input name="paperstatus" type="radio" value="Marginal" <?php if($_GET["status"] == "Marginal") echo "checked"; else if($_GET["status"] != "Accepted") echo "checked"; ?>>
              Marginal</label></td>
          </tr>
		  <?php } ?>
	  	  <tr> 
            <td><label> 
              <input type="radio" name="paperstatus" value="Accepted" <?php if($_GET["status"] == "Accepted" || $paperInfo -> PaperStatusName == "Accepted" || $_GET["status"] = "Marginal") echo "checked"; ?>>
              Accept</label>
			  as
			  <select name="PresentationType">
			  <?php 
			  $types = get_presentation_types();
			  foreach ($types as $type) {  ?>
				<option value="<?php echo $type -> PresentationTypeID ?>" <?php if (get_presentation_type_for_paper($paperInfo -> PaperID) == $type -> PresentationTypeID) echo "selected" ?>>
				<?php echo $type -> PresentationTypeName ?>
				</option>
			  <?php } ?>
			  </select>
			  </td>
          </tr>
		  <?php if ($paperInfo -> PaperStatusName != "Rejected") { ?>
          <tr> 
            <td><label> 
              <input type="radio" name="paperstatus" value="Rejected" <?php if($_GET["status"] == "Rejected") echo "checked"; ?>>
              Reject</label></td>
          </tr>
		  <?php } ?>		  
        </table></td>
    </tr>
    <tr> 
      <td colspan="2">&nbsp;</td>
    </tr>
	<?php
		if (areSessionTracksEnabled()) {
			echo '<tr><td colspan="2">SessionTrack: <select name="SessionTrackID">';
			$tracks = get_SessionTracks();
			foreach ($tracks as $track) {
				echo '<option value="' . $track->SessionTrackID . '"';
				if ($SessionTrack == $track->SessionTrackName)
					echo 'selected="selected"';
				echo '>' . $track->SessionTrackName;
				echo '</option>';
			}
			echo '</select></td></tr>' . "\n";
		}
	?>
    <tr> 
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="2"><input type="submit" name="Submit" value="Submit">
        <input name="Submit" type="submit" id="Submit" value="Cancel"></td>
    </tr>
  </table>
</form>
<?php			
	do_html_footer( &$err_message );
?>

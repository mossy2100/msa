<?php 
	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");	
	session_start();

	$err_message = " Unable to process your request due to the following problems: <br>\n" ;

	do_html_header("View SessionTrack(s)");

	//Establish connection with database
	$db = adodb_connect();

	if (!$db) {
		echo '<strong style="color:red">Could not connect to database server - please try later.</strong>';
		return;
	}
?>

<p>Use these tracks, if the Sessions should not be based on the Tracks, but on other categories.</p>
<p>This frequently happens, if too few papers are submitted, or if one paper can belong to different tracks.<br> After reviewing, new categories are established (called SessionTracks) and papers are assigned to them (in addition to the tracks). They will be automatically scheduled to appropriate slots in the sessions.</p>

<?php
	if (($_GET["action"] === "add") && (count($_POST) === 0))
		add();
	else if ($_GET["action"] === "add")
		add2();
	else if (($_GET["action"] === "del") && (count($_POST) === 0))
		del();
	else if ($_GET["action"] === "del")
		del2();
	else if (($_GET["action"] === "edit") && (count($_POST) === 0))
		edit();
	else if ($_GET["action"] === "edit")
		edit2();
	else
		view();

	do_html_footer();
	return;

	function view()
	{?>
		<table width="100%" border="0" cellspacing="0" cellpadding="1">
		<tr> 
			<td>&nbsp;</td>
			<td align="right"><a href="/conference/admin/SessionTracks.php?action=add">Add new SessionTrack</a></td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr> 
			<td colspan="2"><?php $numSessionTracks = display_SessionTrack_table(); ?></td>
		</tr>
		<tr> 
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		</table>
		<?php
		if ($numSessionTracks == 0) {
			echo '<p style="color:lime">Attention: SessionTracks are currently not in use. IAPRCommence will continue to use Tracks (normal operation).</p>';
		}
	}

	function display_SessionTrack_table()
	{
		$categorySQL = "SELECT * FROM " . $GLOBALS["DB_PREFIX"] . "SessionTrack";
		$categoryResult = $GLOBALS["db"]->Execute($categorySQL);

		if(!$categoryResult) {
			echo '<strong style="color:red">Could not retrieve the SessionTrack information - please try again later</strong>';
			return 0;
		}

		?>
		<table width="100%" border="1" cellspacing="2" cellpadding="2">
		<tr>
		<td width="70%"><strong>SessionTrack</strong></td>
		<td width="15%">&nbsp;</td>
		<td width="15%">&nbsp;</td>
		</tr>
		<?php

		while($categoryInfo = $categoryResult -> FetchNextObj()){
			echo "<tr>";
				echo "<td>".$categoryInfo -> SessionTrackName."</td>";
				echo '<td><a href="/conference/admin/SessionTracks.php?action=edit&ID='.$categoryInfo -> SessionTrackID.'">edit</a></td>';
				echo '<td><a href="/conference/admin/SessionTracks.php?action=del&ID='.$categoryInfo -> SessionTrackID.'">delete</a></td>';
			echo "</tr>\n";
		}

		echo "</table>";
		return $categoryResult->RowCount();
	}

	function add()
	{?>
		<br>
		<form name="form1" method="post" action="SessionTracks.php?action=add">
		<table width="100%" border="0" cellpadding="1" cellspacing="0">
		<tr> 
			<td width="15%">SessionTrack Name:</td>
			<td width="85%"><input name="catName" type="text" id="catname" size="50" maxlength="50"></td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr> 
			<td colspan="2"><input type="submit" name="submit" value="Submit">
			<input name="submit" type="submit" value="Cancel"></td>
		</tr>
		<tr> 
			<td colspan="2"><hr></td>
		</tr>
		<tr> 
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2"><?php $numSessionTracks = display_SessionTrack_table(); ?></td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		</table>
		</form>
	<?php }

	function add2()
	{
		if ($_POST["submit"] === "Submit")
			if (!add_new_SessionTrack( $_POST["catName"] ))
				echo '<strong style="color:red">Could not insert the SessionTrack information - please try again later</strong>';

		view();
	}

	function add_new_SessionTrack( $name )
	{
		$name = db_quote( $GLOBALS["db"], $name ); // escape characters
		$insertSQL = "INSERT INTO " . $GLOBALS["DB_PREFIX"] . "SessionTrack(SessionTrackName)";
		$insertSQL .= " VALUES($name)";
		$insertResult = $GLOBALS["db"]->Execute($insertSQL);

		if(!$insertResult)
			return false;
		else
			return true;
	}

	function edit()
	{
		$ID = intval( $_GET["ID"] );

		$sql = "SELECT SessionTrackName FROM " . $GLOBALS["DB_PREFIX"] . "SessionTrack";
		$sql .= " WHERE SessionTrackID = $ID";
		$result = $GLOBALS["db"]->Execute($sql);
		if ($result && ($info = $result->FetchNextObj()))
			$name = $info->SessionTrackName;

		echo '<br><form name="form1" method="post" action="SessionTracks.php?action=edit&ID='.$_GET["ID"].'">';
	?>
		<table width="100%" border="0" cellpadding="1" cellspacing="0">
		<tr> 
			<td width="15%">SessionTrack Name:</td>
			<td width="85%"><input name="catName" type="text" id="catname" size="50" maxlength="50" value="<?php echo $name; ?>"></td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr> 
			<td colspan="2"><input type="submit" name="submit" value="Submit">
			<input name="submit" type="submit" value="Cancel"></td>
		</tr>
		<tr> 
			<td colspan="2"><hr></td>
		</tr>
		<tr> 
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2"><?php display_SessionTrack_table(); ?></td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		</table>
		</form>
	<?php }

	function edit2()
	{
		if ($_POST["submit"] === "Submit")
			if (!update_SessionTrack( $_GET["ID"], $_POST["catName"] ))
				echo '<strong style="color:red">Could not update the SessionTrack information - please try again later</strong>';

		view();
	}

	function update_SessionTrack( $ID, $name )
	{
		$ID = intval( $ID );
		$updateSQL = "UPDATE " . $GLOBALS["DB_PREFIX"] . "SessionTrack";
		$updateSQL .= " SET SessionTrackName = " . db_quote( $GLOBALS["db"], $name );
		$updateSQL .= " WHERE SessionTrackID = $ID";
		$updateResult = $GLOBALS["db"]->Execute($updateSQL);

		if(!$updateResult)
			return false;
		else
			return true;
	}

	function del()
	{
		$ID = intval( $_GET["ID"] );

		$sql = "SELECT SessionTrackName FROM " . $GLOBALS["DB_PREFIX"] . "SessionTrack";
		$sql .= " WHERE SessionTrackID = $ID";
		$result = $GLOBALS["db"]->Execute($sql);
		if ($result && ($info = $result->FetchNextObj()))
			$name = $info->SessionTrackName;

		echo '<br><form name="form1" method="post" action="SessionTracks.php?action=del&ID='.$_GET["ID"].'">';
	?>
		<table width="100%" border="0" cellpadding="1" cellspacing="0">
		<tr> 
			<td colspan="2">Below is the track that will be deleted. Press confirm to proceed.</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr> 
			<td width="15%">SessionTrack Name:</td>
			<td width="85%"><?php echo $name; ?></td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr> 
			<td colspan="2"><input type="submit" name="submit" value="Confirm" style="background-color:red">
			<input name="submit" type="submit" value="Cancel"></td>
		</tr>
		<tr> 
			<td colspan="2"><hr></td>
		</tr>
		<tr> 
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2"><?php display_SessionTrack_table(); ?></td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		</table>
		</form>
	<?php }

	function del2()
	{
		if ($_POST["submit"] === "Confirm")
			if (!delete_SessionTrack( $_GET["ID"] ))
				echo '<strong style="color:red">Could not update the SessionTrack information - please try again later</strong>';

		view();
	}

	function delete_SessionTrack( $ID )
	{
		$ID = intval( $ID );
		$deleteSQL = "DELETE FROM " . $GLOBALS["DB_PREFIX"] . "SessionTrack";
		$deleteSQL .= " WHERE SessionTrackID = $ID";
		$deleteResult = $GLOBALS["db"]->Execute($deleteSQL);

		if(!$deleteResult)
			return false;
		else
			return true;
	}
?>

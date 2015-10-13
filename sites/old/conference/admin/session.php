<?php
$php_root_path = "..";
$privilege_root_path = "/admin" ;
require_once("includes/include_all_fns.inc");
session_start();


$err_message = " Unable to process your request due to the following problems: <br>\n" ;

function get_chair_usernames()
{
	//Establish connection with database
	$db = adodb_connect();
	
	if (!$db){
		echo "Could not connect to database server - please try later.";
		exit;
	}	
	
	$sql =  "SELECT * FROM ".$GLOBALS["DB_PREFIX"]."Member AS M, ";
	$sql .=  $GLOBALS["DB_PREFIX"]."Registration as R ";
	$sql .=	"WHERE M.RegisterID = R.RegisterID ";
	//$sql .= "WHERE PrivilegeTypeID > 1 ";
	$sql .= "ORDER BY M.PrivilegeTypeID DESC, R.LastName ASC";
	$result = $db -> Execute($sql);
	
	$users = array();
	while ($user = $result -> FetchNextObj())
		$users[] = $user;
	return $users;
}

$sessionID = $_GET["id"];

// Perform session deletion
if ($_POST["Submit"] == "Delete")
{
	delete_session($_POST["SessionID"]);
}
if ($_POST["Submit"] == "Delete" || $_POST["Submit"] == "Cancel")
{
	$url = "Location: sessions.php";
	header($url);
}

// Confirm session deletion
if ($_GET["delete"])
{
	$session = get_session_info($sessionID);
	$track = get_track_info($session -> TrackID);
	$type = get_presentation_info($session -> PresentationTypeID);
	$sessionSlotsInfo = get_session_slots_info($session -> SessionID);
	do_html_header("Delete Session" , &$err_message);
?>
	<br />
	<table align="center">
	<tr>
	<td>
	<strong>#<?php echo $session -> SessionID ?> - 
	<?php echo $session -> SessionName ?></strong><br />
	<?php echo $track -> TrackName ?> - 
	<?php echo $type -> PresentationTypeName ?><br />
	<br />
	
	<strong>Start Time : </strong>
	<?php echo format_date($settingsInfo -> DateFormatShort, $session -> StartTime) ?>
	@
	<?php echo format_date("g:i a", $session -> StartTime) ?>
	<br />
	
	<strong>Finish Time : </strong>
	<?php echo format_date($settingsInfo -> DateFormatShort, $session -> EndTime) ?>
	@
	<?php echo format_date("g:i a", $session -> EndTime) ?>
	<br />
	
	<strong>Slots : </strong>
	<?php echo count($sessionSlotsInfo -> Slots)." / ".($sessionSlotsInfo -> MaxSlots); ?>
	<br />
	<br />
	
	<form name="frmEdit" method="post" action="session.php">
		<input type="hidden" name="SessionID" value="<?php echo $session -> SessionID ?>">
		<input type="submit" name="Submit" value="Delete">
		<input type="submit" name="Submit" value="Cancel">
	</form>
	</td>
	</tr>
	</table>
<?php
	exit;
}

$error_array = array();
if ($_POST["Submit"] == "Submit")
{
	check_form( $_POST , &$error_array , array("SessionID") );
	if (count($error_array)==0)
	{
		$session = new Session();
		$session -> SessionID = $_POST["SessionID"];
		$session -> SessionName = $_POST["SessionName"];
		$session -> TrackID = $_POST["TrackID"];
		if (array_key_exists( "SessionTrackID", $_POST))
			$session -> SessionTrackID = $_POST["SessionTrackID"];
		$session -> PresentationTypeID = $_POST["PresentationTypeID"];
		
		$session -> StartTime = sprintf("%s %02d:%02d:00",
									$_POST["StartDate"],
									$_POST["StartHour"] % 12 +
									(($_POST["StartMeridiem"]=="pm")
									? 12 : 0),
									$_POST["StartMinute"]);
		$session -> EndTime = sprintf("%s %02d:%02d:00",
									$_POST["EndDate"],
									$_POST["EndHour"] % 12 +
									(($_POST["EndMeridiem"]=="pm")
									? 12 : 0),
									$_POST["EndMinute"]);
		$session -> RoomID = $_POST["RoomID"];
		$session -> ChairID = $_POST["ChairID"];
		if ($_POST["SessionID"] && !$_GET["duplicate"])
			$result = update_session($session);
		else {
			$session -> SessionID = 0;
			$result = add_session($session);
		}
		$url = "Location: sessions.php";
		header($url);
	}
}

$vars = array();
if ($sessionID)
{
	$session = get_session_info($sessionID);
	$vars["SessionID"] = $session -> SessionID;
	$vars["SessionName"] = $session -> SessionName;
	$vars["TrackID"] = $session -> TrackID;
	$vars["SessionTrackID"] = $session -> SessionTrackID;
	$vars["PresentationTypeID"]	= $session -> PresentationTypeID;
	
	$startTime = get_date_obj($session -> StartTime);
	$vars["StartDate"] = $startTime -> Date;
	// Transform for am/pm, so that goes 12, 1, 2, etc.
	$vars["StartHour"] = (11 + $startTime -> Hour) % 12 + 1;
	$vars["StartMinute"] = $startTime -> Minute;
	$vars["StartMeridiem"] = ($startTime -> Hour < 12) ? "am" : "pm";
	
	$endTime = get_date_obj($session -> EndTime);
	$vars["EndDate"] = $endTime -> Date;
	// Transform for am/pm, so that goes 12, 1, 2, etc.
	$vars["EndHour"] = (11 + $endTime -> Hour) % 12 + 1;
	$vars["EndMinute"] = $endTime -> Minute;
	$vars["EndMeridiem"] = ($endTime -> Hour < 12) ? "am" : "pm";
	
	$vars["RoomID"] = $session -> RoomID;
	$vars["ChairID"] = $session -> ChairID;
} else {
	$vars = $_POST;
}

/* Magic quotes make feeding the info back in a bit of a problem */
if (get_magic_quotes_gpc() && $_POST["SessionName"] != NULL)
	$vars["SessionName"] = stripslashes($_POST["SessionName"]);

if ($sessionID)
{
	do_html_header("Edit Session" , &$err_message);
} else {
	do_html_header("Add Session" , &$err_message);
}
?>

<script language="JavaScript" src="/conference/admin/script/popcalendar.js"></script> 
<form name="frmEdit" method="post" action="session.php<?php if ($sessionID) echo "?id=".$sessionID; if (array_key_exists( "duplicate", $_GET )) echo '&duplicate=1'; ?>">
<table>
	<tr>
		<td>
		<input type="hidden" name="SessionID" value="<?php echo $sessionID ?>">
		Session Name
		</td>
		<td>
		<input name="SessionName" type="text" value="<?php if ($vars["SessionName"]) echo $vars["SessionName"] ?>">
		<font color="#FF0000"><?php echo $error_array["SessionName"][0]; ?></font>
		</td>
	</tr>
<?php if (!areSessionTracksEnabled()) { ?>
	<tr>
		<td>
		Track
		</td>
		<td>
		<select name="TrackID">
		<?php 
		$tracks = get_tracks();
		foreach ($tracks as $track) { ?>
			<option value="<?php echo $track -> TrackID ?>" <?php if ($vars["TrackID"]==($track -> TrackID)) echo "selected" ?>>
				<?php echo $track -> TrackName ?>
			</option>
		<?php } ?>
		</select>
		<font color="#FF0000"><?php echo $error_array["TrackID"][0]; ?></font>
		</td>
	</tr>
<?php } else { ?>
	<tr>
		<td>
		<span style="color:grey">Track</span>
		</td>
		<td>
		<select name="TrackID_" disabled="disabled" style="color:grey">
		<?php
		$track = get_track_info($vars["TrackID"]);
		echo '<option value="'.$vars["TrackID"].'" selected="selected">'.$track->TrackName.'</option>'."\n";
		?>
		</select>
		<?php
		echo '<input type="hidden" name="TrackID" value="'.$vars["TrackID"].'">'."\n"; // browsers do not send values from disabled items
		?>
		</td>
	</tr>
	<tr>
		<td>
		SessionTrack
		</td>
		<td>
		<select name="SessionTrackID">
		<?php 
		$tracks = get_SessionTracks();
		foreach ($tracks as $track) { ?>
			<option value="<?php echo $track->SessionTrackID ?>" <?php if ($vars["SessionTrackID"]==($track->SessionTrackID)) echo "selected" ?>>
				<?php echo $track->SessionTrackName ?>
			</option>
		<?php } ?>
		</select>
		<font color="#FF0000"><?php echo $error_array["SessionTrackID"][0]; ?></font>
		</td>
	</tr>
<?php } ?>
	<tr>
		<td>
		Presentation Type
		</td>
		<td>
		<select name="PresentationTypeID">
		<?php 
		$types = get_presentation_types();
		foreach ($types as $type) { ?>
			<option value="<?php echo $type -> PresentationTypeID ?>" <?php if ($vars["PresentationTypeID"]==($type -> PresentationTypeID)) echo "selected" ?>>
				<?php echo $type -> PresentationTypeName ?>
			</option>
		<?php } ?>
		</select>
		<font color="#FF0000"><?php echo $error_array["PresentationTypeID"][0]; ?></font>
		</td>
	</tr>
	<tr>
		<td>
		Start Time
		</td>
		<td>
		<input name="StartDate" type=text size=10 value="<?php if ($vars["StartDate"]) echo $vars["StartDate"] ?>" onclick='showCalendar(this, this, "yyyy-mm-dd","en",1)'>
        @
		<select name="StartHour">
		<?php for ($i = 1 ; $i <= 12 ; $i++) { ?>
			<option <?php if ($vars["StartHour"]==$i) echo "selected" ?>>
			<?php echo $i ?>
			</option>
		<?php } ?>
		</select>
		:
		<select name="StartMinute">
		<?php for ($i = 0 ; $i < 60 ; $i =$i + 5) { ?>
			<option <?php if ($vars["StartMinute"]==$i) echo "selected" ?>>
			<?php echo sprintf("%02d",$i) ?>
			</option>
		<?php } ?>
		</select>
		<select name="StartMeridiem">
		<?php $meridiams = array("am","pm");
		foreach ($meridiams as $m) { ?>
			<option <?php if ($vars["StartMeridiem"]==$m) echo "selected" ?>>
			<?php echo $m ?>
			</option>
		<?php } ?>
		</select>
		<font color="#FF0000"><?php echo $error_array["StartDate"][0]; ?></font>
		</td>
	</tr>
	<tr>
		<td>
		Finish Time
		</td>
		<td>
		<input name="EndDate" type=text size=10 value="<?php if ($vars["EndDate"]) echo $vars["EndDate"] ?>" onclick='showCalendar(this, this, "yyyy-mm-dd","en",1)'>
        @
		<select name="EndHour">
		<?php for ($i = 1 ; $i <= 12 ; $i++) { ?>
			<option <?php if ($vars["EndHour"]==$i) echo "selected" ?>>
			<?php echo $i ?>
			</option>
		<?php } ?>
		</select>
		:
		<select name="EndMinute">
		<?php for ($i = 0 ; $i < 60 ; $i = $i + 5) { ?>
			<option <?php if ($vars["EndMinute"]==$i) echo "selected" ?>>
			<?php echo sprintf("%02d",$i) ?>
			</option>
		<?php } ?>
		</select>
		<select name="EndMeridiem">
		<?php $meridiams = array("am","pm");
		foreach ($meridiams as $m) { ?>
			<option <?php if ($vars["EndMeridiem"]==$m) echo "selected" ?>>
			<?php echo $m ?>
			</option>
		<?php } ?>
		</select>
		<font color="#FF0000"><?php echo $error_array["EndDate"][0]; ?></font>
		</td>
	</tr>
	<tr>
		<td>
		Room
		</td>
		<td>
		<select name="RoomID">
		<?php 
		$rooms = get_rooms();
		foreach ($rooms as $room) { ?>
			<option value="<?php echo $room -> RoomID ?>" 
			<?php if ($vars["RoomID"]==$room -> RoomID) echo "selected" ?>>
				<?php echo $room -> RoomName ?>
			</option>
		<?php } ?>
		</select>
		<font color="#FF0000"><?php echo $error_array["RoomID"][0]; ?></font>
		</td>
	</tr>
	<tr>
		<td>
		Chairperson
		</td>
		<td>
		<select name="ChairID">
		<?php 
		$users = get_chair_usernames();
		foreach ($users as $user) { ?>
			<option value="<?php echo $user -> RegisterID ?>"
			<?php if ($vars["ChairID"]==$user -> RegisterID) echo "selected" ?>>
				<?php $chairInfo = get_member_info_with_id($user -> RegisterID); $fullName = getMemberFullName($chairInfo->MemberName); echo $fullName ?>
			</option>
		<?php } ?>
		</select>
		<font color="#FF0000"><?php echo $error_array["ChairID"][0]; ?></font>
		</td>
	</tr>
	<tr>
		<td>
		<input type="submit" name="Submit" value="Submit">
		</td>
		<td>
		
		</td>
	</tr>
</table>
</form>

<?php 
do_html_footer(&$err_message); 
?>

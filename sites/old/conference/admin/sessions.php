<?php
$php_root_path = "..";
$privilege_root_path = "/admin" ;
require_once("includes/include_all_fns.inc");
session_start();

$err_message = " Unable to process your request due to the following problems: <br>\n" ;

$sessions = get_sessions($_GET["sortby"],$_GET["desc"]);

$settingsInfo = get_Conference_Settings();

do_html_header("Conference Sessions" , &$err_message);

if (array_key_exists( "autoschedule", $_GET )) {
	autoschedule_waiting_papers();
	echo '<strong style="color:lime;">All waiting papers were autoscheduled</strong><br><br>';
}
if (array_key_exists( "clearschedule", $_GET )) {
   	// loop over all papers
	//~ $db = adodb_connect();
	//~ if (!$db){
		//~ echo '<strong style="color:red;">Could not connect to database server - please try later.</strong>';
		//~ return;
	//~ }
	//~ $sql = "SELECT SL.PaperID, S.PresentationTypeID FROM " . $GLOBALS["DB_PREFIX"] . "SessionSlot SL, " . $GLOBALS["DB_PREFIX"] . "Session S";
	//~ $sql .= " WHERE SL.SessionID = S.SessionID";
	//~ $result = $db -> Execute($sql);
	//~ while ($result && ($info = $result->FetchNextObj())) {
		//~ $PaperID = $info->PaperID;
		//~ $db->Execute( "DELETE FROM SessionSlot WHERE PaperID = $PaperID" );
		//~ assign_paper_presentation_type( $PaperID, $info->PresentationTypeID );
	//~ }
    if(!clearSchedule($err_message=""))
        echo '<strong style="color:red;">Error: schedules could not be cleared</strong><br><br>';                
    else
        echo '<strong style="color:lime;">All schedules were cleared</strong><br><br>';
}
?>
<a href="/conference/admin/session.php">Add new session</a> &nbsp; &nbsp; &nbsp; <a href="/conference/admin/sessions.php?autoschedule">
Allocate waiting papers to sessions</a> &nbsp; &nbsp; &nbsp; <a href="/conference/admin/sessions.php?clearschedule">
Deallocate all papers from sessions</a>
<table width="100%" border=0>
	<tr><td colspan=3><hr /></td></tr>
	<tr>
		<td colspan=2>
		Group by: 
		<?php 
		$sorts = array(
			"SessionName" => "Name",
			"TrackID" => "Track",
			"StartTime" => "Start Time",
			"RoomID" => "Room",
			"ChairID" => "Chairperson"
			);
		$firstField = true;
		foreach ($sorts as $fieldName => $fieldDesc) {
			if ($firstField) $firstField = false;
			else echo "&nbsp;|&nbsp;";
		?>
		<a href="/conference/admin/sessions.php?sortby=<">
		<img src="<?php echo $php_root_path ; ?>/images/up.gif" border=0>
		</a>
		<?php echo $fieldDesc ?>
		<a href="/conference/admin/sessions.php?sortby=<">
		<img src="<?php echo $php_root_path ; ?>/images/down.gif" border=0>
		</a>
		<?php } ?>
		</td>
	</tr>
	<tr><td colspan=3><hr /></td></tr>
<?php

foreach ($sessions as $session)
{
	$track = get_track_info($session -> TrackID);
	$SessionTrack = get_SessionTrack_info($session -> SessionTrackID);
	$type = get_presentation_info($session -> PresentationTypeID);
	$sessionSlotsInfo = get_session_slots_info($session -> SessionID);
	$chairInfo = get_member_info_with_id($session -> ChairID);
	$room = get_room_info($session -> RoomID);
?>
<tr>
	<td style="text-align: center; vertical-align: text-top">
	&nbsp;&nbsp;&nbsp;
	</td>
	<td>
	<strong><?php echo $session -> SessionName ?></strong><br />
	<i>
	<?php if (!areSessionTracksEnabled()) echo $track -> TrackName; else echo $SessionTrack->SessionTrackName; ?> - 
	<?php echo $type -> PresentationTypeName ?><br />
	</i>
	<strong>Chair:</strong>
	<?php 
	$fullName = getMemberFullName($chairInfo->MemberName); echo $fullName
	?>
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
	
	<strong>Room : </strong>
	<?php echo $room -> RoomName ?>
	<br />
	
	<strong>Slots : </strong>
	<?php echo count($sessionSlotsInfo -> Slots)." / ".($sessionSlotsInfo -> MaxSlots); ?>
	</td>
	<td>
	<a href="/conference/admin/session.php?id=<">
	Edit Session Details
	</a><br />
	<a href="/conference/admin/session.php?id=<">
	Duplicate Session
	</a><br />
	<a href="/conference/admin/session.php?id=<">
	Delete Session
	</a><br />
	<a href="/conference/admin/reschedule_session_slots.php?id=<">
	Manually Reschedule Session Slots
	</a><br />
	</td>
</tr>
<tr><td colspan=3><hr /></td></tr>
<?php 
} ?>
</table>

<?php 
do_html_footer(&$err_message); 
?>

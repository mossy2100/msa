<?php
$php_root_path = "..";
$privilege_root_path = "/admin" ;
require_once("includes/include_all_fns.inc");
session_start();

$err_message = " Unable to process your request due to the following problems: <br>\n" ;

if ($_POST["Submit"])
{
	if ($_POST["Submit"] == "Submit")
	{
		if ($_POST["RoomName"] != "")
		{
			if ($_POST["RoomID"])
			{
				// Update changed rooms
				update_room($_POST["RoomID"], $_POST["RoomName"], &$err_message);
			} else {
				// Add a new room if given
				add_new_room($_POST["RoomName"]);
			}
		}
	}
	else if ($_POST["Submit"] == "Confirm")
	{
		delete_room($_POST["RoomID"]);
	}
	
}

$rooms = get_rooms();

?>
<?php
if ($_GET["add"]){
	do_html_header("Add Room" , &$err_message) ;
	?>
<!-- Start add section -->
<form name="frmEdit" method="post" action="rooms.php">
<div style="padding-top: 0.5cm; padding-bottom: 0.5cm">
<span>
Room Name:
</span>
<span style="padding-left: 1cm">
<input name="RoomName" type="text" size="40" maxlength="50" value=""> 
</span>
</div>
<div>
<input type="submit" name="Submit" value="Submit">
<input type="reset" name="Reset" value="Undo Changes">
</div>
</form>
<hr />
<!-- Finish add section -->
	
	<?php
} else if ($_GET["edit"])
{
	do_html_header("Edit Room" , &$err_message) ;
	$room = get_room_info($_GET["edit"]);
	?>
<!-- Start edit section -->
<form name="frmEdit" method="post" action="rooms.php">
<div style="padding-top: 0.5cm; padding-bottom: 0.5cm">
<span>
Room Name:
</span>
<span style="padding-left: 1cm">
<input name="RoomID" type="hidden" value="<?php echo $room -> RoomID ?>">
<input name="RoomName" type="text" size="40" maxlength="50" value="<?php echo $room -> RoomName; ?>"> 
</span>
</div>
<div>
<input type="submit" name="Submit" value="Submit">
<input type="reset" name="Reset" value="Undo Changes">
</div>
</form>
<hr />
<!-- Finish edit section -->
	<?php
} else if ($_GET["delete"]) {
	do_html_header("Delete Room" , &$err_message) ;
	$room = get_room_info($_GET["delete"]);
	$sessions = sessions_in_room($room -> RoomID);
	if (count($sessions) > 0)
	{
?>
<!-- Start "cannot delete" -->
<div style="padding-top: 0.5cm; padding-bottom: 0.5cm">
Cannot delete "<?php echo $room -> RoomName ?>" because the following sessions
are still using it:
</div>
<div>
<table>
<?php foreach ($sessions as $session) { ?>
<tr>
<td><b>#<?php echo $session -> SessionID ?></b></td>
<td><?php echo $session -> SessionName ?></td>
</tr>
<?php } ?>
</table>
</div>
<div style="padding-top: 0.5cm; padding-bottom: 0.5cm">
<a href="/conference/admin/rooms.php">Back</a>
</div>
<hr />
<!-- Finish "cannot delete" -->
	<?php
	} else {
	?>
<!-- Start delete section -->
<form name="frmEdit" method="post" action="rooms.php">
<div>
Below is the room that will be deleted. Press confirm to proceed.
</div>
<div style="padding-top: 0.5cm; padding-bottom: 0.5cm">
<span>
Room Name:
</span>
<span style="padding-left: 1cm">
<input name="RoomID" type="hidden" value="<?php echo $room -> RoomID ?>">
<?php echo $room -> RoomName ?>
</span>
</div>
<div>
<input type="submit" name="Submit" value="Confirm">
<input type="submit" name="Submit" value="Cancel">
</div>
</form>
<hr />
<!-- Finish delete section -->
	<?php	}
} else {
	do_html_header("Available Rooms" , &$err_message) ;
	?>
<p style="text-align: right">
<a href="/conference/admin/rooms.php?add=1">
Add Room
</a>
</p>
	<?php
}

?>

<!-- Start rooms table -->
<table align="center" width="100%" border="1" cellspacing="2" cellpadding="1">
	<tr>
		<td>
		<strong>Room Name</strong>
		</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
<?php foreach ($rooms as $room) { ?>
	<tr>
		<td>
		<?php echo $room -> RoomName ?>
		</td>
		<td>
		<a href="/conference/admin/rooms.php?edit=<">
		Edit
		</a>
		</td>
		<td>
		<a href="/conference/admin/rooms.php?delete=<">
		Delete
		</a>
		</td>
	</tr>
<?php } ?>

</table>
<p>
<?php 

do_html_footer(&$err_message); 
?>

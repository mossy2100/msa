<?php include("../includes/connection.php"); ?>

<?php 

$date=$_POST['txtDate'];
$type=$_POST['txtType'];
$title=$_POST['txtTitle'];
$events_edit_title2=$_POST['events_edit_title2'];
$text=$_POST['txtText'];
$link=$_POST['txtLink'];

$events_edit=$_POST['events_edit']; echo $events_edit;

$SQL="UPDATE events SET date='".$date."', type='".$type."', title='".$title."', text='".$text."', link='".$link."' 
WHERE title='".$events_edit_title2."'";

mysql_query($SQL);
?>

<br>

You have successfully added a new event.<br><br>

<a href="admin_events.php">Click here to add another entry</a><br>
(Note that it can sometimes take a few minutes for a new event to appear on the site.)
<br><br>

<a href="/admin/admin_main.php">Click here to return to the main admin page</a><br><br>

<a href="../events.php">Click here to see how the entry looks on the Events Page</a><br><br>

<a href="../logout.php">Click here to log out</a><br><br>






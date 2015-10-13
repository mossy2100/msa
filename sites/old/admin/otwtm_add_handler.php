<?php include("../banner.php"); ?>
<?php include("../includes/connection.php"); ?>

<span class="bodytext">

<?php
$add_date=$_POST['otwtm_add_date']; 
$add_category=$_POST['otwtm_add_category']; 
$add_title=$_POST['otwtm_add_title']; 
$add_link=$_POST['otwtm_add_link'];
$add_comment=$_POST['otwtm_add_comment']; 


$SQL="INSERT into on_the_way_to_mars (date, category, title, link, comment) VALUES ('$add_date', '$add_category', '$add_title', '$add_link', '$add_comment')";
mysql_query($SQL);
?>

<br>

You have successfully added a new item. Note that you may need to click 'refresh' 
on your browser in order to see the new entry.<br><br>

<a href="/admin/admin_main.php">Click here to add another entry</a><br>

<br><br>

<a href="/admin/admin_main.php">Click here to return to the main admin page</a><br><br>

<a href="/on_the_way_to_mars.php">Click here to see how the entry looks on the Events Page</a><br><br>

<br><br>

</span>

<?php include("../bottom.php"); ?>





<?php include("includes/banner.php"); ?>
<?php include("includes/menu.php"); ?>
<?php include("includes/stats_collection.php"); ?>
<?php include("includes/connection.php"); ?>
<?php include("includes/user_authentication.php"); ?>

	<td  width="83% "valign="top" align="left">

		<table align="right" cellspacing=0 cellpadding=5 bordercolor="#FF9900"
					bgcolor=white style='width:100%; mso-cellspacing:0cm; background:white; 
					border:outset #FF9900 1pt; mso-padding-alt:1.0pt 1.0pt 1.0pt 1.0pt'>
     		<tr>      	
			<td>
                 <span class="blackheading">Done!</span><br>
                 <span class="bodytext"><br>


<?php 

$date=date('Y-m-d');
$heading=$_POST['txtHeading'];
$para1=$_POST['txtPara1'];
$para2=$_POST['txtPara2'];
$link=$_POST['txtLink'];

$SQL="INSERT into news (date, heading, para1, para2, link) VALUES ('$date', '$heading', '$para1', '$para2', '$link')";
mysql_query($SQL);
?>

<br>

You have successfully added a new news item.<br><br>

<a href="/marsupial/admin_news.php">Click here to add another news item</a><br>
<br>

<a href="admin_main.php">Click here to return to the main admin page</a><br><br>

<a href="/main.php">Click here to see how the entry looks on the main page</a><br><br>

<a href="/marsupial/logout.php">Click here to log out</a><br><br>




				To be completed<br><br>

<br>
				</span>
			</td>
             </tr>
        </table>


</td>
</tr>
</table>

<?php include("includes/footer.php"); ?>
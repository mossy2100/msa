<?php include("includes/banner.php"); ?>
<?php include("includes/menu.php"); ?>
<?php include("includes/connection.php"); ?>
<?php include("includes/stats_collection.php"); ?>

	<td  width="83% "valign="top" align="left">

		<table align="right" cellspacing=0 cellpadding=5 bordercolor="#FF9900"
					bgcolor=white style='width:100%; mso-cellspacing:0cm; background:white; 
					border:outset #FF9900 1pt; mso-padding-alt:1.0pt 1.0pt 1.0pt 1.0pt'>
     		<tr>      	
			<td>
                 <span class="blackheading">Done!</span><br>
                 <span class="bodytext"><br>

				<?php 

$name=$_POST['txtName'];
$url=$_POST['txtUrl'];

$SQL="INSERT into links (url, name) VALUES ('$url', '$name')";
mysql_query($SQL);

?>

<br>

You have successfully added a new link<br><br>

<a href="/marsupial/admin_links.php">Click here to add another entry</a><br>
(Note that it can sometimes take a few minutes for a new link to appear on the site.)
<br><br>

<a href="admin_main.php">Click here to return to the main admin page</a><br><br>

<a href="/marsupial/resources.php">Click here to see how the link looks on the Resources Page</a><br><br>

<a href="/marsupial/logout.php">Click here to log out</a><br><br>


<br>
				</span>
			</td>
             </tr>
        </table>


</td>
</tr>
</table>

<?php include("includes/footer.php"); ?>
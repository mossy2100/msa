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


$name=$_POST['txtName'];
$role=$_POST['txtRole'];
$organisation=$_POST['txtOrganisation'];
$email=$_POST['txtEmail'];
$phone=$_POST['txtPhone'];
$mobile=$_POST['txtMobile'];
$skype_username=$_POST['txtSkype_Username'];
$status=$_POST['txtStatus'];


$SQL="INSERT into team (name, role, organisation, email, phone, mobile, skype_username, status) VALUES ('$name', '$role', '$organisation', '$email', '$phone', '$mobile', '$skype_username', '$status')";
mysql_query($SQL);
?>

<br>

You have successfully added a new team member.<br><br>

<a href="/marsupial/admin_team.php">Click here to add another team member</a><br>
<br>

<a href="admin_main.php">Click here to return to the main admin page</a><br><br>

<a href="/marsupial/contact.php">Click here to see how the entry looks on the contacts page</a><br><br>

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
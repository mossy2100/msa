<?php
//if they haven't pressed the submit button, then show the form
if (!isset($_POST['submit']))
{
?>

<?php include("banner.php"); ?>

<table width=664 border=0 cellpadding=0>
 <tr>
  <td valign=top>
<div style="margin-left:15">



	<td  width="83% "valign="top" align="left">

		<table align="right" cellspacing=0 cellpadding=5 bordercolor="#FF9900"
					bgcolor=black style='width:100%; mso-cellspacing:0cm; background:back; 
					border:outset #FF9900 1pt; mso-padding-alt:1.0pt 1.0pt 1.0pt 1.0pt'>
     		<tr> <center>     	
			<td align="center"><center><br>
                 <span class="headlines">Admin Login</span><br>
                 <span class="bodytext"><br><br>


<br>
<center>

<form action="<?php $_SERVER['PHP_SELF']?>" method="post">
<table align="center" border="1" cellspacing="0" cellpadding="3">
<tr><td><span class="bodytext">Username</span></td><td>
<input type="text" name="username" maxlength="40" class="frm_fields">
</td></tr>
<tr><td><span class="bodytext">Password</span></td><td>
<input type="password" name="password" maxlength="50" class="frm_fields">
<tr><td colspan="2" align="right">
<input type="submit" name="submit" value="Login">
</td></tr>
</table>
</form>
<br><br><br><br>

<?php
}
else //otherwise, let's process this stuff
{
if($_POST['username'] == "manager" && $_POST['password'] == "mrvr2mars") //if they got it right, let's go on
{
session_start();
session_register("mysessionvariable"); //set a variable for use later
$id = session_id(); //let's grab the session ID for those who don't have cookies
$url = "Location: admin_home.php?sid=" . $id;
header($url);
}
else //they got something wrong and we should tell them
{
?>


<?php include("includes/banner.php"); ?>
<?php include("includes/menu.php"); ?>
<?php include("includes/stats_collection.php"); ?>



<center><br>
                 <span class="headlines">Admin Login</span><br>
                 <span class="bodytext"><br><br>

						<span style="color:#ff0000;">

						Password/Username Is Invalid</span><br><br>

					<form action="<?$_SERVER['PHP_SELF']?>" method="post">
					<table align="center" border="1" cellspacing="0" cellpadding="3">
						<tr><td><span class="bodytext">Username</span></td><td>
						<input type="text" name="username" maxlength="40" class="frm_fields">
						</td></tr>
						<tr><td><span class="bodytext">Password</span></td><td>
						<input type="password" name="password" maxlength="50" class="frm_fields">
						<tr><td colspan="2" align="right">
						<input type="submit" name="submit" value="Login">
						</td></tr>
					</table>
					</form>
					<br><br><br>

<br><br>

<?php
}
}
?>

			</td>
             </tr>
        </table>

</div>

<?php include("bottom.php"); ?>

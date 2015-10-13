<?php
//if they haven't pressed the submit button, then show the form
if (!isset($_POST['submit']))
{
?>

<?php include("../banner.php"); ?>
<?php include("../includes/connection.php"); ?>

<div style="margin-left:15">

<TABLE CELLSPACING=0 BORDER=0 CELLPADDING=7 WIDTH=585>
  <TR>
    <TD WIDTH="6%" VALIGN="TOP">&nbsp;</TD>
    <TD WIDTH="94%" VALIGN="TOP" COLSPAN=2> 

<span class="headlines"> 
 Site Admin
</span> 

</TD>
  </TR>
</TABLE>


<TABLE CELLSPACING=0 BORDER=0 CELLPADDING=7 WIDTH=585>
  <TR> 
    <TD WIDTH="6%" VALIGN="TOP" height="100"></TD>
    <TD WIDTH="94%" VALIGN="TOP" height="100"> 
<br>
<span class="bodytext"><b>
Login
</b> </span>

<br>
<br>

<form action="<?$_SERVER['../PHP_SELF']?>" method="post">
<table align="left" border="1" cellspacing="0" cellpadding="3">
<tr><td>Username</td><td>
<input type="text" name="username" maxlength="40" class="frm_fields">
</td></tr>
<tr><td>Password</td><td>
<input type="password" name="password" maxlength="50" class="frm_fields">
<tr><td colspan="2" align="right">
<input type="submit" name="submit" value="Login">
</td></tr>
</table>
</form>


<?php
}
else //otherwise, let's process this stuff
{
if($_POST['username'] == "marsoz" && $_POST['password'] == "arkaringa") //if they got it right, let's go on
{
session_start();
session_register("mysessionvariable"); //set a variable for use later
$id = session_id(); //let's grab the session ID for those who don't have cookies
$url = "Location: admin_main.php?sid=" . $id;
header($url);
}
else //they got something wrong and we should tell them
{
?>

<html>
<head>
<title>My Login Form</title>
</head>
<body>

<?php include("../banner.php"); ?>
<?php include("../includes/connection.php"); ?>

<TABLE CELLSPACING=0 BORDER=0 CELLPADDING=7 WIDTH=585>
  <TR>
    <TD WIDTH="6%" VALIGN="TOP">&nbsp;</TD>
    <TD WIDTH="94%" VALIGN="TOP" COLSPAN=2> 

<span class="headlines"> 
 Site Admin
</span> 

</TD>
  </TR>
</TABLE>

<TABLE CELLSPACING=0 BORDER=0 CELLPADDING=7 WIDTH=585>
  <TR>
    <TD WIDTH="6%" VALIGN="TOP">&nbsp;</TD>
    <TD WIDTH="94%" VALIGN="TOP" COLSPAN=2> 

<span style="color:#ff0000;">

<br>
Password/Username Is Invalid</span><br><br>

<form action="<?$_SERVER['../PHP_SELF']?>" method="post">
<table align="left" border="1" cellspacing="0" cellpadding="3">
<tr><td>Username</td><td>
<input type="text" name="username" maxlength="40" class="frm_fields">
</td></tr>
<tr><td>Password</td><td>
<input type="password" name="password" maxlength="50" class="frm_fields">
<tr><td colspan="2" align="right">
<input type="submit" name="submit" value="Login">
</td></tr>
</table>
</form>

</TD>
  </TR>
</TABLE>


<?php
}
}
?>


</TD>
  </TR>
  <TR> 
    <TD WIDTH="6%" VALIGN="TOP"></TD>
    <TD WIDTH="94%" VALIGN="TOP"> 

        <br>
        <br>
        <br>
        <br>


      </TD>
  </TR>

</td></tr> </table>

</div>

<?php include("../bottom.php"); ?>

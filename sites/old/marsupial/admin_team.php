<?php include("includes/banner.php"); ?>
<?php include("includes/menu.php"); ?>
<?php include("includes/connection.php"); ?>
<?php include("includes/stats_collection.php"); ?>
<?php include("includes/user_authentication.php"); ?>

	<td  width="83% "valign="top" align="left">

		<table align="right" cellspacing=0 cellpadding=5 bordercolor="#FF9900"
					bgcolor=white style='width:100%; mso-cellspacing:0cm; background:white; 
					border:outset #FF9900 1pt; mso-padding-alt:1.0pt 1.0pt 1.0pt 1.0pt'>
     		<tr>      	
			<td><?php include("includes/admin_menu.php"); ?><br>
                 <span class="blackheading">Add to Team </span><br>
                 <span class="bodytext"><br>


<span class="bodytext"> This page is where you can add news item to the team section of the <a href="/contacts.php">contacts</a> page of the website. It is divided into current and past members.  
A list of all people in the team database (current and past) is displayed at the bottom of the page. 
Only their names are currently displayed online. <br>
<br>
<b>User Guidelines</b><br>
If there are no details available for any fields then leave them blank.  Avoid using single quotes in your entry text as this will result in the entry not being recorded. There is currently no function to enable you to remove or edit team member details, but  these will be added in future. 
<script> email ('gmmurphy','ozemail.com.au','Email Guy Murphy')</script> if you would like anything changed. 
<br><br>

<br><br>
<b>Add a Team Member</b><br>
<br>

<table align="center" width="100%">
<form action="admin_team_handler.php" method="post">

<tr align="left">
  <td width="26%" align="left"><div align="right" valign="top"><span class="bodytext"> 
    <div align="right">Name:</div>
  </div></td>
  <td width="74%"><input type="text" name="txtName" size="55"></td>
</tr>

<tr align="left">
  <td align="left"><div align="right"><span class="bodytext">Role:</div></td>
  <td width="74%"><input type="text" name="txtRole" size="55"></td>
</tr>

<tr align="left">
  <td align="left"><div align="right"><span class="bodytext">Organisation:</div></td>
  <td width="74%"><input type="text" name="txtOrganisation" size="55"></td>
</tr>

<tr align="left">
  <td align="left"><div align="right"><span class="bodytext">Email:</div></td>
  <td width="74%"><input type="text" name="txtEmail" size="55"></td>
</tr>

<tr align="left">
  <td align="left"><div align="right"><span class="bodytext">Phone:</div></td>
  <td width="74%"><input type="text" name="txtPhone" size="55"></td>
</tr>

<tr align="left">
  <td align="left"><div align="right"><span class="bodytext">Mobile: </div></td>
  <td width="74%"><input type="text" name="txtMobile" size="55"></td>
</tr>

<tr align="left">
  <td align="left"><div align="right"><span class="bodytext">Skype Username: </div></td>
  <td width="74%"><input type="text" name="txtSkype_Username" size="55"></td>
</tr>

<tr align="left">
  <td align="left"><span class="bodytext"><div align="right" valign="top">
    <div align="right"><label for="txtStatus">Status: </label></div>
  </div></td>
  <td width="74%">

<select name="txtStatus">
  <option value="current">current</option>
  <option value="past">past</option>
  </select>


</td>
</tr>

<tr align="left"><td height="54" colspan="2">
  <label>
    <input type="submit" value="Send"></label></p>
 </td></tr>

</form>
</table>
<br>
<br>
<hr>

<span class="blackheading"> 
Team Member Details<br>
</span>


<span class="bodytext"> 
<br>

<?php
$SQL="SELECT name, role, organisation, email, phone, mobile, skype_username, status FROM team ORDER BY name ASC";
$result=mysql_query($SQL); ?>

<?php
	while($data=mysql_fetch_assoc($result))
		{		

?> 

</span>

<span class="blacksubheading"><br><b>
<?php if(!$data['name']){echo"";} else {?><?PHP echo $data['name']; ?></b><br><?php } ?>
</span>

<span class="bodytext">
<?php if(!$data['role']){echo"";} else {?><?PHP echo $data['role']; ?><br><?php } ?>
</span>

<span class="bodytext">
<?php if(!$data['organisation']){echo"";} else {?><?PHP echo $data['organisation']; ?><br><?php } ?>
</span>

<span class="bodytext">
<?php if(!$data['email']){echo"";} else {?><?PHP echo $data['email']; ?><br><?php } ?>
</span>

<span class="bodytext">
<?php if(!$data['phone']){echo"";} else {?><?PHP echo $data['phone']; ?><br><?php } ?>
</span>

<span class="bodytext">
<?php if(!$data['mobile']){echo"";} else {?><?PHP echo $data['mobile']; ?><br><?php } ?>
</span>

<span class="bodytext">
<?php if(!$data['skype_username']){echo"";} else {?><?PHP echo $data['skype_username']; ?><br><?php } ?>
</span>

<span class="bodytext">
<?php if(!$data['status']){echo"";} else {?><i><?PHP echo $data['status']; ?> team member</i><br><?php } ?>
</span>


<?php } ?>

</span><br>


<br>
				</span>
			</td>
             </tr>
        </table>


</td>
</tr>
</table>

<?php include("includes/footer.php"); ?>
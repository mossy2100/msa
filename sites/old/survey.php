<?php include("banner.php"); ?>

<table width=664 border=0 cellpadding=0>
 <tr>
  <td valign=top>
<div style="margin-left:15">

<span class="headlines">
MSA Members Survey
</span></td>
</tr>

<tr><td>

<tr><td align="left">


<span class="bodytext">
<br>
<table align="center" width="585">
<form action="admin_projects_handler.php" method="post">

<tr align="left">
<td colspan="2">

</span>
<span class="subheadingitalic">
<b>GENERAL</b>
</span><br>

<span class="bodytext">
      <p><label for="txtText">How did you hear about MSA?</label><br>
      <textarea name="txtText" cols=65 rows=1 WRAP=virtual></textarea></p>
</td></tr>

<tr align="left">
<td colspan="2"><span class="bodytext">
      <p><label for="txtText">What inspired you to join MSA?</label><br>
      <textarea name="txtText" cols=65 rows=5 WRAP=virtual></textarea></p>
</td></tr>

<tr align="left">
<td colspan="2"><span class="bodytext">
      <p><label for="txtText">Are you a member of any other space advocacy groups? (If so, please list.)</label><br>
      <textarea name="txtText" cols=65 rows=2 WRAP=virtual></textarea></p>
</td></tr>

<tr align="left">
  <td width="400" align="left" colspan="2">
<span class="subheadingitalic"><br><br>
<b>SKILLS</b>
</span><br><br>

<tr align="left"><td colspan="2">
<span class="bodytext">
      <p><label for="txtText">Do you have any particular skills you would like to volunteer to MSA projects?</label><br>
      <textarea name="txtText" cols=65 rows=1 WRAP=virtual></textarea></p>
</td></tr>

<tr align="left"><td align="right">
<div align="right"><span class="bodytext"> Do you have any experience writing funding applications?</div></td>
  <td>
<select name="txtStatus">
<option value="yes">yes</option>
<option value="no">no</option>
</select></td>
</tr>

<tr align="left"><td align="right">
<div align="right"><span class="bodytext"> Are you a student?</div></td>
  <td>
<select name="txtStatus">
<option value="yes">yes</option>
<option value="no">no</option>
</select></td>
</tr>

<tr align="left"><td colspan="2">
<span class="bodytext">
      <p><label for="txtText">If so, what are you studying and where?</label><br>
      <textarea name="txtText" cols=65 rows=1 WRAP=virtual></textarea></p>
</td></tr>

<tr align="left"><td align="right">
<div align="right"><span class="bodytext"> Would you like to help produce MSA's newsletter?</div></td>
  <td align="left">
<select name="txtStatus">
<option value="yes">yes</option>
<option value="no">no</option>
</select></td>
</tr>

<tr align="left">
  <td width="400" align="left" colspan="2">
<span class="subheadingitalic"><br><br>
<b>EVENTS</b>
</span><br><br>

<tr align="left"><td align="right">
<div align="right"><span class="bodytext"> Have you ever been to a State Branch Meeting?</div></td>
  <td align="left">
<select name="txtStatus">
<option value="yes">yes</option>
<option value="no">no</option>
</select></td>
</tr>

<tr align="left"><td align="right">
<div align="right"><span class="bodytext">Would you like to help organise a state branch meeting?</div></td>
  <td align="left">
<select name="txtStatus">
<option value="yes">yes</option>
<option value="no">no</option>
</select></td>
</tr>

<tr align="left"><td colspan="2">
<span class="bodytext">
      <p><label for="txtText">Where would you prefer meetings to be held?</label><br>
      <textarea name="txtText" cols=65 rows=1 WRAP=virtual></textarea></p><br>
</td></tr>

<tr align="left"><td colspan="2">
<span class="bodytext">
      <p><label for="txtText">What kind of activities would you like to see occur during meetings?</label><br>
      <textarea name="txtText" cols=65 rows=1 WRAP=virtual></textarea></p>
</td></tr>

<tr align="left"><td align="right">
<div align="right"><span class="bodytext">Have you ever attended MSA's annual conference AMEC?</div></td>
  <td align="left">
<select name="txtStatus">
<option value="yes">yes</option>
<option value="no">no</option>
</select></td>
</tr>

<tr align="left">
  <td width="400" align="left" colspan="2">
<span class="subheadingitalic"><br><br>
<b>PROJECTS</b>
</span><br><br>

<tr align="left"><td colspan="2">
<span class="bodytext">
      <p><label for="txtText">Would you like to become involved in any of the following existing MSA projects? (If so, list.)</label><br><br>
MarsSkin, Starchaser Marsupial Rover, MarsOz, Expeditions, Outreach, Education
      <textarea name="txtText" cols=65 rows=1 WRAP=virtual></textarea></p><br>
</td></tr>

<tr align="left"><td colspan="2">
<span class="bodytext">
      <p><label for="txtText">Would you like to propose a new project? (If so, please describe.)</label><br>
      <textarea name="txtText" cols=65 rows=1 WRAP=virtual></textarea></p><br><br>
</td></tr>

<tr align="left">
  <td width="400" align="left" colspan="2">
<span class="subheadingitalic">
<b>GENERAL COMMENTS</b>
</span><br><br>

<tr align="left">
<td colspan="2"><span class="bodytext">
      <p><label for="txtText">Do you have any other suggestions/comments</label><br>
      <textarea name="txtText" cols=65 rows=5 WRAP=virtual></textarea></p><br>
</td></tr>


<tr align="left"><td height="54" colspan="2"></textarea>
  <label>
    <input type="submit" value="Submit Survey"></label></p>
 </td></tr>

</form>
</table>
<br>
<br>

</td>
</tr>
</table>
</center>

  </td>
 </tr>
</table>
</div>

<?php include("bottom.php"); ?>
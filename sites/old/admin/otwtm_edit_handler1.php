<?php include("../includes/user_authentication.php"); ?>
<?php include("../includes/connection.php"); ?>
<?php include("../includes/header.php"); ?>

<?php
$edit_type=$_POST['events_edit_type']; 
$edit_title=$_POST['eventsf_edit_title']; 
$edit_text=$_POST['events_edit_text']; 
$edit_link=$_POST['events_edit_link'];
$edit_link=$_POST['events_edit_link']; ?>

$SQLDATE="SELECT date, title FROM events WHERE title='$edit_title'";
$editdate=mysql_query($SQLDATE); 
while($data=mysql_fetch_assoc($editdate))
		{		$edit_date=$data['date'];  }?>



<TABLE CELLSPACING=0 BORDER=0 CELLPADDING=7 WIDTH=585>
  <TR>
    <TD WIDTH="6%" VALIGN="TOP"></TD>
    <TD WIDTH="94%" VALIGN="TOP"> 

<span class="banner_headline"> 
 Admin Homepage
</span> 

</TD>
  </TR>
</TABLE>

<hr>

<TABLE CELLSPACING=0 BORDER=0 CELLPADDING=7 WIDTH=634>
  <TR>
    <TD WIDTH="6%" VALIGN="TOP"></TD>
    <TD WIDTH="94%" VALIGN="TOP"> 

      <p>

   <span class="bodytext">
      <p><b>Edit an Event </b></p>
      <table align="center" width="585">
	  
  <form action="otwtm_edit_handler2.php" method="post">
  
<tr align="left">
  <td align="left" width="200">

  <span class="bodytext">
         <label for="txtDate">Event Date: </label>
      <input type="text" name="txtDate" size="15" value="<?php echo $edit_date; ?>"></textarea>
  
</td>
  <td><span class="bodytext">
  <label for="txtType">Event Type: </label>
  
<select name="txtType" value="<?php echo $edit_type; ?>">
  <option value="<?php echo $edit_type; ?>" > <?php echo $edit_type; ?></option>
  <option value="Book Launch">Book Launch</option>
  <option value="Exhibition">Exhibition</option>
  <option value="Fair">Fair</option>
  <option value="Meeting">Meeting</option>
  <option value="Performance">Performance</option>
  <option value="Talk">Talk</option>
  <option value="Walking Tour">Walking Tour</option>
  <option value="Workshop">Workshop</option>
  </select>
  
</td></tr>
  
<tr align="left"><td colspan="2"><span class="bodytext">
        <label for="txtTitle">Event Title: </label>
<input type=hidden name="events_edit_title2" value="<?PHP echo $edit_title; ?>">
      <input type="text" name="txtTitle" size="65" value="<?php echo $edit_title; ?>"></textarea>
	 
  
</td></tr>
  
<tr align="left"><td colspan="2"><span class="bodytext">
  

      <p><label for="txtText">Event Description:</label><br>
  <textarea name="txtText" cols=70 rows=10 WRAP=virtual><?php echo $edit_text; ?></textarea></p>
</td></tr>
  
<tr align="left"><td colspan="2"><span class="bodytext">
        <label for="txtLink">Relevent Link: </label>
      <input type="text" name="txtLink" size="65" value="<?php echo $edit_link; ?>"></textarea>
     
    <p><label>
      <input type="submit" value="Send"></label></p>
   </td></tr>
  
</form>


</tr>
</table>



</TD>
  </TR>
  <TR> 
    <TD WIDTH="6%" VALIGN="TOP"></TD>
    <TD WIDTH="94%" VALIGN="TOP"> 

        <br>
        <br>
        <br>
     
      </TD>
  </TR>
</TABLE>

<?php include("../includes/footer.php"); ?>


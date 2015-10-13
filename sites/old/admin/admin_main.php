<?php include("../banner.php"); ?>
<?php include("../includes/connection.php"); ?>


<table width=664 border=0 cellpadding=0>
 <tr>
  <td valign=top>
<div style="margin-left:15">

<span class="headlines">
Control Panel 
</span>
<br>
<br>

<span class="bodytext">




<span class="bodytext"> 
           <span class="black_subheading"><b>Add a News Item</b> </span><br> 
           <span class="bodytext"> 
  The title, link and comment items entered below are automatically added to the 
<a href="/on_the_way_to_mars.php">On the Way to Mars</a> page. 
Avoid using single quotes in your entry text as this will result in the entry not being recorded. 
It is not mandatory to fill out the comment field.<br>
  <br>
  Insert dates using a yyyy-mm-dd format. eg. 10 January 2007 should be 2007-01-10
  <br><br>
      </span></p>
    
      <table align="center" width="585"><br>

<form action="otwtm_add_handler.php" method="post">
  
<tr align="left">
  	<td align="left" width="200">
 
	<span class="bodytext">
	<label for="otwtm_add_date">Date: </label>
	<input type="text" name="otwtm_add_date" size="15"></textarea>
  
</td>
  <td><span class="bodytext">
  <label for="otwtm_add_category">Category: </label>
  
<select name="otwtm_add_category">
  <option value="MSA in the News">MSA in the News</option>
  <option value="Mars News">Mars News</option>
  <option value="Related Space News">Related Space News</option>
  </select>
  
</td></tr>
  
<tr align="left"><td colspan="2"><span class="bodytext">
        <label for="otwtm_add_title">Title: </label>
      <input type="text" name="otwtm_add_title" size="65"></textarea>
  
</td></tr>
  
<tr align="left"><td colspan="2"><span class="bodytext">
        <label for="otwtm_add_link">Link: </label>
      <input type="text" name="otwtm_add_link" size="65"></textarea>

</td></tr>

<tr align="left"><td colspan="2"><span class="bodytext">
      <p><label for="otwtm_add_comment">Comment:</label><br>
      <textarea name="otwtm_add_comment" cols=65 rows=5 WRAP=virtual></textarea></p>
 
    <p><label>
      <input type="submit" value="Send"></label></p>
   </td></tr>

  
</form>
      </table>

      <br>
      <br>
      <hr>

       <span class="bodytext"><b>Edit/Delete News Items</b></span><br>
      <br>
      <?php

$current_time=date('d F Y h:i:s A');

$SQL="SELECT comment, category, title, link, DATE_FORMAT(date, '%D %M, %Y') as newdate FROM on_the_way_to_mars ORDER BY date DESC";

$result=mysql_query($SQL); ?>

<table cellpadding="0" cellspacing="0">
      <?php
	while($data=mysql_fetch_assoc($result))
		{		

?> 
<tr valign="top"><td valign="top" width="80px">

<form name="input" action="otwtm_login.php" method="post">
<input type=hidden name="otwtm_add_date" value="<?PHP echo $data['date']; ?>">
<input type=hidden name="otwtm_add_category" value="<?PHP echo $data['category']; ?>">
<input type=hidden name="otwtm_add_title" value="<?PHP echo $data['title']; ?>">
<input type=hidden name="otwtm_add_link" value="<?PHP echo $data['link']; ?>">
<input type=hidden name="otwtm_add_comment" value="<?PHP echo $data['comment']; ?>">
<input type="submit" value="Edit" valign="top" align="right" width="60">
</form>


<br>
</td><td>

      </span>

<span class="datelines"> 
<?PHP echo $data['newdate']; ?> -   
</span>

<span class="bodytext"> <b>
<?php 
if(!$data['title'])
		{echo"";} else {?>
<?PHP echo $data['title']; } ?></b>
</span><br>

<span class="bodytext"> 
<?php 
if(!$data['link'])
		{echo"";} else {?>
<a href="<?PHP echo $data['../link']; ?>"><?PHP echo $data['link']; ?></a>
</span>

<span class="bodytext"><i>
<?php 
if(!$data['comment'])
		{echo"";} else {?> - 
<?PHP echo $data['comment']; ?><br>

<?php } ?>



<br>

<?php } ?>
</span><br>
</td>
<?php } ?>




</span>
<br>
<br>

  </td>
 </tr>
</table>
</div>

<?php include("../bottom.php"); ?>

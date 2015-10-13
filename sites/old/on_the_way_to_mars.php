<?php include("banner.php"); ?>
<?php include("includes/connection.php"); ?>

<table width=664 border=0 cellpadding=0>
 <tr>
  <td valign=top>
<div style="margin-left:15">

<span class="headlines">
This Week on the Way to Mars...
</span>
<br>
<br>





<ul>
<?php

$SQL="SELECT title, category, comment, link, DATE_FORMAT(date, '%D %M, %Y') as newdate FROM on_the_way_to_mars ORDER BY date DESC";
$result=mysql_query($SQL); ?>
<?php
	while($data=mysql_fetch_assoc($result))
		{		
?> 
<li>
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
<a href="<?PHP echo $data['link']; ?>"><?PHP echo $data['link']; ?></a>
</span>

<span class="bodytext"><i>
<?php 
if(!$data['comment'])
		{echo"";} else {?> - 
<?PHP echo $data['comment']; ?>
<?php } ?>
</i> </span>

<?php } ?>
<br></li><br>
<?php } ?>

</ul>







</span>


  </td>
 </tr>
</table>
</div>

<?php include("bottom.php"); ?>

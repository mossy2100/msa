<?php include("banner.php"); ?>
<?php include("includes/connection.php"); ?>

<table width=664 border=0 cellpadding=0>
 <tr>
  <td valign=top>
<div style="margin-left:15">

<span class="headlines">
MSA in the News...
</span>
<br>
<br>

<?php
$SQL="SELECT DATE_FORMAT(date, '%D %M, %Y') as newdate, title, link FROM in_the_news ORDER BY date DESC";
$result=mysql_query($SQL); 
?>
<?php
	while($data=mysql_fetch_assoc($result))
		{		
?> 

<span class="datelines">
<?php if(!$data['newdate']){echo"";} else {?><?PHP echo $data['newdate']; ?></b><br><?php } ?>
</span> 

<span class="bodytext"><a href="<?PHP echo $data['link']; ?>">
<?php if(!$data['title']){echo"";} else {?><?PHP echo $data['title']; ?></a><br><br><?php } ?>
</span> 

<?php 
} ?>
</span>


<br>
<br>
</span>

  </td>
 </tr>
</table>
</div>

<?php include("bottom.php"); ?>
</body>

</html>

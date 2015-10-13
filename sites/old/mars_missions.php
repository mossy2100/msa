<?php include("banner.php"); ?>
<?php include("includes/connection.php"); ?>

<table width=664 border=0 cellpadding=0>
 <tr>
  <td valign=top>
<div style="margin-left:15">

<span class="headlines">
Mars Missions
</span>
<br>
<br>

<?php
$SQL="SELECT name, DATE_FORMAT(launch_date, '%D %M, %Y') as newdate, mission_type, agency, homepage FROM mars_missions ORDER BY launch_date DESC";
$result=mysql_query($SQL); 
?>
<?php
	while($data=mysql_fetch_assoc($result)) 
		{		
?> 

<span class="bodytext">
<?php if(!$data['name']){echo"";} else {?><b><?PHP echo $data['name']; ?></b><br><?php } ?>
</span> 

<span class="bodytext">
<?php if(!$data['agency']){echo"";} else {?><i>Agency </i>- <?PHP echo $data['agency']; ?></b><br><?php } ?>
</span> 

<span class="bodytext">
<?php if(!$data['mission_type']){echo"";} else {?><i>Mission type </i>- <?PHP echo $data['mission_type']; ?></b><br><?php } ?>
</span> 

<span class="bodytext">
<?php if(!$data['newdate']){echo"";} else {?><i>Launch Date </i>- <?PHP echo $data['newdate']; ?></b><br><?php } ?>
</span> 

<span class="bodytext"><a href="<?PHP echo $data['homepage']; ?>">
<?php if(!$data['homepage']){echo"";} else {?><?PHP echo $data['homepage']; ?></a><br><br><?php } ?>
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

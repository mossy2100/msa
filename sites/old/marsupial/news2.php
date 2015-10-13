<?php include("includes/banner.php"); ?>
<?php include("includes/menu.php"); ?>
<?php include("includes/stats_collection.php"); ?>
<?php include("includes/connection.php"); ?>

	<td  width="80% "valign="top" align="left">

		<table align="right" cellspacing=0 cellpadding=5 bordercolor="#FF9900"
					bgcolor=white style='width:100%; mso-cellspacing:0cm; background:white; 
					border:outset #FF9900 1pt; mso-padding-alt:1.0pt 1.0pt 1.0pt 1.0pt'>
     		<tr>      	
			<td>
                 <span class="blackheading">Project News</span><br>
                 <span class="bodytext">

<?php include("www.marssociety.org.au/blog/wp-content/themes/default/header.php"); ?>
<?php include("www.marssociety.org.au/blog/wp-content/themes/default/index.php"); ?>
<?php include("www.marssociety.org.au/blog/wp-content/themes/default/footer.php"); ?>

<?php
$SQL="SELECT DATE_FORMAT(date, '%D %M, %Y') as newdate, heading, para1, para2, image_name1, caption1, image_name2, caption2 FROM news ORDER BY date DESC";
$result=mysql_query($SQL); ?>

<?php
	while($data=mysql_fetch_assoc($result))
		{		

?> 

</span>

<span class="blacksubheading"><br><b>
<?php if(!$data['heading']){echo"";} else {?><?PHP echo $data['heading']; ?></b><br><?php } ?>
</span>

<span class="datecaption"> <i><b>
<?php if(!$data['newdate']){echo"";} else {?><?PHP echo $data['newdate']; ?></i></b><br><?php } ?>
</span>

<span class="bodytext">
<?php if(!$data['para1']){echo"";} else {?><?PHP echo $data['para1']; ?><?php } ?>
</span><br><br>

<span class="bodytext">
<?php if(!$data['para2']){echo"";} else {?><?PHP echo $data['para2']; ?><br><br><?php } ?>
</span>

<?php 
if(!$data['link'])
		{echo"";} else {?>See 
<a href="<?PHP echo $data['link']; ?>"><?PHP echo $data['link']; ?></a>.<br><br><?php } ?>

<?php 
if(!$data['caption1'])
		{echo"";} else {?>

<table width="100%" border="0">
<tr>
<td valign="top" width="240">
<img src="/marsupial/images/<?PHP echo $data['image_name1']; ?>">
<span class="smallcaption">
<?PHP echo $data['caption1']; ?>
</span>
</td>
<td valign="top" width="240">
<img src="/marsupial/images/<?PHP echo $data['image_name2']; ?>">
<span class="smallcaption">
<?PHP echo $data['caption2']; ?>
</span>
</td>
</tr>
</table>
<?php } ?>





<?php } ?>

				<br>
				</span>
			</td>
             </tr>
        </table>


</td>
</tr>
</table>

<?php include("includes/footer.php"); ?>
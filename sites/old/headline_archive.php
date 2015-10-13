<?php include("banner.php"); ?>
<?php include("includes/connection.php"); ?>

<table width=664 border=0 cellpadding=0>
 <tr>
  <td valign=top>
<div style="margin-left:15">
<span class="headlines">Headline Archive</span><br><br>
<span class="bodytext" style="italic"><i>
The following is an archive of headline articles from the MSA website since its inception in 1999.
Dates cited for articles from before 14 August, 2005 
are approximate only, though their consecutive order remains accurate.</i></span><br><br>
<br>

<?php
$SQL="SELECT DATE_FORMAT(date, '%D %M, %Y') as newdate, headline, article, thumb_gallery FROM headlines ORDER BY date DESC";
$result=mysql_query($SQL); 
?>
<?php
	while($data=mysql_fetch_assoc($result)) 
		{?> 
			<span class="subheadlines">
			<?php if(!$data['headline']){echo"";} else {?><?PHP echo $data['headline']; ?></b><br><?php } ?>
			</span> 
			<span class="datelines">
			<?php if(!$data['newdate']){echo"";} else {?><?PHP echo $data['newdate']; ?></b><br><?php } ?>
			</span> 
			<span class="bodytext">
			<?php if(!$data['article']){echo"";} else {?><?PHP echo $data['article']; ?></b><br><?php } ?>
			<?php if(!$data['thumb_gallery']){ ?><br><br> <?php } else {?><?PHP echo $data['thumb_gallery']; ?><br></b><?php } ?>
			</span> 
		<?php } ?>


<br>
</span>

  </td>
 </tr>
</table>
</div>

<?php include("bottom.php"); ?>
</body>

</html>

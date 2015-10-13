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
                 <span class="blackheading">Add to News</span><br>
                 <span class="bodytext"><br>


<span class="bodytext"> This page is where you can add news item to the <a href="/marsupial/news.php">news</a> page of the website. The entries are listed chronologically, with the most recent at the top of the page.  
A list of news items already in this section is displayed at the bottom of the page. 
<br>
<br>
<b>User Guidelines</b><br>
If there are no relevant links to the news story then leave the links field blank. The system does not display manually entered paragraph breaks, so separate paragraphs need to be entered in the separate boxes below (currently max of 2.). Avoid using single quotes in your entry text as this will result in the entry not being recorded. There is currently no function to enable you to remove or edit news items, or to add images, but these will be added in future. 
<script> email ('gmmurphy','ozemail.com.au','Email Guy Murphy')</script> if you would like anything changed. 
<br><br>

<br><br><b>Add a News Item</b><br><br>

<table align="center" width="100%">
<form action="admin_news_handler.php" method="post">

<tr align="left">
  <td align="left"><div align="right" valign="top"><span class="bodytext"> Heading:</div></td>
  <td><input type="text" name="txtHeading" size="65"></td>
</tr>

<tr align="left">
  <td align="left"><div align="right" valign="top"><span class="bodytext"> Para1:</div></td>
  <td><textarea name="txtPara1" cols=55 rows=12 WRAP=virtual></textarea></td>
</tr>

<tr align="left">
  <td align="left"><div align="right" valign="top"><span class="bodytext"> Para2:</div></td>
  <td><textarea name="txtPara2" cols=55 rows=12 WRAP=virtual></textarea></td>
</tr>

<tr align="left">
  <td align="left"><div align="right" valign="top"><span class="bodytext"> Link:</div></td>
  <td><input type="text" name="txtLink" size="65"></td>
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
News Items<br>
</span>


<span class="bodytext"> 
<br>

<?php
$SQL="SELECT DATE_FORMAT(date, '%D %M, %Y') as newdate, heading, para1, para2, link, image_name1, caption1, image_name2, caption2 FROM news ORDER BY date DESC";
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
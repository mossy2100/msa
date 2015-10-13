<?php include("banner.php"); ?>
<?php include("includes/connection.php"); ?>

<table width=664 border=0 cellpadding=0>
 <tr>
  <td valign=top>
<div style="margin-left:15">

<span class="headlines">
SOUTH AUSTRALIAN BRANCH HOMEPAGE
</span><br><br>

<span class="headlines">

Monthly Discussion Group/Meeting
</span>
<span class="bodytext">
<br><br>

A warm welcome is extended to everyone interested in Mars exploration. 
<br>
<br>

<b>VENUE </b> - 

Marcellina Restaurant, 273 Hindley Street (corner Grey Street) Adelaide
<br>
<br>

<b>WHEN:&nbsp;&nbsp; </b> 6:00PM - 8:00PM - the first Sunday of each month. <Br>
(We recommend <script> email('sa','marssociety.org.au','emailing')</script> before attending just to confirm.)
<br>
<br>

<b>WHAT:&nbsp;&nbsp;  </b> At the meetings we usually have a cuppa or a light meal and
discuss Mars exploration - research and development, the latest discoveries, issues and 
philosophy which may impact on exploration of Mars.
All members, friends and those interested in Mars exploration are welcome.
For those after a meal - prices are very affordable.
<br>
<br>
<br>
<span class="bodytext">
For more information contact - <br>
        <br>

        
South Australian Branch Coordinator <br>
Mars Society of Australia <br>
Email - <script> email('sa','marssociety.org.au','here')</script>  <br>
Tel: 8354 0211<br>
Mob: 0417 800 956<br>




</b>			</span> 
<br>
<br>

<span class="yellowheadlines">
News
</span>
<br>
<br>

<?php
$SQL="SELECT DATE_FORMAT(date, '%D %M, %Y') as newdate, headline, article, thumb_gallery, region FROM headlines WHERE region like '%South Australia%' ORDER BY date DESC";
$result=mysql_query($SQL); 
?>

<?php
	while($data=mysql_fetch_assoc($result)) 
		 {?> 
			<span class="subheadlines"><b>
			<?php if(!$data['headline']){echo"";} else {?><?PHP echo $data['headline']; ?></b><br><?php } ?>
			</span> 
			<span class="datelines">
			<?php if(!$data['newdate']){echo"";} else {?><?PHP echo $data['newdate']; ?></b><br><?php } ?>
			</span> 
			<span class="bodytext">
			<?php if(!$data['article']){echo"";} else {?><?PHP echo $data['article']; ?></b><br><?php } ?>
			<?php if(!$data['thumb_gallery']){ ?><br><br> <?php } else {?><?PHP echo $data['thumb_gallery']; ?><br></b><?php } ?>
			</span> 
		<?php }?>

</span>

        </td>
      </tr>

    </tbody>
  </table>
</form>

  </td>
 </tr>
</table>
</div>

<?php include("bottom.php"); ?>
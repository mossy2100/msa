<?php include("banner.php"); ?>
<?php include("includes/connection.php"); ?>

<table width=664 border=0 cellpadding=0>
 <tr>
  <td valign=top>
<div style="margin-left:15">

<span class="headlines">
WESTERN AUSTRALIAN BRANCH HOMEPAGE
</span><br>
<i>"Home of the Starchaser Marsupial Manned Rover"</i>
<br>
<br><span class="bodytext">

Welcome to the Western Australian Branch of the Mars Society Australia, Inc. 
By joining in and following the MSA's activities, you will become a 
vital part of the Mars Society's goal to make a new home for Humankind on the Red Planet, Mars.
Major projects of the Western Australian branch include the
 <A HREF="http://www.marsupial.org.au">Starchaser Marsupial Manned Rover</A> and the
<A HREF="http://www.hobbycentre.com.au/CPSSindex.html">Centre for Planetary and Space Studies</A>.
<br>
<br>
<b>
Branch Meetings
</b><br>
Monthly meetings are held on the 2nd Sunday of each month in the 
Heritage Room, ground floor Guildford Hotel, cnr James & Johnson Sts, 
Guildford.
Adequate parking in Johnson St carpark, adjacent bottle dept. This is a 
dinner meeting, so be prepare to pay for food and drinks.
If this is your first visit, please contact David Cooper (State 
Coordinator) to make a table booking on 9295 6466 business hours or 9295 
1293 after hours, for latest information.
<br>
<br>

For more information contact - <br>
        <br>

David Cooper - Branch Coordinator <br>
Mars Society of Australia <br>
Email - <script> email('wa','marssociety.org.au','here')</script>  <br>
Bus Hrs - 08 9295 6466<br>
After Hours 08 9295 1293<br>

<br>
<br>
</b>			</span> 

<span class="yellowheadlines">
News
</span>
<br>
<br>

<?php
$SQL="SELECT DATE_FORMAT(date, '%D %M, %Y') as newdate, headline, article, thumb_gallery, region FROM headlines WHERE region like '%Western Australia%' ORDER BY date DESC";
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
		<?php } ?>

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
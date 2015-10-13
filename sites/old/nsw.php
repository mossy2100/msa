<?php include("banner.php"); ?>
<?php include("includes/connection.php"); ?>

<table width=664 border=0 cellpadding=0>
 <tr>
  <td valign=top>
<div style="margin-left:15">

<span class="headlines">
NEW SOUTH WALES BRANCH HOMEPAGE
</span>
<br>
<br>
      
<span class="bodytext">
For more information about branch activities contact - <br>
        <br>

New South Wales Branch Coordinator <br>
Mars Society of Australia <br>
<script> email('nsw','marssociety.org.au','email')</script>  <br>

<br>
<br>
</b>			</span> 

<span class="yellowheadlines">
News
</span>
<br>
<br>

<?php
$SQL="SELECT DATE_FORMAT(date, '%D %M, %Y') as newdate, headline, article, thumb_gallery, region FROM headlines WHERE region like '%New South Wales%' ORDER BY date DESC";
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



  </td>
 </tr>
</table>
</div>

<?php include("bottom.php"); ?>
<?php include("banner.php"); ?>
<?php include("includes/connection.php"); ?>

<table width=764 border=0 cellpadding=0>
 <tr>
  <td valign=top> 
<div style="margin-left:15"> 

<table border=0 cellpadding=0>   	
<tr>    	          
<td width=469 valign=top> 	

<?php
$SQL="SELECT DATE_FORMAT(date, '%D %M, %Y') as newdate, headline, article, thumb_gallery FROM headlines ORDER BY date DESC";   
$result=mysql_query($SQL); 
$i=0;
?>  

<?php
	while(($data=mysql_fetch_assoc($result)) && ($i<5))
		{?> 
			<span class="headlines">
			<?php if(!$data['headline']){echo"";} else {?><?PHP echo $data['headline']; ?></b><br><?php } ?>
			</span> 
			<span class="datelines"><br>
			<?php if(!$data['newdate']){echo"";} else {?><?PHP echo $data['newdate']; ?></b><br><?php } ?>
			</span> 
			<span class="bodytext">
			<?php if(!$data['article']){echo"";} else {?><?PHP echo $data['article']; ?></b><?php } ?>

			<?php if(!$data['thumb_gallery']) { ?><br><br> <?php } else {?><?PHP echo $data['thumb_gallery']; ?></b><?php } ?>
		
			
<?php if(($data['headline']=="Countdown Begins for Phoenix Landing") or ($data['headline']=="Images from an Alien World")){echo"";} else {?><br><?php } ?>	

</span> 

		<?php $i=$i+1; } ?>





<span class="bodytext">
 <a href="/headline_archive.php">
Headline archive ...</a></span>



<br><br></td>
<td width=10></td>

<td width=185 valign=top align=left style='width:138.75pt;padding:.75pt .75pt .75pt .75pt'>    	

<br>






<table border=0 cellspacing=0 cellpadding=0 WIDTH=213 bgcolor=black     	style='width:213 ;mso-cellspacing:0cm;background:black;border:outset #B53908 1.5pt;     	mso-padding-alt:3.0pt 3.0pt 3.0pt 3.0pt'>

    <tr>      	
	<td style='border:inset #B53908 .75pt;padding:3.0pt 3.0pt 3.0pt 3.0pt' WIDTH=210>
	<center>	

	<span class="rhtable_headlines">
	Mars Missions</span>
	<br><span class="bodytext"><i>(Current & Future)</i></span><br><br>

<?php
$SQL="SELECT name, launch_date, homepage FROM mars_missions where expiry_date='0000-00-00' ORDER BY launch_date DESC";
$result=mysql_query($SQL); 
?>
<?php
	while($data=mysql_fetch_assoc($result)) 
		{		
?> 
<span class="bodytext"><a href="<?PHP echo $data['homepage']; ?>">
<?php if(!$data['name']){echo"";} else {?><?PHP echo $data['name']; ?></a><br><br><?php } ?>
</span> 
<?php 
} ?>
</span>

<span class="datelines">
See full <a href="/mars_missions.php">mission catalogue...</a><br>
</span>

      	</td>     	
	</tr>    
	</table>
	<br>





<table border=0 cellspacing=0 cellpadding=0 WIDTH=213 bgcolor=black     	
style='width:213 ;mso-cellspacing:0cm;background:black;border:outset #B53908 1.5pt;     	
mso-padding-alt:3.0pt 3.0pt 3.0pt 3.0pt'>

    <tr>      	
	<td style='border:inset #B53908 .75pt;padding:3.0pt 3.0pt 3.0pt 3.0pt' WIDTH=210>
	<center>


<span class="rhtable_headlines">
	MSA in the News</span>
	<br><br>


<?php
$SQL="SELECT DATE_FORMAT(date, '%D %M, %Y') as newdate, title, link FROM in_the_news ORDER BY date DESC";
$result=mysql_query($SQL); 
$i=0;
?>
<?php
	while(($data=mysql_fetch_assoc($result)) && ($i<13))
		{?> 

<span class="datelines">
<?php if(!$data['newdate']){echo"";} else {?><?PHP echo $data['newdate']; ?></b><br><?php } ?>
</span> 

<span class="bodytext"><a href="<?PHP echo $data['link']; ?>">
<?php if(!$data['title']){echo"";} else {?><?PHP echo $data['title']; ?></a><br><br><?php } ?>
</span> 

<?php $i=$i+1;
                        } ?>
</span>

<span class="datelines">
MSA in the News <a href="/msa_in_the_news.php">archive...</a><br>
</span>

</td>     
</tr>    
</table>  
      <br>






  </td>
 </tr>
</table>
</div>

<?php include("bottom.php"); ?>
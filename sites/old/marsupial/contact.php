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
                 <p><span class="blackheading">Contact
                    </span><br>
                    <br>
                     <span class="bodytext">
    
				 <b>Project Manager</b></span>  <br><br>               
                 <table width="100%"  border="0">
                   <tr>
                     <th width="27%" rowspan="2" align="left" valign="top" scope="col"><img src="/marsupial/images/graham_mann.jpg" width="115" height="141"> <br>
                         <span class="caption"> Dr Graham Mann </span></th>
                     <th width="26%" scope="col" valign="top" align="right"><span class="bodytext"><b>Email </b> ::</span></th>
                     <th width="47%" scope="col" valign="top" align="left"><span class="bodytext">
                       <script> email ('manager','marsupial.org.au','manager(at)marsupial.org.au')</script>
                     </span></th>
                   </tr>
                   <tr valign="top">
                     <td valign="top" align="right"><span class="bodytext"><b>Postal </b> ::</span></td>
                     <td valign="top" align="left" ><span class="bodytext">School of Information Technology, <br>
      Murdoch University, South Street. <br>
      Murdoch, WA 6050.</span></td>
                   </tr>
                 </table>
                 <p>			      </p><span class="bodytext">
                 <p>Dr Graham Mann is an engineer, specialising in robotics and human-machine interactions. After taking a psychology degree and doing research in psychophysiology at the University of WA's Biofeedback Laboratory, he moved to the University of NSW, to study for a Master's degree in cognitive science, and later a PhD in artificial intelligence. He is currently Senior Lecturer, <a href="http://www.it.murdoch.edu.au/">School of Information Technology</a>, BITL, Murdoch University, and has designed and built a number of innovative robots, including a walking biped and a domestic floor-cleaning machine. Graham took part in the Jarntimarra-1 expedition, <a href="http://chapters.marssociety.org/canada/expedition-mars.org/ExpeditionOne/">Expedition 1</a> at the Mars Desert Research Station and is a director of the Mars Society Australia.</p>
				   
                 <p><br>

<b>Project Team</b>

<br><br>
<i>Current Members</i><br>


<?php
$SQL="SELECT name, organisation, email, phone, mobile, skype_username, status FROM team WHERE status='current' ORDER BY name ASC";
$result=mysql_query($SQL); ?>

<?php
	while($data=mysql_fetch_assoc($result))
		{		

?> 

</span>

<span class="bodytext">
<?php if(!$data['name']){echo"";} else {?><?PHP echo $data['name']; ?><br><?php } ?>
</span>


<?php } ?>

<br>




<?php
$SQL="SELECT name, organisation, email, phone, mobile, skype_username, status FROM team WHERE status='past' ORDER BY name ASC";
$result=mysql_query($SQL); ?>

<?php if(!$data['name']){echo"";} else {?>


<span class="bodytext">
<i>Past Members</i><br> <br><?php } ?>

<?php
	while($data=mysql_fetch_assoc($result))
		{		

?> 

</span>


<?php if(!$data['name']){echo"";} else {?><?PHP echo $data['name']; ?><br><?php } ?>
</span>

<br>

<?php } ?>
</span><br>




</span></td>
             </tr>
        </table>

</td>
</tr>
</table>

<?php include("includes/footer.php"); ?>
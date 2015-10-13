<?php include("includes/stats_collection.php"); ?>
<?php include("includes/banner.php"); ?>
<?php include("includes/menu.php"); ?>
<?php include("includes/connection.php"); ?>

              <td width="50%" valign="top" align="left">
			<img src="/marsupial/images/rover.JPG" width="281" height="205" align="middle"><br><br>
                <span class="bodytext">The Starchaser Marsupial Rover is a pioneering experimental vehicle 
				investigating design and operational concepts for future human Mars surface rovers. 
				<a href="/marsupial/about.php">Read more ...</a>
				</span></td>

<td width="3%"></td>

              <td valign="top" align="right">

					<table align="right" 
					cellspacing=0 cellpadding=5 border="1" width="30%" bordercolor="#FF9900"
					bgcolor=white style='width:100%; mso-cellspacing:0cm;
					background:white; border:outset #FF9900 1pt; mso-padding-alt:1.0pt 1.0pt 1.0pt 1.0pt'>
     			<tr>      	
				<td><center>
                        <span class="blackheading">Project News</span><br><br>

<?php
$SQL="SELECT DATE_FORMAT(date, '%D %M, %Y') as newdate, heading FROM news ORDER BY date DESC";
$result=mysql_query($SQL); 
$i=0;
?>

<?php
	while(($data=mysql_fetch_assoc($result)) && ($i<5))
		{		
?> 

<span class="bodytext"><a href="/marsupial/news.php">
<?php if(!$data['heading']){echo"";} else {?><?PHP echo $data['heading']; ?>...</a><br><?php } ?>
</span> 

<span class="blackdatecaption">
<?php if(!$data['newdate']){echo"";} else {?><?PHP echo $data['newdate']; ?></b><br><?php } ?><br>

</span> 

<?php $i=$i+1;
} ?>

</span>





<span class="bodytext"><i><a href="/marsupial/news.php">Full News Archive...</i></a></span><br><br></td>
                    </tr>
                  	</table><br><br>



					<br><br>
				
							
			

			<br><br>

</td>
</tr>
</table>

<hr width="50%" size="1">

<?php include("includes/footer.php"); ?>
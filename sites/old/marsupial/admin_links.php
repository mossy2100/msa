<?php include("includes/banner.php"); ?>
<?php include("includes/menu.php"); ?>
<?php include("includes/stats_collection.php"); ?>
<?php include("includes/connection.php"); ?>

	<td  width="83% "valign="top" align="left">

		<table align="right" cellspacing=0 cellpadding=5 bordercolor="#FF9900"
					bgcolor=white style='width:100%; mso-cellspacing:0cm; background:white; 
					border:outset #FF9900 1pt; mso-padding-alt:1.0pt 1.0pt 1.0pt 1.0pt'>
     		<tr>      	
			<td>
<?php include("includes/admin_menu.php"); ?>
<br>
                 <span class="blackheading">Add to Links</span>
                 <span class="bodytext"><br><br>

		
This page is where you can add entries to the 'Links' section of the <a href="/marsupial/resources.php">Resources</a> page. A list of links already in this section is displayed at the bottom of the page by name and address, so you might want to check this first just to make sure your proposed new link isn't already on there. <br>
<br>
<b>Linking Guidelines</b><br>
The link will appear as whatever title you give it in the 'Link Title' box shown below, so try to make your title either the official name of the site or organisation, or alternatively as descriptive as possible. It shouldn't be any longer than the text entry box. Enter the link address by copying in exactly what you see in the address field of your browser.<br>
<i>ie. http://www.marsupial.org.au/ </i><br>
<br>
There is currently no function to enable you to remove or edit entries, but this could be added in future.
<script> email ('gmmurphy','ozemail.com.au','Email Guy Murphy')</script>
if you would like anything changed. <br>
<br>
</span>
                 <p><span class="bodytext"><b>Add a Link</b></span></p>
                 <form action="admin_links_handler.php" method="post">
                   <p> <span class="bodytext">
                     <label for="txtTitle">LINK TITLE: eg. Starchaser Marsupial Homepage</label>
                     <br>
                     <input type="text" title="Enter link name" name="txtName" rows="1" size=80>
                   </span></p>
                   <p> <span class="bodytext">
                     <label for="txtUrl">FULL LINK ADDRESS: eg. http://www.marsupial.org.au/</label>
                     <br>
                     <input type="text" textarea title="Enter your link url" name="txtUrl" rows="1" size="80">
                   </span></p>
                   <p><span class="bodytext">
                     <label title="Send your entry">
                     <input name="submit" type="submit" value="Send">
                     </label>
                   </span></p>
                 </form>
                 <br>
                 <br>
                 <hr>
                 <b>

 <span class="blackheading">Existing Links</span>

<br>
                 <br>
                 <?php

$SQL="SELECT * FROM links ORDER BY name ASC";

$result=mysql_query($SQL); ?>
                 <?php
	while($data=mysql_fetch_assoc($result))
		{		

?><span class="bodytext">
                 <?PHP echo $data['name']; ?> - <a href="<?PHP echo $data['url']; ?>"><span class="bodytext"><?PHP echo $data['url']; ?></span></a><br>
                 <?php } ?>
                 <br>
                 <br>
                 <br>
             
				 </span>
			</td>
             </tr>
        </table>


</td>
</tr>
</table>

<?php include("includes/footer.php"); ?>
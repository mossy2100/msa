<?php include("includes/banner.php"); ?>
<?php include("includes/menu.php"); ?>
<?php include("includes/connection.php"); ?>
<?php include("includes/stats_collection.php"); ?>

	<td  width="83% "valign="top" align="left">

		<table align="right" cellspacing=0 cellpadding=5 bordercolor="#FF9900"
					bgcolor=white style='width:100%; mso-cellspacing:0cm; background:white; 
					border:outset #FF9900 1pt; mso-padding-alt:1.0pt 1.0pt 1.0pt 1.0pt'>
     		<tr>      	
			<td>
                 <span class="blackheading">So Far So Good ...</span><br>
                 <span class="bodytext"><br>

<?php 
$target = "documents/"; 
$target = $target . basename( $_FILES['uploaded']['name']) ; 
$ok=1; 

//This is our size condition 
if ($uploaded_size>2000000) 
{ 
echo "Your file is too large.<br>
Due to limited server space there is a file upload size limit of 2Mb.<br><br>
Click <a href=\"../marsupial//admin_upload.php\">here</a> to return to the upload page."; 
$ok=0; 
} 

//This is our limit file type condition 
if ($uploaded_type =="text/php") 
{ 
echo "<br><br>No PHP files<br><br>
Click <a href=\"../marsupial/admin_upload.php\">here</a> to return to the upload page."; 
$ok=0; 
}

//Here we check that $ok was not set to 0 by an error 
if ($ok==0) 
{ 
Echo "<br><br>Sorry your file was not uploaded.<br>
Click <a href=\"../marsupial/admin_upload.php\">here</a> to return to the upload page."; 
} 

//If everything is ok we try to upload it 
else 
{ 
if(move_uploaded_file($_FILES['uploaded']['tmp_name'], $target)) 
{ 
echo "<br><br>The file ".basename( $_FILES['uploadedfile']['name']). " has been uploaded<br><br>
Click <a href=\"../marsupial/admin_upload.php\">here</a> to return to the upload page.<br><br> 

Click <a href=\"../marsupial/admin_main.php\">here</a> to return to the admin homepage."; 
} 
else 
{ 
echo "<br><br>Sorry, there was a problem uploading your file.<br><br>
Click <a href=\"../marsupial/admin_upload.php\">here</a> to return to the upload page."; 
} 
} 
?>

<br>
				</span>
			</td>
             </tr>
        </table>


</td>
</tr>
</table>

<?php include("includes/footer.php"); ?>
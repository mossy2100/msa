<?php include("includes/banner.php"); ?>
<?php include("includes/menu.php"); ?>
<?php include("includes/conenction.php"); ?>
<?php include("includes/stats_collection.php"); ?>

	<td  width="83% "valign="top" align="left">

		<table align="right" cellspacing=0 cellpadding=5 bordercolor="#FF9900"
					bgcolor=white style='width:100%; mso-cellspacing:0cm; background:white; 
					border:outset #FF9900 1pt; mso-padding-alt:1.0pt 1.0pt 1.0pt 1.0pt'>
     		<tr>      	
			<td>
<?php include("includes/admin_menu.php"); ?><br>

                 <span class="blackheading">File Upload
</span> 

<br>
<br>

<span class="bodytext">
The page is where you can add documents to the 'Document Archive' section of the <a href="/marsupial/resources.php">Resources</a> page. 
Files are uploaded and then will automatically appear in the list of downloadable files under the 'Document Archive' heading.

<br>
<br>

The following files are currently held in the 'Document Archive'.

<br>
<br>

<?php
  $TheDirectory = "/var/www/vhosts/guymurphy.com/httpdocs/marsupial/documents/";
  $TheFileType = "php";
  $LinkTo="http://www.guymurphy.com/marsupial/documents/";

  function ListFiles($FileType, $Directory, $LinkTo = false) {
    GLOBAL $PHP_SELF;

    $DirectoryFiles = array();
    chdir($Directory);
    $DirectoryHandle = @opendir($Directory) or die("The directory \"$Directory\" was not found.");

    while($DirectoryEntry = readdir($DirectoryHandle)) {
      if ((is_dir($DirectoryEntry)) && ($DirectoryEntry != "..") && 
($DirectoryEntry != ".")) {
      } elseif (($DirectoryEntry != "..") && ($DirectoryEntry != ".") 
&& (substr(strtolower($DirectoryEntry), strrpos($DirectoryEntry, ".")
+1))) {
        $DirectoryFiles[] = $DirectoryEntry;
      }
    }

    sort($DirectoryFiles);
    
    if (!$LinkTo) {
      $LinkTo = $HTTP_HOST . substr($Directory, strpos($Directory, 
substr($PHP_SELF, 0, strrpos($PHP_SELF, "/"))), strlen($Directory));
    }
    
    if (count($DirectoryFiles) > 0);
    for ($idx=0; $idx < count($DirectoryFiles); $idx++) {
      printf ("<a href=\"%s%s\">%s</a> [%sk]
<br>\n", $LinkTo, $DirectoryFiles[$idx], $DirectoryFiles
[$idx], round(filesize($Directory.$DirectoryFiles[$idx])/1000,0));
    }
    if (count($DirectoryFiles) > 0) echo "</ul>\n";

    closedir($DirectoryHandle);
  }

  ListFiles($TheFileType, $TheDirectory, $LinkTo);
?>

<br>
<br>
<b>Upload Guidelines</b><br>
The Marsupial website is currently hosted on Guy Murphy's personal server space and there is not much 
filespace left, so please hold off uploading new files till marssociety.org.au shifts to a new server. 
There is an arbitrary limit of 2Mb in the size of individual files which may be uploaded.<br> 
<br>

It is preferable to upload documents as .pdf files, as these are more secure and are smaller in size. 
Pdf files may be created online at <a href="http://www.zamzar.com/">Zamzar Free Online File Conversion</a> 
for free. (This only takes a minute or two.)<br>

<br>

The names of the uploaded files are directly reproduced in alphabetical order in the 'Document Archive' listing. 
Therefore, try to use as descriptive file names as possible. Always use the underscore '_' instead of spaces 
in file names. 


<br><br>

<form enctype="multipart/form-data" action="admin_upload_handler.php" method="POST">
Please choose a file to upload: <input name="uploaded" type="file"><br>
<input type="submit" value="Upload">
</form>

<br>
Email <script> email  ('gmmurphy','ozemail.com.au','Guy Murphy')</script> if you would like an 
uploaded document to be removed.

<br><br>  Don't forget to <a href="/marsupial/logout.php">log out</a> once you have finished your session.
<br>
<br>
</span>

</td>
</tr>
</table>

<?php include("includes/footer.php"); ?>
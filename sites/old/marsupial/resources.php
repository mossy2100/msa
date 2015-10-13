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
                 <span class="blackheading">Resources</span><br><br>
                 <span class="bodytext">

<b>Links</b><br><br>

<?php

$SQL="SELECT * FROM links ORDER BY name ASC";

$result=mysql_query($SQL); ?>
    
<?php
	while($data=mysql_fetch_assoc($result))
		{		

?> 

<a href="<?PHP echo $data['url']; ?>"><?PHP echo $data['name']; ?></a><br>

<?php } ?>



<br><br>

<b>Document Archive</b><br><br>


<?php
  $TheDirectory = "/var/www/vhosts/marssociety.org.au/httpdocs/marsupial/documents/";
  $TheFileType = "php";
  $LinkTo="http://marssociety.org.au/marsupial/documents/";

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

<i>Progress Drawing set -  February 2007</i><br>

<a href="/marsupial/drawings/fibreglass form progress.pdf">fibreglass form progress.pdf</a> [91k]<br>
<a href="/marsupial/drawings/GA-1 rover DRAFT.pdf">GA-1 rover DRAFT.pdf</a> [203k]<br>
<a href="/marsupial/drawings/modification to chassis progress.pdf">modification to chassis progress.pdf</a> [96k]<br>
<a href="/marsupial/drawings/ST-2 rev2 mid cabin.pdf">ST-2 rev2 mid cabin.pdf</a> [150k]<br>
<a href="/marsupial/drawings/ST-3 rev1  front cabin .pdf">ST-3 rev1  front cabin.pdf</a> [225k]<br>
<a href="/marsupial/drawings/ST-4 rev 1, Mid Cabin Floor.pdf">ST-4 rev 1, Mid Cabin Floor.pdf</a> [262k]<br>


<br><br>

<b>MSA Publications</b><br><br>
Mars Society Australia 2000. "Project Marsupial Human Operations Prototype (HOP)." (Download above.)<br><br>

Hoogland, J. 2000. "An entry in the Mars Society's Mars Analog Rover Design Competition (Marsupial-Wombat)." (Download above.)<br>
<br>

Cairns, B., Fenton, A., and Hoffman, N. "Progress Towards Mars Rover Simulations in the Australian Outback". Abstract for the 32nd Lunar and Planetary Science Conference in Houston, March 2001. (Download above.)<br><br>

Mann, G.A. "Design, Construction, and Operations Plan for the Marsupial Rover". <span style="font-style: italic">Exploring the Red Planet: Proceedings of the Second Australian Mars Exploration Conference</span>, University of Sydney, Sydney Australia, July 2002.<br>
<br>

Mann, G. A. and Clarke, J. D. A. 2003. "First comparative field test of pressurised rover prototypes." <span style="font-style: italic">Abstracts of the 3rd Australian Mars Exploration Conference</span>, Trinity College, Perth, August 2003.<br>
<br>

G.A. Mann, N.B. Wood, J.D.A. Clarke, S. Piechocinski, M. Bamsey and J.H. Laing. (2004) "Comparative Fields Tests of Pressured Rover Prototypes" (AAS 03-319. In Cockell, C. (Ed.), <span style="font-style: italic">Martian Expedition Planning</span>, AAS Science and Technology Series, Vol 107, Univelt Publishing, San Diego, California, 2004, pp. 313-327.<br><br>

(also reproduced as) G.A. Mann, N.B. Wood, J.D.A. Clarke, S. Piechocinski, M. Bamsey and J.H. Laing. (2004) "Comparative Fields Tests of Pressurised Rover Prototypes",<span style="font-style: italic"> Journal of the British Interplanetary Society</span>, (Vol 57, No. 3/4, February 2004), British Interplanetary Society, pp. 135-143.<br>
<br>

Mann, G.A. Trials and Tribulations of the Starchaser Marsupial Rover. <span style="font-style: italic">Proceedings of the 4th Australian Mars Exploration Conference</span>, University of South Australia, July, 2004.<br>
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
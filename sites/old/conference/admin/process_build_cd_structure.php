<?php 
	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");	
	session_start();
	
	//$new_include_path = ini_get('include_path').":$php_root_path/includes/pear";
	//ini_set('include_path', $new_include_path);
    
    ini_set('include_path', "$php_root_path/includes/pear");
	
	require_once($php_root_path."/includes/pear/HTML/Progress.php");
	
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	
	//Code added  to address security problem reported by Sebastian Held 14-Nov-2006
	$header = "Build CD Structure" ;
	$accepted_privilegeID_arr = array ( 3 => "" ) ;
	$accepted_phaseID_arr = array ( 1 => "" , 2 => "" , 3 => "" , 4 => "" ) ;
	authentication( $header , $accepted_privilegeID_arr , $accepted_phaseID_arr , $homepage , $php_root_path , $GLOBALS["DB_PREFIX"] , &$err_message ) ;		
	
	//Call the function to get the conference information
	$conferenceInfo = get_conference_info();		
    
	require_once($php_root_path."/includes/pear/Tar.php");
	require_once($php_root_path."/admin/includes/libzipfile.php");
		
	class File_Archiver
	{
		function File_Archiver( $filename ) {}
		function AddString( $archival_path, $data ) {}
	}
	
	class Tar_Archiver extends File_Archiver
	{
		var $COMPRESSION_TYPE = false;
		var $archive_file;
		
		function Tar_Archiver( $filename )
		{
			echo $COMPRESSION_TYPE;
			$this -> $archive_file = 
				new Archive_Tar($filename, $COMPRESSION_TYPE);
			$this -> $archive_file -> create(array());
		}
		
		function AddString( $archival_path, $data )
		{
			$this -> $archive_file -> addString( $archival_path, $data );
		}
	}
	
	class BZip2_Archiver extends Tar_Archiver
	{
		var $COMPRESSION_TYPE = 'bz2';
	}
	
	class GZip_Archiver extends Tar_Archiver
	{
		var $COMPRESSION_TYPE = 'gz';
	}
	
	class Zip_Archiver extends File_Archiver
	{
		var $filename;
		var $archive_file;
		
		function Zip_Archiver( $filename )
		{
			$this -> filename = $filename;
			$this -> archive_file = new zipfile();
		}
		
		function AddString( $archival_path, $data )
		{
			$this-> archive_file -> add_file( $data , $archival_path);
			$file = fopen($this -> filename, 'w');
			fwrite($file, $this -> archive_file -> file());
		}
	}
	
	$db = adodb_connect();
  
	if (!$db){
   		echo "Could not connect to database server - please try later.";	
		exit;
	}
	
	if(!empty($_POST["extractSize"])){
		//Get extract file size
		$extractSize=$_POST["extractSize"];
	}
	
	
	//Retrieve the setting information
	$settingInfo = get_Conference_Settings();
	$defaultZipSize= $settingInfo->MaxZipFileSize; //Default Maxzipfilesize
	
	if($extractSize!=$defaultZipSize){
		
		$updateSql='UPDATE '.$GLOBALS["DB_PREFIX"].'Settings SET Value='.$extractSize.' WHERE Name="MaxZipFileSize"';
		
		//Execute the query
		$result=$db -> Execute($updateSql);
	}
	
	$archive_papers = get_papers_in_order();
		
		// If there's no papers, tell the user.
		if (count($archive_papers) == 0)
		{
			do_html_header("No Requested Papers");
			
			echo "<p>No papers have been accepted and scheduled yet. ".
			"Without any scheduling data, the CD structure cannot be built. <br>".
			"If you still wish to export papers, use the \"Extract All Papers\" function instead.";
			do_html_footer();
			exit;
			
			
		}
		
		/* Archive download at the top, so extra characters don't interfer */
	if ( $_POST["download"]) {
		$saveasname = $_POST["SaveAsName"];
		$filename = $_POST["FileName"];
		/* START Archive Download */
		if ( file_exists ( $filename ) )
		{
		   // Send binary filetype HTTP header 
			header('Content-Type: application/octet-stream'); 
			// Send content-length HTTP header 
			header('Content-Length: '.filesize($filename)); 
			// Send content-disposition with save file name HTTP header 
			header('Content-Disposition: attachment; filename="'.$saveasname.'"'); 
			// Output file 
			readfile($filename); 
			// Done 
			exit; 
		}
		/* END Archive Download */
	}
		
		// Set up progress bar
		$bar = new HTML_Progress();
		$bar->setAnimSpeed(100);
		$bar->setIncrement(1);
		$bar->setBorderPainted(true);
		$ui =& $bar->getUI();
		$ui->setCellAttributes('active-color=#000084 inactive-color=#3A6EA5 width=4 spacing=0');
		$ui->setBorderAttributes('width=1 style=inset color=white');
		$ui->setStringAttributes(array(
			'width' => 200,
			'height' => 20,
			'font-size' => 14,
			/*'background-color' => '#C3C6C3',*/
			'valign' => 'top'
		));
		$ui->setCellCount(100);		
		
		// Start building archive
		require_once($php_root_path."/includes/pear/Tar.php");
		require_once($php_root_path."/includes/pear/Zip.php");
		
		
	
	//State the current part of zip file that is being extracted
	if(empty($_POST["currentZipPart"])){
		$currentZipPart=1;
	}
	
	//stores number of zip files to create
	if(!empty($_POST["numOfZip"])){
		$numOfZip=$_POST["numOfZip"];
	}
		
		
		
		if ( $_POST["extract"])
	{

		if(!empty($_POST["currentZipPart"])){
		$currentZipPart=$_POST["currentZipPart"];
		$currentZipPart+=1;
		$paperZipped = $_POST["paperZipped"];
		$sqlCondition = "AND P.PaperId NOT In (".substr($paperZipped,0,strlen($paperZipped)-1).") ";
		
		}
	
	}	
	
		//Set SQL statement
		$paperSql = "SELECT * FROM " . $GLOBALS["DB_PREFIX"] . "Paper P , " . $GLOBALS["DB_PREFIX"] . "PaperStatus PS ";
		$paperSql .= "WHERE P.PaperStatusID = PS.PaperStatusID ";
		$paperSql .= "And P.PaperStatusID=3 ";
		$paperSql .= $sqlCondition;
		
		//Execute the query
		$papersResult = $db -> Execute($paperSql);
		
		if(!empty($_POST["paperZipped"])){
			$paperZipped=$_POST["paperZipped"];
		}
		
		//Get the paperId of the papers extracted
		while($paperInfo = $papersResult -> FetchNextObj())
	{	
		
		//Call the function to get the lastest file of the paper
		$fileInfo = get_latestFile($paperInfo -> PaperID , &$err_message );		
		$paperSize=$fileInfo -> FileSize;
		$totalPaperSize += $paperSize;
		$totalSize += $paperSize;
		
		if ($totalPaperSize<=$extractSize){
			//Store ids of the papers that are to be zipped
			$paperZipped .= $paperInfo -> PaperID.",";	
			$paperNotZipped.=$paperInfo -> PaperID.",";
		}
		if(empty($_POST["numOfZip"]) && $currentZipPart==1){
			if($totalSize>$extractSize){
				$numOfZip+=1;
				$totalSize=$paperSize;
			}
		}
		
							
	}	// End while loop
	
	//Offset the zip file that may not bigger than extractSize
	if(empty($_POST["numOfZip"]) && $currentZipPart==1){
	$numOfZip+=1;
	}
		
		// Get the temp directory to put the archive in
		$tmpDir = get_cfg_var("upload_tmp_dir");
		if (!($tmpDir)) $tmpDir = "/tmp";
		
		//Create the zip file name
	//Attach zipfilename extension if more than 1 zip file created
	if($numOfZip>1){
			$zipExtraHeader = "Part";
			if(!empty($_POST["filename"])){
			$basename = $_POST["filename"];
			$basenameArr=explode("_",$basename);
			$basename=$basenameArr[0]."_".$zipExtraHeader.$currentZipPart;
		}else{	
			$basename = $conferenceInfo -> ConferenceCodeName."_".$zipExtraHeader.$currentZipPart;	
			
		}
	}else if($numOfZip==1){
		$zipExtraHeader="";	
		$basename = $conferenceInfo -> ConferenceCodeName;
	}
		
		// Create the archive file
		switch ($_POST["enctype"])
		{
			case 'bz2':
				$saveasname = "$basename.tar.bz2";
				$filename = $tmpDir."/".$saveasname ;
				$tarFile = new BZip2_Archiver($filename);
				break;
			case 'gz':
				$saveasname = "$basename.tar.gz";
				$filename = $tmpDir."/".$saveasname ;
				$tarFile = new GZip_Archiver($filename);
				break;
			case 'zip':
				$saveasname = "$basename.zip";
				$filename = $tmpDir."/".$saveasname ;
				$tarFile = new Zip_Archiver($filename);
				break;
			default:
				$saveasname = "$basename.tar";
				$filename = $tmpDir."/".$saveasname ;
				$tarFile = new Tar_Archiver($filename);
				break;
		}
		
		$cdindex_path = $php_root_path.$privilege_root_path."/cdindex/";
		ob_start();
		include($cdindex_path."technical_program_cd.php");
		$htmlFile = ob_get_contents();
		ob_end_clean();
		$tarFile -> AddString("technical_program_cd.html",$htmlFile);
			
		ob_start();
		include($cdindex_path."technical_program_web.php");
		$htmlFile = ob_get_contents();
		ob_end_clean();
		$tarFile -> AddString("technical_program_web.html",$htmlFile);
		
		ob_start();
		include($cdindex_path."technical_program_guide.php");
		$htmlFile = ob_get_contents();
		ob_end_clean();
		$tarFile -> AddString("technical_program_guide.html",$htmlFile);
		
		ob_start();
		include($cdindex_path."technical_program_abstracts.php");
		$htmlFile = ob_get_contents();
		ob_end_clean();
		$tarFile -> AddString("technical_program_abstracts.html",$htmlFile);
		
		ob_start();
		include($cdindex_path."technical_program_bios.php");
		$htmlFile = ob_get_contents();
		ob_end_clean();
		$tarFile -> AddString("technical_program_bios.html",$htmlFile);
		
		ob_start();
		include($cdindex_path."author_index.php");
		$htmlFile = ob_get_contents();
		ob_end_clean();
		$tarFile -> AddString("author_index.html",$htmlFile);
		
		// Tell progress bar how many papers need to be processed.
		$bar->setMaximum(count($archive_papers));
		
		
		//Get extract file size
		$extractSize=$_POST["extractSize"];
		$totalPaperSize=0;
		
		// Define archiving proceedure for papers
		function archive_next_paper($percent, &$bar)
		{
			global $archive_papers;
			global $tarFile;
			global $totalPaperSize;
			global $extractSize;
			global $paperNotZipped;
			$paperNotZippedArr=explode(",",$paperNotZipped);
			
			$paperInfo = $archive_papers[$bar -> getValue()];
			if (!$paperInfo) return; // Skip empty entries (applies to 100% too)
			$fileInfo = get_latestFile($paperInfo -> PaperID , &$err_message );
			$paperSize=$fileInfo -> FileSize;
			$totalPaperSize += $paperSize;
			
			//Last element of $paperNotZippedArr is a comma and is not looped. 
			for($i=0;$i<count($paperNotZippedArr)-1;$i++){
				if($paperNotZippedArr[$i]==$paperInfo -> PaperID){
					//echo $paperInfo -> PaperID;
					$fileEnding = strstr($fileInfo -> FileName, '.');
					$data = $fileInfo -> File;
					$name = $paperInfo -> PaperID .$fileEnding;
					$tarFile -> AddString("papers/".$name, $data);
				}
			}
		}
		
		// Tell progress bar to archive a paper for each 'tick'
		$bar->setProgressHandler('archive_next_paper');
		
		// Start building head info
		ob_start();
	
?>

<style type="text/css">
<?php echo $bar->getStyle(); ?>
</style>
<script type="text/javascript">
<?php echo $bar->getScript(); ?>
</script>

<?php
		// Attach head info to page
        
		//$homepage -> AddExtraHeadData(ob_get_contents());
		ob_end_clean();
		
		do_html_header("Compiling CD Structure...");
		
		
?>
<center>
<span class="ProgressBar">
<?php 
echo $bar->toHtml(); 
?>
</span>

<style type="text/css">
/* This line hides the download link initially */
.DownloadLink {display: none}
</style>

<span class="DownloadLink">
<form name="form1" method="post">
<input type=hidden name="SaveAsName" value="<?php echo $saveasname ?>">
<input type=hidden name="FileName" value="<?php echo $filename ?>">
<input type=hidden name="paperZipped" value="<?php echo $paperZipped ?>">
<input type=hidden name="currentZipPart" value="<?php echo $currentZipPart ?>">
<input type=hidden name="extractSize" value="<?php echo $extractSize ?>">
<input type=hidden name="numOfZip" value="<?php echo $numOfZip ?>">
<input type=hidden name="enctype" value="<?php echo $_POST["enctype"] ?>">
 <input type="submit" name="download" id="download" disabled value="Download <?php if($currentZipPart!=$numOfZip && $numOfZip>1){echo $zipExtraHeader.$currentZipPart;}else if($currentZipPart==$numOfZip && $numOfZip>1){echo $zipExtraHeader.$currentZipPart;}?>">
  <input type="submit" name="extract" DISABLED id="extract" 
  value="Extract <?php if($currentZipPart!=$numOfZip){$extractName=$currentZipPart; $extractName+=1; echo $zipExtraHeader.$extractName;}else if($currentZipPart==$numOfZip && $numOfZip>1){echo $zipExtraHeader.$currentZipPart;}?>">
</form>
</span>
</center>

<?php
		do_html_footer();
		
		// Set the timeout to infinity [run() will take a while]
		set_time_limit ( 0 ) ;
		
		// Page has reached "end", but now code is added to move progress bar.
		$bar->run();
		
		//Allow the Extract button to be in sync with Download button
			//Only enable extract button when there are more than 1 zip file and so on.
			if($numOfZip>1){
				
			echo '<script language="JavaScript" type="text/javascript" >' ;						
			echo "document.form1.download.disabled=false" ;
			echo '</script>' ;
			
				if($currentZipPart==$numOfZip){
				
			echo '<script language="JavaScript" type="text/javascript" >' ;						
			echo "document.form1.extract.disabled=true" ;
			echo '</script>' ;
			
			
			}else if($currentZipPart!=$numOfZip){
				
				echo '<script language="JavaScript" type="text/javascript" >' ;						
				echo "document.form1.extract.disabled=false" ;
				echo '</script>' ;
			}
		}else{
			
			echo '<script language="JavaScript" type="text/javascript" >' ;						
			echo "document.form1.download.disabled=false" ;
			echo '</script>' ;
			
			echo '<script language="JavaScript" type="text/javascript" >' ;						
			echo "document.form1.extract.disabled=true" ;
			echo '</script>' ;
		}
?>

<style type="text/css">
/* The progress bar must have completed, so show the download link */
.ProgressBar  {display: none}
.DownloadLink {display: block}
</style>

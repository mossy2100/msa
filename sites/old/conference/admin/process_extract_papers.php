<?php 
	define(START, 0); //First record to retrieve
	define (FINISH, 10000); // Last record to retrieve

	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");		
	session_start();	
	require_once("includes/libzip.php");	
	// extract ( $_SESSION , EXTR_REFS ) ;	
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	
	//Code added  to address security problem reported by Sebastian Held 14-Nov-2006
	$header = "Extract Papers" ;
	$accepted_privilegeID_arr = array ( 3 => "" ) ;
	$accepted_phaseID_arr = array ( 1 => "" , 2 => "" , 3 => "" , 4 => "" ) ;
	authentication( $header , $accepted_privilegeID_arr , $accepted_phaseID_arr , $homepage , $php_root_path , $GLOBALS["DB_PREFIX"] , &$err_message ) ;		
	
	class File_Archiver
	{
		function File_Archiver( $filename ) {}
		function AddString( $archival_path, $data ) {}
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
	
	//State the current part of zip file that is being extracted
	if(empty($_POST["currentZipPart"])){
		$currentZipPart=1;
	}
	
	// Get the temp directory to zip to	
	$tmpDir = get_cfg_var("upload_tmp_dir");
    if (!($tmpDir)) $tmpDir = "/tmp";
	
	if ( $_POST["download"])
	{
		$saveasname = $_POST['zipfilename'] ;
        //Problem with magic quotes:  \ gets converted to \\ in $_POST variables.
        //Turn off magic quotes in php.ini!
        //; Magic quotes for incoming GET/POST/Cookie data.
        //  magic_quotes_gpc = Off
		//$filename = $_POST["tmpDir"].'/'.$saveasname ;
        
        // Safer code if magic quotes is on
        $filename = $tmpDir.'/'.$saveasname ;
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
			exit ; 
		}
        else
        {
            echo "Problem finding file '$filename' - please try again.";
            exit;
        }
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
	
	//Make the SQL statement according to user selection
	switch($_POST["extractType"]){
		case 1:
			//All papers from paper table
			$papersSQL	= "SELECT * FROM " . $GLOBALS["DB_PREFIX"] . "Paper P, " . $GLOBALS["DB_PREFIX"]."Track T ";
			$papersSQL	.= "WHERE P.TrackID = T.TrackID ";
			$papersSQL	.= $sqlCondition;
			$papersSQL	.= "ORDER BY P.TrackID ASC, P.PAPERID ASC";
			$dirName = "papers";
			break;	
		case 2:
			//Accepted papers
			$papersSQL	= "SELECT * FROM " . $GLOBALS["DB_PREFIX"] . "Paper P," . $GLOBALS["DB_PREFIX"]."PaperStatus PS, " . $GLOBALS["DB_PREFIX"]."Track T ";
			$papersSQL .= " WHERE P.PaperStatusID = PS.PaperStatusID";	
			$papersSQL .= " AND P.TrackID = T.TrackID";	
			$papersSQL .= " AND PS.PaperStatusName = 'Accepted' ";	
			$papersSQL	.= $sqlCondition;
			$papersSQL .= " ORDER BY P.TrackID ASC, P.PAPERID ASC";	
			$dirName = "accepted_papers";			
			break;
		case 3:
			//Rejected papers
			$papersSQL	= "SELECT * FROM " . $GLOBALS["DB_PREFIX"] . "Paper P," . $GLOBALS["DB_PREFIX"]."PaperStatus PS, " . $GLOBALS["DB_PREFIX"]."Track T ";
			$papersSQL .= " WHERE P.PaperStatusID = PS.PaperStatusID";
			$papersSQL .= " AND P.TrackID = T.TrackID";	
			$papersSQL .= " AND PS.PaperStatusName = 'Rejected' ";	
			$papersSQL	.= $sqlCondition;
			$papersSQL .= " ORDER BY P.TrackID ASC, P.PAPERID ASC";
			$dirName = "rejected_papers";			
			break;
		case 4:
			//All papers except withdrawn
			$papersSQL	= "SELECT * FROM " . $GLOBALS["DB_PREFIX"] . "Paper P, " . $GLOBALS["DB_PREFIX"]."Track T";
			$papersSQL	.= " WHERE P.TrackID = T.TrackID ";
			$papersSQL	.= " AND P.Withdraw = 'false' ";
			$papersSQL	.= $sqlCondition;
			$papersSQL	.= " ORDER BY P.TrackID ASC, P.PAPERID ASC";
			$dirName = "papers_not_withdrawn";			
			break;			
		case 5:
			//Withdrawn papers
			$papersSQL	= "SELECT * FROM " . $GLOBALS["DB_PREFIX"] . "Paper P, " . $GLOBALS["DB_PREFIX"]."Track T";
			$papersSQL	.= " WHERE P.TrackID = T.TrackID ";
			$papersSQL	.= " AND P.Withdraw = 'true' ";
			$papersSQL	.= $sqlCondition;
			$papersSQL	.= " ORDER BY P.TrackID ASC, P.PAPERID ASC";
			$dirName = "withdrawn_papers";			
			break;
	
	}
	
	//Execute the query
	$papersResult = $db -> Execute($papersSQL);
	
	if(!$papersResult){
		die ("Could not retrieve papers from database - please try again");
	}
	
	if(empty($_POST["totalpapers"])) // part 1
			$totalpapers = $papersResult -> RecordCount();
	else
			$totalpapers = $_POST["totalpapers"];
			
	
	if( ($papersResult -> RecordCount() ) == 0){
		do_html_header("No Requested Papers");
		echo "<p>There are no papers of the requested type. <br>You may wish to try to extract other types.</p><a href=\"extract_papers.php\">Back</a>";
		do_html_footer();
		exit;
	}
	
	//stores number of zip files to create
	if(!empty($_POST["numOfZip"])){
		$numOfZip=$_POST["numOfZip"];
	}
	
	//Determine the size of zip file according to user selection
	if(!empty($_POST["extractSize"])){
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

	//Attach zipfilename extension if more than 1 zip file created
	if($numOfZip>1){
		$zipExtraHeader = "Part";
		
	}else{
		$zipExtraHeader="";
		
	}

	//Create the zip file name
	if($numOfZip>1){
	//Insert extra zip header when more than 1 zip file created
	if(!empty($_POST["zipfilename"])){
		$zipfilename = $_POST["zipfilename"];
		$zipArr=explode("_",$zipfilename);
		$zipfilename=$zipArr[0]."_".$zipExtraHeader.$currentZipPart.".zip";
	}else{	
			$conferenceInfo = get_conference_info();
			$zipfilename = $conferenceInfo -> ConferenceCodeName."_".$zipExtraHeader.$currentZipPart.".zip";	
		}
	}else if($numOfZip==1){
		$conferenceInfo = get_conference_info();
		$zipfilename = $conferenceInfo -> ConferenceCodeName.".zip";
	}
	
	//Start creating zip file
	$zipfile = new Zip_Archiver($tmpDir."/". $zipfilename); 	
	
	do_html_header("Extract Papers");
	
?>
<form name="downloadForm" id="downloadForm" method="post" action="process_extract_papers.php">
	<p>File extraction in progress...</p>
	<input name="status" READONLY type="text" id="status" value="0 % Completed" size="30" maxlength="30">
  <input type="hidden" name="zipfilename" value="<?php echo $zipfilename ; ?>">
  <input type="hidden" name="totalpapers" value="<?php echo $totalpapers ; ?>">
  <input type="hidden" name="papersSQL" value="<?php echo $papersSQL ; ?>">
  <input type="hidden" name="tmpDir" value="<?php echo $tmpDir ; ?>">
  <input type="hidden" name="currentZipPart" value="<?php echo $currentZipPart ; ?>">
  <input type="hidden" name="paperZipped" value="<?php echo $paperZipped ; ?>">
  <input type="hidden" name="numOfZip" value="<?php echo $numOfZip ; ?>">
<input type="hidden" name="zipExtraHeader" value="<?php echo $zipExtraHeader ; ?>">
  <input type="hidden" name="extractType" value="<?php echo $_POST["extractType"] ; ?>">
  <input type="hidden" name="extractSize" value="<?php echo $extractSize ; ?>">
  <p>
  <input type="submit" name="download" DISABLED id="download" value="Download <?php if($currentZipPart!=$numOfZip && $numOfZip>1){echo $zipExtraHeader.$currentZipPart;}else if($currentZipPart==$numOfZip && $numOfZip>1){echo $zipExtraHeader.$currentZipPart;}?>">
  <input type="submit" name="extract" DISABLED id="extract" 
  value="Extract <?php if($currentZipPart!=$numOfZip){$extractName=$currentZipPart; $extractName+=1; echo $zipExtraHeader.$extractName;}else if($currentZipPart==$numOfZip && $numOfZip>1){echo $zipExtraHeader.$currentZipPart;}?>">
  </p>
</form>	
<?php	
	do_html_footer();
	
	// Set the timeout to infinity
	set_time_limit ( 0 ) ;
	
	//reset to zero
	$totalPaperSize=0;
	
	//Execute the query
	$papersResult = $db -> Execute($papersSQL);		
	
	//Only setup table of contents once for the complete index.html
	if($currentZipPart==1){
			
			//set properties for papers zipped
			$currentPage = 1;  //Current Page
			$full_currentPage=1; //Current Page for complete index.html
			$currentTrack = 0; //Current Track
			$i = 0; //Set loop counter
		
			$full_TableOfContents="<html><head>\n<title> Commence Conference System </title>\r\n" ;
			$full_TableOfContents .= "<meta http-equiv='Content-Type' content='text/html'; charset='iso-8859-1'>\r\n </head>\n" ;
			$full_TableOfContents .= "<h1>Commence Conference System</h1><br>\r\n" ;
			$full_TableOfContents .= "<table width='100%' border='1' cellspacing='0' cellpadding='4'>\r\n" ;
			$full_TableOfContents .= "<tr> <td width='70%'> </td> <td width='30%'> </td> </tr>\r\n" ;
			
	}else if($currentZipPart>1 && $currentZipPart<=$numOfZip){
		//Retrieve session values needed for next extraction
		$full_TableOfContents=$_SESSION["full_TableOfContents"];
		$currentPage=1;
		$full_currentPage=$_SESSION["full_currentPage"];
		$currentTrack=$_SESSION["currentTrack"];
		$i=$_SESSION["i"];
		
	}
		
		//Set up this only when there are more than 1 zip file created
		if($numOfZip>1){
			// Set up TableOfContents for part index.html
			$TableOfContents = "<html><head>\n<title> Commence Conference System </title>\r\n" ;
			$TableOfContents .= "<meta http-equiv='Content-Type' content='text/html'; charset='iso-8859-1'>\r\n </head>\n" ;
			$TableOfContents .= "<h1>Commence Conference System</h1><br>\r\n" ;
			$TableOfContents .= "<table width='100%' border='1' cellspacing='0' cellpadding='4'>\r\n" ;
			$TableOfContents .= "<tr> <td width='70%'> </td> <td width='30%'> </td> </tr>\r\n" ;
		}
			
	
	//Loop each paper and add to zip file
	while($paperInfo = $papersResult -> FetchNextObj())
	{	
		//Call the function to get the lastest file of the paper
		$fileInfo = get_latestFile($paperInfo -> PaperID , &$err_message );	
		$paperSize=$fileInfo -> FileSize;
		$totalPaperSize += $paperSize;
		
		//Store ids of the papers that are to be zipped
		if ($totalPaperSize<=$extractSize){
			
		$data = $fileInfo -> File;
		$name = $fileInfo -> FileName;
		
		//Get the file type by fetchinglast 4 characters
		//$fileType = substr($name,strlen($name) - 4); //Fails for .ps
		$fileType = strstr($name,'.') ;
				
		// Prepare the file structure
		$currentFileName = "paper_".$paperInfo -> TrackID."_".$paperInfo -> PaperID.$fileType;
		$filePath = "$tmpDir/$currentFileName";	
		$fileStructure = $dirName."/".basename($filePath);
	
		//add entry to TableOfContents
		$authors = retrieve_authors($paperInfo -> PaperID , &$err_message );
	
		
		// Output Table of Contents Entries
		if ($paperInfo -> TrackID > $currentTrack){ // Output Group Header
			$currentTrack = $paperInfo -> TrackID;
			$TableOfContents .= "<tr><td colspan=2 align=\"center\"><H2>".$TRACK_NAME.": ".$paperInfo -> TrackName."</H2></td></tr>\r\n";
			$full_TableOfContents .= "<tr><td colspan=2 align=\"center\"><H2>".$TRACK_NAME.": ".$paperInfo -> TrackName."</H2></td></tr>\r\n";
			}
			
		if($numOfZip>1){
			
			$TableOfContents .= "<tr> <td> <a href=$currentFileName> #";
			$TableOfContents .= $paperInfo -> PaperID ;
			$TableOfContents .= " " ;
			$TableOfContents .= stripslashes($paperInfo -> Title);
			$TableOfContents .= "</a><br>";
			$TableOfContents .= $authors ;
			$TableOfContents .= "</td> <td>page $currentPage </td></tr>\r\n" ;
		}
 		
		
		$full_TableOfContents .= "<tr> <td> <a href=$currentFileName> #";
		$full_TableOfContents .= $paperInfo -> PaperID ;
		$full_TableOfContents .= " " ;
		$full_TableOfContents .= stripslashes($paperInfo -> Title);
		$full_TableOfContents .= "</a><br>";
		$full_TableOfContents .= $authors ;
		$full_TableOfContents .= "</td> <td>page $full_currentPage </td></tr>\r\n" ;
		
		// Increment current page
		$currentPage +=  $paperInfo -> NumberOfPages ; 
		$full_currentPage +=  $paperInfo -> NumberOfPages ;
		// add the binary data stored in the string 'data' 
		$zipfile->AddString( $fileStructure, $data );
		
/*		// Debug use only
		echo "\$totalpapers= $totalpapers<br>" ;
		echo "\$i= $i<br>" ;
		echo "<br><br><br>" . $percentage_completed . "%<br>";
		exit ;*/

		// Update status
		$percentage = number_format(++$i / $totalpapers * 100, 1) ;	
		$percentage_completed = $percentage . "% Done" ;
		echo '<script language="JavaScript" type="text/javascript" >' ;						
		echo "document.downloadForm.status.value='$percentage_completed'" ;
		echo '</script>' ;	
		}
		
															
	}	// End while loop
		
		//Save values required for next extraction using sessions
		$_SESSION["full_TableOfContents"]=$full_TableOfContents;
		$_SESSION["full_currentPage"]=$full_currentPage;
		$_SESSION["currentTrack"]=$currentTrack;
		$_SESSION["i"]=$i;
		
	//Disable Extract button when no more zip files to file
	if(!empty($_POST["numOfZip"])){
		$numOfZip=$_POST["numOfZip"];	
	}
	
	if($numOfZip>1){
		//Close TableOfContents for part index.html
		$TableOfContents .= "</table></html>" ;
		// Prepare the file structure
		$filePath = $tmpDir."/index(".$zipExtraHeader.$currentZipPart.").htm";	
		$fileStructure = $dirName."/".basename($filePath);
		// add the binary data stored in the string 'TableOfCOntents' 
		$zipfile->AddString( $fileStructure, $TableOfContents );
	}
		
		//Close the complete index.html only at the last zip file 
		if($currentZipPart==$numOfZip){
			$full_TableOfContents .= "</table></html>" ;
			// Prepare the file structure
			$filePath = $tmpDir."/index.htm";	
			$fileStructure = $dirName."/".basename($filePath);
			// add the binary data stored in the string 'TableOfCOntents' 
			$zipfile->AddString( $fileStructure, $full_TableOfContents );
		}	
	
	
	$fp=fopen($zipfile->filename, 'w') ;
	
	if ($fp) // ok, push the data
	{
		echo '<script language="JavaScript" type="text/javascript" >' ;						
		echo "document.downloadForm.status.value='Creating Zip archive'" ;
		echo '</script>' ;																	
		if (!fwrite($fp, $zipfile ->archive_file-> file()))
		{
			echo "Problem writing to file '$zipfilename' - please try again.";
			exit;		
		}
		else
		{
			echo '<script language="JavaScript" type="text/javascript" >' ;						
			echo "document.downloadForm.status.value='Finished'" ;
			echo '</script>' ;																				
			echo '<script language="JavaScript" type="text/javascript" >' ;						
			echo "document.downloadForm.download.disabled=false" ;
			echo '</script>' ;	
			
			//Allow the Extract button to be in sync with Download button
			//Only enable extract button when there are more than 1 zip file and so on.
			if($numOfZip>1){
				if($currentZipPart==$numOfZip){
				
			echo '<script language="JavaScript" type="text/javascript" >' ;						
			echo "document.downloadForm.extract.disabled=true" ;
			echo '</script>' ;
			
			//Unset sessions used only when the last zipfile is extracted
			unset($_SESSION["full_currentPage"]) ;
			unset($_SESSION["i"]) ;
			unset($_SESSION["currentTrack"]) ;
			
			}else if($currentZipPart!=$numOfZip){
				
				echo '<script language="JavaScript" type="text/javascript" >' ;						
				echo "document.downloadForm.extract.disabled=false" ;
				echo '</script>' ;
			}
		}else{
			
			echo '<script language="JavaScript" type="text/javascript" >' ;						
			echo "document.downloadForm.extract.disabled=true" ;
			echo '</script>' ;
		}
						
		}
	}	// End if $fp condition		
	
?>

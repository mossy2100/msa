<?php
	//Establish connection with database
	$php_root_path = ".." ;
	require_once("$php_root_path/includes/include_all_fns.inc");		
	session_start();
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;

	$header = "View PDF-Report" ;
	$accepted_privilegeID_arr = array ( 1 => "" ) ;
	$accepted_phaseID_arr = array ( 1 => "" , 2 => "" , 3 => "" , 4 => "" ) ;
	authentication( $header , $accepted_privilegeID_arr , $accepted_phaseID_arr , $homepage , $php_root_path , $GLOBALS["DB_PREFIX"] , &$err_message ) ;

	//Establish connection with database
	$db = adodb_connect( &$err_message );

	$sql = "SELECT FR.File,FR.FileSize FROM " . $GLOBALS["DB_PREFIX"] . "File_report FR , " . $GLOBALS["DB_PREFIX"] . "File F , " . $GLOBALS["DB_PREFIX"] . "Paper P" ;
	$sql .= " WHERE F.FileID=" . $_GET["fileid"] . " AND FR.FileID=F.FileID AND F.PaperID=P.PaperID AND P.MemberName='" . $_SESSION["valid_user"] . "' AND Withdraw='false'" ;
	$result = $db -> Execute($sql);
	if ($result) $rows = $result -> RecordCount() ;

	if (!$result )
	{
		do_html_header("View PDF-report Failed" , &$err_message ) ;	
		$err_message .= " Could not connect to File_report database.<br>\n";	
		$err_message .= "<br><br> Try <a href='/conference/user/view_report.php?fileid=" . $_GET["fileid"] . "'>again</a>?" ;		
		do_html_footer( &$err_message );		
		exit;
	}
	else if ( !$rows )
	{
		do_html_header("View PDF-report Failed" , &$err_message ) ;	
		$err_message .= " The requested PDF-report is not available.<br>\n";	
		$err_message .= "<br><br> Try <a href='/conference/user/view_report.php?fileid=" . $_GET["fileid"] . "'>again</a>?" ;		
		do_html_footer( &$err_message );		
		exit;
	}	

	$row = $result -> FetchNextObj();
	$data = $row -> File;
	$name = "report.pdf";
	$size = $row -> FileSize;
	$type = "application/pdf";

	// Check for Internet Explorer to avoid inline PDF viewing bug

	$browser = getBrowser( ) ;
	if ($browser == "IEWin")
	{
		$method = "attachment" ;
	}
	else
	{
		$method = "inline" ;
	}
	
	
	header("Cache-control: private");	
	header("Content-type: $type" );
	header("Content-length: $size");	
	header("Content-Disposition: $method; filename=$name");	
	header("Content-Description: PHP Generated Data");
	echo $data;
?>

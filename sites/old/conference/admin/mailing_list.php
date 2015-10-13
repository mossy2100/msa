<?php 
	
	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");	
	session_start();
	// extract ( $_SESSION , EXTR_REFS ) ;	
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	
	//Establish connection with database
	$db = adodb_connect( &$err_message );
	
	//Get the get and session variables
	if($_GET["recipientGroupName"])
		$recipientGroupName = $_GET["recipientGroupName"];
	else
		$recipientGroupName = $_POST["recipientGroupName"];
		
	if($_GET["letterID"]){		
		$letterID = $_GET["letterID"];
		unset($_SESSION["arrCurrentRecords"]);
	}
	else
		$letterID = $_POST["letterID"];
				
	//Fetch letter information
	$letterInfo = get_Letter_Info($letterID);			
	
	//Retrieve all the email
	$arrEmails = get_Already_Sent_EmailList($letterID);

	function limit_view_emails(&$emailSQL, &$sort , &$showing , &$sortStr , $max_view_emails = 5 ){

		//Check the sorting by Title
		switch($sort){
				case 1:
					$emailSQL .= " ORDER BY M.MemberName ASC";
					$sortStr = "UserName - Ascending";
					break;
				case 2:
					$emailSQL .= " ORDER BY M.MemberName DESC";
					$sortStr = "UserName - Descending";
					break;
				case 3:
					$emailSQL .= " ORDER BY R.Email ASC";
					$sortStr = "Email - Ascending";
					break;
				case 4:
					$emailSQL .= " ORDER BY R.Email DESC";
					$sortStr = "Email - Descending";
					break;																
				case 5:
					$emailSQL .= " ORDER BY R.FirstName ASC";
					$sortStr = "Fullname - Ascending";
					break;
				case 6:
					$emailSQL .= " ORDER BY R.FirstName DESC";
					$sortStr = "Fullname - Descending";
					break;								
				default:
					$emailSQL .= " ORDER BY M.MemberName ASC";
					$sortStr = "UserName - Ascending";
					break;							
		}
		
		//Limit the records according to max_view_emails
		$emailSQL .= " LIMIT ".$showing.",".$max_view_emails;
		
		return $emailSQL;
	
	}//end of function
	
	if($_POST["showing"])
		$_GET["showing "] = $_POST["showing"];
		
	if($_POST["sort"])
		$_GET["sort"] = $_POST["sort"];
	
	//Call function to evaluate showing
	$_GET["showing"] = evaluate_showing($_GET["showing"]);	
	
	$emailSQL = get_Recipient_Group_SQL($recipientGroupName);
	$emailResult = $db -> Execute($emailSQL);
	$num_rows = $emailResult -> RecordCount();
			
	$emailSQL = limit_view_emails($emailSQL, $_GET["sort"] , $_GET["showing"] ,$sortStr ,MAX_EMAILS);
	//echo $emailSQL;
	$emailResult = $db -> Execute($emailSQL);
	
	if(!$emailResult){
		echo "Could not retrieve user email information - try again";
		exit;
	}
	
	$limit_rows = $emailResult -> RecordCount();
	
	//Update the session accordingly
	if(($_POST["to"]) || isset($_SESSION["arrUpdateEmails"])){
		/*
		echo "<br>Both post and session.<br>";
		
		echo "<br><br>Session \$arrUpdateEmails Array is <pre>";
		print_r($_SESSION["arrUpdateEmails"]);
		echo "</pre><br>Current Record \$arrCurrentRecords array is <pre>";
		print_r($_SESSION["arrCurrentRecords"]);
		echo "</pre><br>";*/
		
		if(count($_SESSION["arrCurrentRecords"]) > 0)
			//Get the array without previous records
			$arrDiff = array_diff_assoc($_SESSION["arrUpdateEmails"],$_SESSION["arrCurrentRecords"]);
		else
			$arrDiff = $_SESSION["arrUpdateEmails"];
		/*
		echo "<br>Diff Array array is <pre>";
		print_r($arrDiff);
		echo "</pre><br>";
		echo "<br>Post Array array is <pre>";
		print_r($_POST["to"]);
		echo "</pre><br>";*/				
		
		//Merge the session array with post array
		$_SESSION["arrUpdateEmails"] = array_merge($arrDiff,$_POST["to"]);
		$arrUpdateEmails = & $_SESSION["arrUpdateEmails"];		
		
		/*
		echo "<br>Merge Array now is <pre>";
		print_r($_SESSION["arrUpdateEmails"]);
		echo "</pre><br>";*/		
		
	}
	/*else if(isset($_SESSION["arrUpdateEmails"])){
	
		echo "<br>Session Only.<br>";	
		//The session of $arrUpdateEmail exisit
		$arrUpdateEmails = & $_SESSION["arrUpdateEmails"];		
	
	}*/	
	else {
		//echo "<br>Very first time.<br>";	
	
		$arrUpdateEmails = get_Unsended_EmailList($letterID,$recipientGroupName);		
		if(count($arrUpdateEmails) > 0)
			$_SESSION["arrUpdateEmails"] = $arrUpdateEmails;
		else
			$_SESSION["arrUpdateEmails"] = array();
	}			

	$arrCurrentRecords = array();
	//All the members of a certain limited by max_view_emails
	for($i = 0;$i < $limit_rows;$i++){
		$emailInfo = $emailResult -> FetchNextObj();
		//list($memberName,$email) = each($arrCurrentRecords);
		$arrCurrentRecords[$emailInfo -> MemberName] = $emailInfo -> Email;
		$arrEmailInfo[$i]["memberName"] = $emailInfo -> MemberName;
		$arrEmailInfo[$i]["email"] = $emailInfo -> Email;
		//$arrEmailInfo[$i]["memberName"] = $memberName;
		//$arrEmailInfo[$i]["email"] = $email;		

		//The user modified the chcke boxes
		/*
		if($_POST["to"]){			
			while(list($memberName,$email) = each($_POST["to"])){
				if($emailInfo -> MemberName == $memberName)
					$arrEmailInfo[$i]["check"] = "checked";
			}
		}
		else */ 
		if(count($arrUpdateEmails) > 0){
			if(arrayKeyExists($emailInfo -> MemberName,$arrUpdateEmails))
				$arrEmailInfo[$i]["check"] = "checked";
		}/*
		else if(count($arrEmails) > 0){ //No session
			if(!arrayKeyExists($emailInfo -> MemberName,$arrEmails)) 
				$arrEmailInfo[$i]["check"] = "checked";				
		}*/
		//else //There email has not sent to anybody yet.
			//$arrEmailInfo[$i]["check"] = "checked";
		
		$sent = "<font color=\"#0000FF\"><strong>Sent</strong></font>";
		$notsent = "<font color=\"#FF0000\"><strong>Not sent</strong></font>";
		
		//Evaluate the email status
		if(count($arrEmails) > 0){//Some emails has been sent
			if(arrayKeyExists($emailInfo -> MemberName,$arrEmails))			
				$arrEmailInfo[$i]["status"] = $sent ;
			else
				$arrEmailInfo[$i]["status"] = $notsent ;						
		}else //No emails has been sent out		
			$arrEmailInfo[$i]["status"] = $notsent ;	
		
	} //end of record set loop
	
	//Reset the array
	reset($arrEmailInfo);
	if($_GET["sort"] == 7) { //Arrary is to be sort by "status" ascending
		asort($arrEmailInfo);
		$sortStr = "Email Status - Ascending";
	}else if($_GET["sort"] == 8) { //Arrary is to be sort by "status" descending
		arsort($arrEmailInfo);
		$sortStr = "Email Status - Descending";			
	}	
	
	//Insert the current records into session
	$_SESSION["arrCurrentRecords"] = $arrCurrentRecords;
	
	//Call the function to display the range of records
	$from = evaluate_records_range($_GET["showing"],$num_rows,MAX_EMAILS);					
	//Call the function to evaluate prev
	$prev = evaluate_prev($_GET["sort"],$_GET["showing"],$num_rows,MAX_EMAILS);
	//Call the function to evaluate next
	$next = evaluate_next($_GET["sort"],$_GET["showing"],$num_rows,MAX_EMAILS);
	//Call the function to evaluate page links
	$pagesLinks = evaluate_pages_links($_GET["sort"],$_GET["showing"],$num_rows,MAX_EMAILS);
	
	//Evalute the JavaScript for $prev
	$js = array() ;	
	$querystring_array = get_querystring_from_href ( $prev ) ;
	$dlimit = count ( $querystring_array ) ;
	for ( $d = 0 ; $d < $dlimit ; $d++ )
	{
		$js[$d] = " \"javascript:papercheckbox( 'mailing_list.php' , '" . $querystring_array[$d] . "')\" " ;
	}
	$prev = insert_js_call_in_href ( $js , $prev ) ;
	$prev = delete_href ( $prev ) ;		
	
	//Evaluate the JavaScript for $next
	$js = array() ;	
	$querystring_array = get_querystring_from_href ( $next ) ;
	$dlimit = count ( $querystring_array ) ;
	for ( $d = 0 ; $d < $dlimit ; $d++ )
	{
		$js[$d] = " \"javascript:papercheckbox( 'mailing_list.php' , '" . $querystring_array[$d] . "')\" " ;
	}
	$next = insert_js_call_in_href ( $js , $next ) ;
	$next = delete_href ( $next ) ;		
	
	
	//Evaluate the JavaScript for $pagelinks
	$js = array() ;	
	$querystring_array = get_querystring_from_href ( $pagesLinks ) ;
	$dlimit = count ( $querystring_array ) ;
	for ( $d = 0 ; $d < $dlimit ; $d++ )
	{
		$js[$d] = " \"javascript:papercheckbox( 'mailing_list.php' , '" . $querystring_array[$d] . "')\" " ;
	}
	$pagesLinks = insert_js_call_in_href ( $js , $pagesLinks ) ;
	$pagesLinks = delete_href ( $pagesLinks ) ;
	
?>
<html>
<head>
<title>Commence Conference System</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?php echo "<link href=\"$php_root_path/stylesheets/CommentStyle.css\" rel=\"stylesheet\" type=\"text/css\">\n"; ?>
<script language="JavaScript">
<!-- Hide script from older browsers

function papercheckbox( mylink , query )
{
	document.frmMailList.action = ( mylink + query ) ;
	document.frmMailList.submit();
}

// End hiding script from older browsers -->
</script>
</head>
<h1><?php echo $letterInfo -> Title; ?></h1>
<body>
<?php 	
	
	if($emailResult -> RecordCount() == 0){
		echo "The requested recipientgroup returns no records. Try others recipient group.";
		exit;	
	}
?>
<form name="frmMailList" method="post" action="update_mailing_list.php">
  <table width="90%" border="0" cellspacing="0" cellpadding="2">
    <tr> 
      <td colspan="4"><input type="submit" name="Submit" value="Update"> <input name="Button" type="button" onClick="JavaScript:window.close()" value="Close"> 
        <input type="submit" name="Submit" value="Reset"><input type="hidden" name="letterID" value="<?php echo $letterID; ?>"> 
        <input type="hidden" name="recipientGroupName" value="<?php echo $recipientGroupName; ?>"></td>
      <td align="right"><a href="/conference/admin/view_emails_list.php?letterID=<">view 
        email list</a></td>
    </tr>
    <tr> 
      <td colspan="4">&nbsp;</td>
      <td align="right">&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="3"><strong><?php echo $sortStr;?></strong></td>
      <td colspan="2" align="right">Recipient Group: <strong><?php echo $letterInfo -> RecipientGroupName; ?></strong></td>
    </tr>
    <tr>
      <td colspan="3">&nbsp;</td>
      <td colspan="2" align="right">&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="3"><?php echo "From :<strong>".$from."</strong>"; ?></td>
      <td colspan="2" align="right"><?php echo $prev; ?> | <?php echo $pagesLinks; ?>| 
        <?php echo $next; ?></td>
    </tr>
    <tr> 
      <td colspan="5">&nbsp;</td>
    </tr>
    <tr> 
      <td width="5%"><strong>To:</strong></td>
      <td width="20%"><?php $urlStr = "javascript:papercheckbox( 'mailing_list.php' , '?sort=1&showing=".$_GET["showing"] . "')" ; echo format_Sorting_URL($urlStr,"ASC"); ?>&nbsp; 
        <strong>User Name</strong>&nbsp;<?php $urlStr = "javascript:papercheckbox( 'mailing_list.php' , '?sort=2&showing=".$_GET["showing"] . "')" ; echo format_Sorting_URL($urlStr,"DESC"); ?>
      </td>
      <td width="25%"><?php $urlStr = "javascript:papercheckbox( 'mailing_list.php' , '?sort=3&showing=".$_GET["showing"] . "')" ; echo format_Sorting_URL($urlStr,"ASC"); ?>&nbsp;	
        <strong>Email Address</strong>&nbsp;<?php $urlStr = "javascript:papercheckbox( 'mailing_list.php' , '?sort=4&showing=".$_GET["showing"] . "')" ; echo format_Sorting_URL($urlStr,"DESC"); ?>	
      </td>
      <td width="20%" align="center"><?php $urlStr = "javascript:papercheckbox( 'mailing_list.php' , '?sort=7&showing=".$_GET["showing"] . "')" ; echo format_Sorting_URL($urlStr,"ASC"); ?>&nbsp;<strong>Email Status</strong>&nbsp;<?php $urlStr = "javascript:papercheckbox( 'mailing_list.php' , '?sort=8&showing=".$_GET["showing"] . "')" ; echo format_Sorting_URL($urlStr,"DESC"); ?></td>
      <td width="30%"><?php $urlStr = "javascript:papercheckbox( 'mailing_list.php' , '?sort=5&showing=".$_GET["showing"] . "')" ; echo format_Sorting_URL($urlStr,"ASC"); ?>&nbsp;	
        <strong>Full Name</strong>&nbsp<?php $urlStr = "javascript:papercheckbox( 'mailing_list.php' , '?sort=6&showing=".$_GET["showing"] . "')" ; echo format_Sorting_URL($urlStr,"DESC"); ?>
      </td>
    </tr>
    <?php 
	
		$r = 0;
		
		foreach($arrEmailInfo as $arrUserInfo){
		//for($r = 0; $r < count($arrEmailInfo); $r++){
			//Change the background color of each row
			if($r % 2)
				echo "<tr bgcolor=\"#FFFFFF\">";
			else
				echo "<tr bgcolor=\"#CCFFFF\">";
	?>
    <td><input type="checkbox" name="to[<?php echo $arrUserInfo["memberName"]; ?>]" value="<?php echo $arrUserInfo["email"]; ?>"		
			<?php 
			 
			 //Evaluate the array to check the check box
			 /*
			 if(isset($_SESSION["arrUpdateEmails"])){
			 	if(arrayKeyExists($memberName,$arrUpdateEmails)) echo "checked";
			 }		
			 else if(count($arrEmails) > 0){
			 	if(!arrayKeyExists($memberName,$arrEmails)) 
					echo "checked";
			 }else
			 	echo "checked";*/
				echo $arrUserInfo["check"];
			?>				
	  ></td>
    <td><?php echo $arrUserInfo["memberName"]; ?></td>
    <td><?php echo $arrUserInfo["email"]; ?></td>
    <td align="center"><?php echo $arrUserInfo["status"]; ?></td>
    <td><?php echo getMemberFullName($arrUserInfo["memberName"]); ?></td>
    </tr>
    <?php $r++; } ?>
    <tr> 
      <td colspan="5"> 
        <?php /*	
	if ($_POST["to"])
	{
		while(list($memberName,$email) = each($_POST["to"]))
		{
			$write = true ;
			for ( $r=0 ; $r < count($arrEmailInfo) ; $r++ )
			{
				if ( $arrEmailInfo[$r]["memberName"] == $memberName && $arrEmailInfo[$r]["check"] )				
				{
					$write = false ;
					break ; //Go out of for loop				
				}
			}
			if ( $write )
			{
				echo "<input type=\"hidden\" value=\"" .$arrEmailInfo[$r]["email"]. "\" name=\"to[".$arrEmailInfo[$r]["memberName"]."]\">\n" ;	
			}
		}
	}*/
	?>
      </td>
    </tr>
    <tr> 
      <td colspan="3">Total: <strong><?php echo $num_rows; ?></strong></td>
      <td colspan="2" align="right"><?php echo $prev; ?> | <?php echo $pagesLinks; ?>| 
        <?php echo $next; ?></td>
    </tr>
  </table>
</form>
</body>
</html>

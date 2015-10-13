<?php
	//define(MAXRECORDS,10);	// records per page

	$php_root_path = ".." ;
	$privilege_root_path = "/reviewer" ;
	
	require_once("includes/include_all_fns.inc");
//	session_cache_limiter('private') ;			
	session_start() ;
	header("Cache-control: private");	

	$err_message = " Unable to process your request due to the following problems: <br>\n" ;	
	$header = "Paper Bids" ;
	$accepted_privilegeID_arr = array ( 2 => "" ) ;
	$accepted_phaseID_arr = array ( 2 => "" ) ;
	//authentication( $header , $accepted_privilegeID_arr , $accepted_phaseID_arr , $homepage , $php_root_path , $GLOBALS["DB_PREFIX"] , &$err_message ) ;		
	
	if(is_null($_SESSION["view"]) && is_null($_POST["view"])){
	define("MAXRECORDS",10);}
	else{	
	
			 if(!is_null($_POST["view"]))
			  $_SESSION['view']=$_POST["view"]; //subsequent accesses
			  
			 if($_SESSION['view']=="all")
			  define("MAXRECORDS",$_SESSION['num']); // generate $num_rows on subsequent access
			 else
			  define("MAXRECORDS",$_SESSION['view']);	
	}	
	
	function setup_view_all_bids( &$num_rows , &$dbprefix , $err_message = "" )
	{
		//Establish connection with database
        $db = adodb_connect( &$err_message );
        
        //global $_SESSION ;
		
		//Retrive the necessary information
		$selectionSQL = "SELECT * FROM " . $GLOBALS["DB_PREFIX"] . "Paper PP , " . $GLOBALS["DB_PREFIX"] . "Selection S , " . $GLOBALS["DB_PREFIX"] . "Preference P";
		$selectionSQL .= " WHERE PP.PaperID = S.PaperID";
		$selectionSQL .= " AND S.PreferenceID = P.PreferenceID";
		$selectionSQL .= " AND S.Membername = '".$_SESSION["valid_user"]."'";
		$selectionSQL .= " AND PP.Withdraw = 'false'";
		$result = $db -> Execute($selectionSQL);
		if ( !$result )
		{
			$err_message .= " Unable to query database. <br>\n" ;
			return NULL ;
		}

		$num_rows = $result -> RecordCount();
		
		if ($num_rows <= 0)
		{
			$selectionSQL = " You have not bid on any papers yet. <br>\n";
		}	
		
		return $selectionSQL ;
	}
	
	function limit_view_all_bids ( &$selectionSQL , &$sort , &$showing , $max_view_per_page = MAX_PAPER , $err_message = "" )
	{
		//Establish connection with database
        $db = adodb_connect( &$err_message );
        
        //Check the sorting by Title
		switch( $sort )
		{
			case 1:
			{
				$selectionSQL .= " ORDER BY S.PreferenceID ASC";
				break;
			}
			case 2:
			{
				$selectionSQL .= " ORDER BY S.PreferenceID DESC";
				break;
			}
			case 3:
			{
				$selectionSQL .= " ORDER BY PP.Title ASC";
				break;
			}
			case 4:
			{
				$selectionSQL .= " ORDER BY PP.Title DESC";
				break;			
			}
			case 5:
			{
				$selectionSQL .= " ORDER BY S.PaperID ASC";
				break;
			}
			case 6:
			{
				$selectionSQL .= " ORDER BY S.PaperID DESC";
				break;			
			}			
			default:
			{
				$selectionSQL .= " ORDER BY S.PaperID";
				break;							
			}
		}							
			
		//Limit the records to the maximun papers per page
		$selectionSQL .= " LIMIT ".$showing.",".MAXRECORDS;	
		$selectionResult = $db -> Execute($selectionSQL) ;
		if ( !$selectionResult )
		{
			$err_message .= " Unable to query database. <br>\n" ;
			return NULL ;
		}
		
		return $selectionResult ;				
	}
	
	if ( $_POST["showing"] )
	{
		$_GET["showing"] = $_POST["showing"] ;
	}
	if ( $_POST["sort"] )
	{
		$_GET["sort"] = $_POST["sort"] ;
	}
	if ( $_POST["storepapers"] )
	{
		$_POST["papers"] = $_POST["storepapers"] ;
	}		
	
	//Call function to evaluate showing
	$_GET["showing"] = evaluate_showing($_GET["showing"]) ;
	if ( isset ( $_GET["err"] ) )
	{
		$error_array["papers"][0] = "You must choose at least one paper before you can update<br>\n" ;
	}	
	
	$num_rows = 0 ;
	$i = 0 ;	
	$array = array() ;	
	if ( ( $selectionSQL = setup_view_all_bids( $num_rows , $GLOBALS["DB_PREFIX"] , &$err_message ) ) !== NULL )
	{
		if ( $num_rows > 0 )
		{
			if ( ( $selectionResult = limit_view_all_bids ( $selectionSQL , $_GET["sort"] , $_GET["showing"] , MAXRECORDS , &$err_message ) ) !== NULL )
			{
				while ( $paperInfo = $selectionResult -> FetchNextObj() )
				{							
					//Get the lastest file of the paper				
					if ( ( $FileIDData = get_latestFileID( $paperInfo->PaperID , &$err_message ) ) === false )
					{
						do_html_header("View Paper Bids Failed" , &$err_message) ;					
						$err_message .= " Could not execute \"get_latestFile\" in \"edit_paper_bids.php\". <br>\n" ;
				  		$err_message .= "<br><br> Try <a href='" . $_SERVER["PHP_SELF"] . "?" . $_SERVER["QUERY_STRING"] . "'>again</a>?" ;
						do_html_footer(&$err_message);		
						exit ;									
					}
					$array[$i]["paperid"] = $paperInfo->PaperID ;
					$array[$i]["bidname"] = $paperInfo->PreferenceName ;
					$array[$i]["fileid"] = $FileIDData -> FileID ;
					$array[$i]["papertitle"] = stripslashes( $paperInfo -> Title ) ;
					if ( $_POST["papers"] )
					{	
						foreach ( $_POST["papers"] as $some => $postpaperid )
						{
							if ( $array[$i]["paperid"] == $postpaperid )
							{
								$array[$i]["check"] = "checked" ;
							}
						}
					}					
					$i++ ;
				}
			}
			else
			{
				do_html_header("View Paper Bids Failed" , &$err_message) ;
				$err_message .= " Could not execute \"limit_view_all_bids\" in \"edit_paper_bids.php\" <br>\n" ;				
				do_html_footer(&$err_message);		
				exit ;			
			}
		}
		else
		{
			do_html_header("View My Bids" , &$err_message) ;
			echo $selectionSQL ;
			do_html_footer(&$err_message);		
			exit ;
		}
	}
	else
	{
		do_html_header("View My Bids Failed" , &$err_message) ;	
		$err_message .= " Could not execute \"setup_view_all_bids\" in \"edit_paper_bids.php\" <br>\n" ;
		do_html_footer(&$err_message);		
		exit ;					
	}
	$_SESSION['num']=$num_rows;
	//Call the function to display the range of records
	$from = evaluate_records_range($_GET["showing"],$num_rows,MAXRECORDS);					
	//Call the function to evaluate prev
	$prev = evaluate_prev($_GET["sort"],$_GET["showing"],$num_rows,MAXRECORDS);
	//Call the function to evaluate next
	$next = evaluate_next($_GET["sort"],$_GET["showing"],$num_rows,MAXRECORDS);
	//Call the function to evaluate page links
	$pagesLinks = evaluate_pages_links($_GET["sort"],$_GET["showing"],$num_rows,MAXRECORDS);
	
	$js = array() ;	
	$querystring_array = get_querystring_from_href ( $prev ) ;
	$dlimit = count ( $querystring_array ) ;
	for ( $d = 0 ; $d < $dlimit ; $d++ )
	{
		$js[$d] = " \"javascript:papercheckbox( 'edit_paper_bids.php' , '" . $querystring_array[$d] . "' )\" " ;
	}
	$prev = insert_js_call_in_href ( $js , $prev ) ;
	$prev = delete_href ( $prev ) ;		
	
	$js = array() ;	
	$querystring_array = get_querystring_from_href ( $next ) ;
	$dlimit = count ( $querystring_array ) ;
	for ( $d = 0 ; $d < $dlimit ; $d++ )
	{
		$js[$d] = " \"javascript:papercheckbox( 'edit_paper_bids.php' , '" . $querystring_array[$d] . "' )\" " ;
	}
	$next = insert_js_call_in_href ( $js , $next ) ;
	$next = delete_href ( $next ) ;		
	
	$js = array() ;	
	$querystring_array = get_querystring_from_href ( $pagesLinks ) ;
	$dlimit = count ( $querystring_array ) ;
	for ( $d = 0 ; $d < $dlimit ; $d++ )
	{
		$js[$d] = " \"javascript:papercheckbox( 'edit_paper_bids.php' , '" . $querystring_array[$d] . "' )\" " ;
	}
	$pagesLinks = insert_js_call_in_href ( $js , $pagesLinks ) ;
	$pagesLinks = delete_href ( $pagesLinks ) ;
		
	do_html_header("View My Bids" , &$err_message) ;		
?>

<script language="JavaScript">
<!-- Hide script from older browsers

function papercheckbox( mylink , query )
{
	document.frmPaper.action = ( mylink + query ) ;
	document.frmPaper.submit();
}



// End hiding script from older browsers -->
</SCRIPT>

 <form name="frmPaper" method="post" action="update_biddings.php?<?php echo "sort=" . $_GET["sort"] . "&showing=" . $_GET["showing"] ; ?>">
<!--<form name="frmPaper" method="post" action="phpinfo.php?<?php // echo "sort=" . $_GET["sort"] . "&showing=" . $_GET["showing"] ; ?>"> -->
<!-- <form name="frmPaper" method="post" action="phpinfo.php">-->
  <table width="100%" border="0" cellspacing="5" cellpadding="0">
    <tr> 
      <td height="27" colspan="2">From: <?php echo "<strong>$from</strong>" ; ?></td>
      <td height="27" align="center">Total Papers: <?php echo $num_rows ; ?></td>
      <td width="32%"><?php echo $prev; ?> | <?php echo $pagesLinks; ?>| <?php echo $next; ?> </td>
    </tr>
    <tr> 
      <td width="1%">&nbsp;</td>
      <td align="right">Order by:<a href="<?php echo "javascript:papercheckbox( 'edit_paper_bids.php' , '" ; ?>?sort=5&showing=<?php echo $_GET["showing"] . "')" ; ?>"><img src="<?php echo $php_root_path ; ?>/images/up.gif" border=0></a> 
        <strong>ID</strong> <a href="<?php echo "javascript:papercheckbox( 'edit_paper_bids.php' , '" ; ?>?sort=6&showing=<?php echo $_GET["showing"] . "')" ; ?>"><img src="<?php echo $php_root_path ; ?>/images/down.gif" border=0></a> 
      </td>
      <td><div align="left">|&nbsp;<a href="<?php echo "javascript:papercheckbox( 'edit_paper_bids.php' , '" ; ?>?sort=3&showing=<?php echo $_GET["showing"] . "')" ; ?>"><img src="<?php echo $php_root_path ; ?>/images/up.gif" border=0></a> 
        <strong>Paper Title</strong> <a href="<?php echo "javascript:papercheckbox( 'edit_paper_bids.php' , '" ; ?>?sort=4&showing=<?php echo $_GET["showing"] . "')" ; ?>"><img src="<?php echo $php_root_path ; ?>/images/down.gif" border=0></a> 
   	|	<a href="<?php echo "javascript:papercheckbox( 'edit_paper_bids.php' , '" ; ?>?sort=1&showing=<?php echo $_GET["showing"] . "')" ; ?>"><img src="<?php echo $php_root_path ; ?>/images/up.gif" border=0></a> 
        <strong>Bid Status</strong> <a href="<?php echo "javascript:papercheckbox( 'edit_paper_bids.php' , '" ; ?>?sort=2&showing=<?php echo $_GET["showing"] . "')"; ?>"><img src="<?php echo $php_root_path ; ?>/images/down.gif" border=0></a> 
     </div> </td>
      <td  rowspan="2%" align="right">
      </form><form name="view"  method="post" action="edit_paper_bids.php">
View papers per page	
	<select name="view">	
		<option value ="10" <?php if($_SESSION['view']==10) echo "SELECTED"; ?> >10 </option>
		<option value ="50" <?php if($_SESSION['view']==50) echo "SELECTED"; ?> >50 </option>
		<option value ="100" <?php if($_SESSION['view']==100) echo "SELECTED"; ?> >100 </option>
		<option value ="200" <?php if($_SESSION['view']==200) echo "SELECTED"; ?> >200 </option>
		<option value ="all" <?php if($_SESSION['view']=="all") echo "SELECTED"; ?> >All </option>
	</select>	
<input type="submit" value="Go"></form>
 <form name="frmPaper2" method="post" action="update_biddings.php?<?php echo "sort=" . $_GET["sort"] . "&showing=" . $_GET["showing"] ; ?>">
      </td>
    </tr>
    <tr> 
      <td colspan="4"><hr></td>
    </tr>
<?php
	for ( $r=0 ; $r < $i ; $r++ )
	{
		if ( $r%2 )
		{
	    	echo "<tr bgcolor=\"#FFFFCC\">" ;
		}
		else
		{
	    	echo "<tr bgcolor=\"#CCFFFF\">" ;
		}
?>	
      <td><input type="checkbox" name="papers[]" value="<?php echo $array[$r]["paperid"] ; ?>" <?php echo $array[$r]["check"] ; ?>></td>
      <td width="10%"><a href="<?php echo "javascript:papercheckbox( 'view_abstract.php' , '?paperid=" . $array[$r]["paperid"] . "&sort=" . $_GET["sort"] . "&showing=" . $_GET["showing"] . "' )" ; ?>">&nbsp;&nbsp;#<?php echo $array[$r]["paperid"] ; ?></a><br> &nbsp;&nbsp<?php 

	$prefs = get_Num_Preferences($array[$r]["paperid"]);
	switch( $prefs )
		{
		case 0: 
			{
				echo "<font color=\"#FF0000\">0&nbsp;bids</font>";
				break;
			}
		case 1: 
			{
				echo "<font color=\"#FF0000\">1&nbsp;bid</font>";
				break;
			}
		case 2: 
			{
				echo "<font color=\"#FF0000\">2&nbsp;bids</font>";
				break;
			}
		default: 
			{
				echo "All&nbsp;in";
				break;
			}
		}
	 ?>
</td>
      <td width="45%"><a href="/conference/reviewer/view_file.php?fileid=<"fileid"] ; ?>"><?php echo $array[$r]["papertitle"] ; ?></a></td>
      <td><a href="<?php echo "javascript:papercheckbox( 'update_biddings.php' , '?paperid=" . $array[$r]["paperid"] . "&sort=" . $_GET["sort"] . "&showing=" . $_GET["showing"] . "&process=update" . "' )" ; ?>"><?php echo $array[$r]["bidname"] ; ?></a></td>	  
    </tr>
<!--	<tr><td colspan="4"><hr></td></tr> -->
<?php
	}
?>
    <tr> 
<?php	
	if ( $_POST["papers"] )
	{	
		foreach ( $_POST["papers"] as $some => $postpaperid )
		{
			$write = true ;
			for ( $r=0 ; $r < $i ; $r++ )
			{
				if ( $array[$r]["paperid"] == $postpaperid && $array[$r]["check"] )				
				{
					$write = false ;
					break ;				
				}
			}
			if ( $write )
			{
				echo "<input type=\"hidden\" value=\"" . $postpaperid . "\" name=\"papers[]\">\n" ;	
			}
		}
	}
?>
      <td colspan="4"><hr></td>
    </tr>
    <tr> 
	  <input type="hidden" value="update" name="process">
	  <input type="hidden" value="edit_paper_bids.php" name="referer">	  	
      <td colspan="3"><input type="submit" name="Submit" value="Change Selected Bids"><?php echo "<font color=\"#FF0000\">&nbsp;&nbsp;" . $error_array["papers"][0] . "</font>" ; ?></td>
      <td><?php echo $prev; ?> | <?php echo $pagesLinks; ?>| <?php echo $next; ?></td>
    </tr>
  </table>
</form>
<?php

do_html_footer(&$err_message) ;

?>

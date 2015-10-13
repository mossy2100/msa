<?php //////////// REVIEWER PHASE 2 ///////////////	
	
	$php_root_path = ".." ;
	$privilege_root_path = "/reviewer" ;
//	extract ( $_POST , EXTR_REFS ) ;

	require_once("includes/include_all_fns.inc");		
	require_once("$php_root_path/includes/page_includes/page_fns.php"); // numCategories()
	session_start() ;
//	extract ( $_SESSION , EXTR_REFS ) ;
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	$header = "Update Paper Bids" ;
	$accepted_privilegeID_arr = array ( 2 => "" ) ;
	$accepted_phaseID_arr = array ( 2 => "" ) ;
	authentication( $header , $accepted_privilegeID_arr , $accepted_phaseID_arr , $homepage , $php_root_path , $GLOBALS["DB_PREFIX"] , &$err_message ) ;			


			
	function redisplay( &$paperid_array , &$process , &$dbprefix , $err_message = "" )
	{
        //Establish connection with database
        $db = adodb_connect( &$err_message );
        
		//global $_SESSION ;
		
		$i = 0;
		$array = array() ;
			
		reset ( $paperid_array ) ;
		foreach( $paperid_array as $some => $paperID )
		{
			//Get the paper information
			if ( ( $paperInfo = get_paper_info($paperID , &$err_message ) ) === false )
			{
				do_html_header("Update Paper Bids Failed" , &$err_message );	
				$err_message .= " Cannot retrieve information from database. <br>\n" ;
				$err_message .= "<br><br> Try <a href='" . $_SERVER["PHP_SELF"] . "?" . $_SERVER["QUERY_STRING"] . "'>again</a>?" ;				
				do_html_footer(&$err_message) ;
				exit ;
			}
			$array[$i]["paperid"] = $paperInfo -> PaperID ;
			$array[$i]["papertitle"] = stripslashes ( $paperInfo -> Title ) ;
			
			//Get the lastest file of the paper				
			if ( ( $FileIDData = get_latestFile($paperID , &$err_message ) ) === false )
			{
				do_html_header("Update Paper Bids Failed" , &$err_message );				
				$err_message .= " Could not execute \"get_latestFile\" in \"update_biddings.php\". <br>\n" ;
				$err_message .= "<br><br> Try <a href='" . $_SERVER["PHP_SELF"] . "?" . $_SERVER["QUERY_STRING"] . "'>again</a>?" ;				
				do_html_footer(&$err_message) ;
				exit ;				
			}
			$array[$i]["fileid"] = $FileIDData -> FileID ;
			
			if ( $catcomsep = getSelectedCategoryCommaSeparated($paperInfo->PaperID , &$err_message ) || (numCategories( &$err_message ) == 0) )
			{
				$array[$i]["cat"] = $catcomsep ;
			}
			else
			{
				do_html_header("Update Paper Bids Failed" , &$err_message );				
				$err_message .= " Could not execute \"getSelectedCategoryCommaSeparated\" in \"update_biddings.php\". <br>\n" ;
				$err_message .= "<br><br> Try <a href='" . $_SERVER["PHP_SELF"] . "?" . $_SERVER["QUERY_STRING"] . "'>again</a>?" ;				
				do_html_footer(&$err_message) ;
				exit ;				
			}		  
			
			if ( $authors = retrieve_authors( $paperInfo->PaperID , &$err_message ) )
			{
				$array[$i]["author"] = $authors ;
			}
			else
			{
				do_html_header("Update Paper Bids Failed" , &$err_message );				
				$err_message .= " Could not execute \"retrieve_authors\" in \"update_biddings.php\". <br>\n" ;
				$err_message .= "<br><br> Try <a href='" . $_SERVER["PHP_SELF"] . "?" . $_SERVER["QUERY_STRING"] . "'>again</a>?" ;				
				do_html_footer(&$err_message) ;
				exit ;				
			}
			
			if ( $process === "update" )
			{
				$preferenceSQL = " SELECT PreferenceID FROM " . $GLOBALS["DB_PREFIX"] . "Selection " ;
				$preferenceSQL .= " WHERE PaperID = ". $paperInfo->PaperID;
				$preferenceSQL .= " AND Membername = '" . $_SESSION["valid_user"] . "'";
				$preferenceResult = $db -> Execute($preferenceSQL);
				if ( !$preferenceResult )
				{
					do_html_header("Update Paper Bids Failed" , &$err_message );				
					$err_message .= " Could not query \"Selection\" table in database by \"redisplay()\" of \"update_biddings.php\". <br>\n" ;
					$err_message .= "<br><br> Try <a href='" . $_SERVER["PHP_SELF"] . "?" . $_SERVER["QUERY_STRING"] . "'>again</a>?" ;				
					do_html_footer(&$err_message) ;
					exit ;							
				}
				$userPreference = $preferenceResult -> FetchNextObj() ;
				$array[$i]["bidid"] = $userPreference -> PreferenceID ;			
			}
			
			if ( $bidtable = Generate_Preference_Radio_Input_Table( $paperInfo->PaperID , $array[$i]["bidid"] , &$err_message )	)
			{
				$array[$i]["bid"] = $bidtable ;
			}
			else
			{
				do_html_header("Update Paper Bids Failed" , &$err_message );				
				$err_message .= " Could not execute \"retrieve_authors\" in \"update_biddings.php\". <br>\n" ;
				$err_message .= "<br><br> Try <a href='" . $_SERVER["PHP_SELF"] . "?" . $_SERVER["QUERY_STRING"] . "'>again</a>?" ;				
				do_html_footer(&$err_message) ;
				exit ;							
			}
			
			$i++;
				
		} //End of for loop
		return $array ;
	}
	
//	$papers_str = "" ;	
	$storepapers_str = "" ;
	
	// from bid_all_papers or edit_paper_bids to update_biddings to view_abstract back to update_biddings
	if ( $_POST["storepapers"] )	// for getting back stored checked boxes from view_abstract.	
	{
		foreach ( $_POST["storepapers"] as $some => $id )
		{
			$storepapers_str .= ( "<input type=\"hidden\" value=\"" . $id . "\" name=\"storepapers[]\">\n" ) ;
		}	
	}
	else if ( $_POST["papers"] )	// for storing checked boxes for bid_all_papers and edit_paper_bids.
	{
		reset ( $_POST["papers"] ) ;
		foreach ( $_POST["papers"] as $some => $storepapid )
		{
			$storepapers_str .= "<input type=\"hidden\" value=\"" . $storepapid . "\" name=\"storepapers[]\">\n" ;
		}
	}	
	
    if ( $_GET["paperid"] )
	{
		$_POST["papers"] = array ( $_GET["paperid"] ) ;
	}
	
	if ( $_POST["process"] )
	{
		$_GET["process"] = $_POST["process"] ;
	}
	
	if ( $_POST["showing"] )
	{
		$_GET["showing"] = $_POST["showing"] ;
	}
$showing = $_GET["showing"] ; //define variables for passing to function calls
$sort = $_GET["sort"] ;

	if ( $_POST["sort"] )
	{
		$_GET["sort"] = $_POST["sort"] ;
	}
	
	$array = array() ;
	$limit = 0 ;
	
	if ( count ( $_POST ) > 0 )
	{		
		if ( !$_POST["papers"] )
		{
//				header("Location: edit_paper_bids.php?err=") ;
			if ( strpos ( $_SERVER["HTTP_REFERER"] , "?" ) === false )
			{
				$str = "Location:" . $_SERVER["HTTP_REFERER"] . "?err=" ;
			}
			else
			{
				$str = "Location:" . $_SERVER["HTTP_REFERER"] . "&err=" ;				
			}
//					echo $str ;
			header($str) ;
			exit ;
		}				
		
		if ( $_POST["Submit"] == "Submit" )
		{		
			$selection = array() ;
			$numpapers = count ( $_POST["papers"] ) ;
			//Loop the total numbers of papers and put them into array called selection	
			for( $i=0 ; $i < $numpapers ; $i++ )
			{	
				$papid = $_POST["papers"][$i] ;
				$selection[$papid] = $_POST["selection".$papid] ;
			}
					
			if ( $_POST["process"] == "update" )
			{
				//Call the function to insert the information to database
				if ( ( $result = update_selection($selection , &$err_message ) ) !== NULL )
				{	
					if ( $result )
					{
						do_html_header("Updating Paper Bids Successful" , &$err_message );
						echo $result;
						do_html_footer(&$err_message);
						exit ;						
					}
					else
					{
						do_html_header("Bids not updated" , &$err_message ) ;
						$err_message .= " No changes to bids detected. <br>\n" ;						
					}
				}
				else
				{
					do_html_header("Updating Paper Bids Failed" , &$err_message );
					$err_message .= " Could not execute \"update_selection\" in \"update_biddings.php\". <br>\n";
					$err_message . "<br><br> Try <a href='/conference/reviewer/update_biddings.php'>again</a>?" ;
				}
			}
			else if ( $_POST["process"] == "insert" )
			{
				//Call the function to insert the information to database
				if ( $result = select_paper($selection , &$err_message ) )
				{	
					do_html_header("Process Paper Bids Successful" , &$err_message );	
					echo $result;
					do_html_footer(&$err_message);					
					exit ;
				}
				else
				{
					do_html_header("Process Paper Bids Failed" , &$err_message );
					$err_message .= "Could not execute \"select_paper\" in \"bid_all_papers.php\". <br>\n" ;
					$err_message .= "<br><br> Try <a href='/conference/reviewer/bid_all_papers.php'>again</a>?" ;
				}			
			}
		}
		else
		{
			do_html_header("Update Paper Bids" , &$err_message );
		}
	}
	else 
	{
		if ( count ( $_POST ) == 0 )
		{	
		
		}
		do_html_header("Update Paper Bids" , &$err_message );
	}	
	
$array = redisplay( $_POST["papers"] , $_POST["process"] , $GLOBALS["DB_PREFIX"] , &$err_message ) ;
if ( !( $limit = count ( $array ) ) )
{
//	do_html_header("Update Paper Bids" , &$err_message );			
	$err_message .= " You have not selected any papers to update the bids. <br>\n" ;
	do_html_footer(&$err_message) ;		
	exit ;
}				
	
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

<form name="frmPaper" method="post" action="update_biddings.php">
  <table width="100%" border="0" cellspacing="2" cellpadding="1">
    <?php
	for ( $i = 0 ; $i < $limit ; $i++ )
	{
?>
    <tr> 
      <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr> 
            <td colspan="2"> <strong><?php echo "Paper " . $array[$i]["paperid"] . " : " . $array[$i]["papertitle"] ; ?></strong> 
            </td>
          </tr>
          <tr> 
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr> 
            <td colspan="2">Area: 
              <?php 
	echo $array[$i]["cat"] ;			
?>
            </td>
          </tr>
          <tr> 
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr> 
            <td colspan="2">Authors: 
              <?php 
	echo $array[$i]["author"] ;
?>
            </td>
          </tr>
          <tr> 
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr> 
            <td colspan="2"> 
              <?php 
	echo $array[$i]["bid"] ;   
?>
            </td>
            <input type="hidden" name="papers[]" value="<?php echo $array[$i]["paperid"] ; ?>">
          </tr>
          <tr> 
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr> 
            <td>&nbsp;<a href="<?php echo "javascript:papercheckbox( 'view_abstract.php' , '?paperid=" . $array[$i]["paperid"] . "&sort=" . $_GET["sort"] . "&showing=" . $_GET["showing"] . "' )" ; ?>" >View 
              Abstract</a> | <a href='/conference/reviewer/view_file.php?fileid=<' >View 
              Paper</a> </td>
            <td>&nbsp;</td>
          </tr>
          <tr> 
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
        </table></td>
    </tr>
    <tr> 
      <td><hr></td>
    </tr>
    <?php //Increment the $i
	}
	
//	echo $papers_str ;
	
	echo $storepapers_str ;
	
	if ( isset ( $_POST["referer"] ) && $_POST["referer"] != "update_biddings.php" )
	{
		$_POST["myreferer"] = $_POST["referer"] ;
	}
?>
    <tr> 
	  <input type="hidden" value="<?php echo $_GET["sort"] ; ?>" name="sort">  
	  <input type="hidden" value="<?php echo $_GET["showing"] ; ?>" name="showing">
	  <input type="hidden" value="<?php echo $_GET["process"] ; ?>" name="process">
	  <input type="hidden" value="update_biddings.php" name="referer">	  
	  <input type="hidden" value="<?php echo $_POST["myreferer"] ; ?>" name="myreferer">	  
      <td> <input type="submit" name="Submit" value="Submit"> <input name="Submit" type="submit" id="Submit" value="Cancel" onClick="<?php echo "javascript:papercheckbox( '" . $_POST["myreferer"] . "' , '' )" ; ?> "> 
      </td>
    </tr>
  </table>
</form>
<?php 

do_html_footer(&$err_message);

?>

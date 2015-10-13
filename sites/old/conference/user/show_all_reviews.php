<?php

	$php_root_path = ".." ;
	require_once("$php_root_path/includes/include_all_fns.inc");	
	session_start();
//	extract ( $_SESSION , EXTR_REFS ) ;	
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	
	
	$header = "Show All Reviews" ;
	$accepted_privilegeID_arr = array ( 1 => "" ) ;
	$accepted_phaseID_arr = array ( 4 => "" ) ;
	authentication( $header , $accepted_privilegeID_arr , $accepted_phaseID_arr , $homepage , $php_root_path , $GLOBALS["DB_PREFIX"] , &$err_message ) ;		
	
	/*
	if ( !check_valid_user( &$err_message ) )
	{
		$homepage->showmenu = 0 ;
		//This user is not login
		do_html_header("Show All Reviews Failed" , &$err_message ) ;			
		$err_message .= " Sorry, You must login to see all reviews about this paper. <br>\n";
		$err_message .= "<br><br> Go to <a href='$php_root_path/index.php'>Login</a> page." ;
		do_html_footer( &$err_message );
		exit;
	}			

	if ( $_SESSION["phase"]->phaseID != 4 )
	{ 
		$homepage->showmenu = 0 ;	
		do_html_header("Show All Reviews Failed" , &$err_message );	
		$err_message .= " The requested infomation is not available at this phase. <br>\n";
		$err_message .= "<br><br> Try <a href='/conference/user/show_all_reviews.php?paperid=".$_GET["paperid"]."'>again</a>?" ;
		do_html_footer( &$err_message );		
		exit ;
	}		
	*/
	do_html_header("Show All Reviews" , &$err_message );
    
    //Establish connection with database
	$db = adodb_connect( &$err_message );
	
	//Get the paper information
	$paperInfo = get_paper_info($_GET["paperid"]);
		
	//Get the lastest file of the paper				
	$FileIDData = get_latestFile($_GET["paperid"] , &$err_message );
	
	//Retrieve the information from Review Table
	$reviewSQL = "SELECT * FROM " . $GLOBALS["DB_PREFIX"] . "Review";
	$reviewSQL .= " WHERE PaperID='".$_GET["paperid"]."'";
	$reviewSQL .= " AND Comments != ''";	// LOOK OUT FOR NOT NULL	
	$reviewResult = $db -> Execute($reviewSQL);
	$numReviews = $reviewResult -> RecordCount();			

?>
<table width="100%" border="0" cellspacing="0" cellpadding="1">
  <tr> 
    <td><h4><?php echo stripslashes($paperInfo -> Title); ?></h4></td>
  </tr>
  <tr> 
    <td><strong>PaperID:</strong> <?php echo $paperInfo -> PaperID; ?></td>
  </tr>
  <tr> 
    <td>&nbsp;</td>
  </tr>
  <tr> 
    <td><strong>Authors:</strong>&nbsp;<?php echo retrieve_authors($paperInfo -> PaperID);?></td>
  </tr>
  <tr> 
    <td>&nbsp;</td>
  </tr>
  <tr> 
    <td><strong>Keywords:</strong>&nbsp;<?php echo  getSelectedCategoryCommaSeparated($paperInfo->PaperID , &$err_message );?></td>
  </tr>
  <tr> 
    <td><hr></td>
  </tr>
  <?php 
 	
	for($i=0;$i<$numReviews;$i++){
	
	$reviewInfo = $reviewResult -> FetchNextObj();
	
	//Assign the values to the variables
	$appropriateness = $reviewInfo -> AppropriatenessToConference;
	$originality = $reviewInfo -> Originality;
	$tech_strength = $reviewInfo -> TechnicalStrength;
	$presentation = $reviewInfo -> Presentation;
	$overall = $reviewInfo -> OverallEvaluation;	
 
 ?>
  <tr> 
    <td><strong><?php echo "Reviewer " . ( $i + 1 ) . "'s opinion"?></strong></td>
  </tr>
  <tr> 
    <td>&nbsp;</td>
  </tr>
  <tr> 
    <td><strong>Ranking Criteria </strong><br> <table width="70%" border="1" cellspacing="2" cellpadding="1">
        <tr> 
          <td width="70%"><strong>Name</strong></td>
          <td width="30%"><strong>Rank</strong></td>
        </tr>
        <tr> 
          <td>Appropriateness to the Conference:</td>
          <td><?php echo get_ranking($appropriateness); ?></td>
        </tr>
        <tr> 
          <td>Originality:</td>
          <td><?php echo get_ranking($originality); ?></td>
        </tr>
        <tr> 
          <td>Technical Strength:</td>
          <td><?php echo get_ranking($tech_strength); ?></td>
        </tr>
        <tr> 
          <td>Presentation:</td>
          <td><?php echo get_ranking($presentation); ?></td>
        </tr>
        <tr> 
          <td>Overall Evaluation:</td>
          <td><?php echo get_ranking($overall); ?></td>
        </tr>
      </table></td>
  </tr>
  <tr> 
    <td>&nbsp;</td>
  </tr>
  <tr> 
    <td><p><strong>Comments:<br>
        </strong><?php echo nl2br( ereg_replace( "  " , "&nbsp;&nbsp;" , stripslashes( get_comment( $paperInfo->PaperID , $reviewInfo->MemberName , &$err_message ) )));?></p></td>
  </tr>
  <tr> 
    <td><hr></td>
  </tr>
  <?php }//end of for loop?>
</table>
<?php 

	do_html_footer(&$err_message);

?>

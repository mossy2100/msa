<?php 
	
	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");	
	session_start();
	// extract ( $_SESSION , EXTR_REFS ) ;
			
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;			
	
	do_html_header("Edit Reviewers");
	
	//Get the paperID
	$paperID = & $_GET["paperID"];
	
	if(isset($_SESSION["arrReviewers"]))
		$arrReviewers = & $_SESSION["arrReviewers"];
								
?> 
<p>&nbsp;</p>
<form name="form1" method="post" action="confirm_assign_paper.php">
  <table width="100%" border="0" cellspacing="2" cellpadding="1" >
    <?php			
		//Get the paper information
		$paperInfo = get_paper_info($paperID);
				
		//Get the lastest file of the paper				
		$FileIDData = get_latestFile($paperID , &$err_message);
		
		//Get Reviewer of the papers
		$arrEditReviewers = get_Reviewers_Of_Paper($paperID);
		for($i=0;$i<count($arrEditReviewers);$i++){
			if($i == count($arrEditReviewers) - 1)
				$strReviewers .= $arrEditReviewers[$i];
			else
				$strReviewers .= $arrEditReviewers[$i].", ";		
		}								

	?>
	<input type="hidden" value="<?php echo $paperID; ?>" name="paperID">
	<!--Send an hidden value to distinguish between edit reviwers and assign reviewers-->
	<input type="hidden" value="true" name="edit">
    <tr> 
      <td width="5%" valign="top"><div align="center"><?php echo "#" .$paperInfo->PaperID; ?></div></td>
      <td><table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr> 
            <td colspan="2"> <strong><?php echo stripslashes($paperInfo -> Title); ?></strong></td>
          </tr>
          <tr> 
            <td colspan="2">
			</td>
          </tr>
          <tr> 
            <td colspan="2">Track: <?php echo  getSelectedTrackText($paperInfo -> PaperID , &$err_message );?></td>
          </tr>
          <tr> 
            <td colspan="2">Category: <?php echo  getSelectedCategoryCommaSeparated($paperInfo -> PaperID , &$err_message );?></td>
          </tr>
          <tr> 
            <td colspan="2">Authors: <?php echo retrieve_authors($paperID);?></td>
          </tr>
          <tr>
            <td colspan="2">Current Reviewers: <?php echo $strReviewers; ?></td>
          </tr>
		  <tr>
		  	<td>&nbsp;</td>
		  </tr>
          <tr> 
            <td colspan="2">
			<table width="75%" border="2">
				<tr><b>Colour Legend:</b></tr>
  				<tr>
					
                  <td bgcolor=#AAFFAA align="center" width="20%"><font color="#000000">Reviewer 
                    wants to review this paper</font></td>
					
                  <td bgcolor=#E0FFE0 align="center" width="20%"><font color="#000000">Reviewer 
                    is qualified to review this paper</font></td>
					
                  <td bgcolor=#FFE0E0 align="center" width="20%"><font color="#000000">Reviewer 
                    does not know this area wel</font>l</td>
                  <td bgcolor=#FFBBBB align="center" width="20%"><font color="#000000">Reviewer 
                    has a conflict of interest on this paper</font></td>
					
                  <td bgcolor=#CCCCCC align="center" width="20%"><font color="#000000">Reviewer 
                    has not specified preference</font></td>
  				</tr>
			</table>
			</td>
          </tr>
          <tr> 
            <td>&nbsp;</td>
          </tr>
          <tr> 
            <td colspan="2">Assign to:</td>
          </tr>
          <tr> 
            <td colspan="2"><?php echo generateReviewerInputTable($paperInfo->PaperID); ?></td>
          </tr>
          <tr> 
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr> 
            <td>&nbsp;<a href="/conference/admin/view_abstract.php?id=<" target="_blank">View 
              Abstract</a> | <a href='/conference/admin/view_file.php?fileid=<' target='_blank'>View 
              File</a></td>
            <td>&nbsp;</td>
          </tr>
          <tr> 
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
        </table></td>
    </tr>
    <tr> 
      <td colspan="2"><hr></td>
    </tr>
    <tr> 
      <td colspan="2">
        <input type="submit" name="Submit" value="Submit">
        <input name="Submit" type="submit" id="Submit" value="Cancel">
      </td>
    </tr>
  </table>
</form>
<script language='JavaScript' type='text/javascript' src='/conference/admin/script/tooltip.js'></script>
<?php
  do_html_footer();
?>

<?php 
	
	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");	
	session_start();
	// extract ( $_SESSION , EXTR_REFS ) ;	
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;			
	
	$paperID = $_GET["paperID"];
	
	do_html_header("Assign Paper #$paperID");
	
	if($_GET["back"] == "true"){
		$arrReviewers = & $_SESSION["arrReviewers"];	
	}
	else{
		unset( $_SESSION["arrReviewers"]);
	}
								
	//Get the paper information
	$paperInfo = get_paper_info($paperID);
				
	//Get the lastest file of the paper				
	$FileIDData = get_latestFile($paperID , &$err_message );
	
?>
<br>
<form name="form1" method="post" action="confirm_assign_paper.php">
  <table width="100%" border="0" cellspacing="2" cellpadding="1">
	<input type="hidden" value="<?php echo $paperID; ?>" name="paperID"> 
    <tr> 
      <td width="5%" valign="top"><div align="center"><?php echo "#".$paperInfo->PaperID; ?></div></td>
      <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr> 
            <td colspan="2"> <strong><?php echo stripslashes($paperInfo -> Title); ?></strong></td>
          </tr>
          <tr> 
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr> 
            <td colspan="2">Category: <?php echo  getSelectedCategoryCommaSeparated($paperInfo -> PaperID , &$err_message );?></td>
          </tr>
          <tr> 
            <td colspan="2">Authors: <?php echo retrieve_authors($paperID);?></td>
          </tr>
          <tr>
            <td colspan="2">&nbsp;</td>
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
            <td colspan="2">Assign To:</td>
          </tr>
          <tr> 
            <td colspan="2"><?php echo generateReviewerInputTable($paperInfo->PaperID); ?></td>
          </tr>
          <tr> 
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>		  
          <tr> 
            <td>&nbsp;<a href='/conference/admin/view_abstract.php?id=<'>View 
              Abstract</a> | <a href='/conference/admin/view_file.php?fileid=<'>View 
              Paper</a></td>
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
<?php
  do_html_footer();
?>

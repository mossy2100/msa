<?php 
	
	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");	
	session_start();
	// extract ( $_SESSION , EXTR_REFS ) ;	
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	
	if($_POST["Submit"] == "Cancel"){
		unset($_SESSION["arrReviewers"]);
		header("Location: view_all_papers.php");
		exit;
	}
		
	$paperID =& $_POST["paperID"];
	
	do_html_header("Confirm Assignment of Paper #$paperID");

	$paper_str = "paper".$paperID;
	$paper_str  = "\$_POST[\"".$paper_str."\"]";
	eval ("\$arrReviewers= $paper_str;");
	
	$_SESSION["arrReviewers"] = $arrReviewers;
	
?>
<br><br>
<form name="form1" method="post" action="process_assign_paper.php">
  <table width="100%" border="0" cellspacing="2" cellpadding="1">
    <tr> 
      <td colspan="2" valign="top">Below is the paper you 
        are going to assign. Press Confirm to proceed.</td>
    </tr>
    <tr>
      <td colspan="2" valign="top">&nbsp;</td>
    </tr>
    <?php			
		//Get the paper information
		$paperInfo = get_paper_info($paperID);
				
		//Get the lastest file of the paper				
		$FileIDData = get_latestFile($paperID , &$err_message );						

	?>
    <input type="hidden" value="<?php echo $paperID; ?>" name="paperID">
	<!--Send an hidden value to distinguish between edit reviwers and assign reviewers-->
	<?php if(!empty($_POST["edit"])){?>
		<input type="hidden" value="true" name="edit">
	<?php } ?>
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
            <td colspan="2">Assign to:</td>
          </tr>
          <tr> 
            <td colspan="2"> 
              <?php 
			$j = 0;			
			
			for($i=0;$i< count($arrReviewers);$i++)
				echo ++$j.". ".getMemberFullName($arrReviewers[$i])."<br>";
			?>
            </td>
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
      <td colspan="2"> <input type="submit" name="Submit" value="Confirm"> <input name="Submit" type="submit" id="Submit" value="Back"> 
      </td>
    </tr>
  </table>
</form>
<?php
  do_html_footer();
?>

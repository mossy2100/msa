<?php 

	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");	
	session_start();
	// extract ( $_SESSION , EXTR_REFS ) ;		
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	
	//Code added  to address security problem reported by Sebastian Held 14-Nov-2006
	$header = "Add Category" ;
	$accepted_privilegeID_arr = array ( 3 => "" ) ;
	$accepted_phaseID_arr = array ( 1 => "" , 2 => "" , 3 => "" , 4 => "" ) ;
	authentication( $header , $accepted_privilegeID_arr , $accepted_phaseID_arr , $homepage , $php_root_path , $GLOBALS["DB_PREFIX"] , &$err_message ) ;		
	
	$catName = $_POST["catName"];
	
	if($_POST["Submit"] == "Cancel"){
		header("Location: view_categories.php");
		exit;
	}

	if(add_new_category($catName)){
		header("Location: view_categories.php");
		exit;
	}
	else{
		do_html_header("Problem");
		echo "Could not insert the category information - please try again later";
		do_html_footer();
	}
		
?>

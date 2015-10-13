<?php //Include all the functions pages that are required for every page
	//db_connect.inc is already included at main_fns.inc
	//So no need to include db_connect.inc here again
	global $php_root_path ;	// ".."
	global $privilege_root_path ;
	
//	$php_root_path = "../.." ;
//	$privilege_root_path = "/reviewer" ;
/*
extract ( $_GET , EXTR_REFS ) ;
extract ( $_POST , EXTR_REFS ) ;
extract ( $_FILES , EXTR_REFS ) ;
*/
	
	require_once("$php_root_path/includes/data_validation_fns.php");		
	require_once("$php_root_path/includes/user_authen_fns.inc");
	require_once("$php_root_path/includes/db_connect.inc");
	require_once("$php_root_path/includes/main_fns.inc");
	require_once("$php_root_path"."$privilege_root_path/includes/main_fns.inc");
	require_once("$php_root_path"."$privilege_root_path/phase/include_all_phase.inc");
	require_once("$php_root_path/includes/output_fns.inc");	
	
?>
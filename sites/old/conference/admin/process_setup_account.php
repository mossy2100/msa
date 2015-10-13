<?php 

	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");	
	session_start() ;
	// extract ( $_SESSION , EXTR_REFS ) ;	
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	
	//Code added  to address security problem reported by Sebastian Held 14-Nov-2006
	$header = "Setup Account" ;
	$accepted_privilegeID_arr = array ( 3 => "" ) ;
	$accepted_phaseID_arr = array ( 1 => "" , 2 => "" , 3 => "" , 4 => "" ) ;
	authentication( $header , $accepted_privilegeID_arr , $accepted_phaseID_arr , $homepage , $php_root_path , $GLOBALS["DB_PREFIX"] , &$err_message ) ;		
	
	if($_POST["Submit"] == "Back"){
		$url = "Location: setup_new_account.php?accountType=".$_POST["accountType"];
		header($url);
		exit;
	}

	//Generate the random password
	$password = generate_password();	
	
	//Call the function to setup reviewer account
	$result = setup_new_account($_POST,$password);
	
	if($result === true){
		do_html_header("Successful Setup");
		echo "<p>The following account has been setup successfully.<br><br>";
		echo "Account Type: ".$_POST["accountType"]."<br><br>";
		echo "Login Name: <strong>".$_POST["loginname"]."</strong><br>";
		echo "Email Address: <strong>".$_POST["email"]."</strong><br><br>";
		if($_POST["accountType"] == "Reviewer")
			{
			echo "You can either <a href=\"setup_new_account.php?accountType=Reviewer\"> Add </a> another reviewer or <a href=\"view_all_reviewers.php\">View Reviewer Accounts</a>.</p>";
			}
		else
			echo "Go back to <a href=\"view_all_users.php\">View All Users</a>.</p>";
					
		do_html_footer();
	}else{
		do_html_header("Error Information");
		echo "<p>$result</p>";
		do_html_footer();
	}
	
	//Destory the session variable
	unset($_SESSION["arrLoginInfo"]);


?>

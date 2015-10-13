<?php

$DEBUG = false; // enable this, if you want debug messages send to all administrators (use this for first time setup!)

// no need to change anything below




$php_root_path = ".." ;
$privilege_root_path = '/admin' ;
require_once( "$php_root_path/includes/include_all_fns.inc" );
//require_once( $php_root_path . "$privilege_root_path/includes/libmail.php" );

// check which xml-rpc implementation is available
$xmlrpc = check_xmlrpc_impl();
if ($DEBUG) informAdmin( "DEBUG validatePDF_callback.php: the following xmlrpc implementations were detected:\n" . print_r( $xmlrpc, true ) );

//
// START - xmlrpc server part
//

$request_xml = $GLOBALS['HTTP_RAW_POST_DATA'];
if(!$request_xml) {
	$request_xml = $_POST['xml'];
}
if(!$request_xml) {
	$request_xml = $_GET['xml']; // especially for debugging
}

if ($DEBUG) informAdmin( "DEBUG validatePDF_callback.php: received the following XML request:\n" . $request_xml );

if(!$request_xml) {
	echo "<h1>No XML input found!</h1>";
}
else {
	if ($xmlrpc['pear']) {
		// use XML_RPC from pear repository
		unset( $request_xml ); // save memory
		$server = new XML_RPC_Server( array( 'validatePDF_callback' => array('function' => 'pear_validatePDF_callback') ), 1 );
		return; // execution end here
	}

	if ($xmlrpc['internal']) {
		// use internal xml-rpc implementation (>php-4.1.0 experimental)
		// create server
		unset( $GLOBALS['HTTP_RAW_POST_DATA'] ); // save memory
		$xmlrpc_server = xmlrpc_server_create();

		if($xmlrpc_server) {
			xmlrpc_server_register_method($xmlrpc_server, "validatePDF_callback", "internal_validatePDF_callback");

			$response = '';
			echo xmlrpc_server_call_method($xmlrpc_server, $request_xml, $response, array('output_type' => "xml", 'version' => "auto"));

			// free server resources
			$success = xmlrpc_server_destroy($xmlrpc_server);
		}
		return; // execution end here
	}

	// no useable xml-rpc impl. found
	echo 'no useable xml-rpc impl. found';
	informAdmin( "no useable xml-rpc impl. found" );
}


// function for XML_RPC implementation
function pear_validatePDF_callback( $params )
{
	$param = $params->getParam(0);
	$file_data = $param->scalarval(); // binary
	$param = $params->getParam(1);
	$valid = $param->scalarval(); // bool
	$param = $params->getParam(2);
	$magic = $param->scalarval(); // int, needed for authentication -> callback
	$param = $params->getParam(3);
	$fileID = $param->scalarval(); // int
	
	validatePDF_callback( $file_data, $valid, $magic, $fileID );
}

// functions for internal xml-rpc impl.
function internal_validatePDF_callback($method, $params)
{
	$file_data = $params[0]->scalar; // binary
	$valid = $params[1]; // bool
	$magic = $params[2]; // int, needed for authentication -> callback
	$fileID = $params[3]; // int

	validatePDF_callback( $file_data, $valid, $magic, $fileID );
}








function validatePDF_callback( $file_data, $valid, $magic, $fileID )
{
	$file_size = strlen( $file_data );

	global $DEBUG;
	if ($DEBUG) informAdmin( "DEBUG validatePDF_callback.php: called validatePDF_callback( <binary>, $valid, $magic, $fileID )" );

	// sanitize all parameters
	$fileID = intval( $fileID );
	$magic = intval( $magic );
	if ($valid)
		$valid = 1;
	else
		$valid = 0;

	// put result into database
	$err_message = "validatePDF_callback(): ";
	$db = adodb_connect(); // FIXME: fix declaration of adodb_connect() to include the reference (&) symbol!
	if ($db) {
		$file_data = $db->qstr( $file_data ); // escape binary
		$sql = "UPDATE " . $GLOBALS["DB_PREFIX"] . "File_report SET File=$file_data, FileSize=$file_size, DateTime=NOW(), Valid=$valid WHERE FileID = $fileID AND Magic = $magic";
		if (!$db->Execute( $sql ))
			informAdmin( "$err_message executing SQL query:\n" . $db->MetaErrorMsg( $db->MetaError() ) );
	} else
		informAdmin( "$err_message \$db is invalid\n" . $db->MetaErrorMsg( $db->MetaError() ) );
}




?>

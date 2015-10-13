<?php


// this function may need much memory: use  "php_value memory_limit 32M"  in your apache configuration file




// this is the server part for validating PDF-files using xml-rpc calls
//
// actual validation is performed by the Enfocus PitStop server
// validation using ghostscript may also be possible

// CHANGE the following values to suit your setup of PitStop-server:
// in my setup, PitStop runs on top of wine
$hotdir = "/var/spool/pitstop/"; // must end with '/'
$adminMail = "sebastian.held@uni-due.de";
$max_processing_seconds = 10 * 60; // 10 min max. processing time for each pdf-file
$DEBUG = false; // enable this, if you want debug messages send to $adminMail (use this for first time setup!)

// these are the defaults, thus normally no change is needed
// this values are the folder names inside the hotdir
$INP="Input Folder";
$NPDF_ERR="Non-PDF Error Logs";
$NPDF="Non-PDF files";
$ORIG_ERR="Original Docs on Failure";
$ORIG_OK="Original Docs on Success";
$PROC_ERR="Processed Docs on Failure";
$PROC_OK="Processed Docs on Success";
$REP_ERR="Reports on Failure";
$REP_OK="Reports on Success";

// absolute directory of PitStop Input Folder
$INP_absolute = $hotdir . $INP;

// no need to change anything below

set_error_handler("userErrorHandler"); // install error handler: email errors to $adminMail

// check which xml-rpc implementation is available
$xmlrpc = check_xmlrpc_impl();
if ($DEBUG) informAdmin( "DEBUG: the following xmlrpc implementations were detected:\n" . print_r( $xmlrpc, true ) );

function check_xmlrpc_impl()
{
	// check which xml-rpc implementation is available
	@include("../server/utils/utils.php"); // uses xml-rpc implementation of >php-4.1.0 (experimental) or http://sourceforge.net/projects/xmlrpc-epi/
	if (extension_loaded('xmlrpc'))
		$xmlrpc_internal = true;
	else
		$xmlrpc_internal = false;

        // if version of php>=4.3.0 we could use set_include_path(), but for backward compatibility we do it this way:
        $old_value = ini_get( 'include_path' );
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // 'This is a server using Windows!';
            ini_set( 'include_path', 'utils;' . $old_value ); // prepend path to local XML_RPC files (should normally appended, but at least XML_RPC 1.4.8 seems to be broken and there is no way to get the version...; thats why we use our own 1.5.1 version)
        } else {
            // 'This is a server not using Windows!';
            ini_set( 'include_path', 'utils:' . $old_value ); // prepend path to local XML_RPC files (should normally appended, but at least XML_RPC 1.4.8 seems to be broken and there is no way to get the version...; thats why we use our own 1.5.1 version)
        }

        include("XML/RPC/Server.php"); // uses XML_RPC from pear
        ini_set( 'include_path', $old_value ); // restore path

	if (defined( 'XML_RPC_ERROR_INVALID_TYPE' ))
		$xmlrpc_pear = true;
	else
		$xmlrpc_pear = false;
	// check is complete

	$ret['internal'] = $xmlrpc_internal;
	$ret['pear'] = $xmlrpc_pear;
	return $ret;
}


//
// START - xmlrpc server part
//

@$request_xml = $GLOBALS['HTTP_RAW_POST_DATA'];
$VISUAL_DEBUG = false; // no output other than xml
if(!$request_xml) {
	if (!$DEBUG)
		@$request_xml = $_POST['xml'];
	elseif (@$_GET['debug'] === 'true') {
		// debugging is enabled
		// and manual debugging is requested
		$VISUAL_DEBUG = true;
		if (array_key_exists( 'xml', $_GET ))
			$request_xml = $_GET['xml'];
		else {
			// no xml provided; construct a debug sample
			$request_xml = <<<EOT
<?xml version="1.0"?>
<methodCall>
<methodName>validatePDF</methodName>
<params>
<param>
<value><base64>ZGVidWdnaW5nLi4u</base64></value>
</param>
<param>
<value><int>12</int></value>
</param>
<param>
<value><string></string></value>
</param>
<param>
<value><int>0</int></value>
</param>
</params>
</methodCall>
EOT;
			echo '<html><head></head><body>';
			echo "<h1>VISUAL DEBUGGING:</h1><br><br>";
		}
	}
	$GLOBALS['HTTP_RAW_POST_DATA'] = $request_xml; // make this available to XML_RPC impl.
}
if ($DEBUG) informAdmin( "DEBUG: received the following XML request:\n" . $request_xml );
if ($VISUAL_DEBUG) echo "availability of xml implementations:<br><pre>" . print_r( $xmlrpc, true ) . "</pre><br>";
if ($VISUAL_DEBUG) echo "processing the following XML request:<br><pre>" . htmlentities($request_xml) . "</pre><br>";

if(!$request_xml) {
	echo "<h1>No XML input found!</h1>";
}
else {
	if ($xmlrpc['pear']) {
		// use XML_RPC from pear repository
		//informAdmin( "using xmlrpc_pear" );
		if ($VISUAL_DEBUG) echo "==> using XML_RPC (pear)<br>";

		unset( $request_xml ); // not needed for pear   (saves memory)
		$server = new XML_RPC_Server( array( 'validatePDF' => array( 'function' => 'pear_validatePDF' ),
		                                     'validatePDF_2' => array( 'function' => 'pear_validatePDF_2' )
		                                   ), 1  // serviceNow
		                            );
		return; // execution ends here
	}

	if ($xmlrpc['internal']) {
		// use internal xml-rpc implementation (>php-4.1.0 experimental)
		//informAdmin( "using xmlrpc_internal" );
		if ($VISUAL_DEBUG) echo "==> using internal xmlrpc<br>";

		unset( $GLOBALS['HTTP_RAW_POST_DATA'] ); // not needed for internal xml-rpc   (saves memory)
		// create server
		$xmlrpc_server = xmlrpc_server_create();

		if($xmlrpc_server) {
			xmlrpc_server_register_method($xmlrpc_server, "validatePDF", "internal_validatePDF");
			xmlrpc_server_register_method($xmlrpc_server, "validatePDF_2", "internal_validatePDF_2");

			$response = '';
			echo xmlrpc_server_call_method($xmlrpc_server, $request_xml, $response, array('output_type' => "xml", 'version' => "auto"));

			// free server resources
			$success = xmlrpc_server_destroy($xmlrpc_server);
		}
		return; // execution ends here
	}

	// no useable xml-rpc impl. found
	echo 'no useable xml-rpc impl. found';
	informAdmin( __FILE__.":".__LINE__." in ".__FUNCTION__."(): no useable xml-rpc impl. found" );
}

// function for XML_RPC implementation
function pear_validatePDF( &$params )
{
	$data = $params->getParam(0)->scalarval();
	$param = $params->getParam(1);
	$magic = $param->scalarval(); // int, needed for authentication -> callback
	$param = $params->getParam(2);
	$callback_URL = $param->scalarval();
	$param = $params->getParam(3);
	$fileID = $param->scalarval();
	
	$ret = validatePDF( $data, $magic, $callback_URL, $fileID );

	if (!$ret['result']) {
		// error condition
		global $XML_RPC_erruser;
		return new XML_RPC_Response(0, $XML_RPC_erruser+1, $ret['msg']);
	} else
		return new XML_RPC_Response(new XML_RPC_Value(true, 'boolean'));
}

function pear_validatePDF_2( $params )
{
	$param = $params->getParam(0);
	$magic = $param->scalarval(); // int, needed for authentication -> callback
	$param = $params->getParam(1);
	$callback_URL = $param->scalarval();
	$param = $params->getParam(2);
	$fileID = $param->scalarval();
	$param = $params->getParam(3);
	$file = $param->scalarval();

	validatePDF_2( $magic, $callback_URL, $fileID, $file );
}

// functions for internal xml-rpc impl.
function internal_validatePDF($method, $params)
{
	$file_data = $params[0]->scalar; // binary
	$magic = $params[1]; // int, needed for authentication -> callback
	$callback_URL = $params[2];
	$fileID = $params[3];

	$ret = validatePDF( $file_data, $magic, $callback_URL, $fileID );
	if (!$ret['result']) {
		$result['faultCode'] = 801; // to be consistant with XML_RPC implementation
		$result['faultString'] = $ret['msg'];
		return $result;
	} else
		return true;
}

function internal_validatePDF_2($method, $params)
{
	$magic = $params[0]; // int, needed for authentication -> callback
	$callback_URL = $params[1];
	$fileID = $params[2];
	$file = $params[3];

	validatePDF_2( $magic, $callback_URL, $fileID, $file );
}








function validatePDF( &$file_data, $magic, $callback_URL, $fileID )
{
	global $INP_absolute, $DEBUG, $VISUAL_DEBUG;

	if ($DEBUG) informAdmin( "DEBUG: called validatePDF( <binary>, $magic, $callback_URL, $fileID )" );
	if ($VISUAL_DEBUG) echo "called validatePDF( <binary>, $magic, $callback_URL, $fileID )<br>";

	$file_size = strlen( $file_data );
	if ($file_size == 0)
		return array( 'result' => false, 'msg' => '$file_size == 0' );

	// generate temporary file name
	$file = @tempnam( $INP_absolute, 'commence_' );
	if (!$file) {
		$msg = __FILE__.":".__LINE__." in ".__FUNCTION__.": tempnam() failed: error was '$php_errormsg'";
		informAdmin( $msg );
		if ($VISUAL_DEBUG) echo $msg."<br>return()<br>";
		return array( 'result' => false, 'msg' => $msg );
	}
	if (realpath(dirname($file)) != realpath($INP_absolute)) {
		$msg = __FILE__.":".__LINE__." in ".__FUNCTION__.": tempnam() failed: wrong directory; created file was '$file'\n";
		$msg .= realpath(dirname($file)) . "  <==>  " . realpath($INP_absolute);
		informAdmin( $msg );
		if ($VISUAL_DEBUG) echo $msg."<br>return()<br>";
		unlink( $file );
		return array( 'result' => false, 'msg' => $msg );
	}

	// put the file into the input folder inside the hot directory (where PitStop will pick it up)
	$fh = @fopen($file, "wb");
	if ($fh) {
		if (!@fwrite($fh, $file_data)) {
			$msg = __FILE__.":".__LINE__." in ".__FUNCTION__.": fwrite() failed: error was '$php_errormsg'";
			informAdmin( $msg );
			if ($VISUAL_DEBUG) echo $msg."<br>return()<br>";
			return array( 'result' => false, 'msg' => $msg );
		}
	} else {
		$msg = __FILE__.":".__LINE__." in ".__FUNCTION__.": fopen() failed: error was '$php_errormsg'";
		informAdmin( $msg );
		if ($VISUAL_DEBUG) echo $msg."<br>return()<br>";
		return array( 'result' => false, 'msg' => $msg );
	}
	fclose($fh);
	chmod( $file, 0666 ); // make file read/writeable for pitstop server

	if ($DEBUG) informAdmin( "DEBUG: placed incoming file in PitStop hotdir. Full file name: $file" );
	if ($VISUAL_DEBUG) echo "placed incoming file in PitStop hotdir. Full file name: ".htmlentities($file)."<br>";

	// now we have a problem:
	// we want to do the validation in the background, but the calling process waits for an answer from us...
	// we need to start a new process in the background
	startNewThread( $magic, $callback_URL, $fileID, basename($file) );

	return array( 'result' => true );
}


function informAdmin( $msg )
{
	global $adminMail;
	if (function_exists('memory_get_usage'))
		$msg .= "\nMEMORY USAGE: " . memory_get_usage();
	$msg .= "\nini_get( 'memory_limit' ) = " . ini_get( 'memory_limit' );
	$msg .= "\nget_cfg_var( 'memory_limit' ) = " . get_cfg_var( 'memory_limit' );
	mail( $adminMail, 'Commence: validatePDF.php (xmlrpc-server) error', $msg );
}

function startNewThread( $magic, $callback_URL, $fileID, $filename )
// this function uses the webserver to start a new thread (in the background!!!)
// it calls the method validatePDF_2 in this php-file
{
	global $DEBUG, $VISUAL_DEBUG;
//	informAdmin( "startNewThread() called" );
	if ($VISUAL_DEBUG) echo "starting new thread... <br>";
	$req = <<<END
<?xml version="1.0" encoding="iso-8859-1"?>
<methodCall>
<methodName>validatePDF_2</methodName>
<params>
 <param>
  <value>
   <int>$magic</int>
  </value>
 </param>
 <param>
  <value>
   <string>$callback_URL</string>
  </value>
 </param>
 <param>
  <value>
   <string>$fileID</string>
  </value>
 </param>
 <param>
  <value>
   <string>$filename</string>
  </value>
 </param>
</params>
</methodCall>
END;
	$host = $_SERVER['SERVER_NAME'];
	$port = $_SERVER['SERVER_PORT'];
	$path = $_SERVER['PHP_SELF'];

	$fp = fsockopen( $host, $port, $errno, $errstr, 30);
	if (!$fp) {
		informAdmin( "startNewThread(): $errstr ($errno)" );
		if ($VISUAL_DEBUG) echo "startNewThread(): $errstr ($errno)<br>";
	} else {
		$out = "POST $path HTTP/1.1\r\n";
		$out .= "Host: $host:$port\r\n";
		$out .= "Content-Type: text/xml\r\n";
		$out .= "Content-Length: " . strlen($req) . "\r\n\r\n";
		$out .= $req;
	
//		informAdmin( "startNewThread(): request: $out\n" . print_r( $GLOBALS, true ) );
		if ($DEBUG) informAdmin( "DEBUG: starting new thread/process. The next mail must announce 'validatePDF_2()'" );
		if ($VISUAL_DEBUG) echo "startNewThread(): contacted <bold>$host</bold> (have a look in your mailbox)<br>";

		fwrite($fp, $out);
		fflush($fp);
		fclose($fp);
	}
}

function validatePDF_2( $magic, $callback_URL, $fileID, $file )
{
	global $hotdir, $max_processing_seconds, $INP, $NPDF_ERR, $NPDF, $ORIG_ERR, $ORIG_OK, $PROC_ERR, $PROC_OK, $REP_ERR, $REP_OK, $INP_absolute, $xmlrpc, $DEBUG;

	ignore_user_abort(); // function may take a long time
	set_time_limit( $max_processing_seconds + 10 ); // does not work in safe mode
	ini_set( "max_execution_time", $max_processing_seconds + 10 ); // does not work in safe mode

	if ($DEBUG) informAdmin( "DEBUG: called validatePDF_2( $magic, $callback_URL, $fileID, $file )" );

	// security issue
	$file = basename( $file ); // to avoid s.th. like '../../../etc/passwd'

	// wait for pitstop to process the file
	$file_log = "${file}_log.pdf"; // PitStop names reports like this
	$start_time = time();
	while (file_exists( cleanPath("$hotdir/$INP/$file") ) && ($start_time + $max_processing_seconds > time())) {
		clearstatcache();
		sleep( 5 );
	}

	// MISCONCEPTION - PitStop removes the input file before all other files have been written to disk. Especially the Report takes longer.
	// wait for report to appear
	$report_present = false;
	while ((!$report_present) && ($start_time + $max_processing_seconds > time())) {
		clearstatcache();
		$report_present = file_exists( cleanPath("$hotdir/$REP_ERR/$file_log") ) || file_exists( cleanPath("$hotdir/$REP_OK/$file_log") ) || file_exists( cleanPath("$hotdir/$NPDF_ERR/$file_log") );
		sleep( 5 );
	}

	if ($start_time + $max_processing_seconds <= time()) {
		// cleanup - not all files will be present, thus neglect warnings
		cleanup( $file );
		informAdmin( "validation tooks too long. PitStop server not running? HotDir is '$hotdir' and input folder is named '$INP'" );
		return false; // validation tooks too long
	}

	sleep( 5 ); // let PitStop write all files
	clearstatcache();

	$valid = 2; // validation was aborted
	// PitStop has removed the file, now check the result
	if (file_exists( cleanPath("$hotdir/$NPDF/$file") )) {
		$reportFileName = cleanPath("$hotdir/$NPDF_ERR/$file_log");
		$valid = false;
	}
	if (file_exists( cleanPath("$hotdir/$ORIG_ERR/$file") )) {
		$reportFileName = cleanPath("$hotdir/$REP_ERR/$file_log");
		$valid = false;
	}
	if (file_exists( cleanPath("$hotdir/$ORIG_OK/$file") )) {
		$reportFileName = cleanPath("$hotdir/$REP_OK/$file_log");
		$valid = true;
	}

	if ($valid === 2) {
		// cleanup - not all files will be present, thus neglect warnings
		cleanup( $file );
		informAdmin( "validation was aborted. Input file was removed, but result is not present." );
		return false;
	}

	if ($DEBUG) {
		if ($valid) $temp = "pdf file is valid"; else $temp = "file is not valid";
		informAdmin( "DEBUG: validation process completed.\n$temp" );
	}

	// PitStop may produce reports with 350 pages and more. These reports can be as large as 8 MiB.
	// If pdftk (http://www.pdftk.com) is available, truncate the report; otherwise do not send, if too large
	$data = truncate_pdf( $reportFileName );

	// call the callback function to announce results of validation
	$server = parse_url( $callback_URL );
	$server['port'] = $server['port'] ? $server['port'] : 80;

	if ($xmlrpc['pear']) {
		// use XML_RPC impl. to contact the callback function, which in turn will commit the result to the database
		$client = new XML_RPC_Client( $server['path'], $server['host'], $server['port'] );
		$params = array( new XML_RPC_Value($data,'base64'),
		                 new XML_RPC_Value($valid,'boolean'),
		                 new XML_RPC_Value($magic,'int'),
		                 new XML_RPC_Value($fileID,'int') );
		$xmlrpc_message = new XML_RPC_Message( 'validatePDF_callback', $params );
		if ($DEBUG) {
			$client->setDebug(1);
			ob_start();
		}
		$client->send( $xmlrpc_message );
		if ($DEBUG) {
			informAdmin( "server reports:\n" . ob_get_contents() );
			ob_end_clean();
		}
	} elseif ($xmlrpc['internal']) {
		// use internal impl. to contact the callback function, which in turn will commit the result to the database
		xmlrpc_set_type( $data, "base64" ); // converts the string to an object with encoding 'base64'
		$params = array( $data, $valid, $magic, $fileID );
		$output = array( 'output_type' => 'php' );
		xu_rpc_http_concise(array(method => 'validatePDF_callback',
		                          args   => $params,
		                          host   => $server['host'],
		                          uri    => $server['path'],
		                          port   => $server['port'],
		                          debug  => 0,
		                          output => $output));
	}

	if ($DEBUG) informAdmin( "DEBUG: callback function validatePDF_callback() at $callback_URL was called." );

	// cleanup - not all files will be present, thus neglect warnings
	cleanup( $file );
}

function truncate_pdf( $file )
{
    // soft dependency on pdftk (http://www.pdftk.com)

    $maxsize = 200000; // bytes

    if (filesize( $file ) > $maxsize) {
        $newfile = $file . "_2.pdf";
        @exec( "pdftk " . escapeshellarg( $file ) . " cat 1-10 output " . escapeshellarg( $newfile ) . " dont_ask" );
        if (file_exists( $newfile ) && (filesize( $newfile ) <= $maxsize)) {
            $data = fread( fopen( $newfile, "r" ), filesize( $newfile ) );
            unlink( $newfile );
        } elseif (file_exists( 'reportTooLarge.pdf' ))
            $data = fread( fopen( 'reportTooLarge.pdf', "r" ), filesize( 'reportTooLarge.pdf' ) );
	else
            $data = '';
    } else
        $data = fread( fopen( $file, "r" ), filesize( $file ) );

    return $data;
}

function cleanup( $file )
{
	global $hotdir, $INP, $NPDF, $ORIG_ERR, $ORIG_OK, $PROC_ERR, $PROC_OK, $NPDF_ERR, $REP_ERR, $REP_OK;

	$file_log = "${file}_log.pdf"; // PitStop names reports like this

	@unlink( cleanPath( "$hotdir/$INP/$file" ) );
	@unlink( cleanPath( "$hotdir/$NPDF/$file" ) );
	@unlink( cleanPath( "$hotdir/$ORIG_ERR/$file" ) );
	@unlink( cleanPath( "$hotdir/$ORIG_OK/$file" ) );
	@unlink( cleanPath( "$hotdir/$PROC_ERR/$file" ) );
	@unlink( cleanPath( "$hotdir/$PROC_OK/$file" ) );
	@unlink( cleanPath( "$hotdir/$NPDF_ERR/$file_log" ) );
	@unlink( cleanPath( "$hotdir/$REP_ERR/$file_log" ) );
	@unlink( cleanPath( "$hotdir/$REP_OK/$file_log" ) );
}

function cleanPath($path) {
// function from bart at mediawave dot nl 21-Sep-2005 08:31 http://de2.php.net/manual/en/function.realpath.php
    $result = array();
    // $pathA = preg_split('/[\/\\\]/', $path);
    $pathA = explode('/', $path);
    if (!$pathA[0])
        $result[] = '';
    foreach ($pathA AS $key => $dir) {
        if ($dir == '..') {
            if (end($result) == '..') {
                $result[] = '..';
            } elseif (!array_pop($result)) {
                $result[] = '..';
            }
        } elseif ($dir && $dir != '.') {
            $result[] = $dir;
        }
    }
    if (!end($pathA)) 
        $result[] = '';
    return implode('/', $result);
}




// http://de.php.net/manual/en/ref.errorfunc.php#ini.error-log
// user defined error handling function
function userErrorHandler($errno, $errmsg, $filename, $linenum, $vars)
{
	$errortype = array (
		E_ERROR              => 'Error',
		E_WARNING            => 'Warning',
		E_PARSE              => 'Parsing Error',
		E_NOTICE             => 'Notice',
		E_CORE_ERROR         => 'Core Error',
		E_CORE_WARNING       => 'Core Warning',
		E_COMPILE_ERROR      => 'Compile Error',
		E_COMPILE_WARNING    => 'Compile Warning',
		E_USER_ERROR         => 'User Error',
		E_USER_WARNING       => 'User Warning',
		E_USER_NOTICE        => 'User Notice',
		E_STRICT             => 'Runtime Notice',
		E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
		);
	$user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);

	$err = "userErrorHandler() called.\n\n";
	$err .= "<errorentry>\n";
	$err .= "\t<datetime>" . date("Y-m-d H:i:s (T)") . "</datetime>\n";
	$err .= "\t<errornum>" . $errno . "</errornum>\n";
	$err .= "\t<errortype>" . $errortype[$errno] . "</errortype>\n";
	$err .= "\t<errormsg>" . $errmsg . "</errormsg>\n";
	$err .= "\t<scriptname>" . $filename . "</scriptname>\n";
	$err .= "\t<scriptlinenum>" . $linenum . "</scriptlinenum>\n";

	if (in_array($errno, $user_errors)) {
		$err .= "\t<vartrace>" . wddx_serialize_value($vars, "Variables") . "</vartrace>\n";
	}
	$err .= "</errorentry>\n\n";

	if (($errno != E_WARNING) && ($errno != E_NOTICE) && ($errno != E_STRICT)) {
		informAdmin( $err );
	}
}

?>

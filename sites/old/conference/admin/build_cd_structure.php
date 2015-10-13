<?php 
	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");	
	session_start();
	
    // Note: Windows needs ; path separator and unix needs :
	//$new_include_path = ini_get('include_path').":$php_root_path/includes/pear";
    
	ini_set('include_path', "$php_root_path/includes/pear");
    
    require_once($php_root_path."/includes/pear/HTML/Progress.php");
	
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;
	
	
    $db = adodb_connect();
  
	//Retrieve the setting information
	$settingInfo = get_Conference_Settings();
	$defaultZipSize= $settingInfo->MaxZipFileSize; //Default Maxzipfilesize
	
	require_once($php_root_path."/includes/pear/Tar.php");
	require_once($php_root_path."/admin/includes/libzipfile.php");
	do_html_header("Build CD Structure");	

?>


<form name="form1" method="post" action="process_build_cd_structure.php">

<div style="padding-top: 3mm">
Select the type of archive should be exported:
</div>

<div>
<table cellpadding="1">
	<tr>
		<td><input type="radio" name="enctype" value="zip" checked /></td>
		<td>zip</td>
		<td>(*.zip)</td>
	</tr>
	<tr>
		<td><input type="radio" name="enctype" value="bz2" /></td>
		<td>bzip2</td>
		<td>(*.tar.bz2)</td>
	</tr>
	<tr>
		<td><input type="radio" name="enctype" value="gz" /></td>
		<td>gzip</td>
		<td>(*.tar.gz)</td>
	</tr>

</table>
</div>

<div style="padding-top: 3mm">
Base filename: <input name="filename"><br />
(conference name will be used if none given)
</div>

<div style="padding-top: 3mm">
<strong>Maximum size of archive file(s):</strong> (If extraction is unsuccessful, reduce this value)
<br><br>
<?php
 
    $defaultString="";
    $extractValArr=array(52428800=>"50MB",41943040=>"40MB",31457280=>"30MB",20971520=>"20MB",
    15728640=>"15MB",10485760=>"10MB",5242880=>"5MB");
  
    echo "<select name=\"extractSize\">";
    
    while(list($value,$text)=each($extractValArr)){
	    echo "<option value=\"".$value."\"";
	    if ($value==$defaultZipSize){
		    echo "Selected";
		    }
	    
	   echo ">".$text." "."</option>";
	   $defaultString="";
    }
  
    echo "</select>";
    ?>
</div>

<div style="padding-top: 3mm">
<input type="submit" name="Submit" value="Build Archive">
</div>

</form>
<?php do_html_footer(); ?>
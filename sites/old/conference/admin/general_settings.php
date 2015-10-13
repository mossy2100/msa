<?php

	$php_root_path = ".." ;
	$privilege_root_path = "/admin" ;
	require_once("includes/include_all_fns.inc");
    require_once("$php_root_path/includes/page_includes/page_fns.php");
	session_start();
	// extract ( $_SESSION , EXTR_REFS ) ;
	$err_message = " Unable to process your request due to the following problems: <br>\n" ;

	//Check whether the form is submitting of click cancel
	if($_POST["Submit"] == "Cancel"){
		//User click Cancel, go back to admin home page
		header("Location: admin_home.php");
		exit;
	}

	// If the user clicked the Submit button then do an update
	if($_POST["Submit"] == "Submit"){


		foreach ($_FILES as $key => $value)
		{
			if ($value['size'] > 0)
			{
				$_POST[$key] = file_get_contents($value["tmp_name"]);
				if (get_magic_quotes_gpc())
					$_POST[$key] = addslashes($_POST[$key]);
			}
		}

		if(!isset($_POST["AbstractOnlySubmissions"])) //Set default value for checkbox, if not checked
        $_POST["AbstractOnlySubmissions"] = 0;

        if(!isset($_POST["DoubleBlindReview"])) //Set default value for checkbox, if not checked
        $_POST["DoubleBlindReview"] = 0;
        
        if(!isset($_POST["UserPhase"])) //Set default value for radiobox, if not checked
        $_POST["UserPhase"] = 0;
        
        if(!isset($_POST["ReviewerPhase"])) //Set default value for radiobox, if not checked
        $_POST["ReviewerPhase"] = 0;

        if(!isset($_POST["ValidatePDFs"])) //Set default value for checkbox, if not checked
        $_POST["ValidatePDFs"] = 0;

        // Update the settings
        $result = updateSettings($_POST);

		if($result === true){

            // Write page
			do_html_header("Successful Update");
			?>
            <p>The settings are successfully updated<br>
			View new settings <a href="/conference/admin/general_settings.php">here</a>.</p>
			<?php
            do_html_footer();
			exit;
		}
		else{
			do_html_header("Error Information");
			echo "<p>$result</p>";
			do_html_footer();
			exit;
		}
	}

	do_html_header("Settings");

	//Establish database connection
  	$db = adodb_connect();

  	if (!$db){
   		echo "Could not connect to database server - please try later.";
		exit;
	}

	//Get the conference information
	$conferenceInfo = get_conference_info();

	//Retrieve the setting information
	$settingInfo = get_Conference_Settings();

	//Get the maximun size of paper
	$maxPaperSize = $settingInfo -> MaxUploadSize;
	$maxPaperSize = $maxPaperSize / pow(2,20);

	$maxLogoSize = $settingInfo -> MaxLogoSize;
	$maxLogoSize = $maxLogoSize / pow(2,20);

	//Get Hex Value of the Background Colour
	$BackgroundColor = $settingInfo -> BackgroundColor;
	$FontColor = $settingInfo -> FontColor;
	$CSSFile = $settingInfo -> CSSFile;
?>
<script language="JavaScript" src="/conference/admin/script/dynlib.js"></script>
<script language="JavaScript" src="/conference/admin/script/bgcolorchanger.js"></script>
<script language="JavaScript" src="/conference/admin/script/CssScript.js"></script>
<script language="JavaScript" src="/conference/admin/script/setVisibility.js"></script>
<form name="form1" method="post" action="general_settings.php" enctype="multipart/form-data" >
  <table width="100%" border="0" cellspacing="0" cellpadding="2">
    <tr>
      <td colspan="2"><strong>General Settings</strong></td>
    </tr>
    <tr>
      <td width="40%" valign="top">Website Homepage: <br>
      </td>
      <td width="60%" valign="top"><input name="HomePage" type="text" id="HomePage" size="60" value="<?php echo htmlentities(stripslashes($settingInfo -> HomePage)); ?>"></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td colspan="2"><strong>Upload Settings</strong></td>
    </tr>
    <tr>
      <td>Max Upload Paper File Size<br>
        <i>For uploaded papers</i></td>
      <td valign="top"><input name="MaxUploadSize" type="text" id="MaxUploadSize" size="6" maxlength="3" value="<?php echo $maxPaperSize; ?>"> MB
      <?php
	    if($maxPaperSize>get_cfg_var('upload_max_filesize'))
	       	echo "<br/><font color='#FF0000'>Warning : The value 'upload_max_filesize' in php.ini is set to '" . get_cfg_var('upload_max_filesize') ."'</font>";
	    if($maxPaperSize>get_cfg_var('post_max_size'))
	       	echo "<br/><font color='#FF0000'>Warning : The value 'post_max_size' in php.ini is set to '" . get_cfg_var('post_max_size') ."'</font>";
        ?>
      </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td valign="top">&nbsp;</td>
    </tr>
    <tr>
      <td>Maximum Logo File Size<br>
        <i>For uploaded logo file</i></td>
      <td valign="top"><input name="MaxLogoSize" type="text" id="MaxLogoSize" size="6" maxlength="3" value="<?php echo $maxLogoSize; ?>"> MB
        <?php
        if($maxLogoSize>get_cfg_var('upload_max_filesize'))
        	echo "<br/><font color='#FF0000'>Warning : The value 'upload_max_filesize' in php.ini is set to '" . get_cfg_var('upload_max_filesize') ."'</font>";
        if($maxLogoSize>get_cfg_var('post_max_size'))
        	echo "<br/><font color='#FF0000'>Warning : The value 'post_max_size' in php.ini is set to '" . get_cfg_var('post_max_size') ."'</font>";
        ?>
        </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td valign="top">&nbsp;</td>
    </tr>
    <tr>
      <td>Maximum Logo Dimensions <br>
        <i>(Height x Width in pixels) <br>
        Logos larger than these dimensions will be resized.<br> </i></td>
      <td valign="top"><input name="MaxLogoHeight" type="text" id="MaxLogoHeight" size="6" maxlength="3" value="<?php echo $settingInfo -> MaxLogoHeight; ?>">
        x
        <input name="MaxLogoWidth" type="text" id="MaxLogoWidth" size="6" maxlength="3" value="<?php echo $settingInfo -> MaxLogoWidth; ?>"></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td colspan="2"><strong>Email Settings</strong></td>
    </tr>
    <!--<tr>
      <td valign="top">Conference contact email:<br>
        This email is the same as the Conference contact. You may also edit this email address
        at Conference Info.</td>
      <td valign="top"><input name="ConferenceContact" type="text" id="ConferenceContact" size="40" maxlength="250" value="<?php echo $conferenceInfo -> ConferenceContact; ?>"></td>
    </tr>-->
    <tr>
      <td valign="top">&nbsp;</td>
      <td valign="top">&nbsp;</td>
    </tr>
    <tr>
      <td valign="top">Email Signature: (optional)<br>
        <i>This text will be included at the end of all outgoing email.</i></td>
      <td valign="top"><textarea name="EmailSignature" cols="72" rows="5" id="EmailSignature"><?php echo htmlentities(stripslashes($settingInfo -> EmailSignature)); ?></textarea></td>
    </tr>
    <tr>
      <td valign="top">&nbsp;</td>
      <td valign="top">&nbsp;</td>
    </tr>
        <tr>
      <td colspan="2"><strong>Localization Settings</strong></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>
      Primary Topic Term<br>
      <i>A paper can have only one primary topic.</i>
      </td>
      <td>
      <input name="TrackName" type="text" id="TrackName" value="<?php echo stripslashes($settingInfo->TrackName) ?>" size="20" maxlength="30">
      </td>
    </tr>
    <tr>
      <td>
      Secondary Topics Term<br>
      <i>A paper can have many secondary topics in addition to its primary topic.</i>
      </td>
      <td>
        <input name="TopicName" type="text" id="TopicName" value="<?php echo stripslashes($settingInfo->TopicName) ?>" size="20" maxlength="30">
      </td>
    </tr>
    <!--  Not used
    <tr>
      <td>
      Level Term
      </td>
      <td>
        <input name="LevelName" type="text" id="LevelName" value="<?php echo stripslashes($settingInfo->LevelName) ?>" size="20" maxlength="30">
      </td>
    </tr>
    -->
    <tr>
      <td>Default Country</td>
      <td>
        <?php
            $def_ctry_cbo = GetCountryDropDownBox( $settingInfo->CountryDefault );
            echo str_replace('"country"','"CountryDefault"',$def_ctry_cbo);
        ?>
      </td>
    </tr>
    <tr>
      <td>Short Time Format</td>
      <td>
        <select name="DateFormatShort" class="form_style" >
			<?php
                $selectedSeen = false;
                foreach ($COMMENCE_DATE_FORMAT_TYPES as $date_format) { ?>
                <option value="<?php echo $date_format ?>" <?php
                    if (!$selectedSeen && $date_format==stripslashes($settingInfo->DateFormatShort))
                    {
                        $selectedSeen = true;
                        echo "selected";
                    }
                ?> > <?php echo date($date_format,0) ?> </option>
            <?php } ?>
		</select>
      </td>
    </tr>
    <tr>
      <td>Long Time Format</td>
      <td>
        <select name="DateFormatLong" class="form_style" >
			<?php
                $selectedSeen = false;
                foreach ($COMMENCE_DATE_FORMAT_TYPES as $date_format) { ?>
                <option value="<?php echo $date_format ?>" <?php
                    if (!$selectedSeen && $date_format==stripslashes($settingInfo->DateFormatLong))
                    {
                        $selectedSeen = true;
                        echo "selected";
                    }
                ?> > <?php echo date($date_format,0) ?> </option>
            <?php } ?>
		</select>
      </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    </tr>
    <tr>
      <td colspan="2"><strong>Conference-Specific Settings</strong></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
	<tr>
      <td>
      Abstract-only Submissions<br>
      <i>If checked, COMMENCE will allow paper submissions that only contain an abstract.</i>
      </td>
      <td>
      <input name="AbstractOnlySubmissions" type="checkbox" value="1"  <?php if ($settingInfo->AbstractOnlySubmissions) echo 'checked'; ?>>
      </td>
    </tr>
    <tr>
    <td>
    Double Blind Reviewing<br>
    <i>If checked, author's names will be suppressed in Reviewer interface</i>
    </td>
    <td>
	<input name="DoubleBlindReview" type="checkbox" value="1"  <?php if ($settingInfo->DoubleBlindReview) echo 'checked'; ?>>
	</td>
    </tr>
    <!--<tr>
      <td>
      SESUG-style Conference<br>
      <i>COMMENCE behaviour modifications intended for SESUG</i>
      </td>
      <td>
      <input name="SESUG" type="checkbox" id="SESUG" value="<?php echo $settingInfo->SESUG ?>">
      </td>
    </tr>-->
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
	</tr>
    <!--<tr>
      <td colspan="2"><strong>Registration Settings</strong></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
	<tr>
      	<td valign="top">
		Show Conference Registration Features<br/>
        <i>
		Conference registration features can be hidden from end-users
		by selecting "No" here. These features should only be enabled
		after an XML registration form template has been uploaded.
		</i>
		</td>
		<td valign="top">
		<input name="RegistrationEnabled" type="radio" value="1"
		<?php echo ($settingInfo->RegistrationEnabled == 1)?'checked':''?>
		>Yes
		<input name="RegistrationEnabled" type="radio" value="0"
		<?php echo ($settingInfo->RegistrationEnabled == 0)?'checked':''?>
		>No
		</td>
    </tr>
	<tr>
		<td valign="top">
		Registration Preamble<br/>
        <i>
		This text will be displayed at the top of the conference registration
		form.
		It should include any information that the registrant is likely to need
		that is not on the form itself.
		</i>
		</td>
		<td valign="top">
		<textarea name="RegPreamble" cols="72" rows="5" id="RegPreamble"><?php echo htmlentities(stripslashes($settingInfo -> RegPreamble)); ?></textarea>
		</td>
    </tr>
	<tr>
      	<td valign="top">
		Currency Unit<br/>
        <i>
		This is the currency unit that will displayed next to all prices on the
		form.
		</i>
		</td>
		<td valign="top">
		<input name="CurrencySymbol" type="text" value="<?php echo stripslashes($settingInfo->CurrencySymbol)?>" />
		<input name="CurrencyPosition" type="radio" value="before"
		<?php echo ($settingInfo->CurrencyPosition == 'before')?'checked':''?>
		>Before
		<input name="CurrencyPosition" type="radio" value="after"
		<?php echo ($settingInfo->CurrencyPosition == 'after')?'checked':''?>
		>After
		</td>
    </tr>
	<tr>
      <td>
      Registration Form XML
	  <br />
      <i>
	  The XML definition for the registration form.
	  </i>
      </td>
      <td>
      <input type="file" name="RegFormXml">
      </td>
    </tr>
    <tr>
		<td valign="top">
		Registration Final Instructions<br/>
        <i>
		This text will be displayed after the user has completed a conference
		registration form, telling them what to do to complete the registration.
		</i>
		</td>
		<td valign="top">
		<textarea name="RegFinalInstruct" cols="72" rows="5" id="RegFinalInstruct"><?php echo htmlentities(stripslashes($settingInfo -> RegFinalInstruct)); ?></textarea>
		</td>
    </tr>-->
	<tr>
      <td colspan="2"><strong>Colour and Style</strong></td>
    </tr>
	<tr>
		<td>
			<input type="radio" value="diy" id="diy" name="diyorcss"
			<?php
				if($CSSFile == "CommentStyleDefault.css")
					echo "checked";

			?> onClick="makeDisable('fixedcss');makeEnable('owncustomize1');makeEnable('owncustomize2');loadStyleSheet(document, 'CommentStyleDefault.css');resumeDefaultColor();setListToDefaultColor();">
        Choose Your Own Colours</td>
		<tr>
			<tr id="owncustomize1"
			<?php
				if($CSSFile != "CommentStyleDefault.css")
					echo " style='display: none;'";
				else
					echo " style='';"
			?>>
				<td valign="top">Customize your own background colour.</td>
     			<td valign="top"><input name="BackgroundColor" type="text" id="BackgroundColor" size="8" maxlength="7" value="<?php echo $BackgroundColor; ?>" onClick="javascript:openBgColorChanger('bgcolor');">
	 			<input type="button" value="Colour Picker" onClick="javascript:openBgColorChanger('bgcolor');">
      </td>
			</tr>
			<tr id="owncustomize2"
			<?php
				if($CSSFile != "CommentStyleDefault.css")
					echo " style='display: none;'";
				else
					echo " style='';"
			?>>
     			<td valign="top"> Customize your own foreground colour.</td>
      			<td valign="top"><input name="FontColor" type="text" id="FontColor" size="8" maxlength="7" value="<?php echo $FontColor; ?>" onClick="javascript:openBgColorChanger('fontcolor');">
	  			<input type="button" value="Colour Picker" onClick="javascript:openBgColorChanger('fontcolor');">
      </td>
   			</tr>
		</tr>
	</tr>

	<tr>

      <td><input type="radio" value="fixcss" id="fixcss" name="diyorcss"
		<?php
				if($CSSFile != "CommentStyleDefault.css")
					echo "checked";
		?> onClick="makeDisable('owncustomize1');makeDisable('owncustomize2');makeEnable('fixedcss');loadStyleSheet(document, '<?php echo $CSSFile?>');">
        Apply Style Sheet</td>
		<tr id="fixedcss"
		<?php
			if($CSSFile == "CommentStyleDefault.css")
					echo " style='display: none;'";
				else
					echo " style='';"
		?>>
    	  <td valign="top"> Apply CSS</td>
      		<td valign="top">
        	<select name="CSSFile" id="CSSFile" onChange="loadStyleSheet(document, document.getElementById('CSSFile').value);">
          	<?php
		 		if ($handle = opendir('../stylesheets')) {
  	 				while (false !== ($file = readdir($handle))) {
   	    			if ($file != "." && $file != "..") {
						$file_len = strlen($needle);
						$file_name = substr($file, $file_len-4, 4);
						if($file_name == ".css")  {
							if($file == $CSSFile)
								echo "<option value ='$file' id ='$file' selected>$file</option>";
							else
								echo "<option value ='$file' id ='$file'>$file</option>";
							}
           				}
   					}
   					closedir($handle);
				}
		  	?>
      		</select>
      </td>
    	</tr>
	</tr>
    <tr>
      <td>&nbsp;</td><td>&nbsp;</td>
    </tr>
    <tr>
      <td colspan="2"><strong>User Phase </strong></td>
    </tr>
    
    <tr>
      <td> This option allows for manual control of the user phase. <br>
            Default is the same as the administrator phase.  
      </td>
      <td>
      Default<input type="radio" name="UserPhase" value="0"  <?php if ($settingInfo->UserPhase == 0) echo 'checked="checked"'; ?>> &nbsp;
      Submission<input type="radio" name="UserPhase" value="1"  <?php if ($settingInfo->UserPhase == 1) echo 'checked="checked"'; ?>> &nbsp;
      Bidding<input type="radio" name="UserPhase" value="2"  <?php if ($settingInfo->UserPhase == 2) echo 'checked="checked"'; ?>> &nbsp;
      Reviewing<input type="radio" name="UserPhase" value="3"  <?php if ($settingInfo->UserPhase == 3) echo 'checked="checked"'; ?>> &nbsp;
      Final Submission<input type="radio" name="UserPhase" value="4"  <?php if ($settingInfo->UserPhase == 4) echo 'checked="checked"'; ?>>      
      </td>
    </tr>
    <tr>
      <td>&nbsp;</td><td>&nbsp;</td>
    </tr>
    <tr>
      <td colspan="2"><strong>Reviewer Phase </strong></td>
    </tr>
    
    <tr>
      <td> This option allows for manual control of the reviewer phase. <br>
            Default is the same as the administrator phase.  
      </td>
      <td>
      Default<input type="radio" name="ReviewerPhase" value="0"  <?php if ($settingInfo->ReviewerPhase == 0) echo 'checked="checked"'; ?>> &nbsp;
      Submission<input type="radio" name="ReviewerPhase" value="1"  <?php if ($settingInfo->ReviewerPhase == 1) echo 'checked="checked"'; ?>> &nbsp;
      Bidding<input type="radio" name="ReviewerPhase" value="2"  <?php if ($settingInfo->ReviewerPhase == 2) echo 'checked="checked"'; ?>> &nbsp;
      Reviewing<input type="radio" name="ReviewerPhase" value="3"  <?php if ($settingInfo->ReviewerPhase == 3) echo 'checked="checked"'; ?>> &nbsp;
      Final Submission<input type="radio" name="ReviewerPhase" value="4"  <?php if ($settingInfo->ReviewerPhase == 4) echo 'checked="checked"'; ?>>      
      </td>
    </tr>
    <tr>
      <td>&nbsp;</td><td>&nbsp;</td>
    </tr>
    <tr>
      <td colspan="2"><strong>PDF Validation Settings</strong></td>
    </tr>
    <tr>
      <td>&nbsp;</td><td>&nbsp;</td>
    </tr>
	<tr>
      <td>Enable validating of submitted PDF-files<br>
          <i>If checked, COMMENCE will use an external program (e.g. PitStop) to validate pdf-files.</i>
      </td>
      <td><input name="ValidatePDFs" type="checkbox" value="1" onchange="setVisibility('ValidatePDFs',this.checked)" <?php if ($settingInfo->ValidatePDFs) echo 'checked="checked"'; ?>></td>
    </tr>
	<tr><TD colspan="2">
		<div id="ValidatePDFs" style="display:<?php if ($settingInfo->ValidatePDFs) echo 'block'; else echo 'none'; ?>">
			<table width="100%">
				<TR><TD width="40%">Select the method used to validate the PDF</TD>
					<TD width="60%"><select name="ValidatePDFs_method">
					<option <?php if (stripslashes($settingInfo->ValidatePDFs_method) == 'local script') echo 'selected="selected"' ?>>local script</option>
					<option <?php if (stripslashes($settingInfo->ValidatePDFs_method) == 'remote via XML-RPC') echo 'selected="selected"' ?>>remote via XML-RPC</option>
					<option <?php if (stripslashes($settingInfo->ValidatePDFs_method) == 'remote via SOAP') echo 'selected="selected"' ?>>remote via SOAP</option>
					</select></TD>
				</TR>
				<TR><TD>Path to the local script or executable<br><i>Only used if method is "local script"</i></TD><TD><input type="text" size="30" maxlength="100" name="ValidatePDFs_path" value="<?php echo stripslashes($settingInfo->ValidatePDFs_path) ?>"></TD></TR>
				<TR><TD>Server URL (eg. http://example.com/validate.php)<br><i>Only used if method is not "local script"</i></TD><TD><input type="text" size="30" maxlength="100" name="ValidatePDFs_URL" value="<?php echo stripslashes($settingInfo->ValidatePDFs_URL) ?>"></TD></TR>
			</table>
		</div></TD>
	</tr>
   <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td valign="top">
      <input type="submit" name="Submit" value="Submit">
      <input name="Submit" type="submit" id="Submit" value="Cancel">
      </td>
      <td>&nbsp;</td>
    </tr>
  </table>
</form>
<?php do_html_footer(& $err_message);?>

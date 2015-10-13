<?php
$php_root_path = ".." ;
$privilege_root_path = "/user" ;
require_once($php_root_path . "/includes/include_all_fns.inc");
require_once($php_root_path . "/includes/page_includes/page_fns.php");

if (isset($_GET['login']) && !$_GET['login']) {
	$homepage->showmenu = 0 ;
} else {
	session_start();
}

if (!$db = adodb_connect()) {
	do_html_header("Attendee Registration Form &#151; Error");
	do_html_footer("Error connecting to database.");
	exit;
}

require_once($php_root_path . "/includes/rego_form_functions.php");

// retrieve conference settings
$settings = get_Conference_Settings();
do_html_header((isset($settings->AttendeeRegoTitle) && ($settings->AttendeeRegoTitle) ? $settings->AttendeeRegoTitle : "Attendee Registration Form"));
	
	// check $_POST to see if form has been submitted (for validation)
	if (count($_POST) > 0) {
		switch ($_POST['submit']) {
			case "Confirm": // form has been confirmed, save details to database
				$rsSave = $db->Execute("SELECT GID, FID, CONCAT('g', GID, 'f', FID) AS GFID, Type, ValFunction, DATE_FORMAT(UID, '%Y%m%d%H%i%s') AS UID " .
				                       "FROM " . TBL_FIELDS . " " .
				                       "WHERE Type > 1");
				// create arrays of UID and Value to store in database (both indexed by GFID)
				$uid = array();
				$value = array();
				while ($save = $rsSave->FetchRow()) {
					// quote UIDs with backticks because they start with a number
					$uid[$save['GFID']] = "`" . $save['UID'] . "`";
					// format value ready for SQL depending on its type
					switch ($save['Type']) {
						case 2: // text field
							if ($save['ValFunction'] == 31) // credit card
								$_POST[$save['GFID']] = ccDecrypt($_POST[$save['GFID']]);
						case 3: // text area
							$value[$save['GFID']] = "'" . (isset($_POST[$save['GFID']]) ? $_POST[$save['GFID']] : "") . "'";
							break;
						case 5: // checkbox group
						case 9: // selectable list
							$value[$save['GFID']] = "'" . (isset($_POST[$save['GFID']]) ? implode(" ", $_POST[$save['GFID']]) : "") . "'";
							break;
						case 4: // checkbox
						case 6: // radio button
						case 7: // radio button group
						case 8: // drop-down menu
							$value[$save['GFID']] = (isset($_POST[$save['GFID']]) ? $_POST[$save['GFID']] : 0);
							break;
					}
				
				}
				// generate SQL for insert
				$db->Execute("INSERT INTO " . TBL_DETAILS . " " .
				             "(PriceTotal, " . implode(", ", $uid) . ") " .
				             "VALUES (" . $_POST['totalprice'] . ", " . implode(", ", $value) . ")");
				// get the registration ID (auto-incremented by database)
				// the number is a 4 digit zerofilled integer so we prepend 2 constant non-zero digits
				$regoID = "25" . ($autoID = str_pad($db->Insert_ID(), 4, "0",  STR_PAD_LEFT));
				// generate check digit
				$regoID .= rfGenCheckDigit($regoID);
				// add RegoID to database record
				$db->Execute("UPDATE " . TBL_DETAILS . " " .
				             "SET RegoID = '" . $regoID . "' " .
				             "WHERE AutoID = " . $autoID);
				// retrieve registration header information from database
				$rsHeader = $db->Execute("SELECT RegoID, DATE_FORMAT(RegoTime, '%a, %d %b %Y, %T') AS RegoTime, PriceTotal " .
				                         "FROM " . TBL_DETAILS . " " .
				                         "WHERE RegoID = $regoID");
				$header = $rsHeader->FetchRow();
				$finished = true;
				// don't break - run through checks for initial state so completed form can be displayed (as in the confirm page)
			case "Submit": // form was submitted, validate
			case "Edit": // form is to be edited, after confrim screen
				$rsValidate = $db->Execute("SELECT GID, FID, CONCAT('g', GID, 'f', FID) AS GFID, Type, Required, ValFunction, ValueList " .
				                           "FROM " . TBL_FIELDS . " " .
				                           "WHERE Required = 1 " .
				                           "OR ValFunction <> ''");
				// select all visibility conditions (to find out which fields are disabled, and hence not required)
				$rsVisCond = $db->Execute("SELECT GID, 0 AS FID, CONCAT('g', GID, 'f0') AS GFID, VisCond " .
				                          "FROM " . TBL_GROUPS . " " .
				                          "WHERE VisCond <> '' " .
				                          "UNION " .
				                          "SELECT GID, FID, CONCAT('g', GID, 'f', FID) AS GFID, VisCond " .
				                          "FROM " . TBL_FIELDS . " " .
				                          "WHERE VisCond <> ''");
				// groups/fields that are disabled are not required so they can be ignored
				$ignoreGroups = array();
				$ignoreFields = array();
				// a list of error messages and their associated field to be displayed to user
				$errors = array();
				// some initial states may vary from database based on submitted data
				$errorInitState = array();
				while ($visCond = $rsVisCond->FetchRow()) {
					if (!$vc = vcParse($visCond['VisCond']))
						continue; // go to next iteration if invalid visibility condition
					if ($visCond['FID'] == 0) { // group
						if (!$compare = (vcCompareValue((isset($_POST[$vc['gfid']]) ? $_POST[$vc['gfid']] : 0), $vc['value']) xor $vc['negate']))
							$ignoreGroups[] = $visCond['GID'];
						$errorInitState[$visCond['GFID']] = ($compare ? 2 : ($vc['action'] == "visible" ? 0 : 1));
					} else { // field
						if (!$compare = (vcCompareValue((isset($_POST[$vc['gfid']]) ? $_POST[$vc['gfid']] : 0), $vc['value']) xor $vc['negate']))
							$ignoreFields[] = $visCond['GFID'];
						$errorInitState[$visCond['GFID']] = ($compare ? 2 : ($vc['action'] == "visible" ? 0 : 1));
					}
				}
				while (!$finished && ($validate = $rsValidate->FetchRow())) {
					// if group or field is in the ignored list, skip to next iteration
					if (in_array($validate['GID'], $ignoreGroups) || in_array($validate['GFID'], $ignoreFields))
						continue;
					switch ($validate['Type']) {
						case 2: // text field
						case 3: // text area
							$valReqd = (!$validate['Required'] || $_POST[$validate['GFID']] > "");
							break;
						case 4: // checkbox
						case 6: // radio button
							$valReqd = (!$validate['Required'] || $_POST[$validate['GFID']]);
							break;
						case 5: // checkbox group
						case 9: // selectable list
							$valReqd = (!$validate['Required'] || sizeof($_POST[$validate['GFID']]) > 1);
							break;
						case 7: // radio button group
						case 8: // drop-down menu
							$valReqd = (!$validate['Required'] || $_POST[$validate['GFID']] > 0);
							break;
					}
					if (!$valReqd)
						$message = "This field is required.";
					elseif ($validate['Type'] == 2 && $_POST[$validate['GFID']] > "" && $validate['ValFunction'] > 0) { // check for validation function
						if ($validate['ValFunction'] == 31) { // credit card number
							if ($_POST['submit'] == "Edit")
								$_POST[$validate['GFID']] = ccDecrypt($_POST[$validate['GFID']]);
							$valFunc = vfValidate($validate['ValFunction'], $_POST[$validate['GFID']], $message, $db,
							                      preg_replace("/[\r\n]+/", "|", $validate['ValueList']));
						} else // other validation function
							$valFunc = vfValidate($validate['ValFunction'], $_POST[$validate['GFID']], $message, $db);
					} else
						$valFunc = true;
					if (!$valReqd || !$valFunc)
						$errors[$validate['GFID']] = $message;
				}
				if (!$finished) {
					if (($numErrors = count($errors) > 0) || $_POST['submit'] == "Edit")
						$edit = true;
					else
						$confirm = true;
				}
				break;
			case "Clear": // start with fresh form again
			default:
				empty($_POST);
				break;
		}
	}
	// queries to select groups and fields
	// common part of sql statements
	$rsGroups = $db->Execute("SELECT GID, CONCAT('g', GID, 'f0') AS GFID, Title, Box, VisCond, InitState, CaptWidth, DATE_FORMAT(UID, '%Y%m%d%H%i%s') AS UID " .
	                         "FROM " . TBL_GROUPS . " " .
	                         "ORDER BY GID");
	$prepFields = $db->Prepare("SELECT GID, FID, CONCAT('g', GID, 'f', FID) AS GFID, CONCAT(GID, ':', FID) AS GFID2, Type, Caption, DefValue, Required, " .
	                           "VisCond, InitState, ValueList, PriceList, CharLen, ValFunction, Width, Height, DATE_FORMAT(UID, '%Y%m%d%H%i%s') AS UID " .
	                           "FROM " . TBL_FIELDS . " " .
	                           "WHERE GID = ? " .
	                           "ORDER BY GID, FID");
	if (!($confirm && finished)) {
		// select all visibility conditions (to generate global JavaScript)
		$rsVisCond = $db->Execute("SELECT GID, 0 AS FID, CONCAT('g', GID, 'f0') AS GFID, VisCond " .
		                          "FROM " . TBL_GROUPS . " " .
		                          "WHERE VisCond <> '' " .
		                          "UNION " .
		                          "SELECT GID, FID, CONCAT('g', GID, 'f', FID) AS GFID, VisCond " .
		                          "FROM " . TBL_FIELDS . " " .
		                          "WHERE VisCond <> ''");
	}
?>
<?php
	if ($numErrors > 0) {
?>
<style type="text/css">
<!--
	.error {
		color: #FF0000;
	}
-->
</style>
<?php
	}
	if (!($confirm && $finished)) {
?>
<script type="text/javascript" src="<?php echo $php_root_path; ?>/includes/script/rego_form_functions.js"></script>
<script type="text/javascript">
<!--

	// array of fields with attached visibility conditions
	var vcArray = new Array(
<?php
		while ($visCond = $rsVisCond->FetchRow()) {
			if (!$vc = vcParse($visCond['VisCond']))
				continue; // go to next iteration if invalid visibility condition
			if ($rowNum++ > 0) // don't add comma before first element
				echo ",\n";
			echo "		new Array('" . $vc['gfid'] . "', '" . $visCond['GFID'] . "', '" . $vc['action'] . "', " . vcValueJS($vc['value']) . ", " . $vc['negateJS'] . ")";
			// create array of fields that need to be checked for visibility conditions (to add an event to the field)
			$eventVC[] = $vc['gfid2'];
		}
		echo "\n"; // add new line after last item
		// remove duplicates from array
		$eventVC = array_unique($eventVC);
?>
	);
	
//-->
</script>
<?php
	}
	if (!$finished) {
?>
<form name="rego" id="rego" action="<?php echo $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']; ?>" method="post">
<?php
	}
?>
<table width="100%"  border="0" align="center" cellspacing="20" id="outerform">
	<tr>
		<td>
			<table border="0" align="center" cellspacing="10" id="regoform">
<?php
	$totalPrice = 0;
	if ($numErrors > 0) {
?>
				<tr>
					<td class="error" align="center"><strong><?php echo $numErrors; ?> ERROR<?php echo ($numErrors == 1 ? "" : "S"); ?> FOUND</strong><br />
						Please check the fields with a red caption.<br />
						Hover over the red caption or its field for a description of the
						error.</td>
				</tr>
				<tr>
					<td height="20"></td>
				</tr>
<?php
	}
	if ($finished) {
?>
				<tr>
					<td align="center"><strong>REGISTRATION COMPLETE</strong><br />
						Please print a copy of this page for your records.</td>
				</tr>
				<tr>
					<td>
						<table width="100%">
							<tr>
								<td width="150"><strong>Reference Number:</strong></td>
								<td><?php echo $header['RegoID']; ?></td>
							</tr>
							<tr>
								<td width="150"><strong>Registration Date:</strong></td>
								<td><?php echo $header['RegoTime']; ?></td>
							</tr>
							<tr>
								<td width="150"><strong>Total Price:</strong></td>
								<td><?php echo printCurr($header['PriceTotal'], $settings); ?></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td height="20"></td>
				</tr>
<?php
	}
	// iterate groups
	$groupRow = -1;
	while ($group = $rsGroups->FetchRow()) {
		$groupRow++;
		// if errors found, replace InitState from database with one generated from posted data and visibility conditions
		if (($edit || $confirm || $finished) && array_key_exists($group['GFID'], $errorInitState))
			$group['InitState'] = $errorInitState[$group['GFID']];
		// if in confirm mode, hidden or disabled groups aren't even sent to browser
		if (($confirm || $finished) && $group['InitState'] < 2)
			continue;
?>
				<tr id="row_<?php echo $group['GFID']; ?>"<?php if ($group['InitState'] < 2): ?> disabled="disabled"<?php endif; if ($group['InitState'] < 1): ?> style="display: none;"<?php endif; ?>>
					<td valign="top">
<?php
		if ($group['Box']) { // add box (fieldset) if required
?>
						<fieldset>
							<legend><?php echo $group['Title']; // group name ?></legend>
<?php
		}
?>
							<table border="0" cellspacing="2" width="100%" id="group<?php echo $group['GID']; ?>">
<?php
		// execute query to get all fields for current group
		$rsFields = $db->Execute($prepFields, array($group['GID']));
		// iterate fields
		$fieldRow = -1;
		while ($field = $rsFields->FetchRow()) {
			$fieldRow++;
			// if errors found, replace InitState from database with one generated from posted data and visibility conditions
			if (($edit || $confirm || $finished) && array_key_exists($field['GFID'], $errorInitState))
				$field['InitState'] = $errorInitState[$field['GFID']];
			// if in confirm mode, hidden or disabled fields aren't even sent to browser
			if (($confirm || $finished) && $field['InitState'] < 2)
				continue;
			switch ($field['Type']) {
				case 1: // plain text
?>
								<tr id="row_<?php echo $field['GFID']; ?>" valign="top"<?php if ($field['InitState'] < 2): ?> disabled="disabled"<?php endif; if ($field['InitState'] < 1): ?> style="display: none;"<?php endif; ?>>
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?><?php if ($field['Required']): ?> <span style="color: #FF0000;">*</span><?php endif; ?></td>
									<td><span style="width: <?php echo $field['Width']; ?>px"><?php echo nl2br($field['ValueList']); ?></span></td>
								</tr>
<?php
				break;
				case 2: // text field
?>
								<tr id="row_<?php echo $field['GFID']; ?>" valign="top"<?php if ($field['InitState'] < 2): ?> disabled="disabled"<?php endif; if ($field['InitState'] < 1): ?> style="display: none;"<?php endif; if (isset($errors) && array_key_exists($field['GFID'], $errors)): ?> title="Error: <?php echo $errors[$field['GFID']]; ?>" class="error"<?php endif; ?>>
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?><?php if ($field['Required']): ?> <span style="color: #FF0000;">*</span><?php endif; ?></td>
<?php
					if ($confirm || $finished) {
						if ($field['ValFunction'] == 31) { // credit card number
?>
									<td><?php echo ccMaskNumber($_POST[$field['GFID']]); ?> (<?php echo ccCalculateType($_POST[$field['GFID']]); ?>)
<?php
							if (!$finished) {
?>
										<input name="<?php echo $field['GFID']; ?>" type="hidden" id="<?php echo $field['GFID']; ?>" value="<?php echo ccEncrypt($_POST[$field['GFID']]); ?>" <?php if ($field['InitState'] < 2): ?>disabled="disabled" <?php endif; ?>/>
<?php
							}
?>
									</td>
<?php
						} else {
?>
									<td><?php echo $_POST[$field['GFID']]; ?><input name="<?php echo $field['GFID']; ?>" type="hidden" id="<?php echo $field['GFID']; ?>" value="<?php echo $_POST[$field['GFID']]; ?>" <?php if ($field['InitState'] < 2): ?>disabled="disabled" <?php endif; ?>/></td>
<?php
						}
					} else {
?>
									<td><input name="<?php echo $field['GFID']; ?>" type="text" id="<?php echo $field['GFID']; ?>" style="width: <?php echo $field['Width']; ?>px;" value="<?php echo ($edit ? $_POST[$field['GFID']] : $field['DefValue']); ?>" maxlength="<?php echo $field['CharLen']; ?>" <?php if (in_array($field['GFID2'], $eventVC)): ?>onchange="vcEvent('<?php echo $field['GFID']; ?>', <?php echo $field['Type']; ?>);" <?php endif; if ($field['InitState'] < 2): ?>disabled="disabled" <?php endif; ?>/></td>
<?php
					}
?>
								</tr>
<?php
				break;
				case 3: // text area
?>
								<tr id="row_<?php echo $field['GFID']; ?>" valign="top"<?php if ($field['InitState'] < 2): ?> disabled="disabled"<?php endif; if ($field['InitState'] < 1): ?> style="display: none;"<?php endif; if (isset($errors) && array_key_exists($field['GFID'], $errors)): ?> title="Error: <?php echo $errors[$field['GFID']]; ?>" class="error"<?php endif; ?>>
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?><?php if ($field['Required']): ?> <span style="color: #FF0000;">*</span><?php endif; ?></td>
<?php
					if ($confirm || $finished) {
?>
									<td><span style="width: <?php echo $field['Width']; ?>px"><?php echo nl2br($_POST[$field['GFID']]); ?></span>
<?php
						if (!$finished) {
?>
										<input name="<?php echo $field['GFID']; ?>" type="hidden" id="<?php echo $field['GFID']; ?>" value="<?php echo $_POST[$field['GFID']]; ?>" <?php if ($field['InitState'] < 2): ?>disabled="disabled" <?php endif; ?>/>
<?php
						}
?>
									</td>
<?php
					} else {
?>
									<td><textarea name="<?php echo $field['GFID']; ?>" id="<?php echo $field['GFID']; ?>" rows="<?php echo $field['Height']; ?>" style="width: <?php echo $field['Width']; ?>px;"<?php if (in_array($field['GFID2'], $eventVC)): ?> onchange="vcEvent('<?php echo $field['GFID']; ?>', <?php echo $field['Type']; ?>);"<?php endif; if ($field['InitState'] < 2): ?>disabled="disabled" <?php endif; ?>><?php echo ($edit ? $_POST[$field['GFID']] : $field['DefValue']); ?></textarea></td>
<?php
					}
?>
								</tr>
<?php
				break;
				case 4: // check box
?>
								<tr id="row_<?php echo $field['GFID']; ?>" valign="top"<?php if ($field['InitState'] < 2): ?> disabled="disabled"<?php endif; if ($field['InitState'] < 1): ?> style="display: none;"<?php endif; if (isset($errors) && array_key_exists($field['GFID'], $errors)): ?> title="Error: <?php echo $errors[$field['GFID']]; ?>" class="error"<?php endif; ?>>
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?><?php if ($field['Required']): ?> <span style="color: #FF0000;">*</span><?php endif; ?></td>
<?php
					if ($confirm || $finished) {
?>
									<td><img src="/../images/<?php echo ($_POST[$field['GFID']] ?"tick" : "cross"); ?>.gif" alt="" width="11" height="11" hspace="5" />
<?php
						if (!$finished) {
?>
										<input name="<?php echo $field['GFID']; ?>" id="<?php echo $field['GFID']; ?>" type="hidden" value="<?php echo ($_POST[$field['GFID']] ? 1 : 0); ?>" <?php if ($field['InitState'] < 2): ?>disabled="disabled" <?php endif; ?>/>
<?php
						}
?>
									</td>
<?php
					} else {
?>
									<td><input name="<?php echo $field['GFID']; ?>" id="<?php echo $field['GFID']; ?>" type="checkbox" value="1" <?php if ($edit ? $_POST[$field['GFID']] : $field['DefValue']): ?>checked="checked" <?php endif; ?><?php if (in_array($field['GFID2'], $eventVC)): ?>onclick="vcEvent('<?php echo $field['GFID']; ?>', <?php echo $field['Type']; ?>);" <?php endif; if ($field['InitState'] < 2): ?>disabled="disabled" <?php endif; ?>/></td>
<?php
					}
?>
								</tr>
<?php
				break;
				case 5: // check box group
?>
								<tr id="row_<?php echo $field['GFID']; ?>" valign="top"<?php if ($field['InitState'] < 2): ?> disabled="disabled"<?php endif; if ($field['InitState'] < 1): ?> style="display: none;"<?php endif; if (isset($errors) && array_key_exists($field['GFID'], $errors)): ?> title="Error: <?php echo $errors[$field['GFID']]; ?>" class="error"<?php endif; ?>>
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?><?php if ($field['Required']): ?> <span style="color: #FF0000;">*</span><?php endif; ?></td>
									<td>
<?php
					if ($confirm || $finished) {
?>
										<table border="0" width="100%">
<?php
						$values = preg_split("/[\r\n]+/", $field['ValueList']);
						if ($field['PriceList'] > "")
							$prices = preg_split("/[\r\n]+/", $field['PriceList']);
						$checked = $_POST[$field['GFID']];
						$checknum = 0;
						foreach ($values as $value => $caption) {
							if ($checked[$checknum] == $value + 1) {
?>
											<tr>
												<td><?php echo $caption; ?>
<?php
								if (!$finished) {
?>
													<input name="<?php echo $field['GFID'] . "[]"; ?>" id="<?php echo $field['GFID'] . "v" . ($value + 1); ?>" type="hidden" value="<?php echo $value + 1; ?>" <?php if ($field['InitState'] < 2): ?>disabled="disabled" <?php endif; ?>/>
<?php
								}
?>
												</td>
<?php
								if ($field['PriceList'] > "") {
									if (!$finished && ($prices[$value] != "-"))
										$totalPrice += $prices[$value];
?>
												<td align="right" width="75"><?php echo ($prices[$value] == "-") ? "" : printCurr($prices[$value], $settings); ?></td>
<?php
								}
?>
											</tr>
<?php
								$checknum++;
							}
						}
?>
										</table>
<?php
					} else {
?>
										<table border="0" width="100%">
<?php
						$values = preg_split("/[\r\n]+/", $field['ValueList']);
						if ($field['PriceList'] > "")
							$prices = preg_split("/[\r\n]+/", $field['PriceList']);
						if ($edit || $confirm || $finished)
							$checked = $_POST[$field['GFID']];
						else
							$checked = preg_split("/ /", $field['DefValue']);
						$checknum = 0;
						foreach ($values as $value => $caption) {
?>
											<tr>
												<td><input name="<?php echo $field['GFID'] . "[]"; ?>" id="<?php echo $field['GFID'] . "v" . ($value + 1); ?>" type="checkbox" value="<?php echo $value + 1; ?>" <?php if ($checked[$checknum] == $value + 1): ?>checked="checked" <?php $checknum++; endif; ?><?php if (in_array($field['GFID2'], $eventVC)): ?>onclick="vcEvent('<?php echo $field['GFID']; ?>', <?php echo $field['Type']; ?>);" <?php endif; if ($field['InitState'] < 2): ?>disabled="disabled" <?php endif; ?>/><label for="<?php echo $field['GFID'] . "v" . ($value + 1); ?>"><?php echo $caption; ?></label></td>
<?php
							if ($field['PriceList'] > "") {
?>
												<td align="right" width="75"><label for="<?php echo $field['GFID'] . "v" . ($value + 1); ?>"><?php echo ($prices[$value] == "-") ? "" : printCurr($prices[$value], $settings); ?></label></td>
<?php
							}
?>
											</tr>
<?php
						}
?>
										</table>
<?php
					}
?>
									</td>
								</tr>
<?php
				break;
				case 6: // radio button
?>
								<tr id="row_<?php echo $field['GFID']; ?>" valign="top"<?php if ($field['InitState'] < 2): ?> disabled="disabled"<?php endif; if ($field['InitState'] < 1): ?> style="display: none;"<?php endif; if (isset($errors) && array_key_exists($field['GFID'], $errors)): ?> title="Error: <?php echo $errors[$field['GFID']]; ?>" class="error"<?php endif; ?>>
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?><?php if ($field['Required']): ?> <span style="color: #FF0000;">*</span><?php endif; ?></td>
<?php
					if ($confirm || $finished) {
?>
									<td><img src="/../images/<?php echo ($_POST[$field['GFID']] ?"tick" : "cross"); ?>.gif" alt="" width="11" height="11" hspace="5" />
<?php
						if (!$finished) {
?>
										<input name="<?php echo $field['GFID']; ?>" id="<?php echo $field['GFID']; ?>" type="hidden" value="<?php echo ($_POST[$field['GFID']] ? 1 : 0); ?>" <?php if ($field['InitState'] < 2): ?>disabled="disabled" <?php endif; ?>/>
<?php
						}
?>
									</td>
<?php
					} else {
?>
									<td><input name="<?php echo "g" . $group['GID']; ?>" id="<?php echo $field['GFID']; ?>" type="radio" value="<?php echo "f" . $field['FID']; ?>" <?php if ($edit ? $_POST[$field['GFID']] : $field['DefValue']): ?>checked="checked" <?php endif; ?><?php if (in_array($field['GFID2'], $eventVC)): ?>onclick="vcEvent('<?php echo $field['GFID']; ?>', <?php echo $field['Type']; ?>);" <?php endif; if ($field['InitState'] < 2): ?>disabled="disabled" <?php endif; ?>/></td>
<?php
					}
?>
								</tr>
<?php
				break;
				case 7: // radio button group
?>
								<tr id="row_<?php echo $field['GFID']; ?>" valign="top"<?php if ($field['InitState'] < 2): ?> disabled="disabled"<?php endif; if ($field['InitState'] < 1): ?> style="display: none;"<?php endif; if (isset($errors) && array_key_exists($field['GFID'], $errors)): ?> title="Error: <?php echo $errors[$field['GFID']]; ?>" class="error"<?php endif; ?>>
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?><?php if ($field['Required']): ?> <span style="color: #FF0000;">*</span><?php endif; ?></td>
									<td>
<?php
					if ($confirm || $finished) {
?>
										<table border="0" width="100%">
<?php
						$values = preg_split("/[\r\n]+/", $field['ValueList']);
						if ($field['PriceList'] > "")
							$prices = preg_split("/[\r\n]+/", $field['PriceList']);
						foreach ($values as $value => $caption) {
							if ($_POST[$field['GFID']] == $value + 1) {
?>
											<tr>
												<td><?php echo $caption; ?></td>
<?php
								if ($field['PriceList'] > "") {
									if (!$finished && ($prices[$value] != "-"))
										$totalPrice += $prices[$value];
?>
												<td align="right" width="75"><?php echo ($prices[$value] == "-") ? "" : printCurr($prices[$value], $settings); ?></td>
<?php
								}
?>
											</tr>
<?php
							}
						}
?>
										</table>
<?php
						if (!$finished) {
?>
										<input name="<?php echo $field['GFID']; ?>" id="<?php echo $field['GFID']; ?>" type="hidden" value="<?php echo $_POST[$field['GFID']]; ?>" <?php if ($field['InitState'] < 2): ?>disabled="disabled" <?php endif; ?>/>
<?php
						}
					} else {
?>
										<table border="0" width="100%">
<?php
						$values = preg_split("/[\r\n]+/", $field['ValueList']);
						if ($field['PriceList'] > "")
							$prices = preg_split("/[\r\n]+/", $field['PriceList']);
						foreach ($values as $value => $caption) {
?>
											<tr>
												<td><input name="<?php echo $field['GFID']; ?>" id="<?php echo $field['GFID'] . "v" . ($value + 1); ?>" type="radio" value="<?php echo $value + 1; ?>" <?php if (($edit ? $_POST[$field['GFID']] : $field['DefValue']) == $value + 1): ?>checked="checked" <?php endif; ?><?php if (in_array($field['GFID2'], $eventVC)): ?>onclick="vcEvent('<?php echo $field['GFID']; ?>', <?php echo $field['Type']; ?>);" <?php endif; if ($field['InitState'] < 2): ?>disabled="disabled" <?php endif; ?>/><label for="<?php echo $field['GFID'] . "v" . ($value + 1); ?>"><?php echo $caption; ?></label></td>
<?php
							if ($field['PriceList'] > "") {
?>
												<td align="right" width="75"><label for="<?php echo $field['GFID'] . "v" . ($value + 1); ?>"><?php echo ($prices[$value] == "-") ? "" : printCurr($prices[$value], $settings); ?></label></td>
<?php
							}
?>
											</tr>
<?php
						}
?>
										</table>
<?php
					}
?>
									</td>
								</tr>
<?php
				break;
				case 8: // drop-down menu
?>
								<tr id="row_<?php echo $field['GFID']; ?>" valign="top"<?php if ($field['InitState'] < 2): ?> disabled="disabled"<?php endif; if ($field['InitState'] < 1): ?> style="display: none;"<?php endif; if (isset($errors) && array_key_exists($field['GFID'], $errors)): ?> title="Error: <?php echo $errors[$field['GFID']]; ?>" class="error"<?php endif; ?>>
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?><?php if ($field['Required']): ?> <span style="color: #FF0000;">*</span><?php endif; ?></td>
									<td>
<?php
					if ($confirm || $finished) {
?>
										<table border="0" width="100%">
<?php
						$values = preg_split("/[\r\n]+/", $field['ValueList']);
						if ($field['PriceList'] > "")
							$prices = preg_split("/[\r\n]+/", $field['PriceList']);
						foreach ($values as $value => $caption) {
							if ($_POST[$field['GFID']] == $value + 1) {
?>
											<tr>
												<td><?php echo $caption; ?></td>
<?php
								if ($field['PriceList'] > "") {
									if (!$finished && ($prices[$value] != "-"))
										$totalPrice += $prices[$value];
?>
												<td align="right" width="75"><?php echo ($prices[$value] == "-") ? "" : printCurr($prices[$value], $settings); ?></td>
<?php
								}
?>
											</tr>
<?php
							}
						}
?>
										</table>
<?php
						if (!$finished) {
?>
										<input name="<?php echo $field['GFID']; ?>" id="<?php echo $field['GFID']; ?>" type="hidden" value="<?php echo $_POST[$field['GFID']]; ?>" <?php if ($field['InitState'] < 2): ?>disabled="disabled" <?php endif; ?>/>
<?php
						}
					} else {
?>
										<select name="<?php echo $field['GFID']; ?>"  id="<?php echo $field['GFID']; ?>" style="width: <?php echo $field['Width']; ?>px;"<?php if (in_array($field['GFID2'], $eventVC)): ?> onchange="vcEvent('<?php echo $field['GFID']; ?>', <?php echo $field['Type']; ?>);"<?php endif; if ($field['InitState'] < 2): ?>disabled="disabled" <?php endif; ?>>
<?php
						if ($field['DefValue'] == 0) { // only add blank option if no default is selected
?>
											<option value="--" selected="selected"></option>
<?php
						}
						$values = preg_split("/[\r\n]+/", $field['ValueList']);
						if ($field['PriceList'] > "")
							$prices = preg_split("/[\r\n]+/", $field['PriceList']);
						foreach ($values as $value => $caption) {
							if ($field['PriceList'] > "")
								if ($prices[$value] != "-")
									$caption = sp2nbsp($caption . "          " . printCurr($prices[$value], $settings));
?>
											<option value="<?php echo $value + 1; ?>"<?php if (($edit ? $_POST[$field['GFID']] : $field['DefValue']) == $value + 1): ?> selected="selected"<?php endif; ?>><?php echo $caption; ?></option>
<?php
						}
?>
										</select>
<?php
					}
?>
									</td>
								</tr>
<?php
				break;
				case 9: // selectable list
?>
								<tr id="row_<?php echo $field['GFID']; ?>" valign="top"<?php if ($field['InitState'] < 2): ?> disabled="disabled"<?php endif; if ($field['InitState'] < 1): ?> style="display: none;"<?php endif; if (isset($errors) && array_key_exists($field['GFID'], $errors)): ?> title="Error: <?php echo $errors[$field['GFID']]; ?>" class="error"<?php endif; ?>>
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?><?php if ($field['Required']): ?> <span style="color: #FF0000;">*</span><?php endif; ?></td>
									<td>
<?php
					if ($confirm || $finished) {
?>
										<table border="0" width="100%">
<?php
						$values = preg_split("/[\r\n]+/", $field['ValueList']);
						if ($field['PriceList'] > "")
							$prices = preg_split("/[\r\n]+/", $field['PriceList']);
						$checked = $_POST[$field['GFID']];
						$checknum = 0;
						foreach ($values as $value => $caption) {
							if ($checked[$checknum] == $value + 1) {
?>
											<tr>
												<td><?php echo $caption; ?><input name="<?php echo $field['GFID'] . "[]"; ?>" id="<?php echo $field['GFID'] . "v" . ($value + 1); ?>" type="hidden" value="<?php echo $value + 1; ?>" <?php if ($field['InitState'] < 2): ?>disabled="disabled" <?php endif; ?>/></td>
<?php
								if ($field['PriceList'] > "") {
									if ($prices[$value] != "-")
										$totalPrice += $prices[$value];
?>
												<td align="right" width="75"><?php echo ($prices[$value] == "-") ? "" : printCurr($prices[$value], $settings); ?></td>
<?php
								}
?>
											</tr>
<?php
								$checknum++;
							}
						}
?>
										</table>
<?php
					} else {
?>
										<select name="<?php echo $field['GFID'] . "[]"; ?>"  id="<?php echo $field['GFID']; ?>" size="<?php echo $field['Height']; ?>" multiple="multiple" style="width: <?php echo $field['Width']; ?>px;"<?php if (in_array($field['GFID2'], $eventVC)): ?> onchange="vcEvent('<?php echo $field['GFID']; ?>', <?php echo $field['Type']; ?>);"<?php endif; if ($field['InitState'] < 2): ?>disabled="disabled" <?php endif; ?>>
<?php
						$values = preg_split("/[\r\n]+/", $field['ValueList']);
						if ($field['PriceList'] > "")
							$prices = preg_split("/[\r\n]+/", $field['PriceList']);
						if ($edit)
							$checked = $_POST[$field['GFID']];
						else
							$checked = preg_split("/ /", $field['DefValue']);
						$checknum = 0;
						foreach ($values as $value => $caption) {
							if ($field['PriceList'] > "")
								if ($prices[$value] != "-")
									$caption = sp2nbsp($caption . "          " . printCurr($prices[$value], $settings));
?>
											<option value="<?php echo $value + 1; ?>"<?php if ($checked[$checknum] == $value + 1): ?> selected="selected"<?php $checknum++; endif; ?>><?php echo $caption; ?></option>
<?php
						}
?>
										</select>
<?php
					}
?>
									</td>
								</tr>
<?php
				break;
			}
		}
		// add empty row to ensure box is drawn (where applicable)
		if ($fieldRow == -1) {
?>
								<tr style="height: 50px">
									<td>&nbsp;</td>
								</tr>
<?php
		}
?>
							</table>
<?php
		if ($group['Box']) {
?>
						</fieldset>
<?php
		}
?>
					</td>
				</tr>
<?php
	}
	if ($confirm) {
		if ($totalPrice < 0)
			$totalPrice = 0;
?>
				<tr>
					<td>
						<table width="100%">
							<tr>
								<td align="right"><strong>Total Price:</strong></td>
								<td align="right" width="75"><strong><?php echo printCurr($totalPrice, $settings); ?></strong><input name="totalprice" id="totalprice" type="hidden" value="<?php echo $totalPrice; ?>" /></td>
								<td width="25"></td>
							</tr>
						</table>
					</td>
				</tr>
<?php
	}
	if (!$finished) {
?>
				<tr>
					<td>
<?php
		if ($confirm) {
?>
						<input name="submit" type="submit" id="confirm" value="Confirm" style="width: 100px;" />
						<input name="submit" type="submit" id="edit" value="Edit" style="width: 100px;" />
<?php
		} else {
?>
						<input name="submit" type="submit" id="submit" value="Submit" style="width: 100px;" />
<?php
		}
?>
						<input name="clear" type="submit" id="clear" value="Clear" style="width: 100px;" />
					</td>
				</tr>
<?php
	} else {
		if (isset($_GET['login']) && !$_GET['login']) {
			$returnPath = $php_root_path . "/index.php";
			$returnName = "Login Page";
		} else {
			$returnPath = $php_root_path . "/user/view_papers.php";
			$returnName = "User Home";
		}
?>
				<tr>
					<td height="10"></td>
				</tr>
				<tr>
					<td><a href="<?php echo $returnPath; ?>">&laquo; Back to <?php echo $returnName; ?></a></td>
				</tr>
<?php
	}
?>
			</table>
		</td>
	</tr>
</table>
<?php
	if (!$finished) {
?>
</form>
<?php
	}
?>
<?php
do_html_footer();
?>

<?php
$php_root_path = "..";
$privilege_root_path = "/admin";
require_once("includes/include_all_fns.inc");
session_start();
if (!$db = adodb_connect()) {
	do_html_header("Create Attendee Registration Form &#151; Error");
	do_html_footer("Error connecting to database.");
	exit;
}

require_once("includes/rego_form_functions.php");

// page used for creating form as well as displaying help
if ($_GET['help'] == "viscond") { // display help
	do_html_header("Help: Visibility Conditions");
?>
	<p>An advanced feature of the registration form is the ability to set <em>Visibility
		Conditions</em>. They allow you to specify that a particular group or field
		will only be displayed or enabled after another condition has been satisfied.</p>
	<p>Each group or field has two visibility properties &#0150; <em>visible</em> and <em>enabled</em> 
		&#0150; each with two options &#0150; <em>true</em> or <em>false</em>. There
		is only one event &#0150; <em>when</em> &#0150; which is triggered when the
		specified field changes (either the text changes or a different option is
		selected).</p>
	<p>Groups and fields are specified using the numbers that appear beside the
		fields in blue. For example, [3:5] represents group 3, field 5. If the
		field is a checkbox group, radio group, drop-down menu or selectable
		list, you can reference the field's options numerically by counting the
		number of options starting at 1. However, if the first option of a drop-down
		menu is <em>--- (select) ---</em>, this does not count as an option and the
		next option is number 1. If the field is a checkbox or radio button (not
		a group), it has only one option and is referenced as number 1. If the field
		is text, you can specifiy a comparison string in curly braces &#0150; { }.</p>
	<p>You can use more than one comparison string or number, although they must
		all be of the same type (only text fields can accept strings). To do
		this, the set of comparisons is enclosed with brackets and each comparison
		is separated from the next with the <em>or</em> keyword. However, if the whole
		comparison set is preceeded by <em>not</em> in which case the keyword <em>and</em> must
		be used (this is the logical opposite of <em>or</em>). You cannot use the <em>not</em> keyword
		inside a comparison set as it can only be applied to the whole statement.</p>
	<p>You will see these visibility conditions in action only while you are not
		in edit mode for groups or fields (ie: when you can see the whole registration
		form, not just one group or field). When you write a visibility condition,
		press the <em>Apply</em> button to save your edits to the group or field. If
		the visibility condition has errors, you will be notified after saving.
		However, if you click <em>Ok</em>, you will not be notified of the error until
		you reopen the group or field for editing.</p>
	<p>The syntax is as follows:</p>
	<pre style="margin: 0 0 0 20;">&lt;action&gt; <strong>when</strong> &lt;field&gt; <strong>is</strong> &lt;negate&gt; &lt;value&gt;</pre>
	<p>where</p>
	<pre style="margin: 0 0 0 20;">&lt;action&gt; = <strong>visible</strong> or <strong>enabled</strong></pre>
	<pre style="margin: 0 0 0 20;">&lt;field&gt; = <strong>[</strong><em>group_number</em><strong>:</strong><em>field_number</em><strong>]</strong></pre>
	<pre style="margin: 0 0 0 20;">&lt;negate&gt; = <strong>not</strong> or omitted</pre>
	<pre style="margin: 0 0 0 20;">&lt;value&gt; = <em>number</em> or <strong>{</strong><em>string</em><strong>}</strong> or &lt;valueset&gt;</pre>
	<pre style="margin: 0 0 0 20;">&lt;valueset&gt; = <strong>(</strong><em>number</em> or <strong>{</strong><em>string</em><strong>}</strong> &lt;join&gt;</pre>
	<pre style="margin: 0 0 0 20;">             <em>number</em> or <strong>{</strong><em>string</em><strong>}</strong> [...]<strong>)</strong></pre>
	<pre style="margin: 0 0 0 20;">&lt;join&gt; = <strong>or</strong> if not negated, or <strong>and</strong> if negated</pre>
	<p>The following examples should help your understanding of the Visibility Conditions.</p>
	<div style="margin: 0 10 0 10;">
		<fieldset>
			<legend>Example 1</legend>
			<pre>visible when [3:4] is 1</pre>
			<em>displays field only when group 3 field 4 is selected (group 3 field 4
			must be a checkbox or radio button) or has its first option selected
			(group 3 field 4 must be a checkbox group, radio button group, drop-down
			menu or selectable list)</em>
		</fieldset>
		<p></p>
		<fieldset>
			<legend>Example 2</legend>
			<pre>visible when [5:8] is 3</pre>
			<em>displays field only when group 5 field 8 has its third option selected
			(group 5 field 8 must be a checkbox group, radio button group, drop-down
			menu or selectable list)</em>
		</fieldset>
		<p></p>
		<fieldset>
			<legend>Example 3</legend>
			<pre>enabled when [1:2] is {dog}</pre>
			<em>enables field only when group 1 field 2 has a value of 'dog' (excluding
			quotes) (group 1 field 2 must be a text field (single or multiple lines))</em>
		</fieldset>
		<p></p>
		<fieldset>
			<legend>Example 4</legend>
			<pre>enabled when [3:1] is not ({cat} and {bird})</pre>
			<em>enables field only when group 3 field 1 has a value that is not 'cat' and
			'bird' (excluding quotes)</em>
		</fieldset>
		<p></p>
		<fieldset>
			<legend>Example 5</legend>
			<pre>visible when [7:3] is (2 or 3 or 5)</pre>
			<em>displays field only when group 7 field 3 has its second, third or fifth
			option selected</em>
		</fieldset>
	</div>
	<p></p>
<?php
} elseif ($_GET['help'] == "prices") { // display help
	do_html_header("Help: Prices");
?>
	<p>You can add prices to checkbox groups, radio button groups, drop-down menus
		and selectable lists. If the option associated with that price is selected,
		then that price is included in the calculation of the total price payable.
		The calculated total price is displayed on the page after the form is
		submitted. This is the same page that validates the submitted data.</p>
	<p>To add prices to a field, end each line with two pipe characters  &#150; || &#150; (same
		key as \ on the keyboard), followed by a decimal currency amount. The
		price must have a decimal point and at least one digit on either side
		of the decimal point. The price can be negative (to give a discount)
		by inserting a dash/hyphen/minus sign between the pipe characters and
		the first digit.</p>
	<p>If you want a particular option to have no price attached to it (only some
		options cost more), you can either set the price to 0.00 (a price of
		0.00 will be displayed) or follow the pipe characters with a dash/hyphen
		(no price will be displayed). If any option in the field has a price, <em>all</em> lines
		must have the pipe characters at the end (whether there is a price attatched
		or not). If any value has omitted the pipe characters, all subsequent
		prices will be shifted up one value.</p>
	<p>Do not include a currency symbol &#150; it will be added automatically based
		on settings on the <em>General Settings</em> page.</p>
<?php
} else {

	// retrieve conference settings
	$settings = get_Conference_Settings();
	// define types of edits
	$levelType = array(0 => "none", 1 => "group", 2 => "field");
	// define types of fields
	$fieldType = array(1 => "plain text", 2 => "text field (single line)", 3 => "text field (multiple lines)", 4 => "checkbox", 5 => "checkbox group",
	                   6 => "radio button", 7 => "radio button group", 8 => "drop-down menu", 9 => "selectable list");
	// define types of validation functions
	$valFunctions = array(11 => "numeric only", 12 => "numeric only (including spaces and dashes)",
	                      13 => "phone number (characters and basic syntax)",
	                      14 => "email address (syntax only)", 15 => "web site/page (syntax only)",
	                      21 => "registered username", 22 => "registered author", 23 => "registered paper name",
	                      31 => "credit card number", 32 => "credit card expiry date");
	
	// check $_POST to see if form has been submitted (for edits)
	if (count($_POST) > 0) {
		// get action
		$pos = strpos($_POST['action'], " ");
		if ($pos === false)
			$action = strtolower($_POST['action']);
		else
			$action = substr(strtolower($_POST['action']), 0, $pos);
		// get level
		$level = (int) $_POST['level'];
		// get group id
		$groupID = (int) $_POST['group'];
		// get field id
		$fieldID = (int) $_POST['field'];
		// test action/level
		if ($action == "back" || $action == "cancel")
			$level--;
		elseif ($action == "edit" || $action == "add") {
			do_html_header("Create Attendee Registration Form &#151; " . ucwords($action . " " . $editType[$level]));
			$level++;
		} elseif ($action == "next")
			do_html_header("Create Attendee Registration Form &#151; " . ucwords("add " . $editType[$level]));
		// perform action as required
		switch ($action) {
			case "up":
			case "down":
				// statements to swap order (and move group/field up or down)
				$moveItem = ($level == 0 ? $groupID : $fieldID);
				$swapItem = ($action == "up" ? $moveItem - 1 : $moveItem + 1);
				$tempItem = 255; // max value for datatype (TINYINT)
				// prepare then execute SQL update statements (two updates to change group)
				for ($i = ($level == 0 ? 0 : 1); $i < 2; $i ++) {
					$prepSwap = $db->Prepare("UPDATE " . ($i == 0 ? TBL_GROUPS : TBL_FIELDS) . " " .
					                         "SET " . ($level == 0 ? "GID" : "FID") . " = ?, " .
					                         "WHERE GID = " . ($level == 0 ? "?" : $groupID . " ") .
					                         ($level == 0 ? "" : "AND FID = ?"));
					$db->Execute($prepSwap, array($tempItem, $moveItem));
					$db->Execute($prepSwap, array($moveItem, $swapItem));
					$db->Execute($prepSwap, array($swapItem, $tempItem));
					// reorder tables to improve performance and deletions
					if ($i == 0)
						$db->Execute("ALTER TABLE " . TBL_GROUPS . " ORDER BY GID");
					else
						$db->Execute("ALTER TABLE " . TBL_FIELDS . " ORDER BY GID, FID");
				}
				break;
			case "delete":
				// execute SQL statements to delete group/field then update remaining groups/fields
				for ($i = ($level == 0 ? 0 : 1); $i < 2; $i ++) {
					// get UIDs of fields that will be deleted so details table can be altered later
					if ($i == 1)
						$rsUid = $db->Execute("SELECT DATE_FORMAT(UID, '%Y%m%d%H%i%s') AS UID " .
						                       "FROM " . TBL_FIELDS . " " .
						                       "WHERE GID = " . $groupID .
						                       ($level == 0 ? "" : " AND FID = " . $fieldID));
					// delete group/field (group requires that group to be deleted from fields table too)
					$db->Execute("DELETE FROM " . ($i == 0 ? TBL_GROUPS : TBL_FIELDS) . " " .
					             "WHERE GID = " . $groupID .
					             ($level == 0 ? "" : " AND FID = " . $fieldID));
					// update tables to reduce GIDs/FIDs by 1 for subsequent groups/fields
					$db->Execute("UPDATE " . ($i == 0 ? TBL_GROUPS : TBL_FIELDS) . " " .
					             "SET " . ($level == 0 ? "GID = GID - 1 " : "FID = FID - 1 ") .
					             "WHERE GID " . ($level == 0 ? "> " : "= ") . $groupID .
					             ($level == 0 ? "" : " AND FID > " . $fieldID));
					// alter details table to remove deleted fields
					if ($i == 1)
						while ($uid = $rsUid->FetchRow())
							$db->Execute("ALTER TABLE " . TBL_DETAILS . " " .
							             "DROP `" . $uid['UID'] . "`");
				}
				break;
			case "ok":
			case "apply":
				if ($level == 1) {
					$db->Execute("UPDATE " . TBL_GROUPS . " " .
					             "SET Title = '" . $_POST['title'] . "', " .
					             "Box = " . ($_POST['box'] ? "1" : "0") . ", " .
					             "VisCond = '" . $_POST['viscond'] . "', " .
					             "InitState = " . (is_numeric($_POST['initstate']) ? $_POST['initstate'] : "InitState") . ", " .
					             "CaptWidth = " . (is_numeric($_POST['captwidth']) ? $_POST['captwidth'] : "CaptWidth") . " " .
					             "WHERE GID = " . $groupID);
				} elseif ($level == 2) {
					if (is_array($_POST['valuelist'])) {
						// contains list of accepted credit card types, convert to text
						$valuelist = implode("\r\n", $_POST['valuelist']);
					} else {
						// split ValueList by removing prices and placing in PriceList (saved in separate columns)
						preg_match_all("/\|{2}((?:\-?[0-9]+(?:\.[0-9]+)?)|\-)/", $_POST['valuelist'], $matches, PREG_PATTERN_ORDER);
						$valuelist = preg_replace("/\|{2}((?:\-?[0-9]+(?:\.[0-9]+)?)|\-)/", "", $_POST['valuelist']);
						$pricelist = implode("\r\n", $matches[1]);
					}
					// now update database
					$db->Execute("UPDATE " . TBL_FIELDS . " " .
					             "SET Caption = '" . $_POST['caption'] . "', " .
					             "DefValue = '" . (is_array($_POST['defvalue']) ? (in_array(0, $_POST['defvalue']) ? 0 : implode(" ", $_POST['defvalue'])) : $_POST['defvalue']) . "', " .
					             "Required = " . ($_POST['required'] ? "1" : "0") . ", " .
					             "VisCond = '" . $_POST['viscond'] . "', " .
					             "InitState = " . (is_numeric($_POST['initstate']) ? $_POST['initstate'] : "InitState") . ", " .
					             "ValueList = '" . $valuelist . "', " .
					             "PriceList = '" . $pricelist . "', " .
					             "CharLen = " . (is_numeric($_POST['charlen']) ? $_POST['charlen'] : "CharLen") . ", " .
					             "ValFunction = " . (is_numeric($_POST['valfunction']) ? $_POST['valfunction'] : "ValFunction") . ", " .
					             "Width = " . (is_numeric($_POST['width']) ? $_POST['width'] : "Width") . ", " .
					             "Height = " . (is_numeric($_POST['height']) ? $_POST['height'] : "Height") . " " .
					             "WHERE GID = " . $groupID . " " .
					             "AND FID = " . $fieldID);
				}
				// OK button returns to previous level after saving (apply only saves)
				if ($action == "ok")
					$level--;
				break;
			case "add":
				if ($level == 1) {
					// insert blank row for new GID
					$db->Execute("INSERT INTO " . TBL_GROUPS . " (GID, UID) " .
					             "VALUES (" . $groupID . "), NOW()");
				}
				break;
			case "next":
				if ($level == 2) {
					// insert blank row for new GID/FID with selected field type
					$db->Execute("INSERT INTO " . TBL_FIELDS . " (GID, FID, Type, UID) " .
					             "VALUES (" . $groupID . ", " . $fieldID . ", " . $_POST['type'] . ", NOW())");
					if ($_POST['type'] > 1) {
						// get UID from inserted row
						$rsUid = $db->Execute("SELECT DATE_FORMAT(UID, '%Y%m%d%H%i%s') AS UID " .
						                      "FROM " . TBL_FIELDS . " " .
						                      "WHERE GID = " . $groupID . " " .
						                      "AND FID = " . $fieldID);
						if ($rowUid = $rsUid->FetchRow())
							$uid = $rowUid['UID'];
						// translate field type to SQL field type
						switch ($_POST['type']) {
							case 2: // text field
							case 5: // checkbox group
							case 9: // selectable list
								$fieldString = "VARCHAR(255) NOT NULL";
								break;
							case 3: // text area
								$fieldString = "TEXT";
								break;
							case 4: // checkbox
							case 6: // radio button
							case 7: // radio button group
							case 8: // drop-down menu
								$fieldString = "TINYINT(1) UNSIGNED NOT NULL";
								break;
						}
						// use UID to create new column in details table
						$db->Execute("ALTER TABLE " . TBL_DETAILS . " " .
						             "ADD `" . $uid . "` " . $fieldString);
					}
					// reorder table to improve performance and deletions
					$db->Execute("ALTER TABLE " . TBL_FIELDS . " ORDER BY GID, FID");
				}
				break;
		}
	} else
		$level = 0;
	if (!($action == "edit" || $action == "add" || $action == "next"))
		do_html_header("Create Attendee Registration Form");
	// queries to select groups and fields (depends on level)
	// common part of sql statements
	$commonGroups = "SELECT GID, CONCAT('g', GID, 'f0') AS GFID, Title, Box, VisCond, InitState, CaptWidth, DATE_FORMAT(UID, '%Y%m%d%H%i%s') AS UID " .
	                "FROM " . TBL_GROUPS . " ";
	$commonFields = "SELECT GID, FID, CONCAT('g', GID, 'f', FID) AS GFID, CONCAT(GID, ':', FID) AS GFID2, Type, Caption, DefValue, Required, " .
	                "VisCond, InitState, ValueList, PriceList, CharLen, ValFunction, Width, Height, DATE_FORMAT(UID, '%Y%m%d%H%i%s') AS UID " .
	                "FROM " . TBL_FIELDS . " ";
	switch ($level) {
		case 0:
			// query to select all groups
			$rsGroups = $db->Execute($commonGroups .
			                         "ORDER BY GID");
			// prepared query to select all fields from specified group (supplied later)
			$prepFields = $db->Prepare($commonFields .
			                           "WHERE GID = ? " .
			                           "ORDER BY GID, FID");
			break;
		case 1:
			// query to select current group
			$rsGroups = $db->Execute($commonGroups .
			                         "WHERE GID = " . $groupID . " " .
			                         "ORDER BY GID");
			// query to select all fields from current group
			$rsFields = $db->Execute($commonFields .
			                         "WHERE GID = " . $groupID . " " .
			                         "ORDER BY GID, FID");
			break;
		case 2:
			// query to select current group
			$rsGroups = $db->Execute($commonGroups .
			                         "WHERE GID = " . $groupID . " " .
			                         "ORDER BY GID");
			// query to select current field
			$rsFields = $db->Execute($commonFields .
			                         "WHERE GID = " . $groupID . " " .
			                         "AND FID = " . $fieldID);
			break;
	}
	// select all visibility conditions (to generate global JavaScript)
	if ($level == 0)
		$rsVisCond = $db->Execute("SELECT GID, 0 AS FID, CONCAT('g', GID, 'f0') AS GFID, VisCond " .
		                          "FROM " . TBL_GROUPS . " " .
		                          "WHERE VisCond <> '' " .
		                          "UNION " .
		                          "SELECT GID, FID, CONCAT('g', GID, 'f', FID) AS GFID, VisCond " .
		                          "FROM " . TBL_FIELDS . " " .
		                          "WHERE VisCond <> ''");
?>
<script type="text/javascript" src="/conference/admin/script/rego_form_functions.js"></script>
<?php
	if ($level == 0) {
?>
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
<p>The attendee registration form is available to any person who wishes to attend
	the conference. They do not need to be a registered user of the site (ie:
	they do not need to be an author or reviewer). There are methods of linking
	the registration form to a registered user's account, which may be helpful
	to ensure someone is attending to present each paper. </p>
<p>To design a registration form, you must add a <em>group</em> and then add
	<em>fields</em> to the group. You can only add groups at the end of the form
	and similarly, you can only add fields at the end of a group. However, you
	can move groups and fields up and down on the form.</p>
<p>A sample registration form is provided upon installation of the system. You
	may use this form as is, make some changes to suit your needs, or delete
	it all and start again. It may be useful to have a look at how the sample
	form is made as it may help you gain ideas for your own registration form.</p>
<p><strong>You should not make any changes to this form after users have started
	registering. If you do, some of their registration details could be lost
	permanently. You will not be warned if you are about to lose any registration
	details. This is your only warning. To prevent this situation, customise
	the registration form before you enable it on the General Settings page.</strong></p>
<form name="vcoverride" id="vcoverride" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
	<p>
		<input type="checkbox" name="showall" id="showall" value="1" onclick="submit();" <?php if ($_GET['showall']): ?>checked="checked" <?php endif; ?>/>
		<label for="showall">Show all groups/fields (disable visibility conditions)</label>
	</p>
</form>
<?php
	}
?>
<table width="100%" border="0" align="center" cellspacing="20" id="createform">
<?php
	if ($level == 1) {
		$group = $rsGroups->FetchRow();
?>
	<tr>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']; ?>" method="post" name="options">
				<table border="0" align="center" cellspacing="2" id="group<?php echo $group['GID']; ?>opt">
					<tr align="center">
						<td colspan="2"><strong>GROUP OPTIONS</strong></td>
					</tr>
					<tr valign="top">
						<td width="200">Title</td>
						<td><input name="title" id="title" type="text" value="<?php echo $group['Title']; ?>" maxlength="64" style="width: 300px;" /></td>
					</tr>
					<tr valign="top">
						<td width="200">Display in Box</td>
						<td valign="middle"><input name="box" id="box" type="checkbox" value="1" <?php if ($group['Box']): ?>checked="checked" <?php endif; ?>/></td>
					</tr>
					<tr valign="top">
						<td width="200">Width of Caption Column</td>
						<td><input name="captwidth" id="captwidth" type="text" value="<?php echo $group['CaptWidth']; ?>" maxlength="5" style="width: 50px;" /> pixels</td>
					</tr>
					<tr valign="top">
						<td width="200">Visibility Conditions <a href="javascript:openHelp('<?php echo $_SERVER['PHP_SELF']; ?>', 'viscond')">[help]</a></td>
						<td><textarea name="viscond" id="viscond" rows="4" style="width: 300px;"><?php echo $group['VisCond']; ?></textarea></td>
					</tr>
					<tr valign="top">
						<td width="200">Initial State</td>
						<td>
							<select name="initstate" id="initstate" style="width: 200px;">
								<option value="2"<?php if ($group['InitState'] == 2): ?> selected="selected"<?php endif; ?>>Normal (Visible &amp; Enabled)</option>
								<option value="1"<?php if ($group['InitState'] == 1): ?> selected="selected"<?php endif; ?>>Visible but not Enabled</option>
								<option value="0"<?php if ($group['InitState'] == 0): ?> selected="selected"<?php endif; ?>>Not Visible</option>
							</select>
						</td>
					</tr>
					<tr>
						<td height="20" colspan="2"></td>
					</tr>
					<tr valign="top">
						<td colspan="2" align="right">
							<input name="uid" id="uid" type="hidden" value="<?php echo $group['UID']; ?>" />
							<input name="group" id="group" type="hidden" value="<?php echo $group['GID']; ?>" />
							<input name="level" id="levelopt" type="hidden" value="<?php echo $level; ?>" />
							<input type="submit" name="action" id="actionok" value="OK" style="width: 75px;" />
							<input type="submit" name="action" id="actioncancel" value="Cancel" style="width: 75px;" />
							<input type="submit" name="action" id="actionapply" value="Apply" style="width: 75px;" />
						</td>
					</tr>
				</table>
			</form>
		</td>
	</tr>
<?php
		// return to first row
		$rsGroups->MoveFirst();
	}
	elseif ($level == 2) {
		if ($action != "add") {
			$field = $rsFields->FetchRow();
?>
	<tr>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']; ?>" method="post" name="options">
				<table border="0" align="center" cellspacing="2" id="group<?php echo $field['FID']; ?>opt">
					<tr align="center">
						<td colspan="2"><strong>FIELD OPTIONS</strong></td>
					</tr>
					<tr valign="top">
						<td width="200">Type of Field</td>
						<td><?php echo $fieldType[$field['Type']]; ?><input name="type" id="type" type="hidden" value="<?php echo $field['Type']; ?>" /></td>
					</tr>
					<tr valign="top">
						<td width="200">Caption</td>
						<td><input name="caption" id="caption" type="text" value="<?php echo $field['Caption']; ?>" maxlength="128" style="width: 300px;" /></td>
					</tr>
					<tr valign="top">
						<td width="200">Required</td>
						<td valign="middle"><input name="required" id="required" type="checkbox" value="1" <?php if ($field['Required']): ?>checked="checked" <?php endif; ?>/></td>
					</tr>
<?php
				if ($field['Type'] == 1) { // plain text
?>
					<tr valign="top">
						<td width="200">Text</td>
						<td><textarea name="valuelist" id="valuelist" rows="6" style="width: 300px;"><?php echo $field['ValueList']; ?></textarea></td>
					</tr>
<?php
				} elseif ($field['Type'] == 5 || $field['Type'] >= 7) { // check/radio group or drop down menu or selectable list
?>
					<tr valign="top">
						<td width="200">Value List<br />
							(one per line)<br />
							<br />
							* Prices can be attached <a href="javascript:openHelp('<?php echo $_SERVER['PHP_SELF']; ?>', 'prices')">[help]</a><br />
							* Apply to refresh default value</td>
<?php
					// combine ValueList and PriceList
					if ($field['PriceList'] > "") {
						$values = preg_split("/[\r\n]+/", $field['ValueList']);
						$prices = preg_split("/[\r\n]+/", $field['PriceList']);
						for ($i = 0; $i < sizeof($values); $i ++)
							$values[$i] .= "||" . $prices[$i];
						$valueList = implode("\r\n", $values);
					} else
						$valueList = $field['ValueList'];
?>
						<td><textarea name="valuelist" id="valuelist" rows="6" style="width: 300px;"><?php echo $valueList; ?></textarea></td>
					</tr>
<?php
				}
				switch ($field['Type']) {
					case 2: // text field
?>
					<tr valign="top">
						<td width="200">Default Value</td>
						<td><input name="defvalue" id="defvalue" type="text" value="<?php echo $field['DefValue']; ?>" maxlength="128" style="width: 300px;" /></td>
					</tr>
<?php
					break;
					case 3: // text area
?>
					<tr valign="top">
						<td width="200">Default Value</td>
						<td><textarea name="defvalue" id="defvalue" rows="5" style="width: 300px;"><?php echo $field['DefValue']; ?></textarea></td>
					</tr>
<?php
					break;
					case 4: // check box
					case 6: // radio button
?>
					<tr valign="top">
						<td width="200">Default Value</td>
						<td>
							<input name="defvalue" id="defvalue1" type="radio" value="1" <?php if ($field['DefValue']): ?>checked="checked" <?php endif; ?>/><label for="defvalue1">Checked</label>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							<input name="defvalue" id="defvalue0" type="radio" value="0" <?php if (!$field['DefValue']): ?>checked="checked" <?php endif; ?>/><label for="defvalue0">Unchecked</label>
						</td>
					</tr>
<?php
					break;
					case 5: // check box group
					case 9: // selectable list
?>
					<tr valign="top">
						<td width="200">Default Values<br />
							(use Ctrl to select multiple)</td>
						<td>
							<select name="defvalue[]" size="5" multiple="multiple" id="defvalue" style="width: 300px;">
								<option value="--"<?php if ($field['DefValue'] == 0): ?> selected="selected"<?php endif; ?>>--- (none) ---</option>
<?php
						$values = preg_split("/[\r\n]+/", $field['ValueList']);
						$checked = preg_split("/ /", $field['DefValue']);
						$checknum = 0;
						foreach ($values as $value => $caption) {
?>
								<option value="<?php echo $value + 1; ?>"<?php if ($field['DefValue'] != "" && $checked[$checknum] == $value + 1): ?> selected="selected"<?php $checknum++; endif; ?>><?php echo $caption; ?></option>
<?php
					}
?>
							</select>
						</td>
					</tr>
<?php
					break;
					case 7: // radio button group
					case 8: // drop down menu
?>
					<tr valign="top">
						<td width="200">Default Value</td>
						<td>
							<select name="defvalue" id="defvalue" style="width: 300px;">
								<option value="--"<?php if ($field['DefValue'] == 0): ?> selected="selected"<?php endif; ?>>--- (none) ---</option>
<?php
						$values = preg_split("/[\r\n]+/", $field['ValueList']);
						foreach ($values as $value => $caption) {
?>
								<option value="<?php echo $value + 1; ?>"<?php if ($field['DefValue'] == $value + 1): ?> selected="selected"<?php endif; ?>><?php echo $caption; ?></option>
<?php
						}
?>
							</select>
						</td>
					</tr>
<?php
					break;
				}
?>
					<tr valign="top">
						<td width="200">Visibility Conditions <a href="javascript:openHelp('<?php echo $_SERVER['PHP_SELF']; ?>', 'viscond')">[help]</a></td>
						<td><textarea name="viscond" id="viscond" rows="4" style="width: 300px;"><?php echo $field['VisCond']; ?></textarea></td>
					</tr>
					<tr valign="top">
						<td width="200">Initial State</td>
						<td>
							<select name="initstate" id="initstate" style="width: 200px;">
								<option value="2"<?php if ($field['InitState'] == 2): ?> selected="selected"<?php endif; ?>>Normal (Visible &amp; Enabled)</option>
								<option value="1"<?php if ($field['InitState'] == 1): ?> selected="selected"<?php endif; ?>>Visible but not Enabled</option>
								<option value="0"<?php if ($field['InitState'] == 0): ?> selected="selected"<?php endif; ?>>Not Visible</option>
							</select>
						</td>
					</tr>
<?php
				if ($field['Type'] == 2) {
?>
					<tr valign="top">
						<td>Character Length</td>
						<td><input name="charlen" id="charlen" type="text" value="<?php echo $field['CharLen']; ?>" maxlength="5" style="width: 50px;" /> 
						(maximum 255)</td>
					</tr>
<?php
				}
				if ($field['Type'] == 2) {
?>
					<tr valign="top">
						<td>Validation Function</td>
						<td><select name="valfunction" id="valfunction" style="width: 300px;" onchange="ccValFuncAcceptedCards(this)">
								<option value="--"<?php if ($field['ValFunction'] == 0): ?> selected="selected"<?php endif; ?>>--- (none) ---</option>
<?php
						foreach ($valFunctions as $value => $caption) {
?>
								<option value="<?php echo $value; ?>"<?php if ($field['ValFunction'] == $value): ?> selected="selected"<?php endif; ?>><?php echo $caption; ?></option>
<?php
						}
?>
							</select>
						</td>
					</tr>
<?php
				}
				if ($field['Type'] == 2) {
?>
					<tr valign="top" id="acceptedcards"<?php if ($field['ValFunction'] != 31): ?> style="display: none;"<?php endif; ?>>
						<td width="200">Accepted Card Types</td>
						<td>
							<table border="0" width="100%">
<?php
					$values = preg_split("/[\r\n]+/", $field['ValueList']);
?>
								<tr>
									<td width="50%">
										<input name="valuelist[]" id="valuelist1" type="checkbox" value="MasterCard" <?php if (in_array("MasterCard", $values)): ?>checked="checked" <?php endif; ?>/><label for="valuelist1">MasterCard</label>
									</td>
									<td width="50%">
										<input name="valuelist[]" id="valuelist2" type="checkbox" value="Visa" <?php if (in_array("Visa", $values)): ?>checked="checked" <?php endif; ?>/><label for="valuelist2">Visa</label>
									</td>
								</tr>
								<tr>
									<td width="50%">
										<input name="valuelist[]" id="valuelist3" type="checkbox" value="BankCard" <?php if (in_array("BankCard", $values)): ?>checked="checked" <?php endif; ?>/><label for="valuelist3">BankCard</label>
									</td>
									<td width="50%">
										<input name="valuelist[]" id="valuelist4" type="checkbox" value="American Express" <?php if (in_array("American Express", $values)): ?>checked="checked" <?php endif; ?>/><label for="valuelist4">American Express</label>
									</td>
								</tr>
								<tr>
									<td width="50%">
										<input name="valuelist[]" id="valuelist5" type="checkbox" value="Diners Club" <?php if (in_array("Diners Club", $values)): ?>checked="checked" <?php endif; ?>/><label for="valuelist5">Diners Club</label>
									</td>
									<td width="50%">
										<input name="valuelist[]" id="valuelist6" type="checkbox" value="Carte Blanche" <?php if (in_array("Carte Blanche", $values)): ?>checked="checked" <?php endif; ?>/><label for="valuelist6">Carte Blanche</label>
									</td>
								</tr>
								<tr>
									<td width="50%">
										<input name="valuelist[]" id="valuelist7" type="checkbox" value="Discover" <?php if (in_array("Discover", $values)): ?>checked="checked" <?php endif; ?>/><label for="valuelist7">Discover</label>
									</td>
									<td width="50%">
										<input name="valuelist[]" id="valuelist8" type="checkbox" value="enRoute" <?php if (in_array("enRoute", $values)): ?>checked="checked" <?php endif; ?>/><label for="valuelist8">enRoute</label>
									</td>
								</tr>
								<tr>
									<td width="50%">
										<input name="valuelist[]" id="valuelist9" type="checkbox" value="JCB" <?php if (in_array("JCB", $values)): ?>checked="checked" <?php endif; ?>/><label for="valuelist9">JCB</label>
									</td>
									<td width="50%"></td>
								</tr>
							</table>
						</td>
					</tr>
<?php
				}
				if ($field['Type'] == 1 || $field['Type'] == 2 || $field['Type'] == 3 || $field['Type'] == 8 || $field['Type'] == 9) {
?>
					<tr valign="top">
						<td width="200">Width of Field</td>
						<td><input name="width" id="width" type="text" value="<?php echo $field['Width']; ?>" maxlength="5" style="width: 50px;" /> pixels</td>
					</tr>
<?php
					if ($field['Type'] == 3 || $field['Type'] == 9) {
?>
					<tr valign="top">
						<td width="200">Height of Field</td>
						<td><input name="height" id="height" type="text" value="<?php echo $field['Height']; ?>" maxlength="3" style="width: 50px;" /> lines</td>
					</tr>
<?php
					}
				}
?>
					<tr>
						<td height="20" colspan="2"></td>
					</tr>
					<tr valign="top">
						<td colspan="2" align="right">
							<input name="uid" id="uid" type="hidden" value="<?php echo $field['UID']; ?>" />
							<input name="group" id="group" type="hidden" value="<?php echo $groupID; ?>" />
							<input name="field" id="field" type="hidden" value="<?php echo $field['FID']; ?>" />
							<input name="level" id="levelopt" type="hidden" value="<?php echo $level; ?>" />
							<input type="submit" name="action" id="actionok" value="OK" style="width: 75px;" />
							<input type="submit" name="action" id="actioncancel" value="Cancel" style="width: 75px;" />
							<input type="submit" name="action" id="actionapply" value="Apply" style="width: 75px;" />
						</td>
					</tr>
				</table>
			</form>
		</td>
	</tr>
<?php
			// return to first row
			$rsFields->MoveFirst();
		} else {
?>
	<tr>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']; ?>" method="post" name="options">
				<table border="0" align="center" cellspacing="2" id="group<?php echo $field['FID']; ?>opt">
					<tr align="center">
						<td colspan="3"><strong>NEW FIELD TYPE</strong></td>
					</tr>
					<tr>
						<td width="455" colspan="3" valign="top">
							<fieldset>
								<legend>
									<input name="type" id="type1" type="radio" value="1" />
									<label for="type1">Plain text</label>&nbsp;
								</legend>
								Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
								eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam
								voluptua. At vero eos et accusam et justo duo dolores et ea rebum.
							</fieldset>
						</td>
					</tr>
					<tr>
						<td height="15" colspan="3"></td>
				   	</tr>
					<tr>
						<td width="215" valign="top">
							<fieldset>
								<legend>
									<input name="type" id="type2" type="radio" value="2" />
									<label for="type2">Text field (single line)</label>&nbsp;
								</legend>
								<input name="sample2" type="text" id="sample2" style="width: 200px;" value="Stet clita kasd gubergren," />
							</fieldset>
						</td>
						<td width="15"></td>
						<td width="215" valign="top">
							<fieldset>
								<legend>
									<input name="type" id="type3" type="radio" value="3" />
									<label for="type3">Text field (multiple lines)</label>&nbsp;
								</legend>
								<textarea name="sample3" rows="3" id="sample3" wrap="soft" style="width: 200px;">no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr,</textarea>
							</fieldset>
						</td>
					</tr>
					<tr>
						<td height="15" colspan="3"></td>
				   	</tr>
					<tr>
						<td width="215" valign="top">
							<fieldset>
								<legend>
									<input name="type" id="type4" type="radio" value="4" />
									<label for="type4">Checkbox (single)</label>&nbsp;
								</legend>
								<input name="sample4" type="checkbox" value="sample4" id="sample4" checked="checked" />
								sed diam nonumy eirmod
							</fieldset>
						</td>
						<td width="15"></td>
						<td width="215" valign="top">
							<fieldset>
								<legend>
									<input name="type" id="type6" type="radio" value="6" />
									<label for="type6">Radio button (single)</label>&nbsp;
								</legend>
								<input name="sample6" type="radio" value="sample6" id="sample6" checked="checked" />
								tempor invidunt ut labore
							</fieldset>
						</td>
					</tr>
					<tr>
						<td height="15" colspan="3"></td>
				   	</tr>
					<tr>
						<td width="215" valign="top">
							<fieldset>
								<legend>
									<input name="type" id="type5" type="radio" value="5" />
									<label for="type5">Checkbox (group)</label>&nbsp;
								</legend>
								<input name="sample5" type="checkbox" value="sample5a" id="sample5a" checked="checked" />
								et dolore magna aliquyam<br />
								<input name="sample5" type="checkbox" value="sample5b" id="sample5b" />
								erat, sed diam voluptua.<br />
								<input name="sample5" type="checkbox" value="sample5c" id="sample5c" checked="checked" />
								At vero eos et accusam
							</fieldset>
						</td>
						<td width="15"></td>
						<td width="215" valign="top">
							<fieldset>
								<legend>
									<input name="type" id="type7" type="radio" value="7" />
									<label for="type7">Radio button (group)</label>&nbsp;
								</legend>
								<input name="sample7" type="radio" value="sample7a" id="sample7a" />
								et justo duo dolores<br />
								<input name="sample7" type="radio" value="sample7b" id="sample7b" checked="checked" />
								et ea rebum. Stet clita<br />
								<input name="sample7" type="radio" value="sample7c" id="sample7c" />
								kasd gubergren, no sea
							</fieldset>
						</td>
					</tr>
					<tr>
						<td height="15" colspan="5"></td>
				   	</tr>
					<tr>
						<td width="215" valign="top">
							<fieldset>
								<legend>
									<input name="type" id="type8" type="radio" value="8" />
									<label for="type8">Drop-down menu</label>&nbsp;
								</legend>
								<select name="sample8" id="sample8" style="width: 200px;">
									<option value="sample8a" selected="selected">takimata sanctus est Lorem ipsum</option>
									<option value="sample8b">dolor sit amet. Lorem ipsum</option>
									<option value="sample8c">dolor sit amet, consetetur</option>
									<option value="sample8d">sadipscing elitr, sed diam nonumy</option>
									<option value="sample8e">eirmod tempor invidunt ut labore</option>
									<option value="sample8f">et dolore magna aliquyam erat,</option>
								</select>
							</fieldset>
						</td>
						<td width="15">&nbsp;</td>
						<td width="215" valign="top">
							<fieldset>
								<legend>
									<input name="type" id="type9" type="radio" value="9" />
									<label for="type9">Selectable list</label>&nbsp;
								</legend>
								<select name="sample9" size="4" multiple="multiple" id="sample9" style="width: 200px;">
									<option value="sample9a">sed diam voluptua. At vero</option>
									<option value="sample9b" selected="selected">eos et accusam et justo duo</option>
									<option value="sample9c" selected="selected">dolores et ea rebum. Stet</option>
									<option value="sample9d">clita kasd gubergren, no sea</option>
									<option value="sample9e">takimata sanctus est</option>
									<option value="sample9f" selected="selected">Lorem ipsumdolor sit amet.</option>
							</select>
							</fieldset>
						</td>
					</tr>
					<tr>
						<td height="20" colspan="5"></td>
					</tr>
					<tr valign="top">
						<td colspan="3" align="right">
							<input name="group" id="group" type="hidden" value="<?php echo $groupID; ?>" />
							<input name="field" id="field" type="hidden" value="<?php echo $fieldID; ?>" />
							<input name="level" id="level" type="hidden" value="<?php echo $level; ?>" />
							<input type="submit" name="action" id="actionnext" value="Next" style="width: 75px;" />
							<input type="submit" name="action" id="actioncancel" value="Cancel" style="width: 75px;" />
						</td>
					</tr>
				</table>
			</form>
		</td>
	</tr>
<?php
		}
	}
	if (!($level == 2 && $action == "add")) {
?>
	<tr align="center">
		<td style="white-space: nowrap"><strong>
			&#151; &#150; &#151; &#150; &#151; &nbsp;
			<?php echo ($level == 0 ? "REGISTRATION FORM" : "CURRENT " . strtoupper($levelType[$level])); ?> BEGINS HERE
			&nbsp; &#151; &#150; &#151; &#150; &#151;
		</strong></td>
	</tr>
	<tr>
		<td>
			<table border="0" align="center" cellspacing="10" id="regoform">
<?php
		// iterate groups
		$groupRow = -1;
		while ($group = $rsGroups->FetchRow()) {
			$groupRow++;
?>
				<tr id="row_<?php echo $group['GFID']; ?>"<?php if ($level == 0 && !$_GET['showall'] && $group['InitState'] < 2): ?> disabled="disabled"<?php endif; if ($level == 0 && !$_GET['showall'] && $group['InitState'] < 1): ?> style="display: none;"<?php endif; ?>>
					<td valign="top">
<?php
			if ($group['Box'] && $level == 0) { // add box (fieldset) if required
?>
						<fieldset>
							<legend><?php echo $group['Title']; // group name ?></legend>
<?php
			}
?>
							<table border="0" cellspacing="2" width="100%" id="group<?php echo $group['GID']; ?>">
<?php
			if ($level == 0) {
				// execute query to get all fields for current group
				$rsFields = $db->Execute($prepFields, array($group['GID']));
			}
			// iterate fields
			$fieldRow = -1;
			while ($field = $rsFields->FetchRow()) {
				$fieldRow++;
				switch ($field['Type']) {
					case 1: // plain text
?>
								<tr id="row_<?php echo $field['GFID']; ?>" valign="top"<?php if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 2): ?> disabled="disabled"<?php endif; if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 1): ?> style="display: none;"<?php endif; ?>>
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?><?php if ($field['Required']): ?> <span style="color: #FF0000;">*</span><?php endif; ?></td>
									<td><span style="width: <?php echo $field['Width']; ?>px"><?php echo nl2br($field['ValueList']); ?></span></td>
									<td width="50" align="right" valign="top" style="color: #0000FF; white-space: nowrap">[<?php echo $field['GFID2']; ?>]</td>
<?php
						if ($level == 1) {
?>
									<td width="90" valign="top">
										<form action="<?php echo $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']; ?>" method="post" name="field<?php echo $field['FID']; ?>">
											<input name="group" id="group<?php echo $field['FID']; ?>hide" type="hidden" value="<?php echo $groupID; ?>" />
											<input name="field" id="field<?php echo $field['FID']; ?>hide" type="hidden" value="<?php echo $field['FID']; ?>" />
											<input name="level" id="field<?php echo $field['FID']; ?>level" type="hidden" value="<?php echo $level; ?>" />
											<input name="action" id="field<?php echo $field['FID']; ?>edit" type="submit" value="Edit Field" style="width: 90px;" /><br /> 
											<input name="action" id="field<?php echo $field['FID']; ?>up" type="submit" value="Up" style="width: 45px;" 
											<?php if ($fieldRow == 0): ?>disabled="disabled"<?php endif; ?>/><input name="action" id="field<?php echo $field['FID']; ?>down" 
											type="submit" value="Down" style="width: 45px;" <?php if ($fieldRow == $rsFields->RecordCount() - 1): ?>disabled="disabled"<?php endif; ?>/><br />
											<input name="action" id="field<?php echo $field['FID']; ?>del" type="submit" value="Delete Field" style="width: 90px;" onclick="return confirmDel('field');" />
										</form>
									</td>
<?php
						}
?>
								</tr>
<?php
					break;
					case 2: // text field
?>
								<tr id="row_<?php echo $field['GFID']; ?>" valign="top"<?php if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 2): ?> disabled="disabled"<?php endif; if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 1): ?> style="display: none;"<?php endif; ?>>
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?><?php if ($field['Required']): ?> <span style="color: #FF0000;">*</span><?php endif; ?></td>
									<td><input name="<?php echo $field['GFID']; ?>" type="text" id="<?php echo $field['GFID']; ?>" style="width: <?php echo $field['Width']; ?>px;" value="<?php echo $field['DefValue']; ?>" maxlength="<?php echo $field['CharLen']; ?>" <?php if ($level == 0 && in_array($group['GID'] . ":" . $field['FID'], $eventVC)): ?>onchange="vcEvent('<?php echo $field['GFID']; ?>', <?php echo $field['Type']; ?>);" <?php endif; if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 2): ?>disabled="disabled" <?php endif; ?>/></td>
									<td width="50" align="right" valign="top" style="color: #0000FF; white-space: nowrap">[<?php echo $field['GFID2']; ?>]</td>
<?php
						if ($level == 1) {
?>
									<td width="90" valign="top">
										<form action="<?php echo $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']; ?>" method="post" name="field<?php echo $field['FID']; ?>">
											<input name="group" id="group<?php echo $field['FID']; ?>hide" type="hidden" value="<?php echo $groupID; ?>" />
											<input name="field" id="field<?php echo $field['FID']; ?>hide" type="hidden" value="<?php echo $field['FID']; ?>" />
											<input name="level" id="field<?php echo $field['FID']; ?>level" type="hidden" value="<?php echo $level; ?>" />
											<input name="action" id="field<?php echo $field['FID']; ?>edit" type="submit" value="Edit Field" style="width: 90px;" /><br /> 
											<input name="action" id="field<?php echo $field['FID']; ?>up" type="submit" value="Up" style="width: 45px;" 
											<?php if ($fieldRow == 0): ?>disabled="disabled"<?php endif; ?>/><input name="action" id="field<?php echo $field['FID']; ?>down" 
											type="submit" value="Down" style="width: 45px;" <?php if ($fieldRow == $rsFields->RecordCount() - 1): ?>disabled="disabled"<?php endif; ?>/><br />
											<input name="action" id="field<?php echo $field['FID']; ?>del" type="submit" value="Delete Field" style="width: 90px;" onclick="return confirmDel('field');" />
										</form>
									</td>
<?php
						}
?>
								</tr>
<?php
					break;
					case 3: // text area
?>
								<tr id="row_<?php echo $field['GFID']; ?>" valign="top"<?php if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 2): ?> disabled="disabled"<?php endif; if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 1): ?> style="display: none;"<?php endif; ?>>
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?><?php if ($field['Required']): ?> <span style="color: #FF0000;">*</span><?php endif; ?></td>
									<td><textarea name="<?php echo $field['GFID']; ?>" id="<?php echo $field['GFID']; ?>" rows="<?php echo $field['Height']; ?>" style="width: <?php echo $field['Width']; ?>px;"<?php if ($level == 0 && in_array($group['GID'] . ":" . $field['FID'], $eventVC)): ?> onchange="vcEvent('<?php echo $field['GFID']; ?>', <?php echo $field['Type']; ?>);"<?php endif; if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 2): ?>disabled="disabled" <?php endif; ?>><?php echo $field['DefValue']; ?></textarea></td>
									<td width="50" align="right" valign="top" style="color: #0000FF; white-space: nowrap">[<?php echo $field['GFID2']; ?>]</td>
<?php
						if ($level == 1) {
?>
									<td width="90" valign="top">
										<form action="<?php echo $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']; ?>" method="post" name="field<?php echo $field['FID']; ?>">
											<input name="group" id="group<?php echo $field['FID']; ?>hide" type="hidden" value="<?php echo $groupID; ?>" />
											<input name="field" id="field<?php echo $field['FID']; ?>hide" type="hidden" value="<?php echo $field['FID']; ?>" />
											<input name="level" id="field<?php echo $field['FID']; ?>level" type="hidden" value="<?php echo $level; ?>" />
											<input name="action" id="field<?php echo $field['FID']; ?>edit" type="submit" value="Edit Field" style="width: 90px;" /><br /> 
											<input name="action" id="field<?php echo $field['FID']; ?>up" type="submit" value="Up" style="width: 45px;" 
											<?php if ($fieldRow == 0): ?>disabled="disabled"<?php endif; ?>/><input name="action" id="field<?php echo $field['FID']; ?>down" 
											type="submit" value="Down" style="width: 45px;" <?php if ($fieldRow == $rsFields->RecordCount() - 1): ?>disabled="disabled"<?php endif; ?>/><br />
											<input name="action" id="field<?php echo $field['FID']; ?>del" type="submit" value="Delete Field" style="width: 90px;" onclick="return confirmDel('field');" />
										</form>
									</td>
<?php
						}
?>
								</tr>
<?php
					break;
					case 4: // check box
?>
								<tr id="row_<?php echo $field['GFID']; ?>" valign="top"<?php if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 2): ?> disabled="disabled"<?php endif; if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 1): ?> style="display: none;"<?php endif; ?>>
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?><?php if ($field['Required']): ?> <span style="color: #FF0000;">*</span><?php endif; ?></td>
									<td><input name="<?php echo $field['GFID']; ?>" id="<?php echo $field['GFID']; ?>" type="checkbox" value="1" <?php if ($field['DefValue']): ?>checked="checked" <?php endif; ?><?php if ($level == 0 && in_array($group['GID'] . ":" . $field['FID'], $eventVC)): ?>onclick="vcEvent('<?php echo $field['GFID']; ?>', <?php echo $field['Type']; ?>);" <?php endif; if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 2): ?>disabled="disabled" <?php endif; ?>/></td>
									<td width="50" align="right" valign="top" style="color: #0000FF; white-space: nowrap">[<?php echo $field['GFID2']; ?>]</td>
<?php
						if ($level == 1) {
?>
									<td width="90" valign="top">
										<form action="<?php echo $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']; ?>" method="post" name="field<?php echo $field['FID']; ?>">
											<input name="group" id="group<?php echo $field['FID']; ?>hide" type="hidden" value="<?php echo $groupID; ?>" />
											<input name="field" id="field<?php echo $field['FID']; ?>hide" type="hidden" value="<?php echo $field['FID']; ?>" />
											<input name="level" id="field<?php echo $field['FID']; ?>level" type="hidden" value="<?php echo $level; ?>" />
											<input name="action" id="field<?php echo $field['FID']; ?>edit" type="submit" value="Edit Field" style="width: 90px;" /><br /> 
											<input name="action" id="field<?php echo $field['FID']; ?>up" type="submit" value="Up" style="width: 45px;" 
											<?php if ($fieldRow == 0): ?>disabled="disabled"<?php endif; ?>/><input name="action" id="field<?php echo $field['FID']; ?>down" 
											type="submit" value="Down" style="width: 45px;" <?php if ($fieldRow == $rsFields->RecordCount() - 1): ?>disabled="disabled"<?php endif; ?>/><br />
											<input name="action" id="field<?php echo $field['FID']; ?>del" type="submit" value="Delete Field" style="width: 90px;" onclick="return confirmDel('field');" />
										</form>
									</td>
<?php
						}
?>
								</tr>
<?php
					break;
					case 5: // check box group
?>
								<tr id="row_<?php echo $field['GFID']; ?>" valign="top"<?php if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 2): ?> disabled="disabled"<?php endif; if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 1): ?> style="display: none;"<?php endif; ?>>
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?><?php if ($field['Required']): ?> <span style="color: #FF0000;">*</span><?php endif; ?></td>
									<td>
										<table border="0" width="100%">
<?php
						$values = preg_split("/[\r\n]+/", $field['ValueList']);
						if ($field['PriceList'] > "")
							$prices = preg_split("/[\r\n]+/", $field['PriceList']);
						$checked = preg_split("/ /", $field['DefValue']);
						$checknum = 0;
						foreach ($values as $value => $caption) {
?>
											<tr>
												<td><input name="<?php echo $field['GFID'] . "[]"; ?>" id="<?php echo $field['GFID'] . "v" . ($value + 1); ?>" type="checkbox" value="<?php echo $value + 1; ?>" <?php if ($checked[$checknum] == $value + 1): ?>checked="checked" <?php $checknum++; endif; ?><?php if ($level == 0 && in_array($group['GID'] . ":" . $field['FID'], $eventVC)): ?>onclick="vcEvent('<?php echo $field['GFID']; ?>', <?php echo $field['Type']; ?>);" <?php endif; if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 2): ?>disabled="disabled" <?php endif; ?>/><label for="<?php echo $field['GFID'] . "v" . ($value + 1); ?>"><?php echo $caption; ?></label></td>
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
									</td>
									<td width="50" align="right" valign="top" style="color: #0000FF; white-space: nowrap">[<?php echo $field['GFID2']; ?>]</td>
<?php
						if ($level == 1) {
?>
									<td width="90" valign="top">
										<form action="<?php echo $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']; ?>" method="post" name="field<?php echo $field['FID']; ?>">
											<input name="group" id="group<?php echo $field['FID']; ?>hide" type="hidden" value="<?php echo $groupID; ?>" />
											<input name="field" id="field<?php echo $field['FID']; ?>hide" type="hidden" value="<?php echo $field['FID']; ?>" />
											<input name="level" id="field<?php echo $field['FID']; ?>level" type="hidden" value="<?php echo $level; ?>" />
											<input name="action" id="field<?php echo $field['FID']; ?>edit" type="submit" value="Edit Field" style="width: 90px;" /><br /> 
											<input name="action" id="field<?php echo $field['FID']; ?>up" type="submit" value="Up" style="width: 45px;" 
											<?php if ($fieldRow == 0): ?>disabled="disabled"<?php endif; ?>/><input name="action" id="field<?php echo $field['FID']; ?>down" 
											type="submit" value="Down" style="width: 45px;" <?php if ($fieldRow == $rsFields->RecordCount() - 1): ?>disabled="disabled"<?php endif; ?>/><br />
											<input name="action" id="field<?php echo $field['FID']; ?>del" type="submit" value="Delete Field" style="width: 90px;" onclick="return confirmDel('field');" />
										</form>
									</td>
<?php
						}
?>
								</tr>
<?php
					break;
					case 6: // radio button
?>
								<tr id="row_<?php echo $field['GFID']; ?>" valign="top"<?php if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 2): ?> disabled="disabled"<?php endif; if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 1): ?> style="display: none;"<?php endif; ?>>
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?><?php if ($field['Required']): ?> <span style="color: #FF0000;">*</span><?php endif; ?></td>
									<td><input name="<?php echo "g" . $group['GID']; ?>" id="<?php echo $field['GFID']; ?>" type="radio" value="<?php echo "f" . $field['FID']; ?>" <?php if ($field['DefValue']): ?>checked="checked" <?php endif; ?><?php if ($level == 0 && in_array($group['GID'] . ":" . $field['FID'], $eventVC)): ?>onclick="vcEvent('<?php echo $field['GFID']; ?>', <?php echo $field['Type']; ?>);" <?php endif; if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 2): ?>disabled="disabled" <?php endif; ?>/></td>
									<td width="50" align="right" valign="top" style="color: #0000FF; white-space: nowrap">[<?php echo $field['GFID2']; ?>]</td>
<?php
						if ($level == 1) {
?>
									<td width="90" valign="top">
										<form action="<?php echo $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']; ?>" method="post" name="field<?php echo $field['FID']; ?>">
											<input name="group" id="group<?php echo $field['FID']; ?>hide" type="hidden" value="<?php echo $groupID; ?>" />
											<input name="field" id="field<?php echo $field['FID']; ?>hide" type="hidden" value="<?php echo $field['FID']; ?>" />
											<input name="level" id="field<?php echo $field['FID']; ?>level" type="hidden" value="<?php echo $level; ?>" />
											<input name="action" id="field<?php echo $field['FID']; ?>edit" type="submit" value="Edit Field" style="width: 90px;" /><br /> 
											<input name="action" id="field<?php echo $field['FID']; ?>up" type="submit" value="Up" style="width: 45px;" 
											<?php if ($fieldRow == 0): ?>disabled="disabled"<?php endif; ?>/><input name="action" id="field<?php echo $field['FID']; ?>down" 
											type="submit" value="Down" style="width: 45px;" <?php if ($fieldRow == $rsFields->RecordCount() - 1): ?>disabled="disabled"<?php endif; ?>/><br />
											<input name="action" id="field<?php echo $field['FID']; ?>del" type="submit" value="Delete Field" style="width: 90px;" onclick="return confirmDel('field');" />
										</form>
									</td>
<?php
						}
?>
								</tr>
<?php
					break;
					case 7: // radio button group
?>
								<tr id="row_<?php echo $field['GFID']; ?>" valign="top"<?php if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 2): ?> disabled="disabled"<?php endif; if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 1): ?> style="display: none;"<?php endif; ?>>
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?><?php if ($field['Required']): ?> <span style="color: #FF0000;">*</span><?php endif; ?></td>
									<td>
										<table border="0" width="100%">
<?php
						$values = preg_split("/[\r\n]+/", $field['ValueList']);
						if ($field['PriceList'] > "")
							$prices = preg_split("/[\r\n]+/", $field['PriceList']);
						foreach ($values as $value => $caption) {
?>
											<tr>
												<td><input name="<?php echo $field['GFID']; ?>" id="<?php echo $field['GFID'] . "v" . ($value + 1); ?>" type="radio" value="<?php echo $value + 1; ?>" <?php if ($field['DefValue'] == $value + 1): ?>checked="checked" <?php endif; ?><?php if ($level == 0 && in_array($group['GID'] . ":" . $field['FID'], $eventVC)): ?>onclick="vcEvent('<?php echo $field['GFID']; ?>', <?php echo $field['Type']; ?>);" <?php endif; if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 2): ?>disabled="disabled" <?php endif; ?>/><label for="<?php echo $field['GFID'] . "v" . ($value + 1); ?>"><?php echo $caption; ?></label></td>
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
									</td>
									<td width="50" align="right" valign="top" style="color: #0000FF; white-space: nowrap">[<?php echo $field['GFID2']; ?>]</td>
<?php
						if ($level == 1) {
?>
									<td width="90" valign="top">
										<form action="<?php echo $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']; ?>" method="post" name="field<?php echo $field['FID']; ?>">
											<input name="group" id="group<?php echo $field['FID']; ?>hide" type="hidden" value="<?php echo $groupID; ?>" />
											<input name="field" id="field<?php echo $field['FID']; ?>hide" type="hidden" value="<?php echo $field['FID']; ?>" />
											<input name="level" id="field<?php echo $field['FID']; ?>level" type="hidden" value="<?php echo $level; ?>" />
											<input name="action" id="field<?php echo $field['FID']; ?>edit" type="submit" value="Edit Field" style="width: 90px;" /><br /> 
											<input name="action" id="field<?php echo $field['FID']; ?>up" type="submit" value="Up" style="width: 45px;" 
											<?php if ($fieldRow == 0): ?>disabled="disabled"<?php endif; ?>/><input name="action" id="field<?php echo $field['FID']; ?>down" 
											type="submit" value="Down" style="width: 45px;" <?php if ($fieldRow == $rsFields->RecordCount() - 1): ?>disabled="disabled"<?php endif; ?>/><br />
											<input name="action" id="field<?php echo $field['FID']; ?>del" type="submit" value="Delete Field" style="width: 90px;" onclick="return confirmDel('field');" />
										</form>
									</td>
<?php
						}
?>
								</tr>
<?php
					break;
					case 8: // drop-down menu
?>
								<tr id="row_<?php echo $field['GFID']; ?>" valign="top"<?php if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 2): ?> disabled="disabled"<?php endif; if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 1): ?> style="display: none;"<?php endif; ?>>
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?><?php if ($field['Required']): ?> <span style="color: #FF0000;">*</span><?php endif; ?></td>
									<td>
										<select name="<?php echo $field['GFID']; ?>"  id="<?php echo $field['GFID']; ?>" style="width: <?php echo $field['Width']; ?>px;"<?php if ($level == 0 && in_array($group['GID'] . ":" . $field['FID'], $eventVC)): ?> onchange="vcEvent('<?php echo $field['GFID']; ?>', <?php echo $field['Type']; ?>);"<?php endif; if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 2): ?>disabled="disabled" <?php endif; ?>>
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
											<option value="<?php echo $value + 1; ?>"<?php if ($field['DefValue'] == $value + 1): ?> selected="selected"<?php endif; ?>><?php echo $caption; ?></option>
<?php
						}
?>
										</select>
									</td>
									<td width="50" align="right" valign="top" style="color: #0000FF; white-space: nowrap">[<?php echo $field['GFID2']; ?>]</td>
<?php
						if ($level == 1) {
?>
									<td width="90" valign="top">
										<form action="<?php echo $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']; ?>" method="post" name="field<?php echo $field['FID']; ?>">
											<input name="group" id="group<?php echo $field['FID']; ?>hide" type="hidden" value="<?php echo $groupID; ?>" />
											<input name="field" id="field<?php echo $field['FID']; ?>hide" type="hidden" value="<?php echo $field['FID']; ?>" />
											<input name="level" id="field<?php echo $field['FID']; ?>level" type="hidden" value="<?php echo $level; ?>" />
											<input name="action" id="field<?php echo $field['FID']; ?>edit" type="submit" value="Edit Field" style="width: 90px;" /><br /> 
											<input name="action" id="field<?php echo $field['FID']; ?>up" type="submit" value="Up" style="width: 45px;" 
											<?php if ($fieldRow == 0): ?>disabled="disabled"<?php endif; ?>/><input name="action" id="field<?php echo $field['FID']; ?>down" 
											type="submit" value="Down" style="width: 45px;" <?php if ($fieldRow == $rsFields->RecordCount() - 1): ?>disabled="disabled"<?php endif; ?>/><br />
											<input name="action" id="field<?php echo $field['FID']; ?>del" type="submit" value="Delete Field" style="width: 90px;" onclick="return confirmDel('field');" />
										</form>
									</td>
<?php
						}
?>
								</tr>
<?php
					break;
					case 9: // selectable list
?>
								<tr id="row_<?php echo $field['GFID']; ?>" valign="top"<?php if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 2): ?> disabled="disabled"<?php endif; if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 1): ?> style="display: none;"<?php endif; ?>>
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?><?php if ($field['Required']): ?> <span style="color: #FF0000;">*</span><?php endif; ?></td>
									<td>
										<select name="<?php echo $field['GFID'] . "[]"; ?>"  id="<?php echo $field['GFID']; ?>" size="<?php echo $field['Height']; ?>" multiple="multiple" style="width: <?php echo $field['Width']; ?>px;"<?php if ($level == 0 && in_array($group['GID'] . ":" . $field['FID'], $eventVC)): ?> onchange="vcEvent('<?php echo $field['GFID']; ?>', <?php echo $field['Type']; ?>);"<?php endif; if ($level == 0 && !$_GET['showall'] && $field['InitState'] < 2): ?>disabled="disabled" <?php endif; ?>>
<?php
						$values = preg_split("/[\r\n]+/", $field['ValueList']);
						if ($field['PriceList'] > "")
							$prices = preg_split("/[\r\n]+/", $field['PriceList']);
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
									</td>
									<td width="50" align="right" valign="top" style="color: #0000FF; white-space: nowrap">[<?php echo $field['GFID2']; ?>]</td>
<?php
						if ($level == 1) {
?>
									<td width="90" valign="top">
										<form action="<?php echo $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']; ?>" method="post" name="field<?php echo $field['FID']; ?>">
											<input name="group" id="group<?php echo $field['FID']; ?>hide" type="hidden" value="<?php echo $groupID; ?>" />
											<input name="field" id="field<?php echo $field['FID']; ?>hide" type="hidden" value="<?php echo $field['FID']; ?>" />
											<input name="level" id="field<?php echo $field['FID']; ?>level" type="hidden" value="<?php echo $level; ?>" />
											<input name="action" id="field<?php echo $field['FID']; ?>edit" type="submit" value="Edit Field" style="width: 90px;" /><br /> 
											<input name="action" id="field<?php echo $field['FID']; ?>up" type="submit" value="Up" style="width: 45px;" 
											<?php if ($fieldRow == 0): ?>disabled="disabled"<?php endif; ?>/><input name="action" id="field<?php echo $field['FID']; ?>down" 
											type="submit" value="Down" style="width: 45px;" <?php if ($fieldRow == $rsFields->RecordCount() - 1): ?>disabled="disabled"<?php endif; ?>/><br />
											<input name="action" id="field<?php echo $field['FID']; ?>del" type="submit" value="Delete Field" style="width: 90px;" onclick="return confirmDel('field');" />
										</form>
									</td>
<?php
						}
?>
								</tr>
<?php
					break;
				}
			}
			if ($level == 1) {
?>
								<tr valign="top">
									<td></td>
									<td></td>
									<td width="50" align="right"></td>
									<td width="90" valign="top">
										<form action="<?php echo $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']; ?>" method="post" name="field<?php echo $field['FID']; ?>">
											<input name="group" id="group<?php echo $rsFields->RecordCount() + 1; ?>hide" type="hidden" value="<?php echo $groupID; ?>" />
											<input name="field" id="field<?php echo $rsFields->RecordCount() + 1; ?>hide" type="hidden" value="<?php echo $rsFields->RecordCount() + 1; ?>" />
											<input name="level" id="field<?php echo $rsFields->RecordCount() + 1; ?>level" type="hidden" value="<?php echo $level; ?>" />
											<input name="action" id="field<?php echo $rsFields->RecordCount() + 1; ?>add" type="submit" value="Add Field" style="width: 90px;" />
										</form>
									</td>
								</tr>
<?php
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
			if ($group['Box'] && $level == 0) {
?>
						</fieldset>
<?php
			}
?>
				</td>
<?php
			if ($level == 0) {
?>
					<td width="90" valign="top" id="btns_<?php echo $group['GFID']; ?>">
						<form action="<?php echo $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']; ?>" method="post" name="group<?php echo $group['GID']; ?>">
							<input name="group" id="group<?php echo $group['GID']; ?>hide" type="hidden" value="<?php echo $group['GID']; ?>" />
							<input name="level" id="field<?php echo $group['GID']; ?>level" type="hidden" value="<?php echo $level; ?>" />
							<input name="action" id="group<?php echo $group['GID']; ?>edit" type="submit" value="Edit Group" style="width: 90px;" /><br />
							<input name="action" id="group<?php echo $group['GID']; ?>up" type="submit" value="Up" style="width: 45px;" 
							<?php if ($groupRow == 0): ?>disabled="disabled"<?php endif; ?>/><input name="action" id="group<?php echo $group['GID']; ?>down" 
							type="submit" value="Down" style="width: 45px;" <?php if ($groupRow == $rsGroups->RecordCount() - 1): ?>disabled="disabled"<?php endif; ?>/><br />
							<input name="action" id="group<?php echo $group['GID']; ?>del" type="submit" value="Delete Group" style="width: 90px;" onclick="return confirmDel('group');" />
							<br />
						</form>
					</td>
<?php
			}
?>
				</tr>
<?php
		}
		if ($level == 0) {
?>
				<tr>
					<td>
						<input name="submit" type="button" id="submit" value="Submit" style="width: 100px;" disabled="disabled" />
						<input name="clear" type="button" id="clear" value="Clear" style="width: 100px;" disabled="disabled" /><br />
						(buttons will be enabled on real form)
					</td>
					<td width="90" valign="top">
						<form action="<?php echo $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']; ?>" method="post" name="add">
							<input name="group" id="group<?php echo $rsGroups->RecordCount() + 1; ?>hide" type="hidden" value="<?php echo $rsGroups->RecordCount() + 1; ?>" />
							<input name="level" id="group<?php echo $rsGroups->RecordCount() + 1; ?>level" type="hidden" value="<?php echo $level; ?>" />
							<input name="action" id="group<?php echo $rsGroups->RecordCount() + 1; ?>add" type="submit" value="Add Group" style="width: 90px;" />
						</form>
					</td>
				</tr>
<?php
		}
?>
			</table>
		</td>
	</tr>
	<tr align="center">
		<td style="white-space: nowrap">
			<strong>&#151; &#150; &#151; &#150; &#151; &nbsp;
			<?php echo ($level == 0 ? "REGISTRATION FORM" : "CURRENT " . strtoupper($levelType[$level])); ?> ENDS HERE
			&nbsp; &#151; &#150; &#151; &#150; &#151;
		</strong></td>
	</tr>
<?php
	}
?>
</table>
<?php
}
do_html_footer();
?>

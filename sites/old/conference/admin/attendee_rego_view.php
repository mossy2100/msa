<?php
$php_root_path = "..";
$privilege_root_path = "/admin";
require_once("includes/include_all_fns.inc");
session_start();
if (!$db = adodb_connect()) {
	do_html_header("Attendee Registration List &#151; Error");
	do_html_footer("Error connecting to database.");
	exit;
}

require_once("includes/rego_form_functions.php");

// retrieve conference settings
$settings = get_Conference_Settings();
// if registration form number is not in $_GET or it isn't a valid format (length or check digit), return to list
if (!isset($_GET['view']) || strlen($_GET['view']) != 7 || !rfValCheckDigit($_GET['view'])) {
	header("Location: " . $_SERVER['PHP_SELF'] . queryString());
	exit;
}
do_html_header("View Attendee Registration Form");

// queries to select groups and fields
$rsGroups = $db->Execute("SELECT GID, CONCAT('g', GID, 'f0') AS GFID, Title, Box, InitState, CaptWidth, DATE_FORMAT(UID, '%Y%m%d%H%i%s') AS UID " .
                         "FROM " . TBL_GROUPS . " " .
                         "ORDER BY GID");
$prepFields = $db->Prepare("SELECT GID, FID, CONCAT('g', GID, 'f', FID) AS GFID, Type, Caption, InitState, ValueList, PriceList, ValFunction, Width, " .
                           "DATE_FORMAT(UID, '%Y%m%d%H%i%s') AS UID " .
                           "FROM " . TBL_FIELDS . " " .
                           "WHERE GID = ? " .
                           "ORDER BY GID, FID");

// get list of all UIDs
$uids = array();
$rsUID = $db->Execute("SELECT CONCAT('g', GID, 'f', FID) AS GFID, DATE_FORMAT(UID, '%Y%m%d%H%i%s') AS UID " .
                      "FROM " . TBL_FIELDS . " " .
                      "WHERE Type > 1");
while ($uid = $rsUID->FetchRow())
	$uids[$uid['GFID']] = $uid['UID'];

// retrieve all details for registration
$rsRego = $db->Execute("SELECT RegoID, DATE_FORMAT(RegoTime, '%a, %d %b %Y, %T') AS RegoTime, PriceTotal, PricePaid, ((PriceTotal - PricePaid) = 0) AS PaidFull, " .
                       "`" . implode("`, `", $uids) . "` " .
                       "FROM " . TBL_DETAILS . " " .
                       "WHERE RegoID = '" . $_GET['view'] . "'");
$rego = $rsRego->FetchRow();

// select all visibility conditions (to calculate which fields don't need to be shown)
$rsVisCond = $db->Execute("SELECT GID, 0 AS FID, CONCAT('g', GID, 'f0') AS GFID, VisCond " .
                          "FROM " . TBL_GROUPS . " " .
                          "WHERE VisCond <> '' " .
                          "UNION " .
                          "SELECT GID, FID, CONCAT('g', GID, 'f', FID) AS GFID, VisCond " .
                          "FROM " . TBL_FIELDS . " " .
                          "WHERE VisCond <> ''");
// some initial states may vary from database based on submitted data
$newInitState = array();
while ($visCond = $rsVisCond->FetchRow()) {
	if (!$vc = vcParse($visCond['VisCond']))
		continue; // go to next iteration if invalid visibility condition
	$newInitState[$visCond['GFID']] = ((vcCompareValue($rego[$uids[$vc['gfid']]], $vc['value']) xor $vc['negate']) ? 2 : 0);
}

?>
<table width="100%"  border="0" align="center" cellspacing="20" id="outerform">
	<tr>
		<td>
			<table border="0" align="center" cellspacing="10" id="regoform">
				<tr>
					<td>
						<table width="100%">
							<tr>
								<td width="150"><strong>Reference Number:</strong></td>
								<td><?php echo $rego['RegoID']; ?></td>
							</tr>
							<tr>
								<td width="150"><strong>Registration Date:</strong></td>
								<td><?php echo $rego['RegoTime']; ?></td>
							</tr>
							<tr>
								<td width="150"><strong>Total Price:</strong></td>
								<td><?php echo printCurr($rego['PriceTotal'], $settings); ?></td>
							</tr>
							<tr>
								<td width="150"><strong>Amount Paid:</strong></td>
								<td><?php echo printCurr($rego['PricePaid'], $settings); ?></td>
							</tr>
							<tr>
								<td width="150"><strong>Paid in Full:</strong></td>
								<td><img src="/../images/<?php echo ($rego['PaidFull'] ?"tick" : "cross"); ?>.gif" alt="" width="11" height="11" /></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td height="20"></td>
				</tr>
<?php
	// iterate groups
	$groupRow = -1;
	while ($group = $rsGroups->FetchRow()) {
		$groupRow++;
		// replace InitState from database with one generated from saved data and visibility conditions
		if (array_key_exists($group['GFID'], $newInitState))
			$group['InitState'] = $newInitState[$group['GFID']];
		// hidden or disabled groups aren't even sent to browser
		if ($group['InitState'] < 2)
			continue;
?>
				<tr id="row_<?php echo $group['GFID']; ?>">
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
			// replace InitState from database with one generated from saved data and visibility conditions
			if (array_key_exists($field['GFID'], $newInitState))
				$field['InitState'] = $newInitState[$field['GFID']];
			// hidden or disabled fields aren't even sent to browser
			if ($field['InitState'] < 2)
				continue;
			switch ($field['Type']) {
				case 1: // plain text
?>
								<tr valign="top">
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?></td>
									<td><span style="width: <?php echo $field['Width']; ?>px"><?php echo nl2br($field['ValueList']); ?></span></td>
								</tr>
<?php
				break;
				case 2: // text field
?>
								<tr valign="top">
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?></td>
									<td><?php echo $rego[$field['UID']]; ?><?php if ($field['ValFunction'] == 31): ?> (<?php echo ccCalculateType($rego[$field['UID']]); ?>)<?php endif; ?></td>
								</tr>
<?php
				break;
				case 3: // text area
?>
								<tr valign="top">
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?></td>
									<td><span style="width: <?php echo $field['Width']; ?>px"><?php echo nl2br($rego[$field['UID']]); ?></span></td>
								</tr>
<?php
				break;
				case 4: // check box
?>
								<tr valign="top">
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?></td>
									<td><img src="/../images/<?php echo ($rego[$field['UID']] ?"tick" : "cross"); ?>.gif" alt="" width="11" height="11" hspace="5" /></td>
								</tr>
<?php
				break;
				case 5: // check box group
?>
								<tr valign="top">
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?></td>
									<td>
										<table border="0" width="100%">
<?php
					$values = preg_split("/[\r\n]+/", $field['ValueList']);
					if ($field['PriceList'] > "")
						$prices = preg_split("/[\r\n]+/", $field['PriceList']);
					$checked = explode(" ", $rego[$field['UID']]);
					$checknum = 0;
					foreach ($values as $value => $caption) {
						if ($checked[$checknum] == $value + 1) {
?>
											<tr>
												<td><?php echo $caption; ?></td>
<?php
							if ($field['PriceList'] > "") {
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
									</td>
								</tr>
<?php
				break;
				case 6: // radio button
?>
								<tr valign="top">
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?></td>
									<td><img src="/../images/<?php echo ($rego[$field['UID']] ?"tick" : "cross"); ?>.gif" alt="" width="11" height="11" hspace="5" /></td>
								</tr>
<?php
				break;
				case 7: // radio button group
?>
								<tr valign="top">
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?></td>
									<td>
										<table border="0" width="100%">
<?php
					$values = preg_split("/[\r\n]+/", $field['ValueList']);
					if ($field['PriceList'] > "")
						$prices = preg_split("/[\r\n]+/", $field['PriceList']);
					foreach ($values as $value => $caption) {
						if ($rego[$field['UID']] == $value + 1) {
?>
											<tr>
												<td><?php echo $caption; ?></td>
<?php
							if ($field['PriceList'] > "") {
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
									</td>
								</tr>
<?php
				break;
				case 8: // drop-down menu
?>
								<tr valign="top">
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?></td>
									<td>
										<table border="0" width="100%">
<?php
					$values = preg_split("/[\r\n]+/", $field['ValueList']);
					if ($field['PriceList'] > "")
						$prices = preg_split("/[\r\n]+/", $field['PriceList']);
					foreach ($values as $value => $caption) {
						if ($rego[$field['UID']] == $value + 1) {
?>
											<tr>
												<td><?php echo $caption; ?></td>
<?php
							if ($field['PriceList'] > "") {
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
									</td>
								</tr>
<?php
				break;
				case 9: // selectable list
?>
								<tr valign="top">
									<td width="<?php echo $group['CaptWidth']; ?>"><?php echo $field['Caption']; ?></td>
									<td>
										<table border="0" width="100%">
<?php
					$values = preg_split("/[\r\n]+/", $field['ValueList']);
					if ($field['PriceList'] > "")
						$prices = preg_split("/[\r\n]+/", $field['PriceList']);
					$checked = explode(" ", $rego[$field['UID']]);
					$checknum = 0;
					foreach ($values as $value => $caption) {
						if ($checked[$checknum] == $value + 1) {
?>
											<tr>
												<td><?php echo $caption; ?></td>
<?php
							if ($field['PriceList'] > "") {
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
?>
				<tr>
					<td height="10"></td>
				</tr>
				<tr>
					<td><input name="back" type="button" id="back" value="Back to Registration List" style="width: 175px;" onclick="location.href = 'attendee_rego_list.php<?php echo queryString("view", NULL); ?>'" /></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<?php
do_html_footer();
?>

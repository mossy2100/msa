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
// look for cancel or back before sending out headers
if (isset($_POST['cancel']) || isset($_POST['back'])) {
	header("Location: " . $_SERVER['PHP_SELF'] . queryString());
	exit;
}
do_html_header("Attendee Registration List");

$payShow = 0;
// check $_POST for payments to process
if (isset($_POST['process'])) {
	if (isset($_POST['payrefs'])) { // list payments
		$payRefs = explode(" ", $_POST['payrefs']);
		// retrieve list of registrations to pay from database
		$rsPay = $db->Execute("SELECT AutoID, RegoID, DATE_FORMAT(RegoTime, '%a, %d %b %Y, %T') AS RegoTime, " .
		                      "PriceTotal, PricePaid, (PriceTotal - PricePaid) AS PriceRemain, ((PriceTotal - PricePaid) = 0) AS PaidFull " .
		                      "FROM " . TBL_DETAILS . " " .
		                      "WHERE RegoID IN ('" . implode("', '", $payRefs) . "')");
		$payShow = 1;
	} elseif (isset($_POST['payamount'])) { // save payments
		$payAmount = $_POST['payamount'];
		// check amounts are numeric and not greater than outstanding amount
		$payAmountRefs = array_keys($payAmount);
		$rsPay = $db->Execute("SELECT RegoID, (PriceTotal - PricePaid) AS PriceRemain " .
		                      "FROM " . TBL_DETAILS . " " .
		                      "WHERE RegoID IN ('" . implode("', '", $payAmountRefs) . "')");
		while ($pay = $rsPay->FetchRow())
			$payPriceRemain[$pay['RegoID']] = $pay['PriceRemain'];
		// if amount is not numeric or greater than remaining, delete from array
		foreach ($payAmount as $ref => $amount)
			if (!is_numeric($amount) || $amount > $payPriceRemain[$ref] || $amount <= 0)
				unset($payAmount[$ref]);
		// update in database
		$prepPayAmount = $db->Prepare("UPDATE " . TBL_DETAILS . " " .
		                              "SET PricePaid = PricePaid + ? " .
		                              "WHERE RegoID = '?'");
		foreach ($payAmount as $ref => $amount)
			$db->Execute($prepPayAmount, array($amount, $ref));
		// retrieve list of registrations paid from database
		$payAmountRefs = array_keys($payAmount);
		$rsPay = $db->Execute("SELECT AutoID, RegoID, DATE_FORMAT(RegoTime, '%a, %d %b %Y, %T') AS RegoTime, " .
		                      "PriceTotal, PricePaid, (PriceTotal - PricePaid) AS PriceRemain, ((PriceTotal - PricePaid) = 0) AS PaidFull " .
		                      "FROM " . TBL_DETAILS . " " .
		                      "WHERE RegoID IN ('" . implode("', '", $payAmountRefs) . "')");
		$payShow = 2;
	}
// otherwise show list
} else {
	// number of records in registrations table (offset can't be higher than this)
	$rsNumRecs = $db->Execute("SELECT COUNT(*) AS NumRecs " .
	                          "FROM " . TBL_DETAILS);
	$numRecs = $rsNumRecs->FetchRow();
	$numRecs = $numRecs['NumRecs'];
	
	// check $_GET for sorting/limit parameters for list (and prevent SQL injection by checking strings)
	if (isset($_GET['scol'])) {
		switch ($_GET['scol']) {
			case "refno":
				// use AutoID not RegoID because the first is numeric and faster (both have same order)
				$listOrder = "AutoID";
				break;
			case "time":
				$listOrder = "RegoTime";
				break;
			case "total":
				$listOrder = "PriceTotal";
				break;
			case "paid":
				$listOrder = "PricePaid";
				break;
			case "full":
				$listOrder = "PaidFull";
				break;
			default:
				$listOrder = "AutoID";
				break;
		}
		if (isset($_GET['sdir'])) {
			switch ($_GET['sdir']) {
				case "asc":
					$listOrder .= " ASC";
					break;
				case "desc":
					$listOrder .= " DESC";
					break;
			}
		}
	} else
		$listOrder = "AutoID";
	$listRows = (isset($_GET['show']) && is_numeric($_GET['show']) ? $_GET['show'] : 25);
	$listOffset = (isset($_GET['start']) && is_numeric($_GET['start'])? ($_GET['start'] > $numRecs ? $numRecs - $listRows : $_GET['start'] - 1) : 0);
	
	// retrieve list of registrations from database
	$rsList = $db->SelectLimit("SELECT AutoID, RegoID, DATE_FORMAT(RegoTime, '%a, %d %b %Y, %T') AS RegoTime, " .
	                           "PriceTotal, PricePaid, (PriceTotal - PricePaid) AS PriceRemain, ((PriceTotal - PricePaid) = 0) AS PaidFull " .
	                           "FROM " . TBL_DETAILS . " " .
	                           "ORDER BY " . $listOrder,
	                           $listRows, $listOffset);
	
	// calculate values for page navigation
	$firstRec = ($listOffset < 1 ? 1 : $listOffset + 1);
	$lastRec = $firstRec + $rsList->RecordCount() - 1;
	$firstPage = ($firstRec > 1 ? 0 : false);
	$prevPage = ($firstRec > 1 ? $firstRec - $listRows : false);
	$nextPage = ($lastRec < $numRecs ? $firstRec + $listRows : false);
	$lastPage = ($lastRec < $numRecs ? ((($numRecs - ($numRecs % $listRows)) / $listRows) * $listRows) + 1 : false);
	
	// options for number of records to display per page
	$showNumbers = array(10, 25, 50, 100);
}

?>
<style>
<!--

.listheader {
	background-color: #CCCCCC;
}

.list0 {
	background-color: #E0E0E0;
}

.list1 {
	background-color: #EFEFEF;
}

-->
</style>
<script type="text/javascript" src="/conference/admin/script/rego_form_functions.js"></script>
<?php
if (!$payShow) {
?>
<table border="0" align="center" cellspacing="2" cellpadding="2" id="header">
	<tr>
		<td align="center">Jump to: <strong>[ <a href="#aregolist">All Registrations</a> | <a href="#apayments">Payments</a> ]</strong></td>
	</tr>
	<tr>
		<td height="40"><a name="aregolist" id="aregolist"></a></td>
	</tr>
	<tr>
		<td align="center"><h3>All Registrations</h3></td>
	</tr>
</table>
<table border="0" align="center" cellspacing="2" cellpadding="2" id="top">
	<tr>
		<td width="200" align="left">show 
			<select name="showtop" onchange="rlJumpMenu(this)">
<?php
	foreach ($showNumbers as $num) {
?>
				<option value="<?php echo $_SERVER['PHP_SELF'] . queryString("show", $num); ?>"<?php if ($listRows == $num): ?> selected="selected"<?php endif; ?>><?php echo $num; ?></option>
<?php
	}
?>
			</select> 
		records per page</td>
		<td width="95" align="right">
			<input name="navtopbtn1" type="submit" id="navtopbtn1" value="&lt;&lt;" style="width: 40px;" <?php if ($firstPage === false): ?>disabled="disabled"<?php else: ?>onclick="location.href='<?php echo $_SERVER['PHP_SELF'] . queryString("start", $firstPage); ?>'"<?php endif; ?> />
		<input name="navtopbtn2" type="submit" id="navtopbtn2" value="&lt;" style="width: 40px;" <?php if ($prevPage === false): ?>disabled="disabled"<?php else: ?>onclick="location.href='<?php echo $_SERVER['PHP_SELF'] . queryString("start", $prevPage); ?>'"<?php endif; ?> />		</td>
		<td width="10"></td>
		<td width="95" align="left">
			<input name="navtopbtn3" type="submit" id="navtopbtn3" value="&gt;" style="width: 40px;" <?php if ($nextPage === false): ?>disabled="disabled"<?php else: ?>onclick="location.href='<?php echo $_SERVER['PHP_SELF'] . queryString("start", $nextPage); ?>'"<?php endif; ?> />
		<input name="navtopbtn4" type="submit" id="navtopbtn4" value="&gt;&gt;" style="width: 40px;" <?php if ($lastPage === false): ?>disabled="disabled"<?php else: ?>onclick="location.href='<?php echo $_SERVER['PHP_SELF'] . queryString("start", $lastPage); ?>'"<?php endif; ?> />		</td>
		<td width="200" align="right"><?php if ($rsList->RecordCount() > 0): ?>showing records <?php echo $firstRec; ?> to <?php echo $lastRec; ?> of <?php echo $numRecs; ?><?php endif; ?></td>
	</tr>
	<tr>
		<td height="15" colspan="5"></td>
	</tr>
</table>
<table border="0" align="center" cellpadding="2" cellspacing="2" id="list">
	<tr class="listheader">
		<td width="55" align="center"></td>
		<td width="85" height="50" align="center"><strong>Reference<br /> 
		Number</strong></td>
		<td width="18" align="center"><a href="<?php echo $_SERVER['PHP_SELF'] . queryString("scol", "refno", "sdir", "asc"); ?>"><img src="/conference/admin/../images/sortup.gif" alt="Sort by Reference Number in Ascending Order" width="18" height="11" border="0" title="Sort by Reference Number in Ascending Order" /></a><br />
		<img src="/conference/admin/../images/sort.gif" alt="" width="18" height="15" /><br />
		<a href="<?php echo $_SERVER['PHP_SELF'] . queryString("scol", "refno", "sdir", "desc"); ?>"><img src="/conference/admin/../images/sortdown.gif" alt="Sort by Reference Number in Descending Order" width="18" height="11" border="0" title="Sort by Reference Number in Descending Order" /></a></td>
		<td width="160" height="50" align="center"><strong>Date/Time<br /> 
		of Registration </strong></td>
		<td width="18" align="center"><a href="<?php echo $_SERVER['PHP_SELF'] . queryString("scol", "time", "sdir", "asc"); ?>"><img src="/conference/admin/../images/sortup.gif" alt="Sort by Date/Time in Ascending Order" width="18" height="11" border="0" title="Sort by Date/Time in Ascending Order" /></a><br />
		<img src="/conference/admin/../images/sort.gif" alt="" width="18" height="15" /><br />
		<a href="<?php echo $_SERVER['PHP_SELF'] . queryString("scol", "time", "sdir", "desc"); ?>"><img src="/conference/admin/../images/sortdown.gif" alt="Sort by Date/Time in Descending Order" width="18" height="11" border="0" title="Sort by Date/Time in Descending Order" /></a></td>
		<td width="65" height="50" align="center"><strong>Total<br /> 
		Price</strong></td>
		<td width="18" align="center"><a href="<?php echo $_SERVER['PHP_SELF'] . queryString("scol", "total", "sdir", "asc"); ?>"><img src="/conference/admin/../images/sortup.gif" alt="Sort by Total Price in Ascending Order" width="18" height="11" border="0" title="Sort by Total Price Number in Ascending Order" /></a><br />
		<img src="/conference/admin/../images/sort.gif" alt="" width="18" height="15" /><br />
		<a href="<?php echo $_SERVER['PHP_SELF'] . queryString("scol", "total", "sdir", "desc"); ?>"><img src="/conference/admin/../images/sortdown.gif" alt="Sort by Total Price in Descending Order" width="18" height="11" border="0" title="Sort by Total Price in Descending Order" /></a></td>
		<td width="65" height="50" align="center"><strong>Amount<br /> 
		Paid</strong></td>
		<td width="18" align="center"><a href="<?php echo $_SERVER['PHP_SELF'] . queryString("scol", "paid", "sdir", "asc"); ?>"><img src="/conference/admin/../images/sortup.gif" alt="Sort by Amount Paid in Ascending Order" width="18" height="11" border="0" title="Sort by Amount Paid in Ascending Order" /></a><br />
		<img src="/conference/admin/../images/sort.gif" alt="" width="18" height="15" /><br />
		<a href="<?php echo $_SERVER['PHP_SELF'] . queryString("scol", "paid", "sdir", "desc"); ?>"><img src="/conference/admin/../images/sortdown.gif" alt="Sort by Amount Paid in Descending Order" width="18" height="11" border="0" title="Sort by Amount Paid in Descending Order" /></a></td>
		<td width="40" height="50" align="center"><strong>All<br /> 
		Paid</strong></td>
		<td width="18" align="center"><a href="<?php echo $_SERVER['PHP_SELF'] . queryString("scol", "full", "sdir", "asc"); ?>"><img src="/conference/admin/../images/sortup.gif" alt="Sort by All Paid in Ascending Order (unpaid first)" width="18" height="11" border="0" title="Sort by All Paid in Ascending Order (unpaid first)" /></a><br />
		<img src="/conference/admin/../images/sort.gif" alt="" width="18" height="15" /><br />
		<a href="<?php echo $_SERVER['PHP_SELF'] . queryString("scol", "full", "sdir", "desc"); ?>"><img src="/conference/admin/../images/sortdown.gif" alt="Sort by All Paid in Descending Order (paid first)" width="18" height="11" border="0" title="Sort by All Paid in Descending Order (paid first)" /></a></td>
		<td width="50" align="center"></td>
	</tr>
<?php
	$rowNum = 0;
	while ($list = $rsList->FetchRow()) {
		$rowNum ++;
?>
	<tr class="list<?php echo ($rowNum % 2); ?>">
		<td align="center"><input name="view" type="button" id="view<?php echo $rowNum; ?>" value="View" style="width: 45px;" onclick="location.href = 'attendee_rego_view.php<?php echo queryString("view", $list['RegoID']); ?>'" /></td>
		<td colspan="2" align="center"><strong><?php echo $list['RegoID']; ?></strong></td>
		<td colspan="2"><?php echo $list['RegoTime']; ?></td>
		<td width="75" colspan="2" align="right"><?php echo printCurr($list['PriceTotal'], $settings); ?></td>
		<td width="75" colspan="2" align="right"><?php echo printCurr($list['PricePaid'], $settings); ?></td>
		<td colspan="2" align="center"><img src="<?php echo $php_root_path; ?>/images/<?php echo ($list['PaidFull'] ? "tick" : "cross"); ?>.gif" width="11" height="11" alt="" /></td>
		<td align="center"><input name="pay" type="button" id="pay<?php echo $rowNum; ?>" value="Pay" style="width: 40px;" onclick="rlAddPayment('<?php echo $list['RegoID']; ?>')" /></td>
	</tr>
<?php
	}
	if ($rsList->RecordCount() == 0) {
?>
	<tr>
		<td colspan="12" align="center">no attendees have registered yet </td>
	</tr>
<?php
	}
?>
</table>
<table border="0" align="center" cellspacing="2" cellpadding="2" id="bottom">
	<tr>
		<td height="15" colspan="5"></td>
	</tr>
	<tr>
		<td width="200" align="left">show
			<select name="showbottom" onchange="rlJumpMenu(this)">
<?php
	foreach ($showNumbers as $num) {
?>
				<option value="<?php echo $_SERVER['PHP_SELF'] . queryString("show", $num); ?>"<?php if ($listRows == $num): ?> selected="selected"<?php endif; ?>><?php echo $num; ?></option>
<?php
	}
?>
			</select> 
		records per page</td>
		<td width="95" align="right">
			<input name="navtopbtn1" type="submit" id="navtopbtn1" value="&lt;&lt;" style="width: 40px;" <?php if ($firstPage === false): ?>disabled="disabled"<?php else: ?>onclick="location.href='<?php echo $_SERVER['PHP_SELF'] . queryString("start", $firstPage); ?>'"<?php endif; ?> />
		<input name="navtopbtn2" type="submit" id="navtopbtn2" value="&lt;" style="width: 40px;" <?php if ($prevPage === false): ?>disabled="disabled"<?php else: ?>onclick="location.href='<?php echo $_SERVER['PHP_SELF'] . queryString("start", $prevPage); ?>'"<?php endif; ?> />		</td>
		<td width="10"></td>
		<td width="95" align="left">
			<input name="navtopbtn3" type="submit" id="navtopbtn3" value="&gt;" style="width: 40px;" <?php if ($nextPage === false): ?>disabled="disabled"<?php else: ?>onclick="location.href='<?php echo $_SERVER['PHP_SELF'] . queryString("start", $nextPage); ?>'"<?php endif; ?> />
		<input name="navtopbtn4" type="submit" id="navtopbtn4" value="&gt;&gt;" style="width: 40px;" <?php if ($lastPage === false): ?>disabled="disabled"<?php else: ?>onclick="location.href='<?php echo $_SERVER['PHP_SELF'] . queryString("start", $lastPage); ?>'"<?php endif; ?> />		</td>
		<td width="200" align="right"><?php if ($rsList->RecordCount() > 0): ?>showing records <?php echo $firstRec; ?> to <?php echo $lastRec; ?> of <?php echo $numRecs; ?><?php endif; ?></td>
	</tr>
</table>
<table border="0" align="center" id="paymenttext">
	<tr>
		<td colspan="3" height="40"><a name="apayments" id="apayments"></a></td>
	</tr>
	<tr>
		<td colspan="3" align="center"><h3>Payments</h3></td>
	</tr>
	<tr>
		<td colspan="3" width="550">To process payments, either press the <em>Pay</em> buttons
			on the right of the table
			above for the reference numbers you want to process; or type the
			reference numbers (one at a time) into the text box below (left)
			and press Enter; or do a combination of both. As you click or type them,
			they will appear in the list below (right).<br />
			<br />
			When you click the <em>Process Payments</em> button, you will be shown a list
			(similar to above) containing all the records you selected. You will
			be shown the total price and amount already paid, and you will be
		able to specify the amount to pay (if not the full amount).</td>
	</tr>
	<tr>
		<td colspan="3" height="20"></td>
	</tr>
	<tr valign="top">
		<td width="275" align="center">
			<fieldset>
				<legend>Enter Reference Number&nbsp;</legend>
				<form name="refform" id="refform" onsubmit="rlAddPayment(this.ref.value); this.ref.value = ''; this.ref.focus(); return false;">
					<input name="ref" type="text" id="ref" maxlength="7" style="width: 150px;" />
					<input name="add" type="submit" id="add" value="Add" style="width: 100px;" />
				</form>
			</fieldset>
		</td>
		<td width="50"></td>
		<td width="225" align="center">
			<fieldset>
				<legend>Payments to Process&nbsp;</legend>
				<form name="paymentform" id="paymentform" action="<?php echo $_SERVER['PHP_SELF'] . queryString(); ?>" method="post" onsubmit="rlGetPayments()">
					<select name="paymentlist" size="10" id="paymentlist" style="width: 200px;" onchange="document.getElementById('delete').disabled = (this.selectedIndex == -1);">
					</select><br />
					<input name="payrefs" type="hidden" id="payrefs" />
					<input name="process" type="submit" id="process" value="Process" style="width: 125px;" disabled="disabled" /><input name="delete" type="button" id="delete" value="Delete" style="width: 75px;" onclick="rlRemPayment()" disabled="disabled" /><br />
					<br />
					To delete a row, select it<br />
					and press <em>Delete</em>.
				</form>
			</fieldset>
		</td>
	</tr>
</table>
<?php
} else {
?>
<form name="paymentform" id="paymentform" action="<?php echo $_SERVER['PHP_SELF'] . queryString(); ?>" method="post">
	<table border="0" align="center" cellpadding="2" cellspacing="2" id="paymentheader">
		<tr>
<?php
	if ($payShow == 1) {
?>
			<td width="550" align="center">Below is a list of the reference numbers you
				selected for payment. Please confirm that all payments are for
				the full amount. If the payment is not for the full amount, adjust
				the <em>Amount to Pay</em> column accordingly. You will be warned if you try
				to enter an amount greater than the amount outstanding.</td>
<?php
	} else {
?>
			<td width="500" align="center">The following payments have been processed.
				The <em>Amount Paid</em> is the total amount paid, not just the amount paid
				in this transaction. Any payments with invalid amounts on the last screen
				have not been processed and are not in this list.<br />
				<br />
				<em>Do not refresh this screen as it may cause payments to be applied again.</em></td>
			<?php
	}
?>
		</tr>
		<tr>
			<td height="20"></td>
		</tr>
	</table>
	<table border="0" align="center" cellpadding="2" cellspacing="2" id="payment">
		<tr class="listheader">
			<td width="105" height="50" align="center"><strong>Reference<br /> 
			Number</strong></td>
			<td width="180" height="50" align="center"><strong>Date/Time<br /> 
			of Registration </strong></td>
			<td width="85" height="50" align="center"><strong>Total<br /> 
			Price</strong></td>
			<td width="85" height="50" align="center"><strong>Amount<br /> 
			Paid</strong></td>
			<td width="40" height="50" align="center"><strong>All<br /> 
			Paid</strong></td>
<?php
	if ($payShow == 1) {
?>
			<td width="125" height="50" align="center"><strong>Amount to Pay</strong></td>
<?php
	}
?>
		</tr>
<?php
	$rowNum = 0;
	while ($pay = $rsPay->FetchRow()) {
		$rowNum ++;
?>
		<tr class="list<?php echo ($rowNum % 2); ?>">
			<td align="center"><strong><?php echo $pay['RegoID']; ?></strong></td>
			<td><?php echo $pay['RegoTime']; ?></td>
			<td width="75" align="right"><?php echo printCurr($pay['PriceTotal'], $settings); ?></td>
			<td width="75" align="right"><?php echo printCurr($pay['PricePaid'], $settings); ?></td>
			<td align="center"><img src="<?php echo $php_root_path; ?>/images/<?php echo ($pay['PaidFull'] ? "tick" : "cross"); ?>.gif" width="11" height="11" alt="" /></td>
<?php
	if ($payShow == 1) {
?>
			<td width="115" align="right" style="white-space: nowrap;"><?php if ($pay['PaidFull']): ?><?php echo printCurr($pay['PriceRemain'], $settings); ?><?php else: ?><?php echo $settings->CurrencyPrefix; ?><input name="payamount[<?php echo $pay['RegoID']; ?>]" type="text" id="pay<?php echo $rowNum; ?>" style="width: 75px; text-align: right;" value="<?php echo printCurr($pay['PriceRemain'], $settings, false); ?>" onchange="return rlMaxAmount(this, '<?php echo printCurr($pay['PriceRemain'], $settings, false); ?>', '<?php echo printCurr($pay['PriceRemain'], $settings); ?>')" /><?php echo $settings->CurrencySuffix; ?><?php endif; ?></td>
<?php
	}
?>
		</tr>
<?php
	}
	if ($rsPay->RecordCount() == 0) {
?>
		<tr>
			<td colspan="<?php echo ($payShow == 1 ? "6" : "5"); ?>" align="center">no payments were processed</td>
		</tr>
<?php
	} else {
?>
		<tr>
			<td colspan="<?php echo ($payShow == 1 ? "6" : "5"); ?>" height="10"></td>
		</tr>
		<tr>
			<td colspan="<?php echo ($payShow == 1 ? "6" : "5"); ?>" align="right">
<?php
	if ($payShow == 1) {
?>
				<input name="process" type="submit" id="process" value="Process" style="width: 100px;" />
				<input name="cancel" type="submit" id="cancel" value="Cancel" style="width: 100px;" />
<?php
	} else {
?>
				<input name="back" type="submit" id="back" value="Back to All Registrations" style="width: 175px;" />
<?php
	}
?>
			</td>
		</tr>
<?php
	}
?>
	</table>
</form>
<?php
}
?>
<p>&nbsp;</p>
<?php
do_html_footer();
?>

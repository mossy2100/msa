<?php

// functions for attendee registration form (admin version)

// constants for database table names
define("TBL_GROUPS", $GLOBALS['DB_PREFIX'] . "AttendeeRegoFormGroups");
define("TBL_FIELDS", $GLOBALS['DB_PREFIX'] . "AttendeeRegoFormFields");
define("TBL_DETAILS", $GLOBALS['DB_PREFIX'] . "AttendeeRegoFormDetails");

// test credit card numbers
//     Visa               4111 1111 1111 1111
//     MasterCard         5555 5555 5555 4444
//     BankCard           5610 0000 0000 0001
//     American Express   3400 0000 0000 009
//     Diners Club        3000 0000 0000 04
//     Carte Blanche      3890 0000 0000 07
//     Discover           6011 1111 1111 1117
//     enRoute            2014 0000 0000 009
//     JCB                3088 0000 0000 0009

// calculate credit card type from card number
function ccCalculateType($cardNum) {
	// strip all non-numeric characters from card number (eg: spaces)
	$num = preg_replace("/[^0-9]/", "", $cardNum);
	// test against regular expressions for each credit card type
	if (preg_match("/^5[1-5][0-9]{14}$/", $num))
		return "MasterCard";
	elseif (preg_match("/^4[0-9]{12}([0-9]{3})?$/", $num))
		return "Visa";
	elseif (preg_match("/^56[0-9]{14}$/", $num))
		return "BankCard";
	elseif (preg_match("/^3[47][0-9]{13}$/", $num))
		return "American Express";
	elseif (preg_match("/^3(0[0-5]|6[0-9]|8[0-8])[0-9]{11}$/", $num))
		return "Diners Club";
	elseif (preg_match("/^389[0-9]{11}$/", $num))
		return "Carte Blanche";
	elseif (preg_match("/^6011[0-9]{12}$/", $num))
		return "Discover";
	elseif (preg_match("/^2(014|149)[0-9]{11}$/", $num))
		return "enRoute";
	elseif (preg_match("/^(3[0-9]{4}|2131|1800)[0-9]{11}$/", $num))
		return "JCB";
	else
		return false;
}

// check credit card number with Luhn Algorithm
function ccCheckNumber($cardNum) {
	$sum = 0;
	// strip all non-numeric characters from card number (eg: spaces)
	$num = preg_replace("/[^0-9]/", "", $cardNum);
	// reverse card number string (easier to deal with odd or even length strings)
	$num = strrev($num);
	// iterate every number
	for ($i = 0; $i < strlen($num); $i++) {
		// get digit
		$digit = substr($num, $i, 1);
		// double every second digit
		if ($i % 2 == 1) {
			$digit *= 2;
			// if doubled digit is over 9 (ie: 2 digits), subtract 9 (ie: add digits together)
			if ($digit > 9)
				$digit -= 9;
		}
		// add digit to running sum of numbers
		$sum += $digit;
	}
	// check if sum is divisible by 10
	return ($sum % 10 == 0);
}

// check credit card expiry date hasn't expired and is within the next 10 years
function ccCheckExpiry($expiry, &$message) {
	// extract month and year (acceptable formats: MMYY, M/YY, MM/YY, M/YYYY, MM/YYYY)
	if (preg_match("/^((?(?=[0-9]{1,2}\/)0?|0)[1-9]|1[0-2])\/?((?:20)?[0-9]{2})$/", $expiry, $parts)) {
		$expMonth = $parts[1];
		$expYear = $parts[2];
		// force 4 digit year
		if (strlen($expYear) == 2)
			$expYear += (date('Y') - date('y'));
	} else {
		$message = "Invalid expiry date.";
		return false; // invalid expiry date
	}
	// get current month and year
	$currMonth = date('m');
	$currYear = date('Y');
	// check year is valid
	$diffYear = $expYear - $currYear;
	if ($diffYear < 0) {
		$message = "This card expired in " . $expYear . ".";
		return false; // already expired
	} elseif ($diffYear > 0)
		return true; // expiry date is valid
	else {
		// expires this year - check month is valid
		$diffMonth = $expMonth - $currMonth;
		if ($diffMonth < 0) {
			$message = "This card expired in " . date('F', mktime(0, 0, 0, (int) $expMonth)) . ".";
			return false; // already expired
		} else
			return true; // expiry date is valid
	}
}

// return credit card number showing only first 6 digits (bank identification number) and last 3 digits
function ccMaskNumber($cardNum) {
	// strip all non-numeric characters from card number (eg: spaces)
	$num = preg_replace("/[^0-9]/", "", $cardNum);
	// return masked number
	return substr($num, 0, 6) . "..." . substr($num, -3);
}

// encrypt credit card number (for masking in source code, not for secure encryption)
function ccEncrypt($cardNum) {
	$key = "";
	$result = "";
	for ($i = 0; $i < strlen($cardNum); $i ++)
		$key .= chr(rand(0, 255));
	for ($i = 0; $i < strlen($cardNum); $i ++) {
		$cardChar = substr($cardNum, $i, 1);
		$keyChar = substr($key, ($i % strlen($key)) - 1, 1);
		$result .= chr(ord($cardChar) + ord($keyChar));
	}
	return base64_encode($result . $key);
}

// decrypt credit card number (for masking in source code, not for secure encryption)
function ccDecrypt($cardNum) {
	$cardNum = base64_decode($cardNum);
	list($cardNum, $key) = str_split($cardNum, (strlen($cardNum) / 2));
	$result = "";
	for ($i = 0; $i < strlen($cardNum); $i ++) {
		$cardChar = substr($cardNum, $i, 1);
		$keyChar = substr($key, ($i % strlen($key)) - 1, 1);
		$result .= chr(ord($cardChar) - ord($keyChar));
	}
	return $result;
}

// regular expression (PCRE) for matching visibility conditions
define("VC_REGEX", "/^(visible|enabled) when \[(\d+):(\d+)\] (?:(?:is |is (not) )" .
	               "({.*}|\d|\((?:(?:\d+(?: (?(4)and|or) \d+)+)|(?:{.*}(?: (?(4)and|or) {.*})+))\)))$/i");

// check validity of visibility condition
function vcValid($viscond) {
	return preg_match(VC_REGEX, $viscond);
}

// parse visibility condition and convert to php array
function vcParse($viscond) {
	if (preg_match(VC_REGEX, $viscond, $matches)) {
		$parsed = array();
		$parsed['action'] = substr(strtolower($matches[1]), 0, 7);
		$parsed['group'] = $matches[2];
		$parsed['field'] = $matches[3];
		$parsed['gfid'] = "g" . $matches[2] . "f" . $matches[3];
		$parsed['gfid2'] = $matches[2] . ":" . $matches[3];
		$parsed['negate'] = ($matches[4] == "not");
		$parsed['negateJS'] = ($matches[4] == "not") ? "true" : "false";
		// parse values
		if (is_numeric($matches[5])) // numeric
			$parsed['value'] = $matches[5];
		elseif ($matches[5][0] == "{") // string
			$parsed['value'] = substr($matches[5], 1, -1);
		else { // multiple values
			if (is_numeric($matches[5][1])) { // multiple numeric
				preg_match_all("/(\d+)/", $matches[5], $values);
				$parsed['value'] = $values[1];
			} else { // multiple strings
				preg_match_all("/{(.*?)}/", $matches[5], $values);
				$parsed['value'] = $values[1];
			}
		}
		return $parsed;
	} else
		return false; // invalid visibility condition
}

// convert visibility condition php value to JavaScript
function vcValueJS($value) {
	if (is_numeric($value)) // numeric, return as is
		return $value;
	elseif (!is_array($value)) // string, return in quotes
		return "\"" . $value . "\"";
	else { // array, return with JavaScript Array() method
		$valueJS = "new Array(";
		$valNum = 0;
		foreach ($value as $val) {
			if ($valNum++ > 0) // don't add comma before first element
				$valueJS .= ", ";
			if (is_numeric($val)) // numeric, add as is
				$valueJS .= $val;
			else // string, return in quotes
				$valueJS .= "\"" . $val . "\"";
		}
		$valueJS .= ")";
		return $valueJS;
	}
}

// compare field value to visibility condition value (for validation)
function vcCompareValue($fieldValue, $vcValue) {
	if (is_string($vcValue) || is_int($vcValue)) {
		if (is_string($fieldValue) || is_int($fieldValue))
			return ($vcValue === $fieldValue);
		else
			for ($j = 0; $j < sizeof($fieldValue); $j ++)
				return ($vcValue === $fieldValue[$j]);
	} else {
		for ($i = 0; $i < sizeof($vcValue); $i ++) {
			if (is_string($fieldValue) || is_int($fieldValue)) {
				if ($vcValue[$i] === $fieldValue)
					return true;
			} else
				for ($j = 0; $j < sizeof($fieldValue); $j ++)
					if ($vcValue[$i] === $fieldValue[$j])
						return true;
		}
		return false; // if nothing else returned
	}
}

// convert spaces to non-breaking spaces
function sp2nbsp($text) {
	return str_replace("  ", " &nbsp;", $text);
}

// format currency using settings from settings table
function printCurr($amount, $settings, $symbols = true) {
	if ($symbols) {
		if (strlen($settings->CurrencyDecimals) == 1)
			return sprintf(sprintf("%%s%%01.%sf%%s", $settings->CurrencyDecimals), $settings->CurrencyPrefix, $amount, $settings->CurrencySuffix);
		else
			return sprintf("%s%01s%s", $settings->CurrencyPrefix, $amount, $settings->CurrencySuffix);
	} else {
		if (strlen($settings->CurrencyDecimals) == 1)
			return sprintf(sprintf("%%01.%sf", $settings->CurrencyDecimals), $amount);
		else
			return sprintf("%01s", $amount);
	}
}

// generate the check digit for a registration ID
function rfGenCheckDigit($regoID) {
	// the check digit is generated using a simple variant of the modulo 10 algorithm
	$sum = 0;
	$factor = 3;
	foreach (str_split(strrev($regoID)) as $digit)
		$sum += ($digit * $factor ++);
	return $sum % 10;
}

// validate the check digit on a registration ID
function rfValCheckDigit($regoID) {
	// strip off last digit of registration ID, generate check digit and compare to stripped digit
	return (rfGenCheckDigit(substr($regoID, 0, -1)) == substr($regoID, -1, 1));
}

// produce query string with current values and change new value only
function queryString() {
	if ($_SERVER['QUERY_STRING'] > "") {
		// split old query string into array
		$oldQS = explode("&", $_SERVER['QUERY_STRING']);
		// convert to key/value pair array
		foreach ($oldQS as $qsItem) {
			list($qsKey, $qsValue) = explode("=", $qsItem);
			$newQS[$qsKey] = $qsValue;
		}
	}
	for ($i = 0; $i < func_num_args() - 1; $i += 2) {
		$key = func_get_arg($i);
		$value = func_get_arg($i + 1);
		// if value is NULL, remove from query string, otherwise add/change it
		if (is_null($value))
			unset($newQS[$key]);
		else	
			$newQS[$key] = $value;	
	}
	// return empty string if query string now empty
	if (count($newQS) == 0)
		return "";
	else {
		// produce string version again
		$retQS = "";
		foreach ($newQS as $qsKey => $qsValue)
			$retQS .= ($retQS == "" ? "?" : "&") . $qsKey . "=" . $qsValue;
		return $retQS;
	}
}

?>

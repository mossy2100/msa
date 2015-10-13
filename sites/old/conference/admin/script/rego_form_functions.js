// functions for attendee registration form (admin version)

// confirm deletion of group or field with user before proceeding
function confirmDel(text) {
	return confirm('Are you sure you want to delete this ' + text + '?\n\n' +
				   'Press OK to delete. This action cannot be undone.');
}

// open help window
function openHelp(page, param) {
	helpWin = window.open(page + "?help=" + param,
						  "Help", "width=400,height=400,scrollbars=yes");
	helpWin.focus();
}

// compare field value to visibility condition value
function vcCompareValue(fieldValue, vcValue) {
	if ((typeof vcValue == "string") || (typeof vcValue == "number")) {
		if ((typeof fieldValue == "string") || (typeof fieldValue == "number"))
			return (vcValue == fieldValue);
		else
			for (var j = 0; j < fieldValue.length; j ++)
				return (vcValue == fieldValue[j]);
	} else {
		for (var i = 0; i < vcValue.length; i ++) {
			if ((typeof fieldValue == "string") || (typeof fieldValue == "number")) {
				if (vcValue[i] == fieldValue)
					return true;
			} else
				for (var j = 0; j < fieldValue.length; j ++)
					if (vcValue[i] == fieldValue[j])
						return true;
		}
		return false; // if nothing else returned
	}
}

// process visibility conditions (trigger on event, test value, take appropriate action)
function vcEvent(fieldId, fieldType) {
	var obj, fieldValue;
	// get value from field
	switch (fieldType) {
		case 2: // text field
		case 3: // text area
			fieldValue = document.getElementById(fieldId).value;
			break;
		case 4: // checkbox
		case 6: // radio button
			fieldValue = (document.getElementById(fieldId).checked) ? 1 : 0;
			break;
		case 5: // checkbox group
			fieldValue = new Array();
			var i = 1;
			while ((obj = document.getElementById(fieldId + "v" + i)) != null) {
				if (obj.checked)
					fieldValue[fieldValue.length] = obj.value;
				i ++;
			}
			break;
		case 7: // radio button group
			var i = 1;
			while ((obj = document.getElementById(fieldId + "v" + i)) != null) {
				if (obj.checked) {
					fieldValue = i;
					break;
				}
				i ++;
			}
			break;
		case 8: // drop-down menu
			if ((obj = document.getElementById(fieldId)) != null)
				fieldValue = obj.options[obj.selectedIndex].value;
			break;
		case 9: // selectable list
			fieldValue = new Array();
			if ((obj = document.getElementById(fieldId)) != null)
				for (var i = 0; i < obj.options.length; i ++)
					if (obj.options[i].selected)
						fieldValue[fieldValue.length] = obj.options[i].value;
			break;
	}
	// find arrays corresponding to this field and compare its value
	for (var i = 0; i < vcArray.length; i ++) {
		if (fieldId == vcArray[i][0]) {
			var compare = vcCompareValue(fieldValue, vcArray[i][3]);
			if (vcArray[i][4]) // negation
				compare = !compare;
			if (vcArray[i][2] == "visible") {
				// enable/disable first (to prevent it being submitted)
				vcEnable(vcArray[i][1], compare);
				// change visibility
				vcVisible(vcArray[i][1], compare);
			} else {
				// enable/disable
				vcEnable(vcArray[i][1], compare);
			}
		}
	}
}

// enable/disable a field
function vcEnable(field, enable) {
	// en/disable whole row if possible
	if ((obj = document.getElementById("row_" + field)) != null)
		obj.disabled = !enable;
	// also en/disable field
	if ((obj = document.getElementById(field)) != null)
		obj.disabled = !enable;
	// for fields that have multiple elements
	var i = 1;
	while ((obj = document.getElementById(field + "v" + i)) != null) {
		obj.disabled = !enable;
		i ++;
	}
}

// show/hide a field
function vcVisible(field, visible) {
	// show/hide whole row
	if ((obj = document.getElementById("row_" + field)) != null)
		if (obj.style)
			obj.style.display = (visible ? "block" : "none");
}

// if validation function is credit card number, show the accepted cards row
function ccValFuncAcceptedCards(field) {
	// are we showing or hiding?
	$show = (field.options[field.selectedIndex].value == 31);
	// enable/disable checkboxes first
	var i = 1;
	while ((obj = document.getElementById("acceptedcards" + i)) != null) {
		obj.disabled = !$show;
		i ++;
	}
	// show/hide whole row
	if ((obj = document.getElementById("acceptedcards")) != null)
		if (obj.style)
			obj.style.display = ($show ? "block" : "none");
}

// jump menu (drop-down menu that navigates to new page on select)
function rlJumpMenu(menu) {
  eval("location = '" + menu.options[menu.selectedIndex].value + "'");
}

// add reference number to payment list
function rlAddPayment(refno) {
	// check reference number check digit (in case typed incorrectly)
	if (!rfValCheckDigit(refno)) {
		alert("The Reference Number you entered is invalid. Please check and try again.\n\n" +
		      "If you get this message again, try finding it in the list above.");
		return;
	}
	// now add to payment list
	if ((obj = document.getElementById("paymentlist")) != null) {
		// if refno already in list, return false
		for (var i = 0; i < obj.length; i ++)
			if (obj.options[i].value == refno)
				return;
		// add refno to list
		newOpt = new Option(refno, refno)
		obj.options[obj.length] = newOpt;
		// enable process button if disabled (only disabled if new length of list is 1)
		if (obj.length == 1)
			if ((obj = document.getElementById("process")) != null)
				obj.disabled = false;
	}
}

// remove reference number from payment list
function rlRemPayment() {
	if ((obj = document.getElementById("paymentlist")) != null) {
		if (obj.selectedIndex > -1) {
			obj.options[obj.selectedIndex] = null;
			// disable process button if list empty (if new length of list is 0)
			if (obj.length == 0)
				if ((obj = document.getElementById("process")) != null)
					obj.disabled = true;
			// disable delete button
			if ((obj = document.getElementById("delete")) != null)
				obj.disabled = true;
		}
	}
}

// converts payment list into a space delimited string in a hidden field
function rlGetPayments() {
	if ((obj = document.getElementById("paymentlist")) != null) {
		var str = "";
		// take each value in list and append to string
		for (var i = 0; i < obj.length; i ++)
			str += obj.options[i].value + " ";
		// remove space at end
		str = str.substr(0, str.length - 1);
		// deselect any values in payment list (if selected) to prevent it being submitted
		obj.selectedIndex = -1;
		// set value of hidden field to string
		if ((obj = document.getElementById("payrefs")) != null)
			obj.value = str;
	}
}

// check the amount entered to pay is not more than the amount remaining
function rlMaxAmount(field, amount, printAmount) {
	if (parseFloat(field.value) > parseFloat(amount)) {
		alert("You cannot apply a payment greater than the amount outstanding.\n\n" +
		      "The amount to pay has been set to " + printAmount + " as this is the amount outstanding.");
		// set value to max amount
		field.value = amount;
		// return false to prevent focus changing to next field
		return false;
	}
}

// generate the check digit for a registration ID
function rfGenCheckDigit(regoID) {
	// the check digit is generated using a simple variant of the modulo 10 algorithm
	var sum = 0;
	var factor = 3;
	for (var i = regoID.length - 1; i >= 0; i --) {
		sum += (regoID.charAt(i) * factor ++);
	}
	return (sum % 10);
}

// validate the check digit on a registration ID
function rfValCheckDigit(regoID) {
	// strip off last digit of registration ID, generate check digit and compare to stripped digit
	return (rfGenCheckDigit(regoID.substr(0, 6)) == regoID.charAt(6));
}

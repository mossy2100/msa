// functions for attendee registration form

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

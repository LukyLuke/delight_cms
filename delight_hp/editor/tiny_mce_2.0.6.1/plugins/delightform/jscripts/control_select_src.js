/**
 * $Id: control_select_src.js,v 1.1.1.1 2009/01/07 20:10:37 lukas Exp $
 *
 * @author Lukas Zurschmiede
 * @copyright Copyright © 2007 delight software gmbh. All rights reserved.
 */


// initialize sorting
var direction = 1;
var sortType = '';

/**
 * add an empty line for option of existing select control to the table of options, thus
 * creating a table in which all options can be edited
 */
function addOption() {
	var table = document.getElementById('options_table');
	var num = table.rows.length;
	var tr = table.insertRow(num);
	// first line of table are titles, thus rows are indexed from 1
	var td0 = tr.insertCell(0);
	var td1 = tr.insertCell(1);
	var td2 = tr.insertCell(2);
	var td3 = tr.insertCell(3);
	td0.innerHTML = '<input type="radio" name="rd_default" value="' + num + '" id="rd_default_' + num + '"/>';
	td1.innerHTML = '<input type="text" id="ed_option_value_' + num + '" name="ed_option_value_' + num + '" class="small_input" onkeyup="validateName(this)"/>'
	td2.innerHTML = '<input type="text" id="ed_option_text_' + num + '" name="ed_option_text_' + num + '" class="medium_input"/>';
	td3.innerHTML = '<input type="text" id="ed_option_pr_' + num + '" name="ed_option_pr_' + num + '" value="' + num * 2 + '" class="small_input" onkeyup="validateNumber(this)"/>';
}

/**
 * sort table lines by text
 */
function sortText() {
	if (sortType == 'text') {
		direction = -direction;
	} else {
		direction = 1;
	}
	sortType = 'text';
	doSort(cmpText);
}

/**
 * sort table lines by priority
 */
function sortPriority() {
	if (sortType == 'priority') {
		direction = -direction;
	} else {
		direction = 1;
	}
	sortType = 'priority';
	doSort(cmpPriority);
}

/**
 * sort table lines by value
 */
function sortValue() {
	if (sortType == 'value') {
		direction = -direction;
	} else {
		direction = 1;
	}
	sortType = 'value';
	doSort(cmpValue);
}

/**
 * Prepare the Options-Table and sort it after
 */
function doSort(sortProc) {
	var table = document.getElementById('options_table');
	var num = table.rows.length;
	var values = Array();
	
	// The first line of the table are titles, thus rows are indexed from 1
	// copy lines from the table into array of values (can't use sort directly on table.rows anyway)
	for (var i = 1; i < num; i++) {
		values[i-1] = Array(
		document.getElementById('ed_option_value_' + i).value,
		document.getElementById('ed_option_text_' + i).value,
		document.getElementById('ed_option_pr_' + i).value
		);
	}
	
	// Sort it by the procedure the user has clicked
	values.sort(sortProc);
	
	// The first line of the table are titles, thus rows are indexed from 1
	for (var i = 1; i < num; i++) {
		document.getElementById('ed_option_value_' + i).value = values[i-1][0];
		document.getElementById('ed_option_text_' + i).value = values[i-1][1];
		document.getElementById('ed_option_pr_' + i).value = values[i-1][2];
	}
}

/**
 * compare by option names
 */
function cmpText(v1,v2) {
	if (v1[1] > v2[1]) {
		return direction;
	}
	if (v1[1] < v2[1]) {
		return direction * -1;
	}
	return 0;
}

/**
 * compare by option values
 */
function cmpValue(v1,v2) {
	if (v1[0] > v2[0]) {
		return direction;
	}
	if (v1[0] < v2[0]) {
		return direction * -1;
	}
	return 0;
}

/**
 * commmpare by option priorities
 */
function cmpPriority(v1,v2) {
	return direction * (parseFloat(v1[2]) - parseFloat(v2[2]));
}

/**
 * initialize popup form for inserting select control
 */
function initSelect() {
	var inst = tinyMCE.getInstanceById(tinyMCE.getWindowArg('editor_id'));
	var focusElm = inst.getFocusElement();
	var nodeName = focusElm.nodeName.toLowerCase();
	
	// Check if a SELECT-Element is selected
	if (nodeName == "select") {
		// store the element for later use
		editing = focusElm;
		
		// remove name prefix
		document.getElementById('ed_name').value = focusElm.name.replace(/^cb_/, '');
		document.getElementById('ed_desc').value = focusElm.title;
		
		var table = document.getElementById('options_table');
		var cnt = 1, len = table.rows.length;
		for (var i = 0; i < editing.childNodes.length; i++) {
			// first line of table is titles, thus rows are indexed from 1
			// firs line of table is already defined in HTML, so fill it
			if (editing.childNodes[i].nodeType == 1) {
				document.getElementById('ed_option_value_' + cnt).value = editing.childNodes[i].value;
				document.getElementById('ed_option_text_' + cnt).value = encodeAttribute(editing.childNodes[i].innerHTML);
			
				// renumber the values, so that the items can be swaped easily
				document.getElementById('ed_option_pr_' + cnt).value = cnt * 2;
				if (editing.childNodes[i].selected) {
					document.getElementById('rd_default_' + cnt).checked = true;
				}
			
				// add another empty line, the table should always end with an empty line for quick adding a new option
				addOption();
				cnt++;
			}
		}
	} else {
		editing = null;
	}
}


/**
 * insert select control
 */
function insertSelect() {
	var inst = tinyMCE.getInstanceById(tinyMCE.getWindowArg('editor_id'));
	
	// Get all values
	var cName = document.getElementById('ed_name').value;
	var cDesc = document.getElementById('ed_desc').value;

	// name is mandatory
	if (cName == '') {
		alert(tinyMCE.entityDecode(tinyMCE.getLang('lang_amadeo_form_err_name_mandatory')));
		return false;
	} else {
		// add prefix to name, so that unique filenames have to be maintained only within controls of the same type
		cName = 'cb_' + cName;
	}

	// name must be unique
	if (!editing && inst.contentWindow.document.getElementById(cName)) {
		alert(tinyMCE.entityDecode(tinyMCE.getLang('lang_amadeo_form_err_name_unique')));
		return false;
	}

	// get style class
	var className = '';

	// insert select in document
	// this must definitely be in DOM to correctly handle options in all browsers
	inst.execCommand('mceBeginUndoLevel');

	// It's a new Element
	if (editing == null) {
		editing = inst.getDoc().createElement('select');
		inst.getFocusElement().appendChild(editing);
	} else {
		// if not a new Element, clean the Content to create it new
		editing.innerHTML = '';
	}

	editing.setAttribute('id', cName);
	editing.setAttribute('name', cName);
	editing.setAttribute('title', cDesc);
	editing.className = className;

	// add all options to select
	var table = document.getElementById('options_table');
	var oValue, oText, optElm;
	// first line of table are titles, thus rows are indexed from 1
	for (var i = 1; i < table.rows.length; i++) {
		oValue = document.getElementById('ed_option_value_' + i).value;
		oText = document.getElementById('ed_option_text_' + i).value;
		
		// both, value and caption, must be entered
		if ((oValue != '') && (oText != '')) {
			optElm = inst.getDoc().createElement('option');
			optElm.value = oValue;
			optElm.innerHTML = oText;
			if (document.getElementById('rd_default_' + i).checked) {
				optElm.setAttribute('selected', 'selected');
			}
			editing.appendChild(optElm);
		}
	}

	inst.execCommand("mceEndUndoLevel");
	tinyMCEPopup.close();
}

/**
 * $Id: control_button_src.js,v 1.1.1.1 2009/01/07 20:10:38 lukas Exp $
 *
 * @author Lukas Zurschmiede
 * @copyright Copyright © 2007 delight software gmbh. All rights reserved.
 */


/**
 * initialize popup for inserting a button control
 */
function initButton() {
	var inst = tinyMCE.getInstanceById(tinyMCE.getWindowArg('editor_id'));
	var focusElm = inst.getFocusElement();
	var nodeName = focusElm.nodeName.toLowerCase();
	
	// Check if the current selected Element is a button
	if ((nodeName == "input") && ((focusElm.type == 'submit') || (focusElm.type == 'reset') || (focusElm.type == 'button'))) {
		// Set the global var to the focused element for later use
		editing = focusElm;
		
		//remove prefix from name
		document.getElementById('ed_name').value = focusElm.name.replace(/^b_/, '');
		document.getElementById('ed_desc').value = focusElm.title;
		document.getElementById('ed_value').value = focusElm.value;
	} else {
		editing = null;
	}
}

/** 
 * insert button control
 */
function insertButton() {
	var inst = tinyMCE.getInstanceById(tinyMCE.getWindowArg('editor_id'));
	
	// Get all values from button-form
	var cName = document.getElementById('ed_name').value;
	var cDesc = document.getElementById('ed_desc').value;
	var cValue = document.getElementById('ed_value').value;
	var typeName = 'submit';

	// name is mandatory
	if (cName == '') {
		alert(tinyMCE.entityDecode(tinyMCE.getLang('lang_delightform_err_name_mandatory')));
		return false;
	} else {
		cName = 'b_' + cName;
	}

	// value (description) is mandatory
	if (cValue == '') {
		alert(tinyMCE.entityDecode(tinyMCE.getLang('lang_delightform_err_value_mandatory')));
		return false;
	}

	// name must be unique
	if (!editing && inst.contentWindow.document.getElementById(cName)) {
		alert(tinyMCE.entityDecode(tinyMCE.getLang('lang_delightform_err_name_unique')));
		return false;
	}

	// get button type
	switch (getSelectValue('cb_type')) {
		case 'submit':
			typeName = 'button';
			break;
		case 'reset':
			typeName = 'reset';
			break;
		default:
			typeName = 'button';
			break;
	}
	
	// set the className
	var className = 'btn' + typeName;

	// insert button-control
	inst.execCommand('mceBeginUndoLevel');
	if (editing != null) {
		editing.className = className;
		editing.setAttribute('id', cName);
		editing.setAttribute('name', cName);
		editing.setAttribute('title', cDesc);
		editing.setAttribute('type', typeName);
		editing.setAttribute('value', cValue);
	} else {
		html = '<input';
		html += makeAttrib('type', typeName);
		html += makeAttrib('id', cName);
		html += makeAttrib('name', cName);
		html += makeAttrib('title', cDesc);
		html += makeAttrib('class', className);
		html += makeAttrib('value', cValue);
		html += '/>';
		inst.execCommand('mceInsertContent', false, html);
	}
	inst.execCommand("mceEndUndoLevel");
	tinyMCEPopup.close();
}
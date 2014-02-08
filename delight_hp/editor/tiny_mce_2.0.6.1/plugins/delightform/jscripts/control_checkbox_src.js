/**
 * $Id: control_checkbox_src.js,v 1.1.1.1 2009/01/07 20:10:39 lukas Exp $
 *
 * @author Lukas Zurschmiede
 * @copyright Copyright © 2007 delight software gmbh. All rights reserved.
 */


/**
 * initialize popup for inserting checkbox control
 */
function initCheckbox() {
	var inst = tinyMCE.getInstanceById(tinyMCE.getWindowArg('editor_id'));
	var focusElm = inst.getFocusElement();
	var nodeName = focusElm.nodeName.toLowerCase();

	// Check if the selected Element is an Edit-Element (input=text,password,hidden,file or textarea)
	if ((nodeName == "input") && (focusElm.type.toLowerCase() == 'checkbox')) {
		// Set the global var to the focused element for later use
		editing = focusElm;

		// remove prefix from name
		document.getElementById('ed_name').value = focusElm.name.replace(/^chk_/, '');
		document.getElementById('ed_desc').value = focusElm.title;
		document.getElementById('ed_value').value = focusElm.value;
		document.getElementById('chk_checked').checked = focusElm.checked;
		document.getElementById('chk_valid_mandatory').checked = (focusElm.className.toLowerCase() == 'mandatory');
	} else {
		editing = null;
	}
}


/**
 * Insert an checkbox control
 */
function insertCheckbox() {
	var inst = tinyMCE.getInstanceById(tinyMCE.getWindowArg('editor_id'));

	// Get all values from Checkbox-Formular
	var cName = document.getElementById('ed_name').value;
	var cDesc = document.getElementById('ed_desc').value;
	var cValue = document.getElementById('ed_value').value;

	// name is mandatory
	if (cName == '') {
		alert(tinyMCE.entityDecode(tinyMCE.getLang('lang_delightform_err_name_mandatory')));
		return false;
	} else {
		/* add prefix to name, so that unique filenames have to be maintained only
		within controls of the same type */
		cName = 'chk_' + cName;
	}

	// name must be unique
	if (!editing && inst.contentWindow.document.getElementById(cName)) {
		alert(tinyMCE.entityDecode(tinyMCE.getLang('lang_delightform_err_name_unique')));
		return false;
	}

	// checkbox value is mandatory
	if (cValue == '') {
		alert(tinyMCE.entityDecode(tinyMCE.getLang('lang_delightform_err_value_mandatory')));
		return false;
	}

	// Check if the Checkbox in mandatory
	if (document.getElementById('chk_valid_mandatory').checked) {
		className = 'mandatory';
	} else {
		className = '';
	}

	// insert control
	inst.execCommand('mceBeginUndoLevel');
	var isNew = (editing == null);
	if (isNew) {
		editing = inst.getDoc().createElement('input');
		editing.setAttribute('type', 'checkbox');
	}
	editing.setAttribute('id', cName);
	editing.setAttribute('name', cName);
	editing.setAttribute('title', cDesc);
	editing.setAttribute('value', cValue);
	editing.className = className;
	// Attention: the attribute "checked" we can not set before we added the element
	
	// Add the Checkbox if it's a new Element
	if (isNew) {
		inst.getFocusElement().appendChild(editing);
	}

	// Set the attribute "checked" or remove it
	// we have to set first the property "checked", after we need to set the attribute for XHTML-Compatibility
	if (document.getElementById('chk_checked').checked) {
		editing.checked = true;
		editing.setAttribute('checked', 'checked');
	} else {
		// IE doesn't work with removeAttribute so we clean first the attribute and remove it after because FF does interpret an existend attribute as checked
		//editing.checked = false;
		editing.setAttribute('checked', '');
		editing.removeAttribute('checked');
	}

	inst.execCommand("mceEndUndoLevel");
	tinyMCEPopup.close();
}

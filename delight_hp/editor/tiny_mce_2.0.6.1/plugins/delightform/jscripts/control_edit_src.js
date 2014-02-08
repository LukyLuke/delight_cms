/**
 * $Id: control_edit_src.js,v 1.1.1.1 2009/01/07 20:10:38 lukas Exp $
 *
 * @author Lukas Zurschmiede
 * @copyright Copyright © 2007 delight software gmbh. All rights reserved.
 */


/**
 * initialize popup for inserting edit control, edit control includes both
 * input type: text, file, password and textarea element
 */
function initEdit() {
	var inst = tinyMCE.getInstanceById(tinyMCE.getWindowArg('editor_id'));
	var focusElm = inst.getFocusElement();
	var nodeName = focusElm.nodeName.toLowerCase();
	
	// Check if the selected Element is an Edit-Element (input=text,password,hidden,file or textarea)
	if ( ((nodeName == "input") && ((focusElm.type == 'text') || (focusElm.type == 'password') || (focusElm.type == 'hidden') || (focusElm.type == 'file'))) || (nodeName == "textarea") ) {
		// Set the global var to the focused element for later use
		editing = focusElm;

		// Set select-value for type to right fieldtype (password,text,file or multi)
		var singleLine = (nodeName != "textarea");
		if (singleLine) {
			selectOption('cb_type', focusElm.type.toLowerCase());
			changeSizeFields(focusElm.type.toLowerCase());
		} else {
			selectOption('cb_type', 'multi');
			changeSizeFields('multi');
		}
		
		// Get all classes from the Element and set the field mandatory and select the CheckType
		classes = focusElm.className.toLowerCase().split(' ');
		document.getElementById('chk_valid_mandatory').selected = false;
		for (var i = 0; i < classes.length; i++) {
			switch (classes[i]) {
				case 'mandatory':
					document.getElementById('chk_valid_mandatory').checked = true;
					break;
				case 'email':
					selectOption('cb_validation', 'email');
					break;
				case 'number':
					selectOption('cb_validation', 'number');
					break;
			}
		}
		
		// Set name, title, value and size - remove prefix from name
		document.getElementById('ed_name').value = focusElm.name.replace(/^ed_/, '');
		document.getElementById('ed_desc').value = focusElm.title;
		if (singleLine) {
			document.getElementById('ed_size').value = focusElm.size;
			document.getElementById('ed_value').value = focusElm.value;
		} else {
			document.getElementById('ed_rows').value = focusElm.getAttribute('rows');
			document.getElementById('ed_size').value = focusElm.getAttribute('cols');
			document.getElementById('ed_value').value = encodeAttribute(focusElm.innerHTML);
		}
	} else {
		editing = null;
		selectOption('cb_type', 'text');
		changeSizeFields('text');
	}
}

/**
 * Show or Hide the second Size-Field based on the type of the Input-Field
 * 'multi' - Textarea - fields have also a cols not only rows-size-field
 */
function changeSizeFields(type) {
	if (type == 'multi') {
		document.getElementById('rows').style.display = 'block';
	} else {
		document.getElementById('rows').style.display = 'none';
	}
}

/**
 * Insert an EDIT-Control
 */
function insertEdit() {
	var inst = tinyMCE.getInstanceById(tinyMCE.getWindowArg('editor_id'));
	
	// Get all values from field-fomular
	var typeName = getSelectValue('cb_type');
	var cName = document.getElementById('ed_name').value;
	var cDesc = document.getElementById('ed_desc').value;
	var cSize = document.getElementById('ed_size').value;
	var cRows = document.getElementById('ed_rows').value;
	var validation = getSelectValue('cb_validation');
	var cValue = document.getElementById('ed_value').value;

	var classes = Array();
	var singleLine = (typeName != 'multi');

	// name is always mandatory
	if (cName == '') {
		alert(tinyMCE.getLang('lang_delightform_err_name_mandatory'));
		return false;
	} else {
		// add prefix to name, so that unique filenames have to be maintained only within controls of the same type
		cName = 'ed_' + cName;
	}

	// name must be unique
	if (!editing && inst.contentWindow.document.getElementById(cName)) {
		alert(tinyMCE.entityDecode(tinyMCE.getLang('lang_delightform_err_name_unique')));
		return false;
	}

	// validation for text means no type validation, otherwise save the class
	if (validation != 'text') {
		classes.push(validation);
	}

	// get validation class
	if (document.getElementById('chk_valid_mandatory').checked) {
		classes.push('mandatory');
	}

	// insert edit control - begin UNDO in TinyMCE
	inst.execCommand('mceBeginUndoLevel');
	
	// control changed from multiline to single line or the otherway; so the element must be removed
	if (editing && ( ((editing.nodeName.toLowerCase() == 'textarea') && singleLine) || ((editing.nodeName.toLowerCase() == 'input') && !singleLine)) ) {
		// Clean the inner HTML to be sure that the element will really be removed
		editing.innerHTML = '';
		// Remove the Element and define that this is a new Element
		inst.execCommand('mceReplaceContent', false, '');
		editing = null;
	}

	// an existend Element
	if (editing != null) {
		editing.setAttribute('id', cName);
		editing.setAttribute('name', cName);
		editing.setAttribute('title', cDesc);
		editing.className = classes.join(' ');
		if (singleLine) {
			switch (typeName) {
				case 'password':
					editing.type = 'password';
					break;
				case 'file':
					editing.type = 'file';
					break;
				default:
					editing.type = 'text';
					break;
			}

			// Add the size-attribute
			editing.setAttribute('size', cSize);
			
			// we can set the value to each element, expect to file-fields
			if (typeName != 'file') {
				editing.setAttribute('value', cValue);
			}
		} else {
			// Add the size-attribute
			editing.setAttribute('cols', cSize);
			editing.setAttribute('rows', cRows);
			
			// Multiline-Elements have no "value" tage, the have innerHTML and innerText
			editing.innerHTML = cValue;
		}

	// it's a new Element
	} else {
		if (singleLine) {
			html = '<input';
			html += makeAttrib('type', typeName);
		} else {
			html = '<textarea';
		}

		html += makeAttrib('id', cName);
		html += makeAttrib('name', cName);
		html += makeAttrib('title', cDesc);
		html += makeAttrib('class', classes.join(' '));
		if (singleLine) {
			// Add the size-attribute
			html += makeAttrib('size', cSize);
			
			// we can set the value to each element, expect to file-fields
			if (typeName != 'file') {
				html += makeAttrib('value', cValue);
			}
			html += '/>';
		} else {
			// Add the size-attribute
			html += makeAttrib('cols', cSize);
			html += makeAttrib('rows', cRows);
			
			// Multiline-Elements have no valueTage, the have a Content
			html += '>' + cValue + '</textarea>';
		}
		html += '&nbsp;';
		inst.execCommand('mceInsertContent', false, html);
	}
	inst.execCommand("mceEndUndoLevel");
	tinyMCEPopup.close();
}

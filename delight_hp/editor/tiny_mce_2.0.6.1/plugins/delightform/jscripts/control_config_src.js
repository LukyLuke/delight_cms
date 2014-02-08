/**
 * $Id: control_config_src.js,v 1.1.1.1 2009/01/07 20:10:40 lukas Exp $
 *
 * @author Lukas Zurschmiede
 * @copyright Copyright © 2007 delight software gmbh. All rights reserved.
 */


/**
 * initialize popup for configure the form for this TextBlock
 * input type: text, file, password and textarea element
 */
function initEdit() {
	var inst = tinyMCE.getInstanceById(tinyMCE.getWindowArg('editor_id'));
	
	// Load all fields from Editor inside "fld_mail_senderfield" to let the user select
	// in which Field a User should writ in his EMail-Address
	loadPossibleUserEmailFields();

	// Load all attributes from TinyMCE_delightformPlugin
	var name,fld;
	for (var i = 0; i < _formValidAttributes.length; i++) {
		name = _formValidAttributes[i];
		if ((fld = document.getElementsByName('fld_' + name)) && (fld.length > 0)) {
			value = tinyMCE.getWindowArg('arg_' + name);
			value = (typeof(value) == 'undefined') ? '' : value;
			//alert(name+'='+value);
			if (value == 'false') {
				value = '';
			}
			if ((name == 'onsuccess') || (name == 'onfailure')) {
				value = unescape(value);
			}
			value = value.replace(/\+/g, ' ');
			value = value.replace(/\#\#43\;/g, '+');
			value = value.replace(/\#\#34\;/g, '\"');
			value = value.replace(/\#\#39\;/g, '\'');
			value = value.replace(/\\n/g, '\n');
			setFieldValue(fld[0], value);
		}
	}
	
	showConfigTab('email');
	showTrackingSettings('newsletter');
}

/**
 * Load all fields from Editor inside "fld_mail_senderfield" to let the user select
 * in which Field a User should writ in his EMail-Address
 */
function loadPossibleUserEmailFields() {
	var inst = tinyMCE.getInstanceById(tinyMCE.getWindowArg('editor_id'));
	var inputs = tinyMCE.getElementsByAttributeValue(inst.getBody(), "input", "type", "text");
	var elem = null;
	
	if ( (elem = document.getElementsByName('fld_mailsenderfield')) && (elem.length > 0) ) {
		elem = elem[0];
		for (var i = 0; i < inputs.length; i++) {
			var opt = document.createElement('option');
			opt.innerHTML = inputs[i].name.replace(/ed_/, '');
			opt.setAttribute('name', inputs[i].name);
			elem.appendChild(opt);
		}
	}
	
	if ( (elem = document.getElementsByName('fld_trasenderemail')) && (elem.length > 0) ) {
		elem = elem[0];
		for (var i = 0; i < inputs.length; i++) {
			var opt = document.createElement('option');
			opt.innerHTML = inputs[i].name.replace(/ed_/, '');
			opt.setAttribute('name', inputs[i].name);
			elem.appendChild(opt);
		}
	}
}

/**
 * Set the value on the given Formular-Field
 * Set the innerHTML on Textareas, the value on input-fields
 * or select the right option on selects
 *
 * @param HTMLElement The Form-Element to set the value
 * @param String Value to set to the FormularField
 */
function setFieldValue(fld, value) {
	switch (fld.nodeName) {
		case 'INPUT':
			fld.value = value;
			break;
		case 'TEXTAREA':
			fld.innerHTML = value;
			break;
		case 'SELECT':
			for (var i = 0; i < fld.options.length; i++) {
				fld.options[i].selected = (fld.options[i].value == value)
			}
			break;
	}
}

/**
 * Return a value from a Formular-Field
 * @param HTMLElement FormField to get the value from
 */
function getFieldValue(fld) {
	switch (fld.nodeName) {
		case 'INPUT':
			return fld.value;
			break;
		case 'TEXTAREA':
			return fld.value;
			break;
		case 'SELECT':
			for (var i = 0; i < fld.options.length; i++) {
				if (fld.options[i].selected) {
					return fld.options[i].value;
				}
			}
			break;
	}
}


/**
 * Configure the Formular in TinyMCE_delightformPlugin
 */
function configureFormular() {
	var inst = tinyMCE.getInstanceById(tinyMCE.getWindowArg('editor_id'));
	
	// Get all values from configuration-fomular and set them as attributes in TinyMCE_delightformPlugin
	var fld,name,value;
	for (var i = 0; i < _formValidAttributes.length; i++) {
		name = _formValidAttributes[i];
		if ((fld = document.getElementsByName('fld_' + name)) && (fld.length > 0)) {
			value = getFieldValue(fld[0]);
			if (name == 'name') {
				value = value.replace(/[^a-zA-Z0-9_-]+/g, '_');
			} else if ((name == 'onsuccess') || (name == 'onfailure')) {
				value = escape(value);
			} else if (name == 'mail_rcpt') {
				value = value.replace(/[^a-zA-Z0-9\.\@_-]+/g, '');
			}
			value = value.replace(/\</g, '&gt;');
			value = value.replace(/\>/g, '&lt;');
			value = value.replace(/\+/g, '##43;');
			value = value.replace(/\n/g, '\\n');
			value = value.replace(/\s/g, '+');
			value = value.replace(/\"/g, '##34;');
			value = value.replace(/\'/g, '##39;');
			inst.execCommand('mcedelightf_setattribute', false, name + '=' + value);
		}
	}

	// Close the Configuration-Window
	tinyMCEPopup.close();
}

/**
 * Change the Visibility of the Config-Formular between email and tracking
 */
function showConfigTab(type) {
	switch (type) {
		case 'email':
			$('options_tab_tracking').hide();
			$('options_tab_email').show();
			break;
		
		case 'tracking':
			$('options_tab_tracking').show();
			$('options_tab_email').hide();
			break;
	}
}

function showTrackingSettings(type) {
	switch (type) {
		case 'newsletter':
			$('tracking_legend_newsletter').show();
			$('tracking_legend_formular').hide();
			break;
		
		case 'formular':
			$('tracking_legend_newsletter').hide();
			$('tracking_legend_formular').show();
			break;
	}
}

var _formValidAttributes = ['name','method','validate','onsuccess','onfailure','encoding','mailrcpt','mailrcptname','mailsubject','mailinform','mailsenderfield','mailpretext','mailposttext','traserver','traaccount','tramodule','trasenderemail'];

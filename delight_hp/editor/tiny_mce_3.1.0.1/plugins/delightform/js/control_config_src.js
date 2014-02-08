/**
 * $Id: control_config_src.js,v 1.1.1.1 2009/01/07 20:08:59 lukas Exp $
 *
 * @author Lukas Zurschmiede
 * @copyright Copyright © 2007 delight software gmbh. All rights reserved.
 */

tinyMCEPopup.requireLangPack();

var DelightConfigFieldDialog = {
	init : function() {
		var t = this;
		tinyMCEPopup.resizeToInnerSize();
		t.__loadValues();
		t.showConfigTab('email');
		t.showTrackingSettings('newsletter');
	},

	close : function() {
		//var e = tinyMCEPopup.editor.selection.getNode();
		tinyMCEPopup.close();
	},
	
	getElem : function(id) {
		return document.getElementById(id);
	},
	
	configureFormular : function() {
		var t = this, ed = tinyMCEPopup.editor, dom = ed.dom, e = tinyMCEPopup.editor.selection.getNode();
		var f = document.forms[0], attr = e.attributes, fld, val, n;
	
		// Get all values from configuration-fomular and set them as attributes in TinyMCE_delightformPlugin
		for (var i = 0; i < f.elements.length; i++) {
			if (f.elements[i].name) {
				n = f.elements[i].name.replace(/fld_/, '');
				val = f.elements[i].value;

				if (n == 'name') {
					val = val.replace(/[^a-zA-Z0-9_-]+/g, '_');
				} else if ((n == 'onsuccess') || (n == 'onfailure')) {
					val = escape(val);
				} else if (n == 'mail_rcpt') {
					val = val.replace(/[^a-z0-9@_.-]+/g, '').toLowerCase();
				}
				val = val.replace(/\</g, '&gt;');
				val = val.replace(/\>/g, '&lt;');
				val = val.replace(/\+/g, '##43;');
				val = val.replace(/\n/g, '##10;');
				val = val.replace(/\s/g, '+');
				val = val.replace(/\"/g, '##34;');
				val = val.replace(/\'/g, '##39;');
				e.setAttribute(n, val);
			}
		}

		// Close the Configuration-Window
		t.close()
	},
	
	showConfigTab : function(type) {
		var t = this;
		switch (type) {
			case 'email':
				t.getElem('options_tab_tracking').style.display = 'none';
				t.getElem('options_tab_email').style.display = 'block';
				break;
			
			case 'tracking':
				t.getElem('options_tab_tracking').style.display = 'block';
				t.getElem('options_tab_email').style.display = 'none';
				break;
		}
	},

	showTrackingSettings : function(type) {
		var t = this;
		switch (type) {
			case 'newsletter':
				t.getElem('tracking_legend_newsletter').style.display = 'block';
				t.getElem('tracking_legend_formular').style.display = 'none';
				break;
			
			case 'formular':
				t.getElem('tracking_legend_newsletter').style.display = 'none';
				t.getElem('tracking_legend_formular').style.display = 'block';
				break;
		}
	},
	
	
	/// Private Functions
	__loadValues : function() {
		var t = this, ed = tinyMCEPopup.editor, dom = ed.dom, e;
		var f = document.forms[0], attr, fld, val, n;
		var dfrm = dom.select('dedtform')[0];
		if (!dfrm) {
			ed.execCommand('mceDelightFormularCreateconfig', true, {});
			dfrm = dom.select('dedtform')[0];
		}
		e = ed.selection.select(dfrm);
		attr = e.attributes;

		for (var i = 0; i < attr.length; i++) {
			n = attr[i].nodeName;
			val = attr[i].nodeValue;
			fld = f.elements['fld_' + n];
			if (fld) {
				val = val.replace(/##39;/g, '\'');
				val = val.replace(/##34;/g, '"');
				val = val.replace(/\+/g, ' ');
				val = val.replace(/##10;/g, "\n");
				val = val.replace(/##43;/g, '+');
				t.__setFieldValue(fld, val);
			}
		}
		// Set a Name if there is none
		val = f.elements.fld_name.value.replace(/[^a-zA-Z0-9_-]+/g, '_');
		if (val.length <= 0) {
			val = new Date();
			f.elements.fld_name.value = 'Formular_' + val.getTime();
		}
	},
	
	__setFieldValue : function(f, v) {
		switch (f.nodeName) {
			case 'INPUT':
				f.value = v;
				break;
			case 'TEXTAREA':
				f.innerHTML = v;
				break;
			case 'SELECT':
				for (var i = 0; i < f.options.length; i++) {
					f.options[i].selected = (f.options[i].value == v);
				}
				break;
		}
	},
	
	__loadEmailFields : function() {
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
	
};

tinyMCEPopup.onInit.add(DelightConfigFieldDialog.init, DelightConfigFieldDialog);

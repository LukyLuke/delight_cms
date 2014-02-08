/**
 * $Id: control_radio_src.js,v 1.1.1.1 2009/01/07 20:08:58 lukas Exp $
 *
 * @author Lukas Zurschmiede
 * @copyright Copyright © 2007 delight software gmbh. All rights reserved.
 */

tinyMCEPopup.requireLangPack();

var DelightRadioFieldDialog = {
	init : function() {
		var t = this;
		tinyMCEPopup.resizeToInnerSize();
		
		if (t.__isValidSelection()) {
			t.__loadValues();
		} else {
			t.changeMode(false);
		}
	},

	close : function() {
		var e = tinyMCEPopup.editor.selection.getNode();
		
		// Change the name temporarly
		if (this.__isValidSelection()) {
			e.name = e.name.replace(/selected_/, '');
		}
		tinyMCEPopup.close();
	},

	insert : function() {
		var t = this, ed = tinyMCEPopup.editor, f = document.forms[0];
		// Insert the contents from the input into the document
		o = {
			replace : t.__isValidSelection(),
			name : f.rd_new.checked ? 'rd_' + f.ed_name.value : f.cb_name.value,
			description : f.ed_desc.value,
			value : f.ed_value.value,
			defaultValue : f.cb_default.value,
			type : 'radio',
			validation : f.chk_valid_mandatory.checked ? 'mandatory' : ''
		};

		if (o.name == 'rd_') {
			tinyMCEPopup.editor.windowManager.alert('delightform.namemandatory');
			return false;
			
		} else if (!t.__isUniqueName(o.name)) {
			tinyMCEPopup.editor.windowManager.alert('delightform.nameunique');
			return false;
		}
		
		if (o.replace) {
			t.__replaceElement(o);
		} else {
			t.__insertElement(o);
		}

		// Close the Editor
		tinyMCEPopup.close();
	},
	
	getElem : function(id) {
		return document.getElementById(id);
	},
	
	doChangeMode : function() {
		this.changeMode(this.getElem('rd_old').checked);
	},
	
	changeMode : function(existing) {
		var t = this;
		if (existing) {
			t.getElem('new_name_span').style.display = 'none'
			t.getElem('old_name_span').style.display = '';
			t.getElem('default_value_tr').style.display = ''
			t.__loadAllExistentRadios();
			t.loadRadioBoxes();
		} else {
			t.getElem('old_name_span').style.display = 'none';
			t.getElem('new_name_span').style.display = '';
			t.getElem('default_value_tr').style.display = 'none'
		}
		t.getElem('rd_old').checked = existing;
		t.getElem('rd_new').checked = !existing;
	},
	
	loadRadioBoxes : function() {
		var t = this, ed = tinyMCEPopup.editor, e = ed.selection.getNode(), sel = t.getElem('cb_default');
		var f = document.forms[0], n = f.rd_new.checked ? f.ed_name.value : f.cb_name.value;
		var cv = t.getElem('ed_value').value, il = ed.getDoc().getElementsByTagName('img'), r;

		// clear the select box with default values and insert special items
		while (sel.options.length > 0) { sel.remove(0); }
		t.__addDefaultSelectOption('none', ed.translate('{#delightform.radio_default_none}'));
		t.__addDefaultSelectOption('current', ed.translate('{#delightform.radio_default_this}'));

		// insert all other items appart from the current value (which is loaded in the edit control
		// ed_value and thus can be changed by the user, and thus we can't rely on identifying
		// the radiobutton by it)
		for (var i = 0; i < il.length; i++) {
			r = il[i];
			p = tinymce.util.JSON.parse('{' + r.getAttribute('title') + '}');
			// add everything but current value
			if ((p.type == 'radio') && (p.name == n) && (p['value'] != cv)) {
				t.__addDefaultSelectOption(p['value'], p.description);
			}
		}
		t.__selectDefaultSelectOption(n); //must be called after setting ed_value
	},


	/// Private functions
	
	__loadAllExistentRadios : function() {
		var t = this, ed = tinyMCEPopup.editor, il = ed.getDoc().getElementsByTagName('img'), r;
		var nl = t.getElem('cb_name'), opt, list = '';
		for (var i = 0; i < il.length; i++) {
			r = il[i];
			p = tinymce.util.JSON.parse('{' + r.getAttribute('title') + '}');
			if ( (p.type == 'radio') && (list.indexOf('#' + p.name + '#') < 0) ) {
				list += '#' + p.name + '#';
				opt = document.createElement('option');
				opt.text = p.name.replace(/rd_/, '');
				opt.value = p.name;
				try { nl.add(opt, null); } catch(e) { nl.add(opt); };
			}
		}
		t.loadRadioBoxes();
	},
	
	__addDefaultSelectOption : function(val, txt) {
		var t = this, sel = t.getElem('cb_default'), o = document.createElement('option');
		for (var i = 0; i < sel.options.length; i++) {
			if (sel.options[i].value == val) return false;
		}
		o.text = txt + ' (' + val + ')';
		o.value = val;
		try { sel.add(o, null); } catch(e) { sel.add(o) };
		return true;
	},
	
	__selectDefaultSelectOption : function(n) {
		var t = this, ed = tinyMCEPopup.editor, e = ed.selection.getNode(), sv='';
		var f = document.forms[0], sel = f.cb_default, il = ed.getDoc().getElementsByTagName('img'), r, p;

		for (var i = 0; i < il.length; i++) {
			r = il[i];
			p = tinymce.util.JSON.parse('{' + r.getAttribute('title') + '}');
			if ((p.type == 'radio') && (p.name == n) && (r.src.indexOf('radio.gif') > 1)) {
				sv = p['value'];
				break;
			}
		}
		p = tinymce.util.JSON.parse('{' + e.getAttribute('title') + '}');
		sv = (sv == p.value) ? 'current' : sv;
		for (var i = 0; i < sel.options.length; i++) {
			if (sel.options[i].value == sv) {
				sel.options[i].selected = true;
			} else {
				sel.options[i].selected = false;
			}
		}
		return true;
	},
	
	__isValidSelection : function() {
		var e = tinyMCEPopup.editor.selection.getNode();
		p = tinymce.util.JSON.parse('{' + e.getAttribute('title') + '}');
		return ( (e.nodeName == 'IMG') && (p.type == 'radio') );
	},
	
	__isUniqueName : function(n) {
		return true;
	},

	__loadValues : function() {
		var t = this, ed = tinyMCEPopup.editor, dom = ed.dom, e = ed.selection.getNode();
		var f = document.forms[0], p = tinymce.util.JSON.parse('{' + e.getAttribute('title') + '}');

		// Select radio as "existent"
		t.changeMode(true);
		t.getElem('defined_radio_disable').style.display = 'none';
		
		// Check MandatoryType
		var i,c = p.validation.split(' ');
		if (p.mandatory) { c.push('mandatory'); }
		t.getElem('chk_valid_mandatory').checked = false;
		for (i = 0; i < c.length; i++) {
			switch (c[i]) {
				case 'mandatory': t.getElem('chk_valid_mandatory').checked = true; break;
			}
		}

		// Set name, title, value and size - remove prefix from name
		t.getElem('ed_name').value = p.name.replace(/^rd_/, '');
		t.getElem('ed_desc').value = p.description || '';
		t.getElem('ed_value').value = p.value || '';

		// Change the name temporarly
		e.name = 'selected_' + p.name;
	},
	
	__replaceElement : function(o) {
		var t = this, ed = tinyMCEPopup.editor, e = ed.selection.getNode();
		var enc = tinymce.util.JSON.serialize(o).replace(/[{}]/g, '');

		e.setAttribute('mce_fname', '');
		e.removeAttribute('mce_fname');
		e.setAttribute('title', enc);

		t.__selectDefaultCheckedRadio(o.name, o.defaultValue, e.id);
	},
	
	__insertElement : function(o) {
		var t = this, ed = tinyMCEPopup.editor, html, url = tinyMCEPopup.getWindowArg('plugin_url');
		var enc = tinymce.util.JSON.serialize(o).replace(/[{}]/g, '');

		html = '<img src="' + url + '/img/radio' + (o.checked ? '' : '_blank') + '.gif" id="' + o.name + t.getElem('cb_name').options.length + '" class="mceplgItemRadio" />';

		ed.execCommand('mceInsertContent', false, html);
		ed.getDoc().getElementById(o.name + t.getElem('cb_name').options.length).title = enc;

		t.__selectDefaultCheckedRadio(o.name, (o.defaultValue == 'current') ? o.value : o.defaultValue, o.name + t.getElem('cb_name').options.length );
	},
	
	__selectDefaultCheckedRadio : function(n, v, id) {
		var t = this, ed = tinyMCEPopup.editor, il = ed.getDoc().getElementsByTagName('img'), r, url = tinyMCEPopup.getWindowArg('plugin_url'), p;
		for (var i = 0; i < il.length; i++) {
			r = il[i];
			p = tinymce.util.JSON.parse('{' + r.getAttribute('title') + '}')
			if (p.type && (p.type == 'radio') && (p.name == n) ) {
				r.src = url + '/img/radio_blank.gif';
				if ( (p.value == v) || ( (r.id == id) && (v == 'current') ) ) {
					r.src = url + '/img/radio.gif';
				}
			}
		}
	}
};

tinyMCEPopup.onInit.add(DelightRadioFieldDialog.init, DelightRadioFieldDialog);

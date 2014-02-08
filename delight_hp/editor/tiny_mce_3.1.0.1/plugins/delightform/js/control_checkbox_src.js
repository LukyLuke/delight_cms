/**
 * $Id: control_checkbox_src.js,v 1.1.1.1 2009/01/07 20:08:58 lukas Exp $
 *
 * @author Lukas Zurschmiede
 * @copyright Copyright © 2007 delight software gmbh. All rights reserved.
 */

tinyMCEPopup.requireLangPack();

var DelightCheckboxFieldDialog = {
	init : function() {
		var t = this;
		tinyMCEPopup.resizeToInnerSize();
		
		if (t.__isValidSelection()) {
			t.__loadValues();
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
			name : 'chk_' + f.ed_name.value,
			description : f.ed_desc.value,
			value : f.ed_value.value,
			checked : f.chk_checked.checked,
			type : 'checkbox',
			validation : f.chk_valid_mandatory.checked ? 'mandatory' : ''
		};

		if (o.name == 'ed_') {
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


	/// Private functions
	__isValidSelection : function() {
		var e = tinyMCEPopup.editor.selection.getNode();
		p = tinymce.util.JSON.parse('{' + e.getAttribute('title') + '}');
		return ( (e.nodeName == 'IMG') && (p.type == 'checkbox') );
	},
	
	__isUniqueName : function(n) {
		var il = tinyMCEPopup.editor.getDoc().getElementsByTagName('img'), c;
		for (var i = 0; i < il.length; i++) {
			r = il[i];
			if (r.hasAttribute('mce_fname') && (r.getAttribute('mce_fname') == n)) {
				return true;
			}
		}
		return true;
	},

	__loadValues : function() {
		var t = this, ed = tinyMCEPopup.editor, dom = ed.dom, e = ed.selection.getNode();
		var f = document.forms[0], p = tinymce.util.JSON.parse('{' + e.getAttribute('title') + '}');

		// Check Mandatory and Checked
		var i,c = p.validation.split(' ');
		if (p.mandatory) { c.push('mandatory'); }
		t.getElem('chk_valid_mandatory').checked = false;
		for (i = 0; i < c.length; i++) {
			switch (c[i]) {
				case 'mandatory': t.getElem('chk_valid_mandatory').checked = true; break;
			}
		}
		t.getElem('chk_checked').checked = p.checked;

		// Set name, title, value and size - remove prefix from name
		t.getElem('ed_name').value = p.name.replace(/^chk_/, '');
		t.getElem('ed_desc').value = p.description || '';
		t.getElem('ed_value').value = p.value || '';
		t.getElem('chk_checked').checked = (e.src.indexOf('_blank.gif') < 0);

		// Change the name temporarly
		e.name = 'selected_' + p.name;
	},
	
	__replaceElement : function(o) {
		var t = this, ed = tinyMCEPopup.editor, e = ed.selection.getNode(), url = tinyMCEPopup.getWindowArg('plugin_url');
		var enc = tinymce.util.JSON.serialize(o).replace(/[{}]/g, '');
		
		e.setAttribute('mce_fname', '');
		e.removeAttribute('mce_fname');
		e.setAttribute('title', enc);
		
		if (o.checked) {
			e.setAttribute('src', url + '/img/checkbox.gif');
		} else {
			e.setAttribute('src', url + '/img/checkbox_blank.gif');
		}
	},
	
	__insertElement : function(o) {
		var t = this, ed = tinyMCEPopup.editor, html, url = tinyMCEPopup.getWindowArg('plugin_url');
		var enc = tinymce.util.JSON.serialize(o).replace(/[{}]/g, '');
		
		html = '<img src="' + url + '/img/checkbox' + (o.checked ? '' : '_blank') + '.gif" id="' + o.name + '" class="mceplgItemCheckbox" />';
		
		ed.execCommand('mceInsertContent', false, html);
		ed.getDoc().getElementById(o.name).title = enc;
	}
};

tinyMCEPopup.onInit.add(DelightCheckboxFieldDialog.init, DelightCheckboxFieldDialog);

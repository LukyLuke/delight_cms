/**
 * $Id: control_edit_src.js,v 1.1.1.1 2009/01/07 20:08:57 lukas Exp $
 *
 * @author Lukas Zurschmiede
 * @copyright Copyright © 2007 delight software gmbh. All rights reserved.
 */

tinyMCEPopup.requireLangPack();

var DelightEditFieldDialog = {
	init : function() {
		var t = this;
		tinyMCEPopup.resizeToInnerSize();
		
		if (t.__isValidSelection()) {
			t.__loadValues();
		} else {
			t.switchSingleLine(true);
			selectOption('cb_type', 'text');
		}
	},

	close : function() {
		var e = tinyMCEPopup.editor.selection.getNode();
		
		// Change the name temporarly
		if (this.__isValidSelection()) {
			e.setAttribute('mce_fname', e.getAttribute('mce_fname').replace(/selected_/, ''));
		}
		tinyMCEPopup.close();
	},

	insert : function() {
		var t = this, ed = tinyMCEPopup.editor, f = document.forms[0];
		// Insert the contents from the input into the document
		o = {
			replace : t.__isValidSelection(),
			type : f.cb_type.value,
			name : 'ed_' + f.ed_name.value,
			description : f.ed_desc.value,
			size : parseInt(f.ed_size.value),
			rows : parseInt(f.ed_rows.value),
			validation : f.cb_validation.value,
			value : f.ed_value.value,
			mandatory : f.chk_valid_mandatory.checked
		};

		if (o.name == 'ed_') {
			tinyMCEPopup.editor.windowManager.alert('delightform.namemandatory');
			return false;
			
		} else if (!t.__isUniqueName(o.name)) {
			tinyMCEPopup.editor.windowManager.alert('delightform.nameunique');
			return false;
		}
		
		if (o.size <= 0) { o.size = 10; }
		if (o.rows <= 0) { o.rows = 10; }
		
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
	
	switchSingleLine : function(is) {
		if (is) {
			this.getElem('rows').style.display = 'none';
		} else {
			this.getElem('rows').style.display = '';
		}
	},


	/// Private functions
	__isValidSelection : function() {
		var ed = tinyMCEPopup.editor, n = ed.selection.getNode(), p;
		if (n.nodeName && (n.nodeName == 'IMG')) {
			p = tinymce.util.JSON.parse('{' + n.getAttribute('title') + '}');
			return (p.type && (tinymce.inArray(['text', 'file', 'password','textarea','multi'], p.type) >= 0));
		}
		return false;
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
		var f = document.forms[0], dim = tinymce.DOM.getRect(e);
		var p = tinymce.util.JSON.parse('{' + e.getAttribute('title') + '}');
		var single = (p.type != 'multi');

		// Check for FieldType
		t.switchSingleLine(single);
		selectOption('cb_type', single ? p.type : 'multi');
		f.cb_type.disabled = true;

		// Check MandatoryType
		var i,c = p.validation.split(' ');
		if (p.mandatory) { c.push('mandatory'); }
		t.getElem('chk_valid_mandatory').checked = false;
		for (i = 0; i < c.length; i++) {
			switch (c[i]) {
				case 'mandatory': t.getElem('chk_valid_mandatory').checked = true; break;
				case 'number': selectOption('cb_validation', 'number'); break;
				case 'email': selectOption('cb_validation', 'email'); break;
			}
		}

		// Set name, title, value and size - remove prefix from name
		t.getElem('ed_name').value = p.name.replace(/^ed_/, '');
		t.getElem('ed_desc').value = p.description || '';
		t.getElem('ed_value').value = p.value || '';
		t.getElem('ed_size').value = p.size;
		t.getElem('ed_rows').value = p.rows;

		// Change the name temporarly
		e.setAttribute('mce_fname', 'selected_' + p.name);
	},
	
	__replaceElement : function(o) {
		var t = this, ed = tinyMCEPopup.editor, e = ed.selection.getNode(), url = tinyMCEPopup.getWindowArg('plugin_url');
		var enc = tinymce.util.JSON.serialize(o).replace(/[{}]/g, '');
		
		e.setAttribute('id', o.name);
		e.setAttribute('mce_fname', '');
		e.removeAttribute('mce_fname');
		e.setAttribute('title', enc);
		e.style.width = o.size + 'px';
		if (o.type == 'multi') {
			e.style.height = o.rows + 'px';
		} else {
			e.style.height = '12pt';
		}
	},
	
	__insertElement : function(o) {
		var t = this, ed = tinyMCEPopup.editor, html, url = tinyMCEPopup.getWindowArg('plugin_url');
		var enc = tinymce.util.JSON.serialize(o).replace(/[{}]/g, '');

		if (o.type == 'multi') {
			html = '<img src="' + url + '/img/spacer.gif" id="' + o.name + '" class="mceplgItemTextarea" style="height:' + (o.cols) + 'px;width:' + (o.size) + 'px;border:2px inset grey;" />';
		} else {
			html = '<img src="' + url + '/img/spacer.gif" id="' + o.name + '" class="mceplgItemInput" style="height:12pt;width:' + (o.size) + 'px;border:2px inset grey;" />';
		}
		
		ed.execCommand('mceInsertContent', false, html);
		ed.getDoc().getElementById(o.name).title = enc;
	}
};

tinyMCEPopup.onInit.add(DelightEditFieldDialog.init, DelightEditFieldDialog);

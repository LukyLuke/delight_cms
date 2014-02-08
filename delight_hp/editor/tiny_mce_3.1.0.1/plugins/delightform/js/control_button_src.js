/**
 * $Id: control_button_src.js,v 1.1.1.1 2009/01/07 20:08:57 lukas Exp $
 *
 * @author Lukas Zurschmiede
 * @copyright Copyright © 2007 delight software gmbh. All rights reserved.
 */

tinyMCEPopup.requireLangPack();

var DelightButtonFieldDialog = {
	init : function() {
		var t = this;
		tinyMCEPopup.resizeToInnerSize();
		
		if (t.__isValidSelection()) {
			t.__loadValues();
		} else {
			selectOption('cb_type', 'submit');
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
			name : 'btn_' + f.ed_name.value,
			title : f.ed_desc.value,
			value : f.ed_value.value
		};

		if (o.name == 'btn_') {
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
		var ed = tinyMCEPopup.editor, n = ed.selection.getNode(), p;
		if (n.nodeName && (n.nodeName == 'DIV')) {
			p = tinymce.util.JSON.parse('{' + n.getAttribute('title') + '}');
			return (p.type && (tinymce.inArray(['button','submit','reset'], p.type) >= 0));
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
		var f = document.forms[0], v = e.innerHTML, p = tinymce.util.JSON.parse('{' + e.getAttribute('title') + '}');

		// Prepare the ButtonValue
		v = v.replace(/\<[^\>]*\>/g, '');
		
		// Set name, title, value and size - remove prefix from name
		selectOption('cb_type', p.type);
		t.getElem('ed_name').value = p.name.replace(/btn_/, '');
		t.getElem('ed_desc').value = p.title || '';
		t.getElem('ed_value').value = v;

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
		e.innerHTML = o.value;
		
		if (!e.nextSibling) {
			e.parentNode.appendChild(document.createTextNode(" "));
		}
	},
	
	__insertElement : function(o) {
		var t = this, ed = tinyMCEPopup.editor, html, url = tinyMCEPopup.getWindowArg('plugin_url');
		var enc = tinymce.util.JSON.serialize(o).replace(/[{}]/g, '');
		
		html = '<div id="' + o.name + '" class="mceplgItemButton"';
		html += ' style="float:none;display:block;text-align:center;padding:3px 10px;margin:1px 5px;font-weight:bold;border:2px outset grey;background:lightgrey;">' + o.value + '</div>&nbsp;';

		ed.execCommand('mceInsertContent', false, html);
		ed.getDoc().getElementById(o.name).title = enc;
	}
};

tinyMCEPopup.onInit.add(DelightButtonFieldDialog.init, DelightButtonFieldDialog);

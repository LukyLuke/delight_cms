/**
 * $Id: control_checkbox_src.js,v 1.1.1.1 2009/01/07 20:08:58 lukas Exp $
 *
 * @author Lukas Zurschmiede
 * @copyright Copyright � 2007 delight software gmbh. All rights reserved.
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
			e.setAttribute('mce_fname', e.getAttribute('mce_fname').replace(/selected_/, ''));
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
		var n = tinyMCEPopup.editor.selection.getNode();
		return (n.nodeName && ( (n.nodeName == 'INPUT') && (n.getAttribute('type').indexOf('checkbox') == 0) ));
	},
	
	__isUniqueName : function(n) {
		var found = false;
		var input = tinyMCEPopup.editor.getDoc().getElementsByTagName('input');
		var select = tinyMCEPopup.editor.getDoc().getElementsByTagName('select');
		var textarea = tinyMCEPopup.editor.getDoc().getElementsByTagName('textarea');
		
		tinymce.each(input, function(s) {
			if (s.hasAttribute('name') && (s.getAttribute('name') == n) && !s.hasAttribute('mce_fname') ) {
				found = true;
			}
		});
		tinymce.each(textarea, function(s) {
			if (s.hasAttribute('name') && (s.getAttribute('name') == n) && !s.hasAttribute('mce_fname') ) {
				found = true;
			}
		});
		tinymce.each(select, function(s) {
			if (s.hasAttribute('name') && (s.getAttribute('name') == n) && !s.hasAttribute('mce_fname') ) {
				found = true;
			}
		});

		return !found;
	},

	__loadValues : function() {
		var t = this, ed = tinyMCEPopup.editor, dom = ed.dom, n = ed.selection.getNode();
		var f = document.forms[0];

		// Check Mandatory and Checked
		var i,c = n.className.split(' ');
		t.getElem('chk_valid_mandatory').checked = false;
		for (i = 0; i < c.length; i++) {
			switch (c[i]) {
				case 'mandatory': t.getElem('chk_valid_mandatory').checked = true; break;
			}
		}
		t.getElem('chk_checked').checked = n.checked;

		// Set name, title, value and size - remove prefix from name
		t.getElem('ed_name').value = n.getAttribute('name').replace(/^chk_/, '');
		t.getElem('ed_desc').value = n.getAttribute('title');
		t.getElem('ed_value').value = n.value;
		
		// Change the name temporarly
		n.setAttribute('mce_fname', 'selected_' + n.getAttribute('name'));
	},
	
	__replaceElement : function(o) {
		var t = this, ed = tinyMCEPopup.editor, n = ed.selection.getNode();
		
		n.setAttribute('id', o.name);
		n.setAttribute('name', o.name);
		n.setAttribute('mce_fname', '');
		n.removeAttribute('mce_fname');
		n.setAttribute('title', o.description);
		n.checked = o.checked;
	},
	
	__insertElement : function(o) {
		var t = this, ed = tinyMCEPopup.editor, html;
		
		html = '<input type="checkbox" id="' + o.name + '" name="' + o.name + '" title="' + o.description+ '" value="'+o.value+'" class="checkbox' + (o.mandatory ? ' mandatory' : '') + '" '+(o.checked ? 'checked="checked" ' : '')+'/>';
		ed.execCommand('mceInsertContent', false, html);
	}
};

tinyMCEPopup.onInit.add(DelightCheckboxFieldDialog.init, DelightCheckboxFieldDialog);

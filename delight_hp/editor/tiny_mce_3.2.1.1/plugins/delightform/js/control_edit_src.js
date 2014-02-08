/**
 * $Id: control_edit_src.js,v 1.1.1.1 2009/01/07 20:08:57 lukas Exp $
 *
 * @author Lukas Zurschmiede
 * @copyright Copyright (c) 2007 delight software gmbh. All rights reserved.
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
		
		if (o.size <= 0) { o.size = 200; }
		if (o.rows <= 0) { o.rows = 100; }
		
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
		var n = tinyMCEPopup.editor.selection.getNode();
		return (n.nodeName && ( ((n.nodeName == 'INPUT') && ( (n.getAttribute('type').indexOf('text') == 0) || (n.getAttribute('type').indexOf('password') == 0) ) ) || (n.nodeName == 'TEXTAREA') ));
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
		var f = document.forms[0], single = (n.nodeName == 'INPUT');

		// Check for FieldType
		t.switchSingleLine(single);
		selectOption('cb_type', single ? n.getAttribute('type') : 'multi');
		f.cb_type.disabled = true;

		// Check MandatoryType
		var i,c = n.className.split(' ');
		t.getElem('chk_valid_mandatory').checked = false;
		for (i = 0; i < c.length; i++) {
			switch (c[i]) {
				case 'mandatory': t.getElem('chk_valid_mandatory').checked = true; break;
				case 'number': selectOption('cb_validation', 'number'); break;
				case 'email': selectOption('cb_validation', 'email'); break;
			}
		}

		// Set name, title, value and size - remove prefix from name
		t.getElem('ed_name').value = n.getAttribute('name').replace(/^ed_/, '');
		t.getElem('ed_desc').value = n.getAttribute('title');
		t.getElem('ed_value').value = n.value;
		t.getElem('ed_size').value = n.style.width.replace(/px/, '');
		t.getElem('ed_rows').value = n.style.height.replace(/px/, '');

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
		n.style.width = o.size + 'px';
		if (o.type == 'multi') {
			n.style.height = o.rows + 'px';
		}
		n.className = (o.validation!='text' ? o.validation : '') + (o.mandatory ? ' mandatory' : '');
	},
	
	__insertElement : function(o) {
		var t = this, ed = tinyMCEPopup.editor, html;

		if (o.type == 'multi') {
			html = '<textarea name="' + o.name + '" title="' + o.description + '" id="' + o.name + '" class="' + (o.validation!='text' ? o.validation : '') + (o.mandatory ? ' mandatory' : '') + '" style="height:' + (o.cols) + 'px;width:' + (o.size) + 'px;">' + o.value + '</textarea>';
		} else {
			html = '<input type="' + o.type + '" name="' + o.name + '" value="' + o.value + '" title="' + o.description + '" id="' + o.name + '" class="' + (o.validation!='text' ? o.validation : '') + (o.mandatory ? ' mandatory' : '') + '" style="width:' + (o.size) + 'px;" />';
		}
		ed.execCommand('mceInsertContent', false, html);
	}
};

tinyMCEPopup.onInit.add(DelightEditFieldDialog.init, DelightEditFieldDialog);

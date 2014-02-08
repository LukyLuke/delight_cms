/**
 * $Id: control_button_src.js,v 1.1.1.1 2009/01/07 20:08:57 lukas Exp $
 *
 * @author Lukas Zurschmiede
 * @copyright Copyright (c) 2001-2011, delight software gmbh. All rights reserved.
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
		tinyMCEPopup.close();
	},

	insert : function() {
		var t = this, ed = tinyMCEPopup.editor, f = document.forms[0];
		// Insert the contents from the input into the document
		o = {
			replace : t.__isValidSelection(),
			type : f.cb_type.value,
			value : f.ed_value.value
		};

		if (o.name == 'btn_') {
			tinyMCEPopup.editor.windowManager.alert('delightform.namemandatory');
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
		return (n.nodeName && (n.nodeName == 'BUTTON'));
	},

	__loadValues : function() {
		var t = this, ed = tinyMCEPopup.editor, dom = ed.dom, n = ed.selection.getNode();
		var f = document.forms[0];

		// Set value and type
		selectOption('cb_type', n.getAttribute('type'));
		t.getElem('ed_value').value = n.innerHTML;
	},
	
	__replaceElement : function(o) {
		var t = this, ed = tinyMCEPopup.editor, e = ed.selection.getNode();
		e.innerHTML = o.value;
		e.setAttribute('type', o.type);
		e.className = 'button_'+o.type;
	},
	
	__insertElement : function(o) {
		var t = this, ed = tinyMCEPopup.editor, html;
		
		html = '<button id="' + ed.dom.uniqueId('form_') + '" class="button_'+o.type+'" type="'+o.type+'">'+o.value+'</button>';
		ed.execCommand('mceInsertContent', false, html);
	}
};

tinyMCEPopup.onInit.add(DelightButtonFieldDialog.init, DelightButtonFieldDialog);

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
		var ed = tinyMCEPopup.editor, n = ed.selection.getNode();
		
		// Fix for #416766 - see https://bugzilla.mozilla.org/show_bug.cgi?id=416766
		// on Firefox select, radio and checkbox cannot be selected
		if (tinymce.isGecko) {
			n = ed.dom.get(ed.dom.getAttrib(n, 'id').replace(/img_/, ''));
		}
		
		// Change the name temporarly
		if (this.__isValidSelection()) {
			n.setAttribute('mce_fname', n.getAttribute('mce_fname').replace(/selected_/, ''));
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
		var ed = tinyMCEPopup.editor, n = ed.selection.getNode();
		
		// Fix for #416766 - see https://bugzilla.mozilla.org/show_bug.cgi?id=416766
		// on Firefox select, radio and checkbox cannot be selected
		if (tinymce.isGecko) {
			n = ed.dom.get(ed.dom.getAttrib(n, 'id').replace(/img_/, ''));
		}
		
		return (n.nodeName && ( (n.nodeName == 'INPUT') && (n.getAttribute('type').indexOf('checkbox') == 0) ));
	},
	
	__isUniqueName : function(n) {
		var found = false;
		var input = tinyMCEPopup.editor.getDoc().getElementsByTagName('input');
		var select = tinyMCEPopup.editor.getDoc().getElementsByTagName('select');
		var textarea = tinyMCEPopup.editor.getDoc().getElementsByTagName('textarea');
		
		tinymce.each(input, function(s) {
			if ((s.getAttribute('name') == n) && ((s.getAttribute('mce_fname')==null)||(s.getAttribute('mce_fname')=='')) ) { // IE still doesn't support s.hasAttribute('mce_fname') and if we extend s width Element.extend(s) it shows an other strange error
				found = true;
			}
		});
		tinymce.each(textarea, function(s) {
			if ((s.getAttribute('name') == n) && ((s.getAttribute('mce_fname')==null)||(s.getAttribute('mce_fname')=='')) ) { // IE still doesn't support s.hasAttribute('mce_fname') and if we extend s width Element.extend(s) it shows an other strange error
				found = true;
			}
		});
		tinymce.each(select, function(s) {
			if ((s.getAttribute('name') == n) && ((s.getAttribute('mce_fname')==null)||(s.getAttribute('mce_fname')=='')) ) { // IE still doesn't support s.hasAttribute('mce_fname') and if we extend s width Element.extend(s) it shows an other strange error
				found = true;
			}
		});

		return !found;
	},

	__loadValues : function() {
		var t = this, ed = tinyMCEPopup.editor, dom = ed.dom, n = ed.selection.getNode();
		var f = document.forms[0];
		
		// Fix for #416766 - see https://bugzilla.mozilla.org/show_bug.cgi?id=416766
		// on Firefox select, radio and checkbox cannot be selected
		if (tinymce.isGecko) {
			n = ed.dom.get(ed.dom.getAttrib(n, 'id').replace(/img_/, ''));
		}

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
		
		// Fix for #416766 - see https://bugzilla.mozilla.org/show_bug.cgi?id=416766
		// on Firefox select, radio and checkbox cannot be selected
		if (tinymce.isGecko) {
			n = ed.dom.get(ed.dom.getAttrib(n, 'id').replace(/img_/, ''));
		}
		
		n.setAttribute('name', o.name);
		n.setAttribute('mce_fname', '');
		n.removeAttribute('mce_fname');
		n.setAttribute('title', o.description);
		n.checked = o.checked;
		if (o.checked) {
			n.setAttribute('checked', 'checked');
		} else {
			n.setAttribute('checked', '');
			n.removeAttribute('checked');
		}
	},
	
	__insertElement : function(o) {
		var t = this, ed = tinyMCEPopup.editor, id = ed.dom.uniqueId('form_'), html = '';
		
		html = '<input type="checkbox" id="'+id+'" name="'+o.name+'" title="'+o.description+'" value="'+o.value+'" class="checkbox'+(o.mandatory ? ' mandatory' : '')+'" '+(o.checked ? 'checked="checked" ' : '')+'/>';
		
		// Fix for #416766 - see https://bugzilla.mozilla.org/show_bug.cgi?id=416766
		// on Firefox select, radio and checkbox cannot be selected
		if (tinymce.isGecko) {
			var url = tinyMCEPopup.getWindowArg('plugin_url');
			html = html.replace(/^\<(input|select)/, '<$1 style="display:none;"');
			html += '<img id="img_'+id+'" src="'+url+'/img/checkbox'+(o.checked ? '' : '_blank')+'.gif" mce_type="checkbox" mce_id="'+id+'" class="mceItem mceDelightForm" style="border:none;margin:0;padding:0;vertical-align:middle;" />';
		}
		
		ed.execCommand('mceInsertContent', false, html);
	}
};

tinyMCEPopup.onInit.add(DelightCheckboxFieldDialog.init, DelightCheckboxFieldDialog);

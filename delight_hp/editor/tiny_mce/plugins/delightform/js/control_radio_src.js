/**
 * $Id: control_radio_src.js,v 1.1.1.1 2009/01/07 20:08:58 lukas Exp $
 *
 * @author Lukas Zurschmiede
 * @copyright Copyright � 2007 delight software gmbh. All rights reserved.
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
			name : 'rd_' + (f.rd_new.checked ? f.ed_name.value : f.cb_name.value),
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
			t.getElem('new_name_span').style.display = 'none';
			t.getElem('old_name_span').style.display = '';
			t.getElem('default_value_tr').style.display = '';
			t.loadExistentRadioBoxes();
		} else {
			t.getElem('old_name_span').style.display = 'none';
			t.getElem('new_name_span').style.display = '';
			t.getElem('default_value_tr').style.display = 'none';
		}
		t.getElem('rd_old').checked = existing;
		t.getElem('rd_new').checked = !existing;
	},
	
	loadExistentRadioBoxes : function() {
		var t = this, ed = tinyMCEPopup.editor, sel = t.getElem('cb_name'), list = '';
		var n = ed.selection.getNode(), name = '';
		
		// Fix for #416766 - see https://bugzilla.mozilla.org/show_bug.cgi?id=416766
		// on Firefox select, radio and checkbox cannot be selected
		if (tinymce.isGecko) {
			n = ed.dom.get(ed.dom.getAttrib(n, 'id').replace(/img_/, ''));
		}
		
		if (t.__isValidSelection()) {
			name = n.getAttribute('name');
		}

		// clear the select box with default values and insert special items
		while (sel.options.length > 0) { sel.remove(0); }

		// Add all radioboxes to the Name-List
		tinymce.each(ed.getDoc().getElementsByTagName('input'), function(item) {
			if (item.getAttribute('type').indexOf('radio') != 0) {
				return;
			}
			if (list.indexOf('#' + item.getAttribute('name') + '#') > 0) {
				return;
			}
			list += '#'+item.getAttribute('name')+'#';
			t.__addExistentSelectOption(item.getAttribute('name').replace(/rd_/,''), name);
		});
		t.loadRadioBoxes();
	},
	
	loadRadioBoxes : function() {
		var t = this, ed = tinyMCEPopup.editor, sel = t.getElem('cb_default');
		var n = ed.selection.getNode(), name = 'rd_'+t.getElem('cb_name').value, selected = 0, cnt=2;

		// Fix for #416766 - see https://bugzilla.mozilla.org/show_bug.cgi?id=416766
		// on Firefox select, radio and checkbox cannot be selected
		if (tinymce.isGecko) {
			n = ed.dom.get(ed.dom.getAttrib(n, 'id').replace(/img_/, ''));
		}
		
		// clear the select box with default values and insert special items
		while (sel.options.length > 0) { sel.remove(0); }
		t.__addDefaultSelectOption('none');
		t.__addDefaultSelectOption('current');

		// Add all radioboxes to the Name-List
		tinymce.each(ed.getDoc().getElementsByTagName('input'), function(item) {
			if (item.getAttribute('type').indexOf('radio') != 0) {
				return;
			}
			if (item.getAttribute('name') != name) {
				return;
			}
			selected = item.checked ? cnt : selected;
			cnt++;
			t.__addDefaultSelectOption(item.value);
		});
		
		// Select the default-selected
		sel.options[selected].selected = true;
	},


	/// Private functions
	
	__addDefaultSelectOption : function(val) {
		var t = this, ed = tinyMCEPopup.editor, sel = t.getElem('cb_default'), o;
		for (var i = 0; i < sel.options.length; i++) {
			if (sel.options[i].value == val) return false;
		}
		var txt = val;
		txt = txt == 'none' ? ed.translate('{#delightform.radio_default_none}') : txt;
		txt = txt == 'current' ? ed.translate('{#delightform.radio_default_this}') : txt;
		
		o = document.createElement('option');
		o.text = txt;
		o.value = val;
		try { sel.add(o, null); } catch(e) { sel.add(o); };
		return true;
	},
	
	__addExistentSelectOption : function(val, selected) {
		var t = this, sel = t.getElem('cb_name'), o = document.createElement('option');
		for (var i = 0; i < sel.options.length; i++) {
			if (sel.options[i].value == val) return false;
		}
		o.text = val;
		o.value = val;
		if ('rd_'+val == selected) {
			o.selected = true;
		}
		try { sel.add(o, null); } catch(e) { sel.add(o); };
		return true;
	},
	
	__isValidSelection : function() {
		var ed = tinyMCEPopup.editor, n = ed.selection.getNode();
		
		// Fix for #416766 - see https://bugzilla.mozilla.org/show_bug.cgi?id=416766
		// on Firefox select, radio and checkbox cannot be selected
		if (tinymce.isGecko) {
			n = ed.dom.get(ed.dom.getAttrib(n, 'id').replace(/img_/, ''));
		}
		
		return (n.nodeName && ( (n.nodeName == 'INPUT') && (n.getAttribute('type').indexOf('radio') == 0) ));
	},
	
	__isUniqueName : function(n) {
		return true;
		//if ((s.getAttribute('name') == n) && s.getAttribute('mce_fname')==null ) { // IE still doesn't support s.hasAttribute('mce_fname') and if we extend s width Element.extend(s) it shows an other strange error
	},

	__loadValues : function() {
		var t = this, ed = tinyMCEPopup.editor, dom = ed.dom, n = ed.selection.getNode();
		var f = document.forms[0];

		// Fix for #416766 - see https://bugzilla.mozilla.org/show_bug.cgi?id=416766
		// on Firefox select, radio and checkbox cannot be selected
		if (tinymce.isGecko) {
			n = ed.dom.get(ed.dom.getAttrib(n, 'id').replace(/img_/, ''));
		}
		
		// Select radio as "existent"
		t.changeMode(true);
		t.getElem('defined_radio_disable').style.display = 'none';
		
		// Check MandatoryType
		var i,c = n.className.split(' ');
		t.getElem('chk_valid_mandatory').checked = false;
		for (i = 0; i < c.length; i++) {
			switch (c[i]) {
				case 'mandatory': t.getElem('chk_valid_mandatory').checked = true; break;
			}
		}

		// Set name, title, value and size - remove prefix from name
		t.getElem('ed_name').value = n.getAttribute('name').replace(/^rd_/, '');
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

		t.__selectDefaultCheckedRadio(o.name, (o.defaultValue == 'current') ? o.value : o.defaultValue);
	},
	
	__insertElement : function(o) {
		var t = this, ed = tinyMCEPopup.editor, id = ed.dom.uniqueId('form_'), html = '';

		html = '<input type="radio" id="'+id+'" name="'+o.name+'" title="'+o.description+'" value="'+o.value+'" class="radio'+(o.mandatory ? ' mandatory' : '')+'" />';
		
		// Fix for #416766 - see https://bugzilla.mozilla.org/show_bug.cgi?id=416766
		// on Firefox select, radio and checkbox cannot be selected
		if (tinymce.isGecko) {
			var url = tinyMCEPopup.getWindowArg('plugin_url');
			html = html.replace(/^\<(input|select)/, '<$1 style="display:none;"');
			html += '<img id="img_'+id+'" src="'+url+'/img/radio'+(o.checked ? '' : '_blank')+'.gif" mce_type="radio" mce_id="'+id+'" class="mceItem mceDelightForm" style="border:none;margin:0;padding:0;vertical-align:middle;" />';
		}
		
		ed.execCommand('mceInsertContent', false, html);

		t.__selectDefaultCheckedRadio(o.name, (o.defaultValue == 'current') ? o.value : o.defaultValue);
	},
	
	__selectDefaultCheckedRadio : function(n, v) {
		var t = this, ed = tinyMCEPopup.editor, il = ed.getDoc().getElementsByTagName('input'), i;
		
		tinymce.each(il, function(item) {
			if (item.getAttribute('type').indexOf('radio') != 0) {
				return;
			}
			if (item.getAttribute('name') != n) {
				return;
			}
			item.checked = (item.value == v);
			if (item.value == v) {
				item.setAttribute('checked', 'checked');
			} else {
				item.setAttribute('checked', '');
				item.removeAttribute('checked');
			}
		});
	}
};

tinyMCEPopup.onInit.add(DelightRadioFieldDialog.init, DelightRadioFieldDialog);

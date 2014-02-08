/**
 * $Id: control_select_src.js,v 1.1.1.1 2009/01/07 20:08:57 lukas Exp $
 *
 * @author Lukas Zurschmiede
 * @copyright Copyright � 2007 delight software gmbh. All rights reserved.
 */

tinyMCEPopup.requireLangPack();

// initialize sorting - this variables must be globally available because we need them inside the JS-Sort-Function
var direction = 1;
var sortType = '';

var DelightSelectFieldDialog = {
	init : function() {
		var t = this;
		tinymce.dom.Event.add(window, 'resize', t.resizeWindow);
		tinyMCEPopup.resizeToInnerSize();
		
		if (t.__isValidSelection()) {
			t.__loadValues();
		}
		setTimeout(t.resizeWindow, 100);
	},

	resizeWindow : function(e) {
		var t = DelightSelectFieldDialog;
		var act = tinymce.DOM.getRect(t.getElem('mceActionPanel'));
		var fix = tinymce.DOM.getRect(t.getElem('mceFixed'));
		var res = tinymce.DOM.getRect(t.getElem('mceResize'));
		t.getElem('mceResize').style.height = (act.y - fix.y - fix.h - 13) + 'px';
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
			name : 'sel_' + f.ed_name.value,
			description : f.ed_desc.value,
			size : f.ed_size.value,
			values : []
		};
		
		tinymce.each(f.elements, function(elem) {
			var sel;
			if ( elem.name && (elem.name.substring(0, 16) == 'ed_option_value_') && (elem.value.length > 0) ) {
				sel = t.getElem('rd_default_' + parseInt(elem.name.replace(/ed_option_value_/, ''))).checked;
				o.values.push( [ elem.value, f[elem.name.replace(/value/, 'text')].value, sel ] );
			}
		});

		if (o.name == 'sel_') {
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
	
	addEmptyOption : function() {
		var t = this, table = t.getElem('options_table'), num = table.rows.length, tr = table.insertRow(num);
		// first line of table are titles, thus rows are indexed from 1
		var td0 = tr.insertCell(0);
		var td1 = tr.insertCell(1);
		var td2 = tr.insertCell(2);
		var td3 = tr.insertCell(3);
		td0.innerHTML = '<input type="radio" name="rd_default" value="' + num + '" id="rd_default_' + num + '"/>';
		td1.innerHTML = '<input type="text" id="ed_option_value_' + num + '" name="ed_option_value_' + num + '" class="small_input" onkeyup="validateName(this)"/>';
		td2.innerHTML = '<input type="text" id="ed_option_text_' + num + '" name="ed_option_text_' + num + '" class="medium_input"/>';
		td3.innerHTML = '<input type="text" id="ed_option_pr_' + num + '" name="ed_option_pr_' + num + '" value="' + num * 2 + '" class="small_input" onkeyup="validateNumber(this)"/>';
	},


	/**
	 * sort table lines by text
	 */
	sortText : function() {
		if (sortType == 'text') {
			direction = -direction;
		} else {
			direction = 1;
		}
		sortType = 'text';
		this.__doSort(this.__cmpText);
	},

	/**
	 * sort table lines by priority
	 */
	sortPriority : function() {
		if (sortType == 'priority') {
			direction = -direction;
		} else {
			direction = 1;
		}
		sortType = 'priority';
		this.__doSort(this.__cmpPriority);
	},

	/**
	 * sort table lines by value
	 */
	sortValue : function() {
		if (sortType == 'value') {
			direction = -direction;
		} else {
			direction = 1;
		}
		sortType = 'value';
		this.__doSort(this.__cmpValue);
	},


	/// Private functions
	
	/**
	 * Prepare the Options-Table and sort it after
	 */
	__doSort : function(sortProc) {
		var t = this, table = t.getElem('options_table'), num = table.rows.length, values = Array();
	
		// The first line of the table are titles, thus rows are indexed from 1
		// copy lines from the table into array of values (can't use sort directly on table.rows anyway)
		for (var i = 1; i < num; i++) {
			values[i-1] = [
				document.getElementById('ed_option_value_' + i).value,
				document.getElementById('ed_option_text_' + i).value,
				document.getElementById('ed_option_pr_' + i).value
			];
		}
	
		// Sort it by the procedure the user has clicked
		values.sort(sortProc);
	
		// The first line of the table are titles, thus rows are indexed from 1
		for (var i = 1; i < num; i++) {
			document.getElementById('ed_option_value_' + i).value = values[i-1][0];
			document.getElementById('ed_option_text_' + i).value = values[i-1][1];
			document.getElementById('ed_option_pr_' + i).value = values[i-1][2];
		}
	},

	/**
	 * compare by option names
	 */
	__cmpText : function(v1,v2) {
		if (v1[1] > v2[1]) {
			return direction;
		}
		if (v1[1] < v2[1]) {
			return direction * -1;
		}
		return 0;
	},

	/**
	 * compare by option values
	 */
	__cmpValue : function(v1,v2) {
		if (v1[0] > v2[0]) {
			return direction;
		}
		if (v1[0] < v2[0]) {
			return direction * -1;
		}
		return 0;
	},

	/**
	 * commmpare by option priorities
	 */
	__cmpPriority : function(v1,v2) {
		return direction * (parseFloat(v1[2]) - parseFloat(v2[2]));
	},
	
	__isValidSelection : function() {
		var ed = tinyMCEPopup.editor, n = ed.selection.getNode();
		
		// Fix for #416766 - see https://bugzilla.mozilla.org/show_bug.cgi?id=416766
		// on Firefox select, radio and checkbox cannot be selected
		if (tinymce.isGecko) {
			n = ed.dom.get(ed.dom.getAttrib(n, 'id').replace(/img_/, ''));
		}
		
		return (n.nodeName && (n.nodeName == 'SELECT'));
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
		
		// Fix for #416766 - see https://bugzilla.mozilla.org/show_bug.cgi?id=416766
		// on Firefox select, radio and checkbox cannot be selected
		if (tinymce.isGecko) {
			n = ed.dom.get(ed.dom.getAttrib(n, 'id').replace(/img_/, ''));
		}
		
		var f = document.forms[0], options = n.getElementsByTagName('option');

		// Check MandatoryType
		var i,c = n.className.split(' ');
		if (t.getElem('chk_valid_mandatory')) {
			t.getElem('chk_valid_mandatory').checked = false;
			for (i = 0; i < c.length; i++) {
				switch (c[i]) {
					case 'mandatory': t.getElem('chk_valid_mandatory').checked = true; break;
				}
			}
		}

		// Set name, title, value and size - remove prefix from name
		t.getElem('ed_name').value = n.getAttribute('name').replace(/^sel_/, '');
		t.getElem('ed_desc').value = n.getAttribute('title');
		t.getElem('ed_size').value = n.style.width.replace(/px/, '');
		
		// all options
		var tbl = t.getElem('options_table'), cnt = 1, len = tbl.rows.length;
		tinymce.each(options, function(opt) {
			
			// fill the table
			f['ed_option_value_' + cnt].value = opt.getAttribute('value');
			f['ed_option_text_' + cnt].value = opt.innerHTML;
			
			// renumber the values, so that the items can be swaped easily
			f['ed_option_pr_' + cnt].value = cnt * 2;
			t.getElem('rd_default_' + cnt).checked = opt.hasAttribute('selected');
			
			// add an empty line
			t.addEmptyOption();
			cnt++;
		});
		
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
		n.style.width = o.size + 'px';
		
		// remove all values
		while (n.childNodes.length > 0) {
			n.removeChild(n.childNodes[0]);
		}

		// Add all options
		tinymce.each(o.values, function(opt) {
			var op = document.createElement('option');
			op.setAttribute('value', opt[0]);
			op.innerHTML = opt[1];
			if (opt[2]) {
				op.setAttribute('selected', 'selected');
			}
			n.appendChild(op);
		});
		
		if (!n.nextSibling) {
			n.parentNode.appendChild(document.createTextNode(" "));
		}
	},
	
	__insertElement : function(o) {
		var t = this, ed = tinyMCEPopup.editor, v = o.values, id = ed.dom.uniqueId('form_'), html = '';
		
		html = '<select name="'+o.name+'" id="'+id+'" title="'+o.description+'" class="'+(o.mandatory ? 'mandatory' : '')+'" style="width:'+(o.size)+'px;">';
		for (var i = 0; i < v.length; i++) {
			html += '<option ' + (v[i][2] ? 'selected="selected"' : '') + ' value="' + v[i][0] + '">' + v[i][1] + '</option>';
		}
		html += '</select>';
		
		// Fix for #416766 - see https://bugzilla.mozilla.org/show_bug.cgi?id=416766
		// on Firefox select, radio and checkbox cannot be selected
		if (tinymce.isGecko) {
			var url = tinyMCEPopup.getWindowArg('plugin_url');
			html = html.replace(/style=(['"])/, 'style=$1 display:none;');
			html += '<img id="img_'+id+'" src="'+url+'/img/select_arrow.gif" mce_type="select" mce_id="'+id+'" class="mceItem mceDelightForm" style="border:1px solid black;margin:0;padding:0 0 0 '+(parseInt(o.size)-20)+'px;vertical-align:middle;" />';
		}
		
		ed.execCommand('mceInsertContent', false, html);
	}
};

tinyMCEPopup.onInit.add(DelightSelectFieldDialog.init, DelightSelectFieldDialog);

/**
 * $Id: control_select_src.js,v 1.1.1.1 2009/01/07 20:08:57 lukas Exp $
 *
 * @author Lukas Zurschmiede
 * @copyright Copyright � 2007 delight software gmbh. All rights reserved.
 */

tinyMCEPopup.requireLangPack();

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
		var n = tinyMCEPopup.editor.selection.getNode();
		
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
		td1.innerHTML = '<input type="text" id="ed_option_value_' + num + '" name="ed_option_value_' + num + '" class="small_input" onkeyup="validateName(this)"/>'
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
		var n = tinyMCEPopup.editor.selection.getNode();
		return (n.nodeName && (n.nodeName == 'SELECT'));
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
		var f = document.forms[0], options = n.getElementsByNodeName('option');

		// Check MandatoryType
		var i,c = n.className.split(' ');
		t.getElem('chk_valid_mandatory').checked = false;
		for (i = 0; i < c.length; i++) {
			switch (c[i]) {
				case 'mandatory': t.getElem('chk_valid_mandatory').checked = true; break;
			}
		}

		// Set name, title, value and size - remove prefix from name
		t.getElem('ed_name').value = n.getAttribute('name').replace(/^sel_/, '');
		t.getElem('ed_desc').value = n.getAttribute('title');
		t.getElem('ed_size').value = n.style.width.replace(/px/, '');
		
		// all options
		var tbl = t.getElem('options_table'), cnt = 1, len = rbl.rows.length;
		tinymce.each(options, function(opt) {
			
			// fill the table
			f['ed_option_value_' + cnt].value = opt.getAttribute('value');
			f['ed_option_text_' + cnt].value = opt.innerText;
			
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
		var t = this, ed = tinyMCEPopup.editor, e = ed.selection.getNode();
		
		e.setAttribute('id', o.name);
		e.setAttribute('name', o.name);
		e.setAttribute('mce_fname', '');
		e.removeAttribute('mce_fname');
		e.setAttribute('title', o.description);
		e.style.width = o.size + 'px';
		
		// remove all values
		tinymce.each(e.childNodes, function(opt) {
			e.removeChild(opt);
		});

		// Add all options
		tinymce.each(o.values, function(opt) {
			var n = document.createElement('option');
			n.setAttribute('value', opt[0]);
			n.innerHTML = opt[1];
			if (opt[2]) {
				n.setAttribute('selected', 'selected');
			}
			e.appendChild(n);
		});
		
		if (!e.nextSibling) {
			e.parentNode.appendChild(document.createTextNode(" "));
		}
	},
	
	__insertElement : function(o) {
		var t = this, ed = tinyMCEPopup.editor, html, v = o.values;
		
		html = '<select name="' + o.name + '" id="' + o.name + '" id="' + o.description + '" class="'+(o.mandatory ? 'mandatory' : '')+'" style="width:' + (o.size) + 'px;">';
		for (var i = 0; i < v.length; i++) {
			html += '<option ' + (v[i][2] ? 'selected="selected"' : '') + ' value="' + v[i][0] + '">' + v[i][1] + '</option>';
		}
		html += '</select>';

		ed.execCommand('mceInsertContent', false, html);
	}
};

tinyMCEPopup.onInit.add(DelightSelectFieldDialog.init, DelightSelectFieldDialog);

// initialize sorting
// this variables must be globally available because we need them inside the JS-Sort-Function
var direction = 1;
var sortType = '';

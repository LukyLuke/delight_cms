/**
 * $Id: control_config_src.js,v 1.1.1.1 2009/01/07 20:08:59 lukas Exp $
 *
 * @author Lukas Zurschmiede
 * @copyright Copyright (c) 2007 delight software gmbh. All rights reserved.
 */

tinyMCEPopup.requireLangPack();

var DelightConfigFieldDialog = {
	init : function() {
		var t = this;
		tinyMCEPopup.resizeToInnerSize();
		t.adminAction = tinyMCEPopup.getWindowArg('adminAction');
		t.adminAction = t.adminAction == null ? 100 : t.adminAction;
		
		t.showConfigTab('email');
		t.showTrackingSettings('newsletter');
		t.__loadEmailFields();
		t.__loadValues();
	},

	close : function() {
		tinyMCEPopup.close();
	},
	
	getElem : function(id) {
		return document.getElementById(id);
	},
	
	configureFormular : function() {
		var t = this,id = t.__getTextId(), f = document.forms[0], config = {};
		
		Form.getElements(f).each(function(item) {
			config[item.name.replace(/fld_/, '')] = item.value;
		});
		new Ajax.Request('/delight_hp/index.php', {
			method : 'post',
			parameters : {adm:t.adminAction, text:id, config:Object.toJSON(config), action:'set_formular_config'},
			onSuccess : function(transport) {
				var cont;
				try {
					cont = transport.responseText.evalJSON();
				} catch (e) {}
				if (cont && cont.success) {
					t.close();
				} else {
					//console.error(transport.responseText);
				}
			}
		});
	},
	
	showConfigTab : function(type) {
		var t = this;
		switch (type) {
			case 'email':
				t.getElem('options_tab_tracking').style.display = 'none';
				t.getElem('options_tab_email').style.display = 'block';
				break;
			
			case 'tracking':
				t.getElem('options_tab_tracking').style.display = 'block';
				t.getElem('options_tab_email').style.display = 'none';
				break;
		}
	},

	showTrackingSettings : function(type) {
		var t = this;
		switch (type) {
			case 'newsletter':
				t.getElem('tracking_legend_newsletter').style.display = 'block';
				t.getElem('tracking_legend_formular').style.display = 'none';
				break;
			
			case 'formular':
				t.getElem('tracking_legend_newsletter').style.display = 'none';
				t.getElem('tracking_legend_formular').style.display = 'block';
				break;
		}
	},
	
	
	/// Private Functions
	__getTextId : function() {
		return parseInt(tinyMCEPopup.editor.editorId.replace(/[^0-9]+/gi, ''));
	},
	__loadValues : function() {
		var t = this, id = t.__getTextId(), f = document.forms[0];
		new Ajax.Request('/delight_hp/index.php', {
			method : 'post',
			parameters : {adm:t.adminAction, text:id, action:'get_formular_config'},
			onSuccess : function(transport) {
				var cont = transport.responseText.evalJSON();
				for (k in cont) {
					if (k == 'method') {
						t.showConfigTab(cont[k]);
					} else if (k == 'tramodule') {
						t.showTrackingSettings(cont[k]);
					} else if ((k == 'traserver') && (cont[k].length <= 0)) {
						cont[k] = 'deponat.ch';
					}
					t.__setFieldValue(f.elements['fld_' + k], cont[k]);
				}
			}
		});
		
		// Set a FormularName if there is none
		val = f.elements.fld_name.value.replace(/[^a-zA-Z0-9_-]+/g, '_');
		if (val.length <= 0) {
			val = new Date();
			f.elements.fld_name.value = 'Formular_' + val.getTime();
		}
	},
	__setFieldValue : function(f, v) {
		if (!f) {
			return;
		}
		switch (f.nodeName) {
			case 'INPUT':
				f.value = v;
				break;
			case 'TEXTAREA':
				f.innerHTML = v;
				break;
			case 'SELECT':
				for (var i = 0; i < f.options.length; i++) {
					f.options[i].selected = (f.options[i].value == v);
				}
				break;
		}
	},
	
	__loadEmailFields : function() {
		var t = this, ed = tinyMCEPopup.editor;
		var input = tinyMCEPopup.editor.getDoc().getElementsByTagName('input');
		var elem = null;

		if ( (elem = document.getElementsByName('fld_mailsenderfield')) && (elem.length > 0) ) {
			elem = elem[0];
			tinymce.each(input, function(s) {
				if ((s.getAttribute('type') == 'text') && ed.dom.hasClass(s, 'email')) {
					var opt = document.createElement('option');
					opt.innerHTML = s.getAttribute('title');
					opt.setAttribute('value', s.getAttribute('name').replace(/ed_/, ''));
					elem.appendChild(opt);
				}
			});
		}

		if ( (elem = document.getElementsByName('fld_trasenderemail')) && (elem.length > 0) ) {
			elem = elem[0];
			tinymce.each(input, function(s) {
				if ((s.getAttribute('type') == 'text') && ed.dom.hasClass(s, 'email')) {
					var opt = document.createElement('option');
					opt.innerHTML = s.getAttribute('title');
					opt.setAttribute('value', s.getAttribute('name'));
					elem.appendChild(opt);
				}
			});
		}
		
		if ( (elem = document.getElementsByName('fld_mailrcptfield')) && (elem.length > 0) ) {
			elem = elem[0];
			tinymce.each(tinyMCEPopup.editor.getDoc().getElementsByTagName('select'), function(s) {
				var opt = document.createElement('option');
				opt.innerHTML = s.getAttribute('title');
				opt.setAttribute('value', s.getAttribute('name'));
				elem.appendChild(opt);
			});
		}
	}
	
};

tinyMCEPopup.onInit.add(DelightConfigFieldDialog.init, DelightConfigFieldDialog);

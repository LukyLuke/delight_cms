
var TemplateDialog = {
	preInit : function() {
		tinyMCEPopup.requireLangPack();
	},

	init : function(ed) {
		var ed = tinyMCEPopup.editor, dom = ed.dom;
		tinyMCEPopup.resizeToInnerSize();
		this.__loadTemplates();
	},

	doClose : function() {
		var ed = tinyMCEPopup.editor, f = document.forms[0], nl = f.elements, v, args = {}, el;

		tinyMCEPopup.restoreSelection();

		// Fixes crash in Safari
		if (tinymce.isWebKit)
			ed.getWin().focus();

		tinyMCEPopup.close();
	},
	
	changeTemplate : function(tpl) {
		var i, opt, optCont = document.getElementsByName('layout_options_div');
		if (optCont && (optCont.length > 0)) {
			for (i = 0; i < optCont.length; i++) {
				optCont[i].style.display = 'none';
			}
		}
		if (document.getElementById('layout_options_' + tpl)) {
			document.getElementById('layout_options_' + tpl).style.display = 'block';
		}
	},
	
	saveTemplate : function() {
		var s='', e, el, opt='';

		// get the selected Layout
		el = document.getElementsByName('_layout');
		for (i = 0; i < el.length; i++) {
			if (el[i].checked) {
				s = el[i].value;
				break;
			}
		}

		// get all selected options from selected layout
		if (s.length > 0) {
			el = typeof(document.forms['options_form_' + s]) != 'undefined' ? document.forms['options_form_' + s].elements : [];
			for (i = 0; i < el.length; i++) {
				e = el[i];
				if ( (e.nodeName == 'INPUT') && (e.type == 'text') ) {
					opt += '#' + e.name + '=' + e.value + '#';
				} else if (e.nodeName == 'SELECT') {
					opt += '#' + e.name + '=' + e.options[e.selectedIndex].value + '#';
				} else  if ( (e.nodeName == 'INPUT') && (e.type == 'radio') && (e.checked) ) {
					opt += '#' + e.name + '=' + e.value + '#';
				}
			}
		}

		// Execute the mceTemplate command without UI this time
		tinyMCEPopup.execCommand('mceDelighttplSet', null, {layout : s, options : opt} );
		this.doClose();
	},

	/// Private Functions
	__templateList : [],
	
	__loadTemplates : function() {
		var t = this, ed = tinyMCEPopup.editor, dom = ed.dom, s = ed.settings;
		var container = dom.get('_templates_container'), id = s.delighttitle_selected;
		var data = s.delighttitle_templateparams; data[s.delighttitle_templateparamid] = id;
		var data_str = 'mce_dummy=null';
		tinymce.each(data, function(v, k) {
			data_str += '&' + k + '=' + v;
		});
		tinymce.util.XHR.send({
			url : s.delighttitle_templateurl,
			content_type : 'application/x-www-form-urlencoded; charset=iso-8859-15',
			type : 'POST',
			async : true,
			data : data_str,
			scope : t,
			success : function(data, req, o) {
				data = data.replace(/while\(1\);/, '');
				this.__displayTemplates(tinymce.util.JSON.parse(data));
			},
			error : function(type, req, o) {
				tinyMCEPopup.editor.windowManager.alert('Error: ' + type);
			},
		});
	},

	__displayTemplates : function(o) {
		var t = this, ed = tinyMCEPopup.editor, dom = ed.dom, s = ed.settings;

		// Add all needed CSS-Files
		var l=[];
		if (o.loadCssFiles) {
			var e, head = document.getElementsByTagName("head");
			tinymce.each(o.loadCssFiles, function(css) {
				if ((tinymce.inArray(l, css) < 0) && (css != "")) {
					e = document.createElement('link');
					e.setAttribute('href', css);
					e.setAttribute('rel', 'stylesheet');
					e.setAttribute('type', 'text/css');
					head.item(0).appendChild(e);
					l.push(css);
				}
			});
			l = [];
		}
		
		// Show all ContentTemplates
		if (o.layoutList) {
			var c = tinyMCEPopup.getWindowArg('template'), cont = document.getElementById('_templates_container');
			var cnt = 0, r, cell0, cell1, tmp;
			
			if (cont && (cont.nodeName.toLowerCase() == 'table')) {
				tinymce.each(o.layoutList, function(tpl, n) {
					tmp = tpl;
					tmp = tmp.replace(/\t/g, "").replace(/\\"/g, '"');
					tmp = tmp.replace(/(\<script)(.*?)(\<\/script\>)/gi, "");
					tmp = tmp.replace(/(\<input)(.*?)(>)/gi, "");
					tmp = tmp.replace(/(\<form)(.*?)(>)/gi, "");
					tmp = tmp.replace(/(\<\/form)(.*?)(>)/gi, "");
					if (tmp.length > 0) {
						row = cont.insertRow(cnt++);
						cell0 = row.insertCell(0);
						cell0.style.width = '50px';
						cell0.innerHTML = '<input type="radio" name="_layout" value="' + n + '"' + ((c == n)?' checked="checked"':'') + ' onclick="TemplateDialog.changeTemplate(this.value)" />';
						cell1 = row.insertCell(1);
						cell1.style.border = '1px inset #3c3c3c';
						cell1.style.backgroundColor = 'rgb(240,240,238)';
						if (tmp.substr(0,3) == '<tr') {
							cell1.innerHTML = '<table style="width:100%;">' + tmp + '<tr><td><b>' + n + '</b></td></tr></table>';
						} else {
							cell1.innerHTML = tmp + '<br /><b>' + n + '</b>';
						}
					}
				});
			}
		}
		
		// Show all settings
		if (o.optionsList) {
			var list='';
			t.__templateList = [];
			tinymce.each(o.optionsList, function(opt, n) {
				if (opt != "") {
					list += '<div style="display:' + ((c == n)?'block':'none') + ';" name="layout_options_div" id="layout_options_' + n.replace(/[^0-9a-zA-Z_-]/g, "")+'">'
					list += '<h2 style="color:black;">{#delighttitle.optionsfor}: ' + n + '</h2>';
					list += '<form id="options_form_' + n.replace(/[^0-9a-zA-Z_-]/g, "") + '">';
					list += opt;
					list += '</form>';
					list += '</div>';
					t.__templateList.push(n);
				}
			});
			document.getElementById('options_panel').innerHTML = ed.translate(list);
		}

		t.__selectOptions(ed.settings.delighttitle_options_field.value, c);
		t.changeTemplate(c);
	},
	
	__selectOptions : function(options, sel) {
		var t = this, ed = tinyMCEPopup.editor, dom = ed.dom, s = ed.settings;
		options = options.split('##');
		tinymce.each(options, function(opt, n) {
			var l = t.__templateList, frm, el, tp, on, ov;
			opt = opt.replace(/\#/g, '').split('=');
			on = opt[0];
			ov = opt[1];
			for (var i = 0; i < l.length; i++) {
				tp = l[i];
				frm = document.getElementById('options_form_' + tp);
				el = frm.elements || [];
				tinymce.each(el, function(e) {
					var nn = e.nodeName.toLowerCase();
					if (e.name == on) {
						if ( (nn == 'input') && (e.type == 'text') ) {
							e.vaue = ov;
						} else if ( (nn == 'input') && (e.type = 'radio') && (e.value == ov) ) {
							e.checked = true;
						} else if (nn == 'select') {
							for (var j = 0; j < e.options.length; j++) {
								e.options[j].selected = false;
								if (e.options[j].value == ov) {
									e.options[j].selected = true;
								}
							}
						}
					}
				});
			}
		});
	}

};

TemplateDialog.preInit();
tinyMCEPopup.onInit.add(TemplateDialog.init, TemplateDialog);

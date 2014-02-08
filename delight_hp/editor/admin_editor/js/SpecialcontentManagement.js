var SpecialcontentManagement = {
	adminAction:1600,
	newType:'all',
	elemComment:'',
	
	preInit : function() { },

	init : function(ed) {
		var mgmt = SpecialcontentManagement;
		AdminDialog.loadSections('section', mgmt.adminAction, mgmt.sectionClick);
		
		// Content-Toolbar
		AdminDialog.addButton('contentToolbar', {
			name : 'content_new',
			button : 'section_new',
			action : mgmt.contentCreate
		});
		AdminDialog.addButton('contentToolbar', {
			name : 'content_delete',
			button : 'section_delete',
			action : mgmt.contentDelete
		});
		Event.observe(window, 'resize', this.checkTabScroller);
	},
	
	// Admin-Editor Toolbar Events
	contentCreate : function(e) {
		if ($('CurrentlySelected').value.length <= 0) return;
		if (SpecialcontentManagement.newType == 'all') {
			var url = '/delight_hp/index.php?lang='+AdminDialog.getLang()+'&adm='+SpecialcontentManagement.adminAction+'&action=create&variable='+$('CurrentlySelected').value;
			AdminDialog.openWindow(url, 450, 150);
		} else {
			var type = SpecialcontentManagement.newType;
			switch(type) {
			case 'number':  type = 0; break;
			case 'string':  type = 1; break;
			case 'decimal': type = 2; break;
			case 'image':   type = 3; break;
			case 'file':    type = 4; break;
			case 'news':    type = 5; break;
			case 'text':    type = 6; break;
			case 'color':   type = 7; break;
			case 'html':    type = 8; break;
			}
			AdminDialog.callFunction(SpecialcontentManagement.adminAction, {
				action: 'docreate',
				type: type,
				variable: $('CurrentlySelected').value,
				call:'reloadContent'
			}, AdminDialog);
		}
	},
	doCreate: function() {
		AdminDialog.callFunction(SpecialcontentManagement.adminAction, {
			action: 'docreate',
			type: $('vartype').value,
			variable: $('variable').value
		}, AdminDialog);
	},
	contentDelete : function(e) {
		AdminDialog.showDelete(SpecialcontentManagement.adminAction, $('CurrentlySelected').value+'_');
	},
	_removeElements: function(cont) {
		this.reloadContent();
	},
	
	// Main Functions
	sectionClick : function(s, p, o) {
		var elem = {'options':{}};
		if (o != undefined) {
			tinymce.each(o, function(e) {
				if (e && e.id && (e.id == s)) {
					elem = e.options ? e.options : elem;
					return;
				}
			});
		}
		
		SpecialcontentManagement.newType = (elem.type) ? elem.type : 'all';
		SpecialcontentManagement.elemComment = (elem.description) ? elem.description : '';
		tinyMCEPopup.editor.setProgressState(1);
		AdminDialog.loadContent(SpecialcontentManagement.adminAction, {
			section:s
		}, SpecialcontentManagement);
	},
	reloadContent: function() {
		var s = AdminDialog.getSelectedSection();
		SpecialcontentManagement.sectionClick(s, '', [{name:s,options:{type:SpecialcontentManagement.newType}}]);
	},
	
	showContent : function(cont) {
		$('tabs').innerHTML = '';
		$('tabs').setStyle({position:'relative'});
		$('panels').innerHTML = '';
		$('CurrentlySelected').value = cont.section;
		$('contentButtonbar').style.display = 'block';
		
		if (cont && cont.list && cont.list.length) {
			var i, l = cont.list, elem, id, t, c;
			for (i = 0; i < l.length; i++) {
				elem = l[i];
				id = elem.name.replace(/[^a-z0-9_-]+/gi, '')+'_'+elem.id;
				
				c = this._createContentTab(elem, id);
				if (c) {
					t = document.createElement('li');
					t.setAttribute('id', id+'_tab');
					if (i == 0) {
						t.setAttribute('class', 'current');
						c.setAttribute('class', 'panel current');
					}
					t.innerHTML = '<span><a href="javascript:mcTabs.displayTab(\''+id+'_tab\',\''+id+'_panel\');" onmousedown="return false;">'+AdminDialog.getLang('content_type_'+elem.type)+' '+(elem.count+1)+'</a></span>';
					
					$('tabs').appendChild(t);
					$('panels').appendChild(c);
				}
			}
			
			// Check for a Tab-Scroller
			this.checkTabScroller();
		} else if (cont && cont.error) {
			console.error(cont.error);
		}
	},
	checkTabScroller: function(dir) {
		var show = false;
		switch (dir) {
		case 'l':
		case 'left':
			var shown = false, finish = false, last;
			$('tabs').childElements().each(function(li, i) {
				if (finish) return;
				if (li.visible() && last && !shown) {
					last.show();
					shown = true;
				} else if (!li.visible() && shown) {
					last.hide();
					finish = true;
				}
				last = li;
			});
			break;

		case 'r':
		case 'right':
			var shown = false, finish = false, last;
			$('tabs').childElements().each(function(li, i) {
				if (finish) return;
				if (li.visible() && !shown) {
					li.hide();
					shown = true;
				} else if (!li.visible() && shown) {
					li.show();
					finish = true;
				}
				last = li;
			});
			break;
			
		default:
			$('tabs').childElements().each(function(li, i) {
				var o = li.positionedOffset();
				if (o[1] > 0) {
					li.hide();
					show = true;
				} else {
					li.show();
				}
			});
			break;
		}
		
		// Show the Scholler
		if (show && !$('tab_scroller')) {
			var p = $($('tabs').parentNode), l = document.createElement('span'), r = document.createElement('span');
			var c = document.createElement('div'), i = document.createElement('div');
			c.addClassName('scroller');
			c.setAttribute('id', 'tab_scroller');
			i.addClassName('inner');
			l.innerHTML = '&lt;';
			l.setStyle({ borderRight:'1px solid #91A7B4' });
			r.innerHTML = '&gt;';
			i.appendChild(l);
			i.appendChild(r);
			c.appendChild(i);
			p.appendChild(c);
			l.observe('click', function() {SpecialcontentManagement.checkTabScroller('l');});
			r.observe('click', function() {SpecialcontentManagement.checkTabScroller('r');});
		} else if (show && $('tab_scroller')) {
			$('tab_scroller').show();
		} else if ($('tab_scroller')) {
			$('tab_scroller').hide();
		}
	},
	
	saveContent : function() {
		var variable = $('CurrentlySelected').value, sel = AdminDialog.getSelectedEntry(variable+'_');
		if ($(sel+'_value').value.length <= 0) {
			tinyMCEPopup.editor.windowManager.alert(AdminDialog.getLang('no_value_given'));
			return;
		}
		if ($(sel+'_menu').value <= 0) {
			$(sel+'_menu').value = 0;
			/*tinyMCEPopup.editor.windowManager.alert(AdminDialog.getLang('no_menu_given'));
			return;*/
		}
		if (sel) {
			tinyMCEPopup.editor.setProgressState(1);
			AdminDialog.callFunction(SpecialcontentManagement.adminAction, {
				action: 'save',
				variable: variable,
				selected: sel.substring(variable.length+1),
				value: $(sel+'_value').value,
				menu: $(sel+'_menu').value,
				recursive: $(sel+'_menu_recursive').checked,
				settings: $(sel+'_settings').value
			}, this);
		}
	},
	
	// private functions
	
	_createContentTab : function(o, id) {
		var el, fs, lb, cont, tr, td;
		el = document.createElement('div');
		el.setAttribute('id', id+'_panel');
		el.setAttribute('class', 'panel');
		
		fs = document.createElement('fieldset');
		lb = document.createElement('legend');
		lb.innerHTML = AdminDialog.getLang('setting_for')+' '+(o.count+1);
		fs.appendChild(lb);
		
		cont = document.createElement('table');
		cont.style.width = '100%';
		
		// Add Value-Field
		tr = document.createElement('tr');
		td = document.createElement('th');
		td.setAttribute('class', 'detail');
		td.innerHTML = AdminDialog.getLang('value');
		tr.appendChild(td);
		td = document.createElement('td');
		switch(o.type) {
		case 0: td.appendChild(this._getIntegerField(o.value, id)); break;
		case 1: td.appendChild(this._getStringField(o.value, id)); break;
		case 2: td.appendChild(this._getFloatField(o.value, id)); break;
		case 3: td.appendChild(this._getImageField(o.value, id)); break;
		case 4: td.appendChild(this._getFileField(o.value, id)); break;
		case 5: td.appendChild(this._getNewsField(o.value, id)); break;
		case 6: td.appendChild(this._getTextField(o.value, id)); break;
		case 7: td.appendChild(this._getColorField(o.value, id)); break;
		case 8: td.appendChild(this._getHtmlField(o.value, id)); break;
		}
		tr.appendChild(td);
		cont.appendChild(tr);
		
		// Menu Chooser
		tr = document.createElement('tr');
		td = document.createElement('th');
		td.setAttribute('class', 'detail');
		td.innerHTML = AdminDialog.getLang('menu');
		tr.appendChild(td);
		td = document.createElement('td');
		td.appendChild(this._createMenuChooser(o.menu, o.recursive, id));
		tr.appendChild(td);
		cont.appendChild(tr);
		
		// Settings
		tr = document.createElement('tr');
		td = document.createElement('th');
		td.setAttribute('class', 'detail');
		td.innerHTML = AdminDialog.getLang('settings');
		tr.appendChild(td);
		td = document.createElement('td');
		switch(o.type) {
		case 0: td.appendChild(this._getIntegerFieldSettings(o.settings, id)); break;
		case 1: td.appendChild(this._getStringFieldSettings(o.settings, id)); break;
		case 2: td.appendChild(this._getFloatFieldSettings(o.settings, id)); break;
		case 3: td.appendChild(this._getImageFieldSettings(o.settings, id)); break;
		case 4: td.appendChild(this._getFileFieldSettings(o.settings, id)); break;
		case 5: td.appendChild(this._getNewsFieldSettings(o.settings, id)); break;
		case 6: td.appendChild(this._getTextFieldSettings(o.settings, id)); break;
		case 7: td.appendChild(this._getColorFieldSettings(o.settings, id)); break;
		case 8: td.appendChild(this._getHtmlFieldSettings(o.settings, id)); break;
		}
		tr.appendChild(td);
		cont.appendChild(tr);
		
		if (SpecialcontentManagement.elemComment != '') {
			tr = document.createElement('tr');
			td = document.createElement('th');
			td.setAttribute('class', 'detail');
			td.style.background = '#dfdfdf';
			td.style.padding = '3px';
			td.innerHTML = AdminDialog.getLang('comment');
			tr.appendChild(td);
			td = document.createElement('td');
			td.style.padding = '3px';
			td.style.fontStyle = 'italic';
			td.innerHTML = SpecialcontentManagement.elemComment;
			tr.appendChild(td);
			cont.appendChild(tr);
		}
		
		fs.appendChild(cont);
		el.appendChild(fs);
		return el;
	},
	
	_setElementValue: function(c) {
		if (c.element && c.data && $(c.element)) {
			$(c.element).innerHTML = c.data;
		}
	},
	_setContainerHtml: function(c) {
		if (c.container && $(c.container) && c.content) {
			$(c.container).innerHTML = c.content;
		}
		if (c.sections && c.type) {
			AdminDialog._drawSectionList($('subsection'), c.sections, SpecialcontentManagement._getListContent, c.type);
		}
	},
	_getListContent: function(id, prefix) {
		if ($('chooser_'+prefix) && $('subcontent')) {
			AdminDialog.callFunction(SpecialcontentManagement.adminAction, {
				action: 'list_content',
				type: prefix,
				section: id,
				container: 'chooser_'+prefix
			}, SpecialcontentManagement);
		}
	},
	_showListContent: function(c) {
		var t = this;
		if ($('subcontent')) {
			$('subcontent').innerHTML = c.content;
			tinymce.each($('subcontent').childNodes, function(elem) {
				elem.observe('click', SpecialcontentManagement._listContentClick);
			});
		}
	},
	_listContentClick: function(e) {
		var id=null, elem = e.element(), variable = $('CurrentlySelected').value, sel = AdminDialog.getSelectedEntry(variable+'_');
		do {
			elem = id == null ? elem : elem.parentNode;
			id = elem.id.split('_');
			if (elem.nodeName == 'BODY') {
				return;
			}
		} while (id.length != 2);
		
		$(sel+'_value').value = id[1];
		$('chooser_'+id[0]).style.display = 'none';
		Event.stop(e);
		SpecialcontentManagement.__getFieldValue(sel, id[1]);
	},
	
	// Different Value-Chooser
	_getIntegerField: function(value, id) {
		var fld = document.createElement('span');
		fld.innerHTML = DelightNumberSpinner.get(id+'_value', value, id+'_value', true);
		return fld;
	},
	_getStringField: function(value, id) {
		var fld = document.createElement('input');
		fld.setAttribute('id', id+'_value');
		fld.value = value;
		return fld;
	},
	_getFloatField: function(value, id) {
		var fld = document.createElement('span');
		fld.innerHTML = DelightNumberSpinner.get(id+'_value', value, id+'_value', false);
		return fld;
	},
	_getImageField: function(value, id) {
		var t = this, fld = document.createElement('span');
		fld.innerHTML = '<span id="'+id+'_title" style="padding:2px;border:1px solid black;display:block;float:left;">...</span><input type="hidden" id="'+id+'_value" value="'+value+'" />';
		fld.style.cursor = 'pointer';
		fld.observe('click', function() {
			var cont = $('chooser_images');
			if (cont && (cont.style.display == 'none')) {
				cont.style.display = 'block';
			} else if (!cont) {
				cont = t.__getChooserDiv('chooser_images', id+'_title');
				tinyMCEPopup.editor.setProgressState(1);
				AdminDialog.callFunction(SpecialcontentManagement.adminAction, {
					action: 'list',
					type: 'images',
					container: 'chooser_images'
				}, SpecialcontentManagement);
			}
		});
		this.__getFieldValue(id);
		return fld;
	},
	_getFileField: function(value, id) {
		var t = this, fld = document.createElement('span');
		fld.innerHTML = '<span id="'+id+'_title" style="padding:2px;border:1px solid black;display:block;float:left;">...</span><input type="hidden" id="'+id+'_value" value="'+value+'" />';
		fld.style.cursor = 'pointer';
		fld.observe('click', function() {
			var cont = $('chooser_files');
			if (cont && (cont.style.display == 'none')) {
				cont.style.display = 'block';
			} else if (!cont) {
				cont = t.__getChooserDiv('chooser_files', id+'_title');
				tinyMCEPopup.editor.setProgressState(1);
				AdminDialog.callFunction(SpecialcontentManagement.adminAction, {
					action: 'list',
					type: 'files',
					container: 'chooser_files'
				}, SpecialcontentManagement);
			}
		});
		this.__getFieldValue(id);
		return fld;
	},
	_getNewsField: function(value, id) {
		var t = this, fld = document.createElement('span');
		fld.innerHTML = '<span id="'+id+'_title">...</span><input type="hidden" id="'+id+'_value" value="'+value+'" />';
		fld.style.cursor = 'pointer';
		fld.observe('click', function() {
			var cont = $('chooser_news');
			if (cont && (cont.style.display == 'none')) {
				cont.style.display = 'block';
			} else if (!cont) {
				cont = t.__getChooserDiv('chooser_news', id+'_title');
				tinyMCEPopup.editor.setProgressState(1);
				AdminDialog.callFunction(SpecialcontentManagement.adminAction, {
					action: 'list',
					type: 'news',
					container: 'chooser_news'
				}, SpecialcontentManagement);
			}
		});
		this.__getFieldValue(id);
		return fld;
	},
	_getTextField: function(value, id) {
		var t = this, fld = document.createElement('span');
		fld.innerHTML = '<span id="'+id+'_title">...</span><input type="hidden" id="'+id+'_value" value="'+value+'" />';
		fld.style.cursor = 'pointer';
		fld.observe('click', function() {
			var cont = $('chooser_texts');
			if (cont && (cont.style.display == 'none')) {
				cont.style.display = 'block';
			} else if (!cont) {
				cont = t.__getChooserDiv('chooser_texts', id+'_title');
				tinyMCEPopup.editor.setProgressState(1);
				AdminDialog.callFunction(SpecialcontentManagement.adminAction, {
					action: 'list',
					type: 'texts',
					container: 'chooser_texts'
				}, SpecialcontentManagement);
			}
		});
		this.__getFieldValue(id);
		return fld;
	},
	_getColorField: function(value, id) {
		var t = this, fld = document.createElement('span');
		fld.innerHTML = '<span id="'+id+'_title" style="display:block;background:'+value+';width:50px;height:16px;">'+value+'</span><input type="hidden" id="'+id+'_value" value="'+value+'" />';
		fld.setStyle({
			cursor:'pointer',
			padding:'1px',
			display:'block',
			border:'1px solid black',
			width:'50px',
			height:'16px'
		});
		fld.observe('click', function() {
			AdminDialog.showColorChooser(SpecialcontentManagement.adminAction, 'SpecialcontentManagement._setColorValue', id, $(id+'_value').value);
		});
		return fld;
	},
	_setColorValue: function(val, id) {
		var cf = $(id+'_title'), vf = $(id+'_value');
		if (vf && vf.hasAttribute('value')) {
			vf.value = '#'+val;
		}
		if (cf) {
			cf.style.backgroundColor = '#'+val;
			cf.innerHTML = '#'+val;
		}
	},
	_getHtmlField: function(value, id) {
		var fld = document.createElement('textarea');
		fld.setAttribute('id', id+'_value');
		fld.style.width = '238px';
		fld.style.height = '88px';
		fld.value = value;
		return fld;
	},
	
	// Show the Value for a SpecialField like Image, File, Text, News, ...
	__getFieldValue: function(id, val) {
		var l = id.lastIndexOf('_'), variable = [id.substring(0, l), id.substring(l+1)];
		AdminDialog.callFunction(SpecialcontentManagement.adminAction, {
			action: 'getvalue',
			element: id+'_title',
			variable: variable[0],
			id: variable[1],
			value: (typeof(val) == 'undefined') ? '' : val
		}, this);
	},
	__getChooserDiv: function(id, parent) {
		var cont = document.createElement('div');
		cont.setAttribute('id', id);
		cont.style.display = 'block';
		cont.style.position = 'absolute';
		cont.style.background = 'white';
		cont.style.padding = '3px';
		cont.style.top = '0';
		cont.style.left = '0';
		cont.style.right = '0';
		cont.style.bottom = '37px';
		$(parent).appendChild(cont);
		return cont;
	},
	
	// Value-Settings
	_getIntegerFieldSettings: function(value, id) {
		var fld = document.createElement('input');
		fld.setAttribute('id', id+'_settings');
		fld.value = value;
		return fld;
	},
	_getStringFieldSettings: function(value, id) {
		var fld = document.createElement('input');
		fld.setAttribute('id', id+'_settings');
		fld.value = value;
		return fld;
	},
	_getFloatFieldSettings: function(value, id) {
		var fld = document.createElement('input');
		fld.setAttribute('id', id+'_settings');
		fld.value = value;
		return fld;
	},
	_getImageFieldSettings: function(value, id) {
		var fld = document.createElement('input');
		fld.setAttribute('id', id+'_settings');
		fld.value = value;
		return fld;
	},
	_getFileFieldSettings: function(value, id) {
		var fld = document.createElement('input');
		fld.setAttribute('id', id+'_settings');
		fld.value = value;
		return fld;
	},
	_getNewsFieldSettings: function(value, id) {
		var fld = document.createElement('input');
		fld.setAttribute('id', id+'_settings');
		fld.value = value;
		return fld;
	},
	_getTextFieldSettings: function(value, id) {
		var fld = document.createElement('input');
		fld.setAttribute('id', id+'_settings');
		fld.value = value;
		return fld;
	},
	_getColorFieldSettings: function(value, id) {
		var fld = document.createElement('input');
		fld.setAttribute('id', id+'_settings');
		fld.value = value;
		return fld;
	},
	_getHtmlFieldSettings: function(value, id) {
		var fld = document.createElement('input');
		fld.setAttribute('id', id+'_settings');
		fld.value = value;
		return fld;
	},
	
	// Menuchooser
	_createMenuChooser: function(value, recursive, id) {
		var id = id+'_menu', fld = document.createElement('input');
		fld.setAttribute('type', 'hidden');
		fld.setAttribute('id', id);
		fld.value = value;
		
		AdminDialog.callFunction(this.adminAction, {
			action: 'menuchooser',
			entry: value,
			element: id,
			recursive: recursive
		}, this);
		
		return fld;
	},
	_createMenuChooserCall: function(cont) {
		if (!cont.element || !$(cont.element)) {
			return;
		}
		var c,m;
		
		// Menu label
		c = document.createElement('div');
		c.setAttribute('container', cont.element+'_menu');
		//c.setAttribute('id', cont.element+'_menu_display');
		c.innerHTML = '<span id="'+cont.element+'_menu_display">...</span><input id="'+cont.element+'_recursive" type="checkbox" '+(cont.recursive>0 ? 'checked="checked"' :'')+' value="1" style="margin-left:20px;" /><span>'+AdminDialog.getLang('recursive')+'</span>';
		c.style.cursor = 'pointer';
		$(cont.element).parentNode.insertBefore(c, $(cont.element));
		$(cont.element+'_menu_display').setAttribute('container', cont.element+'_menu');
		$(cont.element+'_menu_display').observe('click', function(e) {
			var d = $(this.getAttribute('container')).style.display;
			$(this.getAttribute('container')).style.display = (d == 'block') ? 'none' : 'block';
		});
		
		// MenuChooser
		c = document.createElement('div');
		c.setAttribute('id', cont.element+'_menu');
		c.style.position = 'absolute';
		c.style.display = 'none';
		c.style.height = '250px';
		c.style.width = '200px';
		c.style.overflow = 'auto';
		c.style.background = 'white';
		c.style.border = '1px solid #000';
		c.style.padding = '3px';
		m = {
				childs:[],
				id:0,
				text:AdminDialog.getLang('on_all_sites'),
				selected:true
		};
		c.appendChild(this._doCreateMenu([m], 0, cont.element));
		if (cont.menu && (cont.menu.length > 0)) {
			c.appendChild(this._doCreateMenu(cont.menu, 0, cont.element));
		}
		$(cont.element).parentNode.insertBefore(c, $(cont.element));
	},
	_doCreateMenu: function(list, level, input) {
		var t = this, cont = document.createElement('div'), l;
		cont.style.marginLeft = (level*10)+'px';
		list.each(function(c) {
			l = document.createElement('div');
			l.setAttribute('id', 'menuChooserEntry_'+input+'_'+c.id);
			l.setAttribute('container', input+'_menu');
			l.setAttribute('menuid', c.id);
			if (c.selected) {
				l.style.fontWeight = 'bold';
				$(input+'_menu_display').innerHTML = c.text;
			}
			l.innerHTML = c.text;
			l.style.cursor = 'pointer';
			l.observe('click', function(e) {
				if ($('menuChooserEntry_'+input+'_'+$(input).value)) {
					$('menuChooserEntry_'+input+'_'+$(input).value).style.fontWeight = '';
				}
				this.style.fontWeight = 'bold';
				$(input).value = this.getAttribute('menuid');
				$(this.getAttribute('container')).style.display = 'none';
				$(this.getAttribute('container')+'_display').innerHTML = this.innerHTML;
			});
			cont.appendChild(l);
			if (c.childs && (c.childs.length > 0)) {
				cont.appendChild(t._doCreateMenu(c.childs, level+1, input));
			}
		});
		return cont;
	}
	
};

SpecialcontentManagement.preInit();
tinyMCEPopup.onInit.add(SpecialcontentManagement.init, SpecialcontentManagement);

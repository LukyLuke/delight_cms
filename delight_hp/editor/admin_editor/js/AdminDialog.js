var AdminDialog = {
	preInit : function() {
		tinyMCEPopup.requireLangPack();
	},
	init : function(ed) {
		this.initSpinner();
		//tinyMCEPopup.resizeToInnerSize();
		//setTimeout('AdminDialog.resize()', 1000);
		Event.observe(window, 'resize', AdminDialog.resize);
	},
	resize : function(e) {
		//tinyMCEPopup.resizeToInnerSize();
		// ResizeToInnerSize does not work fore some reason, the Height is to little
		// this is a nearly 1:1 with an additional Offset of 25 pixels in height
		var vp = tinyMCEPopup.dom.getViewPort(window), dw, dh;
		dw = tinyMCEPopup.getWindowArg('mce_width') - vp.w;
		dh = tinyMCEPopup.getWindowArg('mce_height') - vp.h;
		if (e == undefined) { // added to prevent "Double-Resize-Calls"
			if (tinyMCEPopup.isWindow)
				window.resizeBy(dw, dh);
			else
				tinyMCEPopup.editor.windowManager.resizeBy(dw, dh+25, tinyMCEPopup.id);
		}
	},
	close : function() {
		tinyMCEPopup.close();
	},
	showError: function(msg) {
		alert(msg);
	},
	redirect : function(url) {
		tinyMCEPopup.getWin().location.href = url;
	},
	getWin : function() {
		return tinyMCEPopup.getWin();
	},
	getDoc : function() {
		return tinyMCEPopup.getWin().document;
	},
	getElement : function(id) {
		return this.getDoc().getElementById(id);
	},
	getSelectedSection : function(id_prefix) {
		id_prefix = (typeof(id_prefix) == 'undefined') ? '' : id_prefix;
		var l = $(id_prefix+'scmain'), i, li;
		if (l) {
			li = l.getElementsByTagName('span');
			for (i = 0; i < li.length; i++) {
				if (Element.hasClassName(li[i], 'selected')) {
					return li[i].id.replace(/tsc/, '').substring(id_prefix.length);
				}
			}
		}
		return false;
	},
	getSelectedEntry : function(id, multiple) {
		var l,i;
		multiple = (typeof(multiple) == 'boolean') && multiple;
		id = (typeof(id) == 'undefined') ? '' : id;
		if ($('sortableContainer')) {
			var list = [];
			l = $('sortableContainer').getElementsByTagName('li');
			for (i = 0; i < l.length; i++) {
				if (Element.hasClassName(l[i], 'selected')) {
					list.push(l[i].id.substr(id.length));
				}
			}
			if (list.length == 1 && !multiple) {
				return list[0];
			} else if (list.length >= 1 && multiple) {
				return list;
			}
		}
		if ($('tabs')) {
			l = $('tabs').getElementsByTagName('li');
			for (i = 0; i < l.length; i++) {
				if ((l[i].id.substring(0, id.length) == id) && Element.hasClassName(l[i], 'current')) {
					return l[i].id.replace(/_tab/, '');
				}
			}
		}
		return false;
	},
	getWindowArg : function(arg) {
		return tinyMCEPopup.getWindowArg(arg);
	},
	getOpener: function() {
		var win, id = AdminDialog.getWindowArg('windowId');
		if (typeof(id) != 'undefined') {
			//tinymce.each(tinyMCEPopup.editor.windowManager.windows, function(w) {
			//	console.info(w.id, ' - ', id);
			//	if (w.id == id) {
			//		win = w.iframeElement.get();
			//		return;
			//	}
			//});
			// Hope thet they don't change the IFrameID, but this is more performant and stable than looping as above
			win = this.getElement(id+'_ifr');
			win = win != null ? win.contentWindow : undefined;
			
		}
		return win;
	},
	loadCSS : function(file) {
		tinyMCEPopup.dom.loadCSS(file);
	},
	
	// Spinner functions
	initSpinner: function() {
		if ($('spinner')) {
			$('spinner').observe('mousedown', AdminDialog._moveSpinnerStart);
		}
	},
	_moveSpinnerStart: function(e) {
		$('spinner').parentNode.observe('mousemove', AdminDialog._moveSpinner);
		$('spinner').parentNode.observe('mouseup', AdminDialog._moveSpinnerStop);
	},
	_moveSpinner: function(e) {
		var w, s = $('spinner');
		if (s != undefined) {
			w = $('spinner').getDimensions().width / 2;
			s.style.left = (e.pointerX() - w)+'px';
			if (s.hasAttribute('spinner_left') && $(s.getAttribute('spinner_left'))) {
				$(s.getAttribute('spinner_left')).style.width = (e.pointerX() - w)+'px';
			}
			if (s.hasAttribute('spinner_right') && $(s.getAttribute('spinner_right'))) {
				$(s.getAttribute('spinner_right')).style.left = (e.pointerX() + w)+'px';
			}
			if ($('sectionToolbar')) {
				$('sectionToolbar').style.width = (e.pointerX() - w - w - 2)+'px';
			}
			if ($('contentToolbar')) {
				$('contentToolbar').style.left = (e.pointerX() + w)+'px';
			}
			if ($('contentButtonbar')) {
				$('contentButtonbar').style.left = (e.pointerX() + w)+'px';
			}
		}
	},
	_moveSpinnerStop: function(e) {
		$('spinner').parentNode.stopObserving('mousemove', AdminDialog._moveSpinner);
		$('spinner').parentNode.stopObserving('mouseup', AdminDialog._moveSpinnerStop);
	},
	
	// Content Functions
	showUpload : function(adm, filter, id, files) {
		var s = AdminDialog.getSelectedSection();
		if (!s) {
			return;
		}
		tinyMCEPopup.editor.windowManager.open({
			file : '/delight_hp/index.php?lang='+AdminDialog.getLang()+'&adm='+adm+'&action=template&template=upload',
			width : 400,
			height : 250,
			inline : true,
			resizable : true,
			maximizable : false
		}, {
			plugin_url : tinymce.baseURL+'/../admin_editor/',
			theme_url : tinymce.baseURL+'/themes/advanced/',
			ftp_files_filter : filter || '*',
			section : AdminDialog.getSelectedSection(),
			entry : id ? AdminDialog.getSelectedEntry(id, true) : '',
			windowId: tinyMCEPopup.id,
			files: files
		});
	},
	showDelete : function(adm, id, multiple) {
		multiple = (typeof(multiple) == 'boolean') && multiple;
		var s = AdminDialog.getSelectedEntry(id, multiple);
		if (!s || s.length <= 0) {
			return;
		}
		var params = {action:'remove'};
		params[multiple ? 'idlist' : 'id'] = s;
		tinyMCEPopup.editor.windowManager.confirm(AdminDialog.getLang('upload_delete_question'), function(d) {
			if (d) {
				AdminDialog.callFunction(adm, params, AdminDialog);
			}
		});
	},
	showTexteditor : function(adm, id, ext) {
		var s = 0, w=550, h=450;
		if ((typeof(id) != 'undefined') && (id != null) && (id != 0)) {
			s = AdminDialog.getSelectedEntry(id);
			if (!s) {
				return;
			}
		}
		if (!AdminDialog.getSelectedSection()) {
			return;
		}
		if (ext) {
			w = 700;
			h = 550;
		}
		
		tinyMCEPopup.editor.windowManager.open({
			file : '/delight_hp/index.php?lang='+AdminDialog.getLang()+'&adm='+adm+'&action=editor&entry='+s+'&section='+AdminDialog.getSelectedSection(),
			width : w,
			height : h,
			inline : true,
			resizable : true,
			maximizable : true
		}, {
			plugin_url : tinymce.baseURL+'/../admin_editor/',
			theme_url : tinymce.baseURL+'/themes/advanced/',
			entry : s,
			section : AdminDialog.getSelectedSection(),
			windowId: tinyMCEPopup.id
		});
	},
	showTimepicker : function(adm, id) {
		var s = AdminDialog.getSelectedEntry(id);
		if (!s) {
			return;
		}
		tinyMCEPopup.editor.windowManager.open({
			file : '/delight_hp/index.php?lang='+AdminDialog.getLang()+'&adm='+adm+'&action=date&entry='+s+'&section='+AdminDialog.getSelectedSection(),
			width : 240,
			height : 253,
			inline : true,
			resizable : true,
			maximizable : true
		}, {
			plugin_url : tinymce.baseURL+'/../admin_editor/',
			theme_url : tinymce.baseURL+'/themes/advanced/',
			entry : s,
			section : AdminDialog.getSelectedSection(),
			windowId: tinyMCEPopup.id
		});
	},
	showColorChooser : function(adm, call, fld, value) {
		tinyMCEPopup.editor.windowManager.open({
			file : '/delight_hp/index.php?lang='+AdminDialog.getLang()+'&adm='+adm+'&action=template&template=colorchooser',
			width : 222,
			height : 173,
			inline : true,
			resizable : false,
			maximizable : false
		}, {
			plugin_url : tinymce.baseURL+'/../admin_editor/',
			theme_url : tinymce.baseURL+'/themes/advanced/',
			call: call,
			field: fld,
			selected: value,
			windowId: tinyMCEPopup.id
		});
	},
	openWindow : function(url, w, h) {
		tinyMCEPopup.editor.windowManager.open({
			url : url,
			width : w,
			height : h,
			inline : true,
			resizable : true,
			maximizable : true
		}, {
			plugin_url : tinymce.baseURL+'/../admin_editor/',
			theme_url : tinymce.baseURL+'/themes/advanced/',
			windowId: tinyMCEPopup.id
		});
	},
	
	addButton : function(c, o) {
		var tc = $(c), tr, td, a;
		if (!tinymce.is(tc) || (tc == null)) {
			return;
		}
		
		if (!$(c+'_table')) {
			tc.innerHTML = '<table id="'+c+'_table" class="defaultSkin dadmToolbar dadmToolbarRow1 Enabled" cellpadding="0" cellspacing="0"><tr><td class="dadmToolbarStart dadmToolbarStartButton dadmFirst"><span> </span></td></tr></table>';
		}
		tr = $(c+'_table').getElementsByTagName('tr')[0];
		td = document.createElement('td');
		td.setAttribute('_params', o.params || '');
		tr.appendChild(td);
		
		a = document.createElement('a');
		a.setAttribute('id', c+'_'+o.name);
		a.setAttribute('class', 'dadmButton dadmButtonEnabled dadm_'+o.button);
		a.setAttribute('title', AdminDialog.getLang(o.name));
		a.innerHTML = '<span class="dadmIcon dadm_'+o.button+'"></span>';
		td.appendChild(a);
		
		if (o.dropdown && (o.dropdown.length > 0)) {
			var d = document.createElement('div');
			d.setAttribute('class', 'dadmButton dadmDropdown');
			d.setAttribute('id', c+'_'+o.name+'_dropdown');
			o.dropdown.each(function(item) {
				d.appendChild(AdminDialog._getDropdownButton(c, item));
			});
			td.appendChild(d);
			td.setAttribute('class', 'dadmDropDown');
		} else {
			td.onclick = o.action || function() {console.error('Undefined Toolbar-Action');};
		}
	},
	_getDropdownButton : function(c, o) {
		var d = document.createElement('div');
		d.setAttribute('class', 'dadmButton dadmDropdownEntry');
		d.setAttribute('id', c+'_'+o.name+'_entry');
		d.innerHTML = '<span class="dadmIcon dadm_'+o.button+'"></span><span class="dadmIconText">'+AdminDialog.getLang(o.name)+'</span>';
		d.onclick = o.action || function() {console.error('Undefined Toolbar-Action');};
		return d;
	},
	
	// Remote Function-Calls
	
	loadSections : function(id, adm, f) {
		tinymce.util.XHR.send({
			url: this._getAdminUrl(),
			data: this._getAdminParams(adm, {action:'sections'}),
			async: false, // Needs to be sync to be able to call functions in a Plugin without a Timeout just after this
			content_type:'application/x-www-form-urlencoded',
			success: function(o) {
				var j = tinymce.util.JSON.parse(o);
				if (typeof(j) == 'undefined') {
					j = { error:'JSON Parse-Error' };
				} else {
					AdminDialog._drawSectionList($(id), j, f);
				}
				tinyMCEPopup.editor.setProgressState(0);
			},
			error: function(e, re) {
				tinyMCEPopup.editor.setProgressState(0);
				console.error(e);
				console.debug({status:re.status,data:re.responseText});
			}
		});
	},
	loadContent : function(adm, params, scope) {
		params = params || {};
		params.action = params.action || 'content';
		params.entity = typeof(entityId) != 'undefined' ? entityId : 0,
		tinymce.util.XHR.send({
			url: this._getAdminUrl(),
			data: this._getAdminParams(adm, params),
			async: true,
			scope: scope,
			content_type:'application/x-www-form-urlencoded',
			success: function(o) {
				var j = tinymce.util.JSON.parse(o);
				if (typeof(j) == 'undefined') {
					j = { error:'JSON Parse-Error' };
				}
				tinyMCEPopup.editor.setProgressState(0);
				
				if (typeof(j.section) != 'undefined') {
					AdminDialog.selectSection(j.section);
				}
				
				if (j.page_content) {
					this.showPageContent(j);
				} else {
					this.showContent(j);
				}
			},
			error: function(e, re) {
				tinyMCEPopup.editor.setProgressState(0);
				console.error(e);
				console.debug({status:re.status,data:re.responseText});
			}
		});
	},
	callFunction : function(adm, params, scope) {
		params = params || {};
		params.action = params.action || 'content';
		tinymce.util.XHR.send({
			url: AdminDialog._getAdminUrl(),
			data: AdminDialog._getAdminParams(adm, params),
			async: true,
			scope: scope,
			content_type: 'application/x-www-form-urlencoded',
			success: function(o) {
				var j = tinymce.util.JSON.parse(o);
				if (typeof(j) == 'undefined') {
					j = { error:'JSON Parse-Error' };
				}
				if (!j.success) {
					console.error('ERROR while calling a Remote-Function. See the Answer-Object below:');
					console.debug(j);
				}
				tinyMCEPopup.editor.setProgressState(0);
				
				if (j.call && this[j.call]) {
					this[j.call](j);
				}
				if (j.call && j.scope) {
					eval('try{'+j.scope+'.'+j.call+'(j);}catch(e){};');
				}
			},
			error: function(e, re) {
				tinyMCEPopup.editor.setProgressState(0);
				console.error(e);
				console.debug({status:re.status,data:re.responseText});
			}
		});
	},
	
	// Section EventHandlers
	sectionCreate : function(e) {
		var id = AdminDialog.getSelectedSection()||0, p = AdminDialog._parseParams(this.getAttribute('_params'));
		AdminDialog.callFunction(p.adm, {action:'section', id:0, parent:id}, AdminDialog);
	},
	sectionCreateFinal : function(o) {
		var c,li,st;
		if (o.parent == 0) {
			c = $('scmain');
			
		} else if ($('sc'+o.parent)) {
			c = $('sc'+o.parent);
			
		} else if ($('lsc'+o.parent)) {
			c = document.createElement('ul');
			c.setAttribute('id', 'sc'+o.parent);
			c.setAttribute('class', 'sectionlist');
			$('lsc'+o.parent).appendChild(c);
			$('lsc'+o.parent).insertBefore(AdminDialog._getSectionExpandImage(o.parent), $('lsc'+o.parent).firstChild);
			$('isc'+o.parent).setAttribute('src', adminUrl+'editor/admin_editor/css/section_collapse.gif');
			
		} else {
			return;
		}
		
		li = document.createElement('li');
		li.setAttribute('id', 'lsc'+o.id);
		Element.addClassName(li, 'sectionlist');
		
		// Expand-Image
		li.appendChild(this._getSectionExpandImage(o.id, false));
		
		// Folder/Icon
		if (typeof(o.icon) == 'undefined') {
			li.appendChild(this._getSectionFolderImage(o.id));
		} else {
			li.appendChild(this._getSectionIconImage(o.id, o.icon));
		}
		
		st = document.createElement('span');
		st.setAttribute('class', 'sectionlist');
		st.setAttribute('id', 'tsc'+o.id);
		st.appendChild(document.createTextNode(o.name));
		st.onclick = function(e) {
			var s = AdminDialog.getSelectedSection();
			if (s) {
				Element.removeClassName($('tsc'+s), 'selected');
			}
			AdminDialog._sectionClick(this.id.replace(/tsc/,''), true);
			Element.addClassName($(this.id), 'selected');
			eval('var f = '+o.click+';');
			f(this.id.replace(/tsc/,''));
		};
		li.appendChild(st);
		c.appendChild(li);
		setTimeout(function(e) {
			$('tsc'+o.id).onclick();
		}, 200);
		setTimeout(function(e) {
			AdminDialog.sectionEditShow(o.adm);
		}, 400);
	},
	
	selectSection : function(sel) {
		var cont = $('section'), sec = $('lsc'+sel);
		if (!cont) return;
		if (!sec) return;
		
		Element.addClassName($('tsc'+sel), 'selected');
		while (sec.nodeName != 'DIV') {
			if ((sec.nodeName == 'UL') && (sec.getAttribute('id') != 'scmain')) {
				AdminDialog._sectionClick(sec.getAttribute('id').replace(/sc/,''), true);
			}
			sec = sec.parentNode;
		}
	},
	
	sectionEdit : function(e) {
		var p = AdminDialog._parseParams(this.getAttribute('_params'));
		AdminDialog.sectionEditShow(p.adm);
	},
	sectionEditShow : function(adm) {
		var id = AdminDialog.getSelectedSection(), s = $('tsc'+id), e = $('etsc'+id), inp;
		if (!e && s) {
			inp = document.createElement('input');
			inp.setAttribute('id', 'etsc'+id);
			inp.setAttribute('value', s.innerHTML);
			s.innerHTML = '';
			s.appendChild(inp);
			
			function remove(id) {
				var inp = $('etsc'+id), v = inp.value, s = $('tsc'+id);
				s.removeChild(inp);
				s.innerHTML = v;
				AdminDialog.callFunction(adm, {action:'section',id:id,name:v}, AdminDialog);
			};
			
			inp.onkeydown = function(e) {
				if (e.keyCode == 13) {
					e.preventDefault();
					remove(this.getAttribute('id').replace(/etsc/,''));
				}
			};
			inp.onblur = function() {
				remove(this.getAttribute('id').replace(/etsc/,''));
			};
			
			setTimeout(function() {
				inp.focus();
				inp.select();
			}, 100);
		}
	},
	
	sectionDelete : function(e) {
		var id = AdminDialog.getSelectedSection(), p = AdminDialog._parseParams(this.getAttribute('_params'));
		if (!id) {
			return;
		}
		var s = $('lsc'+id), v = $('tsc'+id).innerHTML;
		tinyMCEPopup.editor.windowManager.confirm(AdminDialog.getLang('section_delete_question')+v, function(s) {
			if (s) {
				AdminDialog.callFunction(p.adm, {action:'section_remove',id:id}, AdminDialog);
			}
		});
	},
	sectionDeleteFinal : function(o) {
		if (o.success) {
			var s = $('lsc'+o.id);
			if (s) {
				s.parentNode.removeChild(s);
			}
			if (o.scope) {
				eval(o.scope+'.sectionDeleteFinal(o);');
			}
		}
	},
	
	// Helper-functions
	
	getEntityOptions : function() {
		if (tinymce.is(entityOptions))
			return tinymce.util.JSON.parse(entityOptions);
		return {};
	},
	
	getLang : function(l) {
		if (tinymce.is(l)) {
			return tinyMCEPopup.getLang('admin_dlg.'+l);
		} else {
			return tinyMCEPopup.getWin().dedtEditorLanguage_Short;
		}
	},
	
	calcDimension : function(o, m) {
		var dim = {width:o.width, height:o.height};
		if (dim.height > m.height) {
			dim.width = Math.round((m.height * o.width) / o.height);
			dim.height = m.height;
		}
		if (dim.width > m.width) {
			dim.width = m.width;
			dim.height = Math.round((o.height * m.width) / o.width);
		}
		return dim;
	},
	getHRSize : function(size) {
		if (size > 1048576) { // MB = 1024*1024
			size = (Math.round( (size / 1048576) * 100 ) / 100) + ' MiB';
		} else if (size > 1024) { // KB = 1024
			size = (Math.round( (size / 1024) * 100 ) / 100) + ' KiB';
		} else {
			size = (Math.round( size * 100 ) / 100) + ' Byte';
		}
		return size;
	},
	
	getScrollPosition : function() {
		return document.body.scrollTop;
	},
	scrollContent : function(pos, reset) {
		if (typeof pos != 'number') {
			pos = this.scrollPosition;
		}
		document.body.scrollTop = pos;
		if ((typeof reset == 'boolean') && reset) {
			this.setScrollPosition(0);
		}
	},
	setScrollPosition : function(pos) {
		if (typeof pos != 'number') {
			pos = document.body.scrollTop;
		}
		this.scrollPosition = pos;
	},
	
	makeSortable : function(fnc, cont, cls) {
		Sortable.create(cont, {
			dropOnEmpty: true,
			containment: cont,
			constraint: 'vertical',
			scroll: window,
			onUpdate: fnc,
			only: cls
		});
	},
	colorizeSortable: function(id) {
		var c = $(id), cnt = 0;
		$A(c.childNodes).each(function (elem) {
			elem.removeClassName('even');
			elem.removeClassName('odd');
			elem.addClassName((cnt++%2) ? 'odd' : 'even');
		});
	},
	lastSortBy_Asc: true,
	autoSortBy: function(elem) {
		var by = 'sort-'+elem.getAttribute('id').replace(/contentToolbar_sort_/, '').replace(/_entry/, '');
		var list = [];
		$('sortableContainer').childElements().each(function(e) {
			list.push(e.remove());
		});
		list.sort(function(a, b) {
			var _a = a.getElementsByClassName(by);
			var _b = b.getElementsByClassName(by);
			if (_a && _a.length && _b && _b.length) {
				_a = _a.item(0).innerHTML.stripTags();
				_b = _b.item(0).innerHTML.stripTags();
				if (AdminDialog.lastSortBy_Asc) {
					return (_a < _b) ? -1 : (_a > _b) ? 1 : 0;
				} else {
					return (_a < _b) ? 1 : (_a > _b) ? -1 : 0;
				}
			}
			return 0;
		});
		list.each(function(e) {
			$('sortableContainer').appendChild(e);
		});
		AdminDialog.lastSortBy_Asc = !AdminDialog.lastSortBy_Asc;
	},
	
	// Editor-Funcitons
	showTemplatesInEditor : function(id, layouts) {
		var html,l,p,s=false,c = $(id);
		if (!c || !tinymce.is(layouts)) {
			return;
		}
		
		for (l in layouts) {
			p = layouts[l];
			if (entityTemplate == p.name) {
				s = true;
				break;
			}
		}
		
		html = '<table cellpadding="0" cellspacing="2"><colgroup><col style="width:10px"/><col/></colgroup>';
		for (l in layouts) {
			p = layouts[l];
			html += '<tr><td style="background:#efefef;">';
			html += '<input type="radio" name="layout" value="'+p.name+'" '+(((entityTemplate == p.name) || !s) ? ' checked="checked"' : '')+' onclick="AdminDialog.updateTemplate();" />';
			html += '</td><td>';
			html += p.preview;
			html += '</td></tr>';
			AdminDialog.loadCSS(p.style);
			
			if (!s) entityTemplate = p.name;
			s = true;
		}
		html += '</table>';
		c.innerHTML = html;
	},
	updateTemplate : function() {
		$$('input[name="layout"]').each(function(elem) {
			if (elem.checked) {
				entityTemplate = elem.value;
			}
		});
	},
	updateOptions : function() {
		var opts = entityOptions.evalJSON();
		$$('#optionsContainer input[name]','#optionsContainer select[name]').each(function(item) {
			var name = item.getAttribute('name');
			if (name.substr(0,4) == 'opt_') {
				name = name.substr(4);
				opts[name] = item.value;
			}
		});
		entityOptions = Object.toJSON(opts);
	},
	
	showOptionsInEditor : function(id, options) {
		var html,o,p,v,c = $(id);
		if (!c || !tinymce.is(options)) {
			return;
		}
		
		html = '<table cellpadding="0" cellspacing="2" id="optionsContainer"><colgroup><col/><col/></colgroup>';
		for (o in options) {
			p = options[o];
			v = p.value;
			html += '<tr><td style="background:#efefef;">';
			html += '<strong>'+AdminDialog.getLang('option_'+p.name)+'</strong>';
			html += '</td><td>';
			
			if (p.type == 'choose') {
				html += '<select name="opt_'+p.name+'" onchange="AdminDialog.updateOptions();">';
				v.each(function(item) {
					var i = item.split(/\:/);
					html += '<option value="'+item+'">'+i[0]+'</option>';
				});
				html += '</select>';
			} else if (p.type == 'integer') {
				v = v.split(/\:/);
				html += DelightNumberSpinner.get('opt_'+p.name, v[v.length > 1 ? 1 : 0], 'delightNumberSpinner_opt_'+p.name, true);
			} else {
				v = v.split(/\:/);
				html += '<input type="text" name="opt_'+p.name+'" value="'+v[v.length > 1 ? 1 : 0]+'" style="width:100%;" onchange="AdminDialog.updateOptions();" />';
			}
			
			html += '</td></tr>';
		}
		html += '</table>';
		c.innerHTML = html;
		window.setTimeout(function() {
			for (o in options) {
				p = options[o];
				if ($('delightNumberSpinner_opt_'+p.name)) {
					$('delightNumberSpinner_opt_'+p.name).observe('update:value', AdminDialog.updateOptions);
				}
			}
		}, 100);
		window.setTimeout(AdminDialog._presetOptions, 100);
	},
	
	// private functions
	
	_removeElements: function(cont) {
		var l = (cont && cont.list) ? cont.list : cont.length > 0 ? cont : [];
		l.each(function(e) {
			if ($(e)) {
				$(e).parentNode.removeChild($(e));
			}
		});
	},
	
	_parseParams : function(p) {
		var o = {},x,t,i;
		if (p) {
			x = p.split(';');
			for (i = 0; i < x.length; i++) {
				t = x[i].split(':');
				o[t[0]] = t[1];
			}
		}
		return o;
	},
	
	_drawSectionList : function(p, o, content_function, id_prefix) {
		id_prefix = (typeof(id_prefix)=='undefined') ? '' : id_prefix;
		var i,li,st,sl,c,sub,name;
		if (p && p.nodeName.toLowerCase() != 'ul') {
			c = document.createElement('ul');
			Element.addClassName(c, 'sectionlist');
			c.setAttribute('id', id_prefix+'scmain');
			p.appendChild(c);
		}
		if (!p && !c) {
			return;
		}
		for (i = 0; i < o.length; i++) {
			li = document.createElement('li');
			li.setAttribute('id', id_prefix+'lsc'+o[i].id);
			Element.addClassName(li, 'sectionlist');
			
			// Download-Sections can be secured (color=darkred)
			if (o[i].secure) {
				li.style.color = 'darkred';
			}
			
			sub = o[i].sub ? o[i].sub : o[i].childs;
			name = o[i].name ? o[i].name : o[i].text;
			
			// Expand-Image
			li.appendChild(this._getSectionExpandImage(o[i].id, (sub && sub.length), id_prefix));
			
			// Folder/Icon
			if (typeof(o[i].icon) == 'undefined') {
				li.appendChild(this._getSectionFolderImage(o[i].id, id_prefix));
			} else {
				li.appendChild(this._getSectionIconImage(o[i].id, o[i].icon, id_prefix));
			}
			
			st = document.createElement('span');
			Element.addClassName(st, 'sectionlist');
			st.setAttribute('id', id_prefix+'tsc'+o[i].id);
			st.appendChild(document.createTextNode(name));
			st.onclick = function(e) {
				var s = AdminDialog.getSelectedSection(id_prefix),tid = this.id.replace(/tsc/,'').substring(id_prefix.length);
				if (s) {
					Element.removeClassName($(id_prefix+'tsc'+s), 'selected');
					if ($(id_prefix+'fsc'+s)) {
						$(id_prefix+'fsc'+s).setAttribute('src', adminUrl+'editor/admin_editor/css/folder_close.png');
					}
				}
				AdminDialog._sectionClick(tid, true, id_prefix);
				Element.addClassName($(this.id), 'selected');
				if ($(id_prefix+'fsc'+s)) {
					$(id_prefix+'fsc'+tid).setAttribute('src', adminUrl+'editor/admin_editor/css/folder_open.png');
				}
				
				content_function(tid, id_prefix, o);
			};
			li.appendChild(st);
			if (c) {
				c.appendChild(li);
			} else {
				p.appendChild(li);
			}
			
			if (sub && sub.length) {
				sl = document.createElement('ul');
				Element.addClassName(sl, 'sectionlist');
				sl.setAttribute('id', id_prefix+'sc'+o[i].id);
				sl.style.display = 'none';
				this._drawSectionList(sl, sub, content_function, id_prefix);
				li.appendChild(sl);
			}
		}
	},
	_getSectionExpandImage: function(id, sub, id_prefix) {
		id_prefix = (typeof(id_prefix)=='undefined') ? '' : id_prefix;
		var img = document.createElement('img');
		img.setAttribute('id', id_prefix+'isc'+id);
		img.setAttribute('class', 'expand');
		img.setAttribute('alt', 'Expand');
		if (sub) {
			img.setAttribute('src', adminUrl+'editor/admin_editor/css/section_expand.gif');
		} else {
			img.setAttribute('src', adminUrl+'editor/admin_editor/css/section_none.gif');
		}
		img.onclick = function(e) {
			AdminDialog._sectionClick(this.id.replace(/isc/,''), false, id_prefix);
		};
		return img;
	},
	_getSectionFolderImage: function(id, id_prefix) {
		id_prefix = (typeof(id_prefix)=='undefined') ? '' : id_prefix;
		var img = document.createElement('img');
		img.setAttribute('id', id_prefix+'fsc'+id);
		img.setAttribute('class', 'folder');
		img.setAttribute('alt', 'Folder');
		img.setAttribute('src', adminUrl+'editor/admin_editor/css/folder_close.png');
		img.onclick = function(e) {
			AdminDialog._sectionClick(this.id.replace(/fsc/,''), false, id_prefix);
		};
		return img;
	},
	_getSectionIconImage : function(id, icon, id_prefix) {
		id_prefix = (typeof(id_prefix)=='undefined') ? '' : id_prefix;
		var img = document.createElement('img');
		img.setAttribute('id', id_prefix+'icosc'+id);
		img.setAttribute('class', 'sectionimage');
		img.setAttribute('alt', 'Icon');
		img.setAttribute('src', icon);
		img.onclick = function(e) {
			AdminDialog._sectionClick(this.id.replace(/icosc/,''), false, id_prefix);
		};
		return img;
	},
	_sectionClick : function(id, label, id_prefix) {
		id_prefix = (typeof(id_prefix)=='undefined') ? '' : id_prefix;
		id = id.substring(id_prefix.length);
		var el = $(id_prefix+'sc'+id), img = $(id_prefix+'isc'+id);
		if (!el) {
			return;
		}
		if (label || el.style.display == 'none') {
			img.setAttribute('src', adminUrl+'editor/admin_editor/css/section_collapse.gif');
			el.style.display = 'block';
		} else {
			img.setAttribute('src', adminUrl+'editor/admin_editor/css/section_expand.gif');
			el.style.display = 'none';
		}
	},
	
	_getAdminUrl : function() {
		return adminUrl+'index.php';
	},
	_getAdminParams : function(a, o) {
		var k,p='';
		o = o || {};
		o.adm = a;
		if (typeof(o.lang) == 'undefined') {
			o.lang = AdminDialog.getLang();
		}
		for (k in o) {
			p += p == '' ? '' : '&';
			p += k+'='+encodeURIComponent(o[k]).replace(/~/g,'%7E').replace(/\+/g,'%2B');
		}
		return p;
	},
	
	_presetOptions : function() {
		var opts = entityOptions.evalJSON();
		$$('#optionsContainer input[name]','#optionsContainer select[name]').each(function(item) {
			var value,name = item.getAttribute('name');
			if (name.substr(0,4) == 'opt_') {
				name = name.substr(4);
				if (tinymce.is(opts[name])) {
					item.value = opts[name];
				}
			}
		});
		AdminDialog.updateOptions();
	}
};
AdminDialog.preInit();
tinyMCEPopup.onInit.add(AdminDialog.init, AdminDialog);


var DelightNumberSpinner = {
	
	get : function(name, value, id, int) {
		var html = '';
		if (!tinymce.is(id)) {
			id = 'delightNumberSpinner_'+name;
		}
		
		html += '<div style="position:relative;width:54px;">';
		html += '<div id="'+id+'_up" style="position:absolute;right:0;top:0;background:#F0F0EE;margin:0;padding:0 2px;border:1px solid #919B9C;height:8px;width:7px;font-size:7px;line-height:0.8;cursor:pointer;" onclick="DelightNumberSpinner.countUp(\''+id+'\');">&#9650;</div>';
		html += '<div id="'+id+'_down" style="position:absolute;right:0;bottom:0;background:#F0F0EE;margin:0;padding:0 2px;border:1px solid #919B9C;height:8px;width:7px;font-size:7px;line-height:0.8;cursor:pointer;" onclick="DelightNumberSpinner.countDown(\''+id+'\');">&#9660;</div>';
		html += '<input type="text" name="'+name+'" id="'+id+'" value="'+value+'" style="width:40px;height:15px;" onchange="DelightNumberSpinner._onChange(this);" onkeyup="DelightNumberSpinner._checkValue(this);" is_int="'+(int?1:0)+'" />';
		html += '</div>';
		
		return html;
	},
	
	countUp : function(id) {
		var val = DelightNumberSpinner._getValue(id);
		DelightNumberSpinner._setValue(id, val+1);
	},
	
	countDown : function(id) {
		var val = DelightNumberSpinner._getValue(id);
		DelightNumberSpinner._setValue(id, val-1);
	},
	
	_checkValue: function(elem) {
		var v = elem.value.replace(/[^0-9.-]+/gi);
		v = (elem.getAttribute('is_int')>0) ? parseInt(v) : parseFloat(v).toFixed(4);
		elem.value = v;
	},
	
	_onChange: function(elem) {
		Event.fire(elem, 'update:value', {value:elem.value}, true);
	},
	
	_getValue : function(id) {
		var v,d = $(id);
		if (tinymce.is(d)) {
			v = d.value.replace(/[^0-9.-]+/gi);
			return (d.getAttribute('is_int')>0) ? parseInt(v) : parseFloat(v);
		}
		return 0;
	},
	
	_setValue : function(id, value) {
		var v,d = $(id);
		if (tinymce.is(d)) {
			d.value = value;
		}
		this._onChange(d);
	}
};
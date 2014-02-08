var StaticManagement = {
	adminAction:1700,
	
	preInit : function() { },

	init : function(ed) {
		var mgmt = StaticManagement;
		AdminDialog.loadSections('section', StaticManagement.adminAction, mgmt.sectionClick);
	},
	
	// Toolbar-Eventhandler
	fileUpload : function(e) {
		AdminDialog.showUpload(StaticManagement.adminAction, '*');
	},
	fileReplace : function(e) {
		AdminDialog.showUpload(StaticManagement.adminAction, '*', 'fileEntry_');
	},
	fileDelete : function(e) {
		AdminDialog.showDelete(StaticManagement.adminAction, 'fileEntry_');
	},
	fileText: function(e) {
		AdminDialog.showTexteditor(StaticManagement.adminAction, 'fileEntry_');
	},

	// Main Functions
	sectionClick : function(s) {
		AdminDialog.loadContent(StaticManagement.adminAction, {section:s}, StaticManagement);
	},
	
	showContent : function(cont) {
		var t = document.createElement('table'), a = AdminDialog, even = 0;
		$('content').innerHTML = '';
		if (cont) {
			t.style.width = '100%';
			t.style.borderWidth = '0px';
			t.setAttribute('cellpadding', 2);
			t.setAttribute('cellspacing', 0);
			$('content').appendChild(t);
			
			t.appendChild( this._createInfoLine(a.getLang('static_langname'), cont.name, 'lang_name', even++) );
			t.appendChild( this._createInfoLine(a.getLang('static_langshort'), cont.short, 'lang_short', even++) );
			t.appendChild( this._createInfoLine(a.getLang('static_langcharset'), cont.charset, 'lang_charset', even++) );
			t.appendChild( this._createInfoLine(a.getLang('static_sitescountold'), cont.sites_count_old, 'sites_count_old', even++) );
			t.appendChild( this._createInfoLine(a.getLang('static_sitescount'), cont.sites_count, 'sites_count', even++) );
			t.appendChild( this._createInfoLine(a.getLang('static_sitescreated'), cont.sites_created, 'sites_created', even++) );
			t.appendChild( this._createInfoLine(a.getLang('static_sitespending'), cont.sites_pending, 'sites_pending', even++) );
			t.appendChild( this._createInfoLine(a.getLang('static_timeelapsed'), cont.time_elapsed, 'time_elapsed', even++) );
			t.appendChild( this._createInfoLine(a.getLang('static_timepending'), cont.time_pending, 'time_pending', even++) );
			t.appendChild( this._createInfoLine(a.getLang('static_progress'), '', 'static_progress', even++) );
			
			$('content').appendChild( this._createButtonBar(cont.short) );
			
			//AdminDialog.callFunction(StaticManagement.adminAction, {lang:cont.short,action:'state'}, StaticManagement);
			window.StaticManagementUpdate = false;
			this.updateState({lang:cont.short});
		} else if (cont && cont.error) {
			console.error(cont.error);
		}
	},
	
	startCreation : function(short) {
		AdminDialog.callFunction(StaticManagement.adminAction, {lang:short,action:'start'}, StaticManagement);
	},
	
	cancelCreation : function(short) {
		AdminDialog.callFunction(StaticManagement.adminAction, {lang:short,action:'stop'}, StaticManagement);
	},
	
	updateState : function(data) {
		if (typeof(data.lang) != 'undefined') {
			AdminDialog.callFunction(StaticManagement.adminAction, {lang:data.lang,action:'state'}, StaticManagement);
		}
	},
	
	showState : function(data) {
		if (!data.process.finished) {
			this._updateInfoLine('sites_count', data.process.num_pages);
			this._updateInfoLine('sites_created', data.process.total_published);
			this._updateInfoLine('sites_pending', data.process.pending);
			this._updateInfoLine('sites_deleted', data.process.deleted);
			this._updateInfoLine('time_elapsed', data.process.time_elapsed);
			this._updateInfoLine('time_pending', data.process.time_pending);
			if (data.process.total_published > 0) {
				this._updateInfoLine('static_progress', Math.floor(data.process.total_published*100/data.process.num_pages)+'%');
			} else {
				this._updateInfoLine('static_progress', AdminDialog.getLang('static_pleasewait'));
			}
			if (!window.StaticManagementUpdate) {
				window.StaticManagementUpdate = true;
				window.setTimeout(function() {
					StaticManagement.updateState({lang:data.language.short});
					window.StaticManagementUpdate = false;
				}, 500);
			}
		} else {
			this._updateInfoLine('static_progress', AdminDialog.getLang('static_finished'));
		}
	},
	
	// private functions
	
	_createInfoLine : function(label, value, id, even) {
		var a = AdminDialog,tr,td;
		even = (even%2==0);
		tr = document.createElement('tr');
		td = document.createElement('th');
		Element.addClassName(td, 'detail');
		Element.addClassName(td, even?'even':'odd');
		td.innerHTML = label;
		tr.appendChild(td);
		
		td = document.createElement('td');
		td.setAttribute('id', id);
		Element.addClassName(td, even?'even':'odd');
		td.innerHTML = value;
		tr.appendChild(td);
		
		return tr;
	},
	
	_updateInfoLine : function(id, value) {
		if ($(id)) {
			$(id).innerHTML = value;
		}
	},
	
	_createButtonBar : function(short) {
		var bar,btn,div;
		div = document.createElement('div');
		div.setAttribute('id', 'buttonbar');
		
		// Cancel
		bar = document.createElement('div');
		bar.style.cssFloat = 'left';
		btn = document.createElement('input');
		btn.setAttribute('value', AdminDialog.getLang('static_cancel'));
		btn.setAttribute('id', 'cancel');
		btn.setAttribute('type', 'button');
		Event.observe(btn, 'click', function(evt) {
			StaticManagement.cancelCreation(short);
			evt.stop();
		});
		bar.appendChild(btn);
		div.appendChild(bar);
		
		// create
		bar = document.createElement('div');
		bar.style.cssFloat = 'right';
		btn = document.createElement('input');
		btn.setAttribute('value', AdminDialog.getLang('static_create'));
		btn.setAttribute('id', 'insert');
		btn.setAttribute('type', 'button');
		Event.observe(btn, 'click', function(evt) {
			StaticManagement.startCreation(short);
			evt.stop();
		});
		bar.appendChild(btn);
		div.appendChild(bar);
		
		return div;
	}
	
};

StaticManagement.preInit();
tinyMCEPopup.onInit.add(StaticManagement.init, StaticManagement);
window.StaticManagementUpdate = false;

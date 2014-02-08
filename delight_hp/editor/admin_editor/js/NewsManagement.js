var NewsManagement = {
	adminAction:1400,
	pageContent: false,
	
	preInit : function() { },

	init : function(ed) {
		var mgmt = NewsManagement;
		mgmt.pageContent = typeof(entityId) != 'undefined';
		AdminDialog.loadSections('section', NewsManagement.adminAction, mgmt.sectionClick);
		
		// Check if FileManagement is used for Administration or for Content
		if (!mgmt.pageContent) {
			// Section-Toolbar
			AdminDialog.addButton('sectionToolbar', {
				name : 'section_new',
				button : 'section_new',
				params : 'adm:'+mgmt.adminAction,
				action : AdminDialog.sectionCreate
			});
			AdminDialog.addButton('sectionToolbar', {
				name : 'section_edit',
				button : 'section_edit',
				params : 'adm:'+mgmt.adminAction,
				action : AdminDialog.sectionEdit
			});
			AdminDialog.addButton('sectionToolbar', {
				name : 'section_delete',
				button : 'section_delete',
				params : 'adm:'+mgmt.adminAction,
				action : AdminDialog.sectionDelete
			});
			
			// Content-Toolbar
			AdminDialog.addButton('contentToolbar', {
				name : 'news_create',
				button : 'news_create',
				action : mgmt.newsCreate
			});
			AdminDialog.addButton('contentToolbar', {
				name : 'news_addrss',
				button : 'news_addrss',
				action : mgmt.newsCreateRSS
			});
			AdminDialog.addButton('contentToolbar', {
				name : 'news_edit',
				button : 'news_edit',
				action : mgmt.newsChange
			});
			AdminDialog.addButton('contentToolbar', {
				name : 'news_delete',
				button : 'news_delete',
				action : mgmt.newsDelete
			});
			AdminDialog.addButton('contentToolbar', {
				name : 'news_changedate',
				button : 'news_changedate',
				action : mgmt.newsChangeDate
			});

		} else {
			// Section-Toolbar
			AdminDialog.addButton('sectionToolbar', {
				name : 'content_save',
				button : 'content_save',
				params : 'adm:'+mgmt.adminAction,
				action : mgmt.saveContent
			});
			AdminDialog.addButton('sectionToolbar', {
				name : 'open_administration',
				button : 'open_administration',
				params : 'adm:'+mgmt.adminAction,
				action : mgmt.openAdministration
			});
			
			// Load Settings and Layouts
			AdminDialog.loadContent(NewsManagement.adminAction, {
				page_settings: entityId
			}, NewsManagement);
		}
	},
	
	// Admin-Toolbar Events
	newsCreate : function(e) {
		AdminDialog.showTexteditor(NewsManagement.adminAction);
	},
	newsCreateRSS : function(e, s) {
		var url = '/delight_hp/index.php?lang='+AdminDialog.getLang()+'&adm='+NewsManagement.adminAction+'&action=addrss&section='+AdminDialog.getSelectedSection();
		if (typeof(s) != 'undefined') {
			url += '&entry='+s;
		}
		AdminDialog.openWindow(url, 500, 200);
	},
	newsChange : function(e) {
		var s = AdminDialog.getSelectedEntry('newsEntry_');
		if (s && $('newsEntry_'+s) && ($('newsEntry_'+s).getAttribute('dcms:rss') == 'true')) {
			NewsManagement.newsCreateRSS(e, s);
		} else {
			AdminDialog.showTexteditor(NewsManagement.adminAction, 'newsEntry_');
		}
	},
	newsDelete : function(e) {
		AdminDialog.showDelete(NewsManagement.adminAction, 'newsEntry_');
	},
	newsChangeDate: function(e) {
		AdminDialog.showTimepicker(NewsManagement.adminAction, 'newsEntry_');
	},
	
	// Content-Editor Toolbar Events
	saveContent : function(e) {
		tinyMCEPopup.editor.setProgressState(1);
		AdminDialog.callFunction(NewsManagement.adminAction, {
			action: 'save_page',
			section: AdminDialog.getSelectedSection(),
			entity: entityId,
			options: entityOptions,
			template: entityTemplate,
			title: $('heading').value
		}, NewsManagement);
	},
	contentSaved : function(cont) {
		tinyMCEPopup.editor.setProgressState(0);
		if (typeof(cont.success)!='undefined' && cont.success) {
			AdminDialog.getDoc()['adminMenu'+entityId].closeAdminEditor();
			AdminDialog.close();
		} else {
			console.error(cont.error);
			console.debug(cont);
		}
	},
	openAdministration : function(e) {
	},

	// Main Functions
	sectionClick : function(s) {
		tinyMCEPopup.editor.setProgressState(1);
		AdminDialog.loadContent(NewsManagement.adminAction, {
			section:s,
			page_content: NewsManagement.pageContent,
			template: typeof(entityTemplate)!='undefined' ? entityTemplate : '',
			options: typeof(entityOptions)!='undefined' ? entityOptions : ''
		}, NewsManagement);
	},
	
	showContent : function(cont) {
		$('content').innerHTML = '';
		if (cont && cont.list && cont.list.length) {
			var i, l=cont.list, cl, el, c = document.createElement('ul'), section = cont.section || 0;
			c.setAttribute('id', 'sortableContainer');
			c.setAttribute('class', 'table');
			c.setAttribute('_sectionId', section);
			$('content').appendChild(c);
			
			for (i = 0; i < l.length; i++) {
				cl = (i%2) ? 'odd' : 'even';
				el = this._createNewsLine(l[i], cl);
				if (el) {
					c.appendChild(el);
				}
			}
			AdminDialog.makeSortable(NewsManagement.updateSortOrder, 'sortableContainer', 'sortable');
			
		} else if (cont && cont.error) {
			console.error(cont.error);
		}
	},
	reloadContent: function() {
		this.sectionClick(AdminDialog.getSelectedSection());
	},
	
	showPageContent : function(cont) {
		var c;
		if (typeof(cont.list)!='undefined') {
			c = AdminDialog.getElement('admcont_'+entityId);
			c.innerHTML = cont.list.replace(/\[ADMINID\]/g, ' id="admcont_'+entityId+'"');
			AdminDialog.getWin().makeSortable();
		}
		
		if (typeof(cont.settings) != 'undefined') {
			AdminDialog.showOptionsInEditor('content_settings', cont.settings);
		}
		
		if (typeof(cont.layouts) != 'undefined') {
			AdminDialog.showTemplatesInEditor('content_layout', cont.layouts);
		}
		
		if (typeof(cont.title) != 'undefined') {
			$('heading').value = cont.title;
		}
	},
	
	updateSortOrder : function(c) {
		AdminDialog.colorizeSortable('sortableContainer');
		var order = Sortable.serialize(c).replace(/sortableContainer\[\]=/g, '').replace(/&/g, ',');
		AdminDialog.callFunction(NewsManagement.adminAction, {action:'reorder',section:c.getAttribute('_sectionId'),order:order}, NewsManagement);
	},
	
	// private functions
	
	_createNewsLine : function(o, cl) {
		var a = AdminDialog, str,d,el,tbl,tr,td,dt=new Date();
		el = document.createElement('li');
		el.setAttribute('id', 'newsEntry_'+o.id); // For FF-3.x the ID mus be in Format "string_identifier"
		el.setAttribute('class', 'sortable '+cl);
		el.setAttribute('dcms:rss', o.rss?'true':'false');
		
		el.onclick = function(e) {
			var id = AdminDialog.getSelectedEntry('newsEntry_');
			if (id) {
				$('newsEntry_'+id).removeClassName('selected');
			}
			this.addClassName('selected');
		};
		
		el.ondblclick = function(e) {
			AdminDialog.openWindow(o.src, 420, 320);
		};
		
		tbl = document.createElement('table');
		tbl.style.width = '100%';
		el.appendChild(tbl);
		
		tr = document.createElement('tr');
		tbl.appendChild(tr);
		
		td = document.createElement('td');
		str = document.createElement('strong');
		str.innerHTML = o.title;
		td.appendChild(str);
		tr.appendChild(td);
		
		td = document.createElement('td');
		td.setAttribute('class', 'date');
		str = document.createElement('em');
		str.innerHTML = o.fulldate;
		td.appendChild(str);
		tr.appendChild(td);
		
		tr = document.createElement('tr');
		tbl.appendChild(tr);
		
		td = document.createElement('td');
		td.setAttribute('class', 'content');
		td.setAttribute('colspan', 2);
		td.innerHTML = o.text;
		tr.appendChild(td);
		
		return el;
	}
	
};

NewsManagement.preInit();
tinyMCEPopup.onInit.add(NewsManagement.init, NewsManagement);

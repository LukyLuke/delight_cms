var GlobaltextManagement = {
	adminAction:1800,
	pageContent: false,
	
	preInit : function() { },

	init : function(ed) {
		var mgmt = GlobaltextManagement;
		mgmt.pageContent = typeof(entityId) != 'undefined';
		AdminDialog.loadSections('section', GlobaltextManagement.adminAction, mgmt.sectionClick);
		
		// Check if GlobaltextManagement is used for Administration or for Content
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
				name : 'gtext_create',
				button : 'gtext_create',
				action : mgmt.textCreate
			});
			AdminDialog.addButton('contentToolbar', {
				name : 'gtext_delete',
				button : 'gtext_delete',
				action : mgmt.textDelete
			});
			AdminDialog.addButton('contentToolbar', {
				name : 'gtext_edit',
				button : 'gtext_edit',
				action : mgmt.textEdit
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
			AdminDialog.loadContent(GlobaltextManagement.adminAction, {
				page_settings: entityId
			}, GlobaltextManagement);
		}
	},
	
	// Admin-Editor Toolbar Events
	textCreate : function(e) {
		AdminDialog.showTexteditor(GlobaltextManagement.adminAction, 0, true);
	},
	textDelete : function(e) {
		AdminDialog.showDelete(GlobaltextManagement.adminAction, 'textEntry_');
	},
	textEdit: function(e) {
		AdminDialog.showTexteditor(GlobaltextManagement.adminAction, 'textEntry_', true);
	},
	
	// Content-Editor Toolbar Events
	saveContent : function(e) {
		tinyMCEPopup.editor.setProgressState(1);
		AdminDialog.callFunction(GlobaltextManagement.adminAction, {
			action: 'save_page',
			section: AdminDialog.getSelectedSection(),
			entity: entityId,
			options: entityOptions,
			template: entityTemplate,
			title: $('heading').value,
			text: $('gtext_id').value
		}, GlobaltextManagement);
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
		AdminDialog.loadContent(GlobaltextManagement.adminAction, {
			section:s,
			page_content: GlobaltextManagement.pageContent,
			template: typeof(entityTemplate)!='undefined' ? entityTemplate : '',
			options: typeof(entityOptions)!='undefined' ? entityOptions : ''
		}, GlobaltextManagement);
	},
	
	showContent : function(cont, fnc) {
		$('content').innerHTML = '';
		if (cont && cont.list && cont.list.length) {
			var i, l=cont.list, cl, el, c = document.createElement('ul'), section = cont.section || 0;
			c.setAttribute('id', 'sortableContainer');
			c.setAttribute('class', 'table');
			c.setAttribute('_sectionId', section);
			$('content').appendChild(c);
			
			for (i = 0; i < l.length; i++) {
				cl = (i%2) ? 'odd' : 'even';
				el = this._createTextLine(l[i], cl, fnc);
				if (el) {
					c.appendChild(el);
				}
			}

		} else if (cont && cont.error) {
			console.error(cont.error);
		}
	},
	reloadContent: function() {
		this.sectionClick(AdminDialog.getSelectedSection());
	},
	
	showPageContent : function(cont) {
		var c;
		if (typeof(cont.list) != 'undefined') {
			this.showContent(cont, GlobaltextManagement._selectContentText);
		}
		
		if (typeof(cont.content) != 'undefined') {
			c = AdminDialog.getElement('admcont_'+entityId);
			c.innerHTML = cont.content.replace(/\[ADMINID\]/g, ' id="admcont_'+entityId+'"');
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
		AdminDialog.callFunction(GlobaltextManagement.adminAction, {action:'reorder',section:c.getAttribute('_sectionId'),order:order}, GlobaltextManagement);
	},
	
	// private functions
	
	_createTextLine : function(o, cl, fnc) {
		var a = AdminDialog, img,d,el,tbl,tr,td,dt=new Date();
		el = document.createElement('li');
		el.setAttribute('id', 'textEntry_'+o.id); // For FF3.+ the ID mus be in Format "string_identifier"
		el.setAttribute('class', 'sortable '+cl);
		
		el.onclick = function(e) {
			var id = AdminDialog.getSelectedEntry('textEntry_');
			if (id) {
				$('textEntry_'+id).removeClassName('selected');
			}
			this.addClassName('selected');
			if (typeof(fnc) == 'function') {
				try {
					fnc(AdminDialog.getSelectedEntry('textEntry_'));
				} catch(e) {}
			}
		};
		
		el.ondblclick = function(e) {
			Event.stop(e);
			GlobaltextManagement.textEdit(e);
		};
		
		el.innerHTML = '<strong style="text-decoration:underline;font-size:120%;">'+o.title+'</strong><br/>'+o.text;
		
		return el;
	},
	
	_selectContentText : function(id) {
		$('gtext_id').value = id;
		AdminDialog.loadContent(GlobaltextManagement.adminAction, {
			section: AdminDialog.getSelectedSection(),
			page_content: true,
			template: typeof(entityTemplate)!='undefined' ? entityTemplate : '',
			options: typeof(entityOptions)!='undefined' ? entityOptions : '',
			selected: id
		}, GlobaltextManagement);
	}
	
};

GlobaltextManagement.preInit();
tinyMCEPopup.onInit.add(GlobaltextManagement.init, GlobaltextManagement);

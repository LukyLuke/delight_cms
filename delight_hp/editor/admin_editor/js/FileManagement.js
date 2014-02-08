var FileManagement = {
	adminAction:1100,
	pageContent: false,
	
	preInit : function() { },

	init : function(ed) {
		var mgmt = FileManagement;
		mgmt.pageContent = typeof(entityId) != 'undefined';
		AdminDialog.loadSections('section', mgmt.adminAction, mgmt.sectionClick);
		
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
			AdminDialog.addButton('sectionToolbar', {
				name : 'section_secure',
				button : 'section_secure',
				params : 'adm:'+mgmt.adminAction,
				action : mgmt.sectionSecure
			});
			
			// Content-Toolbar
			AdminDialog.addButton('contentToolbar', {
				name : 'file_upload',
				button : 'file_upload',
				action : mgmt.fileUpload
			});
			/*AdminDialog.addButton('contentToolbar', {
				name : 'file_replace',
				button : 'file_replace',
				action : mgmt.fileReplace
			});*/
			AdminDialog.addButton('contentToolbar', {
				name : 'file_delete',
				button : 'file_delete',
				action : mgmt.fileDelete
			});
			AdminDialog.addButton('contentToolbar', {
				name : 'file_text',
				button : 'file_text',
				action : mgmt.fileText
			});
			AdminDialog.addButton('contentToolbar', {
				name : 'sort_content',
				button : 'sort_content',
				dropdown : [{
					name: 'sort_name',
					button: 'sort_name',
					action : mgmt.autoSortBy
				},{
					name: 'sort_title',
					button: 'sort_title',
					action : mgmt.autoSortBy
				},{
					name: 'sort_date',
					button: 'sort_date',
					action : mgmt.autoSortBy
				}]
			});
			
			// make the Content-Container dropable if HTML5 is available
			if (typeof(FileReader) == 'function') {
				var dropbox = $('container');
				dropbox.addEventListener("dragenter", function(e) { Event.stop(e); }, false);
				dropbox.addEventListener("dragexit",  function(e) { Event.stop(e); }, false);
				dropbox.addEventListener("dragover",  function(e) { Event.stop(e); }, false);
				dropbox.addEventListener("drop", mgmt.dropFiles, false);
			}
			
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
			AdminDialog.loadContent(FileManagement.adminAction, {
				page_settings: entityId
			}, FileManagement);
		}
	},

	// Special DropFile function opens the UploadForm and adds all already droped files
	dropFiles: function(e) {
		AdminDialog.showUpload(FileManagement.adminAction, '', false, e.dataTransfer.files);
		Event.stop(e);
	},
	
	// Admin-Editor Toolbar Events
	fileUpload : function(e) {
		AdminDialog.showUpload(FileManagement.adminAction, '');
	},
	/*fileReplace : function(e) {
		AdminDialog.showUpload(FileManagement.adminAction, '', 'fileEntry_');
	},*/
	fileDelete : function(e) {
		AdminDialog.showDelete(FileManagement.adminAction, 'fileEntry_', true);
	},
	fileText: function(e) {
		AdminDialog.showTexteditor(FileManagement.adminAction, 'fileEntry_');
	},
	sectionSecure: function(e) {
		var sel = AdminDialog.getSelectedSection();
		if (sel) {
			tinyMCEPopup.editor.setProgressState(1);
			AdminDialog.callFunction(FileManagement.adminAction, {
				action: 'secure_section',
				section: sel
			}, FileManagement);
		}
	},
	sectionSecureFinal: function(res) {
		if (res.secure) {
			$('lsc'+res.id).style.color = 'darkred';
		} else {
			$('lsc'+res.id).style.color = '';
		}
	},
	autoSortBy: function(e) {
		AdminDialog.autoSortBy(e.findElement('div'));
		FileManagement.updateSortOrder($('sortableContainer'));
	},
	
	// Content-Editor Toolbar Events
	saveContent : function(e) {
		tinyMCEPopup.editor.setProgressState(1);
		AdminDialog.callFunction(FileManagement.adminAction, {
			action: 'save_page',
			section: AdminDialog.getSelectedSection(),
			entity: entityId,
			options: entityOptions,
			template: entityTemplate,
			title: $('heading').value
		}, FileManagement);
	},
	contentSaved : function(cont) {
		tinyMCEPopup.editor.setProgressState(0);
		if (typeof(cont.success) != 'undefined' && cont.success) {
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
		AdminDialog.loadContent(FileManagement.adminAction, {
			section: s,
			page_content: FileManagement.pageContent,
			template: typeof(entityTemplate) != 'undefined' ? entityTemplate : '',
			options: typeof(entityOptions) != 'undefined' ? entityOptions : ''
		}, FileManagement);
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
				el = this._createFileLine(l[i], cl);
				if (el) {
					c.appendChild(el);
				}
			}
			AdminDialog.makeSortable(FileManagement.updateSortOrder, 'sortableContainer', 'sortable');
			
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
		AdminDialog.callFunction(FileManagement.adminAction, {action:'reorder',section:c.getAttribute('_sectionId'),order:order}, FileManagement);
	},
	
	// private functions
	
	_createFileLine : function(o, cl) {
		if (typeof(o)=='undefined') {
			return false;
		}
		var a = AdminDialog,img,el,tbl,tr,td;
		el = document.createElement('li');
		el.setAttribute('id', 'fileEntry_'+o.id); // For FF3.+ the ID mus be in Format "string_identifier"
		el.setAttribute('class', 'sortable '+cl);
		
		el.onclick = function(e) {
			var id = AdminDialog.getSelectedEntry('fileEntry_', true);
			var tid = this.getAttribute('id');
			var unsel = false;
			if (id) {
				if (e.ctrlKey) {
					unsel = this.hasClassName('selected');
					this.removeClassName('selected');
				} else {
					id.each(function(k) {
						$('fileEntry_'+k).removeClassName('selected');
					});
				}
			}
			if (!unsel) {
				this.addClassName('selected');
			}
		};
		
		tbl = document.createElement('table');
		tbl.style.width = '100%';
		el.appendChild(tbl);
		
		tr = document.createElement('tr');
		tbl.appendChild(tr);
		
		td = document.createElement('td');
		td.setAttribute('class', 'file');
		tr.appendChild(td);
		
		img = document.createElement('img');
		img.setAttribute('id', 'file'+o.id);
		img.setAttribute('alt', 'MimeType Icon');
		img.setAttribute('src', o.icon.src);
		img.style.width = '32px';
		img.style.height = '32px';
		td.appendChild(img);
		
		td = document.createElement('td');
		td.setAttribute('class', 'content');
		td.innerHTML = '<span class="title sort-title">'+(o.title.length ? o.title : o.name)+'</span><br/>'+
                       '<span class="identifier">'+a.getLang('file_name')+':</span><span class="value sort-name">'+o.name+'</span><br/>'+
                       '<span class="identifier">'+a.getLang('file_downloaded')+':</span><span class="value">'+o.downloaded+' ('+a.getLang('file_last')+' '+o.downloaded_lastfull+')</span><br/>'+
                       '<span class="identifier">'+a.getLang('file_mime')+':</span><span class="value sort-date">'+o.mimecomment+' ('+o.mimetype+')</span><br/>'+
                       '<span class="identifier">'+a.getLang('file_date')+':</span><span class="value">'+o.date+'</span><br/>'+
                       '<span class="identifier">'+a.getLang('file_link')+':</span><span class="value">'+o.download_link+'</span><br/>'+
		               '<span class="identifier">'+a.getLang('file_size')+':</span><span class="value">'+a.getHRSize(o.size)+'</span><br/>'+
		               o.text;
		tr.appendChild(td);
		return el;
	}
	
};

FileManagement.preInit();
tinyMCEPopup.onInit.add(FileManagement.init, FileManagement);

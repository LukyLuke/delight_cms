var ImageManagement = {
	adminAction:1000,
	pageContent: false,

	preInit : function() { },

	init : function(ed) {
		var mgmt = ImageManagement;
		mgmt.pageContent = typeof(entityId) != 'undefined';
		mgmt.onlyChooser = typeof(windowId) != 'undefined' && windowId != '';
		AdminDialog.loadSections('section', ImageManagement.adminAction, mgmt.sectionClick);

		// Check if ImageManagement is used for Administration or for Content
		if (mgmt.onlyChooser) { // Do only show a SaveButton if we are in ListOnly-Mode
			AdminDialog.addButton('sectionToolbar', {
				name : 'content_save',
				button : 'content_save',
				params : 'adm:'+mgmt.adminAction,
				action : mgmt.callWindowFunction
			});

		} else {
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
					name : 'image_upload',
					button : 'image_upload',
					action : mgmt.imageUpload
				});
				/*AdminDialog.addButton('contentToolbar', {
					name : 'image_replace',
					button : 'image_replace',
					action : mgmt.imageReplace
				});*/
				AdminDialog.addButton('contentToolbar', {
					name : 'image_delete',
					button : 'image_delete',
					action : mgmt.imageDelete
				});
				AdminDialog.addButton('contentToolbar', {
					name : 'image_text',
					button : 'image_text',
					action : mgmt.imageText
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
				AdminDialog.loadContent(ImageManagement.adminAction, {
					page_settings: entityId
				}, ImageManagement);
			}
		}
	},

	// Special DropFile function opens the UploadForm and adds all already droped files
	dropFiles: function(e) {
		AdminDialog.showUpload(ImageManagement.adminAction, '', false, e.dataTransfer.files);
		Event.stop(e);
	},

	// Admin-Editor Toolbar Events
	imageUpload : function(e) {
		AdminDialog.showUpload(ImageManagement.adminAction, '*.jpeg|*.jpg|*.png|*.gif|*.swf');
	},
	/*imageReplace : function(e) {
		AdminDialog.showUpload(ImageManagement.adminAction, '*.jpeg|*.jpg|*.png|*.gif|*.swf', 'imageEntry_');
	},*/
	imageDelete : function(e) {
		AdminDialog.showDelete(ImageManagement.adminAction, 'imageEntry_', true);
	},
	imageText: function(e) {
		AdminDialog.showTexteditor(ImageManagement.adminAction, 'imageEntry_');
	},
	autoSortBy: function(e) {
		AdminDialog.autoSortBy(e.findElement('div'));
		ImageManagement.updateSortOrder($('sortableContainer'));
	},

	// Content-Editor Toolbar Events
	saveContent : function(e) {
		tinyMCEPopup.editor.setProgressState(1);
		AdminDialog.callFunction(ImageManagement.adminAction, {
			action: 'save_page',
			section: AdminDialog.getSelectedSection(),
			entity: entityId,
			options: entityOptions,
			template: entityTemplate,
			title: $('heading').value
		}, ImageManagement);
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

	// ListOnly mode
	callWindowFunction: function(e) {
		tinyMCEPopup.editor.setProgressState(1);

		var close = false, id = AdminDialog.getSelectedEntry('imageEntry_'), img = $('image'+id);
		if (img) {
			var el = tinyMCEPopup.editor.windowManager.windows[windowId].iframeElement;
			var ifr = el.dom.get(el.id);
			if (ifr && ifr.contentWindow && ifr.contentWindow[clickFunction]) {
				ifr.contentWindow[clickFunction](img.getAttribute('image_id'), img.getAttribute('image_url'), img.getAttribute('image_title'));
				close = true;
			} else {
				console.error('Unable to call Function on a remote-window: ', ifr, '  ', clickFunction);
			}
		}
		tinyMCEPopup.editor.setProgressState(0);
		if (close) tinyMCEPopup.close();
	},

	// Main Functions
	sectionClick : function(s) {
		tinyMCEPopup.editor.setProgressState(1);
		AdminDialog.loadContent(ImageManagement.adminAction, {
			section:s,
			page_content: ImageManagement.pageContent,
			template: typeof(entityTemplate)!='undefined' ? entityTemplate : '',
			options: typeof(entityOptions)!='undefined' ? entityOptions : ''
		}, ImageManagement);
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
				el = this._createImageLine(l[i], cl);
				if (el) {
					c.appendChild(el);
				}
			}

			if (!ImageManagement.onlyChooser) {
				AdminDialog.makeSortable(ImageManagement.updateSortOrder, 'sortableContainer', 'sortable');
				Event.observe(document, 'keyup', function(e) {
					if (e.keyCode == Event.KEY_DELETE) {
						Event.stop(e);
						AdminDialog.showDelete(ImageManagement.adminAction, 'imageEntry_');
					}
				});
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
		AdminDialog.callFunction(ImageManagement.adminAction, {action:'reorder',section:c.getAttribute('_sectionId'),order:order}, ImageManagement);
	},

	// private functions

	_createImageLine : function(o, cl) {
		var a = AdminDialog, img,d,el,tbl,tr,td,dt=new Date();
		el = document.createElement('li');
		el.setAttribute('id', 'imageEntry_'+o.id); // For FF3.+ the ID mus be in Format "string_identifier"
		el.setAttribute('class', 'sortable '+cl);

		if (ImageManagement.onlyChooser && (selectedId == o.id)) {
			el.addClassName('selected');
		}

		el.onclick = function(e) {
			var id = AdminDialog.getSelectedEntry('imageEntry_', true);
			var tid = this.getAttribute('id');
			var unsel = false;
			if (id) {
				if (e.ctrlKey) {
					unsel = this.hasClassName('selected');
					this.removeClassName('selected');
				} else {
					id.each(function(k) {
						$('imageEntry_'+k).removeClassName('selected');
					});
				}
			}
			if (!unsel) {
				this.addClassName('selected');
			}
		};

		el.ondblclick = function(e) {
			AdminDialog.openWindow(o.src, parseInt(o.dimension[0])+20, parseInt(o.dimension[1])+20);
		};

		tbl = document.createElement('table');
		tbl.style.width = '100%';
		el.appendChild(tbl);

		tr = document.createElement('tr');
		tbl.appendChild(tr);

		td = document.createElement('td');
		td.setAttribute('class', 'image');
		tr.appendChild(td);

		d = AdminDialog.calcDimension({width:o.dimension[0], height:o.dimension[1]}, {width:100, height:80});
		img = document.createElement('img');
		img.setAttribute('id', 'image'+o.id);
		img.setAttribute('alt', o.name);
		img.setAttribute('src', '/image/'+a.getLang()+'/'+d.width+'x'+d.height+'/0/'+o.file+'/t='+dt.getTime());
		img.setAttribute('image_id', o.id);
		img.setAttribute('image_url', o.file);
		img.setAttribute('image_title', o.title);
		img.style.width = d.width+'px';
		img.style.height = d.height+'px';
		td.appendChild(img);

		td = document.createElement('td');
		td.setAttribute('class', 'content');
		td.innerHTML = '<span class="title sort-title">'+(o.title.length ? o.title : o.name)+'</span><br/>'+
                        '<span class="identifier">'+a.getLang('image_name')+':</span><span class="value sort-name">'+o.name+'</span><br/>'+
                        '<span class="identifier">'+a.getLang('image_dimension')+':</span><span class="value">'+o.dimension[0]+'x'+o.dimension[1]+'</span><br/>'+
                        '<span class="identifier">'+a.getLang('image_date')+':</span><span class="value sort-date">'+o.date+'</span><br/>'+
		                '<span class="identifier">'+a.getLang('image_size')+':</span><span class="value">'+a.getHRSize(o.size)+'</span><br/>'+
		                o.text;
		tr.appendChild(td);

		return el;
	}

};

ImageManagement.preInit();
tinyMCEPopup.onInit.add(ImageManagement.init, ImageManagement);

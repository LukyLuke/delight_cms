var IframeManagement = {
	adminAction:2000,
	
	preInit : function() { },

	init : function(ed) {
		var mgmt = IframeManagement;
		
		// Content-Toolbar
		AdminDialog.addButton('contentToolbar', {
			name : 'content_save',
			button : 'content_save',
			params : 'adm:'+mgmt.adminAction,
			action : mgmt.saveContent
		});
		
		// Load Settings and Layouts
		AdminDialog.loadContent(IframeManagement.adminAction, {
			page_settings: entityId
		}, IframeManagement);
	},
	
	// Content-Editor Toolbar Events
	saveContent : function(e) {
		tinyMCEPopup.editor.setProgressState(1);
		AdminDialog.callFunction(IframeManagement.adminAction, {
			action: 'save_page',
			entity: entityId,
			options: entityOptions,
			template: entityTemplate,
			title: $('heading').value,
			ifr_url: $('frame_url').value,
		}, IframeManagement);
	},
	contentSaved : function(cont) {
		tinyMCEPopup.editor.setProgressState(0);
		if (typeof(cont.success)!='undefined' && cont.success) {
			this.showPageContent(cont);
			AdminDialog.getDoc()['adminMenu'+entityId].closeAdminEditor();
			AdminDialog.close();
		} else {
			console.error(cont.error);
			console.debug(cont);
		}
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
		if (typeof(cont.frame_url) != 'undefined') {
			$('frame_url').value = cont.frame_url;
		}
	},
	
	// private functions
	
	_nonsens : null
};

IframeManagement.preInit();
tinyMCEPopup.onInit.add(IframeManagement.init, IframeManagement);

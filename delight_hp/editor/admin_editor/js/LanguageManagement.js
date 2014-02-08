var LanguageManagement = {
	adminAction:1300,
	
	preInit : function() { },

	init : function(ed) {
		var mgmt = LanguageManagement;
		// Content-Toolbar
		AdminDialog.addButton('contentToolbar', {
			name : 'language_create',
			button : 'language_create',
			action : mgmt.languageCreate
		});
		AdminDialog.addButton('contentToolbar', {
			name : 'language_delete',
			button : 'language_delete',
			action : mgmt.languageDelete
		});
		AdminDialog.addButton('contentToolbar', {
			name : 'language_edit',
			button : 'language_edit',
			action : mgmt.languageEdit
		});
	},
	
	// Toolbar-Eventhandler
	languageCreate : function(e) {
		AdminDialog.showUpload(LanguageManagement.adminAction, '*.jpeg|*.jpg|*.png|*.gif|*.swf');
	},
	languageDelete : function(e) {
		AdminDialog.showDelete(LanguageManagement.adminAction, 'languageEntry_');
	},
	languageDelete: function(e) {
		//AdminDialog.showTexteditor(LanguageManagement.adminAction, 'languageEntry_');
	},

	switchActive : function(id) {
		AdminDialog.callFunction(LanguageManagement.adminAction, {action:'state',language:id}, LanguageManagement);
	},
	
	// private functions
	
	_nonsens : null
};

LanguageManagement.preInit();
tinyMCEPopup.onInit.add(LanguageManagement.init, LanguageManagement);

/**
 * $Id: editor_plugin_src.js,v 1.2 2009/10/15 11:29:01 lukas Exp $
 *
 * @author Moxiecode
 * @copyright Copyright � 2004-2008, Moxiecode Systems AB, All rights reserved.
 */

(function() {
	tinymce.create('tinymce.plugins.AjaxSave', {
		init : function(ed, url) {
			var t = this;

			t.editor = ed;

			// Register commands
			ed.addCommand('mceAjaxSave', t._save, t);
			ed.addCommand('mceAjaxCancel', t._cancel, t);

			// Register buttons
			ed.addButton('save', {title : 'save.save_desc', cmd : 'mceAjaxSave'});
			ed.addButton('cancel', {title : 'save.cancel_desc', cmd : 'mceAjaxCancel'});

			ed.onNodeChange.add(t._nodeChange, t);
			ed.addShortcut('ctrl+s', ed.getLang('save.save_desc'), 'mceAjaxSave');
		},

		getInfo : function() {
			return {
				longname : 'AjaxSave',
				author : 'delight software gmbh',
				authorurl : 'http://www.delightsoftware.com',
				infourl : 'http://www.delightsoftware.com/',
				version : "1.0"
			};
		},

		// Private methods

		_nodeChange : function(ed, cm, n) {
			var ed = this.editor;

			if (ed.getParam('save_enablewhendirty')) {
				cm.setDisabled('save', !ed.isDirty());
				cm.setDisabled('cancel', !ed.isDirty());
			}
		},

		// Private methods

		_save : function() {
			var t = this, ed = t.editor;
			if (ed.getParam("save_enablewhendirty") && !ed.isDirty()) {
				return;
			}
			tinyMCE.triggerSave();
			
			ed.nodeChanged();
			window.setTimeout(tinyMCE.settings.ajaxsave_save, 100);
		},

		_cancel : function() {
			var t = this, ed = t.editor;
			ed.nodeChanged();
			window.setTimeout(tinyMCE.settings.ajaxsave_close, 100);
		}

	});

	// Register plugin
	tinymce.PluginManager.add('ajaxsave', tinymce.plugins.AjaxSave);
})();
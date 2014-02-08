/**
 * $Id: editor_plugin_src.js,v 1.1.1.1 2009/01/07 20:08:51 lukas Exp $
 *
 * @author Moxiecode
 * @copyright Copyright © 2004-2008, Moxiecode Systems AB, All rights reserved.
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
			var t = this, ed = t.editor, formObj, os, i, elementId, as, cp, tp, op, lp, ip;

			formObj = tinymce.DOM.get(ed.id).form || tinymce.DOM.getParent(ed.id, 'form');

			if (ed.getParam("save_enablewhendirty") && !ed.isDirty())
				return;

			tinyMCE.triggerSave();

			// Use callback instead
			if (os = ed.getParam("save_onsavecallback")) {
				if (ed.execCallback('save_onsavecallback', ed)) {
					ed.startContent = tinymce.trim(ed.getContent({format : 'raw'}));
					ed.nodeChanged();
				}

				return;
			}

			if (formObj) {
				ed.isNotDirty = true;

				if (as = ed.getParam("ajaxsave_function")) {
					eval(as + "('"+formObj.id+"');");
				} else if (as = ed.getParam("ajaxsave_url")) {
					cp = ed.getParam("ajaxsave_content_parameter");
					tp = ed.getParam("ajaxsave_title_parameter");
					op = ed.getParam("ajaxsave_options_parameter");
					lp = ed.getParam("ajaxsave_layout_parameter");
					ip = ed.getParam("ajaxsave_textid_parameter");
					
					var data = ed.getParam("ajaxsave_senddata");
					if (ip) {
						data += "&"+ip+"="+formObj.id.replace(/[^0-9]+/g, '');
					}
					if (cp) {
						data += "&"+cp+"="+escape(ed.getContent().replace(/\+/gi, "&#43;"));
					}
					if (tp && tinymce.EditorManager.settings.delighttitle_title_field) {
						data += "&"+tp+"="+escape(tinymce.EditorManager.settings.delighttitle_title_field.value.replace(/\+/gi, "&#43;"));
					}
					if (op && tinymce.EditorManager.settings.delighttitle_options_field) {
						data += "&"+op+"="+escape(tinymce.EditorManager.settings.delighttitle_options_field.value.replace(/\+/gi, "&#43;"));
					}
					if (lp && tinymce.EditorManager.settings.delighttitle_layout_field) {
						data += "&"+lp+"="+escape(tinymce.EditorManager.settings.delighttitle_layout_field.value.replace(/\+/gi, "&#43;"));
					}
					
					ed.setProgressState(1); // Show progress
					
					tinymce.util.XHR.send({
						url : as,
						content_type : 'application/x-www-form-urlencoded; charset=iso-8859-15',
						type : 'POST',
						data : data,
						async : false,
						scope : t,
						
						success : function(data, req, o) {
							ed.setProgressState(0); // Hide progress
							
							if (data.substring(0, 1) == "\n") {
								data = data.substring(1);
							}
							
							if (data.substring(0, 7) == 'success') {
								var tid, cmd = data.substring(8, data.indexOf("\n", 8));
								if (cmd.substring(0, 7) == 'replace') {
									cmd = data.substring(data.indexOf("\n", 7)+1);
									eval(cmd);
									
								} else if (cmd.substring(0, 6) == 'reload') {
									window.location.reload();
								}
							}
						},
						
						error : function(type,req, o) {
							ed.setProgressState(0); // Hide progress
							ed.windowManager.alert('Error: ' + type);
						}
					});
					
				}

				ed.nodeChanged();
			} else
				ed.windowManager.alert("Error: No form element found.");
		},

		_cancel : function() {
			var t = this, ed = t.editor, formObj, os, i, elementId, ip;

			formObj = tinymce.DOM.get(ed.id).form || tinymce.DOM.getParent(ed.id, 'form');

			if (ed.getParam("save_enablewhendirty") && !ed.isDirty())
				return;

			if (formObj) {
				ed.isNotDirty = true;

				if (as = ed.getParam("ajaxsave_url")) {
					ip = ed.getParam("ajaxsave_textid_parameter");
					var data = ed.getParam("ajaxsave_canceldata");
					if (ip) {
						data += "&"+ip+"="+formObj.id.replace(/[^0-9]+/g, '');
					}
					
					ed.setProgressState(1); // Show progress
					
					tinymce.util.XHR.send({
						url : as,
						content_type : 'application/x-www-form-urlencoded; charset=iso-8859-15',
						type : 'POST',
						data : data,
						async : false,
						scope : t,
						
						success : function(data, req, o) {
							ed.setProgressState(0); // Hide progress
							
							if (data.substring(0, 1) == "\n") {
								data = data.substring(1);
							}
							
							if (data.substring(0, 7) == 'success') {
								var tid, cmd = data.substring(8, data.indexOf("\n", 8));
								if (cmd.substring(0, 7) == 'replace') {
									cmd = data.substring(data.indexOf("\n", 7)+1);
									eval(cmd);
									
								} else if (cmd.substring(0, 6) == 'reload') {
									window.location.reload();
								}
							}
						},
						
						error : function(type,req, o) {
							ed.setProgressState(0); // Hide progress
							ed.windowManager.alert('Error: ' + type);
						}
					});
					
				}

				ed.nodeChanged();
			} else {
				ed.windowManager.alert("Error: No form element found.");
			}
		},
		
		__replaceContent : function(a, t, i, c) {
			window.setTimeout(function() {
				var ed = tinyMCE.get(t+i);
				var cont = document.getElementById(a+i);
				tinyMCE.remove(ed);
				cont.replace(c.replace(/\&\#34\;/g, '\'')); // Prototype-Function
				enableSortable();
			}, 1);
		}

	});

	// Register plugin
	tinymce.PluginManager.add('ajaxsave', tinymce.plugins.AjaxSave);
})();
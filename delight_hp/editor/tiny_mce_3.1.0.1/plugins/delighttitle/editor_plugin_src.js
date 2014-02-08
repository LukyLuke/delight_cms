/**
 * $Id: editor_plugin_src.js,v 1.1.1.1 2009/01/07 20:09:27 lukas Exp $
 *
 * @author Lukas Zurschmiede <l.zurschmiede@delightsoftware.com>
 * @company delight software gmbh
 * @copyright Copyright © 2008, delight software gmbh, All rights reserved.
 */

(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('delighttitle');

	tinymce.create('tinymce.plugins.Delighttitle', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
			ed.addCommand('mceDelighttpl', function() {
				ed.windowManager.open({
					file : url + '/popup.htm',
					width : 550 + parseInt(ed.getLang('delighttitle.delta_width', 0)),
					height : 450 + parseInt(ed.getLang('delighttitle.delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url, // Plugin absolute URL
					txtId : tinymce.EditorManager.settings.delighttitle_selected,
					template : tinymce.EditorManager.settings.delighttitle_layout_field.value
				});
			});
			
			ed.addCommand('mceDelighttplSet', function(ui, v) {
				ed.settings.delighttitle_layout_field.value = v.layout;
				ed.settings.delighttitle_options_field.value = v.options;
			});
			
			ed.onInit.add(function() {
				ed.dom.loadCSS(url + "/css/content.css");
			});

			// Register Template button
			ed.addButton('delighttpl', {
				title : 'delighttitle.desc',
				cmd : 'mceDelighttpl',
				image : url + '/img/template.gif',
				ui : true
			});

		},
		

		/**
		 * Creates control instances based in the incomming name. This method is normally not
		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
		 * method can be used to create those.
		 *
		 * @param {String} n Name of the control to create.
		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
		 */
		createControl : function(n, cm) {
			var s = tinymce.EditorManager.settings;
			switch (n) {
				case 'delighttitle':
					//cm.add(tinymce.ui.InputButton);
					var btn = cm.createButton('delighttitle', {
						title : 'delighttitle.desc',
						'class' : 'InputButton',
						label : s.delighttitle_title_field.value,
						field : s.delighttitle_title_field,
						name : 'delighttitle',
						menu_button : 1
					}, tinymce.ui.InputButton);

					// Return the new Button instance
					return btn;
			}

			return null;
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'Delight CMS Title',
				author : 'Lukas Zurschmiede',
				authorurl : 'http://www.delightsoftware.com',
				infourl : 'http://www.delightsoftware.com/de/cms',
				version : "2.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('delighttitle', tinymce.plugins.Delighttitle);
})();


(function() {
	var DOM = tinymce.DOM;

	tinymce.create('tinymce.ui.InputButton:tinymce.ui.Control', {
		InputButton : function(id, s) {
			this.parent(id, s);
			this.classPrefix = 'mceInput';
		},

		renderHTML : function() {
			var cp = this.classPrefix, s = this.settings, h, l, def = tinyMCE.activeEditor.translate('{#delighttitle.default_value}');

			l = DOM.encode(s.label || '');
			h = '<div style="width:180px;overflow:hidden;padding-right:3px;"><span class="' + cp + 'Label" style="font-weight:bold;" id="' + this.id + '">' + ((l=='') ? def : l) + '</span></div>';

			return h;
		},
		
		postRender : function() {
			var t = this, s = t.settings;

			function hideInput(e) {
				var d = DOM.get('input_'+t.id), val, el = DOM.get(t.id), def = tinyMCE.activeEditor.translate('{#delighttitle.default_value}');
				if (d) {
					val = d.value;
					s.field.value = val;
					DOM.remove('input_'+t.id);
					el.innerHTML = (val=='') ? def : val;
					el.style.display = '';
				}
				return tinymce.dom.Event.cancel(e);
			};
			function disableEnter(e) {
				if (e.keyCode == 13) {
					return hideInput(e);
				}
			}
			function showInput(e) {
				var el = DOM.get(t.id), val = el.innerHTML, def = tinyMCE.activeEditor.translate('{#delighttitle.default_value}');
				var elem = DOM.create('input', {id:'input_'+t.id, name:s.name, value:(val==def ? '' : val), style:'border:1px;background:white;'});
				el.parentNode.insertBefore(elem, el);
				DOM.hide(t.id);
				elem.style.width = DOM.getRect(el.parentNode).w + 'px';
				elem.focus();
				
				me = tinymce.dom.Event.add('input_'+t.id, 'blur', hideInput);
				te = tinymce.dom.Event.add('input_'+t.id, 'keydown', disableEnter);
				return tinymce.dom.Event.cancel(e);
			};
			
			tinymce.dom.Event.add(t.id, 'click', showInput);
		}

	});
})();

/*

			// Remember to have the "mce" prefix for commands so they don't intersect with built in ones in the browser.
			case "mceTemplate":
				//alert(tinyMCE.getLang('lang_delighttitle_after_reload'));

				var inst = tinyMCE.selectedInstance;
				var formObj = inst.formElement.form;
				var elementId;
				for (var i=0; i<formObj.elements.length; i++) {
					elementId = formObj.elements[i].name ? formObj.elements[i].name : formObj.elements[i].id;
					if (elementId.indexOf('layout_') == 0) {
						formObj.elements[i].value = value.layout;
					} else if (elementId.indexOf('options_') == 0) {
						formObj.elements[i].value = value.options;
					}
				}

			return true;
			
			case "mceDelighttitle":
				var inst = tinyMCE.getInstanceById(editor_id);
				var template = new Array();
				var inst = tinyMCE.selectedInstance;
				var formObj = inst.formElement.form;
				var elem, elementId = 'plain_text', textId = '';

				for (var i=0; i<formObj.elements.length; i++) {
					elem = formObj.elements[i].name ? formObj.elements[i].name : formObj.elements[i].id;
					if (elem.indexOf('layout_') == 0) {
						elementId = formObj.elements[i].value;
					}
					if (elem.indexOf('id_txt_') == 0) {
						textId = parseInt(formObj.elements[i].value);
					}
				}

				template['file'] = tinyMCE.baseURL + '/plugins/delighttitle/popup.htm';
				template['width'] = 500;
				template['height'] = 400 + (tinyMCE.isMSIE ? 25 : 0);

				// Language specific width and height addons
				template['width'] += tinyMCE.getLang('lang_insert_image_delta_width', 0);
				template['height'] += tinyMCE.getLang('lang_insert_image_delta_height', 0);

				tinyMCE.openWindow(template, {current : elementId, inline : "yes", txtId : textId});
				return true;

			case 'mceTriggerDelightTitle':
				var field = tinyMCE.getParam("delighttitle_title_field", null);
				if ( (field != null) && (field != 'undefined') ) {
					field.value = value;
				}
				return true;

		}


*/
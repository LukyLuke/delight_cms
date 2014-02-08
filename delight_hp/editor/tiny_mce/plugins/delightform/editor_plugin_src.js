/**
 * $Id: editor_plugin_src.js,v 1.2 2009/01/27 15:29:26 lukas Exp $
 *
 * @author Lukas Zurschmiede
 * @copyright Copyright (c) 2001-2011, delight software gmbh. All rights reserved.
 */

(function() {
	var each = tinymce.each;
	
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('delightform');

	tinymce.create('tinymce.plugins.DelightFormular', {
		__attributes : {},
		
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			var t = this;
			
			t.editor = ed;
			t.url = url;

			ed.onPreInit.add(function() {
				// Add additional allowed elements
				ed.serializer.addRules('input[id|type|name|value|class|style|disabled|ismap|maxlength|tabindex|usemap|checked|accesskey|accept|alt|readonly|title]');
				ed.serializer.addRules('button[id|name|class|style|disabled|tabindex|accesskey|alt|title|type]');
				ed.serializer.addRules('select[id|name|class|style|disabled|multiple|tabindex|accesskey|alt|title]');
				ed.serializer.addRules('option[id|disabled|label|selected|value]');
				
				// not verry beautiful but "ed.serializer.findRule('img')", flatten all attribs and set it again ist also ugly...
				try {
					ed.serializer.rules.img.attribs.push({name:'mce_type'});
					ed.serializer.rules.img.attribs.push({name:'mce_id'});
				} catch (ex) {}
			});
			
			// Register the Configurtation and all Input-Types
			ed.addCommand('mceDelightFormularConfig', function() {
				var cust = { plugin_url : url, adminAction: typeof(adminAction) != 'undefined' ? adminAction : null };
				tinymce.each(t.__attributes, function(v, n) {
					cust[n] = v;
				});
				ed.windowManager.open({
					file : url + '/formular_config.html',
					plugin_url : url,
					width : 640,
					height : 450,
					inline : 1
				}, cust);
			});
			
			// Register the FormularElements Windows
			ed.addCommand('mceDelightFormularEdit', function() {
				ed.windowManager.open({ file : url + '/formular_edit.html', width : 400, height : 230, inline : 1 }, { plugin_url : url });
			});
			ed.addCommand('mceDelightFormularCheckbox', function() {
				ed.windowManager.open({ file : url + '/formular_checkbox.html', width : 400, height : 190, inline : 1 }, { plugin_url : url });
			});
			ed.addCommand('mceDelightFormularRadio', function() {
				ed.windowManager.open({ file : url + '/formular_radio.html', width : 400, height : 210, inline : 1 }, { plugin_url : url });
			});
			ed.addCommand('mceDelightFormularSelect', function() {
				ed.windowManager.open({ file : url + '/formular_select.html', width : 470, height : 290, inline : 1 }, { plugin_url : url });
			});
			ed.addCommand('mceDelightFormularButton', function() {
				ed.windowManager.open({ file : url + '/formular_button.html', width : 400, height : 160, inline : 1 }, { plugin_url : url });
			});

			// Register all needed buttons
			ed.addButton('delightform_config', {
				title : 'delightform.config',
				cmd : 'mceDelightFormularConfig',
				image : url + '/img/config.gif'
			});
			ed.addButton('delightform_edit', {
				title : 'delightform.edit',
				cmd : 'mceDelightFormularEdit',
				image : url + '/img/edit.gif'
			});
			ed.addButton('delightform_checkbox', {
				title : 'delightform.checkbox',
				cmd : 'mceDelightFormularCheckbox',
				image : url + '/img/checkbox.gif'
			});
			ed.addButton('delightform_radio', {
				title : 'delightform.radio',
				cmd : 'mceDelightFormularRadio',
				image : url + '/img/radio.gif'
			});
			ed.addButton('delightform_select', {
				title : 'delightform.select',
				cmd : 'mceDelightFormularSelect',
				image : url + '/img/select.gif'
			});
			ed.addButton('delightform_button', {
				title : 'delightform.button',
				cmd : 'mceDelightFormularButton',
				image : url + '/img/button.gif'
			});

			// Add a node change handler, selects the button in the UI when an element is selected
			ed.onNodeChange.add(function(ed, cm, n) {
				// we use now real form-elements and no longer images
				cm.setActive('delightform_edit',     (n.nodeName == 'INPUT') && ((n.getAttribute('type').indexOf('text') == 0) || (n.getAttribute('type').indexOf('password') == 0)));
				cm.setActive('delightform_edit',     (n.nodeName == 'TEXTAREA'));
				cm.setActive('delightform_select',   (n.nodeName == 'SELECT'));
				cm.setActive('delightform_button',   (n.nodeName == 'BUTTON'));
				cm.setActive('delightform_checkbox', (n.nodeName == 'INPUT') && (n.getAttribute('type').indexOf('checkbox') == 0));
				cm.setActive('delightform_radio',    (n.nodeName == 'INPUT') && (n.getAttribute('type').indexOf('radio') == 0));
				
				// Fix for #416766 - see https://bugzilla.mozilla.org/show_bug.cgi?id=416766
				// on Firefox select, radio and checkbox cannot be selected
				if (tinymce.isGecko && n.getAttribute) {
					cm.setActive('delightform_select',   (n.getAttribute('mce_type') == 'select'));
					cm.setActive('delightform_checkbox', (n.getAttribute('mce_type') == 'checkbox'));
					cm.setActive('delightform_radio',    (n.getAttribute('mce_type') == 'radio'));
				}
			});
			
			ed.onInit.add(function() {
				if (ed.settings.content_css !== false)
					ed.dom.loadCSS(url + '/css/content.css');
			});
			
			// Form-Elements needs to be selectable
			ed.onClick.add(function(ed, e) {
				e = e.target;
				if ((e.nodeName === 'INPUT') ||
						(e.nodeName === 'TEXTAREA') ||
						(e.nodeName === 'SELECT') || 
						(e.nodeName === 'BUTTON')) {
					ed.selection.select(e);
				}
				
				// Fix for #416766 - see https://bugzilla.mozilla.org/show_bug.cgi?id=416766
				// on Firefox select, radio and checkbox cannot be selected
				if (tinymce.isGecko) {
					// TODO: Check for Image-Selection
				}
			});
			
			// When HTML gets inserted into the editor (while loading)
			ed.onBeforeSetContent.add(function (ed, o) {
				var h = o.content;

				// Fix elements
				h = h.replace(new RegExp('<[ ]*input','gi'),'<input');
				h = h.replace(new RegExp('<[ ]*option','gi'),'<option');
				h = h.replace(new RegExp('<[ ]*select','gi'),'<select');
				h = h.replace(new RegExp('<[ ]*/[_ ]*select[ ]*>','gi'),'</select>');
				h = h.replace(new RegExp('<[_ ]*textarea','gi'),'<textarea');
				h = h.replace(new RegExp('<[_ ]*/[_ ]*textarea[ ]*>','gi'),'</textarea>');

				o.content = h;
			});
			
			// Fix IDs on all Formular-Elements
			ed.onSetContent.add(function(ed, o) {
				var dom = ed.dom;
				tinymce.each(dom.select('input,select,button,textarea'), function(node) {
					id = dom.uniqueId('form_');
					dom.setAttrib(node, 'id', id);
				});
			});
			
			// Fix for #416766 - see https://bugzilla.mozilla.org/show_bug.cgi?id=416766
			// on Firefox select, radio and checkbox cannot be selected
			if (tinymce.isGecko) {
				ed.onGetContent.add(function(ed, o) {
					if (o.format == 'raw') return;
					
					var valid = [], dom = ed.dom, elem = document.createElement('body');
					elem.innerHTML = o.content;
					
					// Get all Images
					tinymce.each(dom.select('img.mceDelightForm', elem), function(node) {
						var e, id = dom.getAttrib(node, 'id').replace(/img_/, '');
						if (id == null || id == '') return;
						
						valid.push(id);
						e = dom.select('input#'+id, elem);
						if (e.length <= 0) {
							e = dom.select('select#'+id, elem);
						}
						
						if (e.length > 0) {
							e = e[0];
							node.parentNode.replaceChild(e, node);
						} else {
							node.parentNode.removeChild(node);
						}
					});
					
					// Remove all removed elements or show them
					tinymce.each(dom.select('input,select', elem), function(node) {
						var type = dom.getAttrib(node, 'type');
						if ((node.nodeName == 'SELECT') || (type == 'radio') || (type == 'checkbox')) {
							if (valid.indexOf(dom.getAttrib(node, 'id')) === -1) {
								node.parentNode.removeChild(node);
							} else {
								dom.setStyle(node, 'display', '');
							}
						}
					});
					o.content = elem.innerHTML;
				});
				
				ed.onSetContent.add(function(ed, o) {
					var dom = ed.dom;
					tinymce.each(dom.select('input,select'), function(node) {
						var i, attr, src, elem, type = dom.getAttrib(node, 'type'), id = dom.getAttrib(node, 'id');
						if (node.nodeName == 'SELECT') {
							type = 'select';
						}
						if ((id == null) || (id == '')) {
							id = dom.uniqueId('form_');
							dom.setAttrib(node, 'id', id);
						}
						
						if ((type == 'checkbox') || (type == 'radio') || (type == 'select')) {
							elem = document.createElement('img');
							dom.setStyle(elem, 'vertical-align', 'middle');
							dom.setStyle(elem, 'margin', '0');
							dom.setStyle(elem, 'padding', '0');
							dom.setStyle(elem, 'border', 'none');
							dom.addClass(elem, 'mceItem');
							dom.addClass(elem, 'mceDelightForm');
							dom.setAttrib(elem, 'id', 'img_'+id);
							dom.setAttrib(elem, 'mce_id', id);
							dom.setAttrib(elem, 'mce_type', type);
						
							src = type+'_blank.gif';
							if (type == 'select') {
								src = 'select_arrow.gif';
							} else if (node.checked) {
								src = type+'.gif';
							}
							
							if (type == 'select') {
								dom.setStyle(elem, 'padding', '0 0 0 '+(parseInt(dom.getStyle(node, 'width'))-20)+'px');
								dom.setStyle(elem, 'border', '1px solid black');
							}
							
							dom.setAttrib(elem, 'src', url+'/img/'+src);
							node.parentNode.insertBefore(elem, node);
							dom.hide(node);
						}
					});
				});
			}
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'Delight Formular Plugin for TinyMCE',
				author : 'Lukas Zurschmiede',
				authorurl : 'http://www.delightsoftware.com',
				infourl : 'http://www.delightsoftware.com/de/cms/tinymce',
				version : "1.5"
			};
		},
		
		/// Private functions

		
		
		nonsens:null
	});

	// Register plugin
	tinymce.PluginManager.add('delightform', tinymce.plugins.DelightFormular);
})();

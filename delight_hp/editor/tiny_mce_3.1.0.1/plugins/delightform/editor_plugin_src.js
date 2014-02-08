/**
 * $Id: editor_plugin_src.js,v 1.1.1.1 2009/01/07 20:08:55 lukas Exp $
 *
 * @author Lukas Zurschmiede
 * @copyright Copyright © 2007, delight software gmbh, All rights reserved.
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
				// Force in _value parameter this extra parameter is required for older Opera versions
				ed.serializer.addRules('span[name|value|formclass],dedtform[*]');
			});

			ed.addCommand('mceDelightFormularSetconfig', function(ui, v) {
				tinymce.each(v, function(o) {
					t.__setAttribute(o.name, o.value);
				});
			});
			
			ed.addCommand('mceDelightFormularCreateconfig', function(ui, v) {
				var dfrm = ed.dom.select('dedtform')[0], e, doc = ed.getDoc().getElementsByTagName('body')[0];
				if (!dfrm) {
					e = document.createElement('dedtform');
					doc.insertBefore(e, doc.firstChild);
				}
			});

			// Register the Configurtation and all Input-Types
			ed.addCommand('mceDelightFormularConfig', function() {
				var cust = { plugin_url : url };
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
				cm.setActive('delightform_checkbox', (n.nodeName == 'IMG') && (n.className == 'mceplgItemCheckbox') );
				cm.setActive('delightform_radio',    (n.nodeName == 'IMG') && (n.className == 'mceplgItemRadio') );
				cm.setActive('delightform_edit',     (n.nodeName == 'IMG') && ( (n.className == 'mceplgItemInput') || (n.className == 'mceplgItemTextarea') ) );
				cm.setActive('delightform_button',   (n.nodeName == 'DIV') && (n.className == 'mceplgItemButton') );
				cm.setActive('delightform_select',   (n.nodeName == 'DIV') && (n.className == 'mceplgItemSelect') );
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

				// relace elements with spans
				h = h.replace(/<input([^>]*)>/gi, '<span formclass="mceItemInput" $1></span>');
				h = h.replace(/<select([^>]*)>/gi, '<span formclass="mceItemSelect" $1>');
				h = h.replace(/<option([^>]*)>/gi, '<span formclass="mceItemOption" $1>');
				h = h.replace(/<textarea([^>]*)>/gi, '<span formclass="mceItemTextarea" $1>');
				h = h.replace(/<\/(select|option|textarea)([^>]*)>/gi, '</span>');

				o.content = h;
			});

			ed.onSetContent.add(function () {
				t.__spanToImg(ed.getBody());
			});
			
			ed.onPreProcess.add(function(ed, o) {
				var dom = ed.dom;

				if (o.set) {
					t.__spanToImg(o.node);
				}

				if (o.get) {
					each(dom.select('IMG', o.node), function(n) {
						var elem;

						switch (n.className) {
							case 'mceplgItemInput':
								elem = t.__buildInput({type:'text'}, n);
								break;

							case 'mceplgItemRadio':
								elem = t.__buildInput({type:'radio'}, n);
								break;

							case 'mceplgItemCheckbox':
								elem = t.__buildInput({type:'checkbox'}, n);
								break;

							case 'mceplgItemTextarea':
								elem = t.__buildInput({name:'textarea', type:'textarea'}, n);
								break;
						}

						if (elem) {
							dom.replace(elem, n);
						}
					});
					
					each(dom.select('DIV', o.node), function(n) {
						var elem;

						switch (n.className) {
							case 'mceplgItemButton':
								elem = t.__buildInput({type:'button'}, n);
								break;

							case 'mceplgItemSelect':
								elem = t.__buildSelect({type:'select'}, n);
								break;
						}

						if (elem) {
							dom.replace(elem, n);
						}
					});
				}
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
				longname : 'Delight CMS Formular',
				author : 'Lukas Zurschmiede',
				authorurl : 'http://www.delightsoftware.com',
				infourl : 'http://www.delightsoftware.com/de/cms',
				version : "1.0"
			};
		},
		
		/// Private functions

		__spanToImg : function(p) {
			var t = this, dom = t.editor.dom, im, ci;
			
			each(dom.select('span', p), function(n) {
				if (dom.getAttrib(n, 'formclass') == 'mceItemInput') {
					switch (dom.getAttrib(n, 'type')) {
						case 'text':
						case 'password':
						case 'file':
							dom.replace(t.__createImg('mceplgItemInput', n), n);
							break;

						case 'button':
						case 'submit':
						case 'clear':
							dom.replace(t.__createDiv('mceplgItemButton', n), n);
							break;

						case 'radio':
							dom.replace(t.__createImg('mceplgItemRadio', n), n);
							break;

						case 'checkbox':
							dom.replace(t.__createImg('mceplgItemCheckbox', n), n);
							break;
					}

				} else if (dom.getAttrib(n, 'formclass') == 'mceItemTextarea') {
					dom.replace(t.__createImg('mceplgItemTextarea', n), n);

				} else if (dom.getAttrib(n, 'formclass') == 'mceItemSelect') {
					dom.replace(t.__createDiv('mceplgItemSelect', n), n);
				}
			});
			
		},
		
		__createImg : function(cl, n) {
			var im, dom = this.editor.dom, pa = {}, ti = '', s = n.style;

			// Create image
			if (cl == 'mceplgItemRadio') {
				im = dom.create('img', {
					src : this.url + '/img/radio'+(dom.getAttrib(n, 'checked') ? '' : '_blank')+'.gif',
					width : 20,
					height : 20,
					'class' : cl
				});
				pa.type = 'radio';
				if (dom.getAttrib(n, 'checked') == 'checked') {
					pa.defaultValue = 'current';
				}

			} else if (cl == 'mceplgItemCheckbox') {
				im = dom.create('img', {
					src : this.url + '/img/checkbox'+(dom.getAttrib(n, 'checked') ? '' : '_blank')+'.gif',
					width : 20,
					height : 20,
					'class' : cl
				});
				pa.type = 'checkbox';
				if (dom.getAttrib(n, 'checked') == 'checked') {
					pa.checked = true;
				}

			} else {
				im = dom.create('img', {
					src : this.url + '/img/spacer.gif',
					'class' : cl
				});
				im.style.border = '2px inset grey';
				im.style.width = parseInt(s.width) + 'px';
				
				pa.size = parseInt(s.width) || 200;
				if (cl == 'mceplgItemTextarea') {
					im.style.height = parseInt(s.height) + 'px';
					pa.rows = parseInt(s.height) || 100;
					pa.value = n.innerHTML;
					pa.type = 'multi';
				} else {
					im.style.height = '12pt';
					pa.type = 'text';
				}
			}

			// Setup base parameters
			each(['id', 'name', 'value', 'title', 'type'], function(na) {
				var v = dom.getAttrib(n, na);
				if (v) {
					pa[na] = v;
					if (na == 'title') {
						pa.description = v;
					}
				}
			});
			pa.validation = n.className;

			im.title = this.__serialize(pa);

			return im;
		},
		
		__createDiv : function(cl, n) {
			var im, dom = this.editor.dom, pa = {}, ti = '', s = n.style;

			// Create image
			im = dom.create('div', {
				'class' : cl
			});
			
			if (cl == 'mceplgItemButton') {
				im.style.width = '100px';
				im.style.cssFloat = 'none';
				im.style.display = 'block';
				im.style.textAlign = 'center';
				im.style.padding = '3px 10px';
				im.style.margin = '1px 5px';
				im.style.fontWeight = 'bold';
				im.style.border = '2px outset grey';
				im.style.background = 'lightgrey';
				
				pa.type = 'button';
				
			} else {
				im.style.height = '14pt';
				im.style.width = n.style.width;
				im.style.cssFloat = 'none';
				im.style.display = 'block';
				im.style.border = '2px inset grey';
				im.style.background = 'url(' + this.url + '/img/select_arrow.gif) top right no-repeat';
				
				pa.size = parseInt(n.style.width);
				pa.type = 'select';
			}

			// Setup base parameters
			each(['id', 'name', 'value', 'title', 'type'], function(na) {
				var v = dom.getAttrib(n, na);
				if (v) {
					pa[na] = v;
					if (na == 'title') {
						pa.description = v;
					}
				}
			});
			
			if (dom.getAttrib(n, 'value')) {
				im.innerHTML = dom.getAttrib(n, 'value');
			} else {
				im.innerHTML = '&nbsp;';
			}
			if (!im.hasAttribute('id')) {
				im.setAttribute('id', n.getAttribute('name'));
			}

			// Add optional parameters
			var cnt = 0;
			each(dom.select('span', n), function(opt) {
				if (dom.hasClass(opt, 'mceItemOption')) {
					var ov = opt.getAttribute('value') + '##' + opt.innerHTML + '##' + (opt.hasAttribute('selected') ? 1 : 0);
					im.setAttribute('mce_value_' + cnt++, ov);
				}
			});

			im.title = this.__serialize(pa);

			return im;
		},
		
		__buildInput : function(o, n) {
			var ob, ed = this.editor, dom = ed.dom, p = this.__parse(n.title);

			ob = dom.create('span', {
				mce_name : o.name || 'input',
				name : p.name,
				title : p.description
			});
			
			if ( ( (p.type == 'checkbox') && (p.checked) ) || ( (p.type == 'radio') && (p.defaultValue == 'current') ) ) {
				ob.setAttribute('checked', 'checked');
			}
			
			if (p.type == 'button') {
				ob.className = 'button';
			} else if (p.mandatory) {
				ob.className = 'mandatory';
			}
			if (p.validation) {
				ob.className += ' ' + p.validation;
			}
			
			if (p.type == 'multi') {
				ob.innerHTML = p.value;
			} else {
				ob.setAttribute('value', p.value || '');
				ob.setAttribute('type', o.type);
			}
			
			if (p.size) {
				ob.style.width = p.size + 'px';
			}
			if (p.rows) {
				ob.style.height = p.rows + 'px';
			}

			return ob;
		},

		__buildSelect : function(o, n) {
			var ob, ed = this.editor, dom = ed.dom, p = this.__parse(n.title);

			ob = dom.create('span', {
				mce_name : 'select',
				name : p.name,
				title : p.description,
				mce_size : p.size,
				size : 1
			});
			ob.style.width = p.size + 'px';
			
			each (n.attributes, function(v, k) {
				if (/^(mce_value_[0-9]+)$/.test(v.nodeName)) {
					var val = v.nodeValue.split('##');
					var opt = {mce_name : 'option', 'value' : val[0]};
					if (val[2] == 1) {
						opt.selected = 'selected';
					}
					dom.add(ob, 'span', opt, val[1]);
				}
			});

			return ob;
		},

		// Return an Object defined by a JSON-String without {}
		__parse : function(s) {
			return tinymce.util.JSON.parse('{' + s + '}');
		},

		// Return an Object as a JSON-String without {}
		__serialize : function(o) {
			return tinymce.util.JSON.serialize(o).replace(/[{}]/g, '');
		}

	});

	// Register plugin
	tinymce.PluginManager.add('delightform', tinymce.plugins.DelightFormular);
})();

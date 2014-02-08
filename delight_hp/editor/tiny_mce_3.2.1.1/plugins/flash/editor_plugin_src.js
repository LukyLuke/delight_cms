/**
 * $RCSfile: editor_plugin_src.js,v $
 * $Revision: 1.3 $
 * $Date: 2009/01/27 15:47:39 $
 *
 * @author Moxiecode
 * @copyright Copyright © 2004-2006, Moxiecode Systems AB, All rights reserved.
 */

(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('flash');

	tinymce.create('tinymce.plugins.FlashPlugin', {
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
			t.className = 'mceplgItemFlash';
			
			// Register the command
			ed.addCommand('mceFlash', function() {
				var name = "", swffile = "", swfwidth = "", swfheight = "", action = "insert";
				var focusElm = ed.selection.getNode();
				// Is selection a image
				if (focusElm != null && focusElm.nodeName.toLowerCase() == "img") {
					name = ed.dom.getAttrib(focusElm, 'class');

					if (name.indexOf(t.className) == -1) // Not a Flash
						return true;

					// Get rest of Flash items
					swffile = ed.dom.getAttrib(focusElm, 'alt');
					swfwidth = ed.dom.getAttrib(focusElm, 'width');
					swfheight = ed.dom.getAttrib(focusElm, 'height');
					action = "update";
				}
				
				ed.windowManager.open({
					file : url + '/flash.htm',
					width : 430 + parseInt(ed.getLang('example.delta_width', 0)),
					height : 175 + parseInt(ed.getLang('example.delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url, // Plugin absolute URL
					swffile : swffile,
					swfwidth : swfwidth,
					swfheight : swfheight
				});
			});

			// Register example button
			ed.addButton('flash', {
				title : 'flash.desc',
				cmd : 'mceFlash',
				image : url + '/img/flash.gif'
			});

			// Add a node change handler, selects the button in the UI when a image is selected
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('flash', ( (n.nodeName == 'IMG') &&  (ed.dom.getAttrib(n, 'class').indexOf(t.className) == 0)) );
			});
			
			ed.onInit.add(function() {
				if (ed.settings.content_css !== false)
					ed.dom.loadCSS(url + '/css/content.css');
			});
			
			// get_from_editor_dom
			ed.onPreProcess.add(function(ed, o) {
				var imgs = o.node.getElementsByTagName("img");
				for (var i=0; i<imgs.length; i++) {
					if (ed.dom.getAttrib(imgs[i], "class") == t.className) {
						var src = ed.dom.getAttrib(imgs[i], "alt");

						imgs[i].setAttribute('alt', src);
						imgs[i].setAttribute('title', src);
					}
				}
			});
			
			// insert_to_editor
			// When HTML gets inserted into the editor (while loading or after SourceView)
			ed.onBeforeSetContent.add(function(ed, o) {
				var startPos = 0;
				var embedList = new Array();

				// Fix the embed and object elements
				o.content = o.content.replace(new RegExp('<[ ]*embed','gi'),'<embed');
				o.content = o.content.replace(new RegExp('<[ ]*/embed[ ]*>','gi'),'</embed>');
				o.content = o.content.replace(new RegExp('<[ ]*object','gi'),'<object');
				o.content = o.content.replace(new RegExp('<[ ]*/object[ ]*>','gi'),'</object>');
				
				// Parse all embed tags
				while ((startPos = o.content.indexOf('<embed', startPos+1)) != -1) {
					var endPos = o.content.indexOf('>', startPos);
					var attribs = t._parseAttributes(o.content.substring(startPos + 6, endPos));
					embedList[embedList.length] = attribs;
				}

				// Parse all object tags and replace them with images from the embed data
				var index = 0;
				while ((startPos = o.content.indexOf('<object', startPos)) != -1) {
					if (index >= embedList.length)
						break;

					var attribs = embedList[index];

					// Find end of object
					endPos = o.content.indexOf('</object>', startPos);
					endPos += 9;

					// Insert image
					var contentAfter = o.content.substring(endPos);
					o.content = o.content.substring(0, startPos);
					o.content += '<img width="' + attribs["width"] + '" height="' + attribs["height"] + '"';
					o.content += ' src="' + url + '/img/spacer.gif" mce_src="' + url + '/img/spacer.gif" title="' + attribs["src"] + '"';
					o.content += ' alt="' + attribs["src"] + '" class="'+t.className+'" />' + o.content.substring(endPos);
					o.content += contentAfter;
					index++;

					startPos++;
				}

				// Parse all embed tags and replace them with images from the embed data
				var index = 0;
				while ((startPos = o.content.indexOf('<embed', startPos)) != -1) {
					if (index >= embedList.length)
						break;

					var attribs = embedList[index];

					// Find end of embed
					endPos = o.content.indexOf('>', startPos);
					endPos += 9;

					// Insert image
					var contentAfter = o.content.substring(endPos);
					o.content = o.content.substring(0, startPos);
					o.content += '<img width="' + attribs["width"] + '" height="' + attribs["height"] + '"';
					o.content += ' src="' + url + '/img/spacer.gif" mce_src="' + url + '/img/spacer.gif" title="' + attribs["src"] + '"';
					o.content += ' alt="' + attribs["src"] + '" class="'+t.className+'" />' + o.content.substring(endPos);
					o.content += contentAfter;
					index++;

					startPos++;
				}
			});

			// get_from_editor
			// When Content gets converted to HTML (before save or SourceView)
			ed.onGetContent.add(function(ed, o) {
				// Parse all img tags and replace them with object+embed
				var startPos = -1;
				while ((startPos = o.content.indexOf('<img', startPos+1)) != -1) {
					var endPos = o.content.indexOf('/>', startPos);
					var attribs = t._parseAttributes(o.content.substring(startPos + 4, endPos));

					// No attributes found
					if (attribs === null)
						continue;

					// Is not flash, skip it
					if (attribs['class'] != t.className)
						continue;

					endPos += 2;

					var embedHTML = '';
					var wmode = ed.getParam("flash_wmode", "window");
					var quality = ed.getParam("flash_quality", "high");
					var menu = ed.getParam("flash_menu", "false");

					// Insert object + embed
					embedHTML += '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"';
					embedHTML += ' codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,29,0"';
					embedHTML += ' width="' + attribs["width"] + '" height="' + attribs["height"] + '">';
					embedHTML += '<param name="movie" value="' + attribs["title"] + '" />';
					embedHTML += '<param name="quality" value="' + quality + '" />';
					embedHTML += '<param name="menu" value="' + menu + '" />';
					embedHTML += '<param name="wmode" value="' + wmode + '" />';
					embedHTML += '<embed src="' + attribs["title"] + '" wmode="' + wmode + '" quality="' + quality + '" menu="' + menu + '" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="' + attribs["width"] + '" height="' + attribs["height"] + '"></embed></object>';

					// Insert embed/object chunk
					chunkBefore = o.content.substring(0, startPos);
					chunkAfter = o.content.substring(endPos);
					o.content = chunkBefore + embedHTML + chunkAfter;
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
				longname : 'Flash plugin',
				author : 'Lukas Zurschmiede',
				authorurl : 'http://www.delightsoftware.com',
				infourl : 'http://www.delightsoftware.com',
				version : "2.0"
			};
		},
		
		/// Private plugin internal functions
		
		_parseAttributes : function(attribute_string) {
			var attributeName = "";
			var attributeValue = "";
			var withInName;
			var withInValue;
			var attributes = null;
			var whiteSpaceRegExp = new RegExp('^[ \n\r\t]+', 'g');

			if (attribute_string == null || attribute_string.length < 2)
				return null;

			withInName = withInValue = false;

			for (var i=0; i<attribute_string.length; i++) {
				var chr = attribute_string.charAt(i);

				if ((chr == '"' || chr == "'") && !withInValue)
					withInValue = true;
				else if ((chr == '"' || chr == "'") && withInValue) {
					if (attributes === null)
						attributes = new Array();
					withInValue = false;

					var pos = attributeName.lastIndexOf(' ');
					if (pos != -1)
						attributeName = attributeName.substring(pos+1);

					attributes[attributeName.toLowerCase()] = attributeValue.substring(1);

					attributeName = "";
					attributeValue = "";
				} else if (!whiteSpaceRegExp.test(chr) && !withInName && !withInValue)
					withInName = true;

				if (chr == '=' && withInName)
					withInName = false;

				if (withInName)
					attributeName += chr;

				if (withInValue)
					attributeValue += chr;
			}

			return attributes;
		}
		
	});

	// Register plugin
	tinymce.PluginManager.add('flash', tinymce.plugins.FlashPlugin);
})();

/*
	cleanup : function(type, content) {
		switch (type) {
			case "insert_to_editor_dom":
				// Force relative/absolute
				if (tinymce.getParam('convert_urls')) {
					var imgs = content.getElementsByTagName("img");
					for (var i=0; i<imgs.length; i++) {
						if (tinymce.getAttrib(imgs[i], "class") == "mceItemFlash") {
							var src = tinymce.getAttrib(imgs[i], "alt");

							if (tinymce.getParam('convert_urls'))
								src = eval(tinymce.settings['urlconverter_callback'] + "(src, null, true);");

							imgs[i].setAttribute('alt', src);
							imgs[i].setAttribute('title', src);
						}
					}
				}
				break;

			case "get_from_editor_dom":
				var imgs = content.getElementsByTagName("img");
				for (var i=0; i<imgs.length; i++) {
					if (tinymce.getAttrib(imgs[i], "class") == "mceItemFlash") {
						var src = tinymce.getAttrib(imgs[i], "alt");

						if (tinymce.getParam('convert_urls'))
							src = eval(tinymce.settings['urlconverter_callback'] + "(src, null, true);");

						imgs[i].setAttribute('alt', src);
						imgs[i].setAttribute('title', src);
					}
				}
				break;

			case "insert_to_editor":
				var startPos = 0;
				var embedList = new Array();
*/
				// Fix the embed and object elements
				//content = content.replace(new RegExp('<[ ]*embed','gi'),'<embed');
				//content = content.replace(new RegExp('<[ ]*/embed[ ]*>','gi'),'</embed>');
				//content = content.replace(new RegExp('<[ ]*object','gi'),'<object');
				//content = content.replace(new RegExp('<[ ]*/object[ ]*>','gi'),'</object>');
/*
				// Parse all embed tags
				while ((startPos = content.indexOf('<embed', startPos+1)) != -1) {
					var endPos = content.indexOf('>', startPos);
					var attribs = TinyMCE_FlashPlugin._parseAttributes(content.substring(startPos + 6, endPos));
					embedList[embedList.length] = attribs;
				}

				// Parse all object tags and replace them with images from the embed data
				var index = 0;
				while ((startPos = content.indexOf('<object', startPos)) != -1) {
					if (index >= embedList.length)
						break;

					var attribs = embedList[index];

					// Find end of object
					endPos = content.indexOf('</object>', startPos);
					endPos += 9;

					// Insert image
					var contentAfter = content.substring(endPos);
					content = content.substring(0, startPos);
					content += '<img width="' + attribs["width"] + '" height="' + attribs["height"] + '"';
					content += ' src="' + (tinymce.getParam("theme_href") + '/images/spacer.gif') + '" title="' + attribs["src"] + '"';
					content += ' alt="' + attribs["src"] + '" class="mceItemFlash" />' + content.substring(endPos);
					content += contentAfter;
					index++;

					startPos++;
				}

				// Parse all embed tags and replace them with images from the embed data
				var index = 0;
				while ((startPos = content.indexOf('<embed', startPos)) != -1) {
					if (index >= embedList.length)
						break;

					var attribs = embedList[index];

					// Find end of embed
					endPos = content.indexOf('>', startPos);
					endPos += 9;

					// Insert image
					var contentAfter = content.substring(endPos);
					content = content.substring(0, startPos);
					content += '<img width="' + attribs["width"] + '" height="' + attribs["height"] + '"';
					content += ' src="' + (tinymce.getParam("theme_href") + '/images/spacer.gif') + '" title="' + attribs["src"] + '"';
					content += ' alt="' + attribs["src"] + '" class="mceItemFlash" />' + content.substring(endPos);
					content += contentAfter;
					index++;

					startPos++;
				}

				break;

			case "get_from_editor":
				// Parse all img tags and replace them with object+embed
				var startPos = -1;

				while ((startPos = content.indexOf('<img', startPos+1)) != -1) {
					var endPos = content.indexOf('/>', startPos);
					var attribs = TinyMCE_FlashPlugin._parseAttributes(content.substring(startPos + 4, endPos));

					// Is not flash, skip it
					if (attribs['class'] != "mceItemFlash")
						continue;

					endPos += 2;

					var embedHTML = '';
					var wmode = tinymce.getParam("flash_wmode", "");
					var quality = tinymce.getParam("flash_quality", "high");
					var menu = tinymce.getParam("flash_menu", "false");

					// Insert object + embed
					embedHTML += '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"';
					embedHTML += ' codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0"';
					embedHTML += ' width="' + attribs["width"] + '" height="' + attribs["height"] + '">';
					embedHTML += '<param name="movie" value="' + attribs["title"] + '" />';
					embedHTML += '<param name="quality" value="' + quality + '" />';
					embedHTML += '<param name="menu" value="' + menu + '" />';
					embedHTML += '<param name="wmode" value="' + wmode + '" />';
					embedHTML += '<embed src="' + attribs["title"] + '" wmode="' + wmode + '" quality="' + quality + '" menu="' + menu + '" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="' + attribs["width"] + '" height="' + attribs["height"] + '"></embed></object>';

					// Insert embed/object chunk
					chunkBefore = content.substring(0, startPos);
					chunkAfter = content.substring(endPos);
					content = chunkBefore + embedHTML + chunkAfter;
				}
				break;
		}

		// Pass through to next handler in chain
		return content;
	},
*/

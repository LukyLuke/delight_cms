/**
 * $Id: editor_plugin_src.js,v 1.2 2009/01/27 15:29:23 lukas Exp $
 *
 * @author Moxiecode
 * @copyright Copyright © 2004-2008, Moxiecode Systems AB, All rights reserved.
 */

(function() {
	tinymce.create('tinymce.plugins.AdvancedImagePlugin', {
		init : function(ed, url) {
			// Register commands
			ed.addCommand('mceAdvImage', function() {
				// Internal image object like a flash placeholder
				if ( (ed.dom.getAttrib(ed.selection.getNode(), 'class').indexOf('mceItem') != -1) || (ed.dom.getAttrib(ed.selection.getNode(), 'class').indexOf('mceplgItem') != -1) )
					return;

				ed.windowManager.open({
					file : url + '/image.htm',
					width : 490 + parseInt(ed.getLang('advimage.delta_width', 0)),
					height : 395 + parseInt(ed.getLang('advimage.delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url
				});
			});
			
			/* BEGIN: delight lukas */
			ed.onPreInit.add(function() {
				// We need special parameters on ImageTags to replace them while create static sites
				ed.serializer.addRules('img[class|longdesc|usemap|src|border|alt=|title|hspace|vspace|width|height|align|dedtparams=|onmouseover|onmouseout|onclick|ondblclick|style]');
			});
			/* BEGIN: delight lukas */

			// Register buttons
			ed.addButton('image', {
				title : 'advimage.image_desc',
				cmd : 'mceAdvImage'
			});
		},

		getInfo : function() {
			return {
				longname : 'Advanced image changed by delight',
				author : 'Moxiecode Systems AB - changes by delight software gmbh',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/advimage',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('advimage', tinymce.plugins.AdvancedImagePlugin);
})();
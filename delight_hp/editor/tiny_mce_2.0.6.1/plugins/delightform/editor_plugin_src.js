/**
 * $Id: editor_plugin_src.js,v 1.1.1.1 2009/01/07 20:10:36 lukas Exp $
 *
 * @author Lukas Zurschmiede
 * @copyright Copyright © 2007, delight software gmbh, All rights reserved.
 */

/* Import plugin specific language pack */
tinyMCE.importPluginLanguagePack('delightform','de,en');

// Singleton class
var TinyMCE_delightformPlugin = {
	/**
	 * Returns information about the plugin as a name/value array.
	 * The current keys are longname, author, authorurl, infourl and version.
	 *
	 * @returns Name/value array containing information about the plugin.
	 * @type Array
	 */
	getInfo : function() {
		return {
			longname : 'Forms-Plugin for tinyMCE Editor',
			author : 'Lukas Zurschmiede',
			authorurl : 'http://www.delightsoftware.com',
			infourl : 'http://www.delightsoftware.com/',
			version : "1.0"
		};
	},

	/**
	 * Gets executed when a TinyMCE editor instance is initialized.
	 *
	 * @param {TinyMCE_Control} Initialized TinyMCE editor control instance.
	 */
	initInstance : function(inst) {
		// You can take out plugin specific parameters
		//alert("Initialization:" + tinyMCE.getParam("delightform_someparam", false));

		// Register custom keyboard shortcut
		//inst.addShortcut('ctrl', 't', 'lang_delightform_desc', 'mcedelightform');
	},

	/**
	 * Returns the HTML code for a specific control or empty string if this plugin doesn't have that control.
	 * A control can be a button, select list or any other HTML item to present in the TinyMCE user interface.
	 * The variable {$editor_id} will be replaced with the current editor instance id and {$pluginurl} will be replaced
	 * with the URL of the plugin. Language variables such as {$lang_somekey} will also be replaced with contents from
	 * the language packs.
	 *
	 * @param {string} cn Editor control/button name to get HTML for.
	 * @return HTML code for a specific control or empty string.
	 * @type string
	 */
	getControlHTML : function(cn) {
		if (cn == "delightform") {
			return tinyMCE.getButtonHTML(cn + '_config', 'lang_delightform_config', '{$pluginurl}/images/config.gif', 'mcedelightf_config', true) +
				tinyMCE.getButtonHTML(cn + '_edit', 'lang_delightform_edit', '{$pluginurl}/images/edit.gif', 'mcedelightf_edit', true) +
				tinyMCE.getButtonHTML(cn + '_checkbox', 'lang_delightform_checkbox', '{$pluginurl}/images/checkbox.gif', 'mcedelightf_checkbox', true) +
				tinyMCE.getButtonHTML(cn + '_radio', 'lang_delightform_radio', '{$pluginurl}/images/radio.gif', 'mcedelightf_radio', true) +
				tinyMCE.getButtonHTML(cn + '_select', 'lang_delightform_select', '{$pluginurl}/images/select.gif', 'mcedelightf_select', true) +
				tinyMCE.getButtonHTML(cn + '_button', 'lang_delightform_button', '{$pluginurl}/images/button.gif', 'mcedelightf_button', true) +
				'';
		}
		return "";
	},

	/**
	 * Executes a specific command, this function handles plugin commands.
	 *
	 * @param {string} editor_id TinyMCE editor instance id that issued the command.
	 * @param {HTMLElement} element Body or root element for the editor instance.
	 * @param {string} command Command name to be executed.
	 * @param {string} user_interface True/false if a user interface should be presented.
	 * @param {mixed} value Custom value argument, can be anything.
	 * @return true/false if the command was executed by this plugin or not.
	 * @type
	 */
	execCommand : function(editor_id, element, command, user_interface, value) {
		// Handle commands
		var windowArguments = {editor_id : editor_id, inline: "yes"};
		var fileName = '';
		var inst = tinyMCE.getInstanceById(editor_id);
		
		// Remember to have the "mce" prefix for commands so they don't intersect with built in ones in the browser.
		switch (command) {
			// Set an attribute
			case "mcedelightf_setattribute":
				value = value.split('=');
				if (value.length > 0) {
					if ((value.length <= 1) || (typeof(value[1]) == 'undefined') ) {
						value[1] = '';
					}
					tinyMCE.setParam('dedtformplg_'+value[0], {name:value[0], value:value[1]});
					//TinyMCE_delightformPlugin._setFormAttribute(value[0], value[1]);
				}
				return true;
				break;
			
			// formular configuration
			case "mcedelightf_config":
				fileName = 'formular_config.html';
				var attrName, attrValue;
				var keys = TinyMCE_delightformPlugin._formValidAttributes;
				for (var i = 0; i < keys.length; i++) {
					attrName = keys[i];
					attrValue = tinyMCE.getParam('dedtformplg_'+attrName, {name:attrName, value:''});
					eval('windowArguments.arg_' + attrValue.name + '="' + attrValue.value.replace(/\n/g, '\\n') + '";');
				}
				break;
				
			// new field
			case "mcedelightf_edit":
				fileName = 'control_edit.html';
				break;
			case "mcedelightf_button":
				fileName = 'control_button.html';
				break;
			case "mcedelightf_checkbox":
				fileName = 'control_checkbox.html';
				break;
			case "mcedelightf_radio":
				fileName = 'control_radio.html';
				break;
			case "mcedelightf_select":
				fileName = 'control_select.html';
				break;

			// Check what kind of Element the user is going to modify
			case "mcedelightf_modify":
				var node = inst.getFocusElement();
				switch (node.nodeName) {
					case 'TEXTAREA':
						fileName = 'control_edit.html';
						break;
					case 'INPUT':
						switch (node.type) {
							case 'text':
							case 'file':
							case 'password':
								fileName = 'control_edit.html';
								break;
							case 'submit':
							case 'reset':
							case 'button':
								fileName = 'control_button.html';
								break;
							case 'radio':
								fileName = 'control_radio.html';
								break;
							case 'checkbox':
								fileName = 'control_checkbox.html';
								break;
							default:
								return false; // Pass to next handler in chain
						}
						break;
					case 'SELECT':
						fileName = 'control_select.html';
						break;
					default:
						return false; // Pass to next handler in chain
				}
				break;

			default:
				return false; // Pass to next handler in chain
		}
		
		if (fileName != '') {
			var delightform = new Array();
			delightform['file'] = '../../plugins/delightform/' + fileName; // Relative to theme
			switch (fileName) {
				case "control_button.html":
					delightform['width'] = 400;
					delightform['height'] = 160;
					break;
				case "control_checkbox.html":
					delightform['width'] = 400;
					delightform['height'] = 190;
					break;
				case "control_edit.html":
					delightform['width'] = 400;
					delightform['height'] = 230;
					break;
				case "control_radio.html":
					delightform['width'] = 400;
					delightform['height'] = 180;
					break;
				case "control_select.html":
					delightform['width'] = 400;
					delightform['height'] = 267;
					break;
				case "formular_config.html":
					delightform['width'] = 640;
					delightform['height'] = 420;
					break;
			}

			// Open the window
			tinyMCE.openWindow(delightform, windowArguments);

			// Let TinyMCE know that something was modified
			tinyMCE.triggerNodeChange(false);
			return true;
		}
		// Pass to next handler in chain
		return false;
	},
	
	/**
	 * Gets called when HTML contents is inserted or retrived from a TinyMCE editor instance.
	 * The type parameter contains what type of event that was performed and what format the content is in.
	 * Possible valuses for type is get_from_editor, insert_to_editor, get_from_editor_dom, insert_to_editor_dom.
	 *
	 * @param {string} type Cleanup event type.
	 * @param {mixed} content Editor contents that gets inserted/extracted can be a string or DOM element.
	 * @param {TinyMCE_Control} inst TinyMCE editor instance control that performes the cleanup.
	 * @return New content or the input content depending on action.
	 * @type string
	 */
	cleanup : function(type, content, inst) {
		// before editor sows content in editsection
		//   insert_to_editor -> full content
		//   insert_to_editor_dom -> HTMLBodyElement
		//   setup_content_dom -> HTMLBodyElement
		//
		// Before save content
		//   submit_content_dom -> HTMLBodyElement
		//   get_from_editor_dom -> HTMLBodyElement
		//   get_from_editor -> complete source
		//   submit_content -> full content
		//
		// after save?
		//   get_from_editor_dom -> HTMLBodyElement
		//   get_from_deitor -> full source
		
		switch (type) {
			case "insert_to_editor_dom":
				// get first "dedtform which is a PseudoTag for formulars
				// (no chance to make a form-tag inside a form-tag [The whole Editor is a form...])
				var attr,frm = content.getElementsByTagName('dedtform');
				if (frm.length) {
					attr = frm[0].attributes;
					if (typeof attr != 'undefined') {
						for (var i = 0; i < attr.length; i++) {
							//TinyMCE_delightformPlugin._setFormAttribute(attr[i].name, attr[i].value);
							attr[i].value = attr[i].value.replace(/\"/g, '##34;').replace(/\'/g, '##39;');
							tinyMCE.setParam('dedtformplg_'+attr[i].name, attr[i]);
						}
					}
					// remove the tag
					frm[0].innerHTML = '';
					frm[0].parentNode.removeChild(frm[0]);
				}
				break;
				
				case "get_from_editor":
					var tag = '<dedtform';
					var attrName, attrValue;
					var keys = TinyMCE_delightformPlugin._formValidAttributes;
					for (var i = 0; i < keys.length; i++) {
						attrName = keys[i];
						attrValue = tinyMCE.getParam('dedtformplg_'+attrName, {name:attrName, value:''});
						tag += ' ' + attrValue.name + '="' + attrValue.value.replace(/\"/g, '##34;').replace(/\'/g, '##39;') + '"';
					}
					tag += ' />';
					content = tag + "\n" + content;
					break;
				
				// Replace all <_textarea and </_textarea occurences to normal tags
				case "insert_to_editor":
					content = content.replace(/\<_textarea/g, '<textarea');
					content = content.replace(/\<\/_textarea/g, '</textarea');
					break;
		}

		// Pass through to next handler in chain
		return content;
	},

	/**
	 * Gets called ones the cursor/selection in a TinyMCE instance changes. This is useful to enable/disable
	 * button controls depending on where the user are and what they have selected. This method gets executed
	 * alot and should be as performance tuned as possible.
	 *
	 * @param {string} editor_id TinyMCE editor instance id that was changed.
	 * @param {HTMLNode} node Current node location, where the cursor is in the DOM tree.
	 * @param {int} undo_index The current undo index, if this is -1 custom undo/redo is disabled.
	 * @param {int} undo_levels The current undo levels, if this is -1 custom undo/redo is disabled.
	 * @param {boolean} visual_aid Is visual aids enabled/disabled ex: dotted lines on tables.
	 * @param {boolean} any_selection Is there any selection at all or is there only a cursor.
	 */
	handleNodeChange : function(editor_id, node, undo_index, undo_levels, visual_aid, any_selection) {
		// Select delightform button if parent node is a strong or b
		tinyMCE.switchClass(editor_id + '_delightform_config', 'mceButtonNormal');
		tinyMCE.switchClass(editor_id + '_delightform_edit', 'mceButtonNormal');
		tinyMCE.switchClass(editor_id + '_delightform_button', 'mceButtonNormal');
		tinyMCE.switchClass(editor_id + '_delightform_checkbox', 'mceButtonNormal');
		tinyMCE.switchClass(editor_id + '_delightform_radio', 'mceButtonNormal');
		tinyMCE.switchClass(editor_id + '_delightform_select', 'mceButtonNormal');

		if (node == null) {
			return;
		}

		switch (node.nodeName) {
			case 'TEXTAREA':
				tinyMCE.switchClass(editor_id + '_delightform_edit', 'mceButtonSelected');
				return true;
			case 'INPUT':
				switch (node.type) {
					case 'text':
					case 'file':
					case 'password':
						tinyMCE.switchClass(editor_id + '_delightform_edit', 'mceButtonSelected');
						return true;
					case 'submit':
					case 'reset':
					case 'button':
						tinyMCE.switchClass(editor_id + '_delightform_button', 'mceButtonSelected');
						return true;
					case 'radio':
						tinyMCE.switchClass(editor_id + '_delightform_radio', 'mceButtonSelected');
						return true;
					case 'checkbox':
						tinyMCE.switchClass(editor_id + '_delightform_checkbox', 'mceButtonSelected');
						return true;
					default: return false;
				}
			case 'SELECT':
				tinyMCE.switchClass(editor_id + '_delightform_select', 'mceButtonSelected');
				return true;
			default: return false;
		}
	},
	
	
	// Functions for internal use only
	
	// form-tag
	_formValidAttributes : ['name','method','validate','onsuccess','onfailure','encoding','mailrcpt','mailrcptname','mailsubject','mailinform','mailsenderfield','mailpretext','mailposttext'],
	
};

// Adds the plugin class to the list of available TinyMCE plugins
tinyMCE.addPlugin("delightform", TinyMCE_delightformPlugin);

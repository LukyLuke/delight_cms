
/* Import plugin specific language pack */
// Add a comma separated list of all supported languages
tinyMCE.importPluginLanguagePack('delighttitle', 'de,en');

// Singleton class
var TinyMCE_DelighttitlePlugin = {
	/**
	 * Returns information about the plugin as a name/value array.
	 * The current keys are longname, author, authorurl, infourl and version.
	 *
	 * @returns Name/value array containing information about the plugin.
	 * @type Array 
	 */
	getInfo : function() {
		return {
			longname : 'Delight Title plugin',
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
		// Register custom keyboard shortcut
		//inst.addShortcut('ctrl', 't', 'lang_delighttitle_desc', 'mceTemplate');
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
		switch (cn) {
			case "delighttitle":
				var cmd = 'tinyMCE.execInstanceCommand(\'{$editor_id}\',\'mceTriggerDelightTitle\',false,this.value,false);';
				var field = tinyMCE.getParam("delighttitle_title_field", null);
				var value = tinyMCE.getLang('lang_delighttitle_title_desc');
				if ( (field != null) && (field != 'undefined') ) {
					value = field.value;
				}
				//var html  = '<table cellpadding="0" cellpadding="0"><tr><td style="font-size:9pt;font-weight:bold;">&nbsp;' + tinyMCE.getLang('lang_delighttitle_title_desc') + ':&nbsp;&nbsp;</td><td>';
				var html = '<input id="{$editor_id}_delightTitle" name="{$editor_id}_delightTitle" type="text" value="' + value + '" style="background-color:white;color:black;width:250px;font-size:9pt !important;vertical-align:top;" class="mceSelectList" onchange="' + cmd + '" />';
				//    html += '</td></tr></table>';
				if (tinyMCE.getParam('delighttitle_layout_field') != null) {
					html += tinyMCE.getControlHTML("separator") +
				          tinyMCE.getButtonHTML(cn, 'lang_delighttitle_desc', '{$pluginurl}/images/template.gif', 'mceDelighttitle', true);
				}
				return html;

				//return tinyMCE.getButtonHTML(cn, 'lang_delighttitle_desc', '{$pluginurl}/images/template.gif', 'mceTemplate', true);
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
		switch (command) {
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

		// Pass to next handler in chain
		return false;
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
		// Select template button if parent node is a strong or b
		if (node.parentNode.nodeName == "STRONG" || node.parentNode.nodeName == "B") {
			tinyMCE.switchClass(editor_id + '_template', 'mceButtonSelected');
			return true;
		}

		// Deselect template button
		tinyMCE.switchClass(editor_id + '_template', 'mceButtonNormal');
	},

	/**
	 * Gets called when a TinyMCE editor instance gets filled with content on startup.
	 *
	 * @param {string} editor_id TinyMCE editor instance id that was filled with content.
	 * @param {HTMLElement} body HTML body element of editor instance.
	 * @param {HTMLDocument} doc HTML document instance.
	 */
	setupContent : function(editor_id, body, doc) {
	},

	/**
	 * Gets called when the contents of a TinyMCE area is modified, in other words when a undo level is
	 * added.
	 *
	 * @param {TinyMCE_Control} inst TinyMCE editor area control instance that got modified.
	 */
	onChange : function(inst) {
	},

	/**
	 * Gets called when TinyMCE handles events such as keydown, mousedown etc. TinyMCE
	 * doesn't listen on all types of events so custom event handling may be required for
	 * some purposes.
	 *
	 * @param {Event} e HTML editor event reference.
	 * @return true - pass to next handler in chain, false - stop chain execution
	 * @type boolean
	 */
	handleEvent : function(e) {
		// Display event type in statusbar
		top.status = "delighttitle plugin event: " + e.type;

		return true; // Pass to next handler
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
		switch (type) {
			case "get_from_editor":
//				alert("[FROM] Value HTML string: " + content);

				// Do custom cleanup code here

				break;

			case "insert_to_editor":
//				alert("[TO] Value HTML string: " + content);

				// Do custom cleanup code here

				break;

			case "get_from_editor_dom":
//				alert("[FROM] Value DOM Element " + content.innerHTML);

				// Do custom cleanup code here

				break;

			case "insert_to_editor_dom":
//				alert("[TO] Value DOM Element: " + content.innerHTML);

				// Do custom cleanup code here

				break;
		}

		return content;
	},

	// Private plugin internal methods

	/**
	 * This is just a internal plugin method, prefix all internal methods with a _ character.
	 * The prefix is needed so they doesn't collide with future TinyMCE callback functions.
	 *
	 * @param {string} a Some arg1.
	 * @param {string} b Some arg2.
	 * @return Some return.
	 * @type string
	 */
	_someInternalFunction : function(a, b) {
		return 1;
	}
};

// Adds the plugin class to the list of available TinyMCE plugins
tinyMCE.addPlugin("delighttitle", TinyMCE_DelighttitlePlugin);

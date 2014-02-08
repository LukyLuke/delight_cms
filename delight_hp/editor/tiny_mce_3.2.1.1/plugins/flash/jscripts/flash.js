
var FlashDialog = {
	preInit : function() {
		var url;

		tinyMCEPopup.requireLangPack();

		if (url = tinyMCEPopup.getParam("external_flash_list_url"))
			document.write('<script language="javascript" type="text/javascript" src="' + tinyMCEPopup.editor.documentBaseURI.toAbsolute(url) + '"></script>');
	},

	init : function(ed) {
		var f = document.forms[0], nl = f.elements, ed = tinyMCEPopup.editor, dom = ed.dom, n = ed.selection.getNode();
		
		tinyMCEPopup.resizeToInnerSize();

		document.getElementById("filebrowsercontainer").innerHTML = getBrowserHTML('filebrowser','file','flash','flash');

		// Image list outsrc
		var html = FlashDialog.getFlashListHTML('filebrowser','file','flash','flash');
		if (html == "")
			document.getElementById("linklistrow").style.display = 'none';
		else
			document.getElementById("linklistcontainer").innerHTML = html;

		var swffile   = tinyMCEPopup.getWindowArg('swffile');
		var swfwidth  = '' + tinyMCEPopup.getWindowArg('swfwidth');
		var swfheight = '' + tinyMCEPopup.getWindowArg('swfheight');

		if (swfwidth.indexOf('%')!=-1) {
			f.width2.value = "%";
			f.width.value  = swfwidth.substring(0,swfwidth.length-1);
		} else {
			f.width2.value = "px";
			f.width.value  = swfwidth;
		}

		if (swfheight.indexOf('%')!=-1) {
			f.height2.value = "%";
			f.height.value  = swfheight.substring(0,swfheight.length-1);
		} else {
			f.height2.value = "px";
			f.height.value  = swfheight;
		}

		f.file.value = swffile;
		f.insert.value = ed.getLang('lang_' + tinyMCEPopup.getWindowArg('action'), 'Insert', true);

		selectByValue(f, 'linklist', swffile);

		// Handle file browser
		if (isVisible('filebrowser'))
			document.getElementById('file').style.width = '230px';

		// Auto select flash in list
		if (typeof(tinyMCEFlashList) != "undefined" && tinyMCEFlashList.length > 0) {
			for (var i=0; i<f.linklist.length; i++) {
				if (f.linklist.options[i].value == tinyMCEPopup.getWindowArg('swffile'))
					f.linklist.options[i].selected = true;
			}
		}
	},

	getFlashListHTML : function() {
		if (typeof(tinyMCEFlashList) != "undefined" && tinyMCEFlashList.length > 0) {
			var html = "";

			html += '<select id="linklist" name="linklist" style="width: 250px" onfocus="tinymce.addSelectAccessibility(event, this, window);" onchange="this.form.file.value=this.options[this.selectedIndex].value;">';
			html += '<option value="">---</option>';

			for (var i=0; i<tinyMCEFlashList.length; i++)
				html += '<option value="' + tinyMCEFlashList[i][1] + '">' + tinyMCEFlashList[i][0] + '</option>';

			html += '</select>';

			return html;
		}

		return "";
	},

	insertFlash : function() {
		var ed = tinyMCEPopup.editor, t = this, f = document.forms[0], img = f.baseURI.replace(/flash\.htm/, 'img/spacer.gif');
		var html      = '';
		var file      = f.file.value;
		var width     = f.width.value;
		var height    = f.height.value;
		if (f.width2.value=='%') {
			width = width + '%';
		}
		if (f.height2.value=='%') {
			height = height + '%';
		}

		if (width == "")
			width = 100;

		if (height == "")
			height = 100;

		// Fixes crash in Safari
		if (tinymce.isWebKit)
			ed.getWin().focus();

		html += ''
			+ '<img src="'+img+'" mce_src="'+img+'" '
			+ 'width="' + width + '" height="' + height + '" '
			+ 'border="0" alt="' + file + '" title="' + file + '" class="mceplgItemFlash" />';

		ed.execCommand("mceInsertContent", true, html);
		ed.execCommand('mceRepaint');

		tinyMCEPopup.close();
	}
};

FlashDialog.preInit();
tinyMCEPopup.onInit.add(FlashDialog.init, FlashDialog);
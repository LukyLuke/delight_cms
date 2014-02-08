[MAIN_SECTION]
							<script type="text/javascript">
							<!--
							function showSection(obj,dohide) {
								var id = obj.id.replace(/sec/, 'secd');
								var dobj = null;
								var i = 0, img = 0;

								if (obj.childNodes.length > 0) {
									for (i = 0; i < obj.childNodes.length; i++) {
										if ((obj.childNodes.item(i).nodeName.toLowerCase() == 'img') && (obj.childNodes.item(i).id.substring(0,6) == 'sdelim')) {
											img = i;
											break;
										}
									}
								}
								dobj = obj;
								while (dobj = dobj.nextSibling) {
									if (dobj.nodeName.toLowerCase() == 'div') {
										break;
									}
								}
								if ( (typeof(dobj) != 'undefined') && (dobj != null) ) {
									if (dobj.nodeName.toLowerCase() != 'div') {
										dobj = null;
									}
								
									if (dobj.style.display == 'none') {
										// change the Image if there is a plus
										if ( (obj.childNodes.length > 0) && (obj.childNodes.item(img).nodeName == 'IMG')) {
											obj.childNodes.item(img).src = '[MAIN_DIR]images/admin/section_folder_open.gif';
										}
										dobj.style.display = 'block';
									} else {
										if (dohide) {
											// change the Image if there is a plus
											if ( (obj.childNodes.length > 0) && (obj.childNodes.item(img).nodeName == 'IMG')) {
												obj.childNodes.item(img).src = '[MAIN_DIR]images/admin/section_folder_close.gif';
											}
											dobj.style.display = 'none';
										}
									}
								}
							}
							//-->
							</script>
							<div id="sectionMain" class="adm_sectionContainer">
								[SECTION_CONTENT]
							</div>

								[SECTION]<table cellpadding="0" cellspacing="0" style="empty-cells:show;"><tr>
									[SECTION_DELIMITER]
									<td>
										<span id="sec[SECTION_ID]" class="adm_sectionSpan" onmouseover="this.style.cursor='pointer';" onmouseout="this.style.cursor='default';">
											[SECTION_IMAGE]&nbsp;<span onclick="showSection(this.parentNode,false);loadContent('[SECTION_ID]');" name="sectionEntry" id="section[SECTION_ID]" class="section" style="[IF_SELECTED:'text-decoration:underline;':''];">[SECTION_NAME] ([NUMBER_OF_ENTRIES])</span>
										</span>
										[SUBSECTION]
											<div style="[IF_SELECTED_ID:'display:block;':'display:none;']wordwrap:none;">[SECTION_CONTENT]</div>
										[/SUBSECTION]
									</td>
								</tr></table>
								[/SECTION]
[/MAIN_SECTION]

[SECTION_DELIMITER_IMAGE]
	[EMPTY]<img src="[MAIN_DIR]images/admin/section_folder.gif" id="sdelim[SECTION_ID]" alt="." onclick="showSection(this.parentNode,true);" align="middle" style="width:16px;height:16px;" />&nbsp;[/EMPTY]
	[ENTRY]<img src="[MAIN_DIR]images/admin/section_folder_close.gif" id="sdelim[SECTION_ID]" alt="." onclick="showSection(this.parentNode,true);" align="middle" style="width:16px;height:16px;" />&nbsp;[/ENTRY]
	[EXPANDED]<img src="[MAIN_DIR]images/admin/section_folder_open.gif" id="sdelim[SECTION_ID]" alt="." onclick="showSection(this.parentNode,true);" align="middle" style="width:16px;height:16px;" />&nbsp;[/EXPANDED]
[/SECTION_DELIMITER_IMAGE]

[SECTION_DELIMITER_LIST]
	[CLEAN][/CLEAN]
	[DOWN][/DOWN]
	[ENTRY]<td style="width:12px;"><img src="[MAIN_DIR]../images/blank.gif" style="width:12px;" alt="." /></td>[/ENTRY]
	[LAST]<td style="width:12px;"><img src="[MAIN_DIR]../images/blank.gif" style="width:12px;" alt="." /></td>[/LAST]
[/SECTION_DELIMITER_LIST]

[SECTION_CHANGE]
<html>
	<head>
		<title>section editor</title>
		<link rel="stylesheet" type="text/css" href="[MAIN_DIR]/admin/css_adminContent.css" />
		<link rel="stylesheet" type="text/css" href="[DATA_DIR]../editor/delight_editor/themes/simple/css/deditor_popup.css" />
		<link rel="stylesheet" type="text/css" href="[DATA_DIR]../editor/delight_editor/themes/simple/css/deditor_ui.css" />
		<script type="text/javascript">
			var dWebRequest = null;
			function Init() {
				if (window.opener) {
					dWebRequest = window.opener.dWebRequest;
				} else {
					dWebRequest = top.dWebRequest;
				}
			}
			function create() {
				if (document.getElementById('edt_section')) {
					if (document.getElementById('edt_section').value.length > 0) {
						dWebRequest.Init();
						dWebRequest.sendContentRequest('[SECTION_PARENT]',document.getElementById('edt_section').value,'','[SECTION_LINK_AREA]sc',"window.frames['"+window.name+"'].frames[0].parseResponse('[CONT]');");
					} else {
						showError('[LANG_VALUE:sec_001]');
					}
				} else {
					showError('[LANG_VALUE:sec_002]');
				}
			}
			function change() {
				if (document.getElementById('edt_section')) {
					if (document.getElementById('edt_section').value.length > 0) {
						dWebRequest.Init();
						dWebRequest.sendContentRequest('[SECTION_PARENT]',document.getElementById('edt_section').value,'','[SECTION_LINK_AREA]se',"window.frames['"+window.name+"'].frames[0].parseResponse('[CONT]');");
					} else {
						showError('[LANG_VALUE:sec_001]');
					}
				} else {
					showError('[LANG_VALUE:sec_002]');
				}
			}
			function closeWin() {
				var winId = parseInt(window.name.replace(/dedtWindow_/, ''));
				if (window.opener) {
					window.opener.delightEditorPopup.close();
					window.close();
				} else {
					top.dedtWindows.windows['' + winId].close();
				}
			}
			function showError(msg) {
				if (document.getElementById('adm_errormessage')) {
					document.getElementById('adm_errormessage').innerHTML = msg;
				} else {
					alert(msg);
				}
			}
			function parseResponse(msg) {
				eval(msg);
			}
		</script>
	</head>
	<body class="admpopup" onload="window.setTimeout('Init()',1000);">
		<div id="formTag" style="padding-left:5px;padding-right:5px;">
			<h3>[LANG_VALUE:sec_003]</h3>
			<p>
				[LANG_VALUE:sec_004]:
				<input type="text" name="edt_section" id="edt_section" value="[SECTION_NAME]" size="50" />
			</p>
		</div>
		<div id="adm_errormessage" style="color:red;font-weight:bold;text-align:center;">&nbsp;</div>
		<div id="adm_buttonbar">
			<div style="float:left;">
				<input type="button" value="[LANG_VALUE:sec_006]" onclick="closeWin();" id="cancel" />
			</div>
			<div style="float:right;">
				<input type="button" value="[LANG_VALUE:sec_005]" onclick="[ADMIN_FUNCTION]();" id="insert" />
			</div>
		</div>
	</body>
</html>
[/SECTION_CHANGE]

[SECTION_DELETE]
<html>
	<head>
		<title>section editor</title>
		<link rel="stylesheet" type="text/css" href="[MAIN_DIR]/admin/css_adminContent.css" />
		<link rel="stylesheet" type="text/css" href="[DATA_DIR]../editor/delight_editor/themes/simple/css/deditor_popup.css" />
		<link rel="stylesheet" type="text/css" href="[DATA_DIR]../editor/delight_editor/themes/simple/css/deditor_ui.css" />
		<script type="text/javascript">
			var dWebRequest = null;
			function Init() {
				if (window.opener) {
					dWebRequest = window.opener.dWebRequest;
				} else {
					dWebRequest = top.dWebRequest;
				}
			}
			function sdelete() {
				dWebRequest.Init();
				dWebRequest.sendContentRequest('[SECTION_ID]','[SECTION_ID]','','[SECTION_LINK_AREA]sd',"window.frames['"+window.name+"'].frames[0].parseResponse('[CONT]');");
			}
			function closeWin() {
				var winId = parseInt(window.name.replace(/dedtWindow_/, ''));
				if (window.opener) {
					window.opener.delightEditorPopup.close();
					window.close();
				} else {
					top.dedtWindows.windows['' + winId].close();
				}
			}
			function showError(msg) {
				if (document.getElementById('adm_errormessage')) {
					document.getElementById('adm_errormessage').innerHTML = msg;
				} else {
					alert(msg);
				}
			}
			function parseResponse(msg) {
				eval(msg);
			}
		</script>
	</head>
	<body class="admpopup" onload="window.setTimeout('Init()',1000);">
		<div id="formTag" style="padding-left:5px;padding-right:5px;">
			<h3>[LANG_VALUE:sec_008]</h3>
			<p>
				<div class="admdescr">[LANG_VALUE:sec_009]: <strong>[SECTION_NAME]</strong><br />[LANG_VALUE:sec_010]</div>
			</p>
		</div>
		<div id="adm_errormessage" style="color:red;font-weight:bold;text-align:center;">&nbsp;</div>
		<div id="adm_buttonbar">
			<div style="float:left;">
				<input type="button" value="[LANG_VALUE:sec_006]" onclick="closeWin();" id="cancel" />
			</div>
			<div style="float:right;">
				<input type="button" value="[LANG_VALUE:sec_005]" onclick="sdelete();" id="insert" />
			</div>
		</div>
	</body>
</html>
[/SECTION_DELETE]

[SUCCESS_INSERT]
	if (window.opener) {
		var op=window.opener;
	} else {
		var op=top;
	}
	op.location.href='[BASE_LINK]adm=[ADMIN_SECTION]&sec=[SECTION_ID]';
	closeWin();
[/SUCCESS_INSERT]
[FAILED_INSERT]
	showError("[LANG_VALUE:sec_007]");
[/FAILED_INSERT]

[SUCCESS_ACTION]
	if (window.opener) {
		var op=window.opener;
	} else {
		var op=top;
	}
	op.location.href='[BASE_LINK]adm=[ADMIN_SECTION]&sec=[SECTION_ID]';
	closeWin();
[/SUCCESS_ACTION]
[FAILED_ACTION]
	showError("[CONTENT]");
[/FAILED_ACTION]

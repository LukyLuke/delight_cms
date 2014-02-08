[STYLE_INCLUDE]css_boxes.css[/STYLE_INCLUDE]

[LAYOUT]
[BASE]
						<script type="text/javascript">
						// load content from a section
						var csid = [SECTION_ID];
						function loadContent(lnk) {
							csid = lnk;
							dWebRequest.Init();
							dWebRequest.sendContentRequest('adm_prgList',lnk,'','prg',"replaceContent('[CONT]');");
							var obj = document.getElementById('adm_prgList');
							var node = null;
							if (obj.childNodes.length > 0) {
								for (var i = obj.childNodes.length; i > 0; i--) {
									node = obj.childNodes[i-1];
									if ((node.nodeType == 1) && ( (node.className == 'symbolbar') || (node.getAttribute('class') == 'symbolbar') )) {
										continue;
									}
									obj.removeChild(node);
								}
							}
							var loading = document.createElement('div');
							loading.style.display = 'block';
							loading.style.textAlign = 'center';
							loading.style.padding = '10px';
							//loading.innerHTML = '<img src="[MAIN_DIR]/admin/ajax_loading.gif" style="width:220px;height:19px;">';
							loading.id = 'loading_div';
							obj.appendChild(loading);

							var obj = document.getElementsByName('sectionEntry');
							if (obj.length > 0) {
								for (var i = 0; i < obj.length; i++) {
									obj[i].style.textDecoration = 'none';
								}
							}
							if (document.getElementById('section' + lnk)) {
								document.getElementById('section' + lnk).style.textDecoration = 'underline';
							}
						}
						function replaceContent(cont) {
							if (document.getElementById('adm_prgList')) {
								var obj = document.getElementById('adm_prgList');
								if (document.getElementById('loading_div')) {
									document.getElementById('loading_div').parentNode.removeChild(document.getElementById('loading_div'));
								}
								obj.innerHTML = obj.innerHTML + cont;
							}
						}
						
						function createDownload() {
							var nlnk = '[CREATE_PROGRAM_LINK]';
							var par = {  };
							var tpl = { file : nlnk + '0&sec=' + csid, width : '500px', height : '150px' };
							delightEditor.execCommand('admpopup', false, {template : tpl, param : par});
						}
						function changeDownload(nid) {
							var nlnk = '[CREATE_PROGRAM_LINK]';
							var par = {  };
							var tpl = { file : nlnk + nid + '&sec=' + csid, width : '500px', height : '150px' };
							delightEditor.execCommand('admpopup', false, {template : tpl, param : par});
						}
						function changeTexts(nid) {
							var nlnk = '[CHANGE_PROGRAM_LINK]';
							var par = {  };
							var tpl = { file : nlnk + nid + '&sec=' + csid, width : '500px', height : '450px' };
							delightEditor.execCommand('admpopup', false, {template : tpl, param : par});
						}
						function showDownload(ilnk, rw, rh) {
							openWindow(ilnk,rw,rh);
						}
						function deleteDownload(nid) {
							var nlnk = '[DELETE_PROGRAM_LINK]';
							var par = {  };
							var tpl = { file : nlnk + nid, height : '200px' };
							delightEditor.execCommand('admpopup', false, {template : tpl, param : par});
						}
						
						function createSection() {
							var slnk = '[CREATE_SECTION_LINK]';
							var par = {  };
							var tpl = { file : slnk + csid, height : '130px' };
							delightEditor.execCommand('admpopup', false, {template : tpl, param : par});
						}
						function changeSection() {
							var slnk = '[CHANGE_SECTION_LINK]';
							var par = {  };
							var tpl = { file : slnk + csid, height : '130px' };
							delightEditor.execCommand('admpopup', false, {template : tpl, param : par});
						}
						function deleteSection() {
							var slnk = '[DELETE_SECTION_LINK]';
							var par = {  };
							var tpl = { file : slnk + csid, height : '250px' };
							delightEditor.execCommand('admpopup', false, {template : tpl, param : par});
						}
						
						function initEditor() {
							setTextareaToDelightEdit('dedt_ImageContent', 'admin');
						}
						window.setTimeout('initEditor()', 500);
						</script>
					
						<div class="admbase">
							<div class="admhead">[LANG_VALUE:prg_001]</div>
							<div class="admcont">
								<div class="admdescr">[LANG_VALUE:prg_002]</div>
								<table cellpadding="0" cellspacing="0" style="width:100%;margin-right:2px;">
									<tr>
										<td id="adm_sectionList" style="width:152px;vertical-align:top;">
											<div class="symbolbar">
												<div class="symbol" onclick="createSection();"><img src="[MAIN_DIR]/images/admin/symbol_create.gif" style="width:16px;height:16px;border-width:0px;" align="absmiddle" /></div>
												<div class="symbol" onclick="changeSection();"><img src="[MAIN_DIR]/images/admin/symbol_edit.gif" style="width:16px;height:16px;border-width:0px;" align="absmiddle" /></div>
												<div class="symbol" onclick="deleteSection();"><img src="[MAIN_DIR]/images/admin/symbol_delete.gif" style="width:16px;height:16px;border-width:0px;" align="absmiddle" /></div>
											</div>
											[SECTION_LIST]
										</td>
										<td id="adm_prgList" style="vertical-align:top;">
											<div class="symbolbar">
												<div class="symbol" onclick="createDownload();"><img src="[MAIN_DIR]/images/admin/symbol_create.gif" style="width:16px;height:16px;border-width:0px;" align="absmiddle" />&nbsp;[LANG_VALUE:prg_006]</div>
											</div>
											[CONTENT]
										</td>
									</tr>
								</table>
								
								[PROGRAM_LIST]
									<div class="admcontent">
										[SYMBOLBAR]<div class="symbolbar">
											<div class="symbol" onclick="showDownload('[PROGRAM_DETAIL_LINK]',650,400);"><img src="[MAIN_DIR]/images/admin/symbol_show.gif" style="width:16px;height:16px;border-width:0px;" align="absmiddle" />&nbsp;[LANG_VALUE:prg_003]</div>
											<div class="symbol" onclick="changeDownload('[CURRENT_PROGRAM_ID]');"><img src="[MAIN_DIR]/images/admin/symbol_image.gif" style="width:16px;height:16px;border-width:0px;" align="absmiddle" />&nbsp;[LANG_VALUE:prg_004]</div>
											<div class="symbol" onclick="changeTexts('[CURRENT_PROGRAM_ID]');"><img src="[MAIN_DIR]/images/admin/symbol_edit.gif" style="width:16px;height:16px;border-width:0px;" align="absmiddle" />&nbsp;[LANG_VALUE:prg_015]</div>
											<div class="symbol" onclick="deleteDownload('[CURRENT_PROGRAM_ID]');"><img src="[MAIN_DIR]/images/admin/symbol_delete.gif" style="width:16px;height:16px;border-width:0px;" align="absmiddle" />&nbsp;[LANG_VALUE:prg_010]</div>
<!--											<div class="symbol" onclick="selectdwonload('[CURRENT_PROGRAM_ID]');"><img src="[MAIN_DIR]/images/admin/symbol_select.gif" style="width:16px;height:16px;border-width:0px;" align="absmiddle" />&nbsp;[LANG_VALUE:prg_016]</div>-->
<!--											<div class="symbol" onclick="moveSelected('[CURRENT_PROGRAM_ID]');"><img src="[MAIN_DIR]/images/admin/symbol_move2.gif" style="width:16px;height:16px;border-width:0px;" align="absmiddle" />&nbsp;[LANG_VALUE:prg_017]</div>-->
										</div>[/SYMBOLBAR]
										
										<div style="margin:2px;">
											<table cellpadding="0" cellspacing="0" class="dload" style="width:100%;margin-bottom:5px;border:1px dotted rgb(200,200,200);background-color: rgb(245,245,245);">
												<tr>
													<td class="dload_img" style="width:50px;"">
														<img class="dload" src="[PROGRAM_ICON]" style="width:[PROGRAM_ICON_WIDTH]px;height:[PROGRAM_ICON_HEIGHT]px;" alt="[PROGRAM_FILE]" />
													</td>
													<td class="dload_cont">
														<div class="dload_detail" style="padding:3px;">
															<strong class="dload"><u>[LANG_VALUE:msg_014] [PROGRAM_TITLE]</u> (<em>[PROGRAM_FILE]</em>)</strong><br />
															<u>[LANG_VALUE:msg_020]:</u> [PROGRAM_TYPE_EXT]<br />
															<u>[LANG_VALUE:msg_019]:</u> [PROGRAM_SIZE]<br />
															<u>[LANG_VALUE:msg_015]:</u> [PROGRAM_DATE_SMALL]<br />
															<u>[LANG_VALUE:msg_017]:</u> [PROGRAM_DOWNLOADED]
														</div>
													</td>
													<td class="dload_last">&nbsp;</td>
												</tr>
											</table>
										</div>
									</div>
								[/PROGRAM_LIST]
								
							</div>
							<div class="admfoot">&nbsp;</div>
						</div>

						<div style="visibility:hidden;height:1px;overflow:hidden;position:absolute;top:50px;">
							<form action="#" method="post" onsubmit="return false;" id="form_dedt_ImageContent">
								<fieldset style="display:none;">
									<input type="hidden" name="title_dedt_ImageContent" id="title_dedt_ImageContent" value="" />
									<input type="hidden" name="layout_dedt_ImageContent" id="layout_dedt_ImageContent" value="" />
									<input type="hidden" name="options_dedt_ImageContent" id="options_dedt_ImageContent" value="" />
									<input type="hidden" name="id_dedt_ImageContent" id="id_dedt_ImageContent" value="" />
									<input type="hidden" name="dedtid_dedt_ImageContent" id="dedtid_dedt_ImageContent" value="0" />
								</fieldset>
								<div id="dedt_ImageContent">&nbsp;</div>
							</form>
						</div>
					[/BASE]

[PROGRAM_TEXT_EDITOR]
<html>
	<head>
		<title>frame editor</title>
		<link rel="stylesheet" type="text/css" href="[MAIN_DIR]/admin/css_adminContent.css" />
		<script language="javascript" type="text/javascript" src="[DATA_DIR]../editor/tiny_mce/tiny_mce_gzip.php"></script>
		<script language="javascript" type="text/javascript" src="[DATA_DIR]../editor/delight_ajax.js"></script>
		<script type="text/javascript">
		function beginContent(a, b){}
		var dWebRequest = null;
		function Init() {
			if (document.getElementById('loadingBar')) {
				document.getElementById('loadingBar').parentNode.removeChild(document.getElementById('loadingBar'));
			}
			if (window.opener) {
				dWebRequest = window.opener.dWebRequest;
			} else {
				dWebRequest = top.dWebRequest;
			}
			try {
				tinyMCE.settings.delighttitle_title_field = document.getElementById('programTitle');
				tinyMCE.execCommand('mceAddControl', true, 'programTextarea');
			} catch (e) {}
		}
		function create() {
			if (document.getElementById('programTextarea')) {
				var titleVal = '';
				if (document.getElementById('programTitle')) {
					titleVal = document.getElementById('programTitle').value;
				}
				dWebRequest.Init();
				dWebRequest.sendContentRequest('[PROGRAM_ID]',document.getElementById('programTextarea').value,'[SECTION_ID]','prgtext',"window.frames['"+window.name+"'].frames[0].parseResponse('[CONT]');",'',titleVal);
			} else {
				showError('[LANG_VALUE:sec_002]');
			}
			return false;
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
				alert("ERROR: \n" + msg);
			}
		}
		function parseResponse(msg) {
			eval(msg);
		}
		</script>
	</head>
	<body class="admpopup" onload="window.setTimeout('Init()',1000);" marginheight="0" marginwidth="0" leftmargin="0" topmargin="0">
		<script type="text/javascript">
		tinyMCE.init({
			language : 'de',
			mode : "exact",
			theme : "advanced",
			elements : "dynamic_loaded_elements",
			dialog_type : "modal",
			relative_urls : false,
			plugins : "inlinepopups,spellchecker,save,advimage,advlink,insertdatetime,flash,searchreplace,contextmenu,paste,noneditable,delighttitle",
			theme_advanced_buttons1 : "save,|,cut,copy,paste,pastetext,pasteword,|,undo,redo,|,spellchecker,search,replace",
			theme_advanced_buttons1_add : "|,anchor,link,unlink,image,flash",
			theme_advanced_buttons2 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull",
			theme_advanced_buttons2_add : "|,bullist,numlist,|,sub,sup,|,indent,outdent,|,cleanup",
			theme_advanced_buttons3 : "insertdate,inserttime,advhr,charmap,|,removeformat,forecolor,backcolor",
			theme_advanced_buttons4 : "delighttitle",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_path_location : "bottom",
			plugin_insertdate_dateFormat : "%Y-%m-%d",
			plugin_insertdate_timeFormat : "%H:%M:%S",
			extended_valid_elements : "hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
			font_size_style_values : "small,big",
			theme_advanced_resize_horizontal : false,
			theme_advanced_resizing_use_cookie : true,
			theme_advanced_resizing : false,
			apply_source_formatting : true,
			theme_advanced_path : false,
			delighttitle_title_field : null,
			delighttitle_layout_field : null,
			spellchecker_languages : "+Deutsch=de,English=en,French=fr"
		});
		</script>
		<div id="loadingBar" style="width:100%;text-align:center;padding-top:130px;">
			Loading...<br />
			<img src="[MAIN_DIR]/admin/ajax_loading.gif" style="width:220px;height:19px;" />
		</div>
		<form action="" onsubmit="return create();">
			<fieldset style="display:none;"><input type="hidden" name="programTitle" id="programTitle" value="[PROGRAM_TITLE]" /></fieldset>
			<textarea name="programTextarea" id="programTextarea" style="width:100%;height:100%;visibility:hidden;">[PROGRAM_CONTENT]</textarea>
		</form>
	</body>
</html>
[/PROGRAM_TEXT_EDITOR]

[UPLOAD_FORM]
<html>
	<head>
		<title>frame editor</title>
		<link rel="stylesheet" type="text/css" href="[MAIN_DIR]/admin/css_adminContent.css" />
		<link rel="stylesheet" type="text/css" href="[DATA_DIR]../editor/delight_editor/themes/simple/css/deditor_popup.css" />
		<link rel="stylesheet" type="text/css" href="[DATA_DIR]../editor/delight_editor/themes/simple/css/deditor_ui.css" />
		<script type="text/javascript">
		function Init() {}
		function loading() {
			if (document.getElementById('formTag')) {
				document.getElementById('formTag').style.display = 'none';
			}
			if (document.getElementById('loadingBar')) {
				document.getElementById('loadingBar').style.display = 'visible';
			}
			return true;
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
		function displayTab(tab, panel, type) {
			var typeElm = document.getElementsByName('textOptions')[0];
			var panelElm = document.getElementById(panel);
			var panelContainerElm = panelElm ? panelElm.parentNode : null;
			var tabElm = document.getElementById(tab);
			var tabContainerElm = tabElm ? tabElm.parentNode : null;

			if (typeElm && type) {
				typeElm.value = type;
			}
			
			if (tabElm && tabContainerElm) {
				// Hide all other tabs
				var nodes = tabContainerElm.childNodes;
				for (var i = 0; i < nodes.length; i++) {
					if (nodes[i].nodeName == "LI") {
						nodes[i].className = '';
					}
				}
				// Show selected tab
				tabElm.className = 'current';
			}

			if (panelElm && panelContainerElm) {
				// Hide all other panels
				var nodes = panelContainerElm.childNodes;
				for (var i = 0; i < nodes.length; i++) {
					if (nodes[i].nodeName == "DIV") {
						nodes[i].className = 'panel';
					}
				}
				// Show selected panel
				panelElm.className = 'current';
			}
		}
		function setLocalfile(fn, id) {
			var inp = document.getElementById('localfile');
			var file = document.getElementById(id);
			var fileCont = file ? file.parentNode : null;
			if (file) {
				inp.value = fn;
			}
			if (file && fileCont) {
				var nodes = fileCont.childNodes;
				var cnt = 0;
				for (var i = 0; i < nodes.length; i++) {
					if (nodes[i].nodeName == 'DIV') {
						nodes[i].className = 'dynfile' + (cnt%2 ? "odd" : "even");
						cnt++;
					}
				}
				file.className = 'dynfilesel';
			}
		}
		</script>
	</head>
	<body class="admpopup" onload="window.setTimeout('Init()',1000);" marginheight="0" marginwidth="0" leftmargin="0" topmargin="0">
		<div id="loadingBar" style="width:100%;text-align:center;padding-top:130px;display:none;">
			Loading...<br />
			<img src="[MAIN_DIR]/admin/ajax_loading.gif" style="width:220px;height:19px;" />
		</div>
		<div id="formTag" style="padding-left:5px;padding-right:5px;">
			<!-- Tabs on top -->
			<div class="tabs">
				<ul>
					<li id="clientupload_tab" class="current"><span><a href="javascript:displayTab('clientupload_tab','clientupload_panel','local');" onmousedown="return false;">[LANG_VALUE:prg_018]</a></span></li>  <!-- delight -->
					<li id="localupload_tab"><span><a href="javascript:displayTab('localupload_tab','localupload_panel','ftp');" onmousedown="return false;">[LANG_VALUE:prg_019]</a></span></li>
					<li id="remoteupload_tab"><span><a href="javascript:displayTab('remoteupload_tab','remoteupload_panel','remote');" onmousedown="return false;">[LANG_VALUE:prg_020]</a></span></li>
				</ul>
			</div>
			
			<form method="post" action="[UPLOAD_ACTION]" onsubmit="return loading();" enctype="multipart/form-data">
				[UPLOAD_HIDDEN_FIELDS]
				<div class="panel_wrapper">
					<!-- Client Fileupload -->
					<div id="clientupload_panel" class="panel current" style="height:75px;">
						<!--<h3>[LANG_VALUE:prg_014]</h3>-->
						<br />
						<input type="file" name="file_field" id="file_field" value="" size="50" />
						<br /><br />
						<span style="color:rgb(180,10,10);font-style:italic;font-weight:bold;">([LANG_VALUE:prg_016] [UPLOAD_MAX_FILE_SIZE])</span>
					</div>

					<!-- Client Fileupload -->
					<div id="localupload_panel" class="panel" style="height:75px;">
						<input type="hidden" name="textTitle" id="localfile" value="" />
						<div class="dynfilelist">
							<div class="dynfilesel" id="fl0" onclick="setLocalfile('', 'fl0');">[LANG_VALUE:prg_022]</div>
							[FILE_ENTRY]<div class="dynfile[EVENODD:even:odd]" id="[FILE_ID]" onclick="setLocalfile('[FILE_NAME]', '[FILE_ID]');">[NOPERMISSION:<img src="/delight_hp/template/images/admin/nopermission.gif" style="width:16px;height:16px;" align="absmiddle" /> ][FILE_NAME] ([FILE_SIZE])</div>[/FILE_ENTRY]
						</div>
					</div>
					
					<!-- Remote HTTP/FTP Fileupload -->
					<div id="remoteupload_panel" class="panel" style="height:75px;">
						<br />
						<span>[LANG_VALUE:prg_021]</span>
						<br />
						<input type="text" name="textContent" id="remotefile" value="" size="50" />
					</div>

				</div>
			</form>
			
		</div>
		<div id="adm_errormessage" style="color:red;font-weight:bold;text-align:center;">&nbsp;</div>
		<div id="adm_buttonbar">
			<div style="float:left;">
				<input type="button" value="[LANG_VALUE:sec_006]" onclick="closeWin();" id="cancel" />
			</div>
			<div style="float:right;">
				<input type="button" value="[LANG_VALUE:sec_005]" onclick="document.forms[0].submit();" id="insert" />
			</div>
		</div>
	</body>
</html>
[/UPLOAD_FORM]

[/LAYOUT]

[PROGRAM_DELETE]
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
				dWebRequest.sendContentRequest('[PROGRAM_ID]','[PROGRAM_ID]','[SECTION_ID]','prgdel',"window.frames['"+window.name+"'].frames[0].parseResponse('[CONT]');");
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
	<body class="admpopup" onload="Init();">
		<div class="admcont">
			<h3>[LANG_VALUE:prg_007]</h3>
		</div>
		<div class="admdescr">
			<table cellpadding="0" cellspacing="0" class="dload" style="width:100%;margin-bottom:5px;border:1px dotted rgb(200,200,200);background-color: rgb(245,245,245);">
				<tr>
					<td style="width:50px;text-align:center;vertical-align:middle;">
						<img class="dload" src="[PROGRAM_ICON]" style="width:[PROGRAM_ICON_WIDTH]px;height:[PROGRAM_ICON_HEIGHT]px;" alt="[PROGRAM_FILE]" />
					</td>
					<td>
						<div class="dload_detail" style="padding:3px;">
							<strong class="dload"><u>[LANG_VALUE:msg_014] [PROGRAM_TITLE]</u> (<em>[PROGRAM_FILE]</em>)</strong><br />
							<u>[LANG_VALUE:msg_020]:</u> [PROGRAM_TYPE_EXT]<br />
							<u>[LANG_VALUE:msg_019]:</u> [PROGRAM_SIZE]<br />
							<u>[LANG_VALUE:msg_015]:</u> [PROGRAM_DATE_SMALL]<br />
							<u>[LANG_VALUE:msg_017]:</u> [PROGRAM_DOWNLOADED]
						</div>
					</td>
				</tr>
			</table>
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
[/PROGRAM_DELETE]

[SUCCESS_INSERT]
<html>
	<head>
		<script type="text/javascript">
		function closeWin() {
			if (window.opener) {
				var op=window.opener;
			} else {
				var op=top;
			}
			op.location.href='[BASE_LINK]adm=[ADMIN_SECTION]&sec=[SECTION_ID]';
			var winId = parseInt(window.name.replace(/dedtWindow_/, ''));
			if (window.opener) {
				window.opener.delightEditorPopup.close();
				window.close();
			} else {
				top.dedtWindows.windows['' + winId].close();
			}
		}
		</script>
	</head>
	<body onload='closeWin();'></body>
</html>
[/SUCCESS_INSERT]
[FAILED_INSERT]
<html>
	<head>
	</head>
	<body>
		<div class="error">[ERROR_MESSAGE]</div>
	</body>
</html>
[/FAILED_INSERT]

[XLAYOUT]

					[PROGRAM_MIRROR]
					<div>
						[INSERT_ERROR]
							<table cellpadding="0" cellspacing="0" style="width:100%;">
								<colgroup>
									<col style="min-width:5px;width:5px;max-width:5px;" />
									<col style="min-width:200px;" />
									<col style="min-width:5px;width:5px;max-width:5px;" />
								</colgroup>
								<tr>
									<td class="det_tab_h1"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
									<td class="det_tab_h2"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
									<td class="det_tab_h3"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
								</tr>
								<tr>
									<td class="det_tab_t1"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
									<td class="det_tab_t2" style="text-align:center;"><h2 style="color:rgb(150,50,50);">ERROR:</h2></td>
									<td class="det_tab_t3"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
								</tr>
								<tr>
									<td class="det_tab_c1"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
									<td class="det_tab_c2_1" style="text-align:center;padding:10px;">
										<span class="msgError">[ERROR_MESSAGE]</span>
									</td>
									<td class="det_tab_c3"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
								</tr>
								<tr>
									<td class="det_tab_f1"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
									<td class="det_tab_f2"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
									<td class="det_tab_f3"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
								</tr>
							</table>
						[/INSERT_ERROR]
						<table cellpadding="0" cellspacing="0" style="width:560px;">
							<colgroup>
								<col style="" />
								<col style="width:80px;" />
								<col style="width:100px;" />
							</colgroup>
							<tr>
								<td colspan="3" style="background-color:rgb(230,230,230);font-weight:bold;font-size:11pt;color:rgb(50,50,50);text-align:left;padding:3px;border:1px solid rgb(150,150,150);border-bottom-width:0px;">
									[LANG_VALUE:prg_021]
								</td>
							</tr>
							[PROGRAM_MIRROR_ENTRY]<tr>
								<td style="padding:5px;border:1px solid rgb(150,150,150);border-bottom-width:0px;border-right-width:0px;background-color:rgb(250,250,250);">
									<strong>[PROGRAM_MIRROR_URL]</strong> <em>([PROGRAM_MIRROR_LAST_UPDATE])</em>
								</td>
								<td style="padding:5px;border-top:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
									<a href="[MIRROR_ACTIVE_LINK]"><strong style="color:[IS_ACTIVE:'rgb(30,180,0)':'rgb(160,0,0)'];">[PROGRAM_MIRROR_IS_ACTIVE]</strong></a>
								</td>
								<td style="padding:5px;border:1px solid rgb(150,150,150);border-bottom-width:0px;border-left-width:0px;background-color:rgb(250,250,250);">
									<a href="[MIRROR_CHANGE_LINK]"><img src="[MAIN_DIR]images/admin/mirrorManage_edit.gif" style="width:16px;height:16px;border-width:0px;" alt="" /></a>
									&nbsp;&nbsp;
									<a href="[MIRROR_DELETE_LINK]"><img src="[MAIN_DIR]images/admin/mirrorManage_delete.gif" style="width:16px;height:16px;border-width:0px;" alt="" /></a>
								</td>
							</tr>[/PROGRAM_MIRROR_ENTRY]
							<tr>
								<td colspan="3" style="border:1px solid rgb(150,150,150);border-top-width:0px;border-bottom-width:0px;background-color:rgb(250,250,250);"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
							</tr>
							<tr>
								<td colspan="3" style="text-align:right;background-color:rgb(230,230,230);border:1px solid rgb(150,150,150);"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
							</tr>
						</table>
						<br />
						<form method="POST" action="[FORM_LINK]" name="InsertForm">
							<fieldset style="display:none;">
								<input type="hidden" name="lan" value="[FORM_LANGUAGE]" />
								<input type="hidden" name="i" value="[FORM_PROGRAM]" />
								<input type="hidden" name="sec" value="[FORM_SECTION]" />
								<input type="hidden" name="adm" value="[FORM_INSERT_ACTION]" />
								<input type="hidden" name="m" value="[FORM_MENU]" />
							</fieldset>
							<table cellpadding="0" cellspacing="0" style="width:560px;">
								<colgroup>
									<col style="width:150px;" />
									<col style="" />
								</colgroup>
								<tr>
									<td colspan="2" style="background-color:rgb(230,230,230);font-weight:bold;font-size:11pt;color:rgb(50,50,50);text-align:left;padding:3px;border:1px solid rgb(150,150,150);">
										[LANG_VALUE:prg_022]
									</td>
								</tr>

								<tr>
									<td style="padding:10px;padding-bottom:0px;border-left:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
										[LANG_VALUE:prg_026] / [LANG_VALUE:prg_027]
									</td>
									<td style="padding:10px;padding-bottom:0px;border-right:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
										<input type="radio" name="[FORM_ACTIVE_FIELD]" style="vertical-align:middle;margin:0px;" value="1" checked="checked" /> [LANG_VALUE:prg_026]
										&nbsp;&nbsp;&nbsp;
										<input type="radio" name="[FORM_ACTIVE_FIELD]" style="vertical-align:middle;margin:0px;" value="0" /> [LANG_VALUE:prg_027]
									</td>
								</tr>
								<tr>
									<td style="padding:10px;padding-bottom:0px;border-left:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
										[LANG_VALUE:prg_023]
									</td>
									<td style="padding:10px;padding-bottom:0px;border-right:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
										<input type="text" name="[FORM_URL_FIELD]" value="http://" style="font-size:9pt;font-family:Arial, Helvetica, sans serif;width:300px;border:1px solid rgb(150,150,150);background-color:rgb(255,255,255);" />
									</td>
								</tr>
								<tr>
									<td style="padding:10px;padding-bottom:0px;border-left:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
										[LANG_VALUE:prg_024]
									</td>
									<td style="padding:10px;padding-bottom:0px;border-right:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
										<input type="text" name="[FORM_USER_FIELD]" value="" style="font-size:9pt;font-family:Arial, Helvetica, sans serif;width:300px;border:1px solid rgb(150,150,150);background-color:rgb(255,255,255);" />
									</td>
								</tr>
								<tr>
									<td style="padding:10px;padding-bottom:0px;border-left:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
										[LANG_VALUE:prg_025]
									</td>
									<td style="padding:10px;border-right:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
										<input type="text" name="[FORM_PASSWORD_FIELD]" value="" style="font-size:9pt;font-family:Arial, Helvetica, sans serif;width:300px;border:1px solid rgb(150,150,150);background-color:rgb(255,255,255);" />
									</td>
								</tr>

								<tr>
									<td colspan="2" style="text-align:right;background-color:rgb(230,230,230);border:1px solid rgb(150,150,150);">
										<input type="button" value="[LANG_VALUE:prg_022]" onclick="document.InsertForm.submit();" />
									</td>
								</tr>
							</table>
						</form>
					</div>
					[/PROGRAM_MIRROR]

					[PROGRAM_MIRROR_EDIT]
					<div>
						<form method="POST" action="[FORM_LINK]" name="InsertForm">
							<fieldset style="display:none;">
								<input type="hidden" name="lan" value="[FORM_LANGUAGE]" />
								<input type="hidden" name="i" value="[FORM_PROGRAM]" />
								<input type="hidden" name="sec" value="[FORM_SECTION]" />
								<input type="hidden" name="adm" value="[FORM_INSERT_ACTION]" />
								<input type="hidden" name="m" value="[FORM_MENU]" />
								<input type="hidden" name="edtId" value="[FORM_MIRROR_ID_VALUE]" />
							</fieldset>
							<table cellpadding="0" cellspacing="0" style="width:560px;">
								<colgroup>
									<col style="width:150px;" />
									<col style="" />
								</colgroup>
								<tr>
									<td colspan="2" style="background-color:rgb(230,230,230);font-weight:bold;font-size:11pt;color:rgb(50,50,50);text-align:left;padding:3px;border:1px solid rgb(150,150,150);">
										[LANG_VALUE:prg_022]
									</td>
								</tr>

								<tr>
									<td style="padding:10px;padding-bottom:0px;border-left:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
										[LANG_VALUE:prg_026] / [LANG_VALUE:prg_027]
									</td>
									<td style="padding:10px;padding-bottom:0px;border-right:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
										<input type="radio" name="[FORM_ACTIVE_FIELD]" style="vertical-align:middle;margin:0px;" value="1" [IS_ACTIVE:'checked="checked"':''] /> [LANG_VALUE:prg_026]
										&nbsp;&nbsp;&nbsp;
										<input type="radio" name="[FORM_ACTIVE_FIELD]" style="vertical-align:middle;margin:0px;" value="0" [IS_ACTIVE:'':'checked="checked"'] /> [LANG_VALUE:prg_027]
									</td>
								</tr>
								<tr>
									<td style="padding:10px;padding-bottom:0px;border-left:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
										[LANG_VALUE:prg_023]
									</td>
									<td style="padding:10px;padding-bottom:0px;border-right:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
										<input type="text" name="[FORM_URL_FIELD]" value="[FORM_URL_VALUE]" style="font-size:9pt;font-family:Arial, Helvetica, sans serif;width:300px;border:1px solid rgb(150,150,150);background-color:rgb(255,255,255);" />
									</td>
								</tr>
								<tr>
									<td style="padding:10px;padding-bottom:0px;border-left:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
										[LANG_VALUE:prg_024]
									</td>
									<td style="padding:10px;padding-bottom:0px;border-right:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
										<input type="text" name="[FORM_USER_FIELD]" value="[FORM_USER_VALUE]" style="font-size:9pt;font-family:Arial, Helvetica, sans serif;width:300px;border:1px solid rgb(150,150,150);background-color:rgb(255,255,255);" />
									</td>
								</tr>
								<tr>
									<td style="padding:10px;padding-bottom:0px;border-left:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
										[LANG_VALUE:prg_025]
									</td>
									<td style="padding:10px;border-right:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
										<input type="text" name="[FORM_PASSWORD_FIELD]" value="[FORM_PASSWORD_VALUE]" style="font-size:9pt;font-family:Arial, Helvetica, sans serif;width:300px;border:1px solid rgb(150,150,150);background-color:rgb(255,255,255);" />
									</td>
								</tr>

								<tr>
									<td colspan="2" style="text-align:right;background-color:rgb(230,230,230);border:1px solid rgb(150,150,150);">
										<input type="button" value="[LANG_VALUE:prg_029]" onclick="document.InsertForm.submit();" />
									</td>
								</tr>
							</table>
						</form>
					</div>
					[/PROGRAM_MIRROR_EDIT]

					[UPLOAD_FORM]
					<fieldset class="tlw">
						<legend class="tlw h2">[LANG_VALUE:prg_018]</legend>
						<form method="POST" action="[FORM_LINK]" name="MainForm" enctype="multipart/form-data">
							<fieldset style="display:none;">
								<input type="hidden" name="lan" value="[FORM_LANGUAGE]" />
								<input type="hidden" name="i" value="[FORM_PROGRAM]" />
								<input type="hidden" name="sec" value="[FORM_SECTION]" />
								<input type="hidden" name="adm" value="[FORM_ADMINACTION]" />
								<input type="hidden" name="m" value="[FORM_MENU]" />
								<input type="hidden" name="MAX_FILE_SIZE" value="52428800" />
								<input type="hidden" name="textTitle" value="[FORM_PROGRAM_UPLOAD]" />
							</fieldset>
							<table cellpadding="0" cellspacing="0" style="width:100%;" id="programUploadTable">
								<colgroup>
									<col style="width:150px;" />
									<col style="" />
								</colgroup>
								<tr>
									<td style="padding:10px;padding-bottom:5px;padding-top:5px;">
										[LANG_VALUE:prg_019]
									</td>
									<td id="fileNodes" style="padding:10px;padding-bottom:5px;padding-top:5px;">
										<input type="file" name="[FORM_PROGRAM_UPLOAD]_1" id="[FORM_PROGRAM_UPLOAD]_1" value="" style="font-size:9pt;font-family:Arial, Helvetica, sans serif;width:300px;border:1px solid rgb(150,150,150);background-color:rgb(255,255,255);" />
									</td>
								</tr>
								<tr>
									<td colspan="2" style="text-align:right;background-color:rgb(230,230,230);">
										<input type="button" value="[LANG_VALUE:adm_022]" onclick="document.MainForm.submit();" />
									</td>
								</tr>
							</table>
						</form>
					</fieldset>

					<fieldset class="tlw">
						<legend class="tlw h2">[LANG_VALUE:prg_020]</legend>
						<form method="POST" action="[FORM_LINK]" name="LocalForm" enctype="multipart/form-data">
							<fieldset style="display:none;">
								<input type="hidden" name="lan" value="[FORM_LANGUAGE]" />
								<input type="hidden" name="i" value="[FORM_PROGRAM]" />
								<input type="hidden" name="sec" value="[FORM_SECTION]" />
								<input type="hidden" name="adm" value="[FORM_ADMINACTION]" />
								<input type="hidden" name="m" value="[FORM_MENU]" />
								<input type="hidden" name="textTitle" value="[FORM_LOCAL_UPLOAD]" />
							</fieldset>
							<table cellpadding="0" cellspacing="0" style="width:100%;" id="localUploadTable">
								<colgroup>
									<col style="width:150px;" />
									<col style="" />
								</colgroup>
								<tr>
									<td style="padding:10px;padding-bottom:5px;padding-top:5px;">
										[LANG_VALUE:prg_021]
									</td>
									<td style="padding:10px;padding-bottom:5px;padding-top:5px;">
										<select name="[FORM_LOCAL_UPLOAD]" value="http://" style="font-size:9pt;font-family:Arial, Helvetica, sans serif;width:300px;border:1px solid rgb(150,150,150);background-color:rgb(255,255,255);">
											[LOCAL_FILE_SELECTION]<option value="[LOCAL_FILE_NAME]">[LOCAL_FILE_NAME]</option>[/LOCAL_FILE_SELECTION]
										</select>
									</td>
								</tr>
								<tr>
									<td colspan="2" style="text-align:right;background-color:rgb(230,230,230);">
										<input type="button" value="[LANG_VALUE:adm_022]" onclick="document.LocalForm.submit();" />
									</td>
								</tr>
							</table>
						</form>
					</fieldset>

					<fieldset class="tlw">
						<legend class="tlw h2">[LANG_VALUE:prg_022]</legend>
						<form method="POST" action="[FORM_LINK]" name="RemoteForm" enctype="multipart/form-data">
							<fieldset style="display:none;">
								<input type="hidden" name="lan" value="[FORM_LANGUAGE]" />
								<input type="hidden" name="i" value="[FORM_PROGRAM]" />
								<input type="hidden" name="sec" value="[FORM_SECTION]" />
								<input type="hidden" name="adm" value="[FORM_ADMINACTION]" />
								<input type="hidden" name="m" value="[FORM_MENU]" />
								<input type="hidden" name="textTitle" value="[FORM_REMOTE_UPLOAD]" />
							</fieldset>
							<table cellpadding="0" cellspacing="0" style="width:100%;" id="remoteUploadTable">
								<colgroup>
									<col style="width:150px;" />
									<col style="" />
								</colgroup>
								<tr>
									<td style="padding:10px;padding-bottom:5px;padding-top:5px;">
										[LANG_VALUE:prg_023]
									</td>
									<td style="padding:10px;padding-bottom:5px;padding-top:5px;">
										<input type="text" name="[FORM_REMOTE_UPLOAD]" value="http://" style="font-size:9pt;font-family:Arial, Helvetica, sans serif;width:300px;border:1px solid rgb(150,150,150);background-color:rgb(255,255,255);" />
									</td>
								</tr>
								<tr>
									<td colspan="2" style="text-align:right;background-color:rgb(230,230,230);">
										<input type="button" value="[LANG_VALUE:adm_022]" onclick="document.RemoteForm.submit();" />
									</td>
								</tr>
							</table>
						</form>
					</fieldset>
					[/UPLOAD_FORM]

[/XLAYOUT]
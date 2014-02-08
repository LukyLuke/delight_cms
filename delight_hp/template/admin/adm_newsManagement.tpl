[STYLE_INCLUDE]css_boxes.css[/STYLE_INCLUDE]

[LAYOUT]
					[BASE]
						<script type="text/javascript">
						/*function changeSection(sel) {
						alert("IN");
						lnk = '[BASE_LINK]adm=' + (1400 + sel) + '&sec=[SECTION_ID]';
						window.location.href = lnk.replace('amp;','');
						}*/

						var csid = [SECTION_ID];
						function loadContent(lnk) {
							csid = lnk;
							dWebRequest.Init();
							dWebRequest.sendContentRequest('adm_newsList',lnk,'','news',"replaceContent('[CONT]');");
							var obj = document.getElementById('adm_newsList');
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
							loading.innerHTML = '<img src="[MAIN_DIR]/admin/ajax_loading.gif" style="width:220px;height:19px;" alt="loading" />';
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
							if (document.getElementById('adm_newsList')) {
								var obj = document.getElementById('adm_newsList');
								if (document.getElementById('loading_div')) {
									document.getElementById('loading_div').parentNode.removeChild(document.getElementById('loading_div'));
								}
								obj.innerHTML = obj.innerHTML + cont;
							}
						}
						function createNews() {
							var nlnk = '[CREATE_NEWS_LINK]';
							var par = {  };
							var tpl = { file : nlnk + '0&sec=' + csid, width : '500px', height : '450px' };
							delightEditor.execCommand('admpopup', false, {template : tpl, param : par});
						}
						function changeNews(nid) {
							var nlnk = '[CHANGE_NEWS_LINK]';
							var par = {  };
							var tpl = { file : nlnk + nid + '&sec=' + csid, width : '500px', height : '450px' };
							delightEditor.execCommand('admpopup', false, {template : tpl, param : par});
						}
						function showNews(nid) {
							var nlnk = '[SHOW_NEWS_LINK]';
							var par = {  };
							var tpl = { file : nlnk + nid };
							delightEditor.execCommand('admpopup', false, {template : tpl, param : par});
						}
						function deleteNews(nid) {
							var nlnk = '[DELETE_NEWS_LINK]';
							var par = {  };
							var tpl = { file : nlnk + nid, height : '250px' };
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
							setTextareaToDelightEdit('dedt_NewsContent', 'admin');
						}
						window.setTimeout('initEditor()', 500);
						</script>
					
						<div class="admbase">
							<div class="admhead">[LANG_VALUE:new_001]</div>
							<div class="admcont">
								<div class="admdescr">[LANG_VALUE:new_002]</div>
								<table cellpadding="0" cellspacing="0" style="width:100%;margin-right:2px;">
									<tr>
										<td id="adm_sectionList">
											<div class="symbolbar">
												<div class="symbol" onclick="createSection();"><img src="[MAIN_DIR]/images/admin/symbol_create.gif" style="width:16px;height:16px;border-width:0px;" alt="create" align="absmiddle" /></div>
												<div class="symbol" onclick="changeSection();"><img src="[MAIN_DIR]/images/admin/symbol_edit.gif" style="width:16px;height:16px;border-width:0px;" alt="edit" align="absmiddle" /></div>
												<div class="symbol" onclick="deleteSection();"><img src="[MAIN_DIR]/images/admin/symbol_delete.gif" style="width:16px;height:16px;border-width:0px;" alt="delete" align="absmiddle" /></div>
											</div>
											[SECTION_LIST]
										</td>
										<td id="adm_newsList">
											<div class="symbolbar">
												<div class="symbol" onclick="createNews();"><img src="[MAIN_DIR]/images/admin/symbol_create.gif" style="width:16px;height:16px;border-width:0px;" alt="create" align="absmiddle" />&nbsp;[LANG_VALUE:new_006]</div>
											</div>
											[CONTENT]
										</td>
									</tr>
								</table>
								
								[NEWS_LIST]
									<div class="admcontent">
										[SYMBOLBAR]<div class="symbolbar">
											<div class="symbol" onclick="showNews('[CURRENT_NEWS_ID]');"><img src="[MAIN_DIR]/images/admin/symbol_show.gif" style="width:16px;height:16px;border-width:0px;" alt="show" align="absmiddle" />&nbsp;[LANG_VALUE:new_003]</div>
											<div class="symbol" onclick="changeNews('[CURRENT_NEWS_ID]');"><img src="[MAIN_DIR]/images/admin/symbol_edit.gif" style="width:16px;height:16px;border-width:0px;" alt="edit" align="absmiddle" />&nbsp;[LANG_VALUE:new_004]</div>
											<div class="symbol" onclick="deleteNews('[CURRENT_NEWS_ID]');"><img src="[MAIN_DIR]/images/admin/symbol_delete.gif" style="width:16px;height:16px;border-width:0px;" alt="delete" align="absmiddle" />&nbsp;[LANG_VALUE:new_005]</div>
										</div>[/SYMBOLBAR]
										<div style="margin:2px;">
											<p><strong>[NEWS_NUMBER]. [NEWS_TITLE]</strong></p>
											<p>[NEWS_DESCRIPTION:300]</p>
											<hr style="height:1px;border-style:solid;border-top:1px solid rgb(150,150,150);" />
											<p>[NEWS_DATE]</p>
										</div>
									</div>
								[/NEWS_LIST]
								
							</div>
							<div class="admfoot">&nbsp;</div>
						</div>

						<div style="visibility:hidden;height:1px;overflow:hidden;position:absolute;top:50px;">
							<form action="#" method="post" onsubmit="return false;" id="form_dedt_NewsContent">
								<fieldset style="display:none;">
									<input type="hidden" name="title_dedt_NewsContent" id="title_dedt_NewsContent" value="" />
									<input type="hidden" name="layout_dedt_NewsContent" id="layout_dedt_NewsContent" value="" />
									<input type="hidden" name="options_dedt_NewsContent" id="options_dedt_NewsContent" value="" />
									<input type="hidden" name="id_dedt_NewsContent" id="id_dedt_NewsContent" value="" />
									<input type="hidden" name="dedtid_dedt_NewsContent" id="dedtid_dedt_NewsContent" value="0" />
								</fieldset>
								<div id="dedt_NewsContent">&nbsp;</div>
							</form>
						</div>
					[/BASE]

[NEWS_EDITOR]
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
				tinyMCE.settings.delighttitle_title_field = document.getElementById('newsTitle');
				tinyMCE.execCommand('mceAddControl', true, 'newsTextarea');
			} catch (e) {}
		}
		function create() {
			if (document.getElementById('newsTextarea')) {
				var titleVal = '';
				if (document.getElementById('newsTitle')) {
					titleVal = document.getElementById('newsTitle').value;
				}
				dWebRequest.Init();
				dWebRequest.sendContentRequest('[NEWS_ID]',document.getElementById('newsTextarea').value,'[SECTION_ID]','newscreate',"window.frames['"+window.name+"'].frames[0].parseResponse('[CONT]');",'',titleVal);
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
			<input type="hidden" name="newsTitle" id="newsTitle" value="[NEWS_TITLE]" />
			<textarea name="newsTextarea" id="newsTextarea" style="width:100%;height:100%;visibility:hidden;">[NEWS_CONTENT]</textarea>
		</form>
	</body>
</html>
[/NEWS_EDITOR]

[NEWS_DELETE]
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
				dWebRequest.sendContentRequest('[NEWS_ID]','[NEWS_ID]','[SECTION_ID]','newsdel',"window.frames['"+window.name+"'].frames[0].parseResponse('[CONT]');");
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
			<h3>[LANG_VALUE:new_007]</h3>
		</div>
		<div class="admdescr">
			<h2>[NEWS_TITLE]</h2>
			<p>[NEWS_CONTENT]</p>
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
[/NEWS_DELETE]

[NEWS_DETAILS]
<html>
	<head>
		<title>section editor</title>
		<link rel="stylesheet" type="text/css" href="[MAIN_DIR]/admin/css_adminContent.css" />
		<link rel="stylesheet" type="text/css" href="[DATA_DIR]../editor/delight_editor/themes/simple/css/deditor_popup.css" />
		<link rel="stylesheet" type="text/css" href="[DATA_DIR]../editor/delight_editor/themes/simple/css/deditor_ui.css" />
		<script type="text/javascript">
			function closeWin() {
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
	<body class="admpopup">
		<div class="admcontent" style="overflow-y:auto;overflow-x:hidden;">
			<div style="margin:2px;">
				<p><strong style="text-decoration:underline;">[NEWS_TITLE]</strong></p>
				<p>[NEWS_CONTENT]</p>
				<hr style="border-style:solid;height:1px;border-top:1px solid rgb(150,150,150);" />
				<p>[NEWS_DATE]</p>
			</div>
		</div>
		<div id="adm_buttonbar">
			<div style="float:right;">
				<input type="button" value="[LANG_VALUE:sec_011]" onclick="closeWin();" id="insert" />
			</div>
		</div>
	</body>
</html>
[/NEWS_DETAILS]

[/LAYOUT]
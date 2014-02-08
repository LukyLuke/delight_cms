[STYLE_INCLUDE]css_boxes.css[/STYLE_INCLUDE]

[LAYOUT]
[BASE]
						<script type="text/javascript">
						// load content from a section
						var csid = [SECTION_ID];
						function loadContent(lnk) {
							csid = lnk;
							dWebRequest.Init();
							dWebRequest.sendContentRequest('adm_imgList',lnk,'','img',"replaceContent('[CONT]');");
							var obj = document.getElementById('adm_imgList');
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
							loading.innerHTML = '<img src="[MAIN_DIR]/admin/ajax_loading.gif" style="width:220px;height:19px;">';
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
							if (document.getElementById('adm_imgList')) {
								var obj = document.getElementById('adm_imgList');
								if (document.getElementById('loading_div')) {
									document.getElementById('loading_div').parentNode.removeChild(document.getElementById('loading_div'));
								}
								obj.innerHTML = obj.innerHTML + cont;
							}
						}
						function createImage() {
							var nlnk = '[CREATE_IMAGE_LINK]';
							var par = {  };
							var tpl = { file : nlnk + '0&sec=' + csid, width : '500px', height : '150px' };
							delightEditor.execCommand('admpopup', false, {template : tpl, param : par});
						}
						function changeImage(nid) {
							var nlnk = '[CREATE_IMAGE_LINK]';
							var par = {  };
							var tpl = { file : nlnk + nid + '&sec=' + csid, width : '500px', height : '150px' };
							delightEditor.execCommand('admpopup', false, {template : tpl, param : par});
						}
						function changeTexts(nid) {
							var nlnk = '[CHANGE_IMAGE_LINK]';
							var par = {  };
							var tpl = { file : nlnk + nid + '&sec=' + csid, width : '500px', height : '450px' };
							delightEditor.execCommand('admpopup', false, {template : tpl, param : par});
						}
						function showImage(ilnk, rw, rh) {
							openWindow(ilnk,rw,rh);
						}
						function deleteImage(nid) {
							var nlnk = '[DELETE_IMAGE_LINK]';
							var par = {  };
							var tpl = { file : nlnk + nid, height : '350px' };
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
							<div class="admhead">[LANG_VALUE:img_001]</div>
							<div class="admcont">
								<div class="admdescr">[LANG_VALUE:img_002]</div>
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
										<td id="adm_imgList" style="vertical-align:top;">
											<div class="symbolbar">
												<div class="symbol" onclick="createImage();"><img src="[MAIN_DIR]/images/admin/symbol_create.gif" style="width:16px;height:16px;border-width:0px;" align="absmiddle" />&nbsp;[LANG_VALUE:img_006]</div>
											</div>
											[CONTENT]
										</td>
									</tr>
								</table>
								
								[IMAGE_LIST]
									<div class="admcontent">
										[SYMBOLBAR]<div class="symbolbar">
											<div class="symbol" onclick="showImage('[BIG_IMAGE_LINK]',[REAL_WIDTH],[REAL_HEIGHT]);"><img src="[MAIN_DIR]/images/admin/symbol_show.gif" style="width:16px;height:16px;border-width:0px;" align="absmiddle" />&nbsp;[LANG_VALUE:img_003]</div>
											<div class="symbol" onclick="changeImage('[CURRENT_IMAGE_ID]');"><img src="[MAIN_DIR]/images/admin/symbol_image.gif" style="width:16px;height:16px;border-width:0px;" align="absmiddle" />&nbsp;[LANG_VALUE:img_004]</div>
											<div class="symbol" onclick="changeTexts('[CURRENT_IMAGE_ID]');"><img src="[MAIN_DIR]/images/admin/symbol_edit.gif" style="width:16px;height:16px;border-width:0px;" align="absmiddle" />&nbsp;[LANG_VALUE:img_015]</div>
											<div class="symbol" onclick="deleteImage('[CURRENT_IMAGE_ID]');"><img src="[MAIN_DIR]/images/admin/symbol_delete.gif" style="width:16px;height:16px;border-width:0px;" align="absmiddle" />&nbsp;[LANG_VALUE:img_010]</div>
<!--											<div class="symbol" onclick="selectImage('[CURRENT_IMAGE_ID]');"><img src="[MAIN_DIR]/images/admin/symbol_select.gif" style="width:16px;height:16px;border-width:0px;" align="absmiddle" />&nbsp;[LANG_VALUE:img_016]</div>-->
<!--											<div class="symbol" onclick="moveSelected('[CURRENT_IMAGE_ID]');"><img src="[MAIN_DIR]/images/admin/symbol_move2.gif" style="width:16px;height:16px;border-width:0px;" align="absmiddle" />&nbsp;[LANG_VALUE:img_017]</div>-->
										</div>[/SYMBOLBAR]
										
										<div style="margin:2px;">
											<table cellpadding="0" cellspacing="0" style="width:100%;">
												<tr>
													<td style="width:160px;text-align:center;vertical-align:middle;">
														<img src="[IMAGE_SRC]" style="border-width:0px;width:[WIDTH]px;height:[HEIGHT]px;" align="middle" alt="[IMAGE_TITLE]" />
													</td>
													<td>
														<p><strong>[IMAGE_NUMBER]. [IMAGE_TITLE]</strong></p>
														<p>[IMAGE_DESCRIPTION]</p>
														<p><strong>[LANG_VALUE:img_013]</strong> [REAL_WIDTH]x[REAL_HEIGHT]</p>
														<p><strong>[LANG_VALUE:img_012]</strong> [REAL_SIZE]</p>
													</td>
												</tr>
											</table>
										</div>
									</div>
								[/IMAGE_LIST]
								
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

[IMAGE_TEXT_EDITOR]
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
				tinyMCE.settings.delighttitle_title_field = document.getElementById('imageTitle');
				tinyMCE.execCommand('mceAddControl', true, 'imageTextarea');
			} catch (e) {}
		}
		function create() {
			if (document.getElementById('imageTextarea')) {
				var titleVal = '';
				if (document.getElementById('imageTitle')) {
					titleVal = document.getElementById('imageTitle').value;
				}
				dWebRequest.Init();
				dWebRequest.sendContentRequest('[IMAGE_ID]',document.getElementById('imageTextarea').value,'[SECTION_ID]','imgtext',"window.frames['"+window.name+"'].frames[0].parseResponse('[CONT]');",'',titleVal);
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
			<input type="hidden" name="imageTitle" id="imageTitle" value="[IMAGE_TITLE]" />
			<textarea name="imageTextarea" id="imageTextarea" style="width:100%;height:100%;visibility:hidden;">[IMAGE_CONTENT]</textarea>
		</form>
	</body>
</html>
[/IMAGE_TEXT_EDITOR]

[UPLOAD_FORM]
<html>
	<head>
		<title>frame editor</title>
		<link rel="stylesheet" type="text/css" href="[MAIN_DIR]admin/css_adminContent.css" />
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
		</script>
	</head>
	<body class="admpopup" onload="window.setTimeout('Init()',1000);">
		<div id="loadingBar" style="width:100%;text-align:center;padding-top:130px;display:none;">
			Loading...<br />
			<img src="[MAIN_DIR]/admin/ajax_loading.gif" style="width:220px;height:19px;" />
		</div>
		<div id="formTag" style="padding-left:5px;padding-right:5px;">
			<form method="post" action="[UPLOAD_ACTION]" onsubmit="return loading();" enctype="multipart/form-data">
				<h3>[LANG_VALUE:img_014]</h3>
				<p>
					[UPLOAD_HIDDEN_FIELDS]
					<input type="file" name="file_field" id="file_field" value="" class="file_upload" />
				</p>
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


[IMAGE_DELETE]
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
				dWebRequest.sendContentRequest('[IMAGE_ID]','[IMAGE_ID]','[SECTION_ID]','imgdel',"window.frames['"+window.name+"'].frames[0].parseResponse('[CONT]');");
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
			<h3>[LANG_VALUE:img_007]</h3>
		</div>
		<div class="admdescr">
			<table cellpadding="0" cellspacing="0" style="width:100%;">
				<tr>
					<td style="width:160px;text-align:center;vertical-align:middle;">
						<img src="[IMAGE_SRC]" style="border-width:0px;width:[WIDTH]px;height:[HEIGHT]px;" align="middle" alt="[IMAGE_TITLE]" onclick="openWindow('[BIG_IMAGE_LINK]', [REAL_WIDTH], [REAL_HEIGHT]);" />
					</td>
					<td>
						<p><strong>[IMAGE_NUMBER]. [IMAGE_TITLE]</strong></p>
						<p>[IMAGE_DESCRIPTION]</p>
						<p><strong>[LANG_VALUE:img_013]</strong> [REAL_WIDTH]x[REAL_HEIGHT]</p>
						<p><strong>[LANG_VALUE:img_012]</strong> [REAL_SIZE]</p>
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
[/IMAGE_DELETE]

[SUCCESS_INSERT]
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="[DATA_DIR]../editor/delight_editor/themes/simple/css/deditor_popup.css" />
		<link rel="stylesheet" type="text/css" href="[DATA_DIR]../editor/delight_editor/themes/simple/css/deditor_ui.css" />
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
		<link rel="stylesheet" type="text/css" href="[DATA_DIR]../editor/delight_editor/themes/simple/css/deditor_popup.css" />
		<link rel="stylesheet" type="text/css" href="[DATA_DIR]../editor/delight_editor/themes/simple/css/deditor_ui.css" />
		<script type="text/javascript">
		function closeWin() {
			showError("[LANG_VALUE:sec_007]");
		}
		</script>
	</head>
	<body onload='closeWin();'></body>
</html>
[/FAILED_INSERT]


[LAYOUT]
				[MAIN]<form name="MainForm" action="[FORM_ACTION]" method="post">
					<fieldset style="display:none;">
						<input type="hidden" name="i" value="[FORM_CHANGE_ID]" />
						<input type="hidden" name="adm" value="[FORM_ADMIN_SECTION]" />
						<input type="hidden" name="lang" value="[FORM_LANGUAGE]" />
						<input type="hidden" name="m" value="[FORM_PARENT_ID]" />
					</fieldset>
					[SECTION_ENTRY]
					</form>
				[/MAIN]

					[LINK]
					<fieldset class="plain">
						<legend class="plain h1">[LANG_VALUE:adm_016]</legend>

								<span style="padding:3px;" id="hlp1" onmouseover="showmenu(this);" onmouseout="hidemenu(this);">
									<div class="help" id="m_hlp1">
										<div class="helpcont">
											<div class="helptitle">[LANG_VALUE:adm_013]</div>
											<div>[LANG_VALUE:adm_020]</div>
										</div>
									</div>
									[LANG_VALUE:adm_016]
								</span>
								<div style="width:300px;margin-left:150px;">[TEXT]</div>
					</fieldset>
					[/LINK]

[/LAYOUT]

[EDIT]
<html>
	<head>
		<title>[LANG_VALUE:adm_010]</title>
		<link rel="stylesheet" type="text/css" href="[MAIN_DIR]/admin/css_adminContent.css" />
		<link rel="stylesheet" type="text/css" href="[DATA_DIR]../editor/delight_editor/themes/simple/css/deditor_popup.css" />
		<link rel="stylesheet" type="text/css" href="[DATA_DIR]../editor/delight_editor/themes/simple/css/deditor_ui.css" />
		<script type="text/javascript">
		var win = window.opener ? window.opener : window.dialogArguments;
		if (!win) {
			// Try parent
			win = parent.parent;
			// Try top
			if (typeof(win.delightEditor) == "undefined")
			win = top;
		}
		var winId = parseInt(window.name.replace(/dedtWindow_/, ''));
		window.opener = win;
		var dedt = win.delightEditor;
		win = win.dedtWindows.windows['' + winId];
		
		function Init() {
			dedt.setWindowTitle(window);
		}
		function loading() {
			var confirmed = true;
			var nsl,sl = document.getElementsByName('textTitle')[0].value;
			if (sl.indexOf('://') <= 0) {
				nsl = sl.replace(/[^a-z0-9/_\-]/gi, '_');
			} else {
				nsl = sl;
			}
			if (nsl != sl) {
				confirmed = confirm("[LANG_VALUE:adm_101]: '" + nsl + "'");
			}
			if (confirmed) {
				document.getElementsByName('textTitle')[0].value = nsl;
				if (document.getElementById('formTag')) {
					document.getElementById('formTag').style.display = 'none';
				}
				if (document.getElementById('loadingBar')) {
					document.getElementById('loadingBar').style.display = 'visible';
				}
				document.forms[0].submit();
			}
		}
		function closeWin() {
			win.close();
		}
		function expandDialog() {
			var labels = ['[LANG_VALUE:adm_103]', '[LANG_VALUE:adm_104]', '[LANG_VALUE:adm_105]']
			var names  = ['textOptions[]', 'textOptions[]', 'textOptions[]'];
			var values = ['[FORM_MENU_TITLE]', '[FORM_MENU_DESC]', '[FORM_MENU_KEYWORDS]'];
			var exp = document.getElementById('expand');
			for (var i = 0; i < labels.length; i++) {
				exp.parentNode.insertBefore(getMenuTableRow(labels[i], names[i], values[i]), exp);
			}
			exp.parentNode.removeChild(document.getElementById('descr'));
			exp.parentNode.removeChild(exp);
		}
		function getMenuTableRow(label, name, value) {
			var tr = document.createElement('tr');
			var td1 = document.createElement('td');
			var td2 = document.createElement('td');
			var inp = document.createElement('input');
			td1 = tr.appendChild(td1);
			td2 = tr.appendChild(td2);
			inp = td2.appendChild(inp);
			inp.setAttribute('name', name);
			inp.setAttribute('value', value);
			inp.setAttribute('size', '50');
			inp.setAttribute('type', 'text');
			td1.innerHTML = label;
			return tr;
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
	<body class="admpopup" onload="window.setTimeout('Init()',1000);" marginheight="0" marginwidth="0" leftmargin="0" topmargin="0">
		<div id="loadingBar" style="width:100%;text-align:center;padding-top:130px;display:none;">
			Loading...<br />
			<img src="[MAIN_DIR]/admin/ajax_loading.gif" style="width:220px;height:19px;" />
		</div>
		<div id="formTag" style="padding-left:5px;padding-right:5px;">
			<form method="post" action="[UPLOAD_ACTION]" enctype="multipart/form-data">
				[UPLOAD_HIDDEN_FIELDS]
				<h3 style="padding:2px 0px 5px 0px;margin:0;">[LANG_VALUE:adm_010]</h3>
				<table cellpadding="2" cellspacing="0" style="width:100%;">
					<tr>
						<td>[LANG_VALUE:adm_011]</td>
						<td><input type="text" name="textContent" value="[FORM_MENU_TEXT]" size="50" /></td>
					</tr>
					<tr>
						<td>[LANG_VALUE:adm_012]</td>
						<td><input type="text" name="textTitle" value="[FORM_MENU_SHORT]" size="50" /></td>
					</tr>
					<tr id="expand">
						<td colspan="2"><a href="javascript:expandDialog();" class="anchor">[LANG_VALUE:adm_102]</a></td>
					</tr>
					<tr id="descr">
						<td colspan="2"><hr style="border-style:solid;height:1px;" />[LANG_VALUE:adm_098]</td>
					</tr>
				</table>
			</form>
		</div>
		<div id="adm_errormessage" style="color:red;font-weight:bold;text-align:center;">&nbsp;</div>
		<div id="adm_buttonbar">
			<div style="float:left;">
				<input type="button" value="[LANG_VALUE:sec_006]" onclick="closeWin();" id="cancel" />
			</div>
			<div style="float:right;">
				<input type="button" value="[LANG_VALUE:sec_005]" onclick="loading();" id="insert" />
			</div>
		</div>
	</body>
</html>
[/EDIT]

[DELETE]
<html>
	<head>
		<title>[LANG_VALUE:adm_010]</title>
		<link rel="stylesheet" type="text/css" href="[MAIN_DIR]/admin/css_adminContent.css" />
		<link rel="stylesheet" type="text/css" href="[DATA_DIR]../editor/delight_editor/themes/simple/css/deditor_popup.css" />
		<link rel="stylesheet" type="text/css" href="[DATA_DIR]../editor/delight_editor/themes/simple/css/deditor_ui.css" />
		<script type="text/javascript">
		if (top && top.delightEditor) {
			var dedt = top.delightEditor;
		} else if (opener && opener.delightEditor) {
			var dedt = opener.delightEditor;
		}
		function Init() {
			dedt.setWindowTitle(window);
		}
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
	<body class="admpopup" onload="window.setTimeout('Init()',1000);" marginheight="0" marginwidth="0" leftmargin="0" topmargin="0">
		<div id="loadingBar" style="width:100%;text-align:center;padding-top:130px;display:none;">
			Loading...<br />
			<img src="[MAIN_DIR]/admin/ajax_loading.gif" style="width:220px;height:19px;" />
		</div>
		<div id="formTag" style="padding-left:5px;padding-right:5px;">
			<form method="post" action="[UPLOAD_ACTION]" onsubmit="return loading();" enctype="multipart/form-data">
				[UPLOAD_HIDDEN_FIELDS]
				<h3>[LANG_VALUE:adm_017]</h3>
				<p style="font-weight:bold;">[LANG_VALUE:adm_067]</p>
				<br />
				<table cellpadding="2" cellspacing="0" style="width:100%;border:1px dotted rgb(200,200,200);background-color:rgb(240,240,240);">
					<tr>
						<td style="width:150px;">[LANG_VALUE:adm_011]</td>
						<td><strong>[FORM_MENU_TEXT]</strong></td>
					</tr>
					<tr>
						<td>[LANG_VALUE:adm_012]</td>
						<td><strong>[FORM_MENU_SHORT]</strong></td>
					</tr>
				</table>
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
[/DELETE]

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
			op.location.href='[BASE_LINK]';
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
		<script type="text/javascript">
		function closeWin() {
			showError("[LANG_VALUE:sec_007]");
		}
		</script>
	</head>
	<body onload='closeWin();'></body>
</html>
[/FAILED_INSERT]

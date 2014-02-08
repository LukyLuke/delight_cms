

[DELETE]
<html>
	<head>
		<title>[LANG_VALUE:adm_005]</title>
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
				<h3>[LANG_VALUE:adm_019]</h3>
				<p style="font-weight:bold;">[LANG_VALUE:adm_023]</p>
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

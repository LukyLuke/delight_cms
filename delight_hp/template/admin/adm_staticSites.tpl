
[LAYOUT]
				[SITE_LIST]
						<script type="text/javascript">
						function createLanguage(lnk) {
							var par = {  };
							var tpl = { file : '[CREATE_STATIC_SITES_LINK]' + lnk, height : '280px' };
							delightEditor.execCommand('admpopup', false, {template : tpl, param : par});
						}
						function showConvertState(lnk) {
							var par = {  };
							var tpl = { file : '[SHOW_STATIC_SITES_LINK]' + lnk, height : '280px' };
							delightEditor.execCommand('admpopup', false, {template : tpl, param : par});
						}
						function initEditor() {
							setTextareaToDelightEdit('dedt_StaticSiteContent', 'admin');
						}
						window.setTimeout('initEditor()', 500);
						</script>
						
						<div class="admbase">
							<div class="admhead">[LANG_VALUE:sta_001]</div>
							<div class="admcont">
								<div class="admdescr">[LANG_VALUE:sta_002]</div>
								<br />
								[LANGUAGE_LIST]
								<div class="admlist">
									<table cellpadding="0" cellspacing="0" style="width:100%;">
										<tr>
											<td style="width:20px;"><img src="[MAIN_DIR]/images/language/[LANGUAGE_ICON]" style="width:16px;height:16px;border-width:0px;" /></td>
											<td>[LANGUAGE_NAME] ([LANGUAGE_SHORT])</td>
											<td style="width:100px;">[STATIC_FILE_NUMBER]</td>
											<td style="width:150px;"><a class="anchor" href="#" onclick="createLanguage('[LANGUAGE_NAME]');">[LANG_VALUE:sta_003]</a></td>
											<td style="width:50px;">
												<a class="anchor" href="#" onclick="showConvertState('[LANGUAGE_NAME]');">[LANG_VALUE:sta_018]</a>
											</td>
										</tr>
									</table>
								</div>
								[/LANGUAGE_LIST]
							</div>
							<div class="admfoot">&nbsp;</div>
						</div>

						<div style="visibility:hidden;height:1px;overflow:hidden;position:absolute;top:50px;">
							<form action="#" method="post" onsubmit="return false;" id="form_dedt_StaticSiteContent">
								<fieldset style="display:none;">
									<input type="hidden" name="title_dedt_StaticSiteContent" id="title_dedt_StaticSiteContent" value="" />
									<input type="hidden" name="layout_dedt_StaticSiteContent" id="layout_dedt_StaticSiteContent" value="" />
									<input type="hidden" name="options_dedt_StaticSiteContent" id="options_dedt_StaticSiteContent" value="" />
									<input type="hidden" name="id_dedt_StaticSiteContent" id="id_dedt_StaticSiteContent" value="" />
									<input type="hidden" name="dedtid_dedt_StaticSiteContent" id="dedtid_dedt_StaticSiteContent" value="0" />
								</fieldset>
								<div id="dedt_StaticSiteContent">&nbsp;</div>
							</form>
						</div>
				[/SITE_LIST]
				
				[CREATE_STATE_WINDOW]
				<html>
					<head>
						<title>[LANG_VALUE:sta_018]: [LANGUAGE_SELECTED]</title>
						<link rel="stylesheet" type="text/css" href="[MAIN_DIR]admin/css_adminContent.css" />
						<link rel="stylesheet" type="text/css" href="[DATA_DIR]../editor/delight_editor/themes/simple/css/deditor_popup.css" />
						<link rel="stylesheet" type="text/css" href="[DATA_DIR]../editor/delight_editor/themes/simple/css/deditor_ui.css" />
						<script type="text/javascript">
						var req = false, data = '', time_begin, time_end, time_established;
						if (top && top.delightEditor) {
							var dedt = top.delightEditor;
						} else if (opener && opener.delightEditor) {
							var dedt = opener.delightEditor;
						}

						function Init() {
							dedt.setWindowTitle(window);
							window.setTimeout("loadData()", 500);
						}

						function secondsToHumanReadable(sec) {
							var calc,back;
							var ssec='[LANG_VALUE:sta_013]',smin='[LANG_VALUE:sta_014]',shour='[LANG_VALUE:sta_015]';
							if (sec > 60) {
								calc = Math.round(sec * 100 / 60) / 100;
								if (calc > 60) {
									calc = Math.round(sec * 100 / 60) / 100;
									back = calc + ' ' + shour;
								} else {
									back = calc + ' ' + smin;
								}
							} else {
								back = sec + ' ' + ssec;
							}
							return back;
						}

						function closeWin() {
							var winId = parseInt(window.name.replace(/dedtWindow_/, ''));
							if (window.opener) {
								window.close();
							} else {
								top.dedtWindows.windows['' + winId].close();
							}
						}
						
						function getAjaxTransporter() {
							var http = false;
							try { return new XMLHttpRequest(); } catch (trymicrosoft) {
								try { return new ActiveXObject("Msxml2.XMLHTTP"); } catch (othermicrosoft) {
									try { return new ActiveXObject("Microsoft.XMLHTTP"); } catch (failed) {
										return false;
									}
								}
							}
							return http;
						}

						// give attention on some debian-systems
						function loadData() {
							req = null;
							req = getAjaxTransporter();
							if (req) {
								req.onreadystatechange = parseData;
								req.open('GET', '[STATE_STATIC_FILES_LINK]', true);
								req.send(null);
							}
						}
						
						// give attention on some debian-systems
						function parseData() {
							if (req.readyState == 4) {
								if (req.status == 200) {
									try {
										recv_data = req.responseText;
										var idx = recv_data.indexOf('dedt');
										if (recv_data.substr(idx,4) == 'dedt') {
											eval('data = ' + recv_data.substr(idx+4, recv_data.length - 5 - idx) + ';');
											setContent();
											if ( (typeof(data) == 'object') && (typeof(data.finished) != 'undefined') && !data.finished) {
												setTimeout("loadData();", 500);
											}
										}
									} catch (e) { data = {error : true}; }
								} else {
									data = {error : true};
								}
							}
						}

						// give attention on some debian-systems
						function setContent() {
							var total = 0;
							var created = 0;
							var finished = false;
							var error = 0;
							if (typeof(data) == 'object') {
								if (typeof(data.total) != 'undefined') {
									document.getElementById('static_sites_total').innerHTML = data.total;
									total = data.total;
								}
								if (typeof(data.create) != 'undefined') {
									document.getElementById('static_sites_created').innerHTML = data.create;
									created = data.create;
								}
								if (typeof(data.time) != 'undefined') {
									document.getElementById('static_time_site').innerHTML = secondsToHumanReadable(Math.round(data.time * 1000) / 1000);
								}
								if (typeof(data.established) != 'undefined') {
									document.getElementById('static_time_established').innerHTML = secondsToHumanReadable(Math.round(data.established * 100) / 100);
								}
								if (typeof(data.remain) != 'undefined') {
									document.getElementById('static_remaning').innerHTML = secondsToHumanReadable(Math.round(data.remain * 100) / 100);
								}
								if (typeof(data.error) != 'undefined') {
									document.getElementById('static_sites_error').innerHTML = data.error.length;
									error = data.error.length;
								}
								if (typeof(data.finished) != 'undefined') {
									finished = data.finished;
								}
							}
							var img = document.getElementById('static_progress_image');
							var max = document.getElementById('static_progress').offsetWidth;
							img.style.width = (created * max / total) + 'px';
							if (!finished && (total == 0) && (created == 0)) {
								if (!document.getElementById('preparestate')) {
									var state = document.createElement('div');
									state.id = 'preparestate';
									state.innerHTML = 'preparing, please wait...';
									state.className = 'messagepop';
								}
							} else if (document.getElementById('preparestate')) {
								var state = document.getElementById('preparestate');
								state.parentNode.removeChild(state);
							}
						}
						</script>
					</head>
					<body onload="window.setTimeout('Init();',100);" class="admpopup">
						<div id="loadingBar" style="width:100%;text-align:center;padding-top:130px;display:none;">
							Loading...<br />
							<img src="[MAIN_DIR]/admin/ajax_loading.gif" style="width:220px;height:19px;" />
						</div>
						<div id="formTag" style="padding-left:5px;padding-right:5px;">
							<h3>[LANG_VALUE:sta_005]</h3>
							<div class="admcont" id="static_progress" style="margin:5px;margin-left:15px;margin-right:15px;border:1px inset rgb(150,150,150);">
								<img src="[MAIN_DIR]images/admin/progress_bar.gif" style="height:15px;width:0px;" id="static_progress_image" />
							</div>
							<br />
							
							<h3>[LANG_VALUE:sta_012]:</h3>
							<div class="admdescr margin">
								<table cellpadding="2" cellspacing="0">
									<!--<tr><td class="state_header">[LANG_VALUE:sta_016]:</td><td class="state_cell" id="static_sites_current">&nbsp;</td></tr>-->
									<tr><td class="state_header">[LANG_VALUE:sta_006]:</td><td class="state_cell" id="static_sites_total">0</td></tr>
									<tr><td class="state_header">[LANG_VALUE:sta_017]:</td><td class="state_cell" id="static_sites_error">0</td></tr>
									<tr><td class="state_header">[LANG_VALUE:sta_007]:</td><td class="state_cell" id="static_sites_created">&nbsp;</td></tr>
									<tr><td class="state_header">[LANG_VALUE:sta_008]:</td><td class="state_cell" id="static_time_site">&nbsp;</td></tr>
									<tr><td class="state_header">[LANG_VALUE:sta_009]:</td><td class="state_cell" id="static_time_established">&nbsp;</td></tr>
									<tr><td class="state_header">[LANG_VALUE:sta_010]:</td><td class="state_cell" id="static_remaning">&nbsp;</td></tr>
								</table>
							</div>
						</div>
						<div id="adm_errormessage" style="color:red;font-weight:bold;text-align:center;">&nbsp;</div>
						<div id="adm_buttonbar">
							<div style="float:right;">
								<input type="button" value="[LANG_VALUE:sec_011]" onclick="closeWin();" id="cancel" />
							</div>
						</div>
					</body>
				</html>
				[/CREATE_STATE_WINDOW]

				[SITE_CREATE]
				<html>
					<head>
						<title>[LANG_VALUE:sta_005]: [LANGUAGE_SELECTED]</title>
						<link rel="stylesheet" type="text/css" href="[MAIN_DIR]admin/css_adminContent.css" />
						<link rel="stylesheet" type="text/css" href="[DATA_DIR]../editor/delight_editor/themes/simple/css/deditor_popup.css" />
						<link rel="stylesheet" type="text/css" href="[DATA_DIR]../editor/delight_editor/themes/simple/css/deditor_ui.css" />
						<script type="text/javascript">
						var req = false, call = false;
						var data = '';
						var time_begin, time_end, time_established;
						//var nextFile = 0; // 2006-02-06
						if (top && top.delightEditor) {
							var dedt = top.delightEditor;
						} else if (opener && opener.delightEditor) {
							var dedt = opener.delightEditor;
						}

						function Init() {
							dedt.setWindowTitle(window);

							call = null;
							call = getAjaxTransporter();
							if (call) {
								call.onreadystatechange = function() {}; // give attention on some debian-systems
								//call.onreadystatechange = function() {parseFileList(call);};
								call.open('GET', '[CREATE_STATIC_FILES_LINK]', true);
								call.send(null);
							}
							window.setTimeout("loadData()", 500); // give attention on some debian-systems
						}

						function secondsToHumanReadable(sec) {
							var calc,back;
							var ssec='[LANG_VALUE:sta_013]',smin='[LANG_VALUE:sta_014]',shour='[LANG_VALUE:sta_015]';
							if (sec > 60) {
								calc = Math.round(sec * 100 / 60) / 100;
								if (calc > 60) {
									calc = Math.round(sec * 100 / 60) / 100;
									back = calc + ' ' + shour;
								} else {
									back = calc + ' ' + smin;
								}
							} else {
								back = sec + ' ' + ssec;
							}
							return back;
						}

						function closeWin() {
							var winId = parseInt(window.name.replace(/dedtWindow_/, ''));
							if (window.opener) {
								window.close();
							} else {
								top.dedtWindows.windows['' + winId].close();
							}
						}
						
						function getAjaxTransporter() {
							var http = false;
							try { return new XMLHttpRequest(); } catch (trymicrosoft) {
								try { return new ActiveXObject("Msxml2.XMLHTTP"); } catch (othermicrosoft) {
									try { return new ActiveXObject("Microsoft.XMLHTTP"); } catch (failed) {
										return false;
									}
								}
							}
							return http;
						}
						
						/*
						// 2006-02-06
						function parseFileList(request) {
							data = {error : true};
							if (request.readyState == 4) {
								if (request.status == 200) {
									try {
										recv_data = request.responseText;
										if (recv_data.substr(1,4) == 'dedt') {
											eval('data = ' + recv_data.substr(5, recv_data.length - 5) + ';');
											if ( (typeof(data) == 'object') && (data.error == false)) {
												nextFile = 0;
												time_established = new Date();
												setTimeout("createStaticFile();", 500);
											}
										}
									} catch (e) { data = {error : true}; }
								} else {
									data = {error : true};
								}
							}
						}
						
						// 2006-02-06
						function createStaticFile() {
							if ( (data.error == false) && (data.files.length > 0) && (data.files.length >= nextFile) ) {
								data.sites = data.files.length - 1;
								var file = data.files[nextFile];
								if (file != 'end') {
									updateContent('static_sites_current', file);
									updateContent('static_sites_total', data.sites);
									updateContent('static_sites_created', nextFile + 1);
									if (typeof(time_end) != 'undefined') {
										var time_remain = Math.round( ((data.sites - nextFile - 1) * (time_end.getTime() - time_established.getTime()) ) / (nextFile + 1), 0);
										updateContent('static_time_site', secondsToHumanReadable(((time_end.getTime() - time_begin.getTime()) / 1000)));
										updateContent('static_time_established', secondsToHumanReadable(((time_end.getTime() - time_established.getTime()) / 1000)));
										updateContent('static_remaning', secondsToHumanReadable((time_remain / 1000)));
									}
									if (document.getElementById('static_progress_image')) {
										var img = document.getElementById('static_progress_image');
										var max = document.getElementById('static_progress').offsetWidth;
										img.style.width = ((nextFile + 1) * max / data.sites) + 'px';
									}
									
									time_begin = new Date();
									nextFile++;
									req = null;
									req = getAjaxTransporter();
									if (req) {
										req.onreadystatechange = function() {time_end = new Date(); parseCreateStaticFile()};
										req.open('GET', '[STATE_STATIC_FILES_LINK]', true);
										req.send(null);
									}
								}
							}
						}
						
						// 2006-02-06
						function parseCreateStaticFile() {
							if (req.readyState == 4) {
								if (req.status == 200) {
									try {
										var cdata = null;
										eval('cdata = ' + req.responseText + ';');
										if ( (typeof(cdata) != 'object') || (cdata.error && !cdata.end)) {
											if (document.getElementById('static_sites_error')) {
												var num = document.getElementById('static_sites_error').innerHTML;
												document.getElementById('static_sites_error').innerHTML = parseInt(num) + 1;
											}
										}
										createStaticFile();
									} catch (e) {}
								}
							}
						}
						
						// 2006-02-06
						function updateContent(elem, value) {
							if (document.getElementById(elem)) {
								document.getElementById(elem).innerHTML = value;
							}
						}*/

						// give attention on some debian-systems
						function loadData() {
							req = null;
							req = getAjaxTransporter();
							if (req) {
								req.onreadystatechange = parseData;
								req.open('GET', '[STATE_STATIC_FILES_LINK]', true);
								req.send(null);
							}
						}
						
						// give attention on some debian-systems
						function parseData() {
							if (req.readyState == 4) {
								if (req.status == 200) {
									try {
										recv_data = req.responseText;
										if (recv_data.substr(1,4) == 'dedt') {
											eval('data = ' + recv_data.substr(5, recv_data.length - 5) + ';');
											setContent();
											if ( (typeof(data) == 'object') && (typeof(data.finished) != 'undefined') && !data.finished) {
												setTimeout("loadData();", 500);
											}
										}
									} catch (e) { data = {error : true}; }
								} else {
									data = {error : true};
								}
							}
						}

						// give attention on some debian-systems
						function setContent() {
							var total = 0;
							var created = 0;
							var finished = false;
							var error = 0;
							if (typeof(data) == 'object') {
								if (typeof(data.total) != 'undefined') {
									document.getElementById('static_sites_total').innerHTML = data.total;
									total = data.total;
								}
								if (typeof(data.create) != 'undefined') {
									document.getElementById('static_sites_created').innerHTML = data.create;
									created = data.create;
								}
								if (typeof(data.time) != 'undefined') {
									document.getElementById('static_time_site').innerHTML = secondsToHumanReadable(Math.round(data.time * 1000) / 1000);
								}
								if (typeof(data.established) != 'undefined') {
									document.getElementById('static_time_established').innerHTML = secondsToHumanReadable(Math.round(data.established * 100) / 100);
								}
								if (typeof(data.remain) != 'undefined') {
									document.getElementById('static_remaning').innerHTML = secondsToHumanReadable(Math.round(data.remain * 100) / 100);
								}
								if (typeof(data.error) != 'undefined') {
									document.getElementById('static_sites_error').innerHTML = data.error.length;
									error = data.error.length;
								}
								if (typeof(data.finished) != 'undefined') {
									finished = data.finished;
								}
							}
							var img = document.getElementById('static_progress_image');
							var max = document.getElementById('static_progress').offsetWidth;
							img.style.width = (created * max / total) + 'px';
							if (!finished && (total == 0) && (created == 0)) {
								if (!document.getElementById('preparestate')) {
									var state = document.createElement('div');
									state.id = 'preparestate';
									state.innerHTML = 'preparing, please wait...';
									state.className = 'staticprepare';
								}
							} else if (document.getElementById('preparestate')) {
								var state = document.getElementById('preparestate');
								state.parentNode.removeChild(state);
							}
						}
						</script>
					</head>
					<body onload="window.setTimeout('Init();',100);" class="admpopup">
						<div id="loadingBar" style="width:100%;text-align:center;padding-top:130px;display:none;">
							Loading...<br />
							<img src="[MAIN_DIR]/admin/ajax_loading.gif" style="width:220px;height:19px;" />
						</div>
						<div id="formTag" style="padding-left:5px;padding-right:5px;">
							<h3>[LANG_VALUE:sta_005]</h3>
							<div class="admcont" id="static_progress" style="margin:5px;margin-left:15px;margin-right:15px;border:1px inset rgb(150,150,150);">
								<img src="[MAIN_DIR]images/admin/progress_bar.gif" style="height:15px;width:0px;" id="static_progress_image" />
							</div>
							<br />
							
							<h3>[LANG_VALUE:sta_012]:</h3>
							<div class="admdescr margin">
								<table cellpadding="2" cellspacing="0">
									<!--<tr><td class="state_header">[LANG_VALUE:sta_016]:</td><td class="state_cell" id="static_sites_current">&nbsp;</td></tr>-->
									<tr><td class="state_header">[LANG_VALUE:sta_006]:</td><td class="state_cell" id="static_sites_total">0</td></tr>
									<tr><td class="state_header">[LANG_VALUE:sta_017]:</td><td class="state_cell" id="static_sites_error">0</td></tr>
									<tr><td class="state_header">[LANG_VALUE:sta_007]:</td><td class="state_cell" id="static_sites_created">&nbsp;</td></tr>
									<tr><td class="state_header">[LANG_VALUE:sta_008]:</td><td class="state_cell" id="static_time_site">&nbsp;</td></tr>
									<tr><td class="state_header">[LANG_VALUE:sta_009]:</td><td class="state_cell" id="static_time_established">&nbsp;</td></tr>
									<tr><td class="state_header">[LANG_VALUE:sta_010]:</td><td class="state_cell" id="static_remaning">&nbsp;</td></tr>
								</table>
							</div>
						</div>
						<div id="adm_errormessage" style="color:red;font-weight:bold;text-align:center;">&nbsp;</div>
						<div id="adm_buttonbar">
							<div style="float:right;">
								<input type="button" value="[LANG_VALUE:sec_011]" onclick="closeWin();" id="cancel" />
							</div>
						</div>
					</body>
				</html>
				[/SITE_CREATE]

[/LAYOUT]

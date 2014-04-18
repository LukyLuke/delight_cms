[STYLE_INCLUDE]css_boxes.css[/STYLE_INCLUDE]
[STYLE_CONTENT][/STYLE_CONTENT]

[TITLE_TEXT:before][/TITLE_TEXT]
[TITLE_TEXT:after][/TITLE_TEXT]

[LAYOUT]
[TEXT]
[/LAYOUT]

[LAYOUT:original_section]
				<script type="text/javascript">function dload(s) {if(s.substring(0,4)=='java'){eval(s)}else{window.window.open(s,'','');}};</script>
				[CUT_SECTION]<table cellpadding="0" cellspacing="0" style="width:100%;"><tr><td style="width:150px;vertical-align:top;">
					<div class="sec_cont">[SECTION_LIST]</div>
				</td><td style="vertical-align:top;padding-left:5px;">[/CUT_SECTION]
				<div style="">[TEXT]</div>
				[CUT_SECTION]</td></tr></table>[/CUT_SECTION]
[/LAYOUT]

[LAYOUT:fullscreen]
	<script type="text/javascript">var imageTitle = false;[CAT_TITLE]imageTitle = true;[/CAT_TITLE]</script>
	[CAT_CONTENT]<div class="textentry">[TEXT]</div>[/CAT_CONTENT]
[/LAYOUT]

[LAYOUT:registration]
	<div style="padidng:5px;">[TEXT]</div>
[/LAYOUT]

[PROGRAM_LINE]
[TEXT]
[/PROGRAM_LINE]

[PROGRAM_CLEAN:default]
[/PROGRAM_CLEAN]

[PROGRAM_REGISTER_LINK]javascript:openWindow(\'[DOWNLOAD_LINK]\',915,640);[/PROGRAM_REGISTER_LINK]

[PROGRAM:default:1]
<div class="download-file">
	<div class="download-title">
		<strong>[PROGRAM_TITLE]</strong>
	</div>
	<div class="download-content">
		<div class="file-description" onclick="openWindow('[PROGRAM_DETAIL_LINK]',600,300);" title="[LANG_VALUE:msg_027] [PROGRAM_NAME] [LANG_VALUE:msg_026]">
			<div class="file-icon">
				<a href="[PROGRAM_DETAIL_LINK]" onclick="return false;" title="[LANG_VALUE:msg_027] [PROGRAM_NAME] [LANG_VALUE:msg_026]">
					<img src="[PROGRAM_ICON]" style="width:[PROGRAM_ICON_WIDTH]px;height:[PROGRAM_ICON_HEIGHT]px;" alt="[PROGRAM_FILE]" />
				</a>
			</div>
			<div class="file-text">[PROGRAM_DESCRIPTION]</div>
		</div>
		<div class="file-data">
			<div class="file-text">
				<a style="color:black;font-weight:bold;" href="[DOWNLOAD_LINK]">[PROGRAM_FILE]</a><br />
				<u>[LANG_VALUE:msg_020]:</u> [PROGRAM_TYPE_EXT]<br />
				<u>[LANG_VALUE:msg_019]:</u> [PROGRAM_SIZE]<br />
				<u>[LANG_VALUE:msg_015]:</u> [PROGRAM_DATE_SMALL]<br />
				<u>[LANG_VALUE:msg_025]:</u> <a href="[DOWNLOAD_LINK]" title="[PROGRAM_FILE]">[LANG_VALUE:msg_026]</a>
			</div>
		</div>
		<div style="clear:both;height:1px;">&nbsp;</div>
	</div>
</div>
[/PROGRAM]

[PROGRAM:fullscreen:1]
<script type="text/javascript">//<![CDATA[
	var imageBig = true;
	var win, img;
	function initWindow(e) {
		try {var windowId = window.frameElement.getAttribute('id').replace(/[^\d]+/g, '');} catch(ex) { return; }
		win = window.frameElement.ownerDocument['openWindow'+windowId];
		img = document.getElementById('bigimage');
		win.setMaxSize({width:600,height:img.offsetHeight});
		if ((typeof(imageTitle) != 'undefined') && (imageTitle)) {
			win.setTitle('[PROGRAM_FILE]');
		} else {
			win.setTitle('');
		}
		imageBig = false;
		swapSize();
	}
	function swapSize() {
		win.resizeFrame(img, imageBig, false);
		imageBig = !imageBig;
	}
	function changeLocation(e) {
		e = !e ? window.event : e;
		if (e.stopPropagation) {
			e.stopPropagation();
		} else {
			e.cancelBubble = true;
		}
	}
	function captureKey(e) {
		e = !e ? window.event : e;
		if (e.keyCode == 27) {
			win.close();
		}
	}
	window.onload = initWindow;
	window.onkeydown = captureKey;
//]]></script>
<div class="download-header">
	<span>[TITLE]</span>
</div>
<div class="download-details">
	<img src="[PROGRAM_ICON]" style="width:32px;height:32px;padding:0 20px 20px 0;float:left;" alt="[PROGRAM_FILE]" />
	<div>
		<table cellpadding="0" cellspacing="0">
		<tr><th>[LANG_VALUE:msg_016]:</th><td>
			<a href="[DOWNLOAD_LINK]">[PROGRAM_FILE]</a>
		</td></tr>
		<tr><th>[LANG_VALUE:msg_020]:</th><td>[PROGRAM_TYPE_EXT]</td></tr>
		<tr><th>[LANG_VALUE:msg_019]:</th><td>[PROGRAM_SIZE]</td></tr>
		<tr><th>[LANG_VALUE:msg_015]:</th><td>[PROGRAM_DATE]</td></tr>
		</table>
	</div>
</div>
<div class="download-description">
[PROGRAM_DESCRIPTION]
</div>
<div style="text-align:center;padding:5px;border-top:1px dotted black;">
	<a href="[DOWNLOAD_LINK]" style="font-weight:bold;color:black;" title="[LANG_VALUE:msg_016] [PROGRAM_FILE] [LANG_VALUE:msg_025]">[LANG_VALUE:msg_016] [PROGRAM_FILE] [LANG_VALUE:msg_025]</a>
</div>
[/PROGRAM]

[PROGRAM:registration:1]
	[TEMPLATE_FILE]darkwhite[/TEMPLATE_FILE]
	[TITLE_TEXT][LANG_VALUE:dreg_001][/TITLE_TEXT]
	
	<p style="font-size:9pt;text-align:justify;margin:5px;padding:5px;border:1px dotted rgb(150,150,150);background-color:rgb(240,240,240);">[LANG_VALUE:dreg_002]</p>
	<p style="font-size:9pt;">&nbsp;</p>
	<h2 class="ttitle">[LANG_VALUE:dreg_003]</h2>
	<p style="font-size:9pt;">[LANG_VALUE:dreg_004]<br /><br /><strong>[LANG_VALUE:dreg_005]:</strong> <a href="[DIRECT_DOWNLOAD_LINK]" style="font-weight:bold;">[PROGRAM_FILE]</a></p>
	
	<center>
	<div style="font-size:9pt;margin:5px;padding:5px;border:1px dotted rgb(180,180,180);background-color:rgb(250,250,250);width:600px;">
		<form method="POST" action="[MAIN_DIR]../sendDataOverMail.php">
			<input type="hidden" name="fieldName" value="regFld" />
			<input type="hidden" name="fieldsRequired" value="NAME,MAILG" />
			<input type="hidden" name="sendMailTo" value="MailRegisterInformation" />
			<input type="hidden" name="redirectTo" value="[DIRECT_DOWNLOAD_LINK]" />
			<input type="hidden" name="failureTo" value="[DIRECT_DOWNLOAD_LINK]" />
			<input type="hidden" name="regFldFile" value="[DIRECT_DOWNLOAD_LINK]" />
			
			<table cellpadding="0" cellspacing="2" style="width:100%;">
				<tr>
					<td colspan="2">
						<h3 class="ttitle">[LANG_VALUE:dreg_001]</h3>
						<hr style="border-style:solid;height:1px;" />
					</td>
				</tr>
				<tr>
					<td class="key">[LANG_VALUE:dreg_006]</td>
					<td class="val"><input type="text" name="regFldName" value="" style="width:330px;" /></td>
				</tr><tr>
					<td class="key">[LANG_VALUE:dreg_007]</td>
					<td class="val"><input type="text" name="regFldAddr" value="" style="width:330px;" /></td>
				</tr><tr>
					<td class="key">[LANG_VALUE:dreg_008]</td>
					<td class="val"><input type="text" name="regFldCity" value="" style="width:330px;" /></td>
				</tr><tr>
					<td class="key">[LANG_VALUE:dreg_009]</td>
					<td class="val"><input type="text" name="regFldTelG" value="" style="width:330px;" /></td>
				</tr><tr>
					<td class="key">[LANG_VALUE:dreg_010]</td>
					<td class="val"><input type="text" name="regFldTelP" value="" style="width:330px;" /></td>
				</tr><tr>
					<td class="key">[LANG_VALUE:dreg_011]</td>
					<td class="val"><input type="text" name="regFldMailG" value="" style="width:330px;" /></td>
				</tr><tr>
					<td class="key">[LANG_VALUE:dreg_012]</td>
					<td class="val"><input type="text" name="regFldMailP" value="" style="width:330px;" /></td>
				</tr><tr>
					<td colspan="2">&nbsp;</td>
				</tr><tr>
					<td class="key" style="vertical-align:top;">[LANG_VALUE:dreg_013]</td>
					<td class="val">
						<input type="radio" name="regFldCont" value="yes, contact me" style="vertical-align:bottom;" /> [LANG_VALUE:dreg_014]
						<br />
						<input type="radio" name="regFldCont" value="no, dont' contact me" style="vertical-align:bottom;" /> [LANG_VALUE:dreg_015]
						<br />
						<input type="radio" name="regFldCont" value="i will contact you" style="vertical-align:bottom;" /> [LANG_VALUE:dreg_016]
					</td>
				</tr><tr>
					<td colspan="2">&nbsp;</td>
				</tr><tr>
					<td class="key" style="vertical-align:top;">[LANG_VALUE:dreg_017]</td>
					<td class="val">
						<input type="radio" name="regFldContT" value="EMail" style="vertical-align:bottom;" /> [LANG_VALUE:dreg_018]
						<br /	>
						<input type="radio" name="regFldContT" value="Telephone" style="vertical-align:bottom;" /> [LANG_VALUE:dreg_019]
					</td>
				</tr><tr>
					<td colspan="2" style="text-align:right;">
						<input type="submit" class="button" value="[LANG_VALUE:dreg_020]" />
					</td>
				</tr>
			</table>
			
		</form>
	</div>
	</center>
[/PROGRAM]

[PROGRAM_SECTION]
							<script type="text/javascript">
							<!--
							function showSection(obj) {
								var id = obj.id.replace(/sec/, 'secd');
								var dobj = null;
								var i = 0, img = 0;

								if (obj.childNodes.length > 0) {
									for (i = 0; i < obj.childNodes.length; i++) {
										if (obj.childNodes.item(i).nodeName.toLowerCase() == 'img') {
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
								if (dobj.nodeName.toLowerCase() != 'div') {
									dobj = null;
								}
								
								if ( (typeof(dobj) != 'undefined') && (dobj != null) ) {
									if (dobj.style.display == 'none') {
										// change the Image if there is a plus
										if ( (obj.childNodes.length > 0) && (obj.childNodes.item(img).nodeName == 'IMG')) {
											obj.childNodes.item(img).src = '[MAIN_DIR]images/layout/section_minus.gif';
										}
										dobj.style.display = 'block';
									} else {
										// change the Image if there is a plus
										if ( (obj.childNodes.length > 0) && (obj.childNodes.item(img).nodeName == 'IMG')) {
											obj.childNodes.item(img).src = '[MAIN_DIR]images/layout/section_plus.gif';
										}
										dobj.style.display = 'none';
									}
								}
							}
							//-->
							</script>
							<div id="sectionMain" class="sectionContainer">
								<strong><u>[LANG_VALUE:msg_021]:</u></strong><br />
								[SECTION_CONTENT]
							</div>

								[SECTION]<table cellpadding="0" cellspacing="0"> <tr>
									[SECTION_DELIMITER]
									<td>
										<span id="sec[SECTION_ID]" class="sectionSpan" onmouseover="this.style.cursor='pointer';" onmouseout="this.style.cursor='default';">
											[SECTION_IMAGE]<img alt="+" src="[MAIN_DIR]images/layout/section_folder[IF_SELECTED_ID:'_open':''].gif" align="middle" style="width:16px;height:16px;" />
											&nbsp;<a href="[SECTION_LINK]" class="section" style="[IF_SELECTED_ID:'text-decoration:underline;':'']">[SECTION_NAME] ([NUMBER_OF_PROGRAMS])</a>
										</span>
										[SUBSECTION]
											<div style="[IF_SELECTED:'display:block;':'display:block;']">[SECTION_CONTENT]</div>
										[/SUBSECTION]
									</td>
								</tr></table>[/SECTION]

[/PROGRAM_SECTION]

[SECTION_DELIMITER_IMAGE]
	[EMPTY][/EMPTY]
	[ENTRY]<!--<img src="[MAIN_DIR]images/layout/section_plus.gif" style="width:9px;height:9px;" alt="+" onclick="showSection(this.parentNode);" />&nbsp;-->[/ENTRY]
	[EXPANDED]<!--<img src="[MAIN_DIR]images/layout/section_minus.gif" style="width:9px;height:9px;" alt="-" onclick="showSection(this.parentNode);" />&nbsp;-->[/EXPANDED]
[/SECTION_DELIMITER_IMAGE]

[PROGRAM_SECTION_DELIMITER]
	[CLEAN]<!--<td width="12">&nbsp;</td>-->[/CLEAN]
	[DOWN]<!--<td width="12">&nbsp;</td>-->[/DOWN]
	[ENTRY]<td width="12">&nbsp;</td>[/ENTRY]
	[LAST]<td width="12">&nbsp;</td>[/LAST]
[/PROGRAM_SECTION_DELIMITER]

[OPTIONS]
	[show_section_false]false[/show_section_false]
	[show_section_true]true[/show_section_true]
[/OPTIONS]
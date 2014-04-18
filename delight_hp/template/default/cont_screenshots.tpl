[STYLE_INCLUDE]css_boxes.css[/STYLE_INCLUDE]
[STYLE_CONTENT][/STYLE_CONTENT]

[TITLE_TEXT:before][/TITLE_TEXT]
[TITLE_TEXT:after][/TITLE_TEXT]

[LAYOUT]
[TEXT]
[/LAYOUT]

[LAYOUT:original_section]
[CUT_SECTION]<table cellpadding="0" cellspacing="0" style="width:100%;"><tr><td style="width:150px;vertical-align:top;">
<div class="sec_cont">[SECTION_LIST]</div>
</td><td style="vertical-align:top;padding-left:5px;">[/CUT_SECTION]

<table cellpadding="0" cellspacing="5" style="width:100%;">[TEXT]</table>

[CUT_SECTION]</td></tr></table>[/CUT_SECTION]
[/LAYOUT]

[LAYOUT:fullscreen]
	<!--[_TEMPLATE_FILE]image_plain.html[/_TEMPLATE_FILE]
	[_TEXT]-->
	<script type="text/javascript">var imageTitle = '';[CAT_TITLE]imageTitle = '[TITLE]';[/CAT_TITLE]</script>
	[CAT_CONTENT]<div class="textentry">[TEXT]</div>[/CAT_CONTENT]
[/LAYOUT]

[THUMBNAIL_LINE]
[TEXT]
<div class="image-clear">&nbsp;</div>
[/THUMBNAIL_LINE]

[THUMBNAIL_CLEAN:default]
<div class="gallery-image">&nbsp;</div>
[/THUMBNAIL_CLEAN]

[THUMBNAIL:default:2]
<div class="gallery-image">
	<div class="image-title">
		<strong>[IMAGE_TITLE]</strong>
	</div>
	<div class="image-image" style="background: white url([IMAGE_SRC:230x170:false]) center center no-repeat;">
		<a class="image-show" href="[BIG_IMAGE_LINK]" onclick="openWindow('[BIG_IMAGE_LINK]',[MAX_WIDTH],[MAX_HEIGHT]);return false;">
			<img src="[MAIN_DIR]images/layout_01/screenshot_border.png" style="width:240px;height:180px;" alt="[IMAGE_TITLE]" title="[IMAGE_TITLE]" />
			[CUT_DESCRIPTION]<div class="image-description">
				[IMAGE_DESCRIPTION]
			</div>[/CUT_DESCRIPTION]
		</a>
	</div>
	[CUT_DATA]<div class="image-data">
		[REAL_WIDTH]x[REAL_HEIGHT] / [REAL_SIZE]
	</div>[/CUT_DATA]
</div>
[/THUMBNAIL]

[THUMBNAIL:fullscreen:1]
							<script type="text/javascript">/*<![CDATA[*/
							function moveScaled(e) {
								if (!e) {
									e = window.event;
								}
								if (document.getElementById('scaledMessage')) {
									var s = document.getElementById('scaledMessage');
									var x = (e.clientX + 5), y = (e.clientY + 5);
									var bw = document.getElementById('imgbody').offsetWidth;
									if (document.body.scrollTop) {
										y += document.body.scrollTop;
									} else if (document.documentElement.scrollTop) {
										y += document.documentElement.scrollTop;
									} else {
										y += window.pageYOffset;
									}
									if ((x + 160) > bw) {
										x = (bw - 160)
									}
									s.style.left = x + 'px';
									s.style.top = y + 'px';
								}
							}
							
							var imageBig = true;
							var win, img;
							function initWindow(e) {
								try {var windowId = window.frameElement.getAttribute('id').replace(/[^\d]+/g, '');} catch(ex) { return; }
								win = window.frameElement.ownerDocument['openWindow'+windowId];
								img = document.getElementById('bigimage');
								win.setMaxSize({width:[REAL_WIDTH], height:[REAL_HEIGHT]});
								if (typeof(imageTitle) != 'undefined') {
									win.setTitle(imageTitle);
								}
								imageBig = false;
								swapSize();
							}
							function swapSize() {
								win.resizeFrame(img, imageBig);
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
							document.onmousemove = moveScaled;
							window.onkeydown = captureKey;
							/*]]>*/</script>
							
							<div style="text-align:center;position:relative;" onclick="swapSize();return false;">
								[IF_FLASH]<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="[MAX_WIDTH]" height="[MAX_HEIGHT]" style="width:[MAX_WIDTH]px;height:[MAX_HEIGHT]px;" id="bigimage" align="middle">
									<param name="allowScriptAccess" value="sameDomain" />
									<param name="movie" value="[REAL_IMAGE_SRC]" />
									<param name="menu" value="false" />
									<param name="quality" value="high" />
									<param name="bgcolor" value="#ffffff" />
									<embed src="[REAL_IMAGE_SRC]" menu="false" quality="high" bgcolor="#ffffff" width="[MAX_WIDTH]" height="[MAX_HEIGHT]" style="width:[MAX_WIDTH]px;height:[MAX_HEIGHT]px;" name="[IMAGE_TITLE]" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
								</object>[/IF_FLASH]
								[IF_IMAGE]<img id="bigimage" src="[REAL_IMAGE_SRC]" style="border-width:0px;width:[REAL_WIDTH]px;height:[REAL_HEIGHT]px;" alt="[IMAGE_TITLE]" />
								<div style="text-align:center;font-size:8pt;padding:0px;margin:0px;margin-bottom:5px;"><em>([LANG_VALUE:msg_024])</em></div>[/IF_IMAGE]
								
								<div id="hoverNavigation">
									[NEXT_IMAGE]
									<a id="btnNext" href="[NEXT_IMAGE_LINK]">
										<img src="[MAIN_DIR]images/layout_01/gallery_navigation_next.png" alt="next" />
									</a>
									[/NEXT_IMAGE]
									[PREVIOUS_IMAGE]
									<a id="btnPrev" href="[PREVIOUS_IMAGE_LINK]">
										<img src="[MAIN_DIR]images/layout_01/gallery_navigation_previous.png" alt="previous" />
									</a>
									[/PREVIOUS_IMAGE]
								</div>
							</div>
							<div style="padding-left:10px;font-size:9pt;">[IMAGE_DESCRIPTION]</div>
							<script type="text/javascript">/*<![CDATA[*/
							document.body.style.margin = '0px';
							document.body.style.padding = '0px';
							if (document.location.href.indexOf('/noslide') < 0) {
								if (document.getElementById('btnNext')) {
									document.getElementById('btnNext').onclick = changeLocation;
								}
								if (document.getElementById('btnPrev')) {
									document.getElementById('btnPrev').onclick = changeLocation;
								}
							} else {
								if (document.getElementById('btnNext')) {
									document.getElementById('btnNext').style.display = 'none';
								}
								if (document.getElementById('btnPrev')) {
									document.getElementById('btnPrev').style.display = 'none';
								}
							}
							/*]]>*/</script>
[/THUMBNAIL]

[IMAGE_SECTION]
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
[/IMAGE_SECTION]


[SECTION_DELIMITER_IMAGE]
	[EMPTY][/EMPTY]
	[ENTRY]<!--<img src="[MAIN_DIR]images/layout/section_plus.gif" style="width:9px;height:9px;" alt="+" onclick="showSection(this.parentNode);" />&nbsp;-->[/ENTRY]
	[EXPANDED]<!--<img src="[MAIN_DIR]images/layout/section_minus.gif" style="width:9px;height:9px;" alt="-" onclick="showSection(this.parentNode);" />&nbsp;-->[/EXPANDED]
[/SECTION_DELIMITER_IMAGE]

[IMAGE_SECTION_DELIMITER]
	[CLEAN]<!--<td width="12">&nbsp;</td>-->[/CLEAN]
	[DOWN]<!--<td width="12">&nbsp;</td>-->[/DOWN]
	[ENTRY]<td width="12">&nbsp;</td>[/ENTRY]
	[LAST]<td width="12">&nbsp;</td>[/LAST]
[/IMAGE_SECTION_DELIMITER]

[OPTIONS]
	[show_section_false]false[/show_section_false]
	[show_section_true]true[/show_section_true]
	[show_description_true]true[/show_description_true]
	[show_description_false]false[/show_description_false]
	[show_data_true]true[/show_data_true]
	[show_data_false]false[/show_data_false]
[/OPTIONS]
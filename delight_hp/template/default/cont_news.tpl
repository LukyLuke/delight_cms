[STYLE_INCLUDE][/STYLE_INCLUDE]
[STYLE_CONTENT][/STYLE_CONTENT]

[LAYOUT:default]
[TEXT]
[/LAYOUT]

[LAYOUT:show_complete]
	<script type="text/javascript">var imageTitle = false;[CAT_TITLE]imageTitle = true;[/CAT_TITLE]</script>
	[CAT_CONTENT]<div id="bigimage" class="textentry">[TEXT]</div>[/CAT_CONTENT]
[/LAYOUT]

[LAYOUT:news_plain]
	<div class="news-container">[TEXT]</div>
[/LAYOUT]

[LAYOUT:news_motion]
<div style="display:none;width:100%;position:absolute;top:2px;left:0px;" id="newsContentForTicker">[TEXT]</div>
<script type="text/javascript" src="/delight_hp/data/newsTicker.js">/*<![CDATA[]]>*/</script>
<script type="text/javascript">/*<![CDATA[*/
	var tick = new newsTicker();
	tick.direction = 'right';
	tick.delimiter = '&nbsp;+ + +&nbsp;';
	tick.interval = 10;
	tick.minNewsNum = 5;
	tick.waitForContentInElem('newsContentForTicker', 'tick');
/*]]>*/</script>
[/LAYOUT]

[NEWS_LINE]
[TEXT]
[/NEWS_LINE]

[NEWS_ENTRY:default]
<div class="news-entry">
	<div class="news-header">
		<div class="news-date">[NEWS_DATE_EXTENDED]</div>
		<div style="height:0px;clear:both;">&nbsp;</div>
	</div>
	<div class="news-text">
		<div class="news-title"><strong>[NEWS_TITLE]</strong></div>
		[NEWS_TEXT:250]
	</div>
</div>
[/NEWS_ENTRY]

[NEWS_ENTRY:news_plain]
<div class="news-entry-plain">
	<div class="news-header-plain">
		<div class="news-date-plain">[NEWS_DATE]</div>
		<div class="news-title-plain"><strong>[NEWS_TITLE]</strong></div>
	</div>
	<div class="news-text-plain">
		[NEWS_TEXT:250]
	</div>
</div>
[/NEWS_ENTRY]

[NEWS_ENTRY:news_motion]
	tick.add('[NEWS_TEXT:0]');
[/NEWS_ENTRY]

[NEWS_ENTRY:show_complete]
<script type="text/javascript">//<![CDATA[
	var imageBig = true;
	var win, img;
	function initWindow(e) {
		try {var windowId = window.frameElement.getAttribute('id').replace(/[^\d]+/g, '');} catch(ex) { return; }
		win = window.frameElement.ownerDocument['openWindow'+windowId];
		img = document.getElementById('bigimage');
		win.setMaxSize({width:600,height:450}); //img.offsetHeight + 25
		if ((typeof(imageTitle) != 'undefined') && (imageTitle)) {
			win.setTitle('[NEWS_DATE_EXTENDED]');
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
	<div class="news-header">
		<span>[TITLE]</span>
	</div>
	<div class="news-text">
		[NEWS_TEXT]
		<p style="clear:both;line-height:1px;font-size:1px;">&nbsp;</p>
	</div>
[/NEWS_ENTRY]

[NEWS_READ_MORE]... <br /><a href="[NEWS_COMPLETE_LINK]" class="newsReadmore" onclick="openWindow('[NEWS_COMPLETE_LINK]',420,450);return false;">[LANG_VALUE:msg_013]</a>[/NEWS_READ_MORE]
[NEWS_READ_MORE:news_plain]... <br /><a href="[NEWS_COMPLETE_LINK]" class="newsReadmore" onclick="openWindow('[NEWS_COMPLETE_LINK]',420,450);return false;">[LANG_VALUE:msg_013]</a>[/NEWS_READ_MORE]
[NEWS_READ_MORE:news_motion]<a href="[NEWS_COMPLETE_LINK]" class="newsReadmore" onclick="openWindow(\\'[NEWS_COMPLETE_LINK]\\',420,450);return false;">[NEWS_TITLE]</a>[/NEWS_READ_MORE]

[NEWS_SECTION]
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
[/NEWS_SECTION]


[SECTION_DELIMITER_IMAGE]
	[EMPTY][/EMPTY]
	[ENTRY]<!--<img src="[MAIN_DIR]images/layout/section_plus.gif" style="width:9px;height:9px;" alt="+" onclick="showSection(this.parentNode);" />&nbsp;-->[/ENTRY]
	[EXPANDED]<!--<img src="[MAIN_DIR]images/layout/section_minus.gif" style="width:9px;height:9px;" alt="-" onclick="showSection(this.parentNode);" />&nbsp;-->[/EXPANDED]
[/SECTION_DELIMITER_IMAGE]

[NEWS_SECTION_DELIMITER]
	[CLEAN]<!--<td width="12">&nbsp;</td>-->[/CLEAN]
	[DOWN]<!--<td width="12">&nbsp;</td>-->[/DOWN]
	[ENTRY]<td width="12">&nbsp;</td>[/ENTRY]
	[LAST]<td width="12">&nbsp;</td>[/LAST]
[/NEWS_SECTION_DELIMITER]

[OPTIONS]
	[show_title_true]true[/show_title_true]
	[show_title_false]false[/show_title_false]
	[show_section_false]false[/show_section_false]
	[show_section_true]true[/show_section_true]
	[show_number]edit:integer:0[/show_number]
	[show_recursive]choose:Yes,No:2[/show_recursive]
[/OPTIONS]
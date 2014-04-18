[STYLE_INCLUDE][/STYLE_INCLUDE]
[STYLE_CONTENT][/STYLE_CONTENT]

[LAYOUT:fullscreen]
<script type="text/javascript">//<![CDATA[
	var imageBig = true;
	var win, img;
	function initWindow(e) {
		try {var windowId = window.frameElement.getAttribute('id').replace(/[^\d]+/g, '');} catch(ex) { return; }
		win = window.frameElement.ownerDocument['openWindow'+windowId];
		img = document.getElementById('bigimage');
		win.setMaxSize({width:600,height:img.offsetHeight});
		win.setTitle('[TITLE]');
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
	<div style="position:relative;padding:5px;" id="bigimage">
		[TEXT]
		<p style="clear:both;line-height:1px;font-size:1px;">&nbsp;</p>
	</div>
[/LAYOUT]

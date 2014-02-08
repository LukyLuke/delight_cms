var wincnt = 0;
function openWindow(lnk, w, h) {
	var wid = ++wincnt;
	var temp = navigator.appVersion.split("MSIE");
	var version=parseFloat(temp[1]);
	var footerTop,maxDimension,imgDimension;
	if (document.getElementById('fixIEfoot')) {
		var foot = document.getElementById('fixIEfoot');
		footerTop = foot.offsetTop + foot.offsetHeight - 20;
	} else {
		footerTop = 0;
	}
	imgDimension = [parseInt(w),parseInt(h)];
	imgDimension[0] += 80;
	imgDimension[1] += 200;

	maxDimension = browserViewport();

	if ((footerTop > 0) && (footerTop < maxDimension[1])) {
		maxDimension[1] = footerTop;
	}
	var dim = _calcDimension(imgDimension, maxDimension);
	dim[0] = (dim[0] > 450) ? dim[0] : 450;
	dim[1] = (dim[1] > 300) ? dim[1] : 300;

	var body = document.getElementsByTagName('body')[0];
	var lay = document.createElement('div');
	lay.id = 'openWindow'+wid;
	if (is_IE && (version < 7.0)) {
		lay.style.position = 'absolute';
		lay.style.zIndex = '900';
		lay.style.background = 'url(/delight_hp/template/images/window/trans.png) top left repeat';
		lay.style.width = '100%';
		lay.style.height = '100%';
		if (document.body.scrollTop) {
			lay.style.top = document.body.scrollTop+'px';
		} else if (document.documentElement.scrollTop) {
			lay.style.top = document.documentElement.scrollTop+'px';
		} else {
			lay.style.top = '0px';
		}
		lay.style.left = '0px';
	} else {
		lay.style.position = 'fixed';
		lay.style.zIndex = '900';
		lay.style.background = 'url(/delight_hp/template/images/window/trans.png) top left repeat';
		lay.style.width = '100%';
		lay.style.height = '100%';
		lay.style.top = '0px';
		lay.style.left = '0px';
	}
	body.appendChild(lay);

	if ((typeof(windowTitle) == 'undefined') || (windowTitle.indexOf('[LANG_VALUE:') > 0)) {
		windowTitle = 'New Window';
	}
	if ((typeof(windowClose) == 'undefined') || (windowClose.indexOf('[LANG_VALUE:') > 0)) {
		windowClose = 'Close Window';
	}

	var cont = '' +
	'<center>' +
	'<div id="openWindow'+wid+'Div" style="width:'+(dim[0])+'px;height:'+(dim[1])+'px;">' +
	'<table cellpadding="0" cellspacing="0" style="width:100%;">' +
	'<tr><td style="height:20px;">&nbsp;</td></tr>' +
	'<tr><td class="winhleft">&nbsp;</td>' +
	'<td class="winhcenter">'+windowTitle+'</td>' +
	'<td class="winhcenter" style="text-align:right;"><img src="/delight_hp/template/images/window/close.png" onclick="closeWindow('+wid+');" class="winclose" /></td>' +
	'<td class="winhright">&nbsp;</td></tr>' +
	'<tr><td class="winmleft">&nbsp;</td><td colspan="2" class="winmcenter">' +
	'<iframe id="openWindow'+wid+'Ifr" style="width:'+(dim[0]-20)+'px;height:'+(dim[1]-100)+'px;border-width:0px;" src="'+lnk+'" frameborder="0"></iframe>' +
	'</td><td class="winmright">&nbsp;</td></tr>' +
	'<tr><td class="wincleft">&nbsp;</td><td colspan="2" class="winccenter">' +
	'<input type="button" class="winbutton" onclick="closeWindow('+wid+');" value="'+windowClose+'" />' +
	'</td><td class="wincright">&nbsp;</td></tr>' +
	'<tr><td class="winfleft">&nbsp;</td><td colspan="2" class="winfcenter">&nbsp;</td><td class="winfright">&nbsp;</td></tr>' +
	'<tr><td style="height:20px;">&nbsp;</td></tr>' +
	'</table>' +
	'</div>' +
	'</center>';
	lay.innerHTML = cont;
	//window.setTimeout("resizeWindow('"+wid+"',"+(mWidth-100)+","+((ifh>mHeight)?mHeight-100:ifh-100)+")", 500);
}

var wcnt = 0;
function resizeWindow(wid, mw, mh) {
	var ifr = document.getElementById('openWindow'+wid+'Ifr');
	if (is_IE && (!ifr.window || !ifr.window.resize)) {
		window.setTimeout("resizeWindow('"+wid+"',"+mw+","+mh+")", 500);
	} else if (!ifr.contentWindow || !ifr.contentWindow.resize) {
		window.setTimeout("resizeWindow('"+wid+"',"+mw+","+mh+")", 500);
	} else {
		if (ifr.window) {
			ifr.window.resize(wid, mw, mh);
		} else {
			ifr.contentWindow.resize(wid, mw, mh);
		}
	}
}

function closeWindow(wid) {
	var win = document.getElementById('openWindow' + wid);
	while (win.hasChildNodes()) {
		win.removeChild(win.lastChild);
	}
	win.parentNode.removeChild(win);
}

function _calcDimension(o, m) {
	var dim = [o[0], o[1]];
	if (dim[1] > m[1]) {
		dim[0] = Math.round((m[1] * o[0]) / o[1]);
		dim[1] = m[1];
	}
	if (dim[0] > m[0]) {
		dim[0] = m[0];
		dim[1] = Math.round((m[0] * o[1]) / o[0]);
	}
	return dim;
}
function browserViewport() {
	var view = [0,0];
	if (window.innerWidth) {
		view = [window.innerWidth, window.innerHeight];
	} else if (document.documentElement.clientWidth) {
		view = [document.documentElement.clientWidth, document.documentElement.clientHeight];
	} else if (document.body.clientWidth) {
		view = [document.body.clientWidth, document.body.clientHeight];
	} else {
		view = [800,600];
	}
	return view;
}

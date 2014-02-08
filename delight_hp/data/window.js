
function OpenWindowClass() {
	this.id = -1;
	this.width = 0;
	this.height = 0;
	this.maxSize = {width:0, height:0};
	this.link = '';
	this.isIE = ((navigator.userAgent.toLowerCase().indexOf('msie') != -1) && (navigator.userAgent.toLowerCase().indexOf('opera') == -1) && (navigator.userAgent.toLowerCase().indexOf('khtml') == -1));
	this.IEversion = parseFloat(navigator.appVersion.split("MSIE")[1]);
}
OpenWindowClass.prototype = {
	init : function(link, width, height) {
		this.width = parseInt(width);
		this.height = parseInt(height);
		this.link = link;

		while (true) {
			this.id = parseInt(Math.random() * 10000);
			if (typeof(document['openWindow'+this.id]) == 'undefined') {
				break;
			}
		}
		document['openWindow'+this.id] = this;
		this._createContent();
		return this.id;
	},

	close : function() {
		var win = document.getElementById('blockingLayer' + this.id);
		if (win) {
			while (win.hasChildNodes()) {
				win.removeChild(win.lastChild);
			}
			win.parentNode.removeChild(win);
		}
		document['openWindow'+this.id] = null;
	},

	_createContent : function() {
		var max, dim, lay, attach = true;
		if (!document.getElementById('blockingLayer'+this.id)) {
			lay = document.createElement('div');
			lay.setAttribute('id', 'blockingLayer'+this.id);
		} else {
			lay = document.getElementById('blockingLayer'+this.id);
			attach = false;
		}

		if (this.isIE && (this.IEversion < 7.0)) {
			lay.style.position = 'absolute';
			lay.style.zIndex = '999999';
			lay.style.background = 'url(/delight_hp/images/window_blocking_layer.png) top left repeat';
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
			lay.style.zIndex = '999999';
			lay.style.background = 'url(/delight_hp/images/window_blocking_layer.png) top left repeat';
			lay.style.width = '100%';
			lay.style.height = '100%';
			lay.style.top = '0px';
			lay.style.left = '0px';
		}

		if ((typeof(windowClose) == 'undefined') || (windowClose.indexOf('[LANG_VALUE:') > 0)) {
			windowClose = 'Schliessen';
		}

		max = this.getMaxSize(20, 20);
		dim = this.calcDimension({width:this.width, height:this.height}, max);

		lay.innerHTML = '<div id="windowLayer'+this.id+'" style="background:white;width:'+dim.width+'px;height:'+max.height+'px;padding:0;margin:10px auto;position:relative;display:block;" class="floatWindow">' +
		'<div style="display:block;float:left;padding:5px;height:25px;margin-right:100px;" class="floatWindow-title">' +
		'<span style="font-weight:bold;font-size:11pt;" id="windowTitle'+this.id+'">ImageTitle</span>' +
		'</div>' +
		'<div id="window'+this.id+'_close-button" style="display:block;float:right;cursor:pointer;padding:5px;top:0;right:0;position:absolute;" onclick="closeWindow('+this.id+');" class="floatWindow-close">' +
		'<span style="font-size:8pt;font-weight:normal;">'+windowClose+'</span>' +
		'</div>' +
		'<iframe id="openWindowFrame'+this.id+'" style="width:'+dim.width+'px;height:'+(max.height-30)+'px;border-width:0px;" src="'+this.link+'" frameborder="0" class="floatWindow-container"></iframe>' +
		'</div>';

		if (attach) {
			document.body.appendChild(lay);
		}
		document.getElementById('openWindowFrame'+this.id).focus();
		
		if (this.isIE && this.IEversion < 8) {
			var self = this;
			window.setTimeout(function(e) {
				document.getElementById('window'+self.id+'_close-button').style.right = '1px';
			}, 100);
			window.setTimeout(function(e) {
				document.getElementById('window'+self.id+'_close-button').style.right = '0px';
			}, 300);
		}
	},

	setTitle : function(title) {
		if (typeof(title) == 'undefined') {
			title = '&nbsp;';
		}
		document.getElementById('windowTitle'+this.id).innerHTML = title;
	},

	setSize: function(w, h) {
		this.width = w;
		this.height = h;
	},

	setMaxSize : function(s) {
		this.maxSize = s;
		this.setSize(s.width, s.height);
	},

	changeLocation : function(url) {
		document.getElementById('openWindowFrame'+this.id).src = url;
	},

	resizeFrame : function(img, big, resize) {
		var frame = document.getElementById('openWindowFrame'+this.id);
		var layer = document.getElementById('windowLayer'+this.id);
		var dim, idim, max = this.getMaxSize(60, 60), fs = this.getInnerFrameSize(frame);
		resize = ((typeof(resize) != 'boolean') || (typeof(resize) == 'boolean') && resize);

		if (big) {
			dim = this.calcDimension({width:this.maxSize.width, height:this.maxSize.height}, max);
			if ( (dim.width < this.maxSize.width) && (max.width < this.maxSize.width) ) {
				dim.width = max.width;
			} else if ( (dim.width < this.maxSize.width) && (max.width > this.maxSize.width) ) {
				dim.width = this.maxSize.width;
			}

			if ( (dim.height < this.maxSize.height) && (max.height < this.maxSize.height) ) {
				dim.height = max.height;
			} else if ( (dim.height < this.maxSize.height) && (max.height > this.maxSize.height) ) {
				dim.height = this.maxSize.height;
			}
			idim = {width: dim.width, height:dim.height};

		} else {
			idim = this.calcDimension({width:this.width, height:this.height}, max);
			dim = {width: idim.width, height:idim.height};
			if (fs.height < max.height) {
				dim.height = fs.height;
			}
		}

		if (big && ( ((dim.width+28) < parseInt(layer.style.width)) || ((dim.height+37) < parseInt(layer.style.height)) )) {
			return;
		}
		layer.style.width = (dim.width+28)+'px';
		layer.style.height = (dim.height+37)+'px';

		frame.style.width = (dim.width+28)+'px';
		frame.style.height = (dim.height+7)+'px';

		if (img && resize) {
			if (big) {
				img.style.width = this.maxSize.width+'px';
				img.style.height = this.maxSize.height+'px';
			} else {
				img.style.width = idim.width+'px';
				img.style.height = idim.height+'px';
			}
		}
		
		// After we downscaled, let us take a look to the height again of the inner Frame to resize the frame again maybe
		if (!big) {
			fs = this.getInnerFrameSize(frame);
			while ((fs.height < max.height) && (fs.height > dim.height)) {
				if (fs.height < max.height) {
					dim.height = fs.height;
				}
				layer.style.width = (dim.width+28)+'px';
				layer.style.height = (dim.height+37)+'px';
	
				frame.style.width = (dim.width+28)+'px';
				frame.style.height = (dim.height+7)+'px';
				fs = this.getInnerFrameSize(frame);
			}
		}
	},

	calcDimension : function(orig, max) {
		var lay = document.getElementById('windowLayer'+this.id), s, hd = 0, wd = 0;
		if (lay != null) {
			if (lay.currentStyle) {
				s = lay.currentStyle;
				hd = parseInt(s['borderTopWidth']) + parseInt(s['borderBottomWidth']);
				wd = parseInt(s['borderLeftWidth']) + parseInt(s['borderRightWidth']);
			} else if (window.getComputedStyle) {
				s = document.defaultView.getComputedStyle(lay, null);
				hd = parseInt(s.getPropertyValue('border-top-width')) + parseInt(s.getPropertyValue('border-bottom-width'));
				wd = parseInt(s.getPropertyValue('border-left-width')) + parseInt(s.getPropertyValue('border-right-width'));
			}
		}
		var back = { width:orig.width, height:orig.height };
		if (back.height > max.height) {
			back.width = Math.round(max.height * orig.width / orig.height);
			back.height = max.height;
		}
		if (back.width > max.width) {
			back.width = max.width;
			back.height = Math.round(max.width * orig.height / orig.width);
		}
		back.width = back.width - wd;
		back.height = back.height - hd;
		return back;
	},

	getMaxSize : function(dw, dh) {
		if (window.innerWidth) {
			return {width:window.innerWidth-dw, height:window.innerHeight-dh};

		} else if (document.documentElement.clientWidth) {
			return {width:document.documentElement.clientWidth-dw, height:document.documentElement.clientHeight-dh};

		} else if (document.body.clientWidth) {
			return {width:document.body.clientWidth-dw, height:document.body.clientHeight-dh};

		}
		return {width:800-dw, height:600-dh};
	},

	getInnerFrameSize : function(frame) {
		var body, back, h;
		//if (frame.contentWindow.innerHeight) {
		if (frame.contentDocument) {
			body = frame.contentDocument.getElementsByTagName('body')[0];
			h = body.style.height;
			body.style.height = 'auto';
			back = {
				width: body.clientWidth+25,
				height: body.clientHeight+25
			};
			/*back = {
				width: frame.contentWindow.innerWidth+25,
				height: frame.contentWindow.innerHeight+25
			};*/
			body.style.height = h;
		} else { // IE-7
			body = frame.contentWindow.document.getElementsByTagName('body')[0];
			h = body.style.height;
			body.style.height = 'auto';
			back = {
				width: body.clientWidth+25,
				height: body.clientHeight+25
			};
			body.style.height = h;
		}
		return back;
	}
};

function openWindow(link, w, h) {
	var win = new OpenWindowClass();
	var id = win.init(link, w, h);
}
function closeWindow(id) {
	if (document['openWindow'+id]) {
		document['openWindow'+id].close();
	}
}

/*var wincnt = 0;
function openWindow(lnk, w, h) {
	var wid = ++wincnt;
	var temp = navigator.appVersion.split("MSIE");
	var version = parseFloat(temp[1]);
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
		lay.style.zIndex = '999999';
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
		windowClose = 'Schliessen';
	}

	/*lay.innerHTML = '<div id="openWindow'+wid+'Div" style="width:'+(dim[0])+'px;height:'+(dim[1])+'px;padding:30px 0 0;margin:10px auto;position:relative;display:block;">' +
	'<iframe id="openWindow'+wid+'Ifr" style="width:'+dim[0]+'px;height:'+(dim[1]-50)+'px;border-width:0px;" src="'+lnk+'" frameborder="0"></iframe>' +
	'<div style="position:absolute;top:0;right:0;background:white;cursor:pointer;padding:5px;height:20px;" onclick="closeWindow('+wid+');">' +
	'<span style="font-weight:bold;">'+windowClose+'</span>' +
	'</div>' +
	'</div>';*/
	/*
	lay.innerHTML = '<div id="openWindow'+wid+'Div" style="background:white;width:'+dim[0]+'px;height:'+(dim[1]-20)+'px;padding:0;margin:10px auto;position:relative;display:block;">' +
	'<div style="display:block;float:left;padding:5px;height:25px;">' +
	'<span style="font-weight:bold;font-size:11pt;" id="window'+wid+'Title">ImageTitle</span>' +
	'</div>' +
	'<div style="display:block;float:right;cursor:pointer;padding:5px;" onclick="closeWindow('+wid+');">' +
	'<span style="font-size:8pt;font-weight:normal;">'+windowClose+'</span>' +
	'</div>' +
	'<iframe id="openWindow'+wid+'Ifr" style="width:'+dim[0]+'px;height:'+(dim[1]-50)+'px;border-width:0px;" src="'+lnk+'" frameborder="0"></iframe>' +
	'</div>';
}*/

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

/*function closeWindow(wid) {
	var win = document.getElementById('openWindow' + wid);
	while (win.hasChildNodes()) {
		win.removeChild(win.lastChild);
	}
	win.parentNode.removeChild(win);
}*/

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

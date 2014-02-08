/*
function delightWebRequest() {
	this.requestUrl = 'index.php';
	this.requestUrlBase = '';
	this.requestContent = '';
	this.requestParams = 'nm=true';
	this.requestActions = {
		text : '151', template : '155', textload : '156',

		imgsection : '1010', imgshotlist : '1011',
		newssection : '1410', newsshotlist : '1411',
		filesection : '1111', filelist : '1112',

		newslist : '1412',newssclist : '1459',newsselist : '1455',newssdlist : '1457',
		newscreatelist : '1451', newsdellist : '1453',

		imglist : '1012',imgsclist : '1059',imgselist : '1055',imgsdlist : '1057',
		imgcreatelist : '1051', imgtextlist : '1052', imgdellist : '1053',

		prglist : '1112',prgsclist : '1159',prgselist : '1155',prgsdlist : '1157',
		prgcreatelist : '1151', prgtextlist : '1152', prgdellist : '1153',

		nonsens : ''
	};

	this.Init = function(tiny) {
		if ( (typeof(tiny) == 'undefined') || !tiny ) {
			this.requestUrlBase = delightEditor.baseURL + '/../../';
		} else {
			this.requestUrlBase = tinymce.baseURL + '/../../';
		}
		createRequest();
	};

	this.sendTextChange = function(oEditorId, sSendContent, sSendTitle, sSendLayout, sOptionsValue) {
		var str = this.requestParams + '&adm=' + this.requestActions['text'] + '&i=' + oEditorId;
		
		try {
			str += '&textContent=' + escape(sSendContent.replace(/\+/gi, "&#43;"));
		} catch (e) { if (console) {console.debug(e); console.error(sSendContent);} }
		
		try {
			if (typeof sSendTitle != 'undefined') {
				str += '&textTitle=' + escape(sSendTitle.replace(/\+/gi, "&#43;"));
			}
		} catch (e) { if (console) {console.debug(e); console.error(sSendTitle);} }
		
		try {
			if (typeof sSendLayout != 'undefined') {
				str += '&textLayout=' + escape(sSendLayout.replace(/\+/gi, "&#43;"));
			}
		} catch (e) { if (console) {console.debug(e); console.error(sSendLayout);} }
		
		try {
			if (typeof sOptionsValue != 'undefined') {
				str += '&textOptions=' + escape(sOptionsValue.replace(/\+/gi, "&#43;"));
			}
		} catch (e) { if (console) {console.debug(e); console.error(sOptionsValue);} }
		
		//IE_FIX();
		if (dXmlHttp) {
			dXmlHttp.open('POST', this.requestUrlBase + this.requestUrl, true);
			dXmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=iso-8859-15");
			dXmlHttp.send(str);
		} else {
			alert('cannot save this text: failed to initialize XMLHTTP\ncontact you Sytem-Administrator or CMS-Admnistrator.');
		}
	};

	this.sendTemplateRequest = function(txt) {
		if (typeof(txt) == 'undefined') {
			txt = '';
		}
		var str = this.requestParams + '&adm=' + this.requestActions['template'] + '&textContent=ajaxtemplates&textOptions=' + txt;
		//IE_FIX();
		if (dXmlHttp) {
			dXmlHttp.open('POST', this.requestUrlBase + this.requestUrl, true);
			dXmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=iso-8859-15");
			dXmlHttp.send(str);
		} else {
			alert('Unable to receive the templates: failed to initialize XMLHTTP\ncontact you Sytem-Administrator or CMS-Admnistrator.');
		}
	};

	this.sendSectionRequest = function(cSection, cContent, sSelected, act, func) {
		if (typeof(func) == undefined) {
			func = '';
		}

		var str = this.requestParams + '&adm=' + this.requestActions[act + 'section'] + '&textContent=' + cSection + ';;::;;' + cContent + ';;::;;' + escape(sSelected) + ';;::;;' + func;
		IE_FIX();
		if (dXmlHttp) {
			dXmlHttp.open('POST', this.requestUrlBase + this.requestUrl, true);
			dXmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=iso-8859-15");
			dXmlHttp.send(str);
		} else {
			alert('Unable to receive the templates: failed to initialize XMLHTTP\ncontact you Sytem-Administrator or CMS-Admnistrator.');
		}
	};

	this.sendContentRequest = function(cContent, sSelected, iSection, act, func, opt, title, layout) {
		if (typeof(func) == 'undefined') {
			func = '';
		}
		if (typeof(iSection) == 'undefined') {
			iSection = 0;
		}
		if (typeof(opt) == 'undefined') {
			opt = '';
		}
		if (typeof(title) == 'undefined') {
			title = '';
		}
		if (typeof(layout) == 'undefined') {
			layout = '';
		}
		var str = this.requestParams + '&adm=' + this.requestActions[act + 'list'] + '&textContent=' + cContent + ';;::;;' + escape(sSelected) + ';;::;;' + iSection + ';;::;;' + escape(func) + ';;::;;' + escape(opt) + ';;::;;' + escape(title) + ';;::;;' + escape(layout);
		IE_FIX();
		if (dXmlHttp) {
			dXmlHttp.open('POST', this.requestUrlBase + this.requestUrl, true);
			dXmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=iso-8859-15");
			dXmlHttp.send(str);
		} else {
			alert('Unable to receive the templates: failed to initialize XMLHTTP\ncontact you Sytem-Administrator or CMS-Admnistrator.');
		}
	};

	this.reloadContent = function(txt) {
		var str = this.requestParams + '&adm=' + this.requestActions['textload'] + '&textContent=' + txt;
		IE_FIX();
		if (dXmlHttp) {
			dXmlHttp.open('POST', this.requestUrlBase + this.requestUrl, true);
			dXmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=iso-8859-15");
			dXmlHttp.send(str);
		} else {
			alert('Unable to receive the templates: failed to initialize XMLHTTP\ncontact you Sytem-Administrator or CMS-Admnistrator.');
		}
	};

	this.parseResponse = function(data) {
		var errordata = data;
		data = data.replace(/^\s*|\s*$/g, "");
		if (data.substring(0,7).toLowerCase() != 'success') {
			alert('Failed to save the Textentry. Please try to edit again.\n\nIf the failure occures again, please contact delight software gmbh support\n' + errordata);
		} else {
			data = data.substring(7, data.length);
			if (data.substring(0,4) == 'eval') {
				eval(data.substring(4, data.length).replace(/\\'/g, "\\'"));
			} else if (data.substring(1, 7) == 'reload') {
				var reloadId = data.replace(/[^0-9]+/g, '');
				this.reloadContent(reloadId);
			} else {
				this.requestContent = data;
				alert(errordata);
			}
		}
	};

	var IE_FIX = function() {
		var is_IE = false;
		var uag = navigator.userAgent.toLowerCase();
		if ((uag.indexOf('msie') != -1) && (uag.indexOf('opera') == -1) && (uag.indexOf('khtml') == -1)) {
			is_IE = true;
		}
		if (is_IE) {
			dXmlHttp = false;
			if (typeof(delightEditor) == 'undefined') {
				dWebRequest.Init(true);
			} else {
				dWebRequest.Init();
			}
		}
	};

	var showError = function(err) {
		alert('Error occured while safe the text: ' + err);
	};

	var createRequest = function() {
		if (!dXmlHttp) {
			try {
				dXmlHttp = new XMLHttpRequest();
			} catch (trymicrosoft) {
				try {
					dXmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
				} catch (othermicrosoft) {
					try {
						dXmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
					} catch (failed) {
						dXmlHttp = false;
					}
				}
			}
		}

		// Check for request
		if (dXmlHttp) {
			dXmlHttp.onreadystatechange = function() {
				if (dXmlHttp.readyState == 4) {
					if (dXmlHttp.status == 200) {
						dWebRequest.parseResponse(dXmlHttp.responseText);
					} else {
						dWebRequest.showError(dXmlHttp.status);
					}
				}
			}
		}

	};
};

var dXmlHttp = false;
var dWebRequest = new delightWebRequest();
*/
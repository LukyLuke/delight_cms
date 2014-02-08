
var __adminHide = {}, __adminInterval = setInterval('__checkAdminHide()', 500), __admMPos = {x:0, y:0};
function showAdminMenu(mid,hide) {
	var adm = $(mid);
	__adminHide[mid] = false;
	if (adm && !hide && !adm.visible()) {
		window.setTimeout("__showAdminMenu('"+mid+"',"+hide+")", 1000);
	} else if (adm && hide) {
		__adminHide[mid] = true;
	}
}
function __checkAdminHide() {
	Object.keys(__adminHide).each(function(v) {
		if (__adminHide[v]) {
			Element.hide(v);
		}
	});
}
function __showAdminMenu(mid, hide) {
	var adm = $(mid),e;
	if (adm && !__adminHide[mid]) {
		if (!adm.visible() && !hide) {
			if ($('fixIEhead')) {
				adm.style.left = __admMPos.x + 'px';
				adm.style.top = __admMPos.y + 'px';
				adm.style.position = 'fixed';
			} else if ($('navi')) {
				e = $('navi').style;
				adm.style.left = (__admMPos.ox - parseInt(e.marginLeft)) + 'px';
				adm.style.top = (__admMPos.oy - parseInt(e.marginTop)) + 'px';
				adm.style.zIndex = 999;
			} else {
				//adm.style.left = __admMPos.ox + 'px';
				//adm.style.top = (__admMPos.oy + __admMPos.sy) + 'px';
				adm.style.left = __admMPos.x + 'px';
				adm.style.top = __admMPos.y + 'px';
				adm.style.position = 'fixed';
			}
			Element.show(mid);
		}
	}
}
function __getCumulativeMargin(elem) {
	var m={top:0,left:0,right:0,bottom:0},p={top:0,left:0,right:0,bottom:0}, e = elem.parentNode;
	if (e.nodeName.toLowerCase() != 'body') {
		m = __getCumulativeMargin(e);
	}
	p.top = isNaN(e.style.marginTop) ? m.top : e.style.marginTop + m.top;
	p.right = isNaN(e.style.marginRight) ? m.right : e.style.marginRight + m.right;
	p.bottom = isNaN(e.style.marginBottom) ? m.bottom : e.style.marginBottom + m.bottom;
	p.left = isNaN(e.style.marginLeft) ? m.left : e.style.marginLeft + m.left;
	return p;
}
function __grabMousePos(e) {
	e = !e ? window.event : e;
	__admMPos.x = e.clientX;
	__admMPos.y = e.clientY;
	__admMPos.ox = is_IE ? e.offsetX : e.layerX;
	__admMPos.oy = is_IE ? e.offsetY : e.layerY;
	__admMPos.sy = Element.cumulativeScrollOffset($('body')).top;
}
Event.observe('body', 'mousemove', __grabMousePos);

function showCreateText() {
	var sm = $('adm_newtext_sub');
	if (typeof(sm) != 'undefined') {
		if (sm.style.display == 'block') {
			sm.style.display = 'none';
		} else {
			sm.style.display = 'block';
		}
	}
}

window.openAdminFirstCall = true;
function openAdmin(a, template, entry, cnt) {
	var ed = initAdminEditor();
	if (typeof(cnt) != 'number') {
		cnt = 0;
	}
	if (cnt > 100) {
		alert('Beim öffnen des Editors ist etwas fehlgeschlagen. Es konnte keine Instanz des Editors erstellt werden während den letzten 100 Versuchen.');
		return;
	}
	if (ed == null) {
		window.setTimeout(function() { openAdmin(a, template, entry, cnt++); }, 100);
		return;
	}
	//console.info('Tried to create an Instance '+cnt+' times...');

	// Bug: Wrong Size of the opend Window if we don't wait here some ms
	if (window.openAdminFirstCall) {
		window.setTimeout(function() { openAdmin(a, template, entry, cnt); }, 1000);
		window.openAdminFirstCall = false;
		return;
	}

	var isContentPlugin = (typeof(entry) != 'undefined'), url='/delight_hp/index.php?lang='+dedtEditorLanguage_Short+'&adm='+a+'&action=template';
	url += (typeof(entry) != 'undefined') ? '&entry='+entry : '';
	url += (typeof(template) != 'undefined') ? '&template='+template : '';
	url += isContentPlugin ? '&iscontent=true' : '';
	ed.windowManager.open({
		file : url,
		width : 600,
		height : 400,
		inline : true,
		resizable : true,
		maximizable : !isContentPlugin
	}, {
		plugin_url : tinymce.baseURL+'/../admin_editor/',
		theme_url : tinymce.baseURL+'/themes/advanced/'
	});
}
function createMenu(mid, cnt) {
	var ed = initAdminEditor();
	if (typeof(cnt) != 'number') {
		cnt = 0;
	}
	if (cnt > 100) {
		alert('Beim öffnen des Editors ist etwas fehlgeschlagen. Es konnte keine Instanz des Editors erstellt werden während den letzten 100 Versuchen.');
		return;
	}
	if (ed == null) {
		window.setTimeout(function() { createMenu(mid, cnt++); }, 100);
		return;
	}
	//console.info('Tried to create an Instance '+cnt+' times...');

	// Bug: Wrong Size of the opend Window if we don't wait here some ms
	if (window.openAdminFirstCall) {
		window.setTimeout(function() { createMenu(mid, cnt++); }, 1000);
		window.openAdminFirstCall = false;
		return;
	}

	ed.windowManager.open({
		url : ADMIN_LINK_MENU_CREATE.replace(/&amp;/gi, '&') + mid,
		width : 500,
		height : 240,
		inline : true,
		resizable : true,
		maximizable : true
	}, {
		theme_url : tinymce.baseURL+'/themes/advanced/'
	});
}
function changeMenu(mid, cnt) {
	var ed = initAdminEditor();
	if (typeof(cnt) != 'number') {
		cnt = 0;
	}
	if (cnt > 100) {
		alert('Beim öffnen des Editors ist etwas fehlgeschlagen. Es konnte keine Instanz des Editors erstellt werden während den letzten 100 Versuchen.');
		return;
	}
	if (ed == null) {
		window.setTimeout(function() { changeMenu(mid, cnt++); }, 100);
		return;
	}
	//console.info('Tried to create an Instance '+cnt+' times...');

	// Bug: Wrong Size of the opend Window if we don't wait here some ms
	if (window.openAdminFirstCall) {
		window.setTimeout(function() { changeMenu(mid, cnt++); }, 500);
		window.openAdminFirstCall = false;
		return;
	}

	ed.windowManager.open({
		url : ADMIN_LINK_MENU_EDIT.replace(/&amp;/gi, '&') + mid,
		width : 500,
		height : 240,
		inline : true,
		resizable : true,
		maximizable : true
	}, {
		theme_url : tinymce.baseURL+'/themes/advanced/'
	});
}
function deleteMenu(mid, cnt) {
	var ed = initAdminEditor();
	if (typeof(cnt) != 'number') {
		cnt = 0;
	}
	if (cnt > 100) {
		alert('Beim öffnen des Editors ist etwas fehlgeschlagen. Es konnte keine Instanz des Editors erstellt werden während den letzten 100 Versuchen.');
		return;
	}
	if (ed == null) {
		window.setTimeout(function() { deleteMenu(mid, cnt++); }, 100);
		return;
	}
	//console.info('Tried to create an Instance '+cnt+' times...');

	// Bug: Wrong Size of the opend Window if we don't wait here some ms
	if (window.openAdminFirstCall) {
		window.setTimeout(function() { deleteMenu(mid, cnt++); }, 1000);
		window.openAdminFirstCall = false;
		return;
	}

	ed.windowManager.open({
		url : ADMIN_LINK_MENU_DELETE.replace(/&amp;/gi, '&') + mid,
		width : 500,
		height : 210,
		inline : true,
		resizable : true,
		maximizable : true
	}, {
		theme_url : tinymce.baseURL+'/themes/advanced/'
	});
}

// Editor-Initialitation
(function() {
tinyMCE.init({
	language : dedtEditorLanguage,
	mode : "none",
	theme : "advanced",
	elements : "dynamic_loaded_elements",
	dialog_type : "modal",
	relative_urls : false,
	plugins : "safari,visualchars,inlinepopups,spellchecker,style,layer,table,ajaxsave,advhr,advimage,advlink,insertdatetime,flash,searchreplace,print,contextmenu,paste,directionality,noneditable,delighttitle,delightform",
	theme_advanced_buttons1 : "cancel,save,print,code,|,cut,copy,paste,pastetext,pasteword,|,undo,redo,|,spellchecker,search,replace",
	theme_advanced_buttons1_add : "|,anchor,link,unlink,image,flash,|,visualchars",
	theme_advanced_buttons2 : "fontsizeselect,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull",
	theme_advanced_buttons2_add : "|,bullist,numlist,|,sub,sup,|,indent,outdent,|,cleanup,styleprops",
	theme_advanced_buttons3 : "tablecontrols,|,insertdate,inserttime,advhr,charmap,|,removeformat,forecolor,backcolor",
	theme_advanced_buttons4 : "delighttitle,delighttpl,|,delightform_config,delightform_edit,delightform_select,delightform_radio,delightform_checkbox,delightform_button",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_path_location : "bottom",
	plugin_insertdate_dateFormat : "%Y-%m-%d",
	plugin_insertdate_timeFormat : "%H:%M:%S",
	extended_valid_elements : "form[name|id|method|action],input[name|value|style|class|type|id|title|size|checked|selected],select[name|class|style|id|title|size],option[value|class|style|id|selected],textarea[name|cols|rows|class|style|id|title],iframe[name|id|src|style|frameborder|width|height|scrolling|marginwidth|marginheight],map[name|id|title|class|style],area[name|id|shape|coords|alt|title|href|nohref|onblur|onfocus|onmouseover|onmouseout|tabindex|target|accesskey|style|class]",
	font_size_style_values : "small,big",
	element_format: "xhtml",
	theme_advanced_resize_horizontal : false,
	theme_advanced_resizing_use_cookie : true,
	theme_advanced_resizing : true,
	apply_source_formatting : true,
	theme_advanced_path : false,
	popup_screen_center : false,
	convert_fonts_to_spans : true,
	cleanup : true,
	cleanup_on_startup : true,
	inline_styles : true,
	trim_span_elements : true,
	font_size_style_values : '8pt,9pt,10pt,11pt,12pt,14pt,16pt,18pt',
	width : "550",
	delighttitle_title_field : null,
	delighttitle_options_field : null,
	delighttitle_templateurl : '/delight_hp/index.php',
	delighttitle_templateparams : {adm:155, textContent:'ajaxtemplates'},
	delighttitle_templateparamid : 'textOptions',
	paste_auto_cleanup_on_paste : true,
	spellchecker_languages : "+Deutsch=de,English=en,French=fr",
	content_css : editorContentCSS ? editorContentCSS : '',
	setup : function(ed) {
		ed.onSetProgressState.add(function(ed, b) {
			if (b) {
				if (!$('dedtProgressBar')) {
					var p = document.createElement('div');
					p.setAttribute('id', 'dedtProgressBar');
					p.className = 'clearlooks2_modalBlocker';
					p.style.zIndex = 399999;
					document.body.appendChild(p);
				}
				if (!$('dedtProgressBarImage')) {
					var p = document.createElement('div');
					p.setAttribute('id', 'dedtProgressBarImage');
					p.style.textAlign = 'center';
					p.style.width = '100%';
					p.style.position = 'absolute';
					p.style.zIndex = 400001;
					p.innerHTML = '<div style="border:1px solid #000;background:#FFF;width:226px;margin: 3px auto;"><img src="/delight_hp/data/loading.gif" style="width:220px;height:19px;border:none;" alt="progress" /><br/>working...</div>';
					document.body.appendChild(p);
				}
				window.setTimeout(function() {
					var yt = window.scrollY || document.documentElement.scrollTop || document.body.scrollTop;
					$('dedtProgressBar').style.display = 'block';
					$('dedtProgressBarImage').style.display = 'block';
					$('dedtProgressBarImage').style.top = (yt + 100) + 'px';
				}, 100);
				window.progressOpen = (new Date()).getTime();
			} else {
				var o = window.progressOpen || (new Date()).getTime(), t = (new Date()).getTime() - o;
				window.setTimeout(function() {
					if ($('dedtProgressBar')) {
						$('dedtProgressBar').style.display = 'none';
					}
					if ($('dedtProgressBarImage')) {
						$('dedtProgressBarImage').style.display = 'none';
					}
				}, t < 500 ? 500-t : 1);
			}
		});
	}
});
})();

function initAdminEditor() {
	var ed,d,txt = $('adminTextarea');

	if (!txt) {
		d = document.createElement('div');
		d.style.display = 'block';
		d.style.position = 'absolute';
		d.setAttribute('id', 'adminTextareaDiv');
		d.style.top = '-1000px';
		d.style.left = '-1000px';

		txt = document.createElement('textarea');
		txt.setAttribute('id', 'adminTextarea');
		txt.setAttribute('name', 'adminTextarea');
		txt.style.width = '300px';
		txt.style.height = '300px';
		d.appendChild(txt);
		document.body.appendChild(d);
	}

	try {
		tinyMCE.execCommand('mceAddControl', true, 'adminTextarea');
		ed = tinyMCE.get('adminTextarea');
		if (ed != undefined) {
			ed.focus();
			$('adminTextarea_parent').style.display = 'block';
			$('adminTextarea_parent').style.top = '-500px';
		} else {
			throw "ed is undefined";
		}
	} catch(ex) {
		return null;
	}
	return ed;
}
document.observe('dom:loaded', initAdminEditor);

/**
 * New Text-Administration
 */
var AdminMenuClass = Class.create({
	id : 0,
	doShow : false,
	doHide : true,
	edit : '',
	oldValue :'',
	showTimeout : 500,
	isGrouped : false,
	isMenu : false,
	isGroupContainer : false,
	triggerShow : 'mouseover',
	triggerHide : 'mouseout',
	triggerClick : false,

	initialize : function(id, f) {
		this.id = id;
		this.edit = f;
		this.isMenu = (f == 'menu');
		this.isGroupContainer = (f == 'grouped');
		
		this.triggerClick = (typeof(MENU_RIGHT_CLICK) != 'undefined' && MENU_RIGHT_CLICK);
		if (this.triggerClick) {
			this.triggerShow = 'contextmenu';
			this.triggerHide = 'click';
		}
		this.startObservers();
		this.isGrouped = $('grouped_content_'+this.id) != null;
	},

	getAdminContainer : function() {
		var e;
		if (this.isMenu) {
			e = $('admmenu_'+this.id);
		} else {
			e = $('admcont_'+this.id);
			if (!e) e = $('grouped_main_'+this.id);
		}
		return e;
	},

	startObservers : function() {
		if (this.isGrouped) return;
		var e = this.getAdminContainer(), id = this.isMenu ? 'admin_menu_'+this.id :'admin_text_'+this.id;
		if (e) {
			e.observe(this.triggerShow, this.startShow.bind(this));
			e.observe(this.triggerHide, this.startHide.bind(this));
			if (this.isMenu) {
				$(id).observe(this.triggerShow, this.startShow.bind(this));
				$(id).observe(this.triggerHide, this.startHide.bind(this));
			}

			$(id).childElements().each(function(item) {
				if (item.hasClassName('admin_edit')) {
					if (this.isGroupContainer) {
						item.observe('click', this.editGroupText.bind(this));
					} else {
						item.observe('click', this.editText.bind(this));
					}

				} else if (item.hasClassName('admin_grouped')) {
					item.observe('click', this.editText.bind(this));

				} else if (item.hasClassName('admin_grouprm')) {
					item.observe('click', this.deleteText.bind(this));

				} else if (item.hasClassName('admin_delete')) {
					if (this.isGroupContainer) {
						item.observe('click', this.deleteGroupText.bind(this));
					} else {
						item.observe('click', this.deleteText.bind(this));
					}

				} else if (item.hasClassName('admin_moveup')) {
					item.observe('click', this.moveMenuUp.bind(this));

				} else if (item.hasClassName('admin_movedown')) {
					item.observe('click', this.moveMenuDown.bind(this));

				} else if (item.hasClassName('admin_menuedit')) {
					item.observe('click', this.changeMenu.bind(this));

				} else if (item.hasClassName('admin_menudelete')) {
					item.observe('click', this.deleteMenu.bind(this));

				} else if (item.hasClassName('admin_menucreate')) {
					item.observe('click', this.createMenu.bind(this));

				} else if (item.hasClassName('admin_menuhide')) {
					item.observe('click', this.menuVisibility.bind(this));

				}
			}, this);
		}
	},

	stopObservers : function() {
		if (this.isGrouped) return;
		var e = this.getAdminContainer(), id = this.isMenu ? 'admin_menu_'+this.id :'admin_text_'+this.id;
		if (e) {
			e.stopObserving();
			if (this.isMenu) {
				$(id).stopObserving();
			}
			$(id).childElements().each(function(item) {
				item.stopObserving();
			});
		}
		this.doHide = true;
		this.hide();
	},

	getGroupContainerId : function() {
		var id,c,p = $('grouped_content_'+this.id);
		if (p) {
			return p.parentNode.getAttribute('id').replace(/[^\d+]/g, '');
		}
		return null;
	},

	getVisibleTextId : function() {
		if (!this.isGroupContainer) return null;

		var sel=null, e = $('grouped_contents_'+this.id);

		$A(e.childNodes).each(function(item) {
			if ((item.nodeName == 'DIV') && item.hasClassName('grouped_selected')) {
				sel = item.getAttribute('id').replace(/[^\d]+/g, '');
			}
		});
		return sel;
	},

	editGroupText : function(ev, id) {
		if (!this.isGroupContainer) return;

		if (typeof(id) == 'undefined') {
			id = this.getVisibleTextId();
		}
		if (id != null) {
			document['adminMenu'+id].editText();
		}
	},

	deleteGroupText : function(ev, id) {
		if (!this.isGroupContainer) return;

		if (typeof(id) == 'undefined') {
			id = this.getVisibleTextId();
		}
		if (id != null) {
			document['adminMenu'+id].deleteText();
		}
	},

	editText : function(ev) {
		var textContainer = $('txt_'+this.id), html, area;
		if (!textContainer) return;
		textContainer.setAttribute('id', 'edit_txt_'+this.id);
		this.stopObservers();
		disableSortable();

		switch (this.edit) {
		case 'tinymce':
			if (this.isGrouped) {
				document['adminMenu'+this.getGroupContainerId()].stopObservers();
			}
			textContainer.hide();

			html = textContainer.innerHTML;
			html = html.replace(/<textarea/g, '<_textarea');
			html = html.replace(/<\/textarea/g, '<\/_textarea');

			area = document.createElement('textarea');
			area.setAttribute('id', 'txt_'+this.id);
			area.setAttribute('name', 'txt_'+this.id);
			area.value = html;
			if (textContainer.style.height) {
				area.style.height = textContainer.offsetHeight+'px';
			}
			textContainer.parentNode.insertBefore(area, textContainer);

			tinyMCE.settings.delighttitle_title_field = $('title_txt_'+this.id);
			tinyMCE.settings.delighttitle_layout_field = $('layout_txt_'+this.id);
			tinyMCE.settings.delighttitle_options_field = $('options_txt_'+this.id);
			tinyMCE.settings.delighttitle_selected = this.id;
			tinyMCE.settings.width = '100%';
			tinyMCE.settings.ajaxsave_close = this.closeTinymce.bind(this);
			tinyMCE.settings.ajaxsave_save = this.saveTinymce.bind(this);

			tinyMCE.execCommand('mceAddControl', true, 'txt_'+this.id);
			break;

		case 'gallery':
			openAdmin(1000, 'image_content', this.id);
			break;

		case 'files':
			openAdmin(1100, 'download_content', this.id);
			break;

		case 'news':
			openAdmin(1400, 'news_content', this.id);
			break;

		case 'globaltext':
			openAdmin(1800, 'globaltext_content', this.id);
			break;

		case 'grouped':
			openAdmin(2100, 'group_content', this.id);
			break;

		case 'iframe':
			openAdmin(2000, 'iframe_content', this.id);
			break;
		}
	},

	closeAdminEditor : function() {
		if ($('edit_txt_'+this.id)) {
			$('edit_txt_'+this.id).setAttribute('id', 'txt_'+this.id);
		}
		enableSortable();
		this.startObservers();
	},

	closeTinymce : function(s) {
		var textContainer = $('edit_txt_'+this.id), area = $('txt_'+this.id);
		tinyMCE.execCommand('mceRemoveControl', true, 'txt_'+this.id);
		if (area) {
			area.parentNode.removeChild(area);
		}
		if (textContainer) {
			textContainer.setAttribute('id', 'txt_'+this.id);
			textContainer.show();
		}
		if ((typeof(s) != 'boolean') || s) {
			enableSortable();
			this.startObservers();
			if (this.isGrouped) {
				document['adminMenu'+this.getGroupContainerId()].startObservers();
			}
		}
	},

	saveTinymce : function() {
		var t = this, textContainer = $('edit_txt_'+this.id), ed = tinyMCE.get('txt_'+this.id);
		ed.setProgressState(1);

		new Ajax.Request('/delight_hp/index.php', {
			method : 'post',
			contentType : 'application/x-www-form-urlencoded',
			encoding : 'UTF-8',
			parameters : {
				adm: 100,
				action: 'save',
				text: t.id,
				content: ed.getContent(),
				title: $('title_txt_'+t.id).value,
				layout: $('layout_txt_'+t.id).value,
				options: $('options_txt_'+t.id).value
			},
			onSuccess : function(response) {
				if (!response.responseJSON) response.responseJSON = response.responseText.evalJSON();
				if (response.responseJSON && response.responseJSON.success) {
					t.closeTinymce(false);
					var e = t.getAdminContainer();
					if (e && !t.isGrouped) {
						e.replace(response.responseJSON.content.replace(/\&\#34\;/g, '\''));
						e.style.position = 'relative';
					} else if (t.isGrouped) {
						$('txt_'+t.id).innerHTML = response.responseJSON.content.replace(/\&\#34\;/g, '\'');
					}
					t.startObservers();
					enableSortable();
					if (t.isGrouped) {
						document['adminMenu'+t.getGroupContainerId()].startObservers();
					}
					ed.setProgressState(0);
				}
			},
			onFailure : function(reponse) {
				ed.setProgressState(0);
				ed.EditorManager.alert(response);
			}
		});
	},

	deleteText : function(ev) {
		if (!$('txt_'+this.id)) {
			return;
		}
		var t = this, ed = initAdminEditor();
		if (ed == null) {
			alert('Beim öffnen des Editors ist etwas fehlgeschlagen. Es konnte keine Instanz des Editors erstellt werden.\n\nBitte laden Sie die Seite neu und versuchen Sie es erneut.\nGeben Sie ihrem Hoster Bescheid wenn es erneut nicht klappt.');
			return;
		}

		ed.windowManager.open({
			url : ADMIN_LINK_DELETE.replace(/&amp;/gi, '&') + t.id,
			width : 500,
			height : 150,
			inline : true,
			resizable : true,
			maximizable : true
		}, {
			theme_url : tinymce.baseURL+'/themes/advanced/'
		});
	},

	moveMenuUp : function(ev) {
		if (this.isMenu) {
			window.location.href = ADMIN_LINK_MENU_MOVEUP.replace(/&amp;/gi, '&')+this.id;
		}
	},

	moveMenuDown : function(ev) {
		if (this.isMenu) {
			window.location.href = ADMIN_LINK_MENU_MOVEDOWN.replace(/&amp;/gi, '&')+this.id;
		}
	},
	menuVisibility : function(ev) {
		if (this.isMenu) {
			window.location.href = ADMIN_LINK_MENU_VISIBILITY.replace(/&amp;/gi, '&')+this.id;
		}
	},
	createMenu : function(ev) {
		var t = this, ed = initAdminEditor();
		if (ed == null) {
			alert('Beim öffnen des Editors ist etwas fehlgeschlagen. Es konnte keine Instanz des Editors erstellt werden.\n\nBitte laden Sie die Seite neu und versuchen Sie es erneut.\nGeben Sie ihrem Hoster Bescheid wenn es erneut nicht klappt.');
			return;
		}

		ed.windowManager.open({
			url : ADMIN_LINK_MENU_CREATE.replace(/&amp;/gi, '&') + t.id,
			width : 500,
			height : 240,
			inline : true,
			resizable : true,
			maximizable : true
		}, {
			theme_url : tinymce.baseURL+'/themes/advanced/'
		});
	},
	changeMenu : function(ev) {
		var t = this, ed = initAdminEditor();
		if (ed == null) {
			alert('Beim öffnen des Editors ist etwas fehlgeschlagen. Es konnte keine Instanz des Editors erstellt werden.\n\nBitte laden Sie die Seite neu und versuchen Sie es erneut.\nGeben Sie ihrem Hoster Bescheid wenn es erneut nicht klappt.');
			return;
		}

		ed.windowManager.open({
			url : ADMIN_LINK_MENU_EDIT.replace(/&amp;/gi, '&') + t.id,
			width : 500,
			height : 240,
			inline : true,
			resizable : true,
			maximizable : true
		}, {
			theme_url : tinymce.baseURL+'/themes/advanced/'
		});
	},
	deleteMenu : function(ev) {
		var t = this, ed = initAdminEditor();
		if (ed == null) {
			alert('Beim öffnen des Editors ist etwas fehlgeschlagen. Es konnte keine Instanz des Editors erstellt werden.\n\nBitte laden Sie die Seite neu und versuchen Sie es erneut.\nGeben Sie ihrem Hoster Bescheid wenn es erneut nicht klappt.');
			return;
		}

		ed.windowManager.open({
			url : ADMIN_LINK_MENU_DELETE.replace(/&amp;/gi, '&') + t.id,
			width : 500,
			height : 210,
			inline : true,
			resizable : true,
			maximizable : true
		}, {
			theme_url : tinymce.baseURL+'/themes/advanced/'
		});
	},

	startShow : function(ev) {
		this.doHide = false;
		if (!this.doShow) {
			this.doShow = true;
			window.setTimeout(this.show.bind(this, ev), this.showTimeout);
			if (this.triggerClick) {
				Event.stop(ev);
			}
		}
	},

	startHide : function(ev) {
		if (this.doShow && !this.doHide) {
			this.doHide = true;
			window.setTimeout(this.hide.bind(this), this.hideTimeout);
			if (this.triggerClick) {
				Event.stop(ev);
			}
		}
	},

	show : function(ev) {
		if (this.doHide || !this.doShow) return;

		var id = this.isMenu ? 'admin_menu_'+this.id : 'admin_text_'+this.id;
		if (this.id > 0) {
			if (this.isMenu) {
				var p = Event.pointer(ev);
				if ($(id).parentNode.nodeName.toLowerCase() != 'body') {
					document.body.appendChild($(id));
				}
				$(id).setStyle({
					top: (p.y) +'px',
					left: (p.x+5) +'px'
				});
				/*var p = Event.pointer(ev), o = $(id).getOffsetParent().cumulativeOffset();
				$(id).setStyle({
					top: (p.y-o[1]) +'px',
					left: (p.x-o[0]) +'px'
				});*/
			}
			$(id).show();
		}
	},

	hide : function(ev) {
		var id = this.isMenu ? 'admin_menu_'+this.id : 'admin_text_'+this.id;
		if (this.doHide && (this.id > 0)) {
			this.doShow = false;
			this.doHide = true;
			$(id).hide();
		}
	},

	// UTF-8 Encoding/Decoding
	utf8_encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
        var utftext = "";
        for (var n = 0; n < string.length; n++) {
        	var c = string.charCodeAt(n);
            if (c < 128) {
            	utftext += String.fromCharCode(c);
            } else if((c > 127) && (c < 2048)) {
            	utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            } else {
            	utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }
        }
        return utftext;
    },

	utf8_decode : function (utftext) {
    	var string = "";
    	var i = 0;
    	var c = c1 = c2 = 0;
    	while ( i < utftext.length ) {
    		c = utftext.charCodeAt(i);
    		if (c < 128) {
    			string += String.fromCharCode(c);
    			i++;
    		} else if((c > 191) && (c < 224)) {
    			c2 = utftext.charCodeAt(i+1);
    			string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
    			i += 2;
    		} else {
    			c2 = utftext.charCodeAt(i+1);
    			c3 = utftext.charCodeAt(i+2);
    			string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
    			i += 3;
    		}
    	}
    	return string;
    }
});


/**
 * Sortable ContentContainer (Order TextBlocks by Drag'n'Drop)
 */
function makeSortable(scroll) {
	Sortable.create('sortableContainer', {
		tag: 'div',
		treeTag: 'div',
		dropOnEmpty: true,
		containment: 'sortableContainer',
		constraint: 'vertical',
		onUpdate: function() {
			var order = Sortable.serialize('sortableContainer').replace(/sortableContainer\[\]=/g, '').replace(/&/g, ',');
			new Ajax.Request('/delight_hp/index.php', {
				method : 'post',
				parameters : {lang:'de', adm:157, order: order},
				onSuccess : function(transport) {}
			});
		},
		only: 'sortable'
	});
}
function disableSortable() {
	Sortable.destroy('sortableContainer');
}
function enableSortable() {
	makeSortable();
}
Event.observe(window, 'load', function(event) {
	makeSortable();
});

// Firebug / Console wrapper
if (typeof(console) != 'object') {
	var console = {
		showDebug: false,
		showFunctionsCode : false,
		showFunctions : true,
		method: "log",
		_consoleMethod: function() {
			if (!this.showDebug) return false;

			var bg = 'white', fg = 'black';
			switch (this.method) {
			case 'info':
				bg = 'blue';
				fg = 'white';
				break;
			case 'debug':
				bg = '#9E9E9E';
				break;
			case 'error':
				fg = 'red';
				bg = 'yellow';
				break;
			}

			var result = '';
			$H(arguments).each(function(item) {
				result += '<pre>'+this._walk(item)+'</pre><br/>';
			}, this);

			var div = document.createElement('div');
			div.style.position = 'absolute';
			div.style.zIndex = 9999;
			div.style.overflow = 'auto';
			div.style.top = 0;
			div.style.left = 0;
			div.style.right = 0;
			div.style.bottom = 0;
			div.style.margin = 0;
			div.style.padding = '5px';
			div.style.background = bg;
			div.style.color = fg;
			div.innerHTML = result;
			div.ondblclick = function(e) {
				e = e || event;
				div.parentNode.removeChild(div);
			};
			document.body.appendChild(div);
		},
		_walk : function(arg, indent) {
			var k,ind = '',b = '';
			if (typeof(indent) != 'number') {
				indent = 0;
			}
			for (i = 0; i < indent; i++) {
				ind += "\t";
			}

			if (typeof(arg) == 'object' || typeof(arg) == 'array') {
				for (k in arg) {
					if (typeof(arg[k]) == 'object') {
						b += ind+""+k+": Object";
						//b += this._walk(arg[k], indent+1);
						//b += "\n";

					} else if ((typeof(arg[k]) == 'function') && !this.showFunctionsCode) {
						if (this.showFunctions) {
							b += ind+""+k+": function()\n";
						}

					} else if ((typeof(arg[k]) == 'function') && !this.showFunctions) {
						// Do nothing

					} else {
						b += ind+""+k+": ";
						b += arg[k];
						b += "\n";
					}
				}
			} else {
				b += ind+''+arg;
			}
			return b.replace(/\n+/g, "\n");
		},
		log: function() {
			this.method = "log";
			this._consoleMethod.apply(this, arguments);
		},
		error: function() {
			this.method = "error";
			this._consoleMethod.apply(this, arguments);
		},
		info: function() {
			this.method = "info";
			this._consoleMethod.apply(this, arguments);
		},
		warn: function() {
			this.method = "warn";
			this._consoleMethod.apply(this, arguments);
		},
		clear: function() {
			this.method = "clear";
			this._consoleMethod.apply(this);
		},
		count: function() {
			this.method = "count";
			this._consoleMethod.apply(this, arguments);
		},
		debug: function() {
			this.method = "debug";
			this._consoleMethod.apply(this, arguments);
		},
		trace: function() {
			this.method = "trace";
			this._consoleMethod.apply(this, arguments);
		},
		assert: function() {
			this.method = "assert";
			this._consoleMethod.apply(this, arguments);
		}
	};
}

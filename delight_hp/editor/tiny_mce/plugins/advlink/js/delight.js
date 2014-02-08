/* Additional JS-Code for delight Link Wizard */

var dContainerId = '';
function closeDelightLinkWizardList(id) {
	var e = $(id+'_container');
	if (e) {
		tinymce.DOM.remove(e);
	}
}
function showDelightLinkWizardList(id) {
	var ed = tinyMCEPopup.editor, each = tinymce.each, dom = tinymce.DOM, event = tinymce.dom.Event, d, h;
	if (!$(id)) {
		tinyMCEPopup.alert(ed.getLang('advlink_dlg.setup_failed'));
		return;
	}
	dContainerId = id;
	
	function showLinkChooser(e) {
		if ($(id+'_list')) {
			dom.remove($(id+'_list'), false);
		}
		var v, btn = '<div class="delActionPanel"><input class="updateButton" type="button" value="Close" name="close" onclick="closeDelightLinkWizardList(\''+id+'\');" /></div>';
		var c = dom.add(document.body, 'div', {
			id: id+'_container',
			'class': 'dadvlnk_cont',
			content: 'Please wait, loading...'
		}, btn);
		
		if (typeof(e.element) == 'function') {
			v = e.element().getAttribute('id').substr(-1, 1);
		} else {
			v = e;
		}
		
		new Ajax.Request('/delight_hp/index.php', {
			method : 'post',
			parameters : {adm:100, action:'get_link_list', type:v, selected:$('href').value},
			onSuccess : function(transport) {
				var cont = transport.responseJSON;
				if (cont.success) {
					switch (cont.type) {
					case 'm': // Menu
						drawMenuList(c, cont.entries, 0, cont.lang);
						break;
					case 'p': // Picture
						drawDataList(c, cont.entries, cont.lang, 'image');
						break;
					case 'f': // File
						drawDataList(c, cont.entries, cont.lang, 'program');
						break;
					case 'n': // News
						drawDataList(c, cont.entries, cont.lang, 'news');
						break;
					case 't': // Text
						drawDataList(c, cont.entries, cont.lang, 'text');
						break;
					}
				}
			},
			onError: function() {
				c.innerHTML = 'Error: Ran into a Timeout or no Elements where received...';
			}
		});
	};
	
	var check = $('href').value.split('/');
	if ((check.length >= 3) && (check[1].length == 2)) {
		switch (check[2]) {
		case 'image': showLinkChooser('p'); return; break;
		case 'program': showLinkChooser('f'); return; break;
		case 'news': showLinkChooser('n'); return; break;
		case 'text': showLinkChooser('t'); return; break;
		default: showLinkChooser('m'); return; break;
		}
	}
	
	h = '<div class="odd"  id="'+id+'_list_m">'+ed.getLang('advlink_dlg.wizard_link_menu')+'</div>'+
	    '<div class="even" id="'+id+'_list_p">'+ed.getLang('advlink_dlg.wizard_link_picture')+'</div>'+
	    '<div class="odd"  id="'+id+'_list_f">'+ed.getLang('advlink_dlg.wizard_link_file')+'</div>'+
	    '<div class="even" id="'+id+'_list_n">'+ed.getLang('advlink_dlg.wizard_link_news')+'</div>'+
	    '<div class="odd"  id="'+id+'_list_t">'+ed.getLang('advlink_dlg.wizard_link_text')+'</div>';
	
	d = dom.add($(id), 'div', {
		id:id+'_list',
		'class': 'dadvlnk_chooser'
	}, h, 0);
	
	each(['m','p','f','n','t'], function(v) {
		event.add($(id+'_list_'+v), 'click', showLinkChooser);
	});
	/*event.add(d, 'mouseout', function(e) {
		dom.remove($(id+'_list'), false);
	});*/
};

var selectedMenu = 0;
function drawMenuList(parent, list, pid, lang) {
	var show = (pid == 0), event = tinymce.dom.Event, ul = tinymce.DOM.add(parent, 'ul', { 'class':'dadvlnk_menu', id: 'dul_'+pid });
	list.each(function(e) {
		var section = '<img id="dse_'+e.id+'" src="img/section_'+((e.childs && e.childs.length > 0)?'expand':'none')+'.gif" alt="." style="width:9px;height:9px;vertical-align:middle;margin:0 3px 0 0;" />';
		var li = tinymce.DOM.add(ul, 'li', { title:'/'+lang+'/'+e.short, id: 'dli_'+e.id }, '<span id="dsp_'+e.id+'">'+section+e.text+'</span><img id="dim_'+e.id+'" src="img/chooseLink.png" style="margin:0 0 0 10px;vertical-align:middle;width:16px;height:16px;cursor:pointer;" alt="->" />');
		event.add(li.firstChild, 'click', function(ev) {
			var id = ev.element().getAttribute('id').substring(4);
			var d = $('dul_'+id);
			if (d) {
				d.style.display = (d.style.display == 'none') ? 'block' : 'none';
				$('dse_'+id).src = (d.style.display == 'none') ? 'img/section_expand.gif' : 'img/section_collapse.gif';
			}
		});
		event.add(li.firstChild.nextSibling, 'click', function(ev) {
			var id = ev.element().getAttribute('id').substring(4);
			if ($('dsp_'+selectedMenu)) {
				$('dsp_'+selectedMenu).style.fontWeight = 'normal';
			}
			selectedMenu = id;
			$('dsp_'+selectedMenu).style.fontWeight = 'bold';
			
			$('href').value = $('dli_'+selectedMenu).getAttribute('title');
			$('onclick').value = '';
			tinymce.DOM.remove($(dContainerId+'_container'));
		});
		show = (show || e.selected || e.childSelected);
		selectedMenu = e.selected ? e.id : selectedMenu;
		if (selectedMenu == e.id) {
			li.firstChild.style.fontWeight = 'bold';
		}
		if (e.childs && e.childs.length > 0) {
			drawMenuList(li, e.childs, e.id, lang);
		}
	});
	ul.style.display = show ? 'block' : 'none';
};

function drawDataList(parent, list, lang, link) {
	var dom = tinymce.DOM, event = tinymce.dom.Event, sl = dom.add(parent, 'div', {
		'class':'dadvlnk_sectlist',
		id:'dadvlnk_sectionlist'
	}), il = dom.add(parent, 'div', {
		'class':'dadvlnk_contlist',
		id:'dadvlnk_content'
	});

	function section_click(e, elem) {
		if (!elem) {
			elem = e.element();
		}
		var id = elem.getAttribute('id').substr(4);
		il.innerHTML = '';
		list.data.each(function(section) {
			if (section.section == id) {
				section.data.each(function(obj) {
					var d = dom.add(il, 'div', {
						'class':'dadvlnk_'+link+(obj.selected ? ' dadvlnk_selected' : ''),
						id:'imageid_'+obj.id,
						title:obj.size[0]+'x'+obj.size[1]
					}, obj.html);
					event.add(d, 'click', function(ev) {
						var id = Event.findElement(ev, 'div').getAttribute('id').substr(8);
						var size = Event.findElement(ev, 'div').getAttribute('title').split('x');
						$('href').value = '/'+lang+'/'+link+'/'+id+'/noslide';
						$('onclick').value = 'openWindow(\'/'+lang+'/'+link+'/'+id+'/noslide\','+size[0]+','+size[1]+');return false;';
						tinymce.DOM.remove($(dContainerId+'_container'));
					});
				});
			}
		});
	};
	
	addSectionList(sl, 0, list.sections, section_click);
};


function addSectionList(parent, pid, list, f) {
	var show = (pid == 0), event = tinymce.dom.Event, ul = tinymce.DOM.add(parent, 'ul', { 'class':'dadvlnk_menu', id: 'dul_'+pid, style:(show?'padding:0;':'') });
	list.each(function(e) {
		var section = '<img id="dse_'+e.id+'" src="img/section_'+((e.childs && e.childs.length > 0)?'expand':'none')+'.gif" alt="." style="width:9px;height:9px;vertical-align:middle;margin:0 3px 0 0;" />';
		var li = tinymce.DOM.add(ul, 'li', { title:e.id, id: 'dli_'+e.id }, '<span id="dsp_'+e.id+'">'+section+e.name+'</span>');
		event.add(li.firstChild, 'click', function(ev) {
			var id = ev.element().getAttribute('id').substring(4);
			var d = $('dul_'+id);
			if (d) {
				d.style.display = (d.style.display == 'none') ? 'block' : 'none';
				$('dse_'+id).src = (d.style.display == 'none') ? 'img/section_expand.gif' : 'img/section_collapse.gif';
			}
		});
		event.add(li.firstChild, 'click', f);
		if (e.selected) {
			f(null, li.firstChild);
		}
		
		show = (show || e.selected || e.childSelected);
		if (e.childs && e.childs.length > 0) {
			addSectionList(li, e.id, e.childs, f);
		}
	});
	ul.style.display = show ? 'block' : 'none';
}

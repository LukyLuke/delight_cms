var TextgroupManagement = {
	adminAction:2100,
	
	preInit : function() { },

	init : function(ed) {
		var mgmt = TextgroupManagement;
		AdminDialog.loadContent(mgmt.adminAction, { action:'textlist', entry:entityId }, mgmt);
	},
	
	// Main Functions
	showContent : function(cont) {
		if (cont && cont.action) {
			switch (cont.action) {
			case 'textlist':
				this._createTextList(cont.title, cont.selected, cont.list);
				break;
			}
		}
		return;
		
		if (cont && cont.list && cont.list.length) {
			var i, l=cont.list, cl, el, c = document.createElement('ul'), section = cont.section || 0;
			c.setAttribute('id', 'sortableContainer');
			c.setAttribute('class', 'table');
			c.setAttribute('_sectionId', section);
			$('content').appendChild(c);
			
			for (i = 0; i < l.length; i++) {
				cl = (i%2) ? 'odd' : 'even';
				el = this._createFileLine(l[i], cl);
				if (el) {
					c.appendChild(el);
				}
			}
			AdminDialog.makeSortable(FileManagement.updateSortOrder, 'sortableContainer', 'sortable');
			
		} else if (cont && cont.error) {
			console.error(cont.error);
		}
	},
	
	save : function() {
		var order = Sortable.serialize($('grouped_texts')).replace(/grouped_texts\[\]=/g, '').replace(/&/g, ',');
		tinyMCEPopup.editor.setProgressState(1);
		AdminDialog.callFunction(TextgroupManagement.adminAction, {action:'update',entry:entityId,list:order}, TextgroupManagement);
	},
	
	close : function(cont) {
		if (cont && (typeof(cont['success']) != 'undefined') && cont.success) {
			AdminDialog.getWin().location.href = AdminDialog.getWin().location.href.replace(/textParser\=\d+/g, '');
			AdminDialog.close();
		} else {
			console.debug(cont);
			console.error("Error while saving the TextGroup");
		}
		tinyMCEPopup.editor.setProgressState(0);
	},
	
	// private functions
	_createTextList : function(title, selected, list) {
		var left = document.createElement('ul'), right = document.createElement('ul');
		$('content_texts').addClassName('dragdrop');
		
		left.setAttribute('id', 'ungrouped_texts');
		left.addClassName('left');
		$('content_texts').appendChild(left);
		
		var d = document.createElement('li');
		d.innerHTML = AdminDialog.getLang('group_unassigned');
		d.addClassName('title');
		left.appendChild(d);
		
		list.each(function(obj, k) {
			if (selected.indexOf(obj.id) >= 0) {
				return;
			}
			var d = document.createElement('li');
			d.innerHTML = obj.title;
			d.addClassName('draggable');
			d.setAttribute('id', 'text_'+obj.id);
			left.appendChild(d);
		});
		
		right.setAttribute('id', 'grouped_texts');
		right.addClassName('right');
		$('content_texts').appendChild(right);
		
		var d = document.createElement('li');
		d.innerHTML = AdminDialog.getLang('group_assigned');
		d.addClassName('title');
		right.appendChild(d);
		
		list.each(function(obj, k) {
			if (selected.indexOf(obj.id) < 0) {
				return;
			}
			var d = document.createElement('li');
			d.innerHTML = obj.title;
			d.addClassName('draggable');
			d.setAttribute('id', 'text_'+obj.id);
			right.appendChild(d);
		});
		
		// make both lists sortable
		Sortable.create(left, {
			dropOnEmpty: true,
			containment: ['ungrouped_texts','grouped_texts'],
			constraint: false,
			only: 'draggable',
			hoverclass: 'hover',
			onUpdate: TextgroupManagement._updateGroupContent
		});
		
		Sortable.create(right, {
			dropOnEmpty: true,
			containment: ['ungrouped_texts','grouped_texts'],
			constraint: false,
			only: 'draggable',
			hoverclass: 'hover',
			onUpdate: TextgroupManagement._updateGroupContent
		});
		
		// Resize the Contents
		window.setTimeout(TextgroupManagement._resize, 100);
		Event.observe(document.onresize ? document : window, "resize", TextgroupManagement._resize);
	},
	
	_updateGroupContent : function(c) {
		/*if (c.getAttribute('id') == 'grouped_texts') {
			var order = Sortable.serialize(c).replace(/grouped_texts\[\]=/g, '').replace(/&/g, ',');
			console.debug(order);
			AdminDialog.callFunction(TextgroupManagement.adminAction, {action:'update',entry:entityId,list:order}, TextgroupManagement);
		}*/
	},
	
	_resize : function() {
		var off = $('ungrouped_texts').cumulativeOffset();
		var btn = $('buttonbar').cumulativeOffset();
		$('ungrouped_texts').style.height = (btn[1] - off[1] - 10) + 'px';
		$('grouped_texts').style.height = (btn[1] - off[1] - 10) + 'px';
	},
	
	_createFileLine : function(o, cl) {
		if (typeof(o)=='undefined') {
			return false;
		}
		var a = AdminDialog,img,el,tbl,tr,td;
		el = document.createElement('li');
		el.setAttribute('id', 'fileEntry_'+o.id); // For FF3.+ the ID mus be in Format "string_identifier"
		el.setAttribute('class', 'sortable '+cl);
		
		el.onclick = function(e) {
			var id = AdminDialog.getSelectedEntry('fileEntry_');
			if (id) {
				$('fileEntry_'+id).removeClassName('selected');
			}
			this.addClassName('selected');
		};
		
		tbl = document.createElement('table');
		tbl.style.width = '100%';
		el.appendChild(tbl);
		
		tr = document.createElement('tr');
		tbl.appendChild(tr);
		
		td = document.createElement('td');
		td.setAttribute('class', 'file');
		tr.appendChild(td);
		
		img = document.createElement('img');
		img.setAttribute('id', 'file'+o.id);
		img.setAttribute('alt', 'MimeType Icon');
		img.setAttribute('src', o.icon.src);
		img.style.width = '32px';
		img.style.height = '32px';
		td.appendChild(img);
		
		td = document.createElement('td');
		td.setAttribute('class', 'content');
		td.innerHTML = '<span class="title">'+(o.title.length ? o.title : o.name)+'</span><br/>'+
                       '<span class="identifier">'+a.getLang('file_name')+':</span><span class="value">'+o.name+'</span><br/>'+
                       '<span class="identifier">'+a.getLang('file_downloaded')+':</span><span class="value">'+o.downloaded+' ('+a.getLang('file_last')+' '+o.downloaded_lastfull+')</span><br/>'+
                       '<span class="identifier">'+a.getLang('file_mime')+':</span><span class="value">'+o.mimecomment+' ('+o.mimetype+')</span><br/>'+
                       '<span class="identifier">'+a.getLang('file_date')+':</span><span class="value">'+o.date+'</span><br/>'+
                       '<span class="identifier">'+a.getLang('file_link')+':</span><span class="value">'+o.download_link+'</span><br/>'+
		               '<span class="identifier">'+a.getLang('file_size')+':</span><span class="value">'+a.getHRSize(o.size)+'</span><br/>'+
		               o.text;
		tr.appendChild(td);
		
		return el;
	}
	
};

TextgroupManagement.preInit();
tinyMCEPopup.onInit.add(TextgroupManagement.init, TextgroupManagement);

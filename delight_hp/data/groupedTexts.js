var GroupedTexts = {
	tryLoading : {},
	
	show : function(title, content, cssClass) {
		var tId = title.replace(/\d+/gi, ''), cId = content.replace(/\d+/gi, '');
		try { title = document.getElementById(title); } catch(e) {return;}
		try { content = document.getElementById(content); } catch(e) {return;}
		var i,j, tc = title.parentNode.childNodes, cc = content.parentNode.childNodes;
		
		// hide all titles
		for (i=0; i < tc.length; i++) {
			if (tc[i].id &&
					(tc[i].getAttribute('id').indexOf(tId) == 0)) {
				classes = tc[i].className.split(/\s+/);
				newClasses = '';
				for (j=0; j < classes.length; j++) {
					if ((classes[j] != cssClass) && (classes[j] != '')) {
						newClasses += classes[j]+' ';
					}
				}
				tc[i].className = newClasses;
			}
		}
		
		// hide all contents
		for (i=0; i < cc.length; i++) {
			if (cc[i].id && (cc[i].getAttribute('id').indexOf(cId) == 0)) {
				classes = cc[i].className.split(/\s+/);
				newClasses = '';
				for (j=0; j < classes.length; j++) {
					if ((classes[j] != cssClass) && (classes[j] != '')) {
						newClasses += classes[j]+' ';
					}
				}
				cc[i].className = newClasses;
			}
		}
		
		title.className += cssClass;
		content.className += cssClass;
	},
	
	hide : function(list) {
		for (var i = 0; i < list.length; i++) {
			window.groupedTexts.tryLoading['admcont_'+list[i]] = 0;
			window.setTimeout('GroupedTexts.doHideWhenLoaded(\'admcont_'+list[i]+'\');', 1);
		}
	},
	
	doHideWhenLoaded : function(id) {
		var c = document.getElementById(id);
		if (!c && (window.groupedTexts.tryLoading[id] < 100)) {
			window.groupedTexts.tryLoading[id]++;
			window.setTimeout('GroupedTexts.doHideWhenLoaded(\''+id+'\');', 10);
		} else if (c) {
			c.parentNode.removeChild(c);
		}
	}
	
};

window.groupedTexts = GroupedTexts;

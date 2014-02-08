var UserManagement = {
	adminAction:1200,
	actual: null,
	preInit : function() { },

	init : function(ed) {
		var mgmt = UserManagement;
		AdminDialog.loadSections('section', mgmt.adminAction, mgmt.sectionClick);
		AdminDialog.callFunction(mgmt.adminAction, {action:'accesslist'}, mgmt);
		AdminDialog.callFunction(mgmt.adminAction, {action:'grouplist'}, mgmt);

		// Section-Toolbar
		AdminDialog.addButton('sectionToolbar', {
			name : 'section_new',
			button : 'section_new',
			params : 'adm:'+mgmt.adminAction,
			action : AdminDialog.sectionCreate
		});
		AdminDialog.addButton('sectionToolbar', {
			name : 'section_edit',
			button : 'section_edit',
			params : 'adm:'+mgmt.adminAction,
			action : AdminDialog.sectionEdit
		});
		AdminDialog.addButton('sectionToolbar', {
			name : 'section_delete',
			button : 'section_delete',
			params : 'adm:'+mgmt.adminAction,
			action : AdminDialog.sectionDelete
		});

		// Content-Toolbar
		AdminDialog.addButton('contentToolbar', {
			name : 'user_groups',
			button : 'user_groups',
			action : mgmt.addUserGroup
		});
		/*AdminDialog.addButton('contentToolbar', {
			name : 'user_rights',
			button : 'user_rights',
			action : mgmt.showUserRight
		});*/
	},

	// Toolbar-Eventhandler
	addUserGroup : function(e) {
		var url = '/delight_hp/index.php?lang='+AdminDialog.getLang()+'&adm='+UserManagement.adminAction+'&action=addusergroup';
		if (e.groupId) {
			url += '&entry='+e.groupId;
		}
		AdminDialog.openWindow(url, 500, 100);
	},
	storeGroupData : function() {
		var name = $('groupname').value, desc = $('groupdescription').value, id = parseInt($('groupid').value);
		if ( (name.length > 0) && (desc.length > 0) ) {
			tinyMCEPopup.editor.setProgressState(1);
			AdminDialog.callFunction(UserManagement.adminAction, {action:'savegroup',id:id,name:name,description:desc}, UserManagement);
		}
	},

	// Main Functions
	sectionClick : function(s) {
		tinyMCEPopup.editor.setProgressState(1);
		AdminDialog.loadContent(UserManagement.adminAction, {section:s}, UserManagement);
	},
	sectionDeleteFinal : function(o) {
		if (o.success) {
			this._clearInputFields();
		}
	},

	deleteUserGroup : function(e) {
		if (e && e.groupId) {
			tinyMCEPopup.editor.windowManager.confirm(AdminDialog.getLang('user_group_delete_question'), function(d) {
				if (d) {
					tinyMCEPopup.editor.setProgressState(1);
					AdminDialog.callFunction(UserManagement.adminAction, {action:'deletegroup',groupid:e.groupId}, UserManagement);
				}
			});
		}
	},
	deleteGroupFinal : function(o) {
		if (o && o.groupid) {
			var r = $('grouprow'+o.groupid);
			if (r) {
				r.parentNode.removeChild(r);
			}
		}
	},

	showContent : function(c) {
		this._clearInputFields();
		UserManagement.actual = c;

		if (c && !c.error && c.data && !c.data.error) {
			var i,k,v,d = c.data;
			for (k in d) {
				v = d[k];
				if (k == 'groups') {
					for (i = 0; i < v.length; i++) {
						if ($('grp_'+v[i])) {
							$('grp_'+v[i]).checked = true;
						}
					}
				} else if (k == 'rights') {
					for (i = 0; i < v.length; i++) {
						if ($(v[i])) {
							$(v[i]).checked = true;
						}
					}
				} else if ($('fld_'+k)) {
					if (k == 'username') {
						$('fld_'+k).innerHTML = v;
					} else {
						$('fld_'+k).value = v;
					}
				}
			}

		} else if (c && c.error) {
			console.error(c.error);
		}
	},
	reloadContent: function() {
		tinyMCEPopup.editor.setProgressState(1);
		AdminDialog.callFunction(UserManagement.adminAction, {action:'grouplist',section:AdminDialog.getSelectedSection()}, UserManagement);
	},

	save : function() {
		var i,o,data={},groups=[],rights=[],s = AdminDialog.getSelectedSection(),e = document.getElementsByTagName('input');

		if (!s) {
			return;
		}

		for (i = 0; i < e.length; i++) {
			o = e[i];
			if (o.name.substr(0,4) == 'fld_') {
				data[o.name.substr(4)] = o.value;
			} else if (o.name.substr(0,4) == 'RGT_') {
				if (o.checked) {
					rights.push(o.value);
				}
			} else if (o.name.substr(0,4) == 'user') {
				if (o.checked) {
					groups.push(o.value);
				}
			}
		}
		tinyMCEPopup.editor.setProgressState(1);
		AdminDialog.callFunction(UserManagement.adminAction, {action:'saveuser',id:s,data:Object.toJSON(data),groups:Object.toJSON(groups),rights:Object.toJSON(rights)}, UserManagement);
	},

	// private functions

	_loadAccessList : function(c) {
		var i,o,cont = $('access_panel');
		if (!cont) {
			return;
		}
		cont.innerHTML = '';
		for (i = 0; i < c.data.length; i++) {
			o = c.data[i];
			cont.innerHTML += '<input type="checkbox" id="'+o.name.toLowerCase()+'" name="'+o.name+'" value="'+o.name+'" /> '+o.description+'<br/>';
		}
	},

	_loadUserGroups : function(c) {
		var i,o,cont = $('groups_panel'),h='';
		if (!cont) {
			return;
		}
		h = '<table style="width:100%">';
		for (i = 0; i < c.data.length; i++) {
			o = c.data[i];
			h += '<tr id="grouprow'+o.id+'"><td>';
			h += '<input type="checkbox" id="grp_'+o.id+'" name="usergroups[]" value="'+o.id+'" /> <strong>'+o.name+'</strong>, '+o.description+'<br/>';
			h += '</td><td style="text-align:right;">';
			h += '<span onclick="UserManagement.addUserGroup({groupId:'+o.id+'});" class="link">'+AdminDialog.getLang('user_group_edit')+'</span>';
			h += '&nbsp;&nbsp;';
			h += '<span onclick="UserManagement.deleteUserGroup({groupId:'+o.id+'});" class="link">'+AdminDialog.getLang('user_group_delete')+'</span>';
			h += '</td></tr>';
		}
		cont.innerHTML = h+'</table>';
		if (UserManagement.actual && UserManagement.actual.data && UserManagement.actual.data.groups) {
			tinymce.each(UserManagement.actual.data.groups, function(g) {
				if ($('grp_'+g)) {
					$('grp_'+g).checked = true;
				}
			});
		}
	},

	_changeGroupData : function(o) {
		if (o && o.success) {
			var win = AdminDialog.getOpener();
			if (win && win.UserManagement) win.UserManagement.reloadContent();
			AdminDialog.close();
		}
	},

	_addCreatedGroup : function(o) {
		if (o && o.success) {
			var win = AdminDialog.getOpener();
			if (win && win.UserManagement) win.UserManagement.reloadContent();
			AdminDialog.close();
		}
	},

	_clearInputFields : function() {
		var i,j,f, inp = document.getElementsByTagName('input');
		for (i = 0; i < inp.length; i++) {
			f = inp[i];
			switch (f.type) {
			case 'button':
			case 'submit':
			case 'clear':
				break;
			case 'checkbox':
			case 'radio':
				f.checked = f.defaultChecked;
				break;
			default:
				f.value = f.defaultValue;
			}
		}
		inp = document.getElementsByTagName('select');
		for (i = 0; i < inp.length; i++) {
			f = inp[i].options;
			for (j = 0; j < f.length; j++) {
				f[j].selected = f[j].defaultSelected;
			}
		}
		if ($('fld_username')) {
			$('fld_username').innerHTML = ' ';
		}
	}

};

UserManagement.preInit();
tinyMCEPopup.onInit.add(UserManagement.init, UserManagement);

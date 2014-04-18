[SUBMENU_MAIN]
	[SUBMENU_ENTRIES]
[/SUBMENU_MAIN]

[ADMIN_MENU]
	<div style="position:fixed;top:10px;left:10px;width:50px;height:50px;background:url('[MAIN_DIR]images/layout/admin_menu.png') top left no-repeat;z-index:9999999;" onmouseover="document.getElementById('mainadminmenu').style.display='inline';" onmouseout="document.getElementById('mainadminmenu').style.display='none';">&nbsp;
		<div id="mainadminmenu" style="display:none;">
			<table cellpadding="0" cellspacing="5" style="background-color:rgb(255,255,255);border:1px solid black;">
				<tr>
					<td><strong>[LANG_VALUE:adm_008]</strong></td>
				</tr>
				<tr>
					<td>
						[CAT_NOMENU]<a style="padding-left:5px;white-space:nowrap;color:#000;" href="javascript:createMenu('[ADMIN_MENU_BASEID]');"><img src="[DATA_DIR]admin_images/menu_0.png" style="vertical-align:middle;width:22px;height:22px;margin:3px 10px 3px 3px;" />[LANG_VALUE:adm_009]</a><br />[/CAT_NOMENU]
						[SUBMENU]<a style="padding-left:5px;white-space:nowrap;color:#000;" href="[SUBMENU_LINK]">[SUBMENU_TITLE]</a><br />[/SUBMENU]
					</td>
				</tr>
			</table>
		</div>
	</div>
[/ADMIN_MENU]
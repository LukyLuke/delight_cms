[SUBMENU_MAIN]
	[SUBMENU_ENTRIES]
[/SUBMENU_MAIN]

[MENU_1_open]
			<fieldset class="men">
				<legend class="men">
					<a class="menulnkhead" href="[SUBMENU_LINK]">
						[MENU_TITLE]
					</a>
				</legend>

					[SUBMENU:0]
						<div class="menuadm1">
							<a class="menulnk" style="font-weight:bold;" href="[SUBMENU_LINK]">
								[SUBMENU_TITLE]
							</a>
						</div>
					[/SUBMENU]

					[SUBMENU:1]
						<div class="menuadm2" style="margin-left:[LEVEL_INSERT:"2":"8":"m"]px;">
							<a class="menulnk" href="[SUBMENU_LINK]">
								[SUBMENU_TITLE]
							</a>
						</div>
					[/SUBMENU]

					[SUBMENU:3]
						<div class="menuadm2" style="border-left:1px solid rgb(150,150,150);border-right:1px solid rgb(150,150,150);background-color:rgb(200,200,200);margin-left:[LEVEL_INSERT:"2":"8":"m"]px;[IF_FIRST:"border-top:1px solid rgb(150,150,150);":""][IF_LAST:"border-bottom:1px solid rgb(150,150,150);":""]">
							<a class="menulnk" href="[SUBMENU_LINK]">
								[SUBMENU_TITLE]
							</a>
						</div>
					[/SUBMENU]

			</fieldset>
[/MENU_1_open]


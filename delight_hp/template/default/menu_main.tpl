[SUBMENU_MAIN]
<ul class="menu">
	[SUBMENU_ENTRIES]
</ul>
[/SUBMENU_MAIN]

[MENU_1_closed]
	<li class="menu-entry">
		<span class="menu-entry-outer" [ADMINID]>
			<a href="[SUBMENU_LINK]" class="menu-entry [IN_MENU_BACKTRACE:"menu-active"][MENU_NOT_TRANSLATED:" translate"][MENU_NOT_VISIBLE:" invisible"]">
				<span class="menu-entry-inner">[MENU_TITLE]</span>
			</a>
		</span>
		[ADMIN_MENU]
	</li>
[/MENU_1_closed]

[MENU_1_open]
	<li class="menu-entry menu-open [IN_MENU_BACKTRACE:"menu-active"]">
		<span class="menu-entry-outer" [ADMINID]>
			<a href="[SUBMENU_LINK]" class="menu-entry [IN_MENU_BACKTRACE:"menu-active"][MENU_NOT_TRANSLATED:" translate"][MENU_NOT_VISIBLE:" invisible"]">
				<span class="menu-entry-inner">[MENU_TITLE]</span>
			</a>
		</span>
		[ADMIN_MENU]
		[NO_SUBMENU]
		<ul class="submenu">
			[SUBMENU_ENTRY]
		</ul>
		[/NO_SUBMENU]
	</li>
	
	[SUBMENU:0]
	<li class="menu-entry [IF_LAST:"small-bottom"] [IN_MENU_BACKTRACE:"menu-active"]">
		<span class="menu-entry-outer" [ADMINID]>
			<a href="[SUBMENU_LINK]" class="menu-entry [IN_MENU_BACKTRACE:"menu-active"] [MENU_NOT_TRANSLATED:"translate"] [MENU_NOT_VISIBLE:"invisible"]">
				<span class="menu-entry-inner [IN_MENU_BACKTRACE:"menu-active"]">[MENU_IMAGE:32x32:false] [MENU_TITLE]</span>
			</a>
		</span>
		<div class="menu-info">
			<p class="menu-info-menu-name">[MENU_IMAGE:32x32:false] <strong>[MENU_TITLE]</strong></p>
			<p class="menu-info-title"><strong>[SITE_TITLE]</strong></p>
			<p class="menu-info-description">[SITE_DESCRIPTION]</p>
		</div>
		[ADMIN_MENU]
		
		[NO_SUBMENU]
		<ul class="submenu">
			[SUBMENU_ENTRY]
		</ul>
		[/NO_SUBMENU]
	</li>
	[/SUBMENU]
[/MENU_1_open]

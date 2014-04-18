[DESCR:german]
Ein Hinweis mit optionalem Symbol
[/DESCR]

[DESCR:english]
A notice with an optional icon
[/DESCR]

[STYLE_INCLUDE]css_boxes.css[/STYLE_INCLUDE]
[STYLE_CONTENT][/STYLE_CONTENT]

[LAYOUT]
<div class="textentry notice sortable"[ADMINID]>
	<div class="icon [OPTION:icon,none]">
		[CAT_TITLE]<div class="texttitle">
			<[OPTION:title,default] class="ttitle">[TITLE]</[OPTION:title,default]>
		</div>[/CAT_TITLE]
		[CAT_CONTENT]<div class="textcontent">[TEXT]</div>[/CAT_CONTENT]
	</div>
</div>
[/LAYOUT]

[OPTIONS]
	[title_default]h2[/title_default]
	[title_big]h1[/title_big]
	[title_small]h3[/title_small]
	[icon_none]none[/icon_none]
	[icon_info]info[/icon_info]
	[icon_success]success[/icon_success]
	[icon_warning]warning[/icon_warning]
	[icon_failed]stop[/icon_failed]
	[icon_help]help[/icon_help]
[/OPTIONS]

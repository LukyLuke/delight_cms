[DESCR:german]
Nur Text und Titel ohne Abstand
[/DESCR]

[DESCR:english]
Only Text and Title without padding
[/DESCR]

[STYLE_INCLUDE]css_boxes.css[/STYLE_INCLUDE]
[STYLE_CONTENT][/STYLE_CONTENT]

[LAYOUT]
<div class="textentry nospace sortable"[ADMINID]>
	[CAT_TITLE]<div class="texttitle">
		<[OPTION:title,default] class="ttitle tdark">[TITLE]</[OPTION:title,default]>
	</div>[/CAT_TITLE]
	[CAT_CONTENT]<div class="textcontent">[TEXT]</div>
	<div class="textcontent-footer">&nbsp;</div>[/CAT_CONTENT]
</div>
[/LAYOUT]

[OPTIONS]
	[title_default]h2[/title_default]
	[title_big]h1[/title_big]
	[title_small]h3[/title_small]
[/OPTIONS]
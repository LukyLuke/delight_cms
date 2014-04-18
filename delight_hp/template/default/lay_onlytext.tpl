[DESCR:german]
Text - Nur Text und Titel
[/DESCR]

[DESCR:english]
Textbox - Only Text and Title
[/DESCR]

[STYLE_INCLUDE]css_boxes.css[/STYLE_INCLUDE]
[STYLE_CONTENT][/STYLE_CONTENT]

[LAYOUT]
<div class="textentry plaintext sortable"[ADMINID]>
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
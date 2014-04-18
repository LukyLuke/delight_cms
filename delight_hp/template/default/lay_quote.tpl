[DESCR:german]
Ein text der als "Zitat" formatiert wird
[/DESCR]

[DESCR:english]
A textblock which is marked as a quote
[/DESCR]

[STYLE_INCLUDE]css_boxes.css[/STYLE_INCLUDE]
[STYLE_CONTENT][/STYLE_CONTENT]

[LAYOUT]
<div class="textentry quote sortable"[ADMINID]>
	[CAT_CONTENT]<div class="textcontent">[TEXT]</div>[/CAT_CONTENT]
	[CAT_TITLE]<div class="texttitle">
		<[OPTION:title,default] class="ttitle">[TITLE]</[OPTION:title,default]>
	</div>[/CAT_TITLE]
</div>
[/LAYOUT]

[OPTIONS]
	[title_default]h2[/title_default]
	[title_big]h1[/title_big]
	[title_small]h3[/title_small]
[/OPTIONS]

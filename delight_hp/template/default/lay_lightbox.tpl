[DESCR:german]
Text in einer box, eher hell
[/DESCR]

[DESCR:english]
Text in a light box
[/DESCR]

[STYLE_INCLUDE]css_boxes.css[/STYLE_INCLUDE]
[STYLE_CONTENT][/STYLE_CONTENT]

[LAYOUT]
<div class="textentry boxed dark sortable"[ADMINID]>
	[CAT_TITLE]<div class="textheader-container">
			<div class="texttitle">
				<[OPTION:title,default] class="ttitle">[TITLE]</[OPTION:title,default]>
			</div>
		</div>
		<div style="clear:both"></div>[/CAT_TITLE]
		[CAT_CONTENT]<div class="textcontent">[TEXT]</div>
		<div class="textcontent-footer">&nbsp;</div>[/CAT_CONTENT]
		<div class="textfooter-container">
			<div class="textfooter-inner">&nbsp;</div>
		</div>
</div>
[/LAYOUT]

[OPTIONS]
	[title_default]h2[/title_default]
	[title_big]h1[/title_big]
	[title_small]h3[/title_small]
[/OPTIONS]

[STYLE_INCLUDE]css_boxes.css[/STYLE_INCLUDE]

[LAYOUT]
				[SUCCESS]<script>
					<!--
						function doConfirm()
						{
							var lnk = '[CONFIRM_LINK]';
							window.location.href = lnk.replace(/amp;/gi, "");
						}
					//-->
				</script>
				<fieldset class="tlw">
						<legend class="tlw h1">[TITLE]</legend>
						<p class="h2" style="white-space:normal;">[MESSAGE]</p>
						<div style="text-align:right;">
							<input type="button" value="[CONFIRM]" onclick="doConfirm()" />
						</div>
				</fieldset>
				[/SUCCESS]

				[SUCCESS_BACK]<script>
					<!--
						function doConfirm()
						{
							var lnk = '[CONFIRM_LINK]';
							window.location.href = lnk.replace(/amp;/gi, "");
						}
					//-->
				</script>
				<fieldset class="tlw">
						<legend class="tlw h1">[TITLE]</legend>
						<p class="h2" style="white-space:normal;text-align:center;">[MESSAGE]</p>
						<div style="text-align:right;">
							<input type="button" value="[CONFIRM]" onclick="doConfirm()" />
							&nbsp;&nbsp;
							<input type="button" value="[CONFIRM_BACK]" onclick="window.history.back();" />
						</div>
				</fieldset>
				[/SUCCESS_BACK]

				[SUCCESS_NOBUTTON]<fieldset class="tlw">
						<legend class="tlw h1">[TITLE]</legend>
						<p style="text-align:center;">[MESSAGE]</p>
				</fieldset>[/SUCCESS_NOBUTTON]

				[FAILED]
				<script>
					<!--
						function doConfirm()
						{
							var lnk = '[CONFIRM_LINK]';
							window.location.href = lnk.replace(/amp;/gi, "");
						}
					//-->
				</script>
				<fieldset class="tdw">
						<legend class="tdw h1">[TITLE]</legend>
						<p class="h2" style="white-space:normal;text-align:center;">[MESSAGE]</p>
						<div style="text-align:right;">
							<input type="button" value="[CONFIRM]" onclick="doConfirm()" />
						</div>
				</fieldset>
				[/FAILED]

				[BACK_FAILED]
				<script>
					<!--
						function doConfirm() {
							var lnk = '[CONFIRM_LINK]';
							window.location.href = lnk.replace(/amp;/gi, "");
						}
					//-->
				</script>
				<fieldset class="tdw">
						<legend class="tdw h1">[TITLE]</legend>
						<p class="h2" style="white-space:normal;text-align:center;">[MESSAGE]</p>
						<div style="text-align:right;">
							<input type="button" value="[CONFIRM]" onclick="doConfirm()" />
							&nbsp;&nbsp;
							<input type="button" value="[CONFIRM_BACK]" onclick="window.history.back();" />
						</div>
				</fieldset>
				[/BACK_FAILED]
[/LAYOUT]
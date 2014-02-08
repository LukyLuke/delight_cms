[LAYOUT]

	[SETTING_LIST]
		<script type="text/javascript">
			var link = '[CHANGE_SETTINGS_LINK]';
			function changeValue(name) {
				var par = { scrollbars : "no", statusbar : "yes" };
				var tpl = { file : link.replace(/amp;/, "") + name };
				delightEditor.execCommand('admpopup', false, {template : tpl, param : par});
			}
			function initEditor() {
				setTextareaToDelightEdit('dedt_SettingsContent', 'admin');
			}
			window.setTimeout('initEditor()', 500);
		</script>
		<div class="admbase">
			<div class="admhead">[LANG_VALUE:set_001]</div>
			<div class="admcont">
				<div class="admdescr">[LANG_VALUE:set_002]</div>
				<br />
				[SETTINGS]
				<div class="admlist">
					<table cellpadding="0" cellspacing="0" style="width:100%;">
						<tr>
							<td style="width:200px;padding-right:5px;">[LANG_VALUE:set_[SETTING_NAME]]</td>
							<td style="overflow:hidden;">[SETTING_VALUE]</td>
							<td style="width:60px;text-align:right;"><a class="anchor" href="#" onclick="changeValue('[SETTING_NAME]');">[LANG_VALUE:set_003]</a></td>
						</tr>
					</table>
				</div>
				[/SETTINGS]
			</div>
			<div class="admfoot">&nbsp;</div>
		</div>

		<div style="visibility:hidden;height:1px;overflow:hidden;position:absolute;top:50px;">
			<form action="#" method="post" onsubmit="return false;" id="form_dedt_SettingsContent">
				<fieldset style="display:none;">
					<input type="hidden" name="title_dedt_SettingsContent" id="title_dedt_SettingsContent" value="" />
					<input type="hidden" name="layout_dedt_SettingsContent" id="layout_dedt_SettingsContent" value="" />
					<input type="hidden" name="options_dedt_SettingsContent" id="options_dedt_SettingsContent" value="" />
					<input type="hidden" name="id_dedt_SettingsContent" id="id_dedt_SettingsContent" value="" />
					<input type="hidden" name="dedtid_dedt_SettingsContent" id="dedtid_dedt_SettingsContent" value="0" />
				</fieldset>
				<div id="dedt_SettingsContent">&nbsp;</div>
			</form>
		</div>
	[/SETTING_LIST]
	
	[CHANGE_PARAM]
	
	[/CHANGE_PARAM]

[/LAYOUT]
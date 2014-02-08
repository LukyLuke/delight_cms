[STYLE_INCLUDE]css_boxes.css[/STYLE_INCLUDE]

[LAYOUT]
					[LANGUAGE_LIST]
						<script type="text/javascript">
							function addLang() {
								var lnk = '[LANGUAGE_CREATE_LINK]';
								window.location.href = lnk.replace(/amp;/gi, '');
							}
						</script>
						<fieldset class="tdw">
							<legend class="tdw h1">[LANG_VALUE:lang_001]</legend>
							<table cellpadding="0" cellspacing="0" style="width:100%;">
								<colgroup>
									<col style="width:80px;" />
									<col style="" />
									<col style="width:50px;" />
									<col style="width:150px;" />
									<col style="width:32px;" />
								</colgroup>

								<tr>
									<td style="padding:5px;border-bottom:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
										<strong>[LANG_VALUE:lang_002]</strong>
									</td>
									<td style="padding:5px;border-bottom:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
										<strong>[LANG_VALUE:lang_003]</strong>
									</td>
									<td style="padding:5px;border-bottom:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
										<strong>[LANG_VALUE:lang_004]</strong>
									</td>
									<td colspan="2" style="padding:5px;border-bottom:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
										<strong>[LANG_VALUE:lang_005]</strong>
									</td>
								</tr>

								[LANGUAGE_ENTRY]<tr>
									<td style="padding:5px;padding-left:10px;vertical-align:top;">
										<img src="[MAIN_DIR]../images/language/[LANGUAGE_ICON]" alt="[LANGUAGE_TEXT]" style="vertical-align:middle;" />
									</td>
									<td style="padding:5px;padding-left:10px;">
										[LANGUAGE_TEXT]
									</td>
									<td style="padding:5px;padding-left:10px;">
										[LANGUAGE_SHORT]
									</td>
									<td style="padding:5px;padding-left:10px;">
										[LANGUAGE_CHARSET]
									</td>
									<td style="vertical-align:top;">
										<a class="anchor" href="[LANGUAGE_ENABLE_LINK]"><img src="[MAIN_DIR]images/admin/languageManage_[LANGUAGE_ENABLE_STATE].gif" alt="[LANG_VALUE:lang_007]" style="width:12px;height:12px;border-width:0px;vertical-align:bottom;" /></a>
									</td>
								</tr>[/LANGUAGE_ENTRY]

								<tr>
									<td colspan="5" style="text-align:right;background-color:rgb(230,230,230);">
										<!--<input type="button" value="[LANG_VALUE:lang_008]" onclick="addLang();" />-->
										&nbsp;
									</td>
								</tr>
							</table>
						</fieldset>
					[/LANGUAGE_LIST]

					[LANGUAGE_ADD]
						<fieldset class="tdw">
							<legend class="tdw h1">[LANG_VALUE:lang_008]</legend>
							<form name="MainForm" action="[FORM_LINK]" method="post">
								<fieldset style="display:none;">
									<input type="hidden" name="lan" value="[FORM_LANGUAGE]" />
									<input type="hidden" name="m"   value="[FORM_MENU]" />
									<input type="hidden" name="s"   value="[FORM_SECTION]" />
									<input type="hidden" name="adm" value="[FORM_ACTION]" />
								</fieldset>
								<table cellpadding="0" cellspacing="0" style="width:100%;">
									<colgroup>
										<col style="" />
										<col style="" />
									</colgroup>

									<tr>
										<td style="padding:5px;border-bottom:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
											<strong>[LANG_VALUE:lang_003]</strong>
										</td>
										<td style="padding:5px;border-bottom:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
											<input type="text" name="tmpLangText" value="[LANGUAGE_TEXT]" style="padding:2px;font-size:9pt;font-family:Arial, Helvetica, sans serif;width:350px;border:1px solid rgb(150,150,150);background-color:rgb(255,255,255);" />
										</td>
									</tr>
									<tr>
										<td style="padding:5px;border-bottom:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
											<strong>[LANG_VALUE:lang_004]</strong>
										</td>
										<td style="padding:5px;border-bottom:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
											<input type="text" name="tmpLangShort" value="[LANGUAGE_SHORT]" style="padding:2px;font-size:9pt;font-family:Arial, Helvetica, sans serif;width:350px;border:1px solid rgb(150,150,150);background-color:rgb(255,255,255);" />
										</td>
									</tr>
									<tr>
										<td style="padding:5px;border-bottom:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
											<strong>[LANG_VALUE:lang_005]</strong>
										</td>
										<td style="padding:5px;border-bottom:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
											<input type="text" name="tmpLangCharset" value="[LANGUAGE_CHARSET]" style="padding:2px;font-size:9pt;font-family:Arial, Helvetica, sans serif;width:350px;border:1px solid rgb(150,150,150);background-color:rgb(255,255,255);" />
										</td>
									</tr>

									<tr>
										<td colspan="2" style="text-align:right;background-color:rgb(230,230,230);">
											<input type="submit" value="[LANG_VALUE:input_001]" />
										</td>
									</tr>
								</table>
							</form>
						</fieldset>
					[/LANGUAGE_ADD]

[/LAYOUT]
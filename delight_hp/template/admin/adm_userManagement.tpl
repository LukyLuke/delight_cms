[STYLE_INCLUDE]css_box_dark_white.css[/STYLE_INCLUDE]
[STYLE_INCLUDE]css_box_light_white.css[/STYLE_INCLUDE]

[LAYOUT]
					[USER_LIST]
						<fieldset class="tdw">
							<legend class="tdw h1">[LANG_VALUE:usr_001]</legend>
							<script>
								<!--
									function addUser()
									{
										var lnk = '[USER_CREATE_LINK]';
										window.location.href = lnk.replace(/amp;/gi, '');
									}
								//-->
							</script>
							<table cellpadding="0" cellspacing="0" style="width:100%;">
								<colgroup>
									<col style="" />
									<col style="" />
									<col style="" />
									<col style="" />
									<col style="width:32px;" />
								</colgroup>

								<tr>
									<td style="padding:5px;border-bottom:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
										<strong>[LANG_VALUE:usr_002]</strong>
									</td>
									<td style="padding:5px;border-bottom:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
										<strong>[LANG_VALUE:usr_007]</strong>
									</td>
									<td style="padding:5px;border-bottom:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
										<strong>[LANG_VALUE:usr_005]</strong>
									</td>
									<td colspan="2" style="padding:5px;border-bottom:1px solid rgb(150,150,150);background-color:rgb(250,250,250);">
										<strong>[LANG_VALUE:usr_006]</strong>
									</td>
								</tr>

								[USER_ENTRY]<tr>
									<td style="padding:5px;padding-left:10px;">
										<a href="[USER_CHANGE_LINK]">[USERLIST_USERNAME]</a>
									</td>
									<td style="padding:5px;padding-left:10px;">
										[USERLIST_NAME] [USERLIST_SURNAME]
									</td>
									<td style="padding:5px;padding-left:10px;">
										[USERLIST_COMPANY]
									</td>
									<td style="padding:5px;padding-left:10px;">
										[USERLIST_EMAIL]
									</td>
									<td style="">
										<a href="[USER_DELETE_LINK]"><img src="[MAIN_DIR]images/admin/userManage_delete.gif" alt="delete" style="width:16px;height:16px;border-width:0px;" /></a>
									</td>
								</tr>[/USER_ENTRY]

								<tr>
									<td colspan="5" style="text-align:right;background-color:rgb(230,230,230);">
										<input type="button" value="[LANG_VALUE:usr_008]" onclick="addUser();" />
									</td>
								</tr>
							</table>
						</fieldset>
					[/USER_LIST]

					[USER_CREATE]
						<script>
							<!--
								function addUser()
								{
									var pwd1 = document.getElementsByName('tmpUserPasswd')[0];
									var pwd2 = document.getElementsByName('tmpUserPasswdRetype')[0];

									if ((document.getElementsByName('tmpUserUsername')[0]) && (document.getElementsByName('tmpUserUsername')[0].value.length <= 5))
										alert("[LANG_VALUE:usr_022]");
									else if ((document.getElementsByName('tmpUserUsername')[0]) && (pwd1.value.length <= 0))
										alert("[LANG_VALUE:usr_023]");
									else if (pwd1.value != pwd2.value)
										alert("[LANG_VALUE:usr_024]");
									else if ( (document.getElementsByName('tmpUserName')[0].value.length <= 0) || (document.getElementsByName('tmpUserSurname')[0].value.length <= 0) )
										alert("[LANG_VALUE:usr_025]");
									else
										document.MainForm.submit();
								}
							//-->
						</script>
						<fieldset class="tdw">
							<legend class="tdw h1">[TITLE]</legend>
							<form name="MainForm" action="[FORM_LINK]" method="POST">
								<fieldset style="display:none;">
									<input type="hidden" name="lan" value="[FORM_LANGUAGE]" />
									<input type="hidden" name="i"   value="[FORM_CHANGEUSER]" />
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
										<td style="padding:5px;padding-left:10px;background-color:rgb(230,230,230);">
											[LANG_VALUE:usr_009]
										</td>
										<td style="padding:5px;padding-left:10px;background-color:rgb(230,230,230);">
											[CREATE]<input type="text" name="tmpUserUsername" value="" style="padding:2px;font-size:9pt;font-family:Arial, Helvetica, sans serif;width:350px;border:1px solid rgb(150,150,150);background-color:rgb(255,255,255);" />[/CREATE]
											[EDIT][USER_USERNAME][/EDIT]
										</td>
									</tr>
									<tr>
										<td style="padding:5px;padding-left:10px;background-color:rgb(240,240,240);">
											[LANG_VALUE:usr_010]
										</td>
										<td style="padding:5px;padding-left:10px;background-color:rgb(240,240,240);">
											<input type="password" name="tmpUserPasswd" value="" style="padding:2px;font-size:9pt;font-family:Arial, Helvetica, sans serif;width:350px;border:1px solid rgb(150,150,150);background-color:rgb(255,255,255);" />
										</td>
									</tr>
									<tr>
										<td style="padding:5px;padding-left:10px;background-color:rgb(250,250,250);">
											[LANG_VALUE:usr_020]
										</td>
										<td style="padding:5px;padding-left:10px;background-color:rgb(250,250,250);">
											<input type="password" name="tmpUserPasswdRetype" value="" style="padding:2px;font-size:9pt;font-family:Arial, Helvetica, sans serif;width:350px;border:1px solid rgb(150,150,150);background-color:rgb(255,255,255);" />
										</td>
									</tr>

									<tr>
										<td colspan="2" style="text-align:right;background-color:rgb(230,230,230);">
											<img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" />
										</td>
									</tr>

									<tr>
										<td style="padding:5px;padding-left:10px;background-color:rgb(240,240,240);">
											[LANG_VALUE:usr_021]
										</td>
										<td style="padding:5px;padding-left:10px;background-color:rgb(240,240,240);">
											<input type="text" name="tmpUserCompany" value="[USER_COMPANY]" style="padding:2px;font-size:9pt;font-family:Arial, Helvetica, sans serif;width:350px;border:1px solid rgb(150,150,150);background-color:rgb(255,255,255);" />
										</td>
									</tr>
									<tr>
										<td style="padding:5px;padding-left:10px;background-color:rgb(250,250,250);">
											[LANG_VALUE:usr_011]
										</td>
										<td style="padding:5px;padding-left:10px;background-color:rgb(250,250,250);">
											<input type="text" name="tmpUserName" value="[USER_NAME]" style="padding:2px;font-size:9pt;font-family:Arial, Helvetica, sans serif;width:350px;border:1px solid rgb(150,150,150);background-color:rgb(255,255,255);" />
										</td>
									</tr>
									<tr>
										<td style="padding:5px;padding-left:10px;background-color:rgb(240,240,240);">
											[LANG_VALUE:usr_012]
										</td>
										<td style="padding:5px;padding-left:10px;background-color:rgb(240,240,240);">
											<input type="text" name="tmpUserSurname" value="[USER_SURNAME]" style="padding:2px;font-size:9pt;font-family:Arial, Helvetica, sans serif;width:350px;border:1px solid rgb(150,150,150);background-color:rgb(255,255,255);" />
										</td>
									</tr>
									<tr>
										<td style="padding:5px;padding-left:10px;background-color:rgb(250,250,250);">
											[LANG_VALUE:usr_013]
										</td>
										<td style="padding:5px;padding-left:10px;background-color:rgb(250,250,250);">
											<input type="text" name="tmpUserAddress" value="[USER_ADDRESS]" style="padding:2px;font-size:9pt;font-family:Arial, Helvetica, sans serif;width:350px;border:1px solid rgb(150,150,150);background-color:rgb(255,255,255);" />
										</td>
									</tr>
									<tr>
										<td style="padding:5px;padding-left:10px;background-color:rgb(240,240,240);">
											[LANG_VALUE:usr_014]
										</td>
										<td style="padding:5px;padding-left:10px;background-color:rgb(240,240,240);">
											<input type="text" name="tmpUserPlz" value="[USER_POSTALCODE]" style="padding:2px;font-size:9pt;font-family:Arial, Helvetica, sans serif;width:350px;border:1px solid rgb(150,150,150);background-color:rgb(255,255,255);" />
										</td>
									</tr>
									<tr>
										<td style="padding:5px;padding-left:10px;background-color:rgb(250,250,250);">
											[LANG_VALUE:usr_015]
										</td>
										<td style="padding:5px;padding-left:10px;background-color:rgb(250,250,250);">
											<input type="text" name="tmpUserCity" value="[USER_CITY]" style="padding:2px;font-size:9pt;font-family:Arial, Helvetica, sans serif;width:350px;border:1px solid rgb(150,150,150);background-color:rgb(255,255,255);" />
										</td>
									</tr>
									<tr>
										<td style="padding:5px;padding-left:10px;background-color:rgb(240,240,240);">
											[LANG_VALUE:usr_017]
										</td>
										<td style="padding:5px;padding-left:10px;background-color:rgb(240,240,240);">
											<input type="text" name="tmpUserEmail" value="[USER_EMAIL]" style="padding:2px;font-size:9pt;font-family:Arial, Helvetica, sans serif;width:350px;border:1px solid rgb(150,150,150);background-color:rgb(255,255,255);" />
										</td>
									</tr>
									<tr>
										<td style="padding:5px;padding-left:10px;background-color:rgb(250,250,250);">
											[LANG_VALUE:usr_018]
										</td>
										<td style="padding:5px;padding-left:10px;background-color:rgb(250,250,250);">
											<input type="text" name="tmpUserWeb" value="[USER_INTERNET]" style="padding:2px;font-size:9pt;font-family:Arial, Helvetica, sans serif;width:350px;border:1px solid rgb(150,150,150);background-color:rgb(255,255,255);" />
										</td>
									</tr>
									<!--
									<tr>
										<td style="padding:5px;padding-left:10px;background-color:rgb(250,250,250);">
											[LANG_VALUE:usr_019]
										</td>
										<td style="padding:5px;padding-left:10px;background-color:rgb(250,250,250);">
											<input type="text" name="tmpUserInfo" value="[USER_INFORMATION]" style="padding:2px;font-size:9pt;font-family:Arial, Helvetica, sans serif;width:350px;border:1px solid rgb(150,150,150);background-color:rgb(255,255,255);" />
										</td>
									</tr>
									//-->

									[ACCESS]<tr>
										<td colspan="2" style="text-align:right;background-color:rgb(230,230,230);border:1px solid rgb(150,150,150);border-top-width:0px;">
											<img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" />
										</td>
									</tr>

									<tr>
										<td style="padding:5px;padding-left:10px;background-color:rgb(250,250,250);">
											[LANG_VALUE:usr_032]
										</td>
										<td style="padding:5px;padding-left:10px;background-color:rgb(250,250,250);">
											<input type="checkbox" name="tmpUserAccess[]" [RGT=0:'checked="checked"':''] value="0" /> [LANG_VALUE:usr_033]<br />
											<input type="checkbox" name="tmpUserAccess[]" [RGT=1:'checked="checked"':''] value="1" /> [LANG_VALUE:usr_034]<br />
											<input type="checkbox" name="tmpUserAccess[]" [RGT=2:'checked="checked"':''] value="2" /> [LANG_VALUE:usr_035]<br />
											<input type="checkbox" name="tmpUserAccess[]" [RGT=3:'checked="checked"':''] value="3" /> [LANG_VALUE:usr_036]<br />
											<input type="checkbox" name="tmpUserAccess[]" [RGT=4:'checked="checked"':''] value="4" /> [LANG_VALUE:usr_037]<br />
											<input type="checkbox" name="tmpUserAccess[]" [RGT=5:'checked="checked"':''] value="5" /> [LANG_VALUE:usr_038]<br />
											<input type="checkbox" name="tmpUserAccess[]" [RGT=6:'checked="checked"':''] value="6" /> [LANG_VALUE:usr_039]<br />
											<input type="checkbox" name="tmpUserAccess[]" [RGT=7:'checked="checked"':''] value="7" /> [LANG_VALUE:usr_041]<br />
											<input type="checkbox" name="tmpUserAccess[]" [RGT=8:'checked="checked"':''] value="8" /> [LANG_VALUE:usr_042]<br />
											<input type="checkbox" name="tmpUserAccess[]" [RGT=9:'checked="checked"':''] value="9" /> [LANG_VALUE:usr_043]<br />
											<input type="checkbox" name="tmpUserAccess[]" [RGT=10:'checked="checked"':''] value="10" /> [LANG_VALUE:usr_044]<br />
											<input type="checkbox" name="tmpUserAccess[]" [RGT=11:'checked="checked"':''] value="11" /> [LANG_VALUE:usr_045]<br />
											<input type="checkbox" name="tmpUserAccess[]" [RGT=30:'checked="checked"':''] value="30" /> [LANG_VALUE:usr_063]<br />
										</td>
									</tr>[/ACCESS]

									<tr>
										<td colspan="2" style="text-align:right;background-color:rgb(230,230,230);">
											<input type="button" value="[CONFIRMATION]" onclick="addUser();" />
											&nbsp;&nbsp;
											<input type="button" value="[LANG_VALUE:adm_021]" onclick="history.back();" />
										</td>
									</tr>
								</table>
							</form>
						</fieldset>
						<script>
						<!--
							if ( (document) && (document.MainForm) ) {
								if (document.MainForm.tmpUserUsername) {
									document.MainForm.tmpUserUsername.focus();
								} else {
									document.MainForm.tmpUserPasswd.focus();
								}
							}
						//-->
						</script>
					[/USER_CREATE]

[/LAYOUT]
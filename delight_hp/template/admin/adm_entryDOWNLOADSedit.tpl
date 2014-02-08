[STYLE_INCLUDE]css_boxes.css[/STYLE_INCLUDE]
[LAYOUT]
				[MAIN]
					<table cellpadding="0" cellspacing="0" style="width:560px;">
						<colgroup>
							<col style="" />
						</colgroup>
							<tr>
							<td style="background-color:rgb(230,230,230);font-weight:bold;font-size:11pt;color:rgb(50,50,50);text-align:left;padding:3px;border:1px solid rgb(150,150,150);">
								[LANG_VALUE:adm_031]
							</td>
						</tr>
						<tr>
							<td style="background-color:rgb(252,252,252);color:rgb(50,50,50);text-align:left;padding:3px;border:1px solid rgb(150,150,150);border-top-width:0px;">
								<div style="font-size:10pt;font-weight:bold;margin-left:10px;width:80px;float:left;clear:none;">[LANG_VALUE:adm_048]</div>
								<div style="width:410px;float:left;clear:none;">
									<input type="hidden" name="tmpTitle" />
									<input type="hidden" name="tmpText" value="[SECTION_ID]" />
									[SECTION_LIST]
								</div>
							</td>
						</tr>
						<tr>
							<td style="background-color:rgb(252,252,252);color:rgb(50,50,50);text-align:left;padding:3px;border:1px solid rgb(150,150,150);border-top-width:0px;">
								<div style="font-size:10pt;font-weight:bold;margin-left:10px;width:80px;float:left;clear:none;">[LANG_VALUE:adm_049]</div>
								<div style="width:410px;float:left;clear:none;">
									<table cellpadding="0" cellspacing="0" style="width:100%;table-layout:fixed;">
										<colgroup>
											<col style="width:50%;" />
											<col style="width:50%;" />
										</colgroup>
										[PROGRAM_LIST]
									</table>
								</div>
							</td>
						</tr>
					</table>

				<script type="text/javascript">
					<!--
						function changeFaqSection(sid)
						{
							document.MainForm.textContent.value = sid;
							document.MainForm.submit();
						}
						function applyDownloads()
						{
							document.MainForm.adminSection.value = 'opt';
							document.MainForm.submit();
						}
					//-->
				</script>[/MAIN]

				[PROGRAM_LINE]
					<tr>[TEXT]</tr>
				[/PROGRAM_LINE]
				[PROGRAM_PER_LINE]2[/PROGRAM_PER_LINE]

				[PROGRAM_CLEAN]
					<tr><td colspan="2"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td></tr>
				[/PROGRAM_CLEAN]

				[PROGRAM]
						<td>
							<table cellpadding="0" cellspacing="0" style="width:100%;table-layout:fixed;">
								<colgroup>
									<col style="min-width:2px;width:2px;max-width:2px;" />
									<col style="min-width:30px;width:30pxmax-width:30px;" />
									<col style="min-width:5px;width:5px;max-width:5px;" />
									<col style="min-width:20px;" />
									<col style="min-width:11px;width:11px;max-width:11px;" />
									<col style="width:20px;" />
									<col style="min-width:2px;width:2px;max-width:2px;" />
									<col style="min-width:2px;width:2px;max-width:2px;" />
								</colgroup>
								<tr>
									<td class="lwbh_1"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
									<td class="lwbh_2" colspan="3" style="padding-left:5px;"><h3>[PROGRAM_TITLE:25]</h3></td>
									<td class="lwbh_3"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
									<td class="lwbh_4"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
									<td class="lwbh_5"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
									<td rowspan="6"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
								</tr>
								<tr>
									<td class="lwbc_1"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
									<td class="lwbc_2" colspan="5" style="height:85px;vertical-align:top;">
										<img src="[PROGRAM_ICON]" style="border-width:0px;width:[PROGRAM_ICON_WIDTH]px;height:[PROGRAM_ICON_HEIGHT]px;margin:5px;float:left;" alt="[PROGRAM_FILE]" />
										<strong>[LANG_VALUE:msg_001]</strong><br />
										[PROGRAM_DESCRIPTION:150]
									</td>
									<td class="lwbc_3"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
								</tr>
								<tr>
									<td class="lwbc_1"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
									<td class="lwbc_2" colspan="5" style="height:15px;vertical-align:middle;">
										<strong>[LANG_VALUE:msg_034]</strong> [PROGRAM_SIZE]
									</td>
									<td class="lwbc_3"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
								</tr>
								<!--
								<tr>
									<td class="lwbc_1"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
									<td class="lwbc_2" colspan="5" style="height:15px;text-align:center;vertical-align:bottom;border-top:1px solid rgb(114,191,236);white-space:nowrap;">
										<a href="[DOWNLOAD_LINK]">&#91;&nbsp;[LANG_VALUE:msg_004]&nbsp;&#93;</a>
										&nbsp;
										<a href="javascript:openWindow('[PROGRAM_DETAIL_LINK]',450,350);">&#91;&nbsp;[LANG_VALUE:msg_003]&nbsp;&#93;</a>
									</td>
									<td class="lwbc_3"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
								</tr>
								//-->
								<tr>
									<td class="lwbf_1"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
									<td class="lwbf_2"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
									<td class="lwbf_3"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
									<td class="lwbf_4" colspan="3"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
									<td class="lwbf_5"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
								</tr>
								<tr>
									<td class="lwbb_1"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
									<td class="lwbb_2" colspan="5"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
									<td class="lwbb_3"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
								</tr>
								<tr>
									<td style="height:5px;line-height:1px;font-size:1px;" colspan="7"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
								</tr>
							</table>
						</td>
				[/PROGRAM]

				[PROGRAM_SECTION]
							<script type="text/javascript">
								<!--
									function showSectionList()
									{
										if (document.getElementById('sectionList').style.visibility == 'visible')
											document.getElementById('sectionList').style.visibility = 'hidden';
										else
											document.getElementById('sectionList').style.visibility = 'visible';
									}

									var hide = false;
									function setHide(obj, act)
									{
										var vis = document.getElementById('sectionList').style.visibility;
										if (act)
										{
											if (vis == 'visible')
												window.clearTimeout(hide);
											obj.style.cursor = 'pointer';
										}
										else
										{
											if (vis == 'visible')
												hide = window.setTimeout("doHideSectionList()", 1000);
											obj.style.cursor = 'default';
										}
									}

									function clearHide(act)
									{
										if (act)
											window.clearTimeout(hide);
										else
											hide = window.setTimeout("doHideSectionList()", 1000);
									}

									function doHideSectionList()
									{
										document.getElementById('sectionList').style.visibility = 'hidden';
										window.clearTimeout(hide);
									}
								//-->
							</script>
							<div id="sectionMain" style="border:1px solid rgb(76,140,190);background-color:rgb(174,214,237);padding:3px;" onmouseover="setHide(this,true);" onmouseout="setHide(this,false);" onclick="showSectionList();">
								<strong>[LANG_VALUE:msg_041]</strong>&nbsp;&nbsp;&nbsp;[SELECTED_SECTION_NAME]
							</div>
							<div id="sectionList" onmouseover="clearHide(true);" onmouseout="clearHide(false);" style="visibility:hidden;position:absolute;height:300px;width:450px;overflow:auto;padding:5px;padding-left:15px;border:1px solid rgb(200,200,200);border-top-width:0px;background-color:rgb(250,250,250);">
								<table cellpadding="0" cellspacing="0" style="table-layout:fixed;width:420px;">
									[SECTION]<tr>
										<td style="[IF_SELECTED:'background-color:rgb(220,250,220);':'']vertical-align:top;padding-left:2px;font-size:10pt;">
											[SECTION_DELIMITER] <a href="javascript:changeFaqSection('[SECTION_NUMBER]');">[SECTION_NAME]</a> <em style="font-size:9pt;">([NUMBER_OF_PROGRAMS])</em>
										</td>
									</tr>[/SECTION]
								</table>
							</div>
							<br />
				[/PROGRAM_SECTION]

				[PROGRAM_SECTION_DELIMITER]
					[CLEAN]<img src="[MAIN_DIR]images/section_clean.gif" alt="clean" style="margin:0px;padding:0px;width:10px;height:15px;vertical-align:top;" />[/CLEAN]
					[DOWN]<img src="[MAIN_DIR]images/section_down.gif" alt="down" style="margin:0px;padding:0px;width:10px;height:15px;vertical-align:top;" />[/DOWN]
					[ENTRY]<img src="[MAIN_DIR]images/section_entry.gif" alt="entry" style="margin:0px;padding:0px;width:10px;height:15px;vertical-align:top;" />[/ENTRY]
					[LAST]<img src="[MAIN_DIR]images/section_last.gif" alt="last" style="margin:0px;padding:0px;width:10px;height:15px;vertical-align:top;" />[/LAST]
				[/PROGRAM_SECTION_DELIMITER]

[/LAYOUT]


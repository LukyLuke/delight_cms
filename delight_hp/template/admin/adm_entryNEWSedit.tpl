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
											<col style="width:100%;" />
										</colgroup>
										<tr><td>[NEWS_LIST]</td></tr>
									</table>
								</div>
							</td>
						</tr>
					</table>

				<script type="text/javascript">
					<!--
						function changeNewsSection(sid) {
							document.MainForm.textContent.value = sid;
							document.MainForm.submit();
						}
						function applyDownloads() {
							document.MainForm.adminSection.value = 'opt';
							document.MainForm.submit();
						}
					//-->
				</script>[/MAIN]

				[NEWS_ENTRY]
						<table cellpadding="0" cellspacing="0" style="width:100%;table-layout:fixed;margin-bottom:10px;">
							<colgroup>
								<col style="min-width:5px;width:5px;max-width:5px;" />
								<col style="min-width:20px;width:20px;max-width:20px;" />
								<col style="min-width:100px;" />
								<col style="min-width:5px;width:5px;max-width:5px;" />
							</colgroup>
							<tr>
								<td class="det_tab_h1"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
								<td class="det_tab_h2" colspan="2"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
								<td class="det_tab_h3"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
							</tr>
							<tr>
								<td class="det_tab_t1"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
								<td class="det_tab_t2" colspan="2" style="text-align:center;">[NEWS_DATE_EXTENDED]</td>
								<td class="det_tab_t3"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
							</tr>
							<tr>
								<td class="det_tab_c1"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
								<td class="det_tab_c2_2" colspan="2" style="padding:2px;padding-left:5px;"><h3>[NEWS_TITLE]</h3></td>
								<td class="det_tab_c3"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
							</tr>
							<tr>
								<td class="det_tab_c1"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
								<td class="det_tab_c2_1"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
								<td class="det_tab_c2_1">[NEWS_TEXT]</td>
								<td class="det_tab_c3"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
							</tr>
							<tr>
								<td class="det_tab_f1"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
								<td class="det_tab_f2" colspan="2"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
								<td class="det_tab_f3"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
							</tr>
						</table>
				[/NEWS_ENTRY]

				[NEWS_SECTION]
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
							<div id="sectionList" onmouseover="clearHide(true);" onmouseout="clearHide(false);" style="visibility:hidden;position:absolute;height:200px;width:450px;overflow:auto;padding:5px;padding-left:15px;border:1px solid rgb(76,140,190);border-top-width:0px;background-color:rgb(250,250,250);">
								<table cellpadding="0" cellspacing="0" style="table-layout:fixed;width:420px;">
									[SECTION]<tr>
										<td style="[IF_SELECTED:'background-color:rgb(220,250,220);':'']vertical-align:top;padding-left:2px;font-size:10pt;">
											[SECTION_DELIMITER] <a href="javascript:changeNewsSection('[SECTION_NUMBER]');">[SECTION_NAME]</a> <em style="font-size:9pt;">([NUMBER_OF_PROGRAMS])</em>
										</td>
									</tr>[/SECTION]
								</table>
							</div>
							<br />
				[/NEWS_SECTION]

				[NEWS_SECTION_DELIMITER]
					[CLEAN]<img src="[MAIN_DIR]images/section_clean.gif" alt="clean" style="margin:0px;padding:0px;width:10px;height:15px;vertical-align:top;" />[/CLEAN]
					[DOWN]<img src="[MAIN_DIR]images/section_down.gif" alt="down" style="margin:0px;padding:0px;width:10px;height:15px;vertical-align:top;" />[/DOWN]
					[ENTRY]<img src="[MAIN_DIR]images/section_entry.gif" alt="entry" style="margin:0px;padding:0px;width:10px;height:15px;vertical-align:top;" />[/ENTRY]
					[LAST]<img src="[MAIN_DIR]images/section_last.gif" alt="last" style="margin:0px;padding:0px;width:10px;height:15px;vertical-align:top;" />[/LAST]
				[/NEWS_SECTION_DELIMITER]

[/LAYOUT]
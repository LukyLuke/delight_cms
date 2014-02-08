[STYLE_INCLUDE]css_boxes.css[/STYLE_INCLUDE]

[LAYOUT]

				[MAIN]
					<table cellpadding="0" cellspacing="0" style="width:560px;">
						<colgroup>
							<col style="" />
						</colgroup>
							<tr>
							<td style="background-color:rgb(230,230,230);font-weight:bold;font-size:11pt;color:rgb(50,50,50);text-align:left;padding:3px;border:1px solid rgb(150,150,150);">
								[LANG_VALUE:adm_092]
							</td>
						</tr>
						<tr>
							<td style="background-color:rgb(252,252,252);color:rgb(50,50,50);text-align:left;padding:3px;border:1px solid rgb(150,150,150);border-top-width:0px;">
								<div style="font-size:10pt;font-weight:bold;margin-left:10px;width:80px;float:left;clear:none;">[LANG_VALUE:adm_093]</div>
								<div style="width:410px;float:left;clear:none;">
									<input type="hidden" name="tmpTitle" />
									<input type="hidden" name="tmpText" value="[SECTION_ID]" />
									[SECTION_LIST]
								</div>
							</td>
						</tr>
						<tr>
							<td style="background-color:rgb(252,252,252);color:rgb(50,50,50);text-align:left;padding:3px;border:1px solid rgb(150,150,150);border-top-width:0px;">
								<div style="font-size:10pt;font-weight:bold;margin-left:10px;width:80px;float:left;clear:none;">[LANG_VALUE:adm_094]</div>
								<div style="width:410px;float:left;clear:none;">
									<table cellpadding="0" cellspacing="0" style="width:100%;table-layout:fixed;">
										<colgroup>
											<col style="width:50%;" />
											<col style="width:50%;" />
										</colgroup>
										[THUMBNAIL_LIST]
									</table>
								</div>
							</td>
						</tr>
					</table>

				<script type="text/javascript">
					<!--
						function changeImgSection(sid) {
							document.MainForm.textContent.value = sid;
							document.MainForm.submit();
						}
						function applyScreenshots() {
							document.MainForm.adminSection.value = 'opt';
							document.MainForm.submit();
						}
					//-->
				</script>[/MAIN]

				[THUMBNAIL_PER_LINE]2[/THUMBNAIL_PER_LINE]

				[THUMBNAIL_LINE]
									<tr>[TEXT]</tr>
				[/THUMBNAIL_LINE]

				[THUMBNAIL_CLEAN]
										<td><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
				[/THUMBNAIL_CLEAN]

				[THUMBNAIL]
          <td>
												<table cellpadding="0" cellspacing="0" style="width:100%;">
													<colgroup>
														<col style="" />
														<col style="" />
														<col style="" />
														<col style="" />
														<col style="width:15px;" />
													</colgroup>
													<tr>
														<td colspan="4" style="padding-left:5px;background-color:rgb(230,230,230);border:1px solid rgb(150,150,150);border-bottom-width:0px;">
															<h3 style="margin:3px;">Image Nr. [IMAGE_NUMBER]</h3>
														</td>
														<td rowspan="6">&nbsp;</td>
													</tr>
													<tr>
														<td colspan="4" style="padding:0px;height:180px;vertical-align:middle;text-align:center;border-left:1px solid rgb(150,150,150);border-right:1px solid rgb(150,150,150);">
															<img src="[IMAGE_SRC]" style="border-width:0px;width:[WIDTH]px;height:[HEIGHT]px;margin:5px;" alt="[IMAGE_TITLE]" />
														</td>
													</tr>
													<tr>
														<td colspan="4" style="padding-left:15px;height:15px;vertical-align:bottom;border-left:1px solid rgb(150,150,150);border-right:1px solid rgb(150,150,150);">
															[IMAGE_DESCRIPTION:100]<br /><br />
															<strong>[LANG_VALUE:msg_007]</strong> [REAL_WIDTH]x[REAL_HEIGHT]<br />
															<strong>[LANG_VALUE:msg_008]</strong> [REAL_SIZE]
														</td>
													</tr>
													<tr>
														<td colspan="4" style="height:5px;line-height:1px;font-size:1px;background-color:rgb(230,230,230);border:1px solid rgb(150,150,150);border-top-width:0px;">&nbsp;</td>
													</tr>
													<tr>
														<td colspan="4" style="height:15px;line-height:1px;font-size:1px;">&nbsp;</td>
													</tr>
												</table>
											</td>
				[/THUMBNAIL]

				[THUMBNAIL_SECTION]
							<script type="text/javascript">
								<!--
									function showSectionList() {
										if (document.getElementById('sectionList').style.visibility == 'visible') {
											document.getElementById('sectionList').style.visibility = 'hidden';
										} else {
											document.getElementById('sectionList').style.visibility = 'visible';
										}
									}

									var hide = false;
									function setHide(obj, act) {
										var vis = document.getElementById('sectionList').style.visibility;
										if (act) {
											if (vis == 'visible') {
												window.clearTimeout(hide);
											}
											obj.style.cursor = 'pointer';
										} else {
											if (vis == 'visible') {
												hide = window.setTimeout("doHideSectionList()", 1000);
											}
											obj.style.cursor = 'default';
										}
									}

									function clearHide(act) {
										if (act) {
											window.clearTimeout(hide);
										} else {
											hide = window.setTimeout("doHideSectionList()", 1000);
										}
									}

									function doHideSectionList() {
										document.getElementById('sectionList').style.visibility = 'hidden';
										window.clearTimeout(hide);
									}
								//-->
							</script>
							<div id="sectionMain" style="border:1px solid rgb(76,140,190);background-color:rgb(174,214,237);padding:3px;" onmouseover="setHide(this,true);" onmouseout="setHide(this,false);" onclick="showSectionList();">
								<strong>[LANG_VALUE:adm_095]</strong>&nbsp;&nbsp;&nbsp;[SELECTED_SECTION_NAME]
							</div>
							<div id="sectionList" onmouseover="clearHide(true);" onmouseout="clearHide(false);" style="visibility:hidden;position:absolute;height:300px;width:380px;overflow:auto;padding:5px;padding-left:15px;border:1px solid rgb(200,200,200);border-top-width:0px;background-color:rgb(250,250,250);">
								<table cellpadding="0" cellspacing="0" style="table-layout:fixed;width:350px;">
									[SECTION]<tr>
										<td style="[IF_SELECTED:'background-color:rgb(220,250,220);':'']vertical-align:top;padding-left:2px;font-size:10pt;">
											[SECTION_DELIMITER] <a href="javascript:changeImgSection('[SECTION_NUMBER]');">[SECTION_NAME]</a> <em style="font-size:9pt;">([NUMBER_OF_PROGRAMS])</em>
										</td>
									</tr>[/SECTION]
								</table>
							</div>
							<br />
				[/THUMBNAIL_SECTION]

				[THUMBNAIL_SECTION_DELIMITER]
					[CLEAN]<img src="[MAIN_DIR]images/section_clean.gif" alt="clean" style="margin:0px;padding:0px;width:10px;height:15px;vertical-align:top;" />[/CLEAN]
					[DOWN]<img src="[MAIN_DIR]images/section_down.gif" alt="down" style="margin:0px;padding:0px;width:10px;height:15px;vertical-align:top;" />[/DOWN]
					[ENTRY]<img src="[MAIN_DIR]images/section_entry.gif" alt="entry" style="margin:0px;padding:0px;width:10px;height:15px;vertical-align:top;" />[/ENTRY]
					[LAST]<img src="[MAIN_DIR]images/section_last.gif" alt="last" style="margin:0px;padding:0px;width:10px;height:15px;vertical-align:top;" />[/LAST]
				[/THUMBNAIL_SECTION_DELIMITER]

[/LAYOUT]
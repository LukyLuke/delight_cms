[STYLE_INCLUDE]admin/css_adminStatistics.css[/STYLE_INCLUDE]
[LAYOUT]
				[BASE]
					<div>
						<table cellpadding="0" cellspacing="0" style="width:560px;">
							<colgroup>
								<col style="" />
								<col style="" />
							</colgroup>
								<tr>
								<td style="background-color:rgb(230,230,230);font-weight:bold;font-size:11pt;color:rgb(50,50,50);text-align:left;padding:3px;border:1px solid rgb(150,150,150);border-bottom-width:0px;border-right-width:0px;">
									[LANG_VALUE:log_001]: [STATISTIC_DATE]
								</td>
								<td style="background-color:rgb(230,230,230);font-weight:bold;font-size:11pt;color:rgb(50,50,50);text-align:right;padding:3px;border:1px solid rgb(150,150,150);border-bottom-width:0px;border-left-width:0px;">
									[LANG_VALUE:log_002]&nbsp;&nbsp;<img src="[MAIN_DIR]images/admin/down.gif" style="width:16px;height:16px;border-width:0px;vertical-align:bottom;" alt="selectedate" onmouseover="this.style.cursor='pointer';showDateChooser();" onmouseout="hideDateChooser();this.style.cursor='default';" />
								</td>
							</tr>
							<tr>
								<td colspan="2" style="padding:10px;border:1px solid rgb(150,150,150);border-bottom-width:0px;background-color:rgb(250,250,250);">
									[STATISTIC_PLUGIN_INSERT]
								</td>
							</tr>
							<tr>
								<td colspan="2" style="text-align:center;padding:10px;border:1px solid rgb(150,150,150);border-bottom-width:0px;background-color:rgb(230,230,230);">
									<img src="[SHOW_GRAPH:Circle3D]" style="width:350px;height:200px;" alt="graph" />
								</td>
							</tr>
							<tr>
								<td colspan="2" style="padding:10px;border:1px solid rgb(150,150,150);border-bottom-width:0px;background-color:rgb(250,250,250);">
									[STATISTIC_PLUGIN_CONTENT]
								</td>
							</tr>
							<tr>
								<td colspan="2" style="background-color:rgb(230,230,230);border:1px solid rgb(150,150,150);">&nbsp;</td>
							</tr>
						</table>
					</div>

					<div id="div_dateChooser" style="position:absolute;top:175px;left:550px;visibility:hidden;width:230px;height:200px;background-color:transparent;" onmouseover="showDateChooser();" onmouseout="hideDateChooser();">
						[DATE_CHOOSER_INSERT]
					</div>

					<script type="text/javascript">
						<!--
							var showDate=false;
							var dateChooser = document.getElementById('div_dateChooser');
							var dayCol = '';
							var view = '[VIEW_TYPE]';

							function showDateChooser()
							{
								window.clearTimeout(showDate);
								showDate = false;
								dateChooser.style.visibility = 'visible';
							}
							function hideDateChooser()
							{
								showDate = window.setTimeout("clearDateChooser()", 1000);
							}

							function clearDateChooser()
							{
								window.clearTimeout(showDate);
								showDate = false;
								dateChooser.style.visibility = 'hidden';
							}

							function chageChooserDay(obj, act)
							{
								if (parseInt(act) > 0)
								{
									obj.style.cursor = 'pointer';
									dayCol = obj.style.backgroundColor;
									obj.style.backgroundColor = 'rgb(255,224,39)';
								}
								else
								{
									obj.style.cursor = 'default';
									obj.style.backgroundColor = dayCol;
									dayCol = '';
								}
							}

							function changeStatistic(obj, act)
							{
								if (parseInt(act) > 0)
								{
									obj.style.cursor = 'pointer';
									obj.style.backgroundColor = 'rgb(220,220,220)';
									obj.style.borderColor = 'rgb(180,180,180)';
								}
								else
								{
									obj.style.cursor = 'default';
									obj.style.backgroundColor = 'transparent';
									obj.style.borderColor = 'rgb(250,250,250)';
								}
							}

							function setView(str)
							{
								view = str;
								document.getElementById('viewY').style.backgroundColor = 'rgb(250,250,250)';
								document.getElementById('viewM').style.backgroundColor = 'rgb(250,250,250)';
								document.getElementById('viewD').style.backgroundColor = 'rgb(250,250,250)';
								document.getElementById('view' + view.toUpperCase()).style.backgroundColor = 'rgb(80,200,80)';
								dayCol = 'rgb(80,200,80)';
							}

							function changeLocation(lnk)
							{
								lnk = lnk.replace(/amp;/,'');
								lnk = lnk.replace(/textTitle=/, 'textTitle=' + view + ':');
								window.location.href = lnk;
							}

							function changeSort(fld)
							{
								lnk = '[STATISTIC_SORT_LINK]' + fld;
								window.location.href = lnk.replace(/amp;/gi, '');
							}

							setView(view);
						//-->
					</script>
				[/BASE]

				[DATE_CHOOSER]
						<table cellpadding="0" cellspacing="0" style="width:100%;table-layout:fixed;">
							<colgroup>
								<col style="width:3px;" />   <!-- border     //-->
								<col style="" />             <!-- Weeknumber //-->
								<col style="width:28px;" />  <!-- Monday     //-->
								<col style="width:28px;" />  <!-- Thuesday   //-->
								<col style="width:28px;" />  <!-- Wednesday  //-->
								<col style="width:28px;" />  <!-- Thursday   //-->
								<col style="width:28px;" />  <!-- Friday     //-->
								<col style="width:28px;" />  <!-- Saturday   //-->
								<col style="width:28px;" />  <!-- Sunday     //-->
								<col style="width:3px;" />   <!-- border     //-->
							</colgroup>
							<tr>
								<td class="dch_11">&nbsp;</td>
								<td class="dch_12" colspan="8">&nbsp;</td>
								<td class="dch_13">&nbsp;</td>
							</tr>

							<tr>
								<td class="dch_21"  style="height:20px;">&nbsp;</td>
								<td class="dch_22h" style="height:20px;" title="[LANG_VALUE:log_005]" onclick="window.location.href='[LINK_YEAR_BACK]';" onmouseover="chageChooserDay(this, 1);" onmouseout="chageChooserDay(this, 0);">&#060;&#060;</td>
								<td class="dch_22h" style="height:20px;" title="[LANG_VALUE:log_003]" onclick="window.location.href='[LINK_MONTH_BACK]';" onmouseover="chageChooserDay(this, 1);" onmouseout="chageChooserDay(this, 0);">&#060;</td>
								<td class="dch_22h" colspan="2" style="height:20px;font-weight:bold;">[SELECTED_MONTH]</td>
								<td class="dch_22h" colspan="2" style="height:20px;font-weight:bold;">[SELECTED_YEAR]</td>
								<td class="dch_22h" style="height:20px;" title="[LANG_VALUE:log_004]" onclick="window.location.href='[LINK_MONTH_FORWARD]';" onmouseover="chageChooserDay(this, 1);" onmouseout="chageChooserDay(this, 0);">&#062;</td>
								<td class="dch_22h" style="height:20px;" title="[LANG_VALUE:log_006]" onclick="window.location.href='[LINK_YEAR_FORWARD]';" onmouseover="chageChooserDay(this, 1);" onmouseout="chageChooserDay(this, 0);">&#062;&#062;</td>
								<td class="dch_23"  style="height:20px;">&nbsp;</td>
							</tr>

							<tr>
								<td class="dch_21"  style="height:20px;">&nbsp;</td>
								<td class="dch_22h" style="height:20px;background-color:rgb(250,250,250);border-top:1px solid rgb(200,200,200);border-bottom:1px solid rgb(200,200,200);">&nbsp;</td>
								<td class="dch_22h" style="height:20px;text-align:center;border-top:1px solid rgb(200,200,200);border-bottom:1px solid rgb(200,200,200);" id="viewY" colspan="2" onclick="setView('y');" onmouseover="chageChooserDay(this, 1);" onmouseout="chageChooserDay(this, 0);">
									[LANG_VALUE:log_007]
								</td>
								<td class="dch_22h" style="height:20px;text-align:center;border-top:1px solid rgb(200,200,200);border-bottom:1px solid rgb(200,200,200);" id="viewM" colspan="3" onclick="setView('m');" onmouseover="chageChooserDay(this, 1);" onmouseout="chageChooserDay(this, 0);">
									[LANG_VALUE:log_008]
								</td>
								<td class="dch_22h" style="height:20px;text-align:center;border-top:1px solid rgb(200,200,200);border-bottom:1px solid rgb(200,200,200);" id="viewD" colspan="2" onclick="setView('d');" onmouseover="chageChooserDay(this, 1);" onmouseout="chageChooserDay(this, 0);">
									[LANG_VALUE:log_009]
								</td>
								<td class="dch_23"  style="height:20px;">&nbsp;</td>
							</tr>

							<tr>
								<td class="dch_21"  style="height:20px;">&nbsp;</td>
								<td class="dch_22h" style="height:20px;">&nbsp;</td>
								<td class="dch_22h" style="height:20px;border-bottom:1px solid rgb(200,200,200);font-weight:bold;">Mo</td>
								<td class="dch_22h" style="height:20px;border-bottom:1px solid rgb(200,200,200);font-weight:bold;">Di</td>
								<td class="dch_22h" style="height:20px;border-bottom:1px solid rgb(200,200,200);font-weight:bold;">Mi</td>
								<td class="dch_22h" style="height:20px;border-bottom:1px solid rgb(200,200,200);font-weight:bold;">Do</td>
								<td class="dch_22h" style="height:20px;border-bottom:1px solid rgb(200,200,200);font-weight:bold;">Fr</td>
								<td class="dch_22h" style="height:20px;border-bottom:1px solid rgb(200,200,200);font-weight:bold;">Sa</td>
								<td class="dch_22h" style="height:20px;border-bottom:1px solid rgb(200,200,200);font-weight:bold;">So</td>
								<td class="dch_23"  style="height:20px;">&nbsp;</td>
							</tr>

							<tr>
								<td class="dch_21">&nbsp;</td>
								<td class="dch_22h" style="border-right:1px solid rgb(200,200,200);color:rgb(150,150,150);">[WEEK_NUMBER_1]</td>
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								<td class="dch_23">&nbsp;</td>
							</tr>
							<tr>
								<td class="dch_21">&nbsp;</td>
								<td class="dch_22h" style="border-right:1px solid rgb(200,200,200);color:rgb(150,150,150);">[WEEK_NUMBER_2]</td>
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								<td class="dch_23">&nbsp;</td>
							</tr>
							<tr>
								<td class="dch_21">&nbsp;</td>
								<td class="dch_22h" style="border-right:1px solid rgb(200,200,200);color:rgb(150,150,150);">[WEEK_NUMBER_3]</td>
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								<td class="dch_23">&nbsp;</td>
							</tr>
							<tr>
								<td class="dch_21">&nbsp;</td>
								<td class="dch_22h" style="border-right:1px solid rgb(200,200,200);color:rgb(150,150,150);">[WEEK_NUMBER_4]</td>
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								<td class="dch_23">&nbsp;</td>
							</tr>
							<tr>
								<td class="dch_21">&nbsp;</td>
								<td class="dch_22h" style="border-right:1px solid rgb(200,200,200);color:rgb(150,150,150);">[WEEK_NUMBER_5]</td>
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								<td class="dch_23">&nbsp;</td>
							</tr>
							<tr>
								<td class="dch_21">&nbsp;</td>
								<td class="dch_22h" style="border-right:1px solid rgb(200,200,200);color:rgb(150,150,150);">[WEEK_NUMBER_6]</td>
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								[DAY_INSERT]
								<td class="dch_23">&nbsp;</td>
							</tr>

							<tr>
								<td class="dch_31">&nbsp;</td>
								<td class="dch_32" colspan="8">&nbsp;</td>
								<td class="dch_33">&nbsp;</td>
							</tr>
						</table>
				[/DATE_CHOOSER]

				[DATE_CHOOSER_DAY]
					<td class="dch_22[IF_SELECTED:'sel':'']" style="[IF_NOW:'border:1px solid rgb(150,80,80);':''][IF_CURRENT_MONTH:'':'color:rgb(180,180,180);']" onclick="changeLocation('[LINK_DAY]');" onmouseover="chageChooserDay(this, 1);" onmouseout="chageChooserDay(this, 0);">[DAY]</td>
				[/DATE_CHOOSER_DAY]

				[STATISTIC_PLUGIN]
					<table cellpadding="0" cellspacing="0" style="width:100%;table-layout:fixed;">
						[PLUGIN_LINE]<tr>
							[PLUGINS:5]
						</tr>
						<tr><td style="height:5px;line-height:1px;font-size:1px;" colspan="15">&nbsp;</td></tr>[/PLUGIN_LINE]
					</table>
				[/STATISTIC_PLUGIN]

				[STATISTIC_PLUGIN_ENTRY]
					<td>&nbsp;</td>
					<td style="width:80px;text-align:center;vertical-align:middle;height:50px;border:1px solid rgb(250,250,250);" onmouseover="changeStatistic(this, 1);" onmouseout="changeStatistic(this, 0);" onclick="changeLocation('[PLUGIN_LINK]');">
						<img src="[MAIN_DIR]images/[PLUGIN_IMAGE]" alt="[PLUGIN_NAME]" style="width:24px;height:24px;">
						<br />[IF_SELECTED:'<strong>':''][PLUGIN_NAME][IF_SELECTED:'</strong>':'']
					</td>
					<td>&nbsp;</td>
				[/STATISTIC_PLUGIN_ENTRY]

				[STATISTIC_PLUGIN_CLEAR]
					<td>&nbsp;</td>
					<td style="width:80px;">&nbsp;</td>
					<td>&nbsp;</td>
				[/STATISTIC_PLUGIN_CLEAR]

[/LAYOUT]
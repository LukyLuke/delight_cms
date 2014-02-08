[SUBMENU_MAIN]
			<script type="text/javascript">
				<!--
					function showMenu(men)
					{
						document.getElementsByName('tmpIntLnk')[0].value = men;
						document.getElementsByName('tmpIntLnkValue')[0].firstChild.nodeValue = 'selected: ' + document.getElementsByName('l' + men)[0].firstChild.nodeValue;
						var men = 'm' + men;
						var mList = document.getElementsByName(men);
						for (var i = 0; i < mList.length; i++)
						{
							if (mList[i].style.visibility == 'visible')
							{
								var mChild = mList[i].firstChild;
								while (mChild != null)
								{
									if ( ((mChild.nodeName).toLowerCase() == 'div') && ((mChild.getAttribute('name')).substring(0,1) == 'm'))
									{
										mChild.style.visibility = 'hidden';
										mChild.style.position = 'absolute';
									}
									mChild = mChild.nextSibling;
								}
								mList[i].style.visibility = 'hidden';
								mList[i].style.position = 'absolute';
							}
							else
							{
								mList[i].style.visibility = 'visible';
								mList[i].style.position = 'static';
							}
						}
					}
				//-->
			</script>

		[SUBMENU_ENTRIES]

[/SUBMENU_MAIN]

[color:RED_bg:255:114]
[color:GREEN_bg:255:191]
[color:BLUE_bg:255:236]

[color:RED_bg1:0:114]
[color:GREEN_bg1:111:191]
[color:BLUE_bg1:153:236]

[color:RED_bg2:255:161]
[color:GREEN_bg2:255:212]
[color:BLUE_bg2:255:242]

[MENU_1_admin]
			<table cellpadding="0" cellspacing="0" style="width:130px;border-left:3px solid rgb(0,111,153);padding-top:3px;">
				<colgroup>
					<col style="width:15px;win-width:15px;" />
					<col style="width:30px;win-width:30px;" />
					<col style="width:82px;win-width:82px;" />
					<col style="width:3px;win-width:3px;" />
				</colgroup>
				<tr>
					<td class="subm_sub_op_1_2" style="font-size:1px;line-height:1px;height:2px;" colspan="3"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
					<td class="subm_sub_op_1_4" style="font-size:1px;line-height:1px;height:2px;"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
				</tr>
				<tr>
					<td class="subm_sub_cl_menu"  style="vertical-align:middle;background-color:rgb([RED_bg],[GREEN_bg],[BLUE_bg]);" colspan="3">
						<a class="subm_c" name="l[SUBMENU_ID_ADMIN]" href="javascript:showMenu('[SUBMENU_ID_ADMIN]');">[MENU_TITLE]</a>
					</td>
					<td class="subm_sub_op_2_4"   style=""><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
				</tr>
				<tr>
					<td class="subm_sub_op_3_2" style="font-size:1px;line-height:1px;height:2px;" colspan="3"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
					<td class="subm_sub_op_3_4" style="font-size:1px;line-height:1px;height:2px;"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
				</tr>
				<tr>
					<td class="subm_sub_op_sub" style="vertical-align:middle;padding-left:5px;padding-right:0px;">
						<span style="font-size:11px;font-weight:bold;">&nbsp;</span>
					</td>
					<td name="menuContainer" class="subm_sub_op_sub" style="background-color:rgb([RED_bg1],[GREEN_bg1],[BLUE_bg1]);" colspan="2">

				[SUBMENU:0]
						<div name="m[SUBMENU_PARENT_ID_ADMIN]" style="visibility:hidden;position:absolute;color:rgb(0,111,153);background-color:rgb([RED_bg2],[GREEN_bg2],[BLUE_bg2]);padding-left:[LEVEL_INSERT:"1":"5":"m"]px;">
							<a class="subm_s" name="l[SUBMENU_ID_ADMIN]" href="javascript:showMenu('[SUBMENU_ID_ADMIN]');">[SUBMENU_TITLE]</a>
							[SUBMENU_ENTRIES]
						</div>
				[/SUBMENU:0]

					</td>
					<td class="subm_sub_op_4_4" style="font-size:1px;line-height:1px;"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
				</tr>
				<tr>
					<td class="subm_sub_op_5_2" style="font-size:1px;line-height:1px;height:5px;" colspan="2"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
					<td class="subm_sub_op_5_3" style="font-size:1px;line-height:1px;height:5px;"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
					<td class="subm_sub_op_5_4" style="font-size:1px;line-height:1px;height:5px;"><img src="[MAIN_DIR]images/blank.gif" style="width:1px;height:1px;" alt="" /></td>
				</tr>
			</table>
[/MENU_1_admin]


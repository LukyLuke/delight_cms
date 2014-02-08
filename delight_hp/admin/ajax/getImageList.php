<?php
	// Include som default files
	require_once('./config.php');

	function printSections($sel, $parent, $cnt=0, $lev=1) {
		global $DB, $id;
		// Get all sections with parent $parent
		$sql = "SELECT * FROM ".$DB->Table('ims')." WHERE ".$DB->Field('ims','parent')."='".$parent."' ORDER BY ".$DB->Field('ims','text')." ASC";
		$res = $DB->ReturnQueryResult($sql);
		if ($res) {
			$lev++;
			while ($row = mysql_fetch_assoc($res)) {
				$curId = $row[$DB->FieldOnly('ims','id')];
				$curText = $row[$DB->FieldOnly('ims','text')];
				if ($curId == $sel) {
					$bgCol = '#27B900';
				} else if ($cnt%2 == 0) {
					$bgCol = '#F0F0F0';
				} else {
					$bgCol = '#FEFEFE';
				}
				$pad = ($lev * 10);
				$click = 'delightWebRequestOBJECT.Init();delightWebRequestOBJECT.getData(\'id='.$id.',data=img,sec='.$curId.'\', document.getElementById(\'delightWebEditImageLoadingContainer\'));';
				echo '<tr><td style="padding-left:'.$pad.'px;background-color:'.$bgCol.';" onclick="'.$click.'">'.$curText.'</td></tr>';
				printSections($sel, $curId, ++$cnt, $lev);
			}
			$lev--;
		}
	}

	if ( ($id != null) || ($secid != null) ) {
		$sql = "SELECT * FROM ".$DB->Table('img')." WHERE ".$DB->Field('img','id')."='".$id."'";
		$res = $DB->ReturnQueryResult($sql);
		if ($res) {
			// Get selected Image
			$imgSelected = mysql_fetch_assoc($res);
			if ($secid == null) {
				$imgSection = $imgSelected[$DB->FieldOnly('img','section')];
			} else {
				$imgSection = $secid;
			}
			$DB->FreeDatabaseResult($res);

			// Get all Images from section in which the image is
			$sql = "SELECT * FROM ".$DB->Table('img')." WHERE ".$DB->Field('img','section')."='".$imgSection."'";
			$res = $DB->ReturnQueryResult($sql);
			if ($res) {
				// Create the ButtonClick-JS
				$btnClick  = 'var t=document.getElementsByTagName(\'td\');';
				$btnClick .= 'for(var x=0;x<t.length;x++)';
				$btnClick .= 'if(t[x].id.substring(0,2)==\'si\')t[x].style.backgroundColor=\'#F0F0F0\';';
				$btnClick .= 'document.getElementsByName(\'imageSelected\')[0].value=i;';
				$btnClick .= 'document.getElementById(\'si\'+i).style.backgroundColor=\'#27B900\';';

				$showImgClick  = 'var i=document.getElementById(\'it\');';
				$showImgClick .= 'var s=document.getElementById(\'st\');';
				$showImgClick .= 's.style.visibility=\'hidden\';';
				$showImgClick .= 's.style.display=\'none\';';
				$showImgClick .= 'i.style.visibility=\'visible\';';
				$showImgClick .= 'i.style.display=\'inline\';';
				$showSecClick .= 'i.style.width=\'99%\';';

				$showSecClick  = 'var i=document.getElementById(\'it\');';
				$showSecClick .= 'var s=document.getElementById(\'st\');';
				$showSecClick .= 'i.style.visibility=\'hidden\';';
				$showSecClick .= 'i.style.display=\'none\';';
				$showSecClick .= 's.style.visibility=\'visible\';';
				$showSecClick .= 's.style.display=\'inline\';';
				$showSecClick .= 's.style.width=\'99%\';';

				// Show the SectionList
				echo '<table cellpadding="0" cellspacing="0" style="font-size:9pt;border-width:0px;width:99%;border-collapse:collapse;"><tr>';
				echo '<td style="border:1px solid #494949;padding:2px;background-color:#FEFEFE;border-bottom-width:0px;" onclick="'.$showImgClick.'">Images</td>';
				echo '<td style="border:1px solid #494949;padding:2px;background-color:#FEFEFE;border-bottom-width:0px;" onclick="'.$showSecClick.'">Sections</td>';
				echo '<td style="width:99%;">&nbsp;</td></tr></table>';

				// show the Section-Table
				echo '<table id="st" cellpadding="0" cellspacing="0" style="border:1px solid #494949;width:99%;border-collapse:collapse;visibility:hidden;display:none;">';
				printSections($imgSection, 0);
				echo '</table>';

				// print out the Image-Table
				echo '<table id="it" cellpadding="0" cellspacing="0" style="border-width:0px;width:99%;border-collapse:collapse;visibility:visible;display:inline;">';

				// Get all images
				$cnt = 0;
				while ($row = mysql_fetch_assoc($res)) {
					// get the img-source, ID and BackgroundColor
					$img = 'http://'.$_SERVER['SERVER_NAME'].'/delight_hp/images/page/small/'.$row[$DB->FieldOnly('img','image')];
					$imgId = $row[$DB->FieldOnly('img','id')];
					$bgCol = ($imgId == $id) ? '#27B900' : '#F0F0F0';

					// Show a TR
					if ($cnt%2 == 0) {
						echo '<tr>';
					}

					// Show the Image itself in a TD
					echo '<td id="si'.$imgId.'" style="background-color:'.$bgCol.';text-align:center;padding:5px;border:1px solid #494949;">';
					echo '<img src="'.$img.'" style="width:100px;" /><br />';
					echo '<input style="margin-top:5px;" type="button" value="select image" onclick="var i=\''.$imgId.'\';'.$btnClick.'" />';
					echo '</td>';

					// if twoo rows are printed, show the end-TR
					if (($cnt%2 != 0) && ($cnt > 0)) {
						echo '</tr>';
					}
					$cnt++;
				}

				// Finally close the table and free the DB-Result
				echo '</table>';
				$DB->FreeDatabaseResult($res);
			}
		}
	} else {
		echo "Request failure.";
	}
?>
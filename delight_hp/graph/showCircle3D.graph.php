<?php

if (!(isset($_GET['stat'])) || (strlen(trim($_GET['stat'])) <= 0)) {
	$val = base64_encode("4:undefined;3:call;2:read;1:manual");
} else {
	$val = trim($_GET['stat']);
}

$_baseWidth = 0;
if (isset($_GET['width'])) {
	$_baseWidth = (int)$_GET['width'];
}
$_baseHeight = 0;
if (isset($_GET['height'])) {
	$_baseHeight = (int)$_GET['height'];
}

// some base variables
$_baseWidth = $_baseWidth <= 0 ? 579 : $_baseWidth;
$_baseHeight = $_baseHeight <= 0 ? 250 : $_baseHeight;
$_statHeight = 20;
$_graphSpace = 10;
$_graphTextSpace = 20;
$_valueFontFile = realpath('../') . '/config/arialn.ttf';
$_valueFontSize = 9;
$_valueFontAngel = 0;
$_legendPosition = 'right';
$_legendColumnSpace = 20;
$_legendColumns = 1;
$_legendFontFile = realpath('../') . '/config/arialn.ttf';
$_legendFontSize = 8;
$_legendFontAngel = 0;
$_legendBottom = 10;
$_legendRight = 10;
$_legendLeft = 10;
$_legendTop = 10;
$_legendColorBoxSize = 8;
$_baseColorList = array("200,100,100", "100,200,100", "100,100,200", "200,200,100", "100,200,200", "200,100,200",
                        "250,150,150", "150,250,150", "150,150,250", "250,250,150", "150,250,250", "250,150,250");

// get the posted (GET) values
$_real_values = explode(";", base64_decode($val));
$_valueTotal = 0;
$_dataArray = array();
foreach ($_real_values as $k => $v) {
	$v = explode(":", $v);
	$_dataArray[$v[1]] = (int)$v[0];
	$_valueTotal = $_valueTotal + (int)$v[0];
}
unset($_real_values);
unset($val);

// Sort the list by value
arsort($_dataArray);

// calculate the percent (1) and the angle (2)
$_tmp = $_dataArray;
$_dataArray = array();
foreach ($_tmp as $k => $v) {
	$_dataArray[$k][0] = number_format($v, 0, '.', '\'');
	$_dataArray[$k][1] = round(($v * 100 / $_valueTotal), 2);
	$_dataArray[$k][2] = round(($v * 360 / $_valueTotal), 0);
}
unset($_tmp);

// Calculates the Legend-Height and Width
$_legendHeight = 0;
$_legendWidth = 0;
$_legendTextPos = array_fill(0, $_legendColumns, 0);
$_legendTextWidth = array_fill(0, $_legendColumns, 0);
$_cnt = 0;
foreach ($_dataArray as $k => $v) {
	$text = $k . " (" . $v[0] . ")";
	$bbox = ImageTTFBBox($_legendFontSize, $_legendFontAngel, $_legendFontFile, $text);
	$bbox = _getTTFTextDimension($bbox);
	$_w = $bbox['width'] + $_legendColorBoxSize;
	$_h = $bbox['height'] + 3;
	
	// Check for TextWidth in Column1 and Column 2
	$_column = $_cnt%$_legendColumns;
	if ($_w > $_legendTextWidth[$_column]) {
		$_legendTextWidth[$_column] = $_w;
	}
	
	// We have $_legendColumns columns - so on each second entry we need to add one line
	if ($_cnt%$_legendColumns == 0) {
		$_legendHeight += $_h;
	}
	$_cnt++;
}

// Calculate LegendText-Positions
$_w = $_legendLeft;
for ($i = 0; $i < count($_legendTextWidth); $i++) {
	$_w += ($_legendLeft*($i+1)) + ($_legendColorBoxSize*($i+1)) + ($_legendColumnSpace*$i);
	if ($i > 0) {
		$_w += $_legendTextWidth[$i-1];
	}
	$_legendTextPos[$i] = $_w;
}

// Add defined spaces around the Legend
$_legendWidth = array_sum($_legendTextWidth) + ($_legendLeft*$_legendColumns) + ($_legendColumnSpace*$_legendColumns) + ($_legendColorBoxSize*$_legendColumns) + $_legendRight;
$_legendHeight = $_legendHeight + $_legendTop + $_legendBottom;

// Create the BASE-Image and fill them widt white background
$_base = ImageCreateTrueColor($_baseWidth, $_baseHeight);
$_bg = ImageColorAllocate($_base, 255, 255, 255);
$_text = ImageColorAllocate($_base, 60, 60, 60);
$_light = ImageColorAllocate($_base, 240, 240, 240);
$_tmp = ImageFill($_base, 0, 0, $_bg);

// Draw a Border around the Legend
if ($_legendPosition == 'bottom') {
	$_top = $_baseHeight - $_legendHeight;
	$_left = $_legendLeft;
	$_right = $_baseWidth - $_legendRight;
	$_bottom = $_baseHeight - $_legendBottom;
} else if ($_legendPosition == 'left') {
	$_top = $_legendTop;
	$_left = $_legendLeft;
	$_right = $_legendWidth;
	$_bottom = $_legendHeight;
} else {
	$_top = $_legendTop;
	$_left = $_baseWidth - $_legendWidth;
	$_right = $_baseWidth - $_legendRight;
	$_bottom = $_legendHeight;
}
$_tmp = ImageFilledRectangle($_base, $_left, $_top, $_right, $_bottom, $_light);
$_tmp = ImageRectangle($_base, $_left, $_top, $_right, $_bottom, $_text);

// Create the Legend-Text and the Box around it
$_cnt = 0;
$_colors = array();
foreach ($_dataArray as $k => $v) {
	// The text to show
	$text = $k.' ('.$v[0].')';
	
	// Allocate the color for this part (dark, normal and dark-transparent)
	$_col = explode(",", $_baseColorList[$_cnt%count($_baseColorList)]);
	$_darkTrans[$_cnt] = ImageColorAllocateAlpha($_base, ($_col[0] - 50), ($_col[1] - 50), ($_col[2] - 50), 100);
	$_color[$_cnt] = ImageColorAllocate($_base, $_col[0], $_col[1], $_col[2]);
	$_dark[$_cnt] = ImageColorAllocate($_base, ($_col[0] - 50), ($_col[1] - 50), ($_col[2] - 50));
	
	// Get the textbox-Size
	$bbox = ImageTTFBBox($_legendFontSize, $_legendFontAngel, $_legendFontFile, $text);
	$bbox = _getTTFTextDimension($bbox);
	
	// Calculate the TextPosition
	$_column = $_cnt%$_legendColumns;
	if (($_column == 0)) {
		$_top = $_top + $bbox['height'] + 3;
	}
	$_textLeft = $_left + $_legendTextPos[$_column];
	
	// Draw the colored Box and the Text
	$_legendBoxLeft[0] = ($_textLeft - $_legendColorBoxSize - $_legendLeft);
	$_legendBoxLeft[1] = ($_textLeft - $_legendLeft);
	$_legendBoxTop[0] = ($_top - $_legendColorBoxSize) - floor(($bbox['height'] - $_legendColorBoxSize) / 2);
	$_legendBoxTop[1] = ($_top) - floor(($bbox['height'] - $_legendColorBoxSize) / 2);
	
	$_tmp = ImageTTFText($_base, $_legendFontSize, $_legendFontAngel, $_textLeft, $_top, $_text, $_legendFontFile, $text);
	$_tmp = ImageFilledRectangle($_base, $_legendBoxLeft[0], $_legendBoxTop[0], $_legendBoxLeft[1], $_legendBoxTop[1], $_color[$_cnt]);
	$_tmp = ImageRectangle($_base, $_legendBoxLeft[0], $_legendBoxTop[0], $_legendBoxLeft[1], $_legendBoxTop[1], $_dark[$_cnt]);
	
	$_cnt++;
}

// Calculate the Arc-Dimensions and his center
if ($_legendPosition == 'right') {
	$_eHeight = $_baseHeight - $_statHeight - ($_graphSpace*2);
	$_eWidth = $_baseWidth - $_legendWidth - ($_graphSpace*2);
	$_eCenterX = ($_eWidth / 2) + $_graphSpace;
	$_eCenterY = (($_baseHeight - $_statHeight) / 2);
} else if ($_legendPosition == 'left') {
	$_eHeight = $_baseHeight - $_statHeight - ($_graphSpace*2);
	$_eWidth = $_baseWidth - $_legendWidth - ($_graphSpace*2);
	$_eCenterX = $_baseWidth - ($_eWidth / 2) - $_graphSpace;
	$_eCenterY = (($_baseHeight - $_statHeight) / 2);
} else {
	$_eHeight = $_baseHeight - $_legendHeight - ($_graphSpace*2) - $_legendTop - $_legendBottom;
	$_eWidth = $_baseWidth - ($_graphSpace*2);
	$_eCenterX = ($_baseWidth / 2);
	$_eCenterY = (($_baseHeight - $_legendHeight - $_legendTop - $_statHeight) / 2);
}

// prepare the 3D-Effect
$_cnt = 0;
$_angle = 0;
$_3dList = array();
$_printLast = 0;
foreach ($_dataArray as $k => $v) {
	if ($_angle <= 180) {
		$_angleEnd = $_angle + (float)$v[2];
		$_3dList[$_cnt] = array($_angle, $_angleEnd);
		
		if (($_angle <= 90) && ($_angleEnd > 90)) $_printLast = $_cnt;
		
		$_angle = $_angleEnd;
	}
	$_cnt++;
}

// Show arc's at left
for ($i = count($_3dList) - 1; $i > $_printLast; $i--) {
	$_angle = $_3dList[$i][0];
	$_angleEnd = $_3dList[$i][1];
	for ($y = ($_eCenterY + $_statHeight); $y > $_eCenterY; $y--) {
		ImageArc($_base, $_eCenterX, $y, $_eWidth, $_eHeight, $_angle, $_angleEnd, $_dark[$i]);
	}
}
// show arc's on right side
for ($i = 0; $i < $_printLast; $i++) {
	$_angle = $_3dList[$i][0];
	$_angleEnd = $_3dList[$i][1];
	for ($y = ($_eCenterY + $_statHeight); $y > $_eCenterY; $y--) {
		ImageArc($_base, $_eCenterX, $y, $_eWidth, $_eHeight, $_angle, $_angleEnd, $_dark[$i]);
	}
}
// Show the last arc in front
$_angle = $_3dList[$_printLast][0];
$_angleEnd = $_3dList[$_printLast][1];
if ($_angleEnd >= 360) {
	$_tmpCenter = $_eCenterY - 1;
} else {
	$_tmpCenter = $_eCenterY;
}
for ($y = ($_eCenterY + $_statHeight); $y > $_tmpCenter; $y--) {
	ImageArc($_base, $_eCenterX, $y, $_eWidth, $_eHeight, $_angle, $_angleEnd, $_dark[$_printLast]);
}
	
// The arc itselfs
$_cnt = 0;
$_angle = 0;
$_angleEnd = 0;
foreach ($_dataArray as $k => $v) {
	$_angleEnd = ($_angle + $v[2]);
	ImageFilledArc($_base, $_eCenterX, $_eCenterY, $_eWidth, $_eHeight, $_angle, $_angleEnd, $_color[$_cnt], IMG_ARC_PIE);
	
	// Draw the Border, first transparent, after solid...
	ImageSetThickness($_base, 2);
	ImageFilledArc($_base, $_eCenterX, $_eCenterY, $_eWidth, $_eHeight, $_angle, $_angleEnd, $_darkTrans[$_cnt], IMG_ARC_PIE | IMG_ARC_NOFILL | IMG_ARC_EDGED);
	ImageSetThickness($_base, 1);
	ImageFilledArc($_base, $_eCenterX, $_eCenterY, $_eWidth, $_eHeight, $_angle, $_angleEnd, $_dark[$_cnt], IMG_ARC_PIE | IMG_ARC_NOFILL | IMG_ARC_EDGED);
	
	$_angle = $_angle + (float)$v[2];
	$_cnt++;
}

// Calculate the Elipse-Arc for TextValues
if ($_eWidth > $_eHeight) {
	$_factor = $_eHeight / $_eWidth;
	$_tWidth = $_eWidth - $_graphTextSpace;
	$_tHeight = $_eHeight - ($_graphTextSpace*$_factor);
} else {
	$_factor = $_eWidth / $_eHeight;
	$_tWidth = $_eWidth - ($_graphTextSpace*$_factor);
	$_tHeight = $_eHeight - $_graphTextSpace;
}

// the values on the Arc
$_cnt = 0;
$_angle = 0;
$_angleEnd = 0;
$_angleCalc = 0;
foreach ($_dataArray as $k => $v) {
	$_angleEnd = ($_angle + $v[2]);
	
	// calculate the center-angle of the circle-segment
	$_angleMiddle = ($_angle + ($v[2] / 2));
	if ($_angleMiddle <= 90) {
		$_angleCalc = $_angleMiddle;
	} else if ($_angleMiddle <= 180) {
		$_angleCalc = 180 - $_angleMiddle;
	} else if ($_angleMiddle <= 270) {
		$_angleCalc = $_angleMiddle - 180;
	} else {
		$_angleCalc = 360 - $_angleMiddle;
	}
	
	// get the textbox size for positioning
	$_valueText = $v[1] . "%";
	$bbox = ImageTTFBBox($_valueFontSize, $_valueFontAngel, $_valueFontFile, $_valueText);
	$bbox = _getTTFTextDimension($bbox);
	
	// Calculate Position on the Elipse
	$_x = (($_tWidth/2) - 20) * cos(deg2rad($_angleCalc));
	$_y = (($_tHeight/2) - 10) * sin(deg2rad($_angleCalc));
	if ($_angleMiddle <= 90) {
		$_x = $_eCenterX + $_x;
		$_y = $_eCenterY + $_y;
	} else if ($_angleMiddle <= 180) {
		$_x = $_eCenterX - $_x;
		$_y = $_eCenterY + $_y;
	} else if ($_angleMiddle <= 270) {
		$_x = $_eCenterX - $_x;
		$_y = $_eCenterY - $_y;
	} else {
		$_x = $_eCenterX + $_x;
		$_y = $_eCenterY - $_y;
	}
	//imagefilledrectangle($_base, $_x-2, $_y-2, $_x+2, $_y+2, $_text);
	
	$_x = $_x - ($bbox['width'] / 2);
	$_y = $_y + ($bbox['height'] / 2);
	$_tmp = imagettftext($_base, $_valueFontSize, $_valueFontAngel, $_x, $_y, $_text, $_valueFontFile, $_valueText);
	
	// calc the new angles
	$_angle = $_angleEnd;
	$_cnt++;
}

// Show the Image
header("Content-Type: image/png");
ImagePNG($_base);
ImageDestroy($_base);


function _getTTFTextDimension($bbox) {
	$tmp_bbox["left"] = min($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
	$tmp_bbox["top"] = min($bbox[1], $bbox[3], $bbox[5], $bbox[7]);
	$tmp_bbox["width"] = max($bbox[0], $bbox[2], $bbox[4], $bbox[6]) - min($bbox[0], $bbox[2], $bbox[4], $bbox[6]) + 1;
	$tmp_bbox["height"] = max($bbox[1], $bbox[3], $bbox[5], $bbox[7]) - min($bbox[1], $bbox[3], $bbox[5], $bbox[7]);
	$tmp_bbox["left"] = 0 - $tmp_bbox["left"];
	$tmp_bbox["top"] = 0 - $tmp_bbox["top"];
	return $tmp_bbox;
}

?>

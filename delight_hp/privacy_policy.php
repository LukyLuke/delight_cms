<?php
	$_file = 'template/cont_odsp_de.html';
	if (isset($_GET['lang']) && (strlen(trim($_GET['lang'])) > 0) )
	{
		switch (substr(strtolower($_GET['lang']), 0, 4))
		{
			case 'engl': $_file = "template/cont_odsp_en.html"; break;
			case 'germ': $_file = "template/cont_odsp_de.html"; break;
			default:     $_file = "template/cont_odsp_de.html"; break;
		}
	}
	if (!file_exists($_file))
		$_file = 'template/cont_odsp_de.html';

	$fp = fopen($_file, "r");
	if ($fp)
	{
		while (!feof($fp))
			print(fread($fp, 128));
	}
	@fclode($fp);
?>
<?php

interface iPlugin {
	public function setContentParameters($params);
	public function callFunction($function);
	public function getStaticPagesList($langId, $menuId, $shortMenu='', $menuIsActive=true);
}

?>

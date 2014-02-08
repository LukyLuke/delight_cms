<?php
$script = dirname($_SERVER['SCRIPT_FILENAME']);
$script = substr($script,0,(strlen($script) - 1));
$script = substr($scrip,0,(strrpos($script,"/")));
header("Location: ".$script);
?>
<?php
require("../../templates.class.php");

$template = new Templates("tpl/");
$template->assignVars(array(
	"HELLOWORLD"		=>		"Hello World"
));
$template->setTemplate("simple.html");
?>
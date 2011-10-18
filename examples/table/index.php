<?php
require("../../templates.class.php");

$template = new Templates("tpl/");
$template->assignVars(array(
	"POS"		=>		"Position",
	"COLOR"		=>		"red"
));
for ($i=0 ; $i < 5 ; $i++) {
	$template->assignBlockVars("tr", array(
		"COLOR"		=>		$i % 2 == 0
	));
	for ($j=0 ; $j < 5 ; $j++) {
		$template->assignBlockVars("tr.td", array(
			"VAL"		=>		'x:' . $i . ';y:' . $j
		));
	}
}
$template->setTemplate("index.html");
?>
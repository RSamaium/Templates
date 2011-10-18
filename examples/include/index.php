<?php
require("../../templates.class.php");

$template = new Templates("tpl/");
$template->assignVars(array(
	"TITLE"		=>		"My Template",
	"TEXT"		=>		"Quibus ita sceleste patratis Paulus cruore perfusus reversusque ad principis castra multos coopertos paene catenis adduxit in squalorem deiectos atque maestitiam, quorum adventu intendebantur eculei uncosque parabat carnifex et tormenta. et ex is proscripti sunt plures actique in exilium alii, non nullos gladii consumpsere poenales. nec enim quisquam facile meminit sub Constantio, ubi susurro tenus haec movebantur, quemquam absolutum."
));

$meta = array(
	array('keywords', 'vero, tempestate, praefectus, praetorio, praesens, ipse'),
	array('description', 'Ex his quidam aeternitati se commendari posse per statuas aestimantes eas ardenter adfectant quasi plus praemii de figmentis aereis sensu carentibus adepturi')
);

for ($i=0 ; $i < count($meta) ; $i++) {
	$template->assignBlockVars("meta", array(
		'NAME'		=> $meta[$i][0],
		'CONTENT'	=> $meta[$i][1]
	));
}

$template->setTemplate("index.html");
?>
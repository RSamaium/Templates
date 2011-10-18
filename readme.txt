Templates 1.0

https://github.com/RSamaium/Templates

Required :
- PHP 5

Creating Templates for MVC. Templates like phpBB

Example :

$template = new Templates("path/to/tpl/");
$template->assignVars(array(
	"HELLOWORLD"		=>		"Hello !"
));
$template->setTemplate("index.html");


File "index.html" in the "path/to/tpl/" :
	
<p>{HELLOWORLD}</p>

displays : <p>Hello !</p>
 
 
Documentation :
	
	{VAR}											=>	Display the value of VAR. See "assignVars()"
	<!-- IF VAR -->	<!-- ENDIF -->					=>	Test the value of VAR. Condition.
	<!-- IF VAR -->	<!-- ELSE --> <!-- ENDIF -->		
	<!-- IF VAR -->	<!-- ELESIF --> <!-- ENDIF -->
	<!-- INCLUDE template.html -->					=> Include a template (link defined in the constructor)
	<!-- BEGIN test -->{test.VAR}<!-- END test -->	=> Displays the variable loop. See "assignBlockVars()"
	<!-- BEGIN foo --><!-- BEGIN foo.bar --> {foo.bar.VAR}<!-- END foo.bar --><!-- END foo -->
	
Example :


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

Template :

<b>{POS}</b> :<br />
<table>
	<!-- BEGIN tr -->
		<tr<!-- IF tr.COLOR --> style="color: {COLOR}"<!-- ENDIF -->>
		<!-- BEGIN tr.td -->
			<td>{tr.td.VAL}</td>
		<!-- END tr.td -->
		</tr>
	<!-- END tr -->
</table>
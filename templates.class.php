<?php
/*
Copyright (C) 2011 by Creative5 - Samuel Ronce

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

/**
 * @class Templates
 * @version Beta 1.0
 * @constructor
 * @param {String} path Path to the templates
	<pre>
		$template = new Templates("path/to/tpl/");
		$template->assignVars(array(
			"HELLOWORLD"		=>		"Hello !"
		));
		$template->setTemplate("index.html");
	</pre>
	File "index.html" in the "path/to/tpl/" :
	<pre>
		<p>{HELLOWORLD}</p>
	</pre>
	displays : <p>Hello !</p>
 */
 
/**
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
		
*/

class Templates {

	private $name, 
			$vars, 
			$block_vars, 
			$contents, 
			$path, 
			$index_used;
	
	// Limit Imbricate Begin
	const LIMIT_IMBRICATE_BEGIN = 15;
	
	function __construct($path) {
		$this->path = $path;
		$this->vars = array();
		$this->block_vars = array();
	}
	
	/**
	 * Assign variables to the template
	 * @method assignVars
	 * @param {Array} array_vars. The key is the identifier of the variable. The array value is the value of the variable
	*/	
	public function assignVars($array_vars) {
		foreach ($array_vars as $key => $val) {
			$this->vars[$key] = $val;
		}
	}
	
	/**
	 * Check the existing value of a key
	 * @method varExist
	 * @param {String} key Key
	 * @example
		<pre>
			$template->assignVars(array(
				'FOO'	=>	'bar'
			));
			$template->varExist('FOO');		// true
			$template->varExist('TEST');	// false
		</pre>
	 * @return Boolean
	*/	
	public function varExist($key) {
		return isset($this->vars[$key]);
	}
	
	/**
	 * Get the value of an identifier
	 * @method getVar
	 * @param {String} key Key
	 * @example
		<pre>
			$template->assignVars(array(
				'FOO'	=>	'bar'
			));
			$template->getVars('FOO');	// "bar"
		</pre>
	 * @return Boolean
	*/	
	public function getVar($key) {
		return $this->vars[$key];
	}
	
	public function getAllVar() {
		return $this->vars;
	}
	
	public function getBlockVar($key) {
		return $this->block_vars[$key];	
	}
	
	public function getAllBlockVar() {
		return $this->block_vars;
	}
	
	public function setBlockVar($var, $id, $key, $value) {
		$this->block_vars[$var][$id][$key] = $value;	
	}
	
	/**
	 * Assign a block of variables
	 * @method assignBlockVars
	 * @param {String} name_var. Block name
	 * @param {Array} array_vars. See "assignVars()"
	 * @example
	  <pre>
			for ($i = 0 ; $i < 3 ; i++) {
				$template->assignBlockVars("block", array(
					"FOO"	=>	"bar$i"
				));
			}
	  </pre>
		Template :
		<pre>
			<!-- BEGIN block -->
				<p>{block.FOO}</p>
			<!-- END block -->
		</pre>
		displays :
			<p>bar0</p>
			<p>bar1</p>
			<p>bar2</p>
	*/	
	public function assignBlockVars($name_var, $array_vars) {
		$array_name_vars = explode('.', $name_var);
		$this->blockDepth(0, $array_name_vars, $this->block_vars, $array_vars);
		
	}
	
	/**
	 * Assigns a block of variable JS files
	 * @method addJs
	 * @param {String} name_var. Block name
	 * @param {Array} paths_js. JS files. The key is "SRC". See example
	 * @param {String} path. Path to the JS files
	 * @param {Boolean} extension (optional) Display extension. "false" by default
	 * @example
	  <pre>
			$template->addJs("js", array(
				"jquery",
				"custom"
			), "path/to/dir");
			
	  </pre>
		Template :
		<pre>
			<!-- BEGIN js -->
				<script type="text/javascript" src="{js.SRC}"></script> 
			<!-- END js -->
		</pre>
		displays :
			<script type="text/javascript" src="path/to/dir/jquery.js"></script> 
			<script type="text/javascript" src="path/to/dir/custom.js"></script> 
	*/	
	public function addJs($name_var, $paths_js, $path, $extension = false) {
		$this->_addLinkFiles($name_var, $paths_js, $path, array(
			'ext'			=> 	'js',
			'block_name'	=>	'SRC'
		), $extension);
	}
	
	
	/**
	 * Assigns a block of variable Css files
	 * @method addCss
	 * @param {String} name_var. Block name
	 * @param {Array} paths_js. CSS files. The key is "HREF". See example
	 * @param {String} path. Path to the CSS files
	 * @param {Boolean} extension (optional) Display extension. "false" by default
	 * @example
	  <pre>
			$template->addCss("mycss", array(
				"style",
				"other"
			), "path/to/dir");
			
	  </pre>
		Template :
		<pre>
			<!-- BEGIN mycss -->
				<link rel="stylesheet" href="{mycss.HREF}" type="text/css">
			<!-- END mycss -->
		</pre>
		displays :
			<link rel="stylesheet" href="path/to/dir/style.css" type="text/css">
			<link rel="stylesheet" href="path/to/dir/other.css" type="text/css">
	*/	
	public function addCss($name_var, $paths_css, $path, $extension = false) {
		$this->_addLinkFiles($name_var, $paths_css, $path, array(
			'ext'			=> 'css',
			'block_name'	=>	'HREF'
		), $extension);
	}
	
	/**
	 * Displays the template
	 * @method setTemplate
	 * @param {String} name Template name
	*/	
	public function setTemplate($name) {
		$this->name = $name;
		$this->display();
	}
	
	private function includeAssignBlockVars($array_vars) {
		$this->block_vars = $array_vars;
	}
	
	private function blockDepth($i, $name_vars, &$block, $array_vars) {
		
		$t = sizeof($name_vars);
		if (!isset($block[$name_vars[$i]])) {
			$block[$name_vars[$i]] = array();
		}
		
		$t2 = sizeof($block[$name_vars[$i]]);
		
		if ($i == $t-1) {
			
			$block[$name_vars[$i]][] = array();
			$t2 = sizeof($block[$name_vars[$i]]);
		
			foreach ($array_vars as $key => $val) {
				if (!isset($block[$name_vars[$i]][$t2-1][$key])) {
						$block[$name_vars[$i]][$t2-1][$key] = $val;	
				}
			}	
		}
		if ($i  >= $t-1)
			return;
		else
			$this->blockDepth(($i+1),  $name_vars, $block[$name_vars[$i]][$t2-1], $array_vars);
	
	}
	
	private function compileCode() {
		foreach ($this->vars as $key => $val) {
			$this->contents = preg_replace('#\\{' . $key . '\\}#', $val, $this->contents);
		}
		
		$this->begin_imbrique($this->contents, 0);
		if (preg_match_all('#\\{[a-zA-Z0-9_]+\\.[0-9]+.*?\\}#', $this->contents, $matches)) {
			for ($i=0 ; $i < sizeof($matches[0]) ; $i++)
				$this->contents = preg_replace('#' . $matches[0][$i] . '#', $this->vars_begin(preg_replace('#[\\{\\}]#', '', $matches[0][$i])), $this->contents);
			}
		
		$this->contents = preg_replace('#<!-- (BEGIN|END) (.*?) -->#', '', $this->contents);
		
		$preg_replace = array(
			'<!-- IF (!)?([a-zA-Z0-9_]+)([a-zA-Z0-9=><_!" ]+)? -->' 						  => 'if (\\1$this->vars["\\2"]\\3) {',
			'<!-- IF (!)?([a-zA-Z0-9_]+\\.[0-9]+[a-zA-Z0-9_\\.]+)([a-zA-Z0-9=><_!" ]+)? -->'  => 'if (\\1$this->vars_begin("\\2")\\3) {',
			'<!-- ELSEIF (!)?([a-zA-Z0-9_]+)([a-zA-Z0-9=><_!" ]+)? -->'						  =>	'} elseif (\\1$this->vars["\\2"]\\3) {',
			'<!-- ELSEIF (.*?\\.[0-9]+.*?) -->'												  =>	'} elseif ($this->vars_begin("\\1")) {',
			'<!-- ELSE -->'																	  =>	'} else {',
			'<!-- ENDIF -->'																  =>	'}',
			'<!-- INCLUDE (.*?) -->'														  => '$t_include = new Templates($this->path); 
																		$t_include->assignVars($this->vars);
																		$t_include->includeAssignBlockVars($this->block_vars);
																		$t_include->setTemplate("\\1");'
		);
		foreach ($preg_replace as $key => $val) {
			$this->contents = preg_replace('#' . $key . '#', '<?php ' . $val . ' ?>', $this->contents);
		}

	}


	private function vars_begin($var_name) {
		$array_vars = explode('.', $var_name);
		$block = $this->block_vars;
		for ($i=0; $i < sizeof($array_vars) ; $i++) {
			$block = &$block[$array_vars[$i]];
		}
		return $block;
	}
	

	private function begin_imbrique($text_begin, $imbrique) {
		
		if ($imbrique > Templates::LIMIT_IMBRICATE_BEGIN)
			return;

		$regex_begin = '<!-- BEGIN (.*?) -->(.*?)(<!-- BEGINELSE -->(.*?))?<!-- END \\1 -->';
		if (preg_match_all('#' . $regex_begin . '#s', $text_begin, $matches)) {
				for ($i=0 ; $i < sizeof($matches[0]) ; $i++) {
					$text = $matches[2][$i];
					$begin_var = $matches[1][$i];
					$new_text = '';
					$var = $this->vars_begin($begin_var);
					for ($j=0 ; $j < sizeof($var) ; $j++) {
							$tmp_text = $text;
							$tmp_text = preg_replace('#(\\{' . $begin_var . ')(.*?\\})#s', '\\1.' . $j . '\\2', $tmp_text);
							$tmp_text = preg_replace('#(<!-- (BEGIN|END|IF|ELSEIF) !?' . $begin_var . ')(.*? -->)#s', '\\1.' . $j . '\\3', $tmp_text);
							$new_text .= $tmp_text;
							
					}
					$this->contents = str_replace($text, $new_text, $this->contents);
					if (preg_match('#' . $regex_begin . '#s', $new_text))
						$this->begin_imbrique($new_text, $imbrique+1);
			  }
		}
	}
	
	private function assignDisplay() {
		ob_start();
		include($this->path . $this->name);
		$this->contents = ob_get_clean();
	}
	
	private function display() {
		$this->assignDisplay();
		$this->compileCode();
		eval(' ?>' . $this->contents . ' <?php ');
	}
	
	
	private function _addLinkFiles($name_var, $paths_file, $path, $params, $extension = false) {
		foreach ($paths_file as $key => $value) {
			$url = $path . $value . (!$extension ? '.' . $params['ext']  : '');
			if (file_exists($url)) {
				$this->assignBlockVars($name_var, array(
					$params['block_name'] =>  $url
				));
			}
		}
	}
}
?>
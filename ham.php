<!-- @file H.A.M. main library file
     @author Fritz-Walter Schwarm
     @usage Include this file to your project and call ham()
 -->

<?php

//! Include files (order matters)
include "config.php";
include "boxes.php";
include "table.php";
include "entities.php";
include "xy.php";
include "links.php";
include "inputs.php";
include "layout.php";
include "print.php";
include "parser.php";
include "header.php";
include "footer.php";
include "debug.php";

class ham
{
	private $content;
	private $cfg;
	private $layout;

	public function __construct($content, $opts = null) {

		$this->content = $content;
		$this->cfg = new hamConfig($opts);

		switch ($this->cfg->get('layout')) {
		case 'table':
			$this->layout = new hamLayout($this->cfg);
			break;
		default:
			error_log("Unknown layout type " . $this->cfg->get('layout') . "!");
			break;
		}
	}

	public function render() {

		$page = $cfg->get('page');

		$out = "";
	
		//! Add page header if 'page' option is specified
		if ($page) {
			$out .= ham_header($cfg);
		}
	
		//! Parse content
		$out .= $this->parse();
	
		//! Add page footer if 'page' option is specified
		if ($page) {
			$out .= ham_footer($cfg);
		}
	
		return $out;
	}

	public function parse() {

		$comment = $cfg->get('comment');
		$nl = PHP_EOL;
	
		//! Remove comments
		$out = preg_replace("/$comment(.*)$nl/", "", $this->content);

///TODO continue classification
		//! Handle overall layout
		$out = ham_layout($out, $cfg);
	
	//	//! Parse individual elements
	//	$out = ham_links($out, $cfg);
	
		//! Replace input elements
	//	$out = ham_inputs($out, $cfg);
		
		//! Replace variables
	
		return $out;
	}
}

////! Parser
//function ham($content, $opts = null)
//{
//	return ham_string($content, $opts);
//}
//
////! Parses a file
//function ham_file($file, $opts = null)
//{
//	$opts['file'] = $file;
//
//	return ham_string(file_get_contents($file), $opts);
//}

//! Parse the given string

?>

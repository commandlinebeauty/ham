<!-- @file H.A.M. main library file
     @author Fritz-Walter Schwarm
     @usage Include this file to your project and call ham()
 -->

<?php

//! Include files (order matters)
include "vars.php";
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

//! Parser
function ham($content, $opts = null)
{
	return ham_string($content, $opts);
}

//! Parses a file
function ham_file($file, $opts = null)
{
	$opts['file'] = $file;

	return ham_string(file_get_contents($file), $opts);
}

//! Parse the given string
function ham_string($in, $opts = null)
{
	$page = ham_option('page', $opts, false);

	$out = "";

	//! Add page header if 'page' option is specified
	if ($page) {
		$out .= ham_header($opts);
	}

	$out .= ham_parser($in, $opts);

	//! Add page footer if 'page' option is specified
	if ($page) {
		$out .= ham_footer($opts);
	}

	return $out;
}

?>

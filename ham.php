<!-- @file H.A.M. main library file
     @author Fritz-Walter Schwarm
     @usage Include this file to your project and call ham()
 -->

<?php

//! Include files (order matters)
include "vars.php";
include "xy.php";
include "parser.php";
include "header.php";
include "footer.php";

//! Parse the given string
function ham_string($in, $opts = null)
{
	$out = "";

	//! Add page header if 'page' option is specified
	if ($opts && array_key_exists('page', $opts) && $opts['page']) {
		$out .= ham_header($opts);
	} else {
		$opts['page'] = false;
	}

	$content = ham_parser($in, $opts);

	$out .= "<pre class=ham>" . $content . "</pre>";

	//! Add page footer if 'page' option is specified
	if ($opts['page']) {
		$out .= ham_footer($opts);
	}

	return $out;
}

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

?>

<!-- @file H.A.M. main library file
     @author Fritz-Walter Schwarm
 -->

<?php

include "vars.php";
include "header.php";
include "footer.php";

//! Replace links (a/A-z/Z letters and digits within brackets)
function ham_replace_links($in, $opts = null)
{
	$linkLeft   = ham_option('linkLeft',  $opts, "[");
	$linkLeftQ  = preg_quote($linkLeft, "/");
	$linkRight  = ham_option('linkRight', $opts, "]");
	$linkRightQ = preg_quote($linkRight, "/");

	$out = preg_replace_callback(
//		"/(\s*)$linkLeftQ([^$linkRightQ]*)$linkRightQ(\s*)/m",
		"/(\s*)$linkLeftQ([a-zA-Z0-9]*)$linkRightQ(\s*)/m",
		function ($m) use($opts,$linkLeft,$linkRight) {

//			$length = array_sum(array_map('strlen', $m)) +
//				strlen($linkLeft) + strlen($linkRight);
			$name = $m[2];

                        return "$linkLeft<a href=\"#$name\">$name</a>$linkRight";
	}, $in);

	return $out;
}

//! Replace input elements (everything within curled brackets)
function ham_replace_inputs($in, $opts = null)
{
	$inputLeft   = ham_option('inputLeft',  $opts, "{");
	$inputLeftQ  = preg_quote($inputLeft, "/");
	$inputRight  = ham_option('inputRight', $opts, "}");
	$inputRightQ = preg_quote($inputRight, "/");

	$out = preg_replace_callback(
		"/(\s*)$inputLeftQ([_a-zA-Z0-9]*)$inputRightQ(\s*)/m",
		function ($m) use($opts,$inputLeft,$inputRight) {

			$text = $m[2];
			$length = strlen($inputLeft) + strlen($text) +
				strlen($inputRight) + strlen($m[3]) - 1;

                        return "$m[1]<input type=\"text\" size=$length value=$text> ";
	}, $in);

	return $out;
}

//! Preprocess the given string
function ham_prepare_string($in, $opts = null)
{
	$out = "";


	//! Handle overall layout


	$out = ham_replace_links($in);
	$out = ham_replace_inputs($out);
	

	//! Replace variables

	return $out;
}

//! Parse the given string
function ham_parse_string($in, $opts = null)
{
	$out = "";

	//! Add page header if 'page' option is specified
	if ($opts && array_key_exists('page', $opts) && $opts['page']) {
		$out .= ham_header($opts);
	} else {
		$opts['page'] = false;
	}

	$content = ham_prepare_string($in);

	$out .= "<pre class=ham>" . $content . "</pre>";

	//! Add page footer if 'page' option is specified
	if ($opts['page']) {
		$out .= ham_footer($opts);
	}

	return $out;
}

//! Parser
function ham_parse($content, $opts = null)
{
	return ham_parse_string($content, $opts);
}

//! Parses a file
function ham_parse_file($file, $opts = null)
{
	return ham_parse_string(file_get_contents($file), $opts);
}

?>

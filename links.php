<?php

//! Replace links (a/A-z/Z letters and digits within brackets)
function ham_links($in, $opts = null)
{
	$linkLeft   = ham_options_get('linkLeft',  $opts, "[");
	$linkLeftQ  = preg_quote($linkLeft, "/");
	$linkRight  = ham_options_get('linkRight', $opts, "]");
	$linkRightQ = preg_quote($linkRight, "/");

	$out = preg_replace_callback(
//		"/(\s*)$linkLeftQ([^$linkRightQ]*)$linkRightQ(\s*)/m",
		"/(\s*)$linkLeftQ([a-zA-Z0-9]*)$linkRightQ(\s*)/m",
		function ($m) use($opts,$linkLeft,$linkRight) {

//			$length = array_sum(array_map('strlen', $m)) +
//				strlen($linkLeft) + strlen($linkRight);
			$name = $m[2];

                        return "$linkLeft<a href=\"#$name\" id=\"$name\">$name</a>$linkRight";
	}, $in);

	return $out;
}

?>

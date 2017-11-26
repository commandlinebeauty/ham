<?php

//! Replace links (a/A-z/Z letters and digits within brackets)
function ham_links($in, $cfg = null)
{
	$linkLeft   = $cfg->get('linkLeft');
	$linkRight  = $cfg->get('linkRight');

	$linkLeftQ  = preg_quote($linkLeft, "/");
	$linkRightQ = preg_quote($linkRight, "/");

	$out = preg_replace_callback(
//		"/(\s*)$linkLeftQ([^$linkRightQ]*)$linkRightQ(\s*)/m",
		"/(\s*)$linkLeftQ([a-zA-Z0-9]*)$linkRightQ(\s*)/m",
		function ($m) use($cfg,$linkLeft,$linkRight) {

//			$length = array_sum(array_map('strlen', $m)) +
//				strlen($linkLeft) + strlen($linkRight);
			$name = $m[2];

                        return "$m[1]$linkLeft<a href=\"$name\" id=\"$name\">$name</a>$linkRight$m[3]";
	}, $in);

	return $out;
}

?>

<?php

//! Replace links (a/A-z/Z letters and digits within brackets)
function ham_links($in, $cfg = null)
{
	return ham_links_anchor($in, $cfg);
}

//! Replace links (a/A-z/Z letters and digits within brackets)
function ham_links_anchor($in, $cfg = null)
{
	$linkLeft   = $cfg->get('linkLeft');
	$linkRight  = $cfg->get('linkRight');

	$linkLeftQ  = preg_quote($linkLeft, "/");
	$linkRightQ = preg_quote($linkRight, "/");

	$out = preg_replace_callback(
//		"/(\s*)$linkLeftQ([^$linkRightQ]*)$linkRightQ(\s*)/m",
		"/(\s*)$linkLeftQ([a-zA-Z0-9#]*)$linkRightQ(\s*)/m",
		function ($m) use($cfg,$linkLeft,$linkRight) {

//			$length = array_sum(array_map('strlen', $m)) +
//				strlen($linkLeft) + strlen($linkRight);
			$name = $m[2];

                        return "$m[1]$linkLeft<a href=\"$name\" id=\"$name\">$name</a>$linkRight$m[3]";
	}, $in);

	return $out;
}

//! Replace links (a/A-z/Z letters and digits within brackets)
function ham_links_label($in, $cfg = null)
{
	$labelLeft   = $cfg->get('boxLabelLeft');
	$labelRight  = $cfg->get('boxLabelRight');
	$labelLeftQ  = preg_quote($labelLeft, "/");
	$labelRightQ = preg_quote($labelRight, "/");

	$out = preg_replace_callback(

		"/^(.*)$labelLeftQ(.*)$labelRightQ(.*)/",

		function ($m) use($cfg,$labelLeft,$labelRight) {

			$name = $m[2];

                        return "$m[1]<a href=\"#$name\" id=\"$name\">$labelLeft$name$labelRight</a>$m[3]";
	}, $in);

	return $out;
}

?>

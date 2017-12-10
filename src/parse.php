<?php

function ham_parse($in, $cfg = null)
{
//	$out = $in;
	$out = "";

	$lines = preg_split('/$\R?^/m', $in);
	$count = count($lines);

	$offset = $cfg->get('parseOffset');
	$start = $offset;
	$stop = $count - 1;

//	for ($i = 0; $i < $count; $i++) {
//		$out .= $lines[$i] . PHP_EOL;
//	}
//	return $out;

	for ($i = 0; $i < $start; $i++) {
		$out .= $lines[$i] . PHP_EOL;
	}

	for ($i = $start; $i <= $stop; $i++) {
//		$out .= $lines[$i] . PHP_EOL;
		$out .= ham_parse_hamentities($lines[$i], $cfg);

		if ($i < $count - 1) {
			$out .= PHP_EOL;
		}
	}

//	for ($i = $stop + 1; $i < $count - 1; $i++) {
//		$out .= $lines[$i] . PHP_EOL;
//	}
//
//	$out .= $lines[$count - 1];

//	$out = ham_parse_htmlentities($out, $cfg);

	return $out;
}

function ham_parse_htmlentities($in, $cfg = null)
{
	return htmlspecialchars($in, ENT_COMPAT | ENT_HTML5, 'UTF-8');
}

function ham_parse_hamentities($in, $cfg = null)
{
	$out = $in;

	//! Replace input elements
	$out = ham_vars($out, $cfg);

	//! Replace links
	$out = ham_links($out, $cfg);
	
	//! Replace input elements
	$out = ham_inputs($out, $cfg);

	return $out;
}

//function ham_parse_form($in, $label, $cfg = null)
//{
//	$inputTextLeft   = $cfg->get('inputTextLeft');
//	$inputTextRight  = $cfg->get('inputTextRight');
//
//	$inputTextLeftQ  = preg_quote($inputTextLeft, "/");
//	$inputTextRightQ = preg_quote($inputTextRight, "/");
//
//	$out = preg_replace_callback(
//		"/(\s*)$inputTextLeftQ([a-zA-Z0-9#.\/]*)$inputTextRightQ(\s*)/m",
//		function ($m) use($cfg,$linkLeft,$linkRight) {
//
//			$name = $m[2];
//
//                        return "$m[1]$linkLeft<a href=\"$name\" id=\"$name\">$name</a>$linkRight$m[3]";
//	}, $in);
//
//	$out = 
//
//	return $out;
//}

?>

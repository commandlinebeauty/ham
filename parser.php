<?php

//! Preprocess the given string
function ham_parser($in, $opts = null)
{
	$out = $in;

	//! Handle overall layout
	$out = ham_parser_layout($out, $opts);

	//! Parse individual elements
	$out = ham_parser_links($out, $opts);
	$out = ham_parser_inputs($out, $opts);
	
	//! Replace variables

	return $out;
}

//! Create table layout from ASCII boxes
function ham_parser_layout($in, $opts = null)
{
	$out = $in;

	$layout = ham_option('layout', $opts, "plain");

	switch ($layout) {
	case 'table':
		return ham_parser_layout_table($in, $opts);
	default:
		return $in;
	}
}

//! Table layout
function ham_parser_layout_table($in, $opts = null)
{
	$buffer = ham_xy_init($in, $opts);

	$boxes = ham_xy_boxes($buffer, $opts);

echo "HAVE " . count($boxes);
//foreach ($boxes[0] as $key => $value) {
//    echo "Key: $key; Value: $value\n";
//}

	return $in;
}

//! Replace links (a/A-z/Z letters and digits within brackets)
function ham_parser_links($in, $opts = null)
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

                        return "$linkLeft<a href=\"#$name\" id=\"$name\">$name</a>$linkRight";
	}, $in);

	return $out;
}

//! Replace input elements
//! Text-type input within curled brackets,
//! button-type input within brackets.
function ham_parser_inputs($in, $opts = null)
{
	$out = $in;

	$out = ham_parser_inputs_text($out, $opts);
	$out = ham_parser_inputs_button($out, $opts);

	return $out;
}

function ham_parser_inputs_text($in, $opts = null)
{
	$inputLeft   = ham_option('inputTextLeft',  $opts, "{");
	$inputLeftQ  = preg_quote($inputLeft, "/");
	$inputRight  = ham_option('inputTextRight', $opts, "}");
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

function ham_parser_inputs_button($in, $opts = null)
{
	$inputLeft   = ham_option('inputButtonLeft',  $opts, "<");
	$inputLeftQ  = preg_quote($inputLeft, "/");
	$inputRight  = ham_option('inputButtonRight', $opts, ">");
	$inputRightQ = preg_quote($inputRight, "/");

	$out = preg_replace_callback(
		"/(\s*)$inputLeftQ([_a-zA-Z0-9]*)$inputRightQ(\s*)/m",
		function ($m) use($opts,$inputLeft,$inputRight) {

			$text = $m[2];
			$length = strlen($inputLeft) + strlen($text) +
				strlen($inputRight) + strlen($m[3]) - 1;

			if (strtolower($text) === "reset") {
				$type = "reset";
			} else {
				$type = "submit";
			}

                        return "$m[1]$inputLeft<input type=\"$type\" size=$length value=\"$text\">$inputRight";
	}, $in);

	return $out;
}

?>

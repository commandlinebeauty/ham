<?php

//! Replace input elements
//! Text-type input within curled brackets,
//! button-type input within brackets.
function ham_inputs($in, $opts = null)
{
	$out = $in;

	$out = ham_inputs_text($out, $opts);
	$out = ham_inputs_button($out, $opts);

	return $out;
}

function ham_inputs_text($in, $opts = null)
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

function ham_inputs_button($in, $opts = null)
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

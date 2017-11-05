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
	$inputLeft   = ham_options_get('inputTextLeft',  $opts, "{");
	$inputLeftQ  = preg_quote($inputLeft, "/");
	$inputRight  = ham_options_get('inputTextRight', $opts, "}");
	$inputRightQ = preg_quote($inputRight, "/");

	$out = preg_replace_callback(
		"/(\s*)$inputLeftQ([_a-zA-Z0-9]*)$inputRightQ(\s*)/m",
		function ($m) use($opts,$inputLeft,$inputRight) {

			$text = $m[2];
			$length = strlen($inputLeft) + strlen($text) +
				strlen($inputRight) + strlen($m[3]) - 1;

			//! The additional style rule is needed because size is implemented differently on different fonts (and even browsers). It fixes the issue that the input width appears larger on Google Chrome but not on w3m (or links2). lynx does not seem to have this issue.
                        return "$m[1]<input class=\"hamInputText\" type=\"text\" size=$length value=$text style=\"width: ".$length."ch\"> ";
//! Might be worth trying to play with attributes of the textfield, not working out of the box though.
//			return "<textarea cols=\"".$length."\" rows=\"1\" style=\"overflow: visible\" placeholder=\"$text\"></textarea>";
	}, $in);

	return $out;
}

function ham_inputs_button($in, $opts = null)
{
	$inputLeft   = ham_options_get('inputButtonLeft',  $opts, "*");
	$inputLeftQ  = preg_quote($inputLeft, "/");
	$inputRight  = ham_options_get('inputButtonRight', $opts, "*");
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

                        return "$m[1]$inputLeft<input class=\"hamInputButton\" type=\"$type\" size=$length value=\"$text\">$inputRight";
	}, $in);

	return $out;
}

?>

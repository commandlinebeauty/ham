<?php

//! Replace input elements
//! Text-type input within curled brackets,
//! button-type input within brackets.
function ham_inputs($in, $cfg)
{
	$out = $in;

	$out = ham_inputs_text($out, $cfg);
	$out = ham_inputs_button($out, $cfg);

	return $out;
}

function ham_inputs_text($in, $cfg)
{
	$inputLeft   = $cfg->get('inputTextLeft');
	$inputLeftQ  = preg_quote($inputLeft, "/");
	$inputRight  = $cfg->get('inputTextRight');
	$inputRightQ = preg_quote($inputRight, "/");
	$form = $cfg->get('currentForm');

	$out = preg_replace_callback(
		"/([ ]*)$inputLeftQ([_a-zA-Z0-9]*)$inputRightQ([ ]*)/m",
		function ($m) use($cfg,$inputLeft,$inputRight,$form) {

			$name = $m[2];
			$valLength = strlen($m[2]);

			if ($form !== null) {
				$text = ham_form_get($form, $name, $cfg);
//				$valLength = strlen($text) - strlen($m[2]);
			} else {
				$text = $m[2];
			}

			$length = strlen($inputLeft) + $valLength +
//				strlen($inputRight) + strlen($m[3]) - 1;
//! FWS TODO Why do I need - here for chrome? DANGER this - 3 problably leads to failure for very short input fields
//				strlen($inputRight) + strlen($m[3]) - 3;
//! FWS Not a good idea... fix this somewhere else!
				strlen($inputRight) + strlen($m[3]) - 1;

			//! The additional style rule is needed because size is implemented differently on different fonts (and even browsers). It fixes the issue that the input width appears larger on Google Chrome but not on w3m (or links2). lynx does not seem to have this issue.
			return "$m[1]<input class=\"hamInputText\" type=\"text\" size=$length name=\"$text\" value=\"$text\" style=\"width: ".$length."ch\"> ";
//! Might be worth trying to play with attributes of the textfield, not working out of the box though.
//			return "<textarea cols=\"".$length."\" rows=\"1\" style=\"overflow: visible\" placeholder=\"$text\"></textarea>";
	}, $in);

	return $out;
}

function ham_inputs_button($in, $cfg = null)
{
	//! Get configuration
	$inputLeft   = $cfg->get('inputButtonLeft');
	$inputRight  = $cfg->get('inputButtonRight');

	//! Quote for regex delimiter
	$inputLeftQ  = preg_quote($inputLeft, "/");
	$inputRightQ = preg_quote($inputRight, "/");

	$out = preg_replace_callback(
//		"/(\s*)$inputLeftQ([_a-zA-Z0-9]*)$inputRightQ(\s*)/m",
		"/$inputLeftQ([_a-zA-Z0-9]*)$inputRightQ/m",
		function ($m) use($cfg,$inputLeft,$inputRight) {

			$text = $m[1];
			$length = strlen($inputLeft) + strlen($text) +
				strlen($inputRight) - 1;

			if (strtolower($text) === "reset") {
				$type = "reset";
			} else {
				$type = "submit";
			}

                        return "$inputLeft<input class=\"hamInputButton\" type=\"$type\" size=$length value=\"$text\">$inputRight";
	}, $in);

	return $out;
}

?>

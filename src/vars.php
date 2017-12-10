<?php

function ham_vars($in, $cfg = null)
{
	$varLeft   = $cfg->get('varLeft');
	$varLeftQ  = preg_quote($varLeft, "/");
	$varRight  = $cfg->get('varRight');
	$varRightQ = preg_quote($varRight, "/");

	$form = $cfg->get('currentForm');

	$out = preg_replace_callback(
		"/([ ]*)$varLeftQ([_a-zA-Z0-9]*)$varRightQ([ ]*)/m",
		function ($m) use($cfg,$varLeft,$varRight,$form) {

			$name = $m[2];

			if ($form !== null) {
				$text = ham_form_get($form, $name, $cfg);
			} else {
				$text = $m[2];
			}

			$length = strlen($varLeft) + strlen($m[2]) +
				strlen($varRight) + strlen($m[3]) - 1;

			//! The additional style rule is needed because size is implemented differently on different fonts (and even browsers). It fixes the issue that the input width appears larger on Google Chrome but not on w3m (or links2). lynx does not seem to have this issue.
			return "$m[1]<input class=\"hamVariable\" type=\"text\" size=$length name=\"$text\" value=\"$text\" style=\"width: ".$length."ch\" readonly> ";
//! Might be worth trying to play with attributes of the textfield, not working out of the box though.
//			return "<textarea cols=\"".$length."\" rows=\"1\" style=\"overflow: visible\" placeholder=\"$text\"></textarea>";
	}, $in);

	return $out;

//	$out = preg_replace_callback(
//		"/([ ]*)$varLeftQ([_a-zA-Z0-9]*)$varRightQ([ ]*)/m",
//		function ($m) use($cfg,$varLeft,$varRight,$form) {
//
//			$name = $m[2];
//			$valLength = strlen($m[2]);
//
//			if ($form !== null) {
//				$text = ham_form_get($form, $name, $cfg);
//			} else {
//				$text = $m[2];
//			}
//
//			$length = strlen($varLeft) + $valLength +
//				strlen($varRight) + strlen($m[3]) - 1;
//			$spaces = 
//
//			return "$m[1]<span class=\"hamVariable\">$text</span>$m[3]";
//	}, $in);
//
//	return $out;
}

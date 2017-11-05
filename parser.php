<?php

//! Process the given string
function ham_parser($in, $cfg = null)
{
	$out = $in;

	//! Remove all comments
	$out = ham_parser_strip($out, $cfg);

	//! Handle overall layout
	$out = ham_layout($out, $cfg);

//	//! Parse individual elements
//	$out = ham_links($out, $cfg);

	//! Replace input elements
	$out = ham_inputs($out, $cfg);
	
	//! Replace variables

	return $out;
}

//! Preprocess the given string
function ham_parser_strip($in, $cfg = null)
{
	$comment = ham_config_get('comment', $cfg, "#");
	$nl = PHP_EOL;

	$out = preg_replace("/$comment(.*)$nl/", "", $in);

	return $out;
}

?>

<?php

//! Process the given string
function ham_parser($in, $opts = null)
{
	$out = $in;

	//! Remove all comments
	$out = ham_parser_strip($out);

	//! Handle overall layout
	$out = ham_layout($out, $opts);

//	//! Parse individual elements
//	$out = ham_links($out, $opts);

	//! Replace input elements
	$out = ham_inputs($out, $opts);
	
	//! Replace variables

	return $out;
}

//! Preprocess the given string
function ham_parser_strip($in, $opts = null)
{
	$comment = ham_option('comment', $opts, "#");
	$nl = PHP_EOL;

	$out = preg_replace("/$comment(.*)$nl/", "", $in);

	return $out;
}

?>

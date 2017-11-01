<?php

//! Preprocess the given string
function ham_parser($in, $opts = null)
{
	$out = $in;

	//! Handle overall layout
	$out = ham_layout($out, $opts);

	//! Parse individual elements
	$out = ham_links($out, $opts);
	$out = ham_inputs($out, $opts);
	
	//! Replace variables

	return $out;
}



?>

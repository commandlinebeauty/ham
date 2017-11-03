<?php

//! Preprocess the given string
function ham_parser($in, $opts = null)
{
	$out = $in;

	//! Remove all comments
	$out = ham_print_strip($out);

	//! Handle overall layout
	$out = ham_layout($out, $opts);

	//! Parse individual elements
//	$out = ham_links($out, $opts);

	//! Replace input elements
//	$out = ham_inputs($out, $opts);
	
	//! Replace variables

	return $out;
}



?>

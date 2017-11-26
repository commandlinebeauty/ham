<?php

function ham_parse($in, $cfg = null)
{
	$out = $in;

	$out = ham_parse_htmlentities($out, $cfg);
	$out = ham_parse_hamentities($out, $cfg);

	return $out;
}

function ham_parse_htmlentities($in, $cfg = null)
{
	return htmlspecialchars($in, ENT_COMPAT | ENT_HTML5, 'UTF-8');
}

function ham_parse_hamentities($in, $cfg = null)
{
	$out = $in;

	//! Replace links
	$out = ham_links($out, $cfg);
	
	//! Replace input elements
	$out = ham_inputs($out, $cfg);

	return $out;
}

?>

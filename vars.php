<?php
//	$hamOpts = array(
//		title     => "H.A.M.",
//		linkLeft  => "[",
//		linkRight => "]"
//	);

function ham_option($name, $opts, $default = null)
{
	if ($opts && array_key_exists($name, $opts)) {
		return $opts[$name];
	} else {
		if ($default) {
			return $default;
		} else {
			return "";
		}
	}
}

?>

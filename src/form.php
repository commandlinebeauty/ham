<?php

function ham_form_get($form, $field, $cfg = null)
{
	$status = 0;

	$cmd = "$form $field";
	
	unset($out);
	
	exec(escapeshellcmd($cmd), $out, $status);
	
	if ($status != 0) {
		//! An error occured
		throw new Exception("Error on command \"$cmd\"!");
	}

	return $out[0];
}

<?php

$hamFormFieldHidden = array(
	'hamFormLabel',
	'hamBoxAction'
);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	$label = $_POST['hamFormLabel'];

	if (array_key_exists('hamFormType', $_POST)) {
		$type  = $_POST['hamFormType'];
	} else {
		$type = 'form';
	}

	if ($type !== null && $type === 'action') {
		//! Check if this is an action box
		$cmd = $label;

		$status = 0;
		unset($out);

		exec(escapeshellcmd($cmd), $out, $status);

		if ($status != 0) {
			//! An error occured
			throw new Exception("Error on command \"$cmd\"!");
		}
		
	} else if ($label !== null && file_exists($label)) {
		//! Check if a script for handling this POST exists
		foreach (array_keys($_POST) as $field) {

			if (in_array($field, $hamFormFieldHidden)) {
				continue;
			}

			$cmd = "$label $field " . $_POST[$field];

			$status = 0;
			unset($out);

			exec(escapeshellcmd($cmd), $out, $status);

			if ($status != 0) {
				//! An error occured
				throw new Exception("Error on command \"$cmd\"!");
			}
		}
	}
}

?>

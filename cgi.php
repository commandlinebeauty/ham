<?php

$hamFormFieldHidden = array(
	'hamFormLabel'
);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	$label = $_POST['hamFormLabel'];

	//! Check if a script for handling this POST exists
	if ($label != null && file_exists($label)) {

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

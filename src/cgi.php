<?php

//! CGI handling class
class hamCGI
{
	//! Hidden fields with additional information
	private $hiddenFields = array(
		'hamFormLabel',
		'hamBoxAction'
	);

	//! Execution status of a submitted form (0 means success)
	private $formStatus = array();

	//! Initialize CGI
	public function __construct($cfg)
	{
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
		
			$label = $_POST['hamFormLabel'];

			if (array_key_exists('hamFormId', $_POST)) {
				$id = $_POST['hamFormId'];
			} else {
				$id = null;
			}
		
			if (array_key_exists('hamFormType', $_POST)) {
				$type  = $_POST['hamFormType'];
			} else {
				$type = 'form';
			}
		
			if ($type !== null && $type === 'action' && $id !== null) {
				//! Check if this is an action box
				$cmd = $label;
		
				$status = 0;
				unset($out);
		
				exec(escapeshellcmd($cmd), $out, $status);

				$this->formStatus[$id] = $status;

			} else if ($label !== null && file_exists($label)) {
				//! Check if a script for handling this POST exists
				foreach (array_keys($_POST) as $field) {
		
					if (in_array($field, $this->hiddenFields)) {
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
	}

	//! Get the execution status of a form
	public function getFormStatus($id) {
		//! Check for posted execution status of box with given id
		if(array_key_exists($id, $this->formStatus)) {

			return $this->formStatus[$id];
		} else {
			return null;
		}
	}
}

?>

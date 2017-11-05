<?php

function ham_header($cfg = null)
{
	$title = ham_config_get('title', $cfg);

	return "
<!DOCTYPE html>
<html>
	<head>
		<title>
			$title
		</title>

		<link rel='stylesheet' type='text/css'
			href='ham.css' />
	</head>

	<body>
	";
}

?>

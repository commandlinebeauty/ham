<?php

function ham_header($cfg)
{
	$title = $cfg->get('title');

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

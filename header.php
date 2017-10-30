<?php

function ham_header($opts = null)
{
	$title = ham_option('title', $opts);

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

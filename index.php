<?php
	include 'ham.php';

	echo ham_file('ham2.txt', array(
		'debug' => true,
		'title' => 'H.A.M.',
		'page' => true,
		'layout' => 'table',
		'void' => 'X'
	));
?>

<?php
	include 'ham.php';

	echo ham_parse_file('ham.txt', array(
//		'title' => 'H.A.M.',
		'page' => true
	));
?>

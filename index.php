<?php
	include 'ham.php';

	echo ham_file('ham.txt', array(
		'title' => 'H.A.M.',
		'page' => true,
//		'layout' => 'table'
	));
?>

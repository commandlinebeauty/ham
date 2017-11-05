<?php
	include 'ham.php';

	echo ham_file('doc.txt', array(
//		'debug' => true,
		'title' => 'H.A.M.',
		'page' => true,
		'layout' => 'plain',
		'void' => ' '
	));
?>

<?php
	include 'ham.php';

	$hamFile = new hamFile(
		'doc.txt', array(
			//! Enable debugging
//			'debug' => true,
			//! Set page title
			'title' => 'H.A.M.',
			//! Render whole page
			'page' => true,
			//! Set layout
//			'layout' => 'plain',
			'layout' => 'table',
			//! Fill voids with this character
			'void' => ' '
		)
	);

	$hamFile->render();
?>

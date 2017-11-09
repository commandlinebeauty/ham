<?php
	include 'ham.php';

	$ham = new ham(file_get_contents('doc.txt'),
		array(
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

	echo $ham->render();
?>

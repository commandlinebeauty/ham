<?php
//! @file H.A.M. Documentation {doc}
//! @author Fritz-Walter Schwarm
//! @usage Access this file from your browser to see the documentation

	include '../src/ham.php';

	$ham = new ham(file_get_contents('ham.txt'),
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
			'void' => ' ',
			//! Disable box borders
//			'boxBorder' => false
		)
	);

	echo $ham->render();
?>

<?php
//! @file index.php
//! @brief Renders the user documentation
//! @usage Access this file from your browser to see the documentation
//! @author Fritz-Walter Schwarm

	include '../src/ham.php';

	//! Main HAM class instance
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

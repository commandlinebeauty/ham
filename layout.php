<?php

//! Create HTML layout from ASCII boxes
function ham_layout($in, $cfg = null)
{
	$layout = ham_config_get('layout', $cfg);

	$buffer = ham_xy_init($in, $cfg);
	
	switch ($layout) {

	case 'table':
		$table = new hamTable($buffer, $cfg);
		$out = $table->print($buffer, $cfg);
		break;

	case 'rows':
//! TODO: Fix me!
		$rows = ham_layout_rows($buffer, $cfg);
		$out = $in;
		break;

	default:
		$out = "<pre class=hamPlainLayout>" . $in . "</pre>";
	}

	return $out;
}

//! Calculate table columns from boxes
function ham_layout_table($buffer, $cfg = null)
{

	//! Construct table layout

	return $layout;
}

//! Calculate table rows from boxes
function ham_layout_rows($buffer, $cfg = null)
{
	$boxes = ham_xy_boxes($buffer, $cfg);

	$rows_start = array();
	$rows_end = array();

	foreach ($boxes as $box) {
		$y0 = $box['y'][0];
		$y1 = $box['y'][2];

		if (count($rows_end) === 0 || $y0 > end($rows_end)) {
			//! The box starts lower than all previous boxes -> add new row
			array_push($rows_start, $y0);
			array_push($rows_end, $y1);

		} else if (count($rows_end) === 0 || $y1 > end($rows_end)) {
			//! The box ends lower than some previous box -> adjust row end
			$rows_end[-1] = $y1;
		}
	}
}

?>

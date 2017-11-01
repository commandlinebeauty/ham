<?php

//! Create HTML layout from ASCII boxes
function ham_layout($in, $opts = null)
{
	$layout = ham_option('layout', $opts, "plain");

	$buffer = ham_xy_init($in, $opts);
	$boxes = ham_xy_boxes($buffer, $opts);

	switch ($layout) {

	case 'table':
		$table = ham_layout_table($boxes, $opts);
		$out = $in;
		break;

	case 'rows':
//! TODO: Fix me!
		$rows = ham_layout_rows($boxes, $opts);
		$out = $in;
		break;

	default:
		$out = $in;
	}

	return $out;
}

//! Calculate table rows from boxes
function ham_layout_rows($boxes, $opts = null)
{
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

	foreach ($rows_start as $row) {
		echo "Have row_start: " . $row . "\n";
	}

	foreach ($rows_end as $row) {
		echo "Have row_end: " . $row . "\n";
	}
}

//! Calculate table columns from boxes
function ham_layout_table($boxes, $opts = null)
{
	$table = array();
	$rows = array(0);
	$cols = array(0);

	foreach ($boxes as $box) {
		$y0 = $box['y'][0];
		$y1 = $box['y'][2];
		$x0 = $box['x'][0];
		$x1 = $box['x'][2];

		//! Search for all rows
		foreach (array($y0, $y1) as $point) {
			if (!in_array($point, $rows)) {
				//! Add row
				array_push($rows, $point);
			}
		}

		//! Search for all columns
		foreach (array($x0, $x1) as $point) {
			if (!in_array($point, $cols)) {
				//! Add column
				array_push($cols, $point);
			}
		}
	}

	if (!sort($rows)) {
		exception("Failed to sort rows!");
	}
	if (!sort($cols)) {
		exception("Failed to sort cols!");
	}

foreach ($rows as $row) {
	echo "Have rows: " . $row . "\n";
}

foreach ($cols as $col) {
	echo "Have cols: " . $col . "\n";
}

	//! Set rowspan and colspan
	foreach ($boxes as $box) {
		$y0 = $box['y'][0];
		$y1 = $box['y'][2];
		$x0 = $box['x'][0];
		$x1 = $box['x'][2];

		$row_start = array_search($y0, $rows);
		$row_stop = array_search($y1, $rows);
		$col_start = array_search($x0, $cols);
		$col_stop = array_search($x1, $cols);

		$rowspan = $row_stop - $row_start;
		$colspan = $col_stop - $col_start;

		echo "rowspan: " . $rowspan . "<br>";
		echo "colspan: " . $colspan . "<br>";

		$box['rowspan'] = $rowspan;
		$box['colspan'] = $colspan;

//TODO		$table = 
	}

	return $table;
}

?>

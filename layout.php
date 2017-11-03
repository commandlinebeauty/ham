<?php

//! Create HTML layout from ASCII boxes
function ham_layout($in, $opts = null)
{
	$layout = ham_option('layout', $opts, "plain");

	$buffer = ham_xy_init($in, $opts);
	
	switch ($layout) {

	case 'table':
		$table = ham_layout_table($buffer, $opts);
		$out = ham_print_table($table, $buffer, $opts);
		break;

	case 'rows':
//! TODO: Fix me!
		$rows = ham_layout_rows($buffer, $opts);
		$out = $in;
		break;

	default:
		$out = "<pre class=ham>" . $in . "</pre>";
	}

	return $out;
}

//! Calculate table rows from boxes
function ham_layout_rows($buffer, $opts = null)
{
	$boxes = ham_xy_boxes($buffer, $opts);

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

//	foreach ($rows_start as $row) {
//		echo "Have row_start: " . $row . "\n";
//	}
//
//	foreach ($rows_end as $row) {
//		echo "Have row_end: " . $row . "\n";
//	}
}

//! Calculate table columns from boxes
function ham_layout_table($buffer, $opts = null)
{
	$boxes = ham_xy_boxes($buffer, $opts);

	$lastrow = count($buffer) - 2;

	//! Search for last column
	$lastcol = 0;
	foreach ($buffer as $line) {

		$lastchar = count($line) - 1;

		if ($lastchar > $lastcol) {

			$lastcol = $lastchar;
		}
	}

echo "Lastrow: $lastrow\n";
echo "Lastcol: $lastcol\n";

	$rows = array(0, $lastrow);
	$cols = array(0, $lastcol);

	//! Add rows and columns resulting from boxes
	foreach ($boxes as $box) {
		$y0 = $box['y'][0];
		$y1 = $box['y'][2];
		$x0 = $box['x'][0];
		$x1 = $box['x'][2];

		//! Search for box rows
//		foreach (array($y0, $y1) as $point) {
			if (!in_array($y0, $rows)) {
				array_push($rows, $y0);
			}
			if (!in_array($y1, $rows)) {
				array_push($rows, $y1 + 1);
			}
//		}

		//! Search for box columns
//		foreach (array($x0, $x1) as $point) {
			if (!in_array($x0, $cols)) {
				//! Add column
				array_push($cols, $x0);
			}
			if (!in_array($x1, $cols)) {
				//! Add column
				array_push($cols, $x1 + 1);
			}
//		}
	}

	if (!sort($rows)) { exception("Failed to sort rows!"); }
	if (!sort($cols)) { exception("Failed to sort cols!"); }

echo "Have rows: ";
foreach ($rows as $row) {
	 echo $row . ", ";
}
echo "\n";

echo "Have cols: ";
foreach ($cols as $col) {
	 echo $col . ", ";
}
echo "\n";

	//! Construct table layout
	$layout = new tableLayout(count($rows)-1, count($cols)-1);

	$layout->setY($rows);
	$layout->setX($cols);

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

//		echo "rowspan: " . $rowspan . "<br>";
//		echo "colspan: " . $colspan . "<br>";

		$box['rowspan'] = $rowspan;
		$box['colspan'] = $colspan;

		//! Set covered coordinates
		$cell_cur = $layout->getCell($row_start, $col_start);
		$cell_cur->setBox(array($y0, $y1), array($x0, $x1));
		$cell_cur->setSpan($rowspan, $colspan);
		$cell_cur->setType(1);

		//! Set spans for cells covered by this box to zero and type void
//		for ($row = $row_start + 1; $row <= $row_stop; $row++) {
		for ($row = $row_start; $row < $row_stop; $row++) {

			for ($col = $col_start; $col < $col_stop; $col++) {

				if ($row != $row_start || $col != $col_start) {

					$cell = $layout->getCell($row, $col);
					$cell->setSpan(0, 0);
					$cell->setType(0);
				}
			}
		}
	}

	//! Go through all cells and fix missing values
	for ($row = 0; $row < count($rows) - 1; $row++) {

		if ($row == count($rows) - 2) {
			//! Last row
			$row_start = $rows[$row];
			$row_stop = $rows[$row+1];
		} else {
			$row_start = $rows[$row];
			$row_stop = $rows[$row+1]-1;
		}


		for ($col = 0; $col < count($cols) - 1; $col++) {

			$cell = $layout->getCell($row, $col);

	//		if ($cell->getType() < 0 && $cell->getColspan() > 0 && $cell->getRowspan() > 0) {
			if ($cell->getType() < 0) {

//				$cell->setBox(array($rows[$row], $rows[$row+1]), array($cols[$col], $cols[$col+1]));

echo "count(rows) = ".count($rows)."\n";
echo "count(cols) = ".count($cols)."\n";

				if ($col != count($cols) - 2) {
					$col_start = $cols[$col];
					$col_stop = $cols[$col+1]-1;
				} else {
					//! Last column
					$col_start = $cols[$col];
					$col_stop = $cols[$col+1];
				}

echo "$row_start, $row_stop";

				$cell->setBox(array($row_start, $row_stop), array($col_start, $col_stop));

				$cell->setSpan(1, 1);
				$cell->setType(2);
			}
		}
	}

	return $layout;
}

?>

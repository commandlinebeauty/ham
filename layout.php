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

//! Calculate table columns from boxes
function ham_layout_table($buffer, $opts = null)
{
	//! Boxes seperated by edges
	$boxes = ham_xy_boxes($buffer, $opts);

	//! Size of xy buffer (number of chars in y and x direction)
	$xysize = ham_xy_size($buffer, $opts);
	$y_size = $xysize[0];
	$x_size = $xysize[1];

	$y_grid = array(0, $y_size);
	$x_grid = array(0, $x_size);

	//! Add gridpoints resulting from boxes
	foreach ($boxes as $box) {
		$y0 = $box['y'][0];
		$y1 = $box['y'][2];
		$x0 = $box['x'][0];
		$x1 = $box['x'][2];

		//! Add a point at the start and one char after the end of each box
		array_push($y_grid, $y0);
		array_push($y_grid, $y1+1);
		array_push($x_grid, $x0);
		array_push($x_grid, $x1+1);
	}

	//! Sort grids and remove double points
	if (!sort($y_grid)) { exception("Failed to sort y-grid!"); }
	if (!sort($x_grid)) { exception("Failed to sort x-grid!"); }
	if (!$y_grid = array_unique($y_grid)) { exception("Failed to unique y-grid!"); }
	if (!$x_grid = array_unique($x_grid)) { exception("Failed to unique x-grid!"); }

	//! This is important because otherwise the indizes (keys) are numbered incorrectly
	$y_grid = array_values($y_grid);
	$x_grid = array_values($x_grid);

echo "Have y_grid: ";
foreach ($y_grid as $key => $value) {
	 echo $value.", ";
}
echo "\n";

echo "Have x_grid: ";
foreach ($x_grid as $x) {
	 echo $x . ", ";
}
echo "\n";

	$N_y = count($y_grid);
	$N_x = count($x_grid);

	//! Construct table layout
	$layout = new tableLayout($N_y-1, $N_x-1);
	$layout->setY($y_grid);
	$layout->setX($x_grid);

	//! Set box properties (rowspan, covered area, ...)
	foreach ($boxes as $box) {
		$y0 = $box['y'][0];
		$y1 = $box['y'][2];
		$x0 = $box['x'][0];
		$x1 = $box['x'][2];

		//! Search start/end rows/columns of box
		$row_start = array_search($y0, $y_grid, true);
		$row_stop = array_search($y1+1, $y_grid, true);
		$col_start = array_search($x0, $x_grid, true);
		$col_stop = array_search($x1+1, $x_grid, true);

		if ($row_stop === false || $row_stop <= $row_start) {
			exception("Could not find a row that should have been added before!");
		} else {
			$row_stop--;
		}

		if ($col_stop === false || $col_stop <= $col_start) {
			exception("Could not find a column that should have been added before!");
		} else {
			$col_stop--;
		}

		//! Calculate row/column span
		$row_span = $row_stop - $row_start + 1;
		$col_span = $col_stop - $col_start + 1;

		//! Fill cell parameters for box cell
		$cell_cur = $layout->getCell($row_start, $col_start);
		$cell_cur->setBox(array($y0, $y1), array($x0, $x1));
		$cell_cur->setSpan($row_span, $col_span);
		$cell_cur->setType(1);

		//! Set spans for cells covered by this box to void type
		for ($row = $row_start; $row <= $row_stop; $row++) {

			for ($col = $col_start; $col <= $col_stop; $col++) {

				if ($row != $row_start || $col != $col_start) {

					$cell = $layout->getCell($row, $col);
					$cell->setSpan(0, 0);
					$cell->setType(0);
				}
			}
		}
	}

	//! Go through all cells and fix missing values
	for ($row = 0; $row < $N_y - 1; $row++) {

		//! Start/stop coordinates for this row
		$y_start = $y_grid[$row];
		$y_stop = $y_grid[$row+1] - 1;
//		if ($row == $N_y - 2) {
//			//! Last row
//			$y_start = $y_grid[$row];
//			$y_stop = $y_grid[$row+1];
//		} else {
//			$y_start = $y_grid[$row];
//			$y_stop = $y_grid[$row+1]-1;
//		}


		for ($col = 0; $col < $N_x - 1; $col++) {

			$cell = $layout->getCell($row, $col);

			if ($cell->getType() < 0) {
				//! This cell is uninitialized
				$x_start = $x_grid[$col];
				$x_stop = $x_grid[$col+1]-1;
//				if ($col == $N_x - 2) {
//					//! Last column
//					$x_start = $x_grid[$col];
//					$x_stop = $x_grid[$col+1];
//				} else {
//					$x_start = $x_grid[$col];
//					$x_stop = $x_grid[$col+1]-1;
//				}

echo "y_start: ".$y_start."\n";
echo "x_start: ".$x_start."\n";
echo "y_stop: ".$y_stop."\n";
echo "x_stop: ".$x_stop."\n";

				$cell->setBox(array($y_start, $y_stop), array($x_start, $x_stop));
				$cell->setSpan(1, 1);
				$cell->setType(2);
			} else {
			}
		}
	}

	return $layout;
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

?>

<?php

abstract class xyCornerType
{
	const UPPER_LEFT  = 0;
	const UPPER_RIGHT = 1;
	const LOWER_LEFT  = 2;
	const LOWER_RIGHT = 3;
}

//! Initialize the xy-buffer
function ham_xy_init($content, $opts = null)
{
	$lines = explode(PHP_EOL, $content);

	$buffer = array_map(function($line) {
		return str_split($line);
	}, $lines);

	return $buffer;
}

//! Obtain a point from the xy-buffer
function ham_xy_get($x, $y, $buffer)
{
	if ($y >= count($buffer)) {
		return null;
	}

	if ($x >= count($buffer[$y])) {
		return null;
	}

	return $buffer[$y][$x];
}

////! Set a point of the xy-buffer
//function ham_xy_set($x, $y, $value)
//{
//}

//! Scan for box boundary clockwise
function ham_xy_boxes_scan($type, $buffer, $y, $x, &$pos, $opts)
{
	$topCorner     = ham_option('boxTopCorner',         $opts, ".");
	$bottomCorner  = ham_option('boxBottomCorner',      $opts, "'");
	$xEdge         = ham_option('boxHorizontalEdge',    $opts, "-");
	$yEdge         = ham_option('boxVerticalEdge',      $opts, "|");
	$bracketsLeft  = ham_option('boxEdgeBracketsLeft',  $opts, "[(");
	$bracketsRight = ham_option('boxEdgeBracketsRight', $opts, "])");

	$corner = array("", "");
	$bufHeight = count($buffer);

	switch ($type) {

	//! Scan from upper left to upper right corner
	case 0:
		$corner[0] = $topCorner;
		$corner[1] = $topCorner;
		$edge = $xEdge;
		$dy = 0;
		$dx = 1;

		break;

	//! Scan from upper right to lower right corner
	case 1:
		$corner[0] = $topCorner;
		$corner[1] = $bottomCorner;
		$edge = $yEdge;
		$dy = 1;
		$dx = 0;

		break;

	//! Scan from lower right to lower left corner
	case 2:
		$corner[0] = $bottomCorner;
		$corner[1] = $bottomCorner;
		$edge = $xEdge;
		$dy = 0;
		$dx = -1;

		break;

	//! Scan from lower left to upper left corner
	case 3:
		$corner[0] = $bottomCorner;
		$corner[1] = $topCorner;
		$edge = $yEdge;
		$dy = -1;
		$dx = 0;

		break;
	default:
		exception("Unknown box edge scan type");
		return false;
	}

	//! Test for start corner at current position
	if ($buffer[$y][$x] === $corner[0] &&
			$buffer[$y][$x+1] === $xEdge[0] &&
			$buffer[$y+1][$x] === $yEdge[0]) {

		//! Save corner coordinates
		$pos[$type][0] = $y;
		$pos[$type][1] = $x;

		$skip = 0;

		//! Search for next corner
		while (
			$y + $dy >= 0         &&
			$y + $dy < $bufHeight &&
			$x + $dx >= 0         &&
			$x + $dx < count($buffer[$y + $dy]) &&
			( $skip ||
				$buffer[$y + $dy][$x + $dx] === $edge ||
//! TODO: clean this mess! handle different types of brackets properly!!!
				$buffer[$y + $dy][$x + $dx] === $edge ||
				strpos($bracketsLeft, $buffer[$y + $dy]) !== FALSE
			)
		) {
			$y += $dy;
			$x += $dx;

echo $buffer[$y+$dy][$x+$dx];

			//! Test for end corner
			if ($buffer[$y + $dy][$x + $dx] === $corner[1]) {

				//! It's an edge!
				if ($type < 3) {
					$pos[$type+1][0] = $y;
					$pos[$type+1][1] = $x;
				}
				return true;
			}
		}
	}

	return false;
}

//! Retrieve positions and types of boxes
function ham_xy_boxes($buffer, $opts = null)
{
	$minHeight    = 3;
	$minWidth     = 3;
	$bufHeight    = count($buffer);
	$firstWidth   = count($buffer[0]);
	$lastWidth    = count($buffer[$bufHeight-1]);
	$boxes        = array();

	//! Scan buffer line by line
	for ($y = 0; $y < $bufHeight - $minHeight; $y++) {

		$y_tmp = $y;

		$lineWidth = count($buffer[$y]);

		//! Scan each line char by char
		for ($x = 0; $x < $lineWidth - $minWidth; $x++) {

			$pos = array(
				//!   y         , x
				array(0         , 0),
				array(0         , $firstWidth),
				array($bufHeight, $lastWidth),
				array($bufHeight, $lastWidth)
			);

			$y_start = $y;
			$x_start = $x;

			$pos[0][0] = $y;
			$pos[0][1] = $x;

			//! Search for edges
			for ($type = 0; $type < 4; $type++) {

				if (ham_xy_boxes_scan($type, $buffer, $y, $x, $pos, $opts)) {

echo "$type";
					if ($type == 3) {
						//! It's a box
						array_push($boxes, array(
							pos => $pos
						));

						//! Set x-coordinate to upper right corner
						$y = $y_start;
						$x = $pos[1][1];
					} else {
						$y = $pos[$type][0];
						$x = $pos[$type][1];
					}
				} else {
					//! Not a box, continue scanning...
					$y = $y_start;
					$x = $x_start;

					break;
				}
			}
		}
	}

	return $boxes;
}

?>

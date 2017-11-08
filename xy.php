<?php

abstract class xyCornerType
{
	const UPPER_LEFT  = 0;
	const UPPER_RIGHT = 1;
	const LOWER_LEFT  = 2;
	const LOWER_RIGHT = 3;
}

//! Initialize the xy-buffer
function ham_xy_init($content, $cfg = null)
{
	$lines = explode(PHP_EOL, $content);
	
	$buffer = array_map(function($line) {
		return str_split($line);
	}, $lines);
	

	return $buffer;
}

//! Obtain a point from the xy-buffer
function ham_xy_get($y, $x, $buffer, $cfg)
{
	$voidString = $cfg->get('void');

	$N_y = count($buffer);

	if ($y < 0) {
		$y = $N_y + $y;
	}

	if ($y >= $N_y) {
		return $voidString;
	}

	$N_x = count($buffer[$y]);

	if ($x < 0) {
		$x = $N_x + $x;
	}

	if ($x >= $N_x) {
		return $voidString;
	}

	return $buffer[$y][$x];
}

//! Obtain the content of a rectangle within a file
function ham_xy_file($rect, $file, $cfg)
{
	$voidString = $cfg->get('void');

	$out = "";

	$y0 = $rect['y'][0];
	$y1 = $rect['y'][1];

	$x0 = $rect['x'][0];
	$x1 = $rect['x'][1];

	$N_y = $y1 - $y0 + 1;
	$N_x = $x1 - $x0 + 1;

	$y = 0;
	$x = 0;

	if (!file_exists($file)) {
		$out = "File \"$file\" does not exist!\n";
		return $out;
	}

	$handle = fopen($file, "r");

	if ($handle) {

		for($y = 0; $y <= $y1; $y++) {

//			if ($y == $y0) {
//				$out .= ham_xy_rect(array(
//					'y' => array($y0, $y0),
//					'x' => array($x0, $x1)
//				), $buffer, $cfg);
//			}

			$line = fgets($handle);

			if ($y >= $y0) {

				if ($line !== false) {
					//! Use line from file
					$length = strlen($line);
					for($x = $x0; $x <= $x1; $x++) {
						if ($x < $length) {
							$out .= $line[$x];
						} else {
							$out .= $voidString;
						}
					}
				} else {
					//! Use empty line
					$out .= str_repeat($voidString, $N_x) . "\n";
				}
			}
		}
	
		fclose($handle);
	} else {
		$out = "Error reading from file \"$file\"!\n";
		return $out;
	} 

	return $out;
}

//! Obtain the content of a rectangle from the xy-buffer
function ham_xy_rect($rect, $buffer, $cfg = null)
{
	$out = "";

	for ($y = $rect['y'][0]; $y <= $rect['y'][1]; $y++) {

		for ($x = $rect['x'][0]; $x <= $rect['x'][1]; $x++) {

			$out .= ham_xy_get($y, $x, $buffer, $cfg);
		}

		if ($y != $rect['y'][1]) {
			$out .= PHP_EOL;
		}
	}

	return $out;
}

////! Set a point of the xy-buffer
//function ham_xy_set($x, $y, $value)
//{
//}

function ham_xy_size($buffer, $cfg = null)
{
	$lastrow = count($buffer) - 1;

	//! Search for last column
	$lastcol = 0;
	foreach ($buffer as $line) {
		$lastchar = count($line);
		if ($lastchar > $lastcol) { $lastcol = $lastchar; }
	}

	return array($lastrow, $lastcol);
}

function ham_xy_overlay($buffer, $rect, $overlay, $cfg = null)
{
	$out = "";

	$y0 = $rect['y'][0];
	$x0 = $rect['x'][0];

	for ($y = $y0; $y <= $rect['y'][1]; $y++) {

		for ($x = $x0; $x <= $rect['x'][1]; $x++) {

			$buffer[$y][$x] = ham_xy_get($y-$y0, $x-$x0, $overlay, $cfg);
		}

		if ($y != $rect['y'][1]) {
			$out .= PHP_EOL;
		}
	}

	return $buffer;
}

?>

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
function ham_xy_get($y, $x, $buffer, $cfg = null)
{
	$voidString = ham_config_get('void', $cfg);

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
function ham_xy_file($rect, $file, $cfg = null)
{
	$out = "";

	$handle = fopen($file, "r");

	if ($handle) {
		while (($line = fgets($handle)) !== false) {
			//TODO read rect from file, return as $out
		}
	
		fclose($handle);
	} else {
		// error opening the file.
		exception("Error reading file $file");
	} 
}

//! Obtain the content of a rectangle from the xy-buffer
function ham_xy_get_rect($rect, $buffer, $cfg = null)
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

?>

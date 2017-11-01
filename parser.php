<?php

//! Preprocess the given string
function ham_parser($in, $opts = null)
{
	$out = $in;

	//! Handle overall layout
	$out = ham_parser_layout($out, $opts);

	//! Parse individual elements
	$out = ham_parser_links($out, $opts);
	$out = ham_parser_inputs($out, $opts);
	
	//! Replace variables

	return $out;
}

//! Create table layout from ASCII boxes
function ham_parser_layout($in, $opts = null)
{
	$layout = ham_option('layout', $opts, "plain");

	$buffer = ham_xy_init($in, $opts);
	$boxes = ham_xy_boxes($buffer, $opts);

	switch ($layout) {
	case 'table':
		return ham_parser_layout_table($in, $opts);
	case 'rows':
		$rows = ham_parser_layout_rows($boxes, $opts);
		return $in;
	default:
		return $in;
	}
}

//! Calculate table rows from boxes
function ham_parser_layout_rows($boxes, $opts = null)
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
function ham_parser_layout_table($in, $opts = null)
{
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

		$box['rowspan'] = $row_stop - $row_start;
		$box['colspan'] = $col_stop - $col_start;
	}


}

//! Replace links (a/A-z/Z letters and digits within brackets)
function ham_parser_links($in, $opts = null)
{
	$linkLeft   = ham_option('linkLeft',  $opts, "[");
	$linkLeftQ  = preg_quote($linkLeft, "/");
	$linkRight  = ham_option('linkRight', $opts, "]");
	$linkRightQ = preg_quote($linkRight, "/");

	$out = preg_replace_callback(
//		"/(\s*)$linkLeftQ([^$linkRightQ]*)$linkRightQ(\s*)/m",
		"/(\s*)$linkLeftQ([a-zA-Z0-9]*)$linkRightQ(\s*)/m",
		function ($m) use($opts,$linkLeft,$linkRight) {

//			$length = array_sum(array_map('strlen', $m)) +
//				strlen($linkLeft) + strlen($linkRight);
			$name = $m[2];

                        return "$linkLeft<a href=\"#$name\" id=\"$name\">$name</a>$linkRight";
	}, $in);

	return $out;
}

//! Replace input elements
//! Text-type input within curled brackets,
//! button-type input within brackets.
function ham_parser_inputs($in, $opts = null)
{
	$out = $in;

	$out = ham_parser_inputs_text($out, $opts);
	$out = ham_parser_inputs_button($out, $opts);

	return $out;
}

function ham_parser_inputs_text($in, $opts = null)
{
	$inputLeft   = ham_option('inputTextLeft',  $opts, "{");
	$inputLeftQ  = preg_quote($inputLeft, "/");
	$inputRight  = ham_option('inputTextRight', $opts, "}");
	$inputRightQ = preg_quote($inputRight, "/");

	$out = preg_replace_callback(
		"/(\s*)$inputLeftQ([_a-zA-Z0-9]*)$inputRightQ(\s*)/m",
		function ($m) use($opts,$inputLeft,$inputRight) {

			$text = $m[2];
			$length = strlen($inputLeft) + strlen($text) +
				strlen($inputRight) + strlen($m[3]) - 1;

                        return "$m[1]<input type=\"text\" size=$length value=$text> ";
	}, $in);

	return $out;
}

function ham_parser_inputs_button($in, $opts = null)
{
	$inputLeft   = ham_option('inputButtonLeft',  $opts, "<");
	$inputLeftQ  = preg_quote($inputLeft, "/");
	$inputRight  = ham_option('inputButtonRight', $opts, ">");
	$inputRightQ = preg_quote($inputRight, "/");

	$out = preg_replace_callback(
		"/(\s*)$inputLeftQ([_a-zA-Z0-9]*)$inputRightQ(\s*)/m",
		function ($m) use($opts,$inputLeft,$inputRight) {

			$text = $m[2];
			$length = strlen($inputLeft) + strlen($text) +
				strlen($inputRight) + strlen($m[3]) - 1;

			if (strtolower($text) === "reset") {
				$type = "reset";
			} else {
				$type = "submit";
			}

                        return "$m[1]$inputLeft<input type=\"$type\" size=$length value=\"$text\">$inputRight";
	}, $in);

	return $out;
}

?>

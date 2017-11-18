<?php

function ham_debug_config($cfg)
{
	$out = "";
	$names = array('Name', 'Value');

	$out .= "
<table border=1>
	<tr>
	";

	foreach ($names as $name) {
		$out .= "
		<th>
			$name
		</th>
		";
	}

	$out .= "
	</tr>
	";

	$count = 0;

	foreach ($cfg->getArray() as $name => $value) {

		$out .= "
	<tr>
		<td>
			$name
		</td>
		<td align=center>
			$value
		</td>
	</tr>
	";
		$count++;
	}

	$out .= "</table>";

	return $out;
}

function ham_debug_boxes($boxes, $buffer, $cfg = null)
{
	$out = "";
	$names = array('label', 'type', 'y0', 'y1', 'x0', 'x1', 'Content', 'Children');

	$out .= "
<table border=1>
	<tr>
	";

	foreach ($names as $name) {
		$out .= "
		<th>
			$name
		</th>
		";
	}

	$out .= "
	</tr>
	";

	$count = 0;

	foreach ($boxes as $box) {

		$values = array(
			$box->getType(),
			$box->getY()[0], $box->getY()[2],
			$box->getX()[0], $box->getX()[2],
			"<pre>".
//				ham_entities($buffer->rect($box->getRect(), $buffer, $cfg), $cfg).
//				ham_entities($box->render($buffer, $cfg), $buffer, $cfg) .
				$box->render($buffer, $cfg) .
				"</pre>",
			$box->getChildCount() > 0 ?
				ham_debug_boxes($box->getLayout()->getBoxes(), $buffer, $cfg) :
				""
		);


		$out .= "
	<tr>
		<th>
			".$box->getLabel()."
		</th>
	";

		foreach ($values as $value) {
			$out .= "
		<td align=center>
			$value
		</td>
			";
		}
	$out .= "
	</tr>
	";
		$count++;
	}

	$out .= "</table>";

	return $out;
}

?>

<?php

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
			"<pre>".$buffer->rect(
				array(
					'y' => array(
						$box->getY()[0],
						$box->getY()[2]),
					'x' => array(
						$box->getX()[0],
						$box->getX()[2])
				), $buffer, $cfg
			)."</pre>",
			$box->getBoxCount() > 0 ?
				ham_debug_boxes($box->getBoxes(), $buffer, $cfg) :
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

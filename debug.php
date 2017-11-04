<?php

function ham_debug_boxes($boxes, $buffer, $opts = null)
{
	$names = array('y0', 'y1', 'x0', 'x1', 'Content');

	echo "
<table>
	<tr>
		<th>
		</th>
	";

	foreach ($names as $name) {
		echo "
		<th>
			$name
		</th>
		";
	}

	echo "
	</tr>
	";

	$count = 0;

	foreach ($boxes as $box) {

		$values = array(
			$box['y'][0], $box['y'][2],
			$box['x'][0], $box['x'][2],
			"<pre>".ham_xy_get_box(
				array(
					'y' => array($box['y'][0], $box['y'][2]),
					'x' => array($box['x'][0], $box['x'][2])
				),
				$buffer, $opts
			)."</pre>"
		);


		echo "
	<tr>
		<th>
			Box $count
		</th>
	";

		foreach ($values as $value) {
			echo "
		<td>
			$value
		</td>
			";
		}
	echo "
	</tr>
	";
		$count++;
	}

	echo "</table>";
}

?>

<?php

function ham_debug_boxes($boxes, $buffer, $opts = null)
{
	$names = array('label', 'type', 'y0', 'y1', 'x0', 'x1', 'Content');

	echo "
<table>
	<tr>
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
			$box->getType(),
			$box->getY()[0], $box->getY()[2],
			$box->getX()[0], $box->getX()[2],
			"<pre>".ham_xy_rect(
				array(
					'y' => array(
						$box->getY()[0],
						$box->getY()[2]),
					'x' => array(
						$box->getX()[0],
						$box->getX()[2])
				), $buffer, $opts
			)."</pre>"
		);


		echo "
	<tr>
		<th>
			".$box->getLabel()."
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

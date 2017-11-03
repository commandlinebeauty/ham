
<?php

function ham_print_strip($in, $opts = null)
{
	$comment = ham_option('comment', $opts, "#");
	$nl = PHP_EOL;

	$out = preg_replace("/$comment(.*)$nl/", "", $in);

	return $out;
}

function ham_print_table($table, $buffer, $opts = null)
{
	$out = "";

	$out .= "<table cellspacing=0 cellpadding=0>\n";

	for ($row = 0; $row < $table->getRows(); $row++) {

		$out .= "<tr>\n";

		for ($col = 0; $col < $table->getCols(); $col++) {

			$cell = $table->getCell($row, $col);
			$rowspan = $cell->getRowspan();
			$colspan = $cell->getColspan();
			$type = $cell->getType();

//			if ($colspan > 0 && $rowspan > 0) {
			if ($type > 0) {

				$out .= "\t<td rowspan=$rowspan colspan=$colspan>";

				$box = $cell->getBox();
				$content = ham_xy_get_box($box, $buffer, $opts);

				$out .= "<pre>";

				//! Replace HTML entities
//				$out .= $content;
				$out .= ham_entities($content, $opts);

				$out .= "</pre>";
				$out .= "</td>\n";
			}
		}

		$out .= "</tr>\n";
	}

	$out .= "</table>";

	return $out;
}

?>


<?php

function ham_print_table($table, $buffer, $opts = null)
{
	$out = "";

	$out .= "<table role=\"presentation\" border=0 cellspacing=0 cellpadding=0 class=\"hamTableLayout\">\n";

	for ($row = 0; $row < $table->getRows(); $row++) {

		$out .= "<tr>\n";

		for ($col = 0; $col < $table->getCols(); $col++) {

			$cell = $table->getCell($row, $col);
			$rowspan = $cell->getRowspan();
			$colspan = $cell->getColspan();
			$type = $cell->getType();

			if ($type > 0) {

				$out .= "\t<td rowspan=$rowspan colspan=$colspan>";

				if ($type == 1) {
					$out .= "<a href=\"#asdf\">";
				}

				$box = $cell->getBox();
				$content = ham_xy_get_box($box, $buffer, $opts);

				$out .= "<pre>";

				//! Replace HTML entities
				$out .= ham_entities($content, $opts);

				$out .= "</pre>";

				if ($type == 1) {
					$out .= "</a>";
				}

				$out .= "</td>";

			}

		}

		$out .= "</tr>\n";
	}

	$out .= "</table>";

	return $out;
}

?>

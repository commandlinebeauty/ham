<?php

class hamTable
{
	private $y = array();
	private $x = array();
	private $rows = 0;
	private $cols = 0;
	private $cells = array();

	public function __construct($buffer, $cfg = null)
	{
		//! Boxes seperated by edges
		$boxes = ham_boxes($buffer, $cfg);
	
		//! Size of xy buffer (maximum char indizes in y and x direction)
		$xysize = ham_xy_size($buffer, $cfg);
		$y_size = $xysize[0];
		$x_size = $xysize[1];
	
		$y_grid = array(0, $y_size);
		$x_grid = array(0, $x_size);
	
		//! Add gridpoints resulting from boxes
		foreach ($boxes as $box) {
			$y0 = $box->getY()[0];
			$y1 = $box->getY()[2];
			$x0 = $box->getX()[0];
			$x1 = $box->getX()[2];
	
			//! Add a point at the start and one char after the end of each box
			array_push($y_grid, $y0);
			array_push($y_grid, $y1+1);
			array_push($x_grid, $x0);
			array_push($x_grid, $x1+1);
		}
	
		//! Sort grids and remove double points
		if (!sort($y_grid)) { exception("Failed to sort y-grid!"); }
		if (!sort($x_grid)) { exception("Failed to sort x-grid!"); }
		if (!$y_grid = array_unique($y_grid)) { exception("Failed to unique y-grid!"); }
		if (!$x_grid = array_unique($x_grid)) { exception("Failed to unique x-grid!"); }
	
		//! This is important because otherwise the indizes (keys) are numbered incorrectly
		$y_grid = array_values($y_grid);
		$x_grid = array_values($x_grid);
	
		$N_y = count($y_grid);
		$N_x = count($x_grid);

		$this->rows = $N_y - 1;
		$this->cols = $N_x - 1;

echo "HURZ: " . $this->rows;

		//! Add all base cells
		for ($row = 0; $row < $this->rows; $row++) {

			$this->cells[$row] = array();

			for ($col = 0; $col < $this->cols; $col++) {
				$this->cells[$row][$col] = new hamTableCell(0, 0);
			}
		}

		$this->setY($y_grid);
		$this->setX($x_grid);
	
		//! Set box properties (rowspan, covered area, ...)
		foreach ($boxes as $box) {
			$y0 = $box->getY()[0];
			$y1 = $box->getY()[2];
			$x0 = $box->getX()[0];
			$x1 = $box->getX()[2];
	
			//! Search start/end rows/columns of box
			$row_start = array_search($y0, $y_grid, true);
			$row_stop = array_search($y1+1, $y_grid, true);
			$col_start = array_search($x0, $x_grid, true);
			$col_stop = array_search($x1+1, $x_grid, true);
	
			if ($row_stop === false || $row_stop <= $row_start) {
				exception("Could not find a row that should have been added before!");
			} else {
				$row_stop--;
			}
	
			if ($col_stop === false || $col_stop <= $col_start) {
				exception("Could not find a column that should have been added before!");
			} else {
				$col_stop--;
			}
	
			//! Calculate row/column span
			$row_span = $row_stop - $row_start + 1;
			$col_span = $col_stop - $col_start + 1;
	
			//! Fill cell parameters for box cell
			$cell_cur = $this->getCell($row_start, $col_start);
			$cell_cur->setRect(array($y0, $y1), array($x0, $x1));
			$cell_cur->setSpan($row_span, $col_span);
			$cell_cur->setType(1);
			$cell_cur->setBoxType($box->getType());
	
			//! Set spans for cells covered by this box to void type
			for ($row = $row_start; $row <= $row_stop; $row++) {
	
				for ($col = $col_start; $col <= $col_stop; $col++) {
	
					if ($row != $row_start || $col != $col_start) {
	
						$cell = $this->getCell($row, $col);
						$cell->setSpan(0, 0);
						$cell->setType(0);
						$cell->setBoxType(hamBoxType::BOX_TYPE_NONE);
					}
				}
			}

			//! Go through all cells and fix missing values
			for ($row = 0; $row < $N_y - 1; $row++) {
		
				//! Start/stop coordinates for this row
				$y_start = $y_grid[$row];
				$y_stop = $y_grid[$row+1] - 1;
		
				for ($col = 0; $col < $N_x - 1; $col++) {
		
					$cell = $this->getCell($row, $col);
		
					if ($cell->getType() < 0) {
						//! This cell is uninitialized
						$x_start = $x_grid[$col];
						$x_stop = $x_grid[$col+1]-1;
		
						$cell->setRect(
							array($y_start, $y_stop),
							array($x_start, $x_stop)
						);
						$cell->setSpan(1, 1);
						$cell->setType(2);
						$cell->setBoxType(hamBoxType::BOX_TYPE_NONE);
					} else {
					}
				}
			}
		}

	}

	public function print($buffer, $cfg = null)
	{
		$out = "";
	
		$out .= "<table role=\"presentation\" border=0 cellspacing=0 cellpadding=0 class=\"hamTableLayout\">\n";
	
		for ($row = 0; $row < $this->getRows(); $row++) {
	
			$out .= "<tr>\n";
	
			for ($col = 0; $col < $this->getCols(); $col++) {
	
				$cell = $this->getCell($row, $col);
				$rowspan = $cell->getRowspan();
				$colspan = $cell->getColspan();
				$type = $cell->getType();
				$boxtype = $cell->getBoxType();
				$boxlabel = $cell->getBoxLabel();
	
				if ($type > 0) {
	
					$out .= "\t<td rowspan=$rowspan colspan=$colspan>";
	
					if ($type == 1) {
						$out .= "<a href=\"#asdf\">";
					}

					$rect = $cell->getRect();

					switch ($boxtype) {

					case hamBoxType::BOX_TYPE_NONE:
					case hamBoxType::BOX_TYPE_ANY:
					case hamBoxType::BOX_TYPE_FORM:
						$content = ham_xy_rect($rect, $buffer, $cfg);
						break;

					case hamBoxType::BOX_TYPE_FILE:
						$file = $boxlabel;
						$content = ham_xy_file(array(
							'y' => array(
								0,
								$rect['y'][1] - $rect['y'][0] - 1
							),
							'x' => array(
								0,
								$rect['x'][1] - $rect['x'][0] - 1,
							)),
							$file,
							$cfg
						);
						break;
					
					case hamBoxType::BOX_TYPE_CMD:
//						$content = ham_xy_cmd(array(
//							'y' => array(
//								0,
//								$rect['y'][1] - $rect['y'][0] - 1
//							),
//							'x' => array(
//								0,
//								$rect['x'][1] - $rect['x'][0] - 1,
//							),
//							$file,
//							$cfg
//						);
						break;
					
					default:
						exception("Unknown box type $type!");
					}
	
					$out .= "<pre>";
	
					//! Replace HTML entities
					$out .= ham_entities($content, $cfg);
	
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

	public function getY() {
		return $this->y;
	}

	public function setY($y) {
		$this->y = $y;
	}

	public function getX() {
		return $this->x;
	}

	public function setX($x) {
		$this->x = $x;
	}

	public function getRows() {
		return $this->rows;
	}

	public function getCols() {
		return $this->cols;
	}

	public function getCell($row, $col) {
		return $this->cells[$row][$col];
	}

	public function setCell($row, $col, $cell) {
		$this->cells[$row][$col] = $cell;
	}
}

class hamTableCell
{
	private $y = array();
	private $x = array();
	private $rowspan = 1;
	private $colspan = 1;
	private $type = -1;
	private $boxtype = hamBoxType::BOX_TYPE_NONE;
	private $boxlabel;

	public function __construct($rowspan, $colspan) {
		$this->rowspan = $rowspan;
		$this->colspan = $colspan;
	}

	public function setSpan($rowspan, $colspan) {
		$this->setRowspan($rowspan);
		$this->setColspan($colspan);
	}

	public function getType() {
		return $this->type;
	}

	public function setType($type) {
		$this->type = $type;
	}

	public function getBoxType() {
		return $this->boxtype;
	}

	public function setBoxType($type) {
		$this->boxtype = $type;
	}

	public function getBoxLabel() {
		return $this->boxlabel;
	}

	public function setBoxLabel($label) {
		$this->boxlabel= $label;
	}

	public function getRect() {
		return array(
			'y' => $this->y,
			'x' => $this->x
		);
	}

	public function setRect($y, $x) {
		$this->y = $y;
		$this->x = $x;
	}

	public function setRowspan($rowspan) {
		$this->rowspan = $rowspan;
	}

	public function getRowspan() {
		return $this->rowspan;
	}

	public function setColspan($colspan) {
		$this->colspan = $colspan;
	}

	public function getColspan() {
		return $this->colspan;
	}
}

?>

<?php

//! Type of a table cell
abstract class hamCellType {
	//! Uninitialized cell
	const NONE = -1;
	//! This cell is not rendered (covered by another cell with row/colspan > 1)
	const VOID =  0;
	//! Cell contains a box
	const BOX  =  1;
	//! Cell contains background
	const BKG  =  2;
}

//! Layout the page as (possibly nested) HTML tables
class hamLayoutTable extends hamLayout
{
	private $rowspan = 0;     ///< Number of rows
	private $colspan = 0;     ///< Number of columns
	private $y = array();     ///< Coordinates of row borders
	private $x = array();     ///< Coordinates of column borders
	private $cells = array(); ///< All table cells of type #hamTableCell

	//! Initialize table layout
	public function __construct($buffer, $label, $cfg)
	{
		//! Call general layout constructor
		parent::__construct($buffer, $label, $cfg);
		
		$this->init($buffer, $cfg);
	}

	//! Render HTML table and return as string
	public function render($buffer, $cfg = null)
	{
		$out = "";

		$level = $this->getLevel();

		//! Table start tag
		$out .= "<table role='presentation' border=0 cellspacing=0 cellpadding=0 class='hamLayoutTable hamLayoutLevel$level'>\n";

		for ($row = 0; $row < $this->getRowspan(); $row++) {
	
			$out .= "<tr>";
	
			for ($col = 0; $col < $this->getColspan(); $col++) {

				$out .= $this->getCell($row, $col)->render($buffer, $cfg);
			}
	
			$out .= "</tr>\n";
		}
	
		$out .= "</table>";
	
		return $out;
	}

	//! Helper function for initialization
	public function init($buffer, $cfg = null)
	{
		//! Helper grid for table construction
		$valid = $buffer->getValid();
		$this->y = array($valid->getY(0), $valid->getY(1) + 1);
		$this->x = array($valid->getX(0), $valid->getX(1) + 1);

		//! Add a point at the start and one char after the end of each box
		foreach ($this->getBoxes() as $box) {

			array_push($this->y, $box->getY(0)    );
			array_push($this->y, $box->getY(2) + 1);
			array_push($this->x, $box->getX(0)    );
			array_push($this->x, $box->getX(2) + 1);
		}
	
		//! Sort grids and remove double points
		if (!sort($this->y)) { throw new Exception("Failed to sort y-grid!"); }
		if (!sort($this->x)) { throw new Exception("Failed to sort x-grid!"); }
		if (!$this->y = array_unique($this->y)) { throw new Exception("Failed to uniquify y-grid!"); }
		if (!$this->x = array_unique($this->x)) { throw new Exception("Failed to uniquify x-grid!"); }
	
		//! Important: Recalculate array indizes after sorting and removing double points
		$this->y = array_values($this->y);
		$this->x = array_values($this->x);
	
		//! One row/column between each gridpoint
		$this->rowspan = count($this->y) - 1;
		$this->colspan = count($this->x) - 1;

		//! Add all base cells
		for ($row = 0; $row < $this->rowspan; $row++) {

			$this->cells[$row] = array();

			for ($col = 0; $col < $this->colspan; $col++) {
				$this->cells[$row][$col] = new hamTableCell(0, 0);
			}
		}

		//! Set cell properties for all boxes
		foreach ($this->getBoxes() as $box) {

			//! Search start/end rows/columns of box
			$row_start = array_search($box->getY(0)    , $this->y, true);
			$row_stop  = array_search($box->getY(2) + 1, $this->y, true);
			$col_start = array_search($box->getX(0)    , $this->x, true);
			$col_stop  = array_search($box->getX(2) + 1, $this->x, true);
	
			if ($row_stop === false || $row_stop <= $row_start) {
				throw new Exception("Could not find a row that should have been added before!");
			}

	
			if ($col_stop === false || $col_stop <= $col_start) {
				throw new Exception("Could not find a column that should have been added before!");
			}

			$cell_cur = $this->getCell($row_start, $col_start);

			//! Set type and associated box
			$cell_cur->setType(hamCellType::BOX);
			$cell_cur->setBox($box);

			//! Set rectangle covered by cell
			$cell_cur->setRect($box->getRect());

			//! Set row/column span
			$cell_cur->setSpan(
				$row_stop - $row_start,
				$col_stop - $col_start
			);

			//! Set type of cells covered by this box to void
			for ($row = $row_start; $row < $row_stop; $row++) {
	
				for ($col = $col_start; $col < $col_stop; $col++) {
	
					if ($row !== $row_start || $col !== $col_start) {
	
						$cell = $this->getCell($row, $col);
						$cell->setType(hamCellType::VOID);
					}
				}
			}
		}

		//! Go through all cells and fix missing values
		for ($row = 0; $row < $this->rowspan; $row++) {
		
			//! Start/stop coordinates for this row
			$y_start = $this->y[$row];
			$y_stop = $this->y[$row+1] - 1;
		
			for ($col = 0; $col < $this->colspan; $col++) {
		
				$cell = $this->getCell($row, $col);
		
				if ($cell->getType() === hamCellType::NONE) {

					//! This cell is still uninitialized
					$x_start = $this->x[$col];
					$x_stop = $this->x[$col+1] - 1;

					$cell->setType(hamCellType::BKG);

					if ($cfg->get('tableUnify')) {
						//! Search for overlapping background (i.e. atm uninitialized) cells
						$rowspan_bkg = 1;
						$colspan_bkg = 1;
	
						//! Search for void columns right of the current cell
						for ($col_bkg = $col + 1; $col_bkg < $this->colspan; $col_bkg++) {
	
							$cell_bkg = $this->getCell($row, $col_bkg);
	
							if ($cell_bkg->getType() === hamCellType::NONE) {
								//! This cell is an adjacent background column
								$cell_bkg->setType(hamCellType::VOID);
								$colspan_bkg++;
							} else {
								break;
							}
						}
	
						$found = true;
	
						for ($row_bkg = $row + 1; $row_bkg < $this->rowspan; $row_bkg++) {
	
							for ($col_bkg = $col; $col_bkg < $col + $colspan_bkg; $col_bkg++) {
	
								$cell_bkg = $this->getCell($row_bkg, $col_bkg);
	
								if ($cell_bkg->getType() !== hamCellType::NONE) {
									$found = false;
									break;
								}
							}
	
							if ($found) {
								//! Go through row again and set cell values
								for ($col_bkg = $col; $col_bkg < $col + $colspan_bkg; $col_bkg++) {
									$cell_bkg = $this->getCell($row_bkg, $col_bkg);
									$cell_bkg->setType(hamCellType::VOID);
								}
	
								$rowspan_bkg++;
							} else {
								break;
							}
						}
	
						$y_stop = $this->y[$row+$rowspan_bkg] - 1;
						$x_stop = $this->x[$col+$colspan_bkg] - 1;
	
						$cell->setSpan($rowspan_bkg, $colspan_bkg);
					} else {
						$cell->setSpan(1, 1);
					}

					$cell->setRect(new hamRect(
						$y_start,
						$y_stop,
						$x_start,
						$x_stop
					));
				}
			}
		}
	}

	//! Get row border coordinates
	public function getY() {
		return $this->y;
	}

	//! Get row column border coordinates
	public function getX() {
		return $this->x;
	}

	//! Get number of rows
	public function getRowspan() {
		return $this->rowspan;
	}

	//! Get number of columns
	public function getColspan() {
		return $this->colspan;
	}

	//! Get a cell by its row and column
	public function getCell($row, $col) {
		return $this->cells[$row][$col];
	}

	//! Set a cell at a specific row and column
	public function setCell($row, $col, $cell) {
		$this->cells[$row][$col] = $cell;
	}
}

//! Describes on table cell
class hamTableCell
{
	private $type = hamCellType::NONE; ///< Cell type
	private $rowspan = 1;              ///< Number of rows covered by this cell
	private $colspan = 1;              ///< Number of columns covered by this cell
	private $box = null;               ///< Box contained in this cell
	private $rect;                     ///< Cell area of type #hamRect

	//! Initialize table cell
	public function __construct($rowspan, $colspan)
	{
		$this->rowspan = $rowspan;
		$this->colspan = $colspan;
	}

	//! Render the cell
	public function render($buffer, $cfg = null)
	{
		$out = "";
		$type = $this->getType();
		$rowspan = $this->getRowspan();
		$colspan = $this->getColspan();

		if ($type === hamCellType::BOX ||
			$type === hamCellType::BKG) {

			if ($type === hamCellType::BOX) {

				$label = $this->getBox()->getLabel();
			} else {
				$label = "";
			}

			$out .= "\n\t<td id=\"" . $label . "\" rowspan=$rowspan colspan=$colspan>";

			if ($type === hamCellType::BOX) {

				//! Make each cell a link if configured so
				if ($cfg->get('tableCellBoxLink')) {

					$out .= "<a href=\"#" . $label . "\">";
				}
                
				$out .= $this->getBox()->render($buffer, $cfg);

				//! Make each cell a link if configured so
				if ($cfg->get('tableCellBoxLink')) {

					$out .= "</a>";
				}
			} else {
				$rect = $this->getRect();
				$content = $buffer->rect($rect, $cfg);

				$out .= "<pre>";
				$out .= ham_parse_htmlentities($content, $cfg);
				$out .= "</pre>";
			}

			$out .= "</td>";
		}

		return $out;
	}

	//! Set number of rows and columns
	public function setSpan($rowspan, $colspan) {
		$this->setRowspan($rowspan);
		$this->setColspan($colspan);
	}

	//! Get number of rows
	public function getRowspan() {
		return $this->rowspan;
	}

	//! Set number of rows
	public function setRowspan($rowspan) {
		$this->rowspan = $rowspan;
	}

	//! Get number of columns
	public function getColspan() {
		return $this->colspan;
	}

	//! Set number of columns
	public function setColspan($colspan) {
		$this->colspan = $colspan;
	}

	//! Get cell type
	public function getType() {
		return $this->type;
	}

	//! Set cell type
	public function setType($type) {
		$this->type = $type;
	}

	//! Get box
	public function getBox() {
		return $this->box;
	}

	//! Set box
	public function setBox($box) {
		$this->box = $box;
	}

	//! Get covered area
	public function getRect() {
		return $this->rect;
	}

	//! Set covered area
	public function setRect($rect) {
		$this->rect = $rect;
	}
}

?>

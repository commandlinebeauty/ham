<?php

class hamTableLayout
{
	private $y = array();
	private $x = array();
	private $rows = 0;
	private $cols = 0;
	private $cells = array();

	public function __construct($rows, $cols) {

		$this->rows = $rows;
		$this->cols = $cols;

		//! Add all base cells
		for ($row = 0; $row < $rows; $row++) {

			$this->cells[$row] = array();

			for ($col = 0; $col < $cols; $col++) {
				$this->cells[$row][$col] = new hamTableCell(0, 0);
			}
		}
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

	public function getBox() {
		return array(
			'y' => $this->y,
			'x' => $this->x
		);
	}

	public function setBox($y, $x) {
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

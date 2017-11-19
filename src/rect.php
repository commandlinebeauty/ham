<?php

//! Stores information about a rectangle in buffer space
class hamRect
{
	private $y = array(0, 0); ///< y-coordinates
	private $x = array(0, 0); ///< x-coordinates

	//! Initialize rectangle
	public function __construct($y0, $y1, $x0, $x1)
	{
		$this->y[0] = $y0;
		$this->y[1] = $y1;
		$this->x[0] = $x0;
		$this->x[1] = $x1;
	}

	//! Test if the rectangle contains a given point
	public function contains($y, $x, $cfg = null)
	{
		if (
			$y >= $this->getY(0) &&
			$y <= $this->getY(1) &&
			$x >= $this->getX(0) &&
			$x <= $this->getX(1)
		) {
			return true;
		} else {
			return false;
		}
	}

	//! Return smaller (or larger for negativ values of $offset) rectangle
	public function offset($offset, $cfg = null)
	{
		return new hamRect(
			$this->getY(0)+$offset,
			$this->getY(1)-$offset,
			$this->getX(0)+$offset,
			$this->getX(1)-$offset
		);
	}

	//! Get y-coordinate 0/1
	public function getY($index) {
		if ($index < 0) {
			$index = -$index;
		}
		if ($index > 1) {
			$index = $index % 2;
		}
		return $this->y[$index];
	}

	//! Get x-coordinate 0/1
	public function getX($index) {
		if ($index < 0) {
			$index = -$index;
		}
		if ($index > 1) {
			$index = $index % 2;
		}
		return $this->x[$index];
	}
}

?>

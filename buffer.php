<?php

class hamBuffer
{
	//! Array of char arrays
	private $buffer;
	//! Number of lines
	private $y_size;
	//! Maximum number of characters per line
	private $x_size;
	//! Character for filling voids
	private $voidChar;

	public function __construct($content, $cfg = null)
	{
		if ($content === null || $content === '') {
			throw new Exception('Empty content!');
		}

		$this->voidChar = $cfg->get('void');

		$lines = explode(PHP_EOL, $content);
		
		$this->buffer = array_map(function($line) {
			if ($line) {
				return str_split($line);
			}
		}, $lines);

		$this->y_size = count($this->buffer);
		$this->x_size = 0;

		foreach ($this->buffer as $line) {
			$length = count($line);
			if ($length > $this->x_size) {
				$this->x_size = $length;
			}
		}
	}

	//! Obtain the given point from the buffer
	public function get($y, $x, $cfg = null)
	{
		if ($y < 0) {
			$y = $this->getSizeY() + $y;
		}

		if ($y >= $this->getSizeY()) {
			return $this->voidChar;
		}

		$length = $this->getWidth($y);

		if ($x < 0) {
			$x = $length + $x;
		}

		if ($x >= $length) {
			return $this->voidChar;
		}

		return $this->buffer[$y][$x];
	}

	//! Set the given point in the buffer
	public function set($y, $x, $value, $cfg = null)
	{
		if ($y < 0) {
			$y = $this->getSizeY() + $y;
		}

		$length = $this->getWidth($y);

		if ($x < 0) {
			$x = $length + $x;
		}

        	$this->buffer[$y][$x] = $value;
	}

	//! Obtain the content of a rectangle from the buffer
	public function rect($rect, $cfg = null)
	{
		$out = "";
	
		for ($y = $rect['y'][0]; $y <= $rect['y'][1]; $y++) {
	
			for ($x = $rect['x'][0]; $x <= $rect['x'][1]; $x++) {
	
				$out .= $this->get($y, $x, $cfg);
			}
	
			if ($y != $rect['y'][1]) {
				$out .= PHP_EOL;
			}
		}

		return $out;
	}

	//! Overlay given area of another buffer
	public function overlay($rect, $overlay, $cfg = null)
	{
		$y0 = $rect['y'][0];
		$x0 = $rect['x'][0];

		for ($y = $y0; $y <= $rect['y'][1]; $y++) {

			for ($x = $x0; $x <= $rect['x'][1]; $x++) {

				$this->buffer[$y][$x] = $overlay->get($y-$y0, $x-$x0, $cfg);
			}
		}
	}

	//! Maximum rectangle covered by this buffer
	public function getRect()
	{
		return array(
			'y' => array(0, $this->y_size),
			'x' => array(0, $this->x_size)
		);
	}

	//! Return buffer content as string
	public function getContent()
	{
		$content = $this->rect($this->getRect());

		return $content;
	}

	//! Getter/Setter methods
	public function getSizeY() {
		return $this->y_size;
	}

	public function getSizeX() {
		return $this->x_size;
	}

	public function getWidth($y) {
		return count($this->buffer[$y]);
	}
}

?>

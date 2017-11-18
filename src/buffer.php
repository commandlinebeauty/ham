<?php

class hamBuffer
{
	//! Array of char arrays
	private $buffer;

	//! Index of last line (count($buffer) - 1)
	private $y_max;

	//! Last character of the longest line
	private $x_max;

	//! Character for filling voids
	private $voidChar;

	//! The valid area of the buffer
	//! Characters outside this area are replaced by $voidChar chars
	private $valid;

	//! Transform the content into an array of arrays of chars
	public function __construct($content, $cfg)
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

		$this->y_max = count($this->buffer) - 1;

		$this->x_max = 0;

		foreach ($this->buffer as $line) {

//! FWS DANGER Why -2 and not -1? Seems to produce the right result though...
//			$cur = count($line) - 1;
//! FWS Actually no... it doesnt... does not work with FILE CMD boxes...
//			$cur = count($line) - 2;
//! TODO Fix extra char on the right side. This is probably not the right place for doing so.
			$cur = count($line) - 1;

			if ($cur > $this->x_max) {
				$this->x_max = $cur;
			}
		}

		$this->valid = $this->getMax();
	}

	//! Warning: This function actually changes the given point!
	public function validate(&$y, &$x, $cfg = null)
	{
		$rect = $this->getValid();

		//! Negativ index means: this many steps (+1) from the bottom
		if ($y < 0) {
			$y += $this->getSizeY();
		}

		//! Test if it is still negativ
		if ($y < 0) {
			$y -= $this->getSizeY();
			return false;
		}

		//! Point must be inside buffer and inside the valid rectangle
		if (
			$y <  $rect->getY(0) ||
			$y >  $rect->getY(1) ||
			$y >= $this->getSizeY()
		) {
			return false;
		}

		$length = $this->getWidth($y);

		//! Invert negative index (count from right side instead)
		if ($x < 0) {
			$x += $length;
		}

		//! Test if it is still negativ
		if ($x < 0) {
			$x -= $length;
			return false;
		}

		//! Point must be inside buffer and inside the valid rectangle
		if (
			$x <  $rect->getX(0) ||
			$x >  $rect->getX(1) ||
			$x >= $length
		) {
			return false;
		}

		return true;
	}

	//! Obtain the given point from the buffer
	public function get($y, $x, $cfg = null, &$success = null)
	{
		if (! $this->validate($y, $x, $cfg)) {

			if ($success !== null) {
				$success = false;
			}

			return $this->voidChar;
		}

		if ($success !== null) {
			$success = true;
		}

		return $this->buffer[$y][$x];
	}

	//! Set the given point in the buffer
	public function set($y, $x, $value, $cfg = null)
	{
		if (! $this->validate($y, $x, $cfg)) {

			return false;
		} else {
	        	$this->buffer[$y][$x] = $value;

			return true;
		}
	}

	//! Obtain the content of a rectangle from the buffer
	public function rect($rect, $cfg = null)
	{
		$out = "";

		$y_min = $rect->getY(0);
		$y_max = $rect->getY(1);
		$x_min = $rect->getX(0);
		$x_max = $rect->getX(1);

		for ($y = $y_min; $y <= $y_max; $y++) {

			for ($x = $x_min; $x <= $x_max; $x++) {

				$out .= $this->get($y, $x, $cfg);
			}

			if ($y != $rect->getY(1)) {
				$out .= PHP_EOL;
			}
		}

		return $out;
	}

	//! Overlay given area of another buffer
	public function overlay($rect, $overlay = null, $cfg = null)
	{
		$y0 = $rect->getY(0);
		$x0 = $rect->getX(0);
		$y1 = $rect->getY(1);
		$x1 = $rect->getX(1);

		for ($y = $y0; $y <= $y1; $y++) {

			for ($x = $x0; $x <= $x1; $x++) {

				if ($overlay !== null) {
					$this->set($y, $x,
						$overlay->get($y-$y0, $x-$x0, $cfg), $cfg);
//					$this->buffer[$y][$x] = $overlay->get($y-$y0, $x-$x0, $cfg);
				} else {
					$this->set($y, $x,
						$this->voidChar, $cfg);
//					$this->buffer[$y][$x] = $this->voidChar;
				}
			}
		}
	}

	//! Maximum rectangle covered by this buffer
	public function getMax()
	{
		return new hamRect(0, $this->y_max, 0, $this->x_max);
	}

	//! Getter/Setter methods
	public function getSizeY() {
		return $this->y_max + 1;
	}

	public function getSizeX() {
		return $this->x_max + 1;
	}

	//! Return valid rectangle
	public function getValid()
	{
		return $this->valid;
	}

	//! Set valid rectangle
	//! Set to full buffer size for $rect === null
	public function setValid($rect)
	{
		if ($rect === null) {

			$this->valid = $this->getMax();
		} else {
			$this->valid = $rect;
		}
	}

	//! Return buffer content as string
	public function getContent()
	{
		$content = $this->rect($this->getValid());

		return $content;
	}

	//! Width of one line
	public function getWidth($y) {
		return count($this->buffer[$y]);
	}
}

?>

<?php

//! Base class for all layout types
abstract class hamLayout
{
	//! Layout type
	private $type;
	//! Layout nesting level
	private $level;
	//! Boxes contained in this layout
	private $boxes = array();

	//! Should be called at the beginning of all inherited layout classes' contructors
	//! @param $buffer Buffer storing the ASCII content to be layouted (see #hamBuffer).
	//! @param $region Restrict layout work to this region of $buffer. The remaining valid area of the buffer will be interpreted as background. Use complete buffer if null is given.
	public function __construct($buffer, $cfg)
	{
		$this->level = $cfg->get('layoutLevel');

		$outer = $buffer->getValid();
		$region = $cfg->get('layoutScanRect');

		//! Limit scan region
		if ($region !== null) {
			$buffer->setValid($region);
		}

		//! Scan for boxes
		$this->boxes = $this->scan($buffer, $cfg);

		if ($region !== null) {
			$buffer->setValid($outer);
		}
	}

//	public function render($buffer, $cfg = null)
//	{
//		//! Common rendering tasks
//	}

	//! Scan valid area of buffer for boxes
	public function scan($buffer, $cfg)
	{
		$boxes = array();

		$rect = $buffer->getValid();
//echo "scanY0: " . $rect->getY(0) . "->" . $rect->getY(1) . "\n";
//echo "scanX0: " . $rect->getX(0) . "->" . $rect->getX(1) . "\n";

		//! Scan buffer line by line
		for ($y = $rect->getY(0); $y <= $rect->getY(1) - $cfg->get('boxHeightMin'); $y++) {

			$lineWidth = $buffer->getWidth($y);

			if ($lineWidth > $rect->getX(1)) {
				$lineWidth = $rect->getX(1) + 1;
			}
	
			//! Scan each line char by char
			for ($x = $rect->getX(0); $x < $lineWidth - $cfg->get('boxWidthMin'); $x++) {

				$skip = false;

				//! Skip point if it is already contained inside another box
				foreach ($boxes as $box) {
					if ($box->getRect()->contains($y, $x)) {
						$end = $box->getRect()->getX(1);
						$skip = true;
						break;
					}
				}

				//! Set x to first point after box
				if ($skip) {
					$x = $end + 1;
					continue;
				}
	
				$y_start = $y;
				$x_start = $x;
	
				//! Test each type individually
				$types = array(
					hamBoxType::PLAIN,
					hamBoxType::FORM,
					hamBoxType::FILE,
					hamBoxType::CMD
				);
	
				foreach ($types as $type) {
	
					$pos = array(
						'y' => array(0,0,0,0),
						'x' => array(0,0,0,0)
					);
	
					$break = false;
					$label = "";
					$border = true;
	
					//! Search for edges
					for ($dir = 0; $dir < 4; $dir++) {
		
						if ($this->edge($type, $dir, $buffer, $y, $x, $pos, $label, $border, $cfg)) {

							if ($dir == 3) {
								//! It's a box
								//! Scan for nested boxes recursively
								$box = new hamBox(
									$type,
									$label,
									$pos['y'],
									$pos['x'],
									$border,
									$buffer,
									$cfg
								);

								array_push($boxes, $box);

								//! Set coordinates to upper right corner
								$y = $y_start;
								$x = $pos['x'][1];
	
								$break = true;
							} else {
								//! Start at end corner and scan for the next edge
								$y = $pos['y'][$dir+1];
								$x = $pos['x'][$dir+1];
							}
						} else {
							//! Not a box, continue scanning...
							$y = $y_start;
							$x = $x_start;
							break;
						}
					}
	
					if ($break) {
						break;
					}
				}
			}
		}

		return $boxes;
	}

	//! Scan for box boundary clockwise
	private function edge($type, $dir, $buffer, $y, $x, &$pos, &$label, &$border, $cfg)
	{
		//! null means search for any type (FWS: clutter or useful feature?)
		if ($type === null) {
			$type = hamBoxType::ANY;
		}
	
		$delim = new hamBoxDelimiters($type, $cfg);
	
		$corner = array("", "");
		$bufHeight = $buffer->getSizeY();
	
		switch ($dir) {
	
		//! Scan from upper left to upper right corner
		case 0:
			$corner[0] = $delim->topCorner;
			$corner[1] = $delim->topCorner;
			$edge = $delim->xEdge;
			$dy = 0;
			$dx = 1;
			break;
	
		//! Scan from upper right to lower right corner
		case 1:
			$corner[0] = $delim->topCorner;
			$corner[1] = $delim->bottomCorner;
			$edge = $delim->yEdge;
			$dy = 1;
			$dx = 0;
			break;
	
		//! Scan from lower right to lower left corner
		case 2:
			$corner[0] = $delim->bottomCorner;
			$corner[1] = $delim->bottomCorner;
			$edge = $delim->xEdge;
			$dy = 0;
			$dx = -1;
			break;
	
		//! Scan from lower left to upper left corner
		case 3:
			$corner[0] = $delim->bottomCorner;
			$corner[1] = $delim->topCorner;
			$edge = $delim->yEdge;
			$dy = -1;
			$dx = 0;
			break;
		default:
			exception("Unknown box edge scan direction");
			return false;
		}
	
		//! Test for start corner at current position
		$skip = 0;
	
		if (
			(strpos($corner[0],            $buffer->get($y, $x, $cfg)) !== FALSE)   ||
			(strpos($delim->bracketLeft,   $buffer->get($y, $x, $cfg)) !== FALSE  &&
			     	$dx > 0 && $skip = 1)                                           ||
			(strpos($delim->bracketRight,  $buffer->get($y, $x, $cfg)) !== FALSE  &&
				$dx < 0 && $skip = 1)                                           ||
			(strpos($delim->bracketTop,    $buffer->get($y, $x, $cfg)) !== FALSE  &&
			     	$dy > 0 && $skip = 1)                                           ||
			(strpos($delim->bracketBottom, $buffer->get($y, $x, $cfg)) !== FALSE  &&
				$dy < 0 && $skip = 1)
		) {
			//! Start corner found -> save coordinates
			$pos['y'][$dir] = $y;
			$pos['x'][$dir] = $x;
	
			$y_next = $y + $dy;
			$x_next = $x + $dx;
	
			//! Search for next corner
			while (
				$y_next >= 0                      &&
				$y_next < $bufHeight              &&
				$x_next >= 0                      &&
				$x_next < $buffer->getWidth($y_next) &&
				(
				(strpos($edge, $buffer->get($y_next, $x_next, $cfg)) !== FALSE) ||
				(strpos($delim->bracketLeft,   $buffer->get($y_next, $x_next, $cfg)) !== FALSE &&
				     	($dx > 0) && ($skip = 1))                                                  ||
				(strpos($delim->bracketRight,  $buffer->get($y_next, $x_next, $cfg)) !== FALSE &&
				     	($dx < 0) && ($skip = 1))                                                  ||
				(strpos($delim->bracketTop,    $buffer->get($y_next, $x_next, $cfg)) !== FALSE &&
				     	($dy > 0) && ($skip = 1))                                                  ||
				(strpos($delim->bracketBottom, $buffer->get($y_next, $x_next, $cfg)) !== FALSE &&
				     	($dy < 0) && ($skip = 1))                                                  ||
				(strpos($delim->bracketLeft,   $buffer->get($y_next, $x_next, $cfg)) !== FALSE &&
				     	($dx < 0) && ($skip == 1 ? $border = false || true : true) && !($skip = 0))                                                 ||
				(strpos($delim->bracketRight,  $buffer->get($y_next, $x_next, $cfg)) !== FALSE &&
				     	($dx > 0) && ($skip == 1 ? $border = false || true : true) && !($skip = 0))                                                 ||
				(strpos($delim->bracketTop,    $buffer->get($y_next, $x_next, $cfg)) !== FALSE &&
				     	($dy < 0) && ($skip == 1 ? $border = false || true : true) && !($skip = 0))                                                 ||
				(strpos($delim->bracketBottom, $buffer->get($y_next, $x_next, $cfg)) !== FALSE &&
				     	($dy > 0) && ($skip == 1 ? $border = false || true : true) && !($skip = 0))                                                 ||
				($skip > 0 && $skip++)
				)
			) {
				if ($skip > 1 && $dir == 0) {
					//! Box header line
					$label .= $buffer->get($y_next, $x_next, $cfg);
				}
	
				$y += $dy;
				$x += $dx;
	
				$y_next = $y + $dy;
				$x_next = $x + $dx;

				//! Test for end corner
				if (
					$skip   <= 0         &&
					$y_next >= 0         &&
					$y_next < $bufHeight &&
					$x_next >= 0         &&
					$x_next < $buffer->getWidth($y_next) &&
					(strpos($corner[1], $buffer->get($y_next, $x_next, $cfg)) !== FALSE)
				) {
					//! It's an edge!
					if ($dir < 3) {
						$pos['y'][$dir+1] = $y_next;
						$pos['x'][$dir+1] = $x_next;
					}
	
					return true;
				}
			}
	
			
		}
	
		return false;
	}

	//! Getter/Setter methods
	public function getType() {
		return $this->type;
	}

	public function setType($type) {
		return $this->type = $type;
	}

	public function getBoxes() {
		return $this->boxes;
	}

	public function setBoxes($boxes) {
		$this->boxes = $boxes;
	}

	public function getLevel() {
		return $this->level;
	}

	public function setLevel($level) {
		return $this->level = $level;
	}
}

?>

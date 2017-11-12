<?php

//! Base class for all layout types
abstract class hamLayout
{
	//! Layout type
	private $type;
	private $boxes;

	public function __construct($buffer, $cfg)
	{
		//! Scan for boxes
		$this->boxes = $this->scan($buffer, $cfg);
	}

	//! Scan buffer for boxes
	public function scan($buffer, $cfg)
	{
		$boxes        = array();
	
		//! Scan buffer line by line
		for ($y = 0; $y < $buffer->getSizeY() - $cfg->get('boxHeightMin'); $y++) {
	
			$y_tmp = $y;
	
			$lineWidth = $buffer->getWidth($y);
	
			//! Scan each line char by char
			for ($x = 0; $x < $lineWidth - $cfg->get('boxWidthMin'); $x++) {
	
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
					$hidden = false;
	
					//! Search for edges
					for ($dir = 0; $dir < 4; $dir++) {
		
						if ($this->edge($type, $dir, $buffer, $y, $x, $pos, $label, $hidden, $cfg)) {

							if ($dir == 3) {
								//! It's a box
								array_push($boxes, new hamBox(
									$type,
									$label,
									$pos['y'],
									$pos['x'],
									$hidden,
									$cfg
								));
		
								//! Set coordinates to upper right corner
								$y = $y_start;
								$x = $pos['x'][1];
	
								$break = true;
							} else {
								//! Start at end corner
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
	
		if ($cfg->get('debug')) {
			ham_debug_boxes($boxes, $buffer, $cfg);
		}
	
		return $boxes;
	}

	//! Scan for box boundary clockwise
	private function edge($type, $dir, $buffer, $y, $x, &$pos, &$label, &$hidden, $cfg)
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
				     	($dx < 0) && ($skip == 1 ? $hidden = true : true) && !($skip = 0))                                                 ||
				(strpos($delim->bracketRight,  $buffer->get($y_next, $x_next, $cfg)) !== FALSE &&
				     	($dx > 0) && ($skip == 1 ? $hidden = true : true) && !($skip = 0))                                                 ||
				(strpos($delim->bracketTop,    $buffer->get($y_next, $x_next, $cfg)) !== FALSE &&
				     	($dy < 0) && ($skip == 1 ? $hidden = true : true) && !($skip = 0))                                                 ||
				(strpos($delim->bracketBottom, $buffer->get($y_next, $x_next, $cfg)) !== FALSE &&
				     	($dy > 0) && ($skip == 1 ? $hidden = true : true) && !($skip = 0))                                                 ||
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

//	public function render($buffer, $cfg = null)
//	{
//		//! Common rendering tasks
//	}

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
}

?>

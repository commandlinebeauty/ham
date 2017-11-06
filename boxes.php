<?php

abstract class hamBoxType {
	const BOX_TYPE_NONE = -1;
	const BOX_TYPE_ANY  =  0;
	const BOX_TYPE_FORM =  1;
	const BOX_TYPE_FILE =  2;
	const BOX_TYPE_CMD  =  3;
}

class hamBox
{
	private $type;
	private $y;
	private $x;

	public function __construct($type, $y, $x, $cfg = null) {
		$this->type = $type;
		$this->y = $y;
		$this->x = $x;
	}

	public function getType()
	{
		return $this->type;
	}

	public function setType($type = null)
	{
		if ($type === null) {
			$this->type = hamBoxType::BOX_TYPE_NONE;
		} else {
			$this->type = $type;
		}
	}

	public function getY()
	{
		return $this->y;
	}

	public function getX()
	{
		return $this->x;
	}

	public function getPos()
	{
		return array('y' => $this->y, 'x' => $this->x);
	}

	public function setPos($y, $x)
	{
		$this->y = $y;
		$this->x = $x;
	}
}

class hamBoxDelimiters
{
	public $topCorner     = "";
	public $bottomCorner  = "";
	public $yEdge         = "";
	public $xEdge         = "";
	public $bracketLeft   = "";
	public $bracketRight  = "";
	public $bracketTop    = "";
	public $bracketBottom = "";

	public function __construct($type, $cfg)
	{
		$this->setType($type, $cfg);
	}

	public function setType($type, $cfg)
	{
		//! Set needle strings according to box type
		switch ($type) {
	
		case hamBoxType::BOX_TYPE_NONE:

			break;
	
		case hamBoxType::BOX_TYPE_ANY:
	
			foreach (array(
				hamBoxType::BOX_TYPE_FORM,
				hamBoxType::BOX_TYPE_FILE,
				hamBoxType::BOX_TYPE_CMD
			) as $type_tmp) {
	
				$delim = new hamBoxDelimiters($type_tmp, $cfg);

				$this->topCorner     .= $delim->topCorner;
				$this->bottomCorner  .= $delim->bottomCorner;
				$this->yEdge         .= $delim->yEdge;
				$this->xEdge         .= $delim->xEdge;
				$this->bracketLeft   .= $delim->bracketLeft;
				$this->bracketRight  .= $delim->bracketRight;
				$this->bracketTop    .= $delim->bracketTop;
				$this->bracketBottom .= $delim->bracketBottom;

				unset($delim);
			}
			break;
	
		case hamBoxType::BOX_TYPE_FORM:

$this->topCorner      = ham_config_get('boxFormCornerTop',         $cfg);
$this->bottomCorner   = ham_config_get('boxFormCornerBottom',      $cfg);
$this->yEdge          = ham_config_get('boxFormEdgeVertical',      $cfg);
$this->xEdge          = ham_config_get('boxFormEdgeHorizontal',    $cfg);
$this->bracketLeft    = ham_config_get('boxFormEdgeBracketLeft',   $cfg);
$this->bracketRight   = ham_config_get('boxFormEdgeBracketRight',  $cfg);
$this->bracketTop     = ham_config_get('boxFormEdgeBracketTop',    $cfg);
$this->bracketBottom  = ham_config_get('boxFormEdgeBracketBottom', $cfg);
			break;
	
		case hamBoxType::BOX_TYPE_FILE:

$this->topCorner      = ham_config_get('boxFileCornerTop',         $cfg);
$this->bottomCorner   = ham_config_get('boxFileCornerBottom',      $cfg);
$this->yEdge          = ham_config_get('boxFileEdgeVertical',      $cfg);
$this->xEdge          = ham_config_get('boxFileEdgeHorizontal',    $cfg);
$this->bracketLeft    = ham_config_get('boxFileEdgeBracketLeft',   $cfg);
$this->bracketRight   = ham_config_get('boxFileEdgeBracketRight',  $cfg);
$this->bracketTop     = ham_config_get('boxFileEdgeBracketTop',    $cfg);
$this->bracketBottom  = ham_config_get('boxFileEdgeBracketBottom', $cfg);
			break;
	
		case hamBoxType::BOX_TYPE_CMD:

$this->topCorner      = ham_config_get('boxCmdCornerTop',          $cfg);
$this->bottomCorner   = ham_config_get('boxCmdCornerBottom',       $cfg);
$this->yEdge          = ham_config_get('boxCmdEdgeVertical',       $cfg);
$this->xEdge          = ham_config_get('boxCmdEdgeHorizontal',     $cfg);
$this->bracketLeft    = ham_config_get('boxCmdEdgeBracketLeft',    $cfg);
$this->bracketRight   = ham_config_get('boxCmdEdgeBracketRight',   $cfg);
$this->bracketTop     = ham_config_get('boxCmdEdgeBracketTop',     $cfg);
$this->bracketBottom  = ham_config_get('boxCmdEdgeBracketBottom',  $cfg);
			break;
	
		default:
			exception("Unknown box type $type!");
		}
	}
}

//! Retrieve positions and types of boxes
function ham_boxes($buffer, $cfg = null)
{
	$debug = ham_config_get('debug', $cfg);

	$minHeight    = 1;
	$minWidth     = 1;
	$bufHeight    = count($buffer);
	$firstWidth   = count($buffer[0]);
	$lastWidth    = count($buffer[$bufHeight-1]);
	$boxes        = array();

	//! Scan buffer line by line
	for ($y = 0; $y < $bufHeight - $minHeight; $y++) {

		$y_tmp = $y;

		$lineWidth = count($buffer[$y]);

		//! Scan each line char by char
		for ($x = 0; $x < $lineWidth - $minWidth; $x++) {

			$y_start = $y;
			$x_start = $x;

			//! Test each type individually
			$types = array(
				hamBoxType::BOX_TYPE_FORM,
				hamBoxType::BOX_TYPE_FILE,
				hamBoxType::BOX_TYPE_CMD
			);

			foreach ($types as $type) {

				$pos = array(
					'y' => array(0,0,0,0),
					'x' => array(0,0,0,0)
				);

				$break = false;

				//! Search for edges
				for ($dir = 0; $dir < 4; $dir++) {
	
					if (ham_boxes_scan(
						$type, $dir, $buffer, $y, $x, $pos, $cfg
						)) {
	
						if ($dir == 3) {
							//! It's a box
							array_push($boxes, new hamBox(
								$type,
								$pos['y'],
								$pos['x'],
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

	if ($debug) {
		ham_debug_boxes($boxes, $buffer, $cfg);
	}

	return $boxes;
}

//! Scan for box boundary clockwise
function ham_boxes_scan($type, $dir, $buffer, $y, $x, &$pos, $cfg)
{
	//! null means search for any type (FWS: clutter or useful feature?)
	if ($type === null) {
		$type = hamBoxType::BOX_TYPE_ANY;
	}

	$delim = new hamBoxDelimiters($type, $cfg);

	$corner = array("", "");
	$bufHeight = count($buffer);

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
	if (
		strpos($corner[0],          $buffer[$y][$x]) !== FALSE ||
		strpos($delim->bracketLeft, $buffer[$y][$x]) !== FALSE ||
		strpos($delim->bracketTop,  $buffer[$y][$x]) !== FALSE
	) {
		//! Start corner found -> save coordinates
		$pos['y'][$dir] = $y;
		$pos['x'][$dir] = $x;

		$skip = false;
		$y_next = $y + $dy;
		$x_next = $x + $dx;

		//! Search for next corner
		while (
			$y_next >= 0         &&
			$y_next < $bufHeight &&
			$x_next >= 0         &&
			$x_next < count($buffer[$y_next]) &&
			( $skip ||
			   (strpos($edge, $buffer[$y_next][$x_next]) !== FALSE) ||
			   (strpos($delim->bracketLeft, $buffer[$y_next][$x_next]) !== FALSE &&
					$dx > 0 && $skip = true) ||
			   (strpos($delim->bracketRight, $buffer[$y_next][$x_next]) !== FALSE &&
					$dx < 0 && $skip = true) ||
			   (strpos($delim->bracketTop, $buffer[$y_next][$x_next]) !== FALSE &&
					$dy > 0 && $skip = true) ||
			   (strpos($delim->bracketBottom, $buffer[$y_next][$x_next]) !== FALSE &&
					$dy < 0 && $skip = true) ||
			   (strpos($delim->bracketLeft, $buffer[$y_next][$x_next]) !== FALSE &&
					$dx < 0 && $skip = false) ||
			   (strpos($delim->bracketRight, $buffer[$y_next][$x_next]) !== FALSE &&
					$dx > 0 && $skip = false) ||
			   (strpos($delim->bracketTop, $buffer[$y_next][$x_next]) !== FALSE &&
					$dy < 0 && $skip = false) ||
			   (strpos($delim->bracketBottom, $buffer[$y_next][$x_next]) !== FALSE &&
					$dy > 0 && $skip = false)
			)
		) {
			$y += $dy;
			$x += $dx;

			$y_next = $y + $dy;
			$x_next = $x + $dx;

			//! Test for end corner
			if (
				$y_next >= 0         &&
				$y_next < $bufHeight &&
				$x_next >= 0         &&
				$x_next < count($buffer[$y_next]) &&
				$buffer[$y_next][$x_next] === $corner[1]
// (strpos($corner[1], $buffer[$y_next][$x_next]) !== FALSE)
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


?>

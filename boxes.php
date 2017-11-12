<?php

abstract class hamBoxType
{
	const NONE  = -1;
	const ANY   =  0;
	const PLAIN =  1;
	const FORM  =  2;
	const FILE  =  3;
	const CMD   =  4;

	//! The name generated here will be used for additional CSS styling classes
	public function getName($type)
	{
		switch ($type) {
		case -1:
			return 'hamBoxNone';
		case  0:
			return 'hamBoxAny';
		case  1:
			return 'hamBoxPlain';
		case  2:
			return 'hamBoxForm';
		case  3:
			return 'hamBoxFile';
		case  4:
			return 'hamBoxCmd';
		default:
			throw new Exception("Unknown box type \"$type\"!");
		}
	}
}

class hamBox
{
	private $type;
	private $label;
	private $y;
	private $x;
	private $hidden;

	public function __construct($type, $label, $y, $x, $hidden, $cfg = null)
	{
		$this->type = $type;
		$this->label = $label;
		$this->y = $y;
		$this->x = $x;
		$this->hidden = $hidden;
	}

	//! Returns the buffer content of this box
	//! without adding tags (e.g. for plain layout)
	public function rect($buffer, $cfg = null)
	{
		$rect = $this->getRect();
		
		return $buffer->rect($rect, $cfg);
	}

	public function render($buffer, $cfg = null)
	{
		$type = $this->getType();
		$label = $this->getLabel();
		$rect = $this->getRect();
		$typename = hamBoxType::getName($type);

		$out = "";
		$hideBorder = "";

		if ($this->hidden) {

			$y_size = $buffer->getSizeY();
			$x_size = $buffer->getSizeX();
			$voidChar = $cfg->get('void');
			$margin = 1;

			//! Delete border from local copy of buffer
			for ($y = $rect['y'][0]; $y <= $rect['y'][1]; $y++) {
				for ($x = $rect['x'][0]; $x <= $rect['x'][1]; $x++) {
					if (
						$y - $rect['y'][0] < $margin ||
						$rect['y'][1] - $y < $margin ||
						$x - $rect['x'][0] < $margin ||
						$rect['x'][1] - $x < $margin
					) {
						$buffer->set($y, $x, $voidChar, $cfg);
					}
				}
			}
			$hideBorder = " hamBoxHidden";
//FWS this is prob not a good idea
//			$rect['y'][0]++;
//			$rect['y'][1]--;
//			$rect['x'][0]++;
//			$rect['x'][1]--;
		}

		$content = $buffer->rect($rect, $cfg);

		switch ($this->getType()) {

		case hamBoxType::NONE:
		case hamBoxType::ANY:
		case hamBoxType::PLAIN:

			$out .= "<pre class=\"$typename$hideBorder\">";
			$out .= ham_entities($content, $cfg);
			$out .= "</pre>";
			break;

		case hamBoxType::FORM:

			$out .= "<form action=\"" .
				htmlspecialchars($_SERVER["PHP_SELF"]) . "#$label" .
				"\" method=\"post\">";

			$out .= "<input type=\"hidden\" name=\"hamFormLabel\" value=\"$label\">";

			$out .= "<pre class=\"$typename\">";
			$out .= ham_entities($content, $cfg);
			$out .= "</pre>";

			$out .= "</form>";
			break;

		case hamBoxType::FILE:

			$file = $this->getLabel();

			if (!file_exists($file)) {
				throw new Exception("File \"$file\" not found!");
			}

			$overlay = new hamBuffer(file_get_contents($file), $cfg);

			$tmp = clone $buffer;

			$tmp->overlay(
				//! Coordinates in buffer frame
				array(
					'y' => array(
						$rect['y'][0] + 1,
						$rect['y'][1] - 1
					),
					'x' => array(
						$rect['x'][0] + 1,
						$rect['x'][1] - 1
					)
				),
				//! Overlay buffer and configuration
				$overlay, $cfg
			);

			$content = $tmp->rect($rect, $cfg);

			$out .= "<pre class=\"$typename\">";
			$out .= ham_entities($content, $cfg);
			$out .= "</pre>";
			break;
		
		case hamBoxType::CMD:

			$cmd = $this->getLabel();
			$result = "";

			if ($cmd === null || $cmd === "") {
				throw new Exception("Can not execute empty command!");
			}

			exec(escapeshellcmd($cmd), $result);

			if ($result === null || count($result) <= 0) {
				throw new Exception("Empty output from command \"$cmd\"!");
			}

			$overlay = new hamBuffer(implode("\n",$result), $cfg);

			$tmp = clone $buffer;

			$tmp->overlay(
				//! Coordinates in buffer frame
				array(
					'y' => array(
						$rect['y'][0] + 1,
						$rect['y'][1] - 1
					),
					'x' => array(
						$rect['x'][0] + 1,
						$rect['x'][1] - 1
					)
				),
				//! Overlay buffer and configuration
				$overlay, $cfg
			);

			$content = $tmp->rect($rect, $cfg);

			$out .= "<pre class=\"$typename\">";
			$out .= ham_entities($content, $cfg);
			$out .= "</pre>";
			break;

		default:
			throw new Exception("Unknown box type $type!");
		}

		return $out;
	}

	//! Getter/Setter methods
	public function getType() {
		return $this->type;
	}

	public function setType($type = null) {
		if ($type === null) {
			$this->type = hamBoxType::NONE;
		} else {
			$this->type = $type;
		}
	}

	public function getLabel() {
		return $this->label;
	}

	public function setLabel($label) {

		$this->label = $label;
	}

	public function getY($index = null) {

		if ($index === null) {
			return $this->y;
		}

		return $this->y[$index];
	}

	public function getX($index = null) {

		if ($index === null) {
			return $this->x;
		}

		return $this->x[$index];
	}

	public function getRect() {

		return array(
			'y' => array($this->y[0], $this->y[2]),
			'x' => array($this->x[0], $this->x[2])
		);
	}

	public function setRect($rect) {
		$this->y[0] = $rect['y'][0];
		$this->y[1] = $rect['y'][0];
		$this->y[2] = $rect['y'][1];
		$this->y[3] = $rect['y'][1];
		$this->x[0] = $rect['x'][0];
		$this->x[1] = $rect['x'][1];
		$this->x[2] = $rect['x'][1];
		$this->x[3] = $rect['x'][0];
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
	
		case hamBoxType::NONE:
			break;
	
		case hamBoxType::ANY:
			//! Combine all delimiters
			foreach (array(
				hamBoxType::FORM,
				hamBoxType::FILE,
				hamBoxType::CMD
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

		case hamBoxType::PLAIN:

$this->topCorner      = $cfg->get('boxPlainCornerTop');
$this->bottomCorner   = $cfg->get('boxPlainCornerBottom');
$this->yEdge          = $cfg->get('boxPlainEdgeVertical');
$this->xEdge          = $cfg->get('boxPlainEdgeHorizontal');
$this->bracketLeft    = $cfg->get('boxPlainEdgeBracketLeft');
$this->bracketRight   = $cfg->get('boxPlainEdgeBracketRight');
$this->bracketTop     = $cfg->get('boxPlainEdgeBracketTop');
$this->bracketBottom  = $cfg->get('boxPlainEdgeBracketBottom');
			break;
	
		case hamBoxType::FORM:

$this->topCorner      = $cfg->get('boxFormCornerTop');
$this->bottomCorner   = $cfg->get('boxFormCornerBottom');
$this->yEdge          = $cfg->get('boxFormEdgeVertical');
$this->xEdge          = $cfg->get('boxFormEdgeHorizontal');
$this->bracketLeft    = $cfg->get('boxFormEdgeBracketLeft');
$this->bracketRight   = $cfg->get('boxFormEdgeBracketRight');
$this->bracketTop     = $cfg->get('boxFormEdgeBracketTop');
$this->bracketBottom  = $cfg->get('boxFormEdgeBracketBottom');
			break;
	
		case hamBoxType::FILE:

$this->topCorner      = $cfg->get('boxFileCornerTop');
$this->bottomCorner   = $cfg->get('boxFileCornerBottom');
$this->yEdge          = $cfg->get('boxFileEdgeVertical');
$this->xEdge          = $cfg->get('boxFileEdgeHorizontal');
$this->bracketLeft    = $cfg->get('boxFileEdgeBracketLeft');
$this->bracketRight   = $cfg->get('boxFileEdgeBracketRight');
$this->bracketTop     = $cfg->get('boxFileEdgeBracketTop');
$this->bracketBottom  = $cfg->get('boxFileEdgeBracketBottom');
			break;
	
		case hamBoxType::CMD:

$this->topCorner      = $cfg->get('boxCmdCornerTop');
$this->bottomCorner   = $cfg->get('boxCmdCornerBottom');
$this->yEdge          = $cfg->get('boxCmdEdgeVertical');
$this->xEdge          = $cfg->get('boxCmdEdgeHorizontal');
$this->bracketLeft    = $cfg->get('boxCmdEdgeBracketLeft');
$this->bracketRight   = $cfg->get('boxCmdEdgeBracketRight');
$this->bracketTop     = $cfg->get('boxCmdEdgeBracketTop');
$this->bracketBottom  = $cfg->get('boxCmdEdgeBracketBottom');
			break;
	
		default:
			throw new Exception("Unknown box type $type!");
		}
	}
}

?>

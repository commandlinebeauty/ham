<?php

abstract class hamBoxType
{
	const NONE   = -1;
	const ANY    =  0;
	const PLAIN  =  1;
	const FORM   =  2;
	const FILE   =  3;
	const CMD    =  4;
	const ACTION =  5;

	//! Return array of all real box types (no meta-types)
	public function getTypes($meta = false)
	{
		if ($meta) {
			$out = array(
				$this->NONE,
				$this->ANY
			);
		} else {
			$out = array();
		}

		array_push($out, hamBoxType::PLAIN);
		array_push($out, hamBoxType::FORM);
		array_push($out, hamBoxType::FILE);
		array_push($out, hamBoxType::CMD);
		array_push($out, hamBoxType::ACTION);

		return $out;
	}

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
		case  5:
			return 'hamBoxAction';
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
	private $border;

	private $layout = null;

	public function __construct($type, $label, $y, $x, $border, $buffer, $cfg)
	{
		$this->type = $type;
		$this->label = $label;
		$this->y = $y;
		$this->x = $x;
		$this->border = $border;

		//! Limit the valid and region of buffer to box area (order matters!)
		$outer = $buffer->getValid();
		$buffer->setValid($this->getRect());

		//! Limit scan region of buffer to inside box area
		//! This is necessary in order to avoid infinite recursion (box finds itself as child box)
		$oldScanRect = $cfg->get('layoutScanRect');
		$oldTableLevel = $cfg->get('layoutLevel');

		$cfg->set('layoutScanRect', $this->getRect()->offset(1));
		$cfg->set('layoutLevel', $oldTableLevel + 1);

		switch ($cfg->get('layout')) {

		case 'plain':
			$this->layout = new hamLayoutPlain($buffer, $cfg);
			break;

		case 'table':
			$this->layout = new hamLayoutTable($buffer, $cfg);
			break;

		default:
			error_log("Unknown layout type " . $cfg->get('layout') . "!");
			break;
		}

		//! Restore scan region and valid buffer region
		$cfg->set('layoutLevel', $oldTableLevel);
		$cfg->set('layoutScanRect', $oldScanRect);
		$buffer->setValid($outer);
	}

	public function render($buffer, $cfg = null)
	{
		$type = $this->getType();
		$label = $this->getLabel();
		$rect = $this->getRect();
		$typename = hamBoxType::getName($type);
		

		$out = "";
		$hideBorder = "";

		//! Limit buffer to box area
		$outer = $buffer->getValid();

//		if (! $this->border || ! $cfg->get('boxBorder')) {
		if (! $this->border) {
			$buffer->setValid($rect->offset(1));
		} else {
			$buffer->setValid($rect);
		}

		$children = $this->getChildCount();

		if ($children > 0) {

			$out = $this->getLayout()->render($buffer, $cfg);
		} else {
			$content = $buffer->rect($rect, $cfg);

			switch ($this->getType()) {
	
			case hamBoxType::NONE:
			case hamBoxType::ANY:
			case hamBoxType::PLAIN:
	
				$out .= "<pre class=\"$typename\">";
	
				if ($children === 0) {
					$out .= ham_entities($content, $cfg);
				} else {
					$out .= $content;
				}
				$out .= "</pre>";
				break;
	
			case hamBoxType::FORM:
	
				$out .= "<form action=\"" .
					htmlspecialchars($_SERVER["PHP_SELF"]) . "#$label" .
					"\" method=\"post\">";
	
				$out .= "<input type=\"hidden\" name=\"hamFormLabel\" value=\"$label\">";
	
				$out .= "<pre class=\"$typename\">";
	
				if ($children === 0) {
					$out .= ham_entities($content, $cfg);
				} else {
					$out .= $content;
				}
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
					new hamRect(
						$rect->getY(0) + 1,
						$rect->getY(1) - 1,
						$rect->getX(0) + 1,
						$rect->getX(1) - 1
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
					new hamRect(
						$rect->getY(0) + 1,
						$rect->getY(1) - 1,
						$rect->getX(0) + 1,
						$rect->getX(1) - 1
					),
					//! Overlay buffer and configuration
					$overlay, $cfg
				);
	
				$content = $tmp->rect($rect, $cfg);
	
				$out .= "<pre class=\"$typename\">";
				$out .= ham_entities($content, $cfg);
				$out .= "</pre>";
				break;

			case hamBoxType::ACTION:

				$label = $this->getLabel();
				$id = $this->getId();
				$cgi = $cfg->get('cgi');
				$status = $cgi->getFormStatus($id);

				//! Check for posted execution status of this box
				if ($status !== null) {

					//! A status has been set for this label
					if($status === 0) {
						//! Command executed successfully
						$actionclass = "hamBoxActionSuccess";
					} else {
						//! Failure on previous command execution
						$actionclass = "hamBoxActionFailure";
					}
				} else {
					$actionclass = "";
				}

				$content = $buffer->rect($rect, $cfg);

				$out .= "<pre class=\"$typename $actionclass\">" .
					"<form action=\""                        .
					htmlspecialchars($_SERVER["PHP_SELF"])   .
					"#$label" .  "\" method=\"post\">"       .
					"<input type=\"hidden\" "                .
						"name=\"hamFormLabel\""          .
						"value=\"$label\">"              .
					"<input type=\"hidden\" "                .
						"name=\"hamFormId\""             .
						"value=\"$id\">"                 .
					"<input type=\"hidden\" "                .
						"name=\"hamFormType\""           .
						"value=\"action\">"              .
					"<button type=submit>"                   .
					ham_entities($content, $cfg)             .
					"</button>"                              .
					"</form>"                                .
					"</pre>"                                 ;
				break;
	
			default:
				throw new Exception("Unknown box type $type!");
			}
		}

		//! Set buffer to previous size
		$buffer->setValid($outer);

		return $out;
	}

	//! Returns the buffer content of this box
	//! without adding tags (e.g. for plain layout)
	public function rect($buffer, $cfg = null)
	{
		$rect = $this->getRect();
		
		return $buffer->rect($rect, $cfg);
	}

	//! Return a unique id for this box
	public function getId()
	{
		return "(".$this->getY(0).",".$this->getX(0).")";
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

	public function getLayout() {
		return $this->layout;
	}

	public function setLayout($layout) {
		$this->layout = $layout;
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

		return new hamRect(
			$this->y[0],
			$this->y[2],
			$this->x[0],
			$this->x[2]
		);
	}

	public function setRect($rect) {
		$this->y[0] = $rect->getY(0);
		$this->y[1] = $rect->getY(0);
		$this->y[2] = $rect->getY(1);
		$this->y[3] = $rect->getY(1);
		$this->x[0] = $rect->getX(0);
		$this->x[1] = $rect->getX(1);
		$this->x[2] = $rect->getX(1);
		$this->x[3] = $rect->getX(0);
	}

	public function getChildren() {
		return $this->layout->getBoxes();
	}

	public function getChildCount() {
		if ($this->layout === null) {
			return 0;
		} else {
			return count($this->layout->getBoxes());
		}
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

		case hamBoxType::ACTION:

$this->topCorner      = $cfg->get('boxActionCornerTop');
$this->bottomCorner   = $cfg->get('boxActionCornerBottom');
$this->yEdge          = $cfg->get('boxActionEdgeVertical');
$this->xEdge          = $cfg->get('boxActionEdgeHorizontal');
$this->bracketLeft    = $cfg->get('boxActionEdgeBracketLeft');
$this->bracketRight   = $cfg->get('boxActionEdgeBracketRight');
$this->bracketTop     = $cfg->get('boxActionEdgeBracketTop');
$this->bracketBottom  = $cfg->get('boxActionEdgeBracketBottom');
			break;
	
		default:
			throw new Exception("Unknown box type $type!");
		}
	}
}

?>

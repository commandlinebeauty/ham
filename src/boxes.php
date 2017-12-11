<?php

//! Type of a box, i.e., its look
abstract class hamBoxType
{
	const NONE   = -1; ///< Uninitalized
	const ANY    =  0; ///< Means: any of the following types
	const INFO   =  1; ///< Info boxes parse their content unaltered
	const FORM   =  2; ///< A form box is nested into an HTML form
	const ACTION =  3; ///< Action boxes are rendered as button like elements
	const CHART  =  4; ///< Chart boxes are rendered as diagrams
	const IMAGE  =  5; ///< Image boxes render images as ASCII art

	//! Return array of all box types (no meta-types)
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

		array_push($out, hamBoxType::INFO);
		array_push($out, hamBoxType::FORM);
		array_push($out, hamBoxType::ACTION);
		array_push($out, hamBoxType::CHART);
		array_push($out, hamBoxType::IMAGE);

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
			return 'hamBoxAction';
		case  4:
			return 'hamBoxChart';
		case  5:
			return 'hamBoxImage';
		default:
			throw new Exception("Unknown box type \"$type\"!");
		}
	}
}

//! The different box source types, i.e., methods of content aquisition
abstract class hamBoxSource
{
	const NONE = -1; ///< Uninitialized
	const ANY  =  0; ///< Any of the types below
	const TEXT =  1; ///< Use text inside box
	const FILE =  2; ///< Load text from file
	const CMD  =  3; ///< Obtain content from command output
}

//! Main box class
class hamBox
{
	private $status = 0; ///< Will be increased on errors

	private $type;       ///< Box type of type #hamBoxType
	private $source = hamBoxSource::NONE; ///< Source type of type #hamBoxType
	private $label;      ///< Label (text on box edges)
	private $y;          ///< y-coordinates in buffer frame
	private $x;          ///< x-coordinates in buffer frame
	private $border;     ///< Border is shown if this is != 0

	private $layout = null; ///< Layout for rendering child boxes
	private $chart  = null; ///< Chart component or null
	private $image  = null; ///< Chart component or null

	//! Initialization of a new box
	public function __construct($type, $label, $y, $x, $border, $buffer, $cfg)
	{
		$this->type = $type;
		$this->y = $y;
		$this->x = $x;
		$this->border = $border;

		$modifier = substr($label, 0, 1);

		//! Set source type depending on the label modifier:
		if ($modifier === '@') {
			//! - Read content from file
			$this->label = substr($label, 1);
			$this->source = hamBoxSource::FILE;
			
		} else if ($modifier === '!') {
			//! - Read content from command output
			$this->label = substr($label, 1);
			$this->source = hamBoxSource::CMD;
		} else {
			//! - Read content from text inside box
			$this->label = $label;
			$this->source = hamBoxSource::TEXT;
		}

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
			$this->layout = new hamLayoutPlain($buffer, $label, $cfg);
			break;

		case 'table':
			$this->layout = new hamLayoutTable($buffer, $label, $cfg);
			break;

		default:
			throw new Exception("Unknown layout type " . $cfg->get('layout') . "!");
			break;
		}

		//! Restore scan region and valid buffer region
		$cfg->set('layoutLevel', $oldTableLevel);
		$cfg->set('layoutScanRect', $oldScanRect);
		$buffer->setValid($outer);
	}

	//! Render box
	public function render($buffer, $cfg = null)
	{
		$type = $this->getType();
		$label = $this->getLabel();
		$rect = $this->getRect();
		$typename = hamBoxType::getName($type);

		$out = "";
		$hideBorder = null;

		//! Limit buffer to box area
		$outer = $buffer->getValid();

		if (! $this->border || ! $cfg->get('boxBorder')) {

			$buffer->setValid($rect->offset(1));
			$hideBorder = true;
		} else {
			$buffer->setValid($rect);
			$hideBorder = false;

		}

		$children = $this->getChildCount();

		if ($children > 0) {

			$out = $this->getLayout()->render($buffer, $cfg);
		} else {
			$content = ham_parse_htmlentities($this->getContent($buffer, $cfg), $cfg);

			if (! $hideBorder) {

				$content = ham_links_label($content, $cfg);
			}

			switch ($this->getType()) {
	
			case hamBoxType::NONE:
			case hamBoxType::ANY:
			case hamBoxType::INFO:
	
				$out .= "<pre class=\"$typename\">";
	
				if ($children === 0) {

//					$out .= ham_parse_htmlentities($content, $cfg);
					$out .= $content;
				} else {
					$out .= $content;
				}
				$out .= "</pre>";
				break;
	
			case hamBoxType::FORM:
	
				$out .= "<form action=\"" .
					htmlspecialchars($_SERVER["PHP_SELF"]) .
					"#$label\" method=\"post\">";
	
				$out .= "<input type=\"hidden\"
					name=\"hamFormLabel\" value=\"$label\">";
	
				$out .= "<pre class=\"$typename\">";

				if ($children === 0) {

					$cfg->set('currentForm', $label);
//					$content = ham_parse_form($content, $label, $cfg);
					$out .= ham_parse($content, $cfg);
//					$out .= $content;
					$cfg->set('currentForm', null);
				} else {
					$out .= $content;
				}
				$out .= "</pre>";
	
				$out .= "</form>";
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

//				$content = $buffer->rect($rect, $cfg);

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
//					ham_parse_htmlentities($content, $cfg)   .
					$content                                 .
					"</button>"                              .
					"</form>"                                .
					"</pre>"                                 ;
				break;

			case hamBoxType::CHART:

				unset($this->chart);

				$this->chart = new hamChart(
					$content,
					$cfg
				);

				$out .= "<pre class=\"$typename\">";
	
				if ($children === 0) {

//					$out .= ham_parse_htmlentities($content, $cfg);
//					$out .= $content;
					$out .= $this->chart->render($cfg);
				} else {
					$out .= $content;
				}
				$out .= "</pre>";
				break;

			case hamBoxType::IMAGE:

				unset($this->image);

				$this->image = new hamImage(
					$content,
					$label,
					$cfg
				);

				$out .= "<pre class=\"$typename\">";
	
				if ($children === 0) {

//					$out .= ham_parse_htmlentities($content, $cfg);
//					$out .= $content;
					$out .= $this->image->render($cfg);
				} else {
					$out .= $content;
				}
				$out .= "</pre>";
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

	//! Return the content of the box depending on the source type
	public function getContent($buffer, $cfg)
	{
		$content = "";
		$rect = $this->getRect();

		if ($this->source === hamBoxSource::FILE) {

			$file = $this->getLabel();

			if (!file_exists($file)) {
//				throw new Exception("File \"$file\" not found!");
				$this->status++;
//				error_log("\"$file\" not found!");
				return "\"$file\" not found!";
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

		} else if ($this->source === hamBoxSource::CMD) {

			$cmd = $this->getLabel();
			$result = "";

			if ($cmd === null || $cmd === "") {
				$this->status++;
				return "Empty command!";
			}

			$retval = -1;

			exec(escapeshellcmd($cmd), $result, $retval);

			if ($retval !== 0) {
				$this->status++;
				return "`$cmd` not found!";
			}

			if (count($result) <= 0) {
				return "";
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

		} else if ($this->source === hamBoxSource::TEXT) {
			$content = $buffer->rect($rect, $cfg);
		}

		return $content;
	}

	//! Return a unique id for this box
	public function getId()
	{
		return "(".$this->getY(0).",".$this->getX(0).")";
	}

	//! Get the box type
	public function getType() {
		return $this->type;
	}

	//! Set the box type
	public function setType($type = null) {
		if ($type === null) {
			$this->type = hamBoxType::NONE;
		} else {
			$this->type = $type;
		}
	}

	//! Get box label
	public function getLabel() {
		return $this->label;
	}

	//! Set box label
	public function setLabel($label) {

		$this->label = $label;
	}

	//! Get layout for child boxes
	public function getLayout() {
		return $this->layout;
	}

	//! Set layout for child boxes
	public function setLayout($layout) {
		$this->layout = $layout;
	}

	//! Return associated chart component
	public function getChart() {
		return $this->chart;
	}

	//! Set associated chart component
	public function setChart($chart) {
		$this->chart = $chart;
	}

	//! Return associated image component
	public function getImage() {
		return $this->image;
	}

	//! Set associated image component
	public function setImage($image) {
		$this->image = $image;
	}

	//! Get y-coordinates 0/1/2/3
	public function getY($index = null) {

		if ($index === null) {
			return $this->y;
		}

		return $this->y[$index];
	}

	//! Get x-coordinates 0/1/2/3
	public function getX($index = null) {

		if ($index === null) {
			return $this->x;
		}

		return $this->x[$index];
	}

	//! Get box area
	public function getRect() {

		return new hamRect(
			$this->y[0],
			$this->y[2],
			$this->x[0],
			$this->x[2]
		);
	}

	//! Set box coordinates
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

	//! Get array of child boxes
	public function getChildren() {
		return $this->layout->getBoxes();
	}

	//! Get number of child boxes
	public function getChildCount() {
		if ($this->layout === null) {
			return 0;
		} else {
			return count($this->layout->getBoxes());
		}
	}
}

//! Used for storing a set of delimiters for one box type
class hamBoxDelimiters
{
	public $topCorner     = ""; ///< Top corner symbol
	public $bottomCorner  = ""; ///< Bottom corner symbol
	public $yEdge         = ""; ///< Symbol for vertical edges
	public $xEdge         = ""; ///< Symbol for horizontal edges
	public $bracketLeft   = ""; ///< Start bracket from left to right
	public $bracketRight  = ""; ///< Start bracket from right to left
	public $bracketTop    = ""; ///< Start bracket from top to bottom
	public $bracketBottom = ""; ///< Start bracket from bottom to top

	//! Initialize delimiters for a specific box type
	public function __construct($type, $cfg)
	{
		$this->setType($type, $cfg);
	}

	//! Update delimiter type
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

		case hamBoxType::INFO:

$this->topCorner      = $cfg->get('boxInfoCornerTop');
$this->bottomCorner   = $cfg->get('boxInfoCornerBottom');
$this->yEdge          = $cfg->get('boxInfoEdgeVertical');
$this->xEdge          = $cfg->get('boxInfoEdgeHorizontal');
$this->bracketLeft    = $cfg->get('boxInfoEdgeBracketLeft');
$this->bracketRight   = $cfg->get('boxInfoEdgeBracketRight');
$this->bracketTop     = $cfg->get('boxInfoEdgeBracketTop');
$this->bracketBottom  = $cfg->get('boxInfoEdgeBracketBottom');
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
	
//		case hamBoxType::FILE:
//
//$this->topCorner      = $cfg->get('boxFileCornerTop');
//$this->bottomCorner   = $cfg->get('boxFileCornerBottom');
//$this->yEdge          = $cfg->get('boxFileEdgeVertical');
//$this->xEdge          = $cfg->get('boxFileEdgeHorizontal');
//$this->bracketLeft    = $cfg->get('boxFileEdgeBracketLeft');
//$this->bracketRight   = $cfg->get('boxFileEdgeBracketRight');
//$this->bracketTop     = $cfg->get('boxFileEdgeBracketTop');
//$this->bracketBottom  = $cfg->get('boxFileEdgeBracketBottom');
//			break;
//	
//		case hamBoxType::CMD:
//
//$this->topCorner      = $cfg->get('boxCmdCornerTop');
//$this->bottomCorner   = $cfg->get('boxCmdCornerBottom');
//$this->yEdge          = $cfg->get('boxCmdEdgeVertical');
//$this->xEdge          = $cfg->get('boxCmdEdgeHorizontal');
//$this->bracketLeft    = $cfg->get('boxCmdEdgeBracketLeft');
//$this->bracketRight   = $cfg->get('boxCmdEdgeBracketRight');
//$this->bracketTop     = $cfg->get('boxCmdEdgeBracketTop');
//$this->bracketBottom  = $cfg->get('boxCmdEdgeBracketBottom');
//			break;

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

		case hamBoxType::CHART:

$this->topCorner      = $cfg->get('boxChartCornerTop');
$this->bottomCorner   = $cfg->get('boxChartCornerBottom');
$this->yEdge          = $cfg->get('boxChartEdgeVertical');
$this->xEdge          = $cfg->get('boxChartEdgeHorizontal');
$this->bracketLeft    = $cfg->get('boxChartEdgeBracketLeft');
$this->bracketRight   = $cfg->get('boxChartEdgeBracketRight');
$this->bracketTop     = $cfg->get('boxChartEdgeBracketTop');
$this->bracketBottom  = $cfg->get('boxChartEdgeBracketBottom');
			break;

		case hamBoxType::IMAGE:

$this->topCorner      = $cfg->get('boxImageCornerTop');
$this->bottomCorner   = $cfg->get('boxImageCornerBottom');
$this->yEdge          = $cfg->get('boxImageEdgeVertical');
$this->xEdge          = $cfg->get('boxImageEdgeHorizontal');
$this->bracketLeft    = $cfg->get('boxImageEdgeBracketLeft');
$this->bracketRight   = $cfg->get('boxImageEdgeBracketRight');
$this->bracketTop     = $cfg->get('boxImageEdgeBracketTop');
$this->bracketBottom  = $cfg->get('boxImageEdgeBracketBottom');
			break;
	
		default:
			throw new Exception("Unknown box type $type!");
		}
	}
}

?>

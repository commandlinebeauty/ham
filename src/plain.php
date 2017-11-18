<?php

class hamLayoutPlain extends hamLayout
{
	public function __construct($buffer, $cfg = null)
	{
		//! Call general layout constructor
		parent::__construct($buffer, $cfg);
	}

	//! Render HTML table and return as string
	public function render($buffer, $cfg = null)
	{
		$tmp = clone $buffer;

		foreach (parent::getBoxes() as $box) {

			$content = $box->rect($buffer, $cfg);

			$overlay = new hamBuffer($content, $cfg);

			$tmp->overlay(
				//! Coordinates in buffer frame
//				$box->getRect(),
				$box->render($buffer, $cfg),
				//! Overlay buffer and configuration
				$overlay, $cfg
			);
		}

		$out = "";

		//! Render content as simple as possible
		$out .= "<pre class='hamLayoutPlain'>\n";

		$out .= ham_parse_htmlentities($tmp->getContent(), $cfg);
	
		$out .= "</pre>";
	
		return $out;
	}
}

?>

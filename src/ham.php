<?php
//! @file ham.php
//! @usage Include this file to your project and call ham()
//! @brief H.A.M. main library file
//! @author Fritz-Walter Schwarm <root@commandlinebeauty.com>
//! @copyright Copyright (C) 2017 Fritz-Walter Schwarm
//! @license This project is released under the [GNU AGPLv3](@ref agplv3)
//! 
//! This program is free software: you can redistribute it and/or modify     
//! it under the terms of the GNU Affero General Public License as published 
//! by the Free Software Foundation, either version 3 of the License, or     
//! (at your option) any later version.                                      
//!                                                                          
//! This program is distributed in the hope that it will be useful,          
//! but WITHOUT ANY WARRANTY; without even the implied warranty of           
//! MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            
//! GNU Affero General Public License for more details.                      
//!                                                                          
//! You should have received a copy of the GNU Affero General Public License 
//! along with this program. If not, see <http://www.gnu.org/licenses/>.    
//!

include "form.php";
include "vars.php";
include "parse.php";
include "rect.php";
include "cgi.php";
include "config.php";
include "buffer.php";
include "boxes.php";
include "layout.php";
include "plain.php";
include "table.php";
include "links.php";
include "inputs.php";
include "debug.php";

//! Main interface class
class ham
{
	private $cfg;    ///< Configuration object of type #hamConfig
	private $buffer; ///< ASCII buffer of type #hamBuffer
	private $layout; ///< Site layout of type #hamLayout

	//! Create configuration and initialize buffer and layout
	public function __construct($content, $opts = null)
	{
		//! Create configuration from options
		unset($this->cfg);
		$this->cfg = new hamConfig($opts);

		//! Initialize buffer from content
		$this->init($content, $this->cfg);

		//! Optional debugging
		if ($this->cfg->get('debug')) {
			echo ham_debug_config($this->cfg);
			echo ham_debug_boxes($this->layout->getBoxes(), $this->buffer, $this->cfg);
		}
	}

	//! Render page and return result
	public function render($cfg = null)
	{
		if ($cfg === null) {
			$cfg = $this->cfg;
		}

		$page = $cfg->get('page');

		$out = "";
	
		//! Add page header if 'page' option is specified
		if ($page) {
			$out .= $this->header($cfg);
		}
	
		//! Render layout and parse result
		$out .= "<div class='ham'>" .
//			$this->parse($this->layout->render($this->buffer, $cfg), $cfg) .
			$this->layout->render($this->buffer, $cfg) .
			"</div>";
	
		//! Add page footer if 'page' option is specified
		if ($page) {
			$out .= $this->footer($cfg);
		}
	
		return $out;
	}

	//! (Re-)create buffer
	public function init($content, $cfg = null)
	{
		if ($cfg === null) {
			$cfg = $this->cfg;
		}

		$this->cfg->set('cgi', new hamCGI(
			$cfg
		));

		//! Parse content and create buffer
		unset($this->buffer);
		unset($this->layout);

		$this->buffer = new hamBuffer(
			$this->filter($content, $cfg)
		, $cfg);

		switch ($cfg->get('layout')) {

		case 'plain':
			$this->layout = new hamLayoutPlain($this->buffer, $cfg->get('title'), $cfg);
			break;

		case 'table':
			$this->layout = new hamLayoutTable($this->buffer, $cfg->get('title'), $cfg);
			break;

		default:
			throw new Exception("Unknown layout type " . $cfg->get('layout') . "!");
			break;
		}
	}

	//! Filter provided content (before buffer creation)
	public function filter($content, $cfg = null)
	{
		if ($cfg === null) {
			$cfg = $this->cfg;
		}

		$comment = preg_quote($cfg->get('comment'), '/');
		$nl = PHP_EOL;

		//! Remove out-commented lines (starting with $comment char)
		$out = preg_replace("/^$comment(.*)$$nl?/m", "", $content);

		return $out;
	}

	//! Parse content (modifies the result of $this->layout->render())
	public function parse($content, $cfg = null)
	{
		if ($cfg === null) {
			$cfg = $this->cfg;
		}

		return ham_parse_hamentities($content, $cfg);
	}

	//! Render header
	public function header($cfg = null)
	{
		if ($cfg === null) {
			$cfg = $this->cfg;
		}

		$title = $cfg->get('title');
		$css = $cfg->get('css');
	
		return "
<!DOCTYPE html>
<html>
	<head>
		<title>
			$title
		</title>

		<link rel='stylesheet' type='text/css'
			href='$css' />
	</head>
	<body>\n";
	}

	//! Render footer
	public function footer($cfg = null)
	{
		if ($cfg === null) {
			$cfg = $this->cfg;
		}

		return "
	</body>
</html>
		";
	}

	//! Obtain internal configuration object
	public function getConfig() {
		return $this->cfg;
	}
}

?>

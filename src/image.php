<?php

//! Images currently rely on an external application for converting images files into ASCII art (jp2a is the default)
class hamImage {

	private $lines = null; ///< Content split into lines
	private $file = null;  ///< Image file
	private $width = 0;    ///< Image width in number of characters
	private $height = 0;   ///< Image height in number of characters

	//! Instantiation of a new chart
	public function __construct($content, $file, $cfg)
	{
		$this->lines = explode(PHP_EOL, $content);

		$this->width = min(array_map('strlen', $this->lines));
		$this->height = count($this->lines) - 2;

		$this->file = $file;
	}

	//! Render image
	public function render($cfg = null)
	{
		$cmd = "jp2a --width=$this->width --height=$this->height $this->file"; //#2> /dev/null";

		$status = 0;
		unset($out);
		unset($image);

		exec($cmd, $image, $status);

		$out = "";

		$out .= $this->lines[0] . PHP_EOL;
		$out .= implode(PHP_EOL, $image) . PHP_EOL;
		$out .= $this->lines[count($this->lines) - 1];

		return $out;
	}
}


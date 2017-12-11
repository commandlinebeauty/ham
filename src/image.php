<?php

class hamImage {

	private $lines = null;
	private $file = null;
	private $width = 0;
	private $height = 0;

	//! Instantiation of a new chart
	public function __construct($content, $file, $cfg)
	{
		$this->lines = explode(PHP_EOL, $content);
echo $this->lines[1];

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


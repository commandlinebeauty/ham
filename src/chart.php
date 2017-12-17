<?php

//! Charts are currently using Gnuplot with the dumb terminal
class hamChart {
	private $content = null;     ///< Content string
	private $lines = null;       ///< Content split into lines
	private $width = 0;          ///< Chart width in number of characters
	private $height = 0;         ///< Chart height in number of characters
	private $hideBorder = false; ///< Hide border flag

	//! Instantiation of a new chart
	public function __construct($content, $hideBorder, $cfg)
	{
		$this->content = preg_replace('/^.(.*).$/m', '$1', $content);
		$this->content = substr($this->content, strpos($this->content, "\n")+1);
		$this->content = substr($this->content, 0, strrpos($this->content, "\n"));
//		$this->content = preg_replace(/^.*PHP_EOL(.*)PHP_EOL.*$/", "$1", $this->content);

		$this->lines = explode(PHP_EOL, $content);

		$this->width = min(array_map('strlen', $this->lines)) + 2;
		$this->height = count($this->lines);

		$this->hideBorder = $hideBorder;

		if ($this->hideBorder) {
			$this->height += 2;
		}
	}

	//! Render chart
	public function render($cfg = null)
	{
		$out = "";

		$key_cmd = "set nokey;";
		$cmd = "printf \"$this->content\" | (cat > /dev/shm/hamChartData.tmp && trap 'rm /dev/shm/hamChartData.tmp' EXIT && gnuplot -e \"$key_cmd set terminal dumb size $this->width,$this->height; set tics scale 0.25; plot for [i=2:5] '/dev/shm/hamChartData.tmp' u 1:i\") 2> /dev/null";

		$status = 0;
		unset($plot);

//		exec(escapeshellcmd($cmd), $out, $status);
		exec($cmd, $plot, $status);

		if (!$this->hideBorder) {

			$out .= $this->lines[0];
		}

		$out .= implode(PHP_EOL, $plot);

		if (!$this->hideBorder) {

			$out .= $this->lines[count($this->lines) - 1];
		}

		return $out;
	}
}

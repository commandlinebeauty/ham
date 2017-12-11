<?php

class hamChart {
	private $lines = null;
	private $content = null;
	private $width = 0;
	private $height = 0;

	//! Instantiation of a new chart
	public function __construct($content, $cfg)
	{
		$this->content = preg_replace('/^.(.*).$/m', '$1', $content);
		$this->content = substr($this->content, strpos($this->content, "\n")+1);
		$this->content = substr($this->content, 0, strrpos($this->content, "\n"));
//		$this->content = preg_replace(/^.*PHP_EOL(.*)PHP_EOL.*$/", "$1", $this->content);
echo $this->content;

		$this->lines = explode(PHP_EOL, $content);

		$this->width = min(array_map('strlen', $this->lines));
		$this->height = count($this->lines);
	}

	//! Render chart
	public function render($cfg = null)
	{
		$out = "";

		$key_cmd = "set nokey;";
		$cmd = "printf \"$this->content\" | (cat > /dev/shm/hamChartData.tmp && trap 'rm /dev/shm/hamChartData.tmp' EXIT && gnuplot -e \"$key_cmd set terminal dumb $this->width $this->height; plot for [i=2:5] '/dev/shm/hamChartData.tmp' u 1:i\")"; // 2> /dev/null";

		$status = 0;
		unset($plot);

//		exec(escapeshellcmd($cmd), $out, $status);
		exec($cmd, $plot, $status);

		$out .= $this->lines[0] . PHP_EOL;
		$out .= implode(PHP_EOL, $plot) . PHP_EOL;
		$out .= $this->lines[count($this->lines) - 1];

		return $out;
	}
}

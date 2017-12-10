<?php

class hamChart {
	private $lines = null;
	private $series = null;
	private $axes = null;

	//! Instantiation of a new chart
	public function __construct($content, $cfg)
	{
		$this->init($content, $cfg);
	}

	//! Initialization helper function
	public function init($content, $cfg)
	{
		$delimiter = $cfg->get('chartSeriesDelimiter');

		$delim = new hamBoxDelimiters(hamBoxType::CHART, $cfg);
		$yEdge = preg_quote($delim->yEdge, "/");

//		$this->series = str_getcsv($content, $delimiter); // [, string $enclosure = '"' [, string $escape = "\\" ]]] )

		$this->lines = explode(PHP_EOL, $content);
		$lines = $this->lines;
		$count = count($lines);

		unset($this->series);
		$this->series = array();

//		$line = preg_replace("/[$yEdge]\s*(.*)\s*[$yEdge]/", "$1", $lines[1]);
//		array_push($this->series, explode($delimiter, $line));
//		$cols = count($this->series[0]);

		for ($i = 1; $i < $count - 1; $i++) {

			$line = preg_replace("/[$yEdge]\s*(.*)\s*[$yEdge]/", "$1", $lines[$i]);
			array_push($this->series, explode($delimiter, $line));
		}
	}

	//! Render chart
	public function render($cfg = null)
	{
		$out = "";

		$lines = $this->lines;
		$count = count($lines);

		$header = $lines[0];
		$footer = $lines[$count - 1];

		foreach ($this->series as $series) {
			$out .= implode('-', $series) . PHP_EOL;
		}

		$out .= $lines[count($lines)-1];

		return $out;
	}
}

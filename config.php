<?php

class hamConfig
{
	private $options = array();

	private $defaults = array(
		//! Debugging
		'debug'                    => false    ,
		//! Layout
		'layout'                   => 'plain'  ,
		//! Lines starting with this string with be ignored
		'comment'                  => '#'      ,
		//! Empty space
		'void'                     => ' '      ,
		//! Form box
		'boxFormCornerTop'         => '.'      ,
		'boxFormCornerBottom'      => '\''     ,
		'boxFormEdgeHorizontal'    => '-'      ,
		'boxFormEdgeVertical'      => '|'      ,
                'boxFormEdgeBracketLeft'   => '[(|'    ,
                'boxFormEdgeBracketRight'  => '])|'    ,
                'boxFormEdgeBracketTop'    => '^'      ,
                'boxFormEdgeBracketBottom' => 'v'      ,
		//! File box
		'boxFileCornerTop'         => ';'      ,
		'boxFileCornerBottom'      => '\''     ,
		'boxFileEdgeHorizontal'    => '~'      ,
		'boxFileEdgeVertical'      => '{}'     ,
                'boxFileEdgeBracketLeft'   => '[(|'    ,
                'boxFileEdgeBracketRight'  => '])|'    ,
                'boxFileEdgeBracketTop'    => '^'      ,
                'boxFileEdgeBracketBottom' => 'v'      ,
		//! Command box
		'boxCmdCornerTop'          => ';'      ,
		'boxCmdCornerBottom'       => '\''     ,
		'boxCmdEdgeHorizontal'     => '*'      ,
		'boxCmdEdgeVertical'       => '*'      ,
		'boxCmdEdgeBracketLeft'    => '[(|'    ,
		'boxCmdEdgeBracketRight'   => '])|'    ,
		'boxCmdEdgeBracketTop'     => '^'      ,
		'boxCmdEdgeBracketBottom'  => 'v'      ,
		//! Links
		'linkLeft'                 => '['      ,
		'linkRight'                => ']'      ,
		//! Input button
		'inputButtonLeft'          => '*'      ,
		'inputButtonRight'         => '*'      ,
		'inputTextLeft'            => '{'      ,
		'inputTextRight'           => '}'      ,
	);

	public function __construct($opts = null) {
		foreach ($opts as $key => $value) {
			$this->options[$key] = $value;
		}
	}

	public function getDefault($name) {

		return $this->defaults[$name];
	}

	//! Return requested value
	public function get($name, $default = null) {

		if (array_key_exists($name, $this->options)) {

			$value = $this->options[$name];
		} else {
			if ($default === null) {

				$value = $this->defaults[$name];
			} else {
				$value = $default;
			}
		}

		return $value;
	}

	public function set($name, $value = null) {

		if ($value === null) {
			$this->options[$name] = $this->defaults[$name];
		} else {
			$this->options[$name] = $value;
		}
	}
}

?>


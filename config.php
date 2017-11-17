<?php

class hamConfig
{
	private $options = array();

	private $defaults = array(
		//! Debugging
		'debug'                       => false    ,
		//! Layout
		'layout'                      => 'plain'  ,
		//! Lines starting with this string with be ignored
		'comment'                     => '#'      ,
		//! Empty space
		'void'                        => ' '      ,
		//! Default value for box borders (true means draw border)
		'boxBorder'                   =>  true    ,
		//! Minimum height for being recognized as a box
		'boxHeightMin'                =>  1       ,
		//! Minimum width for being recognized as a box
		'boxWidthMin'                 =>  1       ,
		//! Plain box
		'boxPlainCornerTop'           => '.'      ,
		'boxPlainCornerBottom'        => '\''     ,
		'boxPlainEdgeHorizontal'      => '-'      ,
		'boxPlainEdgeVertical'        => '|'      ,
                'boxPlainEdgeBracketLeft'     => '[('     ,
                'boxPlainEdgeBracketRight'    => '])'     ,
                'boxPlainEdgeBracketTop'      => '^'      ,
                'boxPlainEdgeBracketBottom'   => 'v'      ,
		//! Form box
		'boxFormCornerTop'            => '+'      ,
		'boxFormCornerBottom'         => '+'      ,
		'boxFormEdgeHorizontal'       => '-'      ,
		'boxFormEdgeVertical'         => '|'      ,
                'boxFormEdgeBracketLeft'      => '[('     ,
                'boxFormEdgeBracketRight'     => '])'     ,
                'boxFormEdgeBracketTop'       => '^'      ,
                'boxFormEdgeBracketBottom'    => 'v'      ,
		//! File box
		'boxFileCornerTop'            => ';'      ,
		'boxFileCornerBottom'         => '\''     ,
		'boxFileEdgeHorizontal'       => '~'      ,
		'boxFileEdgeVertical'         => '{}'     ,
                'boxFileEdgeBracketLeft'      => '[('     ,
                'boxFileEdgeBracketRight'     => '])'     ,
                'boxFileEdgeBracketTop'       => '^'      ,
                'boxFileEdgeBracketBottom'    => 'v'      ,
		//! Command box
		'boxCmdCornerTop'             => ';'      ,
		'boxCmdCornerBottom'          => '\''     ,
		'boxCmdEdgeHorizontal'        => '*'      ,
		'boxCmdEdgeVertical'          => '*'      ,
		'boxCmdEdgeBracketLeft'       => '[('     ,
		'boxCmdEdgeBracketRight'      => '])'     ,
		'boxCmdEdgeBracketTop'        => '^'      ,
		'boxCmdEdgeBracketBottom'     => 'v'      ,
		//! Action
		'boxActionCornerTop'          => '.'      ,
		'boxActionCornerBottom'       => '\''     ,
		'boxActionEdgeHorizontal'     => '-'      ,
		'boxActionEdgeVertical'       => '!'      ,
		'boxActionEdgeBracketLeft'    => '[('     ,
		'boxActionEdgeBracketRight'   => '])'     ,
		'boxActionEdgeBracketTop'     => '^'      ,
		'boxActionEdgeBracketBottom'  => 'v'      ,
                //! Overwrite this many characters with the void char when accessing a buffer
		'bufferMargin'                =>  0       ,
		//! Links
		'linkLeft'                    => '['      ,
		'linkRight'                   => ']'      ,
		//! Input button
		'inputButtonLeft'             => '('      ,
		'inputButtonRight'            => ')'      ,
		'inputTextLeft'               => '{'      ,
		'inputTextRight'              => '}'      ,
		//! Scan region inside layout. null means: scan the whole valid region
		'layoutScanRect'              => null     ,
		//! Table nesting level (0 means not nested inside another table)
		'layoutLevel'                 => 0        ,
		//! Render background into as few cells as possible
//		'tableUnify'                  => true     ,
		'tableUnify'                  => false    ,
		//! Make a link out of each box
//		'tableCellBoxLink'            => true     ,
		'tableCellBoxLink'            => false    ,
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


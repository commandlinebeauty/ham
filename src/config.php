<?php
//! @file config.php
//! @brief Configuration

//! Stores all configuration options
class hamConfig
{
	private $options = array(); ///< Array of option/value pairs

	//! Default values
	private $defaults = array(
//		//! HAM root dir
//		'root_dir'                    => '../'    ,
//		//! Directory for CGI scripts
//		'cgi_dir'                     => '../cgi' ,
		//! CSS style file
		'css'                         => '../css/ham.css',
		//! Debugging
		'debug'                       => false    ,
		//! Layout
		'layout'                      => 'table'  ,
		//! Lines starting with this string with be ignored
		'comment'                     => '#'      ,
		//! Empty space
		'void'                        => ' '      ,
		//! Offset for parsing of special elements (ignore top and bottom rows)
		'parseOffset'                 =>  1       ,
		//! Default value for box borders (true means draw border)
		'boxBorder'                   =>  true    ,
		//! Minimum height for being recognized as a box
		'boxHeightMin'                =>  1       ,
		//! Minimum width for being recognized as a box
		'boxWidthMin'                 =>  1       ,
		//! Info box
		'boxInfoCornerTop'           => '.'      ,
		'boxInfoCornerBottom'        => '\''     ,
		'boxInfoEdgeHorizontal'      => '-'      ,
		'boxInfoEdgeVertical'        => '|'      ,
                'boxInfoEdgeBracketLeft'     => '[('     ,
                'boxInfoEdgeBracketRight'    => '])'     ,
                'boxInfoEdgeBracketTop'      => '^'      ,
                'boxInfoEdgeBracketBottom'   => 'v'      ,
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
		//! Chart
		'boxChartCornerTop'           => '^.'      ,
		'boxChartCornerBottom'        => '+>'     ,
		'boxChartEdgeHorizontal'      => '-'      ,
		'boxChartEdgeVertical'        => '|'      ,
		'boxChartEdgeBracketLeft'     => '[('     ,
		'boxChartEdgeBracketRight'    => '])'     ,
		'boxChartEdgeBracketTop'      => '-'      ,
		'boxChartEdgeBracketBottom'   => '-'      ,
		'boxChartCmd'                 => '',
		//! Image
		'boxImageCornerTop'           => '.'      ,
		'boxImageCornerBottom'        => '\''     ,
		'boxImageEdgeHorizontal'      => '~'      ,
		'boxImageEdgeVertical'        => '|'      ,
		'boxImageEdgeBracketLeft'     => '[('     ,
		'boxImageEdgeBracketRight'    => '])'     ,
		'boxImageEdgeBracketTop'      => '^'      ,
		'boxImageEdgeBracketBottom'   => 'v'      ,
		'boxImageCmd'                 => '',
		//! Label
		'boxLabelLeft'                => '['      ,
		'boxLabelRight'               => ']'      ,
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
		//! Variables
		'varLeft'                     => '$'      ,
		'varRight'                    => ' '      ,
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
		'currentForm'                 => null     ,
	);

	//! Initialize configuration from an array of option/value pairs
	public function __construct($opts = null) {
		foreach ($opts as $key => $value) {
			$this->options[$key] = $value;
		}
	}

	//! Get the default value for an option
	public function getDefault($name) {

		return $this->defaults[$name];
	}

	//! Get all option names
	public function getKeys()
	{
		return array_keys($this->defaults);
	}

	//! Get an array of all option/value pairs
	//! TODO FWS: Why not return options array instead?
	public function getArray()
	{
		$out = array();

		foreach ($this->getKeys() as $key) {
			$out[$key] = $this->get($key);
		}

		return $out;
	}

	//! Return requested value
	public function get($name, $default = null)
	{

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

	//! Set an option to the given value
	//! Sets the option to the default value if $value is null
	public function set($name, $value = null)
	{

		if ($value === null) {
			$this->options[$name] = $this->defaults[$name];
		} else {
			$this->options[$name] = $value;
		}
	}
}

?>


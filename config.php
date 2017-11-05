<?php

class hamConfig
{
	private $options = array(),

	private $defaults = array(
		//! Form box
		'boxFormCornerTop'      => '.'  ,
		'boxFormCornerBottom'   => '\'' ,
		'boxFormEdgeHorizontal' => '-'  ,
		'boxFormEdgeVertical'   => '|'  ,
		//! Command box
		'boxCmdCornerTop'       => '.'  ,
		'boxCmdCornerBottom'    => '\'' ,
		'boxCmdEdgeHorizontal'  => '-'  ,
		'boxCmdEdgeVertical'    => '|'  ,
		//! File box
		'boxFileCornerTop'      => ';'  ,
		'boxFileCornerBottom'   => '\'' ,
		'boxFileEdgeHorizontal' => '~'  ,
		'boxFileEdgeVertical'   => '{}' ,
	),

	public function __construct($opts = null) {
		foreach ($opts as $key => $value) {
			$this->options[$key] = $value;
		}
	},

	public function getDefault($name) {

		return $this->defaults[$name];
	},

	//! Return requested value
	public function get($name, $default = null) {

		$value = $this->options[$name];

		if ($value === null) {

			if ($default === null) {

				$value = $this->defaults[$name];
			} else {
				$value = $default;
			}
		}

		return $value;
	},

	public function set($name, $value = null) {

		if ($value === null) {
			$this->options[$name] = $this->defaults[$name];
		} else {
			$this->options[$name] = $value;
		}
	}
}

function ham_config_get($name, $cfg, $default = null)
{
//	if ($default === null) {
//		$default = 
//	}
//
//	if ($opts && array_key_exists($name, $opts)) {
//		return $opts[$name];
//	} else {
//		if ($default) {
//			return $default;
//		} else {
//			return "";
//		}
//	}

	return $cfg->get($name);
}

function ham_config_set($name, $value, &$cfg)
{
	$cfg->set($name, $value);
}

?>


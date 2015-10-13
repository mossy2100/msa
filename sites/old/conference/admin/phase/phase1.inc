<?php

global $php_root_path ;
global $privilege_root_path ;

require_once ("$php_root_path" . $privilege_root_path . "/phase/phasebase.inc") ;

class phase1 extends phasebase
{
	function phase1()
	{
		$this->phaseID = 1 ;
	}
}

?>
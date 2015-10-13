<?php

global $php_root_path ;
global $privilege_root_path ;

require_once ("$php_root_path" . $privilege_root_path . "/phase/phasebase.inc") ;

class phase2 extends phasebase
{
	function phase2()
	{
		$this->phaseID = 2 ;
	}	
}

?>
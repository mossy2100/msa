<?php

global $php_root_path ;
global $privilege_root_path ;

require_once ("$php_root_path/includes/page_includes/pagebase.inc") ;

class Page extends PageBase
{	
	function DisplayHeader( $err_message = "" )
	{
		global $php_root_path ;
		
		header('Content-Type: text/html; charset=ISO-8859-1');
		//$header_str = "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n" ;  // html documents must have a DOCTYPE declaration ... BUT interferes with that ugly menu
		$header_str = "<html>\n" ;  // Display
		$header_str .= "<head>\n" ;	// Display
		$this->DisplayTitle( $header_str ) ;	// Display
		$this->GetExtraHeadData( $header_str );
		$this->GetMetaHeader( $header_str ) ; // Display
		$this->Style( $header_str ) ;	// Display
		$header_str .= "</head>\n" ;	// Display
		
		if (file_exists("$php_root_path/includes/preferences.inc"))
		{
			$settingInfo = get_Conference_Settings();
			$header_str .= "<body bgcolor=\"" . $settingInfo -> BackgroundColor . "\""; // Set colours
			$header_str .= " text=\"" . $settingInfo -> FontColor . "\"";
			$header_str .= " link=\"" . $settingInfo -> FontColor . "\"";
			$header_str .= " alink=\"" . $settingInfo -> FontColor . "\"";
			$header_str .= " vlink=\"" . $settingInfo -> FontColor . "\"";
		}
		else
		{
			$header_str .= "<body bgcolor=\"#FFFFFF\" "; // Set colours
			$header_str .= " text=\"#000000\"";	
			$header_str .= " link=\"#000000\"";
			$header_str .= " alink=\"#000000\"";
			$header_str .= " vlink=\"#000000\"";
		}
		$header_str .= ">\n" ;
					
		$this->DisplayMenu ( $header_str , &$err_message ) ;	// Layout
		
		echo "<div style=\"width: 100%\">\n" ;
		echo "<h1> $this->header </h1>\n" ;
		echo "</div>\n" ;	
		
		echo "<div style=\"width: 100% ; margin: 0 ; padding: 0\">\n" ;	// Layout
	}
	
	function DisplayLayout ()
	{
		echo $this->content ;  	
	}		
	
	function DisplayFooter( $err_message = "" )
	{
		if ( !empty ( $err_message ) )
		{
			$confer = get_conference_info() ;
		}
		
		echo "<font color=\"#FF0000\">" . $err_message . "</font>" ;		
		?>
		</div>
		
		<div style="width: 100% ; border: none; margin: 0 ; padding: 0">
			<p class=foot>
			<a href="http://iaprcommence.sourceforge.net">Powered by IAPRCommence</a>
			</p>
		</div>
		</body>
		</html>
		<?php		
	}
	
	function Display()
	{
		$this->DisplayHeader() ;
		$this->DisplayLayout();
		$this->DisplayFooter() ;
	}
}
?>
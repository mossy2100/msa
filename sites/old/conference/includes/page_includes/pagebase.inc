<?php

global $php_root_path ;
global $privilege_root_path ;

require_once ("$php_root_path/includes/page_includes/pagebase.inc") ;

class PageBase
{
	var $content ;
	var $title = "IAPR Commence Conference System" ;
	var $header = "IAPR Commence Conference System" ;
	var $menu ;
	var $showmenu = 1 ;
	//var $bgcolor = "#FFFFFF" ;
	var $meta_header = "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=ISO-8859-1\">\n" ;	// Display
	var $extra_head_data = "";

	function SetContent ( $newcontent )
	{
		$this->content = $newcontent ;
	}

	function SetHeader ( $newcontent )
	{
		$this->header = $newcontent ;
	}

	function GetMetaHeader( &$header_str )
	{
		$header_str .= $this->meta_header ;
	}

	function SetMetaHeader( $text )
	{
		$this->meta_header = $text ;
	}

	function AddExtraHeadData( $content )
	{
		$this->$extra_head_data .= $content;
	}

	function ClearExtraHeadData( &$header_str )
	{
		$this->$extra_head_data = "";
	}

	function GetExtraHeadData( &$header_str )
	{
		if ( !empty( $this->$extra_head_data ) )
			$header_str .= $this->$extra_head_data ;
	}

	function SetTitle ( $newcontent )
	{
		$this->title = $newcontent ;
	}

	function DisplayTitle ( &$header_str )
	{
		$header_str .= "<title> $this->title </title>\n" ;
	}

	function Style( &$header_str )
	{
		global $php_root_path ;
		
		if (file_exists("$php_root_path/includes/preferences.inc"))
		{
			$settingInfo = get_Conference_Settings();
			$header_str .= "<link href=\"$php_root_path/stylesheets/". $settingInfo -> CSSFile." \" rel=\"stylesheet\" type=\"text/css\">\n" ;
		}
		else // needed for install since database is not available
		{
			$header_str .= "<link href=\"$php_root_path/stylesheets/CommentStyleDefault.css \" rel=\"stylesheet\" type=\"text/css\">\n" ;
		}
	}

	function DisplayHeader()
	{
		echo "<div style=\"width: 100%\">\n" ;
		echo "<h1> $this->header </h1>\n" ;
		echo "</div>\n" ;
	}

	function DisplayMenu ( &$header_str , $err_message = "" )
	{
//		if ( session_is_registered ( "valid_user" ) )
//		{
			global $php_root_path ;
			//The Menu goes here

			$header_str .= '<table style="width: 100% ; border: none; margin: 0 ; padding: 4">' ;
  			$header_str .= '<tr style="text-align: center">' ;

//		echo "phaseID: " . $_SESSION["phase"]->phaseID . "<BR>\n" ;
			if ( $this->showmenu == 1 )
			{
				//global $_SESSION ;
				if ( isset ( $_SESSION["phase"] ) )
				{
				$_SESSION["phase"]->display_menu( $header_str , &$err_message ) ;
				}
				else
				{
					global $homepage ;
					$homepage->showmenu = 0 ;
					do_html_header("Page View Failed" , &$err_message ) ;
					$err_message .= " Sorry, You must login to view this page. <br>\n";
					$err = $err_message . "<br><br> Go to <a href='/$php_root_path/index.php'>Login</a> page." ;
					do_html_footer($err);
					exit;
				}
			}
			else
			{
				echo $header_str . "<td></td>"; // empty <tr></tr> element is not allowed by W3C
			}
?>
  			</tr>
  			<tr><td>&nbsp;</td></tr>
		</table>
<?php
			//The Menu ends here
//		}
//		else
//		{
//			echo $header_str ;
//		}
	}

	function DisplayLayout ( $err_message = "" )
	{
		$this->DisplayMenu ( &$err_message ) ;

		echo "<table style=\"width: 100% ; border: none; margin: 0 ; padding: 0\">\n" ;
		echo "<tr>\n" ;
			echo "<td>\n" ;
				$this->menu ;	// Add whatever you want in this menu column
			echo "</td>\n" ;
		echo "</tr>\n" ;
		echo "<tr>\n" ;
	   	 	echo "<td>\n" ;
				echo $this->content ;  // Add whatever you want in this content column
			echo "</td>\n" ;
		echo "</tr>\n" ;
		echo "</table>\n" ;
	}

	function DisplayFooter()
	{
		?>
		<table style="width: 100% ; border: none; margin: 0 ; padding: 0">
    	<tr>
	    <td style="width: 100%">
			<p class=foot><a href="http://iaprcommence.sourceforge.net"> Powered by IAPRCommence</a></p>
		</td>
	  	</tr>
		</table>
		<?php
	}

	function Display()
	{
		echo "<html>\n" ;
		echo "<head>\n" ;
		$this->DisplayTitle() ;	// Display Title
		echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n" ;
		echo "</head>\n" ;
		$this->Style() ;	//	Display Title
		echo "<body>\n" ;
		$this->DisplayHeader() ;	// Display Header
		$this->DisplayLayout();	// Display Layout
		$this->DisplayFooter() ;	// Display Footer
		echo "</body>\n" ;
		echo "</html>\n" ;
	}
}
?>

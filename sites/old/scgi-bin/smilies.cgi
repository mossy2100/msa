#!/usr/bin/perl -Tw
$| = 1;

# =====================================================================
# YaWPS - Yet another Web Portal System 
#
# Copyright (C) 2001 by Adrian Heiszler (d3m1g0d@users.sourceforge.net)
#
# This program is free software; you can redistribute it and/or 
# modify it under the terms of the GNU General Public License 
# as published by the Free Software Foundation; either version 2 
# of the License, or (at your option) any later version. 
#
# This program is distributed in the hope that it will be useful, 
# but WITHOUT ANY WARRANTY; without even the implied warranty of 
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
# GNU General Public License for more details. 
# 
# You should have received a copy of the GNU General Public License 
# along with this program; if not, write to the 
# Free Software Foundation, Inc.,
# 59 Temple Place - Suite 330, 
# Boston, MA  02111-1307, USA. 
#
#
# $Id: smilies.cgi,v 1.13 2004/02/17 12:20:38 d3m1g0d Exp $
# =====================================================================

# Load necessary modules.
use strict;
use lib '.';
use yawps;

# Get user profile.
my $query     = new CGI;
my %user_data = authenticate();

if ($user_data{theme} =~ /^([\w.]+)$/) { $user_data{theme} = $1; }
require "$cfg{themesdir}/$user_data{theme}/theme.pl";

print_header();
print <<HTML;
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
    
<html>

<head>

<meta name="Generator" content="YaWPS $VERSION">
<title>$cfg{pagetitle}</title>
<link rel="stylesheet" href="$cfg{themesurl}/standard/style.css" type="text/css">
<script language="javascript" type="text/javascript"><!--
function addCode(anystr) { 
opener.document.creator.message.value+=anystr;
}
// --></script>

</head>

<body bgcolor="#ffffff" text="#000000">
<table align="center" border="0" class="bg5" cellspacing="1" cellpadding="0" width="99%">
<tr>
<td>
<table border="0" cellspacing="1" cellpadding="2" width="100%">
<tr class="tbl_header">
<td valign="top" width="50%"><b>Code</b></td>
<td valign="top" width="50%"><b>Smilie</b></td>
</tr>
<tr class ="tbl_row_light">
<td valign="top" width="50%">[bones]</td>
<td valign="top" width="50%"><a href="javascript:addCode('[bones]')"><img src="$cfg{imagesurl}/smilies/bones.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_dark">
<td valign="top" width="50%">[bounce]</td>
<td valign="top" width="50%"><a href="javascript:addCode('[bounce]')"><img src="$cfg{imagesurl}/smilies/bounce.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_light">
<td valign="top" width="50%">:-?</td>
<td valign="top" width="50%"><a href="javascript:addCode(':-?')"><img src="$cfg{imagesurl}/smilies/confused.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_dark">
<td valign="top" width="50%">[confused]</td>
<td valign="top" width="50%"><a href="javascript:addCode('[confused]')"><img src="$cfg{imagesurl}/smilies/confused.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_light">
<td valign="top" width="50%">8)</td>
<td valign="top" width="50%"><a href="javascript:addCode('8)')"><img src="$cfg{imagesurl}/smilies/cool.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_dark">
<td valign="top" width="50%">8-)</td>
<td valign="top" width="50%"><a href="javascript:addCode('8-)')"><img src="$cfg{imagesurl}/smilies/cool.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_light">
<td valign="top" width="50%">[cool]</td>
<td valign="top" width="50%"><a href="javascript:addCode('[cool]')"><img src="$cfg{imagesurl}/smilies/cool.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_dark">
<td valign="top" width="50%">[cry]</td>
<td valign="top" width="50%"><a href="javascript:addCode('[cry]')"><img src="$cfg{imagesurl}/smilies/cry.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_light">
<td valign="top" width="50%">:o</td>
<td valign="top" width="50%"><a href="javascript:addCode(':o')"><img src="$cfg{imagesurl}/smilies/eek.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_dark">
<td valign="top" width="50%">:-o</td>
<td valign="top" width="50%"><a href="javascript:addCode(':-o')"><img src="$cfg{imagesurl}/smilies/eek.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_light">
<td valign="top" width="50%">[eek]]</td>
<td valign="top" width="50%"><a href="javascript:addCode('[eek]')"><img src="$cfg{imagesurl}/smilies/eek.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_dark">
<td valign="top" width="50%">[evil]</td>
<td valign="top" width="50%"><a href="javascript:addCode('[evil]')"><img src="$cfg{imagesurl}/smilies/evil.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_light">
<td valign="top" width="50%">:(</td>
<td valign="top" width="50%"><a href="javascript:addCode(':(')"><img src="$cfg{imagesurl}/smilies/frown.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_dark">
<td valign="top" width="50%">:-(</td>
<td valign="top" width="50%"><a href="javascript:addCode(':-(')"><img src="$cfg{imagesurl}/smilies/frown.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_light">
<td valign="top" width="50%">[frown]</td>
<td valign="top" width="50%"><a href="javascript:addCode('[frown]')"><img src="$cfg{imagesurl}/smilies/frown.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_dark">
<td valign="top" width="50%">:D</td>
<td valign="top" width="50%"><a href="javascript:addCode(':D')"><img src="$cfg{imagesurl}/smilies/grin.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_light">
<td valign="top" width="50%">:-D</td>
<td valign="top" width="50%"><a href="javascript:addCode(':-D')"><img src="$cfg{imagesurl}/smilies/grin.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_dark">
<td valign="top" width="50%">[grin]</td>
<td valign="top" width="50%"><a href="javascript:addCode('[grin]')"><img src="$cfg{imagesurl}/smilies/grin.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_light">
<td valign="top" width="50%">[lol]</td>
<td valign="top" width="50%"><a href="javascript:addCode('[lol]')"><img src="$cfg{imagesurl}/smilies/lol.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_dark">
<td valign="top" width="50%">:x</td>
<td valign="top" width="50%"><a href="javascript:addCode(':x')"><img src="$cfg{imagesurl}/smilies/mad.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_light">
<td valign="top" width="50%">:-x</td>
<td valign="top" width="50%"><a href="javascript:addCode(':-x')"><img src="$cfg{imagesurl}/smilies/mad.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_dark">
<td valign="top" width="50%">[mad]</td>
<td valign="top" width="50%"><a href="javascript:addCode('[mad]')"><img src="$cfg{imagesurl}/smilies/mad.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_light">
<td valign="top" width="50%">[ninja]</td>
<td valign="top" width="50%"><a href="javascript:addCode('[ninja]')"><img src="$cfg{imagesurl}/smilies/ninja.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_dark">
<td valign="top" width="50%">[nonsense]</td>
<td valign="top" width="50%"><a href="javascript:addCode('[nonsense]')"><img src="$cfg{imagesurl}/smilies/nonsense.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_light">
<td valign="top" width="50%">[oops]</td>
<td valign="top" width="50%"><a href="javascript:addCode('[oops]')"><img src="$cfg{imagesurl}/smilies/oops.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_dark">
<td valign="top" width="50%">[rolleyes]</td>
<td valign="top" width="50%"><a href="javascript:addCode('[rolleyes]')"><img src="$cfg{imagesurl}/smilies/rolleyes.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_light">
<td valign="top" width="50%">:)</td>
<td valign="top" width="50%"><a href="javascript:addCode(':)')"><img src="$cfg{imagesurl}/smilies/smile.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_dark">
<td valign="top" width="50%">:-)</td>
<td valign="top" width="50%"><a href="javascript:addCode(':-)')"><img src="$cfg{imagesurl}/smilies/smile.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_light">
<td valign="top" width="50%">[smile]</td>
<td valign="top" width="50%"><a href="javascript:addCode('[smile]')"><img src="$cfg{imagesurl}/smilies/smile.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_dark">
<td valign="top" width="50%">:P</td>
<td valign="top" width="50%"><a href="javascript:addCode(':P')"><img src="$cfg{imagesurl}/smilies/tongue.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_light">
<td valign="top" width="50%">:-P</td>
<td valign="top" width="50%"><a href="javascript:addCode(':-P')"><img src="$cfg{imagesurl}/smilies/tongue.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_dark">
<td valign="top" width="50%">[tongue]</td>
<td valign="top" width="50%"><a href="javascript:addCode('[tongue]')"><img src="$cfg{imagesurl}/smilies/tongue.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_light">
<td valign="top" width="50%">;)</td>
<td valign="top" width="50%"><a href="javascript:addCode(';)')"><img src="$cfg{imagesurl}/smilies/wink.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_dark">
<td valign="top" width="50%">;-)</td>
<td valign="top" width="50%"><a href="javascript:addCode(';-)')"><img src="$cfg{imagesurl}/smilies/wink.gif" border="0" alt=""></a></td>
</tr>
<tr class ="tbl_row_light">
<td valign="top" width="50%">[wink]</td>
<td valign="top" width="50%"><a href="javascript:addCode('[wink]')"><img src="$cfg{imagesurl}/smilies/wink.gif" border="0" alt=""></a></td>
</tr>
</table>
</td>
</tr>
</table>
<br>
<center class="textsmall ">[<a href="javascript:window.close();">$nav{close_window}</a>]</center>
<br>

</body>

</html>
HTML

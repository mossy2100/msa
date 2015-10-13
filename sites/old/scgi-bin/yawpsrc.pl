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
# $Id: yawpsrc.pl,v 1.16 2004/04/13 06:54:34 d3m1g0d Exp $
# =====================================================================

# Global variables, change them according to site specifics.
%cfg = (
	# ---------------------------------------------------------------------
	# General settings.
	# ---------------------------------------------------------------------

	# The name of the website.
	pagename => 'Mars Society Australia',

	# The complete title of the website.
	pagetitle => 'Mars Society Australia',

	# Absolute path to the YaWPS cgi-bin diretory (NO trailing slash!).
	cgi_bin_dir => '/home/marssoc/public_html/scgi-bin',

	# Absolute path to the YaWPS non-cgi diretory (NO trailing slash!).
	non_cgi_dir => '/home/marssoc/public_html/yawps',

	# The URL to the YaWPS cgi-bin directory (NO trailing slash!).
	cgi_bin_url => 'http://marssociety.org.au/scgi-bin',

	# The URL to the YaWPS non-cgi directory (NO trailing slash!).
	non_cgi_url => 'http://marssociety.org.au/yawps',

	# Which language module to use.
	lang => 'english',

	# Character encoding.
	codepage => 'iso-8859-1',

	# User/IP log refresh time in minutes.
	ip_time => 5,

	# Default theme to use if user not logged in.
	default_theme => 'standard',

	# Do we require new users to be approved by an admin??
	enable_approvals => '0',

	# ---------------------------------------------------------------------
	# Email specific setting.
	# ---------------------------------------------------------------------

	# Email address of site responsible person.
	webmaster_email => 'sandy@bom.gov.au',

	# Mail delivery type. 
	# Possible values: 0..sendmail, 1..SMTP server, 2..NET::SMTP Module
	mail_type => 0,

	# The path to sendmail. NO -t switch
	mail_program => '/usr/sbin/sendmail',

	# SMTP server host name or IP address.
	smtp_server => 'localhost',

	# ---------------------------------------------------------------------
	# News specific settings.
	# ---------------------------------------------------------------------

	# Number of news to be displayed on the index page.
	max_news => 5,

	# Decide if users can submit news. 
	# Possible values: 0..no, 1..yes
	enable_user_articles => 1,

	# ---------------------------------------------------------------------
	# Forums specific settings.
	# ---------------------------------------------------------------------

	# Decide if guests can post in the forums. 
	# Possible values: 0..no, 1..yes
	enable_guest_posting => 1,

	# Forum notification feature. 
	# Possible values: 0..no, 1..yes
	enable_notification => 1,

	# How many days to keep track of read posts.
	max_log_days_old => 21,

	# ---------------------------------------------------------------------
	# Other settings.
	# ---------------------------------------------------------------------

	# Adjust time difference between your local time and 
	# sever time in hours (-12 to 12).
	time_offset => 0,

	# Define format of date string (0 to 5).
	date_format => 0,

	# Expiration date for the cookie.
	cookie_expire => '+24h',

	# How many items to be displayed per page.
	max_items_per_page => 25,

	# Picture size settings in pixels.
	picture_height => 93,
	picture_width  => 60,

	# Max. number of forumsposts for forumbox
	max_latest_posts => 5,

	# File locking settings. Possible values: 0..no, 1..yes
	use_flock => 1,

	# The extension of the cgi executable files (i.e. 'pl' or 'cgi').
	ext => 'cgi',

	# The common theme header.
	common_theme_header => join (
		"\n",
		('<tr>',
		'<td><a href="%cgi_bin_url%/index.%ext%">',
		'<img src="%non_cgi_url%/themes/%theme_name%/images/logo.gif" border="0" alt="">',
		'</a></td>',
		'<td align="right" valign="bottom" class="time">&nbsp;%date%&nbsp;</td>',
		'</tr>')
	),

	# The common theme footer.
	common_theme_footer => join (
		"\n",
		(
		'<td align="center" class="copyleft">This web site was made with <a href="http://yawps.sourceforge.net/" target="_blank">YaWPS</a>, a lightweight web portal system<br>',
		'written in <a href="http://www.perl.org/">Perl</a> by the YaWPS development team and released under the <a href="http://www.gnu.org/licenses/gpl.txt">GPL</a>.<br>'
		))
        );

# Do NOT edit any values below unless you know what you are doing!
$cfg{scriptdir}     = $cfg{cgi_bin_dir};
$cfg{datadir}       = $cfg{scriptdir} . '/db';
$cfg{modulesdir}    = $cfg{scriptdir} . '/modules';
$cfg{langdir}       = $cfg{scriptdir} . '/lang';

$cfg{imagesdir}     = $cfg{non_cgi_dir} . '/images';
$cfg{themesdir}     = $cfg{non_cgi_dir} . '/themes';

$cfg{memberdir}     = $cfg{datadir} . '/members';
$cfg{topicsdir}     = $cfg{datadir} . '/topics';
$cfg{articledir}    = $cfg{topicsdir} . '/articles';
$cfg{logdir}        = $cfg{datadir} . '/stats';
$cfg{linksdir}      = $cfg{datadir} . '/links';
$cfg{polldir}       = $cfg{datadir} . '/polls';
$cfg{boardsdir}     = $cfg{datadir} . '/forum';
$cfg{messagedir}    = $cfg{boardsdir} . '/messages';
$cfg{pagesdir}      = $cfg{datadir} . '/pages';
$cfg{blocksdir}     = $cfg{datadir} . '/blocks';
$cfg{calendardir}   = $cfg{datadir} . '/calendar';
$cfg{modulesavedir} = $cfg{datadir} . '/modules';
$cfg{yawpsnewsdir}  = $cfg{scriptdir};

$cfg{pageurl}       = $cfg{cgi_bin_url};
$cfg{modulesurl}    = $cfg{pageurl} . '/modules';

$cfg{themesurl}     = $cfg{non_cgi_url} . '/themes';
$cfg{imagesurl}     = $cfg{non_cgi_url} . '/images';
$cfg{yawpsnewsurl}  = $cfg{non_cgi_url};

%usr = (
	admin    => 'Administrator',
	mod      => 'Moderator',
	user     => 'User',
	anonuser => 'Guest'
    );

1;

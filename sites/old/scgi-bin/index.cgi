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
# $Id: index.cgi,v 1.17 2004/02/17 12:20:38 d3m1g0d Exp $
# =====================================================================

# Load necessary modules.
use strict;
use lib '.';
use yawps;

# Get user profile.
my %user_data = authenticate();

# Get the welcome message.
my $welcome_message = file2array("$cfg{datadir}/welcomemsg.txt", 1);

# Print start page.
print_header();
print_html($user_data{theme}, $nav{home});

# Print welcome message.
print <<HTML;
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
<td><p class="texttitle">$welcome_message->[0]</p>
$welcome_message->[1]<br>
<br><br></td>
</tr>
</table>
HTML

# Get the latest headlines.
my $top_news = get_latest_articles($cfg{max_news});

foreach (@{$top_news})
{
	my ($id, $subject, $poster, $postdate, $comments, $views, $cat) =
	    split (/\|/, $_);

	# Get correct category name and link.
	my ($cat_link, undef) = split (/\./, $cat);
	my $cats = file2array("$cfg{topicsdir}/cats.dat", 1);
	my $cat_name;

	foreach (@{$cats})
	{
		my ($name, $link) = split (/\|/, $_);
		if ($cat_link eq $link) { $cat_name = $name; }
	}

	# Comments counter format.
	my $comments_count =
	    $comments == 1 
	    ? $comments . " " . $msg{comment}
	    : $comments . " " . $msg{comments};
	my $user_profile = file2array("$cfg{memberdir}/$poster.dat", 1);

	# Get topic text.
	my $text = file2array("$cfg{articledir}/$id.txt", 1);
	my @text = split (/\|/, $text->[0]);

	# Format text.
	my $message        = do_ubbc($text[3]);
	my $formatted_date = format_date($postdate);

	print <<HTML;
<hr noshade="noshade" size="1">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
<td class="texttitle">$subject</td>
</tr>
<tr>
<td class="textsmall">$cat_name: $formatted_date $msg{by} <a href="$cfg{pageurl}/user.$cfg{ext}?op=view_profile;username=$poster">$user_profile->[1]</a></td>
</tr>
<tr>
<td>&nbsp;</td>
</tr>
<tr>
<td valign="top">
HTML

	# Make a preview of the topic.
	if (length($message) > 250)
	{
		my $tmp_message = substr($message, 0, 250);
		$tmp_message =~ s/(.*)\s.*/$1/;
		print <<HTML;
<img src="$cfg{imagesurl}/topics/$cat_link.gif" border="0" align="right" vspace="5" alt="$cat_name">
$tmp_message ...
</td>
</tr>
<tr>
<td align="right">[<a href="$cfg{pageurl}/topics.$cfg{ext}?op=view_topic;cat=$cat_link;id=$id">$nav{more}</a>] [<a href="$cfg{pageurl}/topics.$cfg{ext}?op=view_topic;cat=$cat_link;id=$id">$comments_count</a>]</td>
</tr>
</table>
HTML
	}

	# Topic is shorter than 250 bytes.
	else
	{
		print <<HTML;
<img src="$cfg{imagesurl}/topics/$cat_link.gif" border="0" align="right" vspace="5" alt="$cat_name">
$message
</td>
</tr>
<tr>
<td align="right">[<a href="$cfg{pageurl}/topics.$cfg{ext}?op=view_topic;cat=$cat_link;id=$id">$comments_count</a>]</td>
</tr>
</table>
HTML
	}
}

print_html($user_data{theme}, $nav{home}, 1);

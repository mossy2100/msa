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
# $Id: stats.cgi,v 1.15 2004/04/16 14:07:26 d3m1g0d Exp $
# =====================================================================

# Load necessary modules.
use strict;
use lib '.';
use yawps;

# Get user profile.
my %user_data = authenticate();

# Get access log data.
my $log = file2array("$cfg{logdir}/stats.dat", 1);

# Parse the access log.
my (%referer, %day, @user_agents);
foreach my $i (@{$log})
{
	if ($i =~ /(.*) - (.*) - \"(.*)\" - \"(.*)\"/)
	{
		my $date1 = $1;
		my ($date, $time) = split (/ - /, $date1);
		my ($day, $month, $year) = split (/\//, $date);

		$day = $year . $month . $day;

		my $host_name  = $2;
		my $user_agent = $3;
		my $referer    = $4;

		$referer{$referer}++;
		$day{$day}++;

		push (@user_agents, $user_agent);
	}
}

my $total_agent  = @user_agents || 1;
my $unknown      = $msg{unknown};
my $searchengine = $msg{search_engines};

# Get browser types.
my %browser = (
	'MSIE'        => 0,
	'Netscape'    => 0,
	'Opera'       => 0,
	'Lynx'        => 0,
	'Konqueror'   => 0,
	'WebTV'       => 0,
	$searchengine => 0,
	$unknown      => 0
    );

foreach my $agent (@user_agents)
{
	if    ($agent =~ /Opera/i) { $browser{'Opera'}++; next; }
	elsif ($agent =~ /Lynx/i)  { $browser{'Lynx'}++;  next; }
	elsif ($agent =~ /Konqueror\/(\d)/i || $agent =~ /Konqueror (\d)/i)
	{
		$browser{'Konqueror'}++;
		next;
	}
	elsif ($agent =~ /WebTV/i || $agent =~ /WebTV (\d)/i)
	{
		$browser{'WebTV'}++;
		next;
	}

	# Identify the most common spiders.
	elsif ($agent =~ /ArchitextSpider/i
		|| $agent =~ /Googlebot/i
		|| $agent =~ /InfoSeek/i
		|| $agent =~ /KIT_Fireball/i
		|| $agent =~ /libwww-perl/i
		|| $agent =~ /Lycos/i
		|| $agent =~ /Scooter/i
		|| $agent =~ /Slurp/i
		|| $agent =~ /UltraSeek/i
		|| $agent =~ /WebCrawler/i
		|| $agent =~ /WiseWire/i)
	{
		$browser{$searchengine}++;
		next;
	}
	elsif ($agent =~ /MSIE/i)
	{
		if ($agent =~ /MSIE (\d)/i) { $browser{'MSIE'}++; next; }
		else { $browser{$searchengine}++; }
	}
	elsif ($agent =~ /Mozilla/i)
	{
		if (($agent =~ /Mozilla\/5/i || $agent =~ /Mozilla 5/i)
			|| ($agent =~ /Mozilla\/(\d)/i || $agent =~ /Mozilla (\d)/i))
		{
			$browser{'Netscape'}++;
			next;
		}
		else { $browser{$searchengine}++; }
	}
	elsif ($agent ne '') { $browser{$unknown}++; next; }
	else { $browser{$unknown}++; }
}

# Get operating systems types.
my %os = (
	'Windows' => 0,
	'Mac/PPC' => 0,
	'Linux'   => 0,
	'SunOS'   => 0,
	'BSD'     => 0,
	'AIX'     => 0,
	'OS/2'    => 0,
	'Irix'    => 0,
	'BeOS'    => 0,
	'HP-UX'   => 0,
	$unknown  => 0
    );

foreach my $user_agent (@user_agents)
{
	if ($user_agent =~ /MSIE/i)
	{
		if ($user_agent =~ /Windows 95/i
			|| $user_agent =~ /Windows 98/i
			|| $user_agent =~ /Windows NT/i)
		{
			$os{'Windows'}++;
			next;
		}
		elsif ($user_agent =~ /Mac_PowerPC/i || $user_agent =~ /Macintosh/i)
		{
			$os{'Mac/PPC'}++;
			next;
		}
	}
	elsif ($user_agent =~ /Windows 95/i
		|| $user_agent =~ /Windows 98/i
		|| $user_agent =~ /Windows NT/i)
	{
		$os{'Windows'}++;
		next;
	}
	elsif ($user_agent =~ /Mac_PowerPC/i || $user_agent =~ /Macintosh/i)
	{
		$os{'Mac/PPC'}++;
		next;
	}
	elsif ($user_agent =~ /X11/i)
	{
		if    ($user_agent =~ /Linux/i) { $os{'Linux'}++; next; }
		elsif ($user_agent =~ /SunOS/i) { $os{'SunOS'}++; next; }
		elsif ($user_agent =~ /AIX/i)   { $os{'AIX'}++;   next; }
		elsif ($user_agent =~ /Irix/i)  { $os{'Irix'}++;  next; }
		elsif ($user_agent =~ /HP-UX/i) { $os{'HP-UX'}++; next; }
		else { $os{'BSD'}++; }
	}
	elsif ($user_agent =~ /Linux/i) { $os{'Linux'}++; next; }
	elsif ($user_agent =~ /Win16/i || $user_agent =~ /Windows 3\.1/i)
	{
		$os{'Windows'}++;
		next;
	}
	elsif ($user_agent =~ /OS\/2/i) { $os{'OS/2'}++; next; }
	elsif ($user_agent =~ /BeOS/i)  { $os{'BeOS'}++; next; }
	else { $os{$unknown}++; }
}

# Get total days of site activity.
my $total_days = 0;
foreach (keys %day) { $total_days++; }

# Get detailed hits statistic.
my $total         = scalar @{$log};
my $hits_per_day  = $total_days ? sprintf("%.2f", ($total / $total_days)) : 0;
my $hits_per_hour = $total_days ?
       sprintf("%.2f", ($total / $total_days / 24)) :
       0;

print_header();
print_html($user_data{theme}, $nav{statistics});
print <<HTML;
<table align="center" border="0" cellspacing="0" cellpadding="1">
<tr>
<td><b>$msg{total_visits}</b></td>
<td colspan="2"><b>$total</b></td>
</tr>
<tr>
<td><b>$msg{total_days}</b></td>
<td colspan="2"><b>$total_days</b></td>
</tr>
<tr>
<td><b>$msg{visits_per_day}</b></td>
<td colspan="2"><b>$hits_per_day</b></td>
</tr>
<tr>
<td><b>$msg{visits_per_hour}</b></td>
<td colspan="2"><b>$hits_per_hour</b></td>
</tr>
</table>
<br><br>
<table align="center" border="0" cellspacing="0" cellpadding="2">
<tr>
<td align="center" colspan="4"><b>$msg{browser}</b></td>
</tr>
HTML

# Print browser stats graphic.
foreach my $i (sort { $browser{$b} <=> $browser{$a} } keys %browser)
{
	my $img_width = int($browser{$i} * 200 / $total_agent);
	my $percent   = sprintf("%.2f", ($browser{$i} / $total_agent * 100));

	# Get name of icon.
	my $browser_pic = $i;
	$browser_pic =~ tr/A-Z/a-z/;
	if ($i eq $searchengine) { $browser_pic = "searchengines"; }
	if ($i eq $unknown)      { $browser_pic = "unknown"; }

	print <<HTML;
<tr>
<td><img src="$cfg{imagesurl}/stats/$browser_pic.gif" alt=""></td>
<td>$i</td>
<td>$browser{$i}</td>
<td><img src="$cfg{imagesurl}/leftbar.gif" width="7" height="14" alt=""><img src="$cfg{imagesurl}/mainbar.gif" width="$img_width" height="14" alt=""><img src="$cfg{imagesurl}/rightbar.gif" width="7" height="14" alt=""> $percent\%</td>
</tr>
HTML
}

print <<HTML;
</table>
<br><br>
<table align="center" border="0" cellspacing="0" cellpadding="2">
<tr>
<td align="center" colspan="4"><b>$msg{os}</b></td>
</tr>
HTML

# Print operation systems stats graphic.
foreach my $i (sort { $os{$b} <=> $os{$a} } keys %os)
{
	my $img_width = int($os{$i} * 200 / $total_agent);
	my $percent   = sprintf("%.2f", ($os{$i} / $total_agent * 100));

	# Get name of icon.
	my $os_pic = $i;
	$os_pic =~ tr/A-Z/a-z/;
	if ($os_pic eq "mac/ppc") { $os_pic = "mac"; }
	if ($os_pic eq "os/2")    { $os_pic = "os2"; }
	if ($i eq $unknown) { $os_pic = "unknown"; }

	print <<HTML;
<tr>
<td><img src="$cfg{imagesurl}/stats/$os_pic.gif" alt=""></td>
<td>$i</td>
<td>$os{$i}</td>
<td><img src="$cfg{imagesurl}/leftbar.gif" width="7" height="14" alt=""><img src="$cfg{imagesurl}/mainbar.gif" width="$img_width" height="14" alt=""><img src="$cfg{imagesurl}/rightbar.gif" width="7" height="14" alt=""> $percent\%</td>
</tr>
HTML
}

# Get member count.
my $members = file2array("$cfg{memberdir}/memberlist.dat", 1);
my $member_count = scalar @{$members};

# Get articles and replies.
my (@catnames, @catlinks);
my $topics_cat = file2array("$cfg{topicsdir}/cats.dat", 1);
my $topics_cat_count;

for (@{$topics_cat}) { $topics_cat_count++; }

foreach (@{$topics_cat})
{
	my @item = split (/\|/, $_);
	push (@catnames, $item[0]);
	push (@catlinks, $item[1]);
}

my $article_count = 0;
my $reply_count   = 0;
foreach (@catlinks)
{
	my $articles = file2array("$cfg{topicsdir}/$_.cat", 1);
	my $count    = 0;

	foreach (@{$articles})
	{
		my (undef, undef, undef, undef, $replies, undef) = split (/\|/, $_);
		$replies = ($replies eq '') ? 0 : $replies;

		$count++;
		$reply_count = $reply_count + $replies;
	}

	$article_count = $article_count + $count;
}

# Get unpublished articles.
my $pending_article_count = 0;
my $pending_articles      = file2array("$cfg{topicsdir}/newarticles.dat", 1);

for (@{$pending_articles}) { $pending_article_count++; }

# Get forum posts and replies.
my $forum_cats = file2array("$cfg{boardsdir}/cats.txt", 1);
my $post_count = 0;
my $topic_count = 0;

foreach (@{$forum_cats})
{
	my $cat_info = file2array("$cfg{boardsdir}/$_.cat", 1);

	foreach my $board (@{$cat_info}[2 .. $#$cat_info])
	{
		my $posts = file2array("$cfg{boardsdir}/$board.txt", 1);
		my $category_topics = scalar @{$posts};
		my $message_count   = 0;

		foreach (@{$posts})
		{
			my (
				undef, undef, undef, undef, $replies,
				undef, undef, undef, undef
			    )
			    = split (/\|/, $_);
			$message_count++;
			$message_count += $replies;
		}
		$post_count  += $message_count;
		$topic_count += $category_topics;
	}
}

# Get number of links categories.
my $link_cat_count = 0;
my $link_cats      = file2array("$cfg{linksdir}/linkcats.dat", 1);

for (@{$link_cats}) { $link_cat_count++; }

# Get number of links.
my $links_count = 0;

foreach (@{$link_cats})
{
	my @item = split (/\|/, $_);
	my $links = file2array("$cfg{linksdir}/$item[1].dat", 1);
	my $cat_links = scalar @{$links};
	$links_count = $links_count + $cat_links;
}

# Print YaWPS statistics.
print <<HTML;
</table>
<br><br>
<table align="center" border="0" cellspacing="0" cellpadding="2">
<tr>
<td align="center" colspan="2"><b>$msg{misc}</b></td>
</tr>
<tr>
<td><img src="$cfg{imagesurl}/stats/users.gif" alt="">&nbsp;$msg{member_countC} </td>
<td><b>$member_count</b></td>
</tr>
<tr>
<td><img src="$cfg{imagesurl}/stats/topics.gif" alt="">&nbsp;$msg{active_topicsC} </td>
<td><b>$topics_cat_count</b></td>
</tr>
<tr>
<td><img src="$cfg{imagesurl}/stats/articles.gif" alt="">&nbsp;$msg{published_articlesC} </td>
<td><b>$article_count</b></td>
</tr>
<tr>
<td><img src="$cfg{imagesurl}/stats/waiting.gif" alt="">&nbsp;$msg{articles_waitingC} </td>
<td><b>$pending_article_count</b></td>
</tr>
<tr>
<td><img src="$cfg{imagesurl}/stats/comments.gif" alt="">&nbsp;$msg{published_commentsC} </td>
<td><b>$reply_count</b></td>
</tr>
<tr>
<td><img src="$cfg{imagesurl}/stats/topics.gif" alt="">&nbsp;$msg{forum_threadsC} </td>
<td><b>$topic_count</b></td>
</tr>
<tr>
<td><img src="$cfg{imagesurl}/stats/forummessages.gif" alt="">&nbsp;$msg{forum_messagesC} </td>
<td><b>$post_count</b></td>
</tr>
<tr>
<td><img src="$cfg{imagesurl}/stats/topics.gif" alt="">&nbsp;$msg{links_catsC}</td>
<td><b>$link_cat_count</b></td>
</tr>
<tr>
<td><img src="$cfg{imagesurl}/stats/links.gif" alt="">&nbsp;$msg{published_linksC} </td>
<td><b>$links_count</b></td>
</tr>
<tr>
<td><img src="$cfg{imagesurl}/stats/scriptver.gif" alt="">&nbsp;$msg{yawps_versionC} </td>
<td><b>$VERSION</b></td>
</tr>
</table>
HTML

print_html($user_data{theme}, $nav{statistics}, 1);

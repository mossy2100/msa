package yawps;

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
# $Id: yawps.pm,v 1.49 2004/04/14 11:38:48 d3m1g0d Exp $
# =====================================================================

# Load necessary modules.
use strict;
use Carp;
use CGI qw(:standard);
use Fcntl qw(:DEFAULT :flock);

#use open ':utf8';

# Clean up the environment.
delete @ENV{qw(IFS CDPATH ENV BASH_ENV)};

$CGI::POST_MAX        = 1024 * 100;
$CGI::DISABLE_UPLOADS = 1;
$CGI::HEADERS_ONCE    = 1;

BEGIN
{

	# Figure out how to exit.
	use constant IS_MODPERL => $ENV{MOD_PERL};
	use subs qw(exit);
	*exit = IS_MODPERL ? \&Apache::exit : sub { CORE::exit };

	# Catch fatal errors.
	$SIG{__DIE__} = \&fatal_error;

	# Initialize global variables.
	use vars qw(
		$VERSION @ISA @EXPORT 
		%cfg %usr %err %msg %btn %nav %inf %hlp %months %week_days
	    );

	# Read YaWPS configuration variables.
	require 'yawpsrc.pl';

	# Load the language library.
	my $lang_lib = "$cfg{langdir}/$cfg{lang}.pl";
	if ($lang_lib =~ /^([\w.]+)$/) { $lang_lib = $1; }
	require $lang_lib;

	# Export global YaWPS routines and variables.
	require Exporter;
	require AutoLoader;

	$VERSION = '0.70';

	@ISA    = qw(Exporter AutoLoader);
	@EXPORT = qw(
		authenticate
		print_header
		print_html
		get_date
		format_date
		calc_time_diff
		user_error
		fatal_error
		print_ubbc_panel
		print_ubbc_image_selector
		do_ubbc html_escape
		html_to_text
		send_email
		get_latest_articles
		rdf_export
		read_forum_log
		write_forum_log
		load_ranks
		common_theme_header
		common_theme_footer
		acl_check
		update_subscriptions
		notify_users
		dir2array
		file2array
		file2scalar
		$VERSION %cfg %usr %err %msg %btn %nav %inf %hlp 
		%months %week_days %adm
		LOCK_EX LOCK_SH O_RDONLY O_WRONLY O_RDWR O_CREAT 
		O_EXCL O_APPEND O_TRUNC O_NONBLOCK
	    );
}

# Check if the installer has been deleted.
#if (-e "$cfg{scriptdir}/installer.$cfg{ext}") 
#{ 
#	croak "The installer (installer.$cfg{ext}) is still present. " .
#	'YaWPS will not run until this file is removed!'; 
#}

# ---------------------------------------------------------------------
# Check if user is logged on.
# ---------------------------------------------------------------------
sub authenticate
{

	# Check cookie and get status of user.
	my $query = new CGI;
	my $uid   = $query->cookie('yawps_uid') || '';
	my $pwd   = $query->cookie('yawps_pwd') || '';

	my %user_data  = ();
	my %guest_data = (
		uid         => $usr{anonuser},
		pwd         => '',
		nick        => $usr{anonuser},
		email       => '',
		website     => '',
		website_url => '',
		signature   => '',
		forum_posts => '',
		sec_level   => $usr{anonuser},
		icq         => '',
		pic         => '',
		joined      => '',
		topic_posts => '',
		comments    => '',
		theme       => $cfg{default_theme}
	    );

	# If user isn't logged in.
	unless ($uid && $pwd)
	{

		# Check bans.
		check_ban($guest_data{uid}, '');

		return %guest_data;
	}

	# Data integrity check.
	if ($uid =~ /^([\w.]+)$/) { $uid = $1; }
	else { croak "Unsafe data in $cfg{memberdir}/$uid.dat detected"; }

	# Otherwise get user's data.
	my $user_data = file2array("$cfg{memberdir}/$uid.dat", 1);
	my $sec_level = $user_data->[7];
	if ($user_data->[7] eq '') { $sec_level = $usr{user}; }

	%user_data = (
		uid         => $uid,
		pwd         => $user_data->[0],
		nick        => $user_data->[1],
		email       => $user_data->[2],
		website     => $user_data->[3],
		website_url => $user_data->[4],
		signature   => $user_data->[5],
		forum_posts => $user_data->[6],
		sec_level   => $sec_level,
		icq         => $user_data->[8],
		pic         => $user_data->[9],
		joined      => $user_data->[10],
		topic_posts => $user_data->[11],
		comments    => $user_data->[12],
		theme       => $user_data->[13]
	    );

	# Check password.
	if ($pwd ne $user_data{pwd})
	{
		croak "User data mismatch (password) for account $uid";
	}

	# Check approved. This can happen if the admin enables approvals while
	# the user is logged in. We will log them out nicely :)
	if ($cfg{enable_approvals} && !$user_data->[14] &&
	    $user_data{sec_level} ne $usr{admin})
	{
		# Check bans.
		check_ban($guest_data{uid}, '');

		return %guest_data;
	}

	# Check bans.
	check_ban($user_data{uid}, $user_data{email});

	return %user_data;
}

# ---------------------------------------------------------------------
# Print the HTTP header.
# ---------------------------------------------------------------------
sub print_header
{
	my $cookie = shift;

	# Get user profile.
	my $query     = new CGI;
	my %user_data = authenticate();

	# Load user theme.
	if ($user_data{theme} =~ /^([\w.]+)$/) { $user_data{theme} = $1; }
	require "$cfg{themesdir}/$user_data{theme}/theme.pl";

	if ($cookie)
	{
		print $query->header(
			-cookie  => $cookie,
			-expires => 'now',
			-charset => $cfg{codepage}
		    );
	}
	else
	{
		print $query->header(
			-expires => 'now', 
			-charset => $cfg{codepage}
		    );
	}
}

# ---------------------------------------------------------------------
# Print the HTML template.
# ---------------------------------------------------------------------
sub print_html
{
	my ($theme, $location, $type) = @_;

	# Header functions.
	if (!$type)
	{

		# Log visitors.
		log_visitors();

		# Build access log.
		access_log();
	}

	# Print the header.
	if (!$type) { theme_top($location); }

	# Print the footer.
	if ($type) { theme_bottom($location); }
}

# ---------------------------------------------------------------------
# Print Main menu and get installed modules.
# ---------------------------------------------------------------------
sub main_menu
{
	my @menu_content = @_;
	my $main_menu    = box_header($nav{main_menu});

	foreach (@menu_content)
	{
		my ($title, $link) = split (/\|/, $_);
		$main_menu .= menu_item($link, $title);
	}

	# Get list of installed modules.
	my $modules      = dir2array($cfg{modulesdir}, 1);
	my $mods_enabled = file2array("$cfg{modulesavedir}/enabled.dat", 1);

	if ($modules)
	{
		foreach (sort @{$modules})
		{
			if (m/^\.{1,2}$/) { next; }

			my ($module_name, $extension) = split (/\./, $_);
			if ($extension) { next; }
			if (grep(/^$module_name$/, @{$mods_enabled}) == 0) 
			{ 
				next; 
			}

			$main_menu .= menu_item(
				"$cfg{modulesurl}/$module_name/index.$cfg{ext}",
				$module_name);
		}
	}

	$main_menu .= box_footer();

	return $main_menu;
}

# ---------------------------------------------------------------------
# Display a box with actions, depending on user's permissions.
# ---------------------------------------------------------------------
sub user_panel
{

	# Get user profile.
	my $query     = new CGI;
	my %user_data = authenticate();

	# Get help topic.
	my $script_name = $ENV{SCRIPT_NAME};
	$script_name =~ s(^.*/)();
	my ($topic, undef) = split (/\./, $script_name);

	my $user_panel = box_header("$msg{my} $cfg{pagename}");

	# Print help link.
	$user_panel .=
	    menu_item("$cfg{pageurl}/help.$cfg{ext}?topic=$topic", $nav{help});

	# Print register link for guests only.
	if ($user_data{uid} eq $usr{anonuser})
	{
		$user_panel .= menu_item(
			    "$cfg{pageurl}/register.$cfg{ext}", 
			    $nav{new_user});
	}

	# Print special actions.
	if ($user_data{uid} ne $usr{anonuser})
	{
		$user_panel .= menu_item(
			"$cfg{pageurl}/user.$cfg{ext}?op=edit_profile;username=$user_data{uid}",
			$nav{edit_profile});
		$user_panel .= menu_item("$cfg{pageurl}/memberlist.$cfg{ext}",
			$nav{member_list});
		if ($cfg{enable_user_articles} == 1)
		{
			$user_panel .=
			    menu_item("$cfg{pageurl}/topics.$cfg{ext}?op=write_news",
				$nav{write_article});
		}
	}

	# Print admin link if user is authorized.
	if ($user_data{sec_level} eq $usr{admin}
		|| $user_data{sec_level} eq $usr{mod})
	{
		$user_panel .= menu_item(
			"$cfg{pageurl}/admin.$cfg{ext}", 
			$nav{site_admin});
	}

	# Print logout or login link, depending on status.
	if ($user_data{uid} eq $usr{anonuser})
	{
		$user_panel .=
		    menu_item("$cfg{pageurl}/login.$cfg{ext}", $nav{login});
	}
	else
	{
		$user_panel .= menu_item("$cfg{pageurl}/login.$cfg{ext}?op=logout",
			$nav{logout});
	}

	$user_panel .= box_footer();

	return $user_panel;
}

# ---------------------------------------------------------------------
# Display a box with current user status.
# ---------------------------------------------------------------------
sub user_status
{

	# Get user profile.
	my $query     = new CGI;
	my %user_data = authenticate();

	my $guests = 0;
	my $users  = 0;

	# Get visitor log.
	my $log = file2array("$cfg{datadir}/log.dat", 1);

	foreach (@{$log})
	{
		my ($name, $value) = split (/\|/, $_);
		if ($name =~ /\./) { $guests++ }
		else { $users++ }
	}

	my $user_status = box_header($nav{who_is_online});

	# Show login information, depending on user status.
	if ($user_data{uid} ne $usr{anonuser})
	{
		my $user_data = file2array(
			"$cfg{memberdir}/$user_data{uid}.dat", 
			1);

		# Get number of instant messages.
		my $instant_messages_count = 0;
		if ($user_data{uid} ne $usr{anonuser})
		{
			my $instant_messages = file2array(
				    "$cfg{memberdir}/$user_data{uid}.msg", 
				    1);
			if (@$instant_messages)
			{
				$instant_messages_count = scalar 
					@{$instant_messages};
			}
		}

		$user_status .= <<HTML;
<tr>
<td class="cat">$msg{logged_in_asC} '$user_data->[1]'</td>
</tr>
<tr>
<td class="cat">$msg{messagesC} <a href="$cfg{pageurl}/instant_messenger.$cfg{ext}" 
class="menu">$instant_messages_count</a></td>
</tr>
HTML
	}

	# Show online users and guests.
	$user_status .= <<HTML;
<tr>
<td class="cat">$msg{guestsC} $guests<br>
$msg{membersC} $users</td>
</tr>
HTML

	$user_status .= box_footer();

	return $user_status;
}

# ---------------------------------------------------------------------
# Display a box with current poll.
# ---------------------------------------------------------------------
sub current_poll
{

	# Get all polls.
	my ($id, $name);
	my $polls = file2array("$cfg{datadir}/polls/polls.txt", 1);
	my $current_poll = box_header($nav{poll});

	if (!@$polls)
	{
		$current_poll .=
		    qq(<tr>\n<td align="center" class="cat">$msg{no_polls}</td>\n</tr>);
	}
	else
	{
		($id, $name) = split (/\|/, $polls->[0]);
		$current_poll .= qq(<tr>\n<td class="cat">\n);

		# Get current poll data.
		my $file      = $id . '_q.dat';
		my $poll_data = file2array("$cfg{datadir}/polls/$file", 1);

		$current_poll .= <<HTML;
<form action="$cfg{pageurl}/polls.$cfg{ext}" method="post">
<table>
<tr>
<td align="center" class="cat">$name</td>
</tr>
<tr>
<td>&nbsp;</td>
</tr>
HTML

		# Print all possible questions.
		for (my $i = 0; $i <= $#{$poll_data}; $i++)
		{
			$current_poll .= <<HTML;
<tr>
<td valign="top" class="cat"><input type="radio" name="answer" 
value="$i">$poll_data->[$i]</td>
</tr>
HTML
		}

		$current_poll .= <<HTML;
<tr>
<td>&nbsp;</td>
</tr>
<tr>
<td align="center"><input type="hidden" name="op" value="do_vote">
<input type="hidden" name="id" value="$id">
<input type="hidden" name="submitted" value="1">
<input type="submit" value="$btn{vote}"><br>
<div class="cat"><a href="$cfg{pageurl}/polls.$cfg{ext}?op=view_poll;id=$id" 
class="menu">$nav{results}</a></div></td>
</tr>
</table>
</form>
</td>
</tr>
HTML
	}

	$current_poll .= box_footer();

	return $current_poll
}

# ---------------------------------------------------------------------
# Display a box with latest forum posts.
# ---------------------------------------------------------------------
sub latest_forum_posts
{

	# Get user profile.
	my $query     = new CGI;
	my %user_data = authenticate();

	# Get post data.
	my (@posts, @data, @sorted_posts, $latest_forum_posts);

	my $cats = file2array("$cfg{boardsdir}/cats.txt", 1);
	if (!@$cats) { return ''; }

	# Cycle through the categories.
	foreach my $cat (@{$cats})
	{
		my $cat_info = file2array("$cfg{boardsdir}/$cat.cat", 1);

		foreach my $board (@{$cat_info}[2 .. $#$cat_info])
		{

			# Check if board is private.
			if ($cat_info->[1] && 
			    $user_data{sec_level} ne $usr{admin} &&
			    $user_data{sec_level} ne $cat_info->[1])
			{
				next;
			}

			my $messages = file2array(
				"$cfg{boardsdir}/$board.txt", 
				1);

			foreach my $thread (@{$messages})
			{
				my ($num, $subject, $postdate, $replies);
				($num, $subject, undef, $postdate, $replies,
				    undef, undef, undef, undef)
				    = split (/\|/, $thread);

				push (@posts,
					join ('|', 
						$num, 
						$board, 
						$subject, 
						$postdate, 
						$replies));
			}
		}
	}

	for (0 .. $#posts)
	{
		my @fields = split (/\|/, $posts[$_]);
		for my $i (0 .. $#fields) { $data[$_][$i] = $fields[$i]; }
	}

	# Sort the posts.
	my @sorted = reverse sort { $a->[3] <=> $b->[3] } @data;
	for (@sorted)
	{
		my $sorted_row = join ('|', @$_);
		push (@sorted_posts, $sorted_row);
	}

	# Get the latest posts.
	$latest_forum_posts = box_header($nav{latest_posts});

	if (!@sorted_posts)
	{
		$latest_forum_posts .= qq(<tr>\n<td>&nbsp;</td>\n</tr>\n);
	}
	for (my $i = 0; $i < @sorted_posts && $i < $cfg{max_latest_posts}; $i++)
	{
		my ($num, $board, $subject, undef, $replies) =
		    split (/\|/, $sorted_posts[$i]);

		# Get last page's start post in this topic.
		my ($count, $strt) = (0, 0);
		while ($count * $cfg{max_items_per_page} < $replies + 1)
		{
			$strt = $count * $cfg{max_items_per_page};
			$count++;
		}

		$latest_forum_posts .= menu_item(
			"$cfg{pageurl}/forum.$cfg{ext}?op=view_thread;board=$board;thread=$num;start=$strt#$replies",
			$subject);
	}

	$latest_forum_posts .= box_footer();

	return $latest_forum_posts;
}

# ---------------------------------------------------------------------
# Print user defined blocks.
# ---------------------------------------------------------------------
sub show_blocks
{
	my $position = shift;
	my $blocks;

	# Get all available blocks.
	my $all_blocks = file2array("$cfg{blocksdir}/blocks.dat", 1);
	if (!@$all_blocks) { return ''; }

	# Print links to the pages.
	foreach (@{$all_blocks})
	{
		my ($id, $name, $type, $active) = split (/\|/, $_);

		# Check which blocks to get.
		if ($active && $position eq $type)
		{

			# Get block data.
			my $block_data = file2scalar("$cfg{blocksdir}/$id.txt", 1);

			# Print block.
			$blocks .= box_header($name);
			$blocks .= $block_data;
			$blocks .= box_footer();
		}
	}

	return $blocks;
}

# ---------------------------------------------------------------------
# Print a random quote.
# ---------------------------------------------------------------------
sub show_quote
{

	# Get all available quotes.
	my $quotes = file2array("$cfg{datadir}/quotes.dat", 1);
	if (!@$quotes) { return '&nbsp;'; }

	# Get a random quote id.
	my $count = scalar @{$quotes} || 0;
	srand();
	my $rand = int(rand($count));

	# Get quote data.
	my @item;
	for (my $i = 0; $i <= $#{$quotes}; $i++)
	{
		@item = split (/\|/, $quotes->[$i]);
		if ($i == $rand) { last; }
	}

	return '<i>' . $item[1] . "</i><br>\n<small>" . $item[2] . '</small>';
}

# ---------------------------------------------------------------------
# Print meta tags to site HTML output.
# ---------------------------------------------------------------------
sub get_meta_tags
{
	my $data = file2array("$cfg{datadir}/meta.txt", 1);

	if (!@$data) { return; }
	if ($data->[2] == 1)
	{
		return <<HTML;
<meta name="description" content="$data->[0]">
<meta name="keywords" content="$data->[1]">
HTML
	}
}

# ---------------------------------------------------------------------
# Check for new instant messages.
# ---------------------------------------------------------------------
sub check_ims
{

	# Get user profile.
	my $query            = new CGI;
	my %user_data        = authenticate();
	my $instant_messages =
	    file2array("$cfg{memberdir}/$user_data{uid}.msg", 1);
	my $im_alert = 0;

	if (!@$instant_messages) { return $im_alert; }

	# Check if there are new IMs.
	foreach (@{$instant_messages})
	{
		my @message = split (/\|/, $_);
		if ($message[5]) { $im_alert++; }
	}

	if ($im_alert)
	{
		$im_alert = <<HTML;
<script language="javascript"><!--
alert("$inf{im_waiting}")
// --></script>
HTML
	}

	# Unmark new IMs (only warn the user one time).
	$user_data{uid} =
	    ($user_data{uid} =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	sysopen(FH, "$cfg{memberdir}/$user_data{uid}.msg",
		O_WRONLY | O_TRUNC | O_CREAT);
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	foreach (@{$instant_messages})
	{
		my @message = split (/\|/, $_);
		print FH
		    "$message[0]|$message[1]|$message[2]|$message[3]|$message[4]\n";
	}
	close(FH);

	return $im_alert;
}

# ---------------------------------------------------------------------
# Log visitors and users.
# ---------------------------------------------------------------------
sub log_visitors
{

	# Get user profile.
	my $query     = new CGI;
	my %user_data = authenticate();

	# Get current time.
	my $date = get_date();

	# Get online users/visitors.
	my $log = file2array("$cfg{datadir}/log.dat", 1);

	# Check if user or guest.
	my $logname = $user_data{uid};
	if ($logname eq $usr{anonuser})
	{
		$logname = $ENV{REMOTE_ADDR} || '127.0.0.1';
	}

	sysopen(FH, "$cfg{datadir}/log.dat", O_WRONLY | O_TRUNC | O_CREAT)
	    or user_error("$err{not_writable} $cfg{datadir}/log.dat ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	print FH "$logname|$date\n";

	# Refresh the log.
	if ($log)
	{
		foreach (@{$log})
		{
			my ($name, $value) = split (/\|/, $_);
			my $result = calc_time_diff($value, $date);

			if ($name ne $logname && $result <= $cfg{ip_time} && $result >= 0)
			{
				print FH "$_\n";
			}
		}
	}

	close(FH);
}

# ---------------------------------------------------------------------
# Log IP addresses and build access log.
# ---------------------------------------------------------------------
sub access_log
{
	my @skip_ip = ("127.0.0.1");
	my $check   = 0;

	my $host = $ENV{REMOTE_ADDR};
	if ($ENV{REMOTE_HOST}) { $host = $ENV{REMOTE_HOST}; }

	# Skip definded IP addresses.
	foreach (@skip_ip)
	{
		if ($host =~ /$_/) { $check = 1; last; }
	}

	# Process all other IPs.
	if ($check == 0)
	{
		my $this_time = time;
		my $log       = file2array("$cfg{logdir}/ip.log", 1);

		# Refresh online IPs.
		sysopen(FH, "$cfg{logdir}/ip.log", O_WRONLY | O_TRUNC | O_CREAT);
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }

		if ($log)
		{
			foreach (@{$log})
			{
				my ($ip_address, $time_stamp) = split (/\|/, $_);
				if ($this_time < $time_stamp + 60 * $cfg{ip_time})
				{
					if ($ip_address eq $host) 
					{ 
						$check = 1; 
					}
					else 
					{ 
						print FH "$ip_address|$time_stamp\n"; 
					}
				}
			}
		}
		print FH "$host|$this_time\n";

		close(FH);
	}

	# Build access log.
	if ($check == 0)
	{

		# Get referer.
		my $referer = $ENV{HTTP_REFERER} ? $ENV{HTTP_REFERER} : '-';

		# Get current date.
		my $date = format_date('', -1);

		# Update log file.
		sysopen(FH, "$cfg{logdir}/stats.dat", O_WRONLY | O_APPEND | O_CREAT)
		    or die $!;
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		print FH "$date - $host - \"$ENV{HTTP_USER_AGENT}\" - \"$referer\"\n";
		close(FH);
	}
}

# ---------------------------------------------------------------------
# Get the current date and time in epoch seconds.
# ---------------------------------------------------------------------
sub get_date
{
	return time + 3600 * $cfg{time_offset};
}

# ---------------------------------------------------------------------
# Format date output.
# ---------------------------------------------------------------------
sub format_date
{
	my $date = shift || &get_date;
	my $type = shift || $cfg{date_format};

	# Get user profile.
	my $query     = new CGI;
	my %user_data = authenticate();

	# Get selected date format.
	my $sel_date_format =
	    (exists $user_data{date_format}) 
	    ? $user_data{date_format}
	    : $cfg{date_format};
	$sel_date_format = ($type || $type ne '') ? $type : $cfg{date_format};
	$date            = ($date || $date ne '') ? $date : get_date();

	my ($sec, $min, $hour, $mday, $mon, $year, $wday, $yday, $isdst) =
	    localtime($date + 3600 * $cfg{time_offset});
	my ($formatted_date, $cmon, $cday, $syear);

	$year += 1900;

	$cmon  = $mon + 1;
	$syear = sprintf("%02d", $year % 100);

	if ($hour < 10) { $hour = 0 . $hour; }
	if ($min < 10)  { $min  = 0 . $min; }
	if ($sec < 10)  { $sec  = 0 . $sec; }

	if ($cmon < 10) { $cmon = 0 . $cmon; }
	$cday = ($mday < 10) ? 0 . $mday : $mday;

	# Format: 01/15/00, 15:15:30
	if (!$sel_date_format || $sel_date_format == 0)
	{
		$formatted_date = "$cmon/$cday/$syear, $hour:$min:$sec";
	}

	# Format: 15.01.00, 15:15:30
	if ($sel_date_format == 1)
	{
		$formatted_date = "$cday.$cmon.$syear, $hour:$min:$sec";
	}

	# Format: 15.01.2000, 15:15:30
	if ($sel_date_format == 2)
	{
		$formatted_date = "$cday.$cmon.$year, $hour:$min:$sec";
	}

	# Format: Jan 15th, 2000, 3:15pm
	if ($sel_date_format == 3)
	{
		my $ampm = 'am';
		if ($hour > 11) { $ampm = 'pm'; }
		if ($hour > 12) { $hour = $hour - 12; }
		if ($hour == 0) { $hour = 12; }

		if ($mday > 10 && $mday < 20) { $cday = '<sup>th</sup>'; }
		elsif ($mday % 10 == 1) { $cday = '<sup>st</sup>'; }
		elsif ($mday % 10 == 2) { $cday = '<sup>nd</sup>'; }
		elsif ($mday % 10 == 3) { $cday = '<sup>rd</sup>'; }
		else { $cday = '<sup>th</sup>'; }

		$formatted_date = "$months{$mon} $mday$cday, $year, $hour:$min$ampm";
	}

	# Format: 15. Jan 2000, 15:15
	if ($sel_date_format == 4)
	{
		$formatted_date = "$wday. $months{$mon} $year, $hour:$min";
	}

	# Format: 01/15/00, 3:15pm
	if ($sel_date_format == 5)
	{
		my $ampm = 'am';
		if ($hour > 11) { $ampm = 'pm'; }
		if ($hour > 12) { $hour = $hour - 12; }
		if ($hour == 0) { $hour = 12; }

		$formatted_date = "$cmon/$cday/$syear, $hour:$min$ampm";
	}

	# Format: Sunday, 15 January, 2000
	if ($sel_date_format == 6)
	{
		$formatted_date = "$week_days{$wday}, $mday $months{$mon} $year";
	}

	# Format: 15/01/2000 - 03:15:30 (internal stats logfile format).
	if ($sel_date_format == -1)
	{
		$formatted_date = "$cday/$cmon/$year - $hour:$min:$sec";
	}

	return $formatted_date;
}

# ---------------------------------------------------------------------
# Calculate difference between two dates.
# ---------------------------------------------------------------------
sub calc_time_diff
{
	my ($in_date1, $in_date2, $type) = @_;
	my $result = $in_date1 - $in_date2;

	# Calculate difference in hours.
	if (!$type) { $result = int($result / 3600); }

	# Calculate difference in days.
	else { $result = int($result / (24 * 3600)); }

	return $result;
}

# ---------------------------------------------------------------------
# Display an error message if user input isn't valid.
# ---------------------------------------------------------------------
sub user_error
{
	my ($error, $theme) = @_;

	$| = 1;
	print_header();
	print_html($theme, $nav{error});
	print $error;
	print_html($theme, $nav{error}, 1);

	exit();
}

# ---------------------------------------------------------------------
# Display fatal errors.
# ---------------------------------------------------------------------
sub fatal_error
{
	my $error = shift;
	my ($msg, $path) = split " at ", $error;
	my $query = new CGI;

	print_header();
	print $query->start_html(-title => 'YaWPS ' . $VERSION . ' Fatal Error');
	print <<HTML;
<font face="arial, verdana, helvetica" size="6" 
color="#333366">YaWPS $VERSION Fatal Error</font>
<hr size="1" color="#000000" noshade>
<font face="arial, verdana, helvetica" size="3" color="#00000">YaWPS has 
exited with the following error:<br><br>
<b>$msg</b><br><br>This error was reported at: <font color="#000099" 
face="courier, courier new, arial, verdana, helvetica">$path</font><br><br>
<font size="3" color="#990000"><b>Please inform the webmaster if this 
error persists.</b></font>
HTML
	print $query->end_html();

	exit();
}

# ---------------------------------------------------------------------
# Print the UBBC panel.
# ---------------------------------------------------------------------
sub print_ubbc_panel
{
	my $panel = <<HTML;
<a href="javascript:addCode('[b][/b]')"><img src="$cfg{imagesurl}/forum/bold.gif" align="bottom" width="23" height="22" alt="$msg{bold}" border="0"></a>
<a href="javascript:addCode('[i][/i]')"><img src="$cfg{imagesurl}/forum/italicize.gif" align="bottom" width="23" height="22" alt="$msg{italic}" border="0"></a>
<a href="javascript:addCode('[u][/u]')"><img src="$cfg{imagesurl}/forum/underline.gif" align="bottom" width="23" height="22" alt="$msg{underline}" border="0"></a>
<a href="javascript:addCode('[center][/center]')"><img src="$cfg{imagesurl}/forum/center.gif" align="bottom" width="23" height="22" alt="$msg{center}" border="0"></a>
<a href="javascript:addCode('[url][/url]')"><img src="$cfg{imagesurl}/forum/url.gif" align="bottom" width="23" height="22" alt="$msg{insert_link}" border="0"></a>
<a href="javascript:addCode('[email][/email]')"><img src="$cfg{imagesurl}/forum/email2.gif" align="bottom" width="23" height="22" alt="$msg{insert_email}" border="0"></a>
<a href="javascript:addCode('[code][/code]')"><img src="$cfg{imagesurl}/forum/code.gif" align="bottom" width="23" height="22" alt="$msg{insert_code}" border="0"></a>
<a href="javascript:addCode('[quote][/quote]')"><img src="$cfg{imagesurl}/forum/quote2.gif" align="bottom" width="23" height="22" alt="$msg{quote}" border="0"></a>
<a href="javascript:addCode('[list][*][*][*][/list]')"><img src="$cfg{imagesurl}/forum/list.gif" align="bottom" width="23" height="22" alt="$msg{insert_list}" border="0"></a>
<a href="javascript:void(0)" onClick="window.open('$cfg{pageurl}/smilies.$cfg{ext}','_blank','scrollbars=yes,toolbar=no,height=270,width=270')"><img src="$cfg{imagesurl}/forum/smilie.gif" align="bottom" width="23" height="22" alt="$msg{insert_smilie}" border="0"></a><br>
<select name="color" onChange="showColor(this.options[this.selectedIndex].value)">
<option value="Black" selected>$msg{black}</option>
<option value="Red">$msg{red}</option>
<option value="Yellow">$msg{yellow}</option>
<option value="Pink">$msg{pink}</option>
<option value="Green">$msg{green}</option>
<option value="Orange">$msg{orange}</option>
<option value="Purple">$msg{purple}</option>
<option value="Blue">$msg{blue}</option>
<option value="Beige">$msg{beige}</option>
<option value="Brown">$msg{brown}</option>
<option value="Teal">$msg{teal}</option>
<option value="Navy">$msg{navy}</option>
<option value="Maroon">$msg{maroon}</option>
<option value="LimeGreen">$msg{lime}</option>
</select>
HTML
	return $panel;
}

# ---------------------------------------------------------------------
# Print the UBBC image selector.
# ---------------------------------------------------------------------
sub print_ubbc_image_selector
{
	my $selected_icon = shift || 'xx';

	# Display the pre selected icon?
	my $pre_selected_icon = '';
	if ($selected_icon && $selected_icon ne 'xx')
	{
		$pre_selected_icon = qq(<option value="$selected_icon"></option>\n);
	}

	my $selector = <<HTML;
<script language="javascript" type="text/javascript"><!--
function showImage() {
document.images.icons.src="$cfg{imagesurl}/forum/"+
document.creator.icon.options[document.creator.icon.selectedIndex].value+
".gif";
}
// --></script>
<select name="icon" onChange="showImage()">
$pre_selected_icon<option value="xx">$msg{standard}</option>
<option value="thumbup">$msg{thumb_up}</option>
<option value="thumbdown">$msg{thumb_down}</option>
<option value="exclamation">$msg{excl_marl}</option>
<option value="question">$msg{question_mark}</option>
<option value="lamp">$msg{lamp}</option>
</select>
<img src="$cfg{imagesurl}/forum/$selected_icon.gif" name="icons" width="15" 
height="15" border="0" hspace="15" alt=""></td>
</tr>
<tr>
<td valign=top><b>$msg{textC}</b></td>
<td>
<script language="javascript" type="text/javascript">
<!--
function addCode(anystr) { 
document.creator.message.value+=anystr;
} 
function showColor(color) { 
document.creator.message.value+="[color="+color+"][/color]";
}
// -->
</script>
HTML
	return $selector;
}

# ---------------------------------------------------------------------
# Convert UBBC tags.
# ---------------------------------------------------------------------
sub do_ubbc
{
	my $message = shift;

	# Make the smilies.
	if ($message =~ /^\#nosmileys/) { $message =~ s/^\#nosmileys//; }
	else
	{
		$message =~
		    s~\[bones\]~<img src="$cfg{imagesurl}/smilies/bones.gif" alt="">~g;
		$message =~
		    s~\[bounce\]~<img src="$cfg{imagesurl}/smilies/bounce.gif" alt="">~g;
		$message =~
		    s~\:-\?~<img src="$cfg{imagesurl}/smilies/confused.gif" alt="">~g;
		$message =~
		    s~\[confused\]~<img src="$cfg{imagesurl}/smilies/confused.gif" alt="">~g;
		$message =~
		    s~\Q8)\E~<img src="$cfg{imagesurl}/smilies/cool.gif" alt="">~g;
		$message =~
		    s~\Q8-)\E~<img src="$cfg{imagesurl}/smilies/cool.gif" alt="">~g;
		$message =~
		    s~\[cool\]~<img src="$cfg{imagesurl}/smilies/cool.gif" alt="">~g;
		$message =~
		    s~\[cry\]~<img src="$cfg{imagesurl}/smilies/cry.gif" alt="">~g;
		$message =~
		    s~\:o~<img src="$cfg{imagesurl}/smilies/eek.gif" alt="">~g;
		$message =~
		    s~\:\-o~<img src="$cfg{imagesurl}/smilies/eek.gif" alt="">~g;
		$message =~
		    s~\[eek\]~<img src="$cfg{imagesurl}/smilies/eek.gif" alt="">~g;
		$message =~
		    s~\[evil\]~<img src="$cfg{imagesurl}/smilies/evil.gif" alt="">~g;
		$message =~
		    s~\:\(~<img src="$cfg{imagesurl}/smilies/frown.gif" alt="">~g;
		$message =~
		    s~\:-\(~<img src="$cfg{imagesurl}/smilies/frown.gif" alt="">~g;
		$message =~
		    s~\[frown\]~<img src="$cfg{imagesurl}/smilies.gif" alt="">~g;
		$message =~
		    s~\:D~<img src="$cfg{imagesurl}/smilies/grin.gif" alt="">~g;
		$message =~
		    s~\:-D~<img src="$cfg{imagesurl}/smilies/grin.gif" alt="">~g;
		$message =~
		    s~\[grin\]~<img src="$cfg{imagesurl}/smilies/grin.gif" alt="">~g;
		$message =~
		    s~\[lol\]~<img src="$cfg{imagesurl}/smilies/lol.gif" alt="">~g;
		$message =~
		    s~\:x~<img src="$cfg{imagesurl}/smilies/mad.gif" alt="">~g;
		$message =~
		    s~\:-x~<img src="$cfg{imagesurl}/smilies/mad.gif" alt="">~g;
		$message =~
		    s~\[mad\]~<img src="$cfg{imagesurl}/smilies/mad.gif" alt="">~g;
		$message =~
		    s~\[ninja\]~<img src="$cfg{imagesurl}/smilies/ninja.gif" alt="">~g;
		$message =~
		    s~\[nonsense\]~<img src="$cfg{imagesurl}/smilies/nonsense.gif" alt="">~g;
		$message =~
		    s~\[oops\]~<img src="$cfg{imagesurl}/smilies/oops.gif" alt="">~g;
		$message =~
		    s~\[razz\]~<img src="$cfg{imagesurl}/smilies/razz.gif" alt="">~g;
		$message =~
		    s~\[rolleyes\]~<img src="$cfg{imagesurl}/smilies/rolleyes.gif" alt="">~g;
		$message =~
		    s~\:\)~<img src="$cfg{imagesurl}/smilies/smile.gif" alt="">~g;
		$message =~
		    s~\:-\)~<img src="$cfg{imagesurl}/smilies/smile.gif" alt="">~g;
		$message =~
		    s~\[smile\]~<img src="$cfg{imagesurl}/smilies/smile.gif" alt="">~g;
		$message =~
		    s~\:P~<img src="$cfg{imagesurl}/smilies/tongue.gif" alt="">~g;
		$message =~
		    s~\:-P~<img src="$cfg{imagesurl}/smilies/tongue.gif" alt="">~g;
		$message =~
		    s~\[tongue\]~<img src="$cfg{imagesurl}/smilies/tongue.gif" alt="">~g;
		$message =~
		    s~\;\)~<img src="$cfg{imagesurl}/smilies/wink.gif" alt="">~g;
		$message =~
		    s~\;-\)~<img src="$cfg{imagesurl}/smilies/wink.gif" alt="">~g;
		$message =~
		    s~\[wink\]~<img src="$cfg{imagesurl}/smilies/wink.gif" alt="">~g;
	}

	# Convert the UBBC tags.
	$message =~ s~\[\[~\{\{~g;
	$message =~ s~\]\]~\}\}~g;
	$message =~ s~\n\[~\[~g;
	$message =~ s~\]\n~\]~g;

	$message =~ s~\[hr\]\n~<hr size="1">~g;
	$message =~ s~\[hr\]~<hr size="1">~g;

	$message =~ s~\[b\]~<b>~isg;
	$message =~ s~\[\/b\]~</b>~isg;

	$message =~ s~\[i\]~<i>~isg;
	$message =~ s~\[\/i\]~</i>~isg;

	$message =~ s~\[u\]~<u>~isg;
	$message =~ s~\[\/u\]~</u>~isg;

	$message =~ s~\[move\](.+?)\[/move\]~<marquee>$1</marquee>~isg;

	$message =~ s~\[img\](.+?)\[\/img\]~<img src="$1" alt="">~isg;
	$message =~
	    s~\[img width=(\d+) height=(\d+)\](.+?)\[/img\]~<img src="$3" width="$1" height="$2" alt="">~isg;
	$message =~
	    s~\[aright_img\](.+?)\[\/img\]~<img align="right" hspace="5" src="$1" alt="">~isg;
	$message =~
	    s~\[aleft_img\](.+?)\[\/img\]~<img align="left" hspace="5" src="$1" alt="">~isg;

	$message =~
	    s~\[iframe\](.+?)\[\/iframe\]~<iframe src="$1" width="480" height="400"></iframe>~isg;

	$message =~
	    s~\[color=([\w#]+)\](.*?)\[/color\]~<font color="$1">$2</font>~isg;

	$message =~
	    s~\[quote\]<br>(.+?)<br>\[\/quote\]~<blockquote><hr>$1<hr></blockquote>~isg;
	$message =~
	    s~\[quote\](.+?)\[\/quote\]~<blockquote><hr><b>$1</b><hr></blockquote>~isg;

	$message =~ s~\[sup\]~<sup>~isg;
	$message =~ s~\[\/sup\]~</sup>~isg;

	$message =~ s~\[sub\]~<sub>~isg;
	$message =~ s~\[\/sub\]~</sub>~isg;

	$message =~ s~\[center\]~<center>~isg;
	$message =~ s~\[\/center\]~</center>~isg;

	$message =~ s~\[list\]~<ul>~isg;
	$message =~ s~\[\*\]~<li>~isg;
	$message =~ s~\[\/list\]~</ul>~isg;

	$message =~ s~\[pre\]~<pre>~isg;
	$message =~ s~\[\/pre\]~</pre>~isg;

	if ($message =~ m~\[table\]\s*(.+?)\s*\[tr\]~i)
	{
		while ($message =~
			s~<marquee>(.*?)\[table\](.*?)\[/table\](.*?)</marquee>~<marquee>$1<table>$2</table>$3</marquee>~s
		    )
		{
		}
		while ($message =~
			s~<marquee>(.*?)\[table\](.*?)</marquee>(.*?)\[/table\]~<marquee>$1\[//table\]$2</marquee>$3\[//table\]~s
		    )
		{
		}
		while ($message =~
			s~\[table\](.*?)<marquee>(.*?)\[/table\](.*?)</marquee>~\[//table\]$1<marquee>$2\[//table\]$3</marquee>~s
		    )
		{
		}
		$message =~
		    s~\n{0,1}\[table\]\n*(.+?)\n*\[/table\]\n{0,1}~<table>$1</table>~isg;

		while ($message =~
			s~\<table\>(.*?)\n*\[tr\]\n*(.*?)\n*\[/tr\]\n*(.*?)\</table\>~<table>$1<tr>$2</tr>$3</table>~is
		    )
		{
		}
		while ($message =~
			s~\<tr\>(.*?)\n*\[td\]\n{0,1}(.*?)\n{0,1}\[/td\]\n*(.*?)\</tr\>~<tr>$1<td>$2</td>$3</tr>~is
		    )
		{
		}
	}

	$message =~
	    s~\[email\]\s*(\S+?\@\S+?)\s*\[/email\]~<A href="mailto:$1">$1</a>~isg;
	$message =~
	    s~\[email=\s*(\S+?\@\S+?)\]\s*(.*?)\s*\[/email\]~<a href="mailto:$1">$2</a>~isg;

	$message =~
	    s~\[url\]www\.\s*(.+?)\s*\[/url\]~<a href="http://www.$1" target="_blank">www.$1</a>~isg;
	$message =~
	    s~\[url=\s*(\w+\://.+?)\](.+?)\s*\[/url\]~<a href="$1" target="_blank">$2</a>~isg;
	$message =~
	    s~\[url=\s*(.+?)\]\s*(.+?)\s*\[/url\]~<a href="http://$1" target="_blank">$2</a>~isg;
	$message =~
	    s~\[url\]\s*(.+?)\s*\[/url\]~<a href="$1" target="_blank">$1</a>~isg;

	$message =~
	    s~([^\w\"\=\[\]]|[\n\b]|\A)\\*(\w+://[\w\~\.\;\:\,\$\-\+\!\*\?/\=\&\@\#\%]+[\w\~\.\;\:\$\-\+\!\*\?/\=\&\@\#\%])~$1<a href="$2" target="_blank">$2</a>~isg;
	$message =~
	    s~([^\"\=\[\]/\:\.]|[\n\b]|\A)\\*(www\.[\w\~\.\;\:\,\$\-\+\!\*\?/\=\&\@\#\%]+[\w\~\.\;\:\$\-\+\!\*\?/\=\&\@\#\%])~$1<a href="http://$2" target="_blank">$2</a>~isg;

	$message =~ s~\{\{~\[~g;
	$message =~ s~\}\}~\]~g;

	return $message;
}

# ---------------------------------------------------------------------
# Escape HTML tags.
# ---------------------------------------------------------------------
sub html_escape
{
	my $text = shift;

	$text =~ s/&/&amp;/g;
	$text =~ s/"/&quot;/g;
	$text =~ s/  / \&nbsp;/g;
	$text =~ s/</&lt;/g;
	$text =~ s/>/&gt;/g;
	$text =~ s/\t/ \&nbsp; \&nbsp; \&nbsp;/g;
	$text =~ s/\|/\&#124;/g;
	$text =~ s/\n/<br>/g;
	$text =~ s/\cM//g;

	return $text;
}

# ---------------------------------------------------------------------
# Transform HTML tags.
# Usage: my $html = html_to_text($html);
# ---------------------------------------------------------------------
sub html_to_text
{
	my $html = shift;

	$html =~ s/&amp;/&/g;
	$html =~ s/&quot;/"/g;
	$html =~ s/ \&nbsp;/  /g;
	$html =~ s/&lt;/</g;
	$html =~ s/&gt;/>/g;
	$html =~ s/ \&nbsp; \&nbsp; \&nbsp;/\t/g;
	$html =~ s/\&#124;/\|/g;
	$html =~ s/<br>/\n/g;

	return $html;
}

# ---------------------------------------------------------------------
# Send emails.
# ---------------------------------------------------------------------
sub send_email
{
	my ($from, $to, $subject, $message) = @_;
	my ($x, $here, $there, $null);

	# Format input.
	$to      =~ s/[ \t]+/, /g;
	$from    =~ s/.*<([^\s]*?)>/$1/;
	$message =~ s/^\./\.\./gm;
	$message =~ s/\r\n/\n/g;
	$message =~ s/\n/\r\n/g;

	$cfg{smtp_server} =~ s/^\s+//g;
	$cfg{smtp_server} =~ s/\s+$//g;

	# Send email via SMTP.
	if ($cfg{mail_type} == 1)
	{
		($x, $x, $x, $x, $here)  = gethostbyname($null);
		($x, $x, $x, $x, $there) = gethostbyname($cfg{smtp_server});

		my $thisserver   = pack('S n a4 x8', 2, 0,  $here);
		my $remoteserver = pack('S n a4 x8', 2, 25, $there);

		if (!(socket(S, 2, 1, 6))) { croak "Socket failure. $!"; }
		if (!(bind(S, $thisserver))) { croak "Bind failure. $!"; }
		if (!(connect(S, $remoteserver)))
		{
			croak "Connection to $cfg{smtp_server} has failed. $!";
		}

		my $oldfh = select(S);
		$| = 1;
		select($oldfh);
		$_ = <S>;
		if ($_ !~ /^220/)
		{
			croak "Sending Email: data in Connect error - 220. $!";
		}
		print S "HELO $cfg{smtp_server}\r\n";
		$_ = <S>;
		if ($_ !~ /^250/)
		{
			croak "Sending Email: data in Connect error - 250. $!";
		}
		print S "MAIL FROM:<$from>\n";
		$_ = <S>;
		if ($_ !~ /^250/)
		{
			croak "Sending Email: Sender address '$from' not valid. $!";
		}
		print S "RCPT TO:<$to>\n";
		$_ = <S>;
		if ($_ !~ /^250/)
		{
			croak "Sending Email: Recipient address '$to' not valid. $!";
		}
		print S "DATA\n";
		$_ = <S>;
		if ($_ !~ /^354/)
		{
			croak "Sending Email: Message send failed - 354. $!";
		}
	}

	# Send email via NET::SMTP.
	if ($cfg{mail_type} == 2)
	{
		eval q^
			use Net::SMTP;
			my $smtp = Net::SMTP->new($cfg{smtp_server}, Debug => 0) 
				or croak "Unable to connect to '$cfg{smtp_server}'. $!";

			$smtp->mail($from);
			$smtp->to($to);
			$smtp->data();
			$smtp->datasend("From: $from\n");
			$smtp->datasend("Subject: $subject\n");
			$smtp->datasend("\n");
			$smtp->datasend($message);
			$smtp->dataend();
			$smtp->quit();
		^;
		if ($@) { croak "Net::SMTP fatal error: $@"; }
		return 1;
	}

	# Send email via sendmail.
	$ENV{PATH} = '';
	if ($cfg{mail_type} == 0)
	{
		open S, "| $cfg{mail_program} -t" or croak "Mailprogram error. $!";
	}

	print S "To: $to\n";
	print S "From: $from\n";
	print S "Subject: $subject\n\n";
	print S "$message";
	print S "\n\n";

	# Send email via SMTP.
	if ($cfg{mail_type} == 1)
	{
		$_ = <S>;
		if ($_ !~ /^250/)
		{
			croak "Sending Email: Message send failed - try again - 250. $!";
		}
		print S "QUIT\n";
	}

	close(S);
	return 1;
}

# ---------------------------------------------------------------------
# Get latest articles.
# ---------------------------------------------------------------------
sub get_latest_articles
{
	my $max_articles = shift || 0;
	my (@articles, @data, @top_news);

	# Get article data.
	my $files = dir2array($cfg{topicsdir});
	my @cats  = grep(/\.cat/, @{$files});

	# Cycle through the categories.
	foreach my $cat (@cats)
	{
		my $topic_data = file2array("$cfg{topicsdir}/$cat", 1);

		if ($topic_data)
		{
			foreach (@{$topic_data})
			{
				my (
					$id, 
					$subject, 
					$poster, 
					$postdate, 
					$comments, 
					$views
				    ) = split (/\|/, $_);
				push (
					@articles,
					join ('|',
						$id,
						$subject, 
						$poster,
						$postdate, 
						$comments,
						$views,   
						$cat
					    )
				    );
			}
		}
	}

	for (0 .. $#articles)
	{
		my @fields = split (/\|/, $articles[$_]);
		for my $i (0 .. $#fields) { $data[$_][$i] = $fields[$i]; }
	}

	# Sort them.
	my @sorted = reverse sort { $a->[0] <=> $b->[0] } @data;
	if (@sorted && $max_articles)
	{
		$max_articles = ($max_articles < @sorted) ? 
			$max_articles : 
			@sorted;
		for (@sorted[0 .. $max_articles - 1])
		{
			my $sorted_row = join ('|', @$_);
			push (@top_news, $sorted_row);
		}
		return \@top_news;
	}

	return;
}

# ---------------------------------------------------------------------
# Export latest news to RDF file.
# ---------------------------------------------------------------------
sub rdf_export
{
	my $top_news = get_latest_articles($cfg{max_items_per_page});
	my ($channel, $item) = ('', '');

	# Get the latest headlines.
	foreach (@{$top_news})
	{
		my ($num, $subject, undef, undef, undef, undef, undef) =
		    split (/\|/, $_);

		$channel .=
		    qq(<rdf:li resource="$cfg{pageurl}/topics.$cfg{ext}?op=view_topic;id=$num" />);
		$item .= <<RDF;
<item rdf:about="$cfg{pageurl}/topics.$cfg{ext}?op=view_topic;id=$num">
<title>$subject</title>
<link>$cfg{pageurl}/topics.$cfg{ext}?op=view_topic;id=$num</link>
</item>
RDF
	}

	# Export data to XML file.
	sysopen(FH, "$cfg{yawpsnewsdir}/yawpsnews.xml",
		O_WRONLY | O_TRUNC | O_CREAT);
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	print FH <<RDF;
<?xml version="1.0" encoding="$cfg{codepage}"?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" 
xmlns="http://purl.org/rss/1.0/">

<channel rdf:about="$cfg{yawpsnewsurl}/yawpsnews.xml">
<title>$cfg{pagename}</title>
<link>$cfg{pageurl}/index.$cfg{ext}</link>
<description>$cfg{pagetitle}</description>
<items>
<rdf:Seq>
$channel</rdf:Seq>
</items>
</channel>
$item
</rdf:RDF>
RDF
	close(FH);
}

# ---------------------------------------------------------------------
# Get entries from forum log.
# ---------------------------------------------------------------------
sub read_forum_log
{
	my ($user, $id) = @_;

	if ($user eq $usr{anonuser}) { return; }

	my $log = file2array("$cfg{memberdir}/$user.log", 1);
	if (!@$log) { return; }

	foreach (@{$log})
	{
		my ($name, $value) = split (/\|/, $_);
		if ($name eq $id) { return $value; }
	}
}

# ---------------------------------------------------------------------
# Log read forum topics.
# ---------------------------------------------------------------------
sub write_forum_log
{
	my ($user, $id) = @_;

	if ($user ne $usr{anonuser})
	{
		my $log = file2array("$cfg{memberdir}/$user.log", 1);

		# Get current date.
		my $date = get_date();

		# Write log.
		$user =
		    ($user =~ /^([\w.]+)$/) 
		    ? $1
		    : croak "Unsafe data in $cfg{memberdir}/$user.dat detected";
		sysopen(FH, "$cfg{memberdir}/$user.log",
			O_WRONLY | O_TRUNC | O_CREAT);
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		print FH "$id|$date\n";
		if ($log)
		{
			foreach (@{$log})
			{
				my ($name, $value) = split (/\|/, $_);
				my $diff = calc_time_diff($value, $date, 1);

				if ($name ne $id && $diff <= $cfg{max_log_days_old})
				{
					print FH "$_\n";
				}
			}
		}
		close(FH);
	}
}

# ---------------------------------------------------------------------
# Check for banned users.
# ---------------------------------------------------------------------
sub check_ban
{
	my ($uid, $email) = @_;
	my $host = $ENV{REMOTE_ADDR} || $ENV{REMOTE_HOST} || '';
	my $banned = file2array("$cfg{datadir}/ban.txt", 1);

	if ($banned)
	{
		foreach (@{$banned})
		{

			# Check for banned usernames, emails and IP addresses.
			if ($uid eq $_ || $email eq $_ || $host eq $_)
			{
				croak $err{banned};
			}
		}
	}
}

# ---------------------------------------------------------------------
# Check if referer is valid.
# ---------------------------------------------------------------------
sub check_referer
{
	my $check_referer = 0;

	if ($ENV{HTTP_REFERER})
	{
		foreach ($ENV{SERVER_NAME}, $ENV{SERVER_ADDR})
		{
			if ($ENV{HTTP_REFERER} =~ m|https?://([^/]*)$_|i)
			{
				$check_referer = 1;
				last;
			}
		}
	}
	else { $check_referer = 2; }

	if ($check_referer != 1) { croak "Invalid referer"; }
}

# ---------------------------------------------------------------------
# Load user rank list.
# ---------------------------------------------------------------------
sub load_ranks
{
	my %ranks;

	sysopen(FH, "$cfg{memberdir}/membergroups.dat", O_RDONLY)
	    or croak "$err{open_file} $cfg{memberdir}/membergroups.dat. ($!)";
	while (<FH>)
	{
		chomp $_;
		my @bits = split (/\|/, $_);
		$ranks{$bits[0]} = $bits[1];
	}
	close(FH);

	return %ranks;
}

# ---------------------------------------------------------------------
# Evaluate tags for a theme
# ---------------------------------------------------------------------
sub eval_theme_tags
{
	my $string     = shift;
	my $time       = get_date();
	my $date_type  = get_theme_date_type();
	my $date       = format_date($time, $date_type);
	my $theme_name = get_theme_name();

	$string =~ s|%cgi_bin_url%|$cfg{cgi_bin_url}|g;
	$string =~ s|%non_cgi_url%|$cfg{non_cgi_url}|g;
	$string =~ s|%date%|$date|g;
	$string =~ s|%ext%|$cfg{ext}|g;
	$string =~ s|%pagename%|$cfg{pagename}|g;
	$string =~ s|%pagetitle%|$cfg{pagetitle}|g;
	$string =~ s|%language%|$cfg{lang}|g;
	$string =~ s|%codepage%|$cfg{codepage}|g;
	$string =~ s|%webmaster_email%|$cfg{webmaster_email}|g;
	$string =~ s|%theme_name%|$theme_name|g;

	return $string;
}

# ---------------------------------------------------------------------
# Evaluate common theme header.
# ---------------------------------------------------------------------
sub common_theme_header
{
	return eval_theme_tags($cfg{common_theme_header});
}

# ---------------------------------------------------------------------
# Evaluate common theme footer.
# ---------------------------------------------------------------------
sub common_theme_footer
{
	return eval_theme_tags($cfg{common_theme_footer});
}

# ---------------------------------------------------------------------
# Check user permissions to access certain area.
# ---------------------------------------------------------------------
sub acl_check
{
	my ($user, $access_area) = @_;
	my $access_granted = 0;

	if (-r "$cfg{datadir}/acl.dat")
	{
		sysopen(FH, "$cfg{datadir}/acl.dat", O_RDONLY);
		while (my $line = <FH>)
		{
			my ($area, $members) = split /\|\s*/, $line;
			my @members = split ' ', $members;

			if ($access_area eq $area)
			{
				foreach (@members)
				{
					if ($_ eq $user) { $access_granted = 1; last; }
				}
			}
		}
		close(FH);
	}

	return $access_granted;
}

# ---------------------------------------------------------------------
# Add user to category subscription list (topics, forums, links).
# ---------------------------------------------------------------------
sub update_subscriptions
{
	my ($file, $user_email, $state) = @_;

	# Get list of user's watching this category.
	my $mails = file2array($file);

	# Update subscriber list.
	sysopen(FH, $file, O_WRONLY | O_TRUNC | O_CREAT);
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	if ($state) { print FH "$user_email\n"; }
	if ($mails)
	{
		foreach (@{$mails})
		{
			chomp($_);
			if ($user_email ne $_) { print FH "$_\n"; }
		}
	}
	close(FH);
}

# ---------------------------------------------------------------------
# Send notification email to subscribed users.
# ---------------------------------------------------------------------
sub notify_users
{
	my ($file, $link) = @_;

	# Get list of users watching this topic.
	my $mails = file2array($file, 1);
	if ($#$mails > 0)
	{
		my $subject = $cfg{pagename} . ' ' . $msg{thread_reply};
		my $message = $inf{view_here} . ' ' . $link;

		# Send the email to recipients.
		foreach (@{$mails})
		{
			send_email(
				$cfg{webmaster_email}, 
				$_, 
				$subject, 
				$message
			    );
		}
	}
}

# ---------------------------------------------------------------------
# Read directory listing to array and return reference.
# ---------------------------------------------------------------------
sub dir2array
{
	my $file = shift;
	my @content;

	if (!(-d $file)) { return []; }

	opendir(DIR, $file);
	@content = readdir(DIR);
	closedir DIR;

	return \@content;
}

# ---------------------------------------------------------------------
# Read file to array and return reference.
# ---------------------------------------------------------------------
sub file2array
{
	my $file  = shift;
	my $chomp = shift || 0;

	if (!(-r $file)) { return []; }

	sysopen(FH, $file, O_RDONLY);
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	my @content = <FH>;
	close(FH);

	if ($chomp) { chomp(@content); }

	return \@content;
}

# ---------------------------------------------------------------------
# Read file to scalar and return it.
# ---------------------------------------------------------------------
sub file2scalar
{
	my $file  = shift;
	my $chomp = shift || 0;

	if (!(-r $file)) { return; }

	sysopen(FH, $file, O_RDONLY);
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	local $/ = undef;
	my $content = <FH>;
	close(FH);

	if ($chomp) { chomp($content); }

	return $content;
}

1;

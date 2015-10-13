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
# $Id: forum.cgi,v 1.38 2004/04/14 11:37:34 d3m1g0d Exp $
# =====================================================================

# Load necessary modules.
use strict;
use lib '.';
use yawps;

# Assign global variables.
use vars qw(
    $query
    $op $board $thread $post $start $quote $notify $modify $delete $del $state
    $post_subject $post_message $post_icon
    $to_board $from_board
    %user_data
    %user_action
    );

# Create a new CGI object.
$query = new CGI;

# Get the input.
$op     = $query->param('op')     || '';
$board  = $query->param('board')  || '';
$thread = $query->param('thread') || '';
$post   = $query->param('post');
$start  = $query->param('start')  || 0;
$quote  = $query->param('quote');
$notify = $query->param('notify');
$modify = $query->param('modify') || '';
$delete = $query->param('delete') || '';
$del    = $query->param('del')    || 0;
$state  = $query->param('state');

$post_subject = $query->param('subject');
$post_message = $query->param('message');
$post_icon    = $query->param('icon');

$to_board   = $query->param('to_board');
$from_board = $query->param('from_board');

# Get user profile.
%user_data = authenticate();

# Define possible user actions.
%user_action = (
	view_board     => \&view_board,
	view_thread    => \&view_thread,
	post           => \&post,
	post2          => \&post2,
	modify         => \&modify,
	modify2        => \&modify2,
	notify         => \&notify,
	notify2        => \&notify2,
	move_thread    => \&move_thread,
	move_thread2   => \&move_thread2,
	remove_thread  => \&remove_thread,
	remove_thread2 => \&remove_thread2,
	lock_thread    => \&lock_thread,
	print_thread   => \&print_thread
    );

# Depending on user action, decide what to do.
if ($user_action{$op}) { $user_action{$op}->(); }
else { board_index(); }

# ---------------------------------------------------------------------
# Display the board index.
# ---------------------------------------------------------------------
sub board_index
{

	# Get all categories.
	my $board_index    = '';
	my $total_threads  = 0;
	my $total_messages = 0;

	my $categories = file2array("$cfg{boardsdir}/cats.txt", 1);
	if (!@$categories) { user_error("$err{no_forums}", $user_data{theme}); }

	# Get current date.
	my $current_date = get_date();

	# Cycle through the categories.
	foreach my $curcat (@{$categories})
	{

		# Get name of category.
		my $cat_info = file2array("$cfg{boardsdir}/$curcat.cat", 1);
		my $cat_name = $cat_info->[0];

		# Check if category is visible for user.
		if ($cat_info->[1] && $cat_info->[1] ne $usr{anonuser} &&
		    $cat_info->[1] ne 'anonuser')
		{
			if (!$user_data{sec_level}) { next; }
			if ($cat_info->[1] ne $user_data{sec_level} &&
			    $user_data{sec_level} ne $usr{admin} &&
			    ($cat_info->[1] ne $usr{user} ||
				$user_data{sec_level} ne $usr{mod}))
			{
					next;
			}
		}

		$board_index .= <<HTML;
<tr class="bg2">
<td width="10" valign="top">&nbsp;</td>
<td colspan="5"><b>$cat_name</b></td>
</tr>
HTML

		my $row_color = qq( class="tbl_row_dark");
		foreach my $curboard (@{$cat_info}[2 .. $#$cat_info])
		{

			# Alternate the row colors.
			$row_color =
			    ($row_color eq qq( class="tbl_row_dark"))
			    ? qq( class="tbl_row_light")
			    : qq( class="tbl_row_dark");

			# Get board info.
			my $board_info = file2array("$cfg{boardsdir}/$curboard.dat", 1);
			my $board_name = $board_info->[0];
			my $board_desc = $board_info->[1];

			# Get data of current board.
			my $messages = file2array("$cfg{boardsdir}/$curboard.txt", 1);

			# Get count of topics in this board.
			my $board_topics = scalar @{$messages};

			# Get date and name of poster of last post in this forum.
			my ($date, $poster, $num, $post_date, $replies) = '';
			if (@$messages > 0)
			{
				(
				    undef, undef,   undef, $date, undef,
				    undef, $poster, undef, undef)
				    = split (/\|/, $messages->[0]);

				my $user_profile =
				    file2array("$cfg{memberdir}/$poster.dat", 1);
				if (@$user_profile)
				{
					$poster =
					    qq(<a href="$cfg{pageurl}/user.$cfg{ext}?op=view_profile;username=$poster">$user_profile->[1]</a>);
				}
			}

			# Get count of messages in every thread.
			my ($message_count, $unseen) = 0;
			foreach (@{$messages})
			{
				($num,     undef, undef, $post_date, $replies,
				    undef, undef, undef, undef)
				    = split (/\|/, $_);

				$message_count++;
				$message_count = $message_count + $replies;

				# Check if there are new messages in this forum.
				my $last_post_date = read_forum_log($user_data{uid}, $num);

				# Calulate time difference between postdate and current date.
				my $time_diff = calc_time_diff($current_date, $post_date, 1);

				# Count the unread messages.
				if ($last_post_date && $time_diff <= $cfg{max_log_days_old} &&
					$user_data{uid} ne $usr{anonuser})
				{
					$unseen++;
				}
			}

			# Get moderator(s) for this forum.
			my $moderators;
			my @moderators = split (/ +/, $board_info->[2]);

			foreach (@moderators)
			{
				my $mod_profile = file2array("$cfg{memberdir}/$_.dat", 1);
				$moderators .=
				    qq(<a href="$cfg{pageurl}/user.$cfg{ext}?op=view_profile;username=$_">$mod_profile->[1]</a> );
			}

			# Check if forum contains new posts.
			my $new = qq(<img src="$cfg{imagesurl}/forum/off.gif" alt="">);
			if ($unseen)
			{
				$new = qq(<img src="$cfg{imagesurl}/forum/on.gif" alt="">);
			}

			$poster = (!$poster) ? $poster = '???' : $poster;
			$date   = (!$date)   ? $date   = '???' : format_date($date);

			$board_index .= <<HTML;
<tr$row_color>
<td valign="top" width=10>$new</td>
<td valign="top">
<b><a href="$cfg{pageurl}/forum.$cfg{ext}?op=view_board;board=$curboard">$board_name</a></b><br>
$board_desc</td>
<td valign="top" width="15%" align="center">$board_topics</td>
<td valign="top" width="15%" align="center">$message_count</td>
<td valign="top" width="15%" align="center">$date<br>($poster)</td>
<td valign="top" width="20%">$moderators</td>
</tr>
HTML

			# Calculate total thread and message count.
			$total_messages = $total_messages + $message_count;
			$total_threads  = $total_threads + $board_topics;
		}
	}

	print_header();
	print_html($user_data{theme}, $nav{forums});

	print <<HTML;
<table width="100%" border="0" cellspacing="1" cellpadding="2">
<tr>
<td valign="bottom"><img src="$cfg{imagesurl}/forum/open.gif" width="17" height="15" border="0" alt="">&nbsp;&nbsp;
$nav{forums}</td>
</tr>
</table>
<table class="bg5" width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td>
<table width="100%" border="0" cellspacing="1" cellpadding="2">
<tr class="tbl_header">
<td width="10">&nbsp;</td>
<td><b>$msg{forum_name}</b></td>
<td nowrap align="center"><b>$msg{threads}</b></td>
<td nowrap align="center"><b>$msg{posts}</b></td>
<td nowrap align="center"><b>$msg{last_post}</b></td>
<td nowrap><b>$msg{moderator}</b></td>
</tr>
$board_index
</table>
</td>
</tr>
</table>
<table width="100%">
<tr>
<td align="right">$total_messages $msg{posts}<br>
$total_threads $msg{threads}</td>
</tr>
</table>
HTML

	print_html($user_data{theme}, $nav{login}, 1);
}

# ---------------------------------------------------------------------
# Display the message index.
# ---------------------------------------------------------------------
sub view_board
{

	# Complain about suspicious input.
	if ($board &&
		$board =~ /[\.+\/\\\*\?\~\^\$\@\%\`\"\'\&\;\|\<\>\x00-\x1F]/)
	{
		user_error($err{bad_input});
	}

	# Get all categories.
	my $categories = file2array("$cfg{boardsdir}/cats.txt", 1);
	if (!@$categories) { user_error("$err{no_forums}", $user_data{theme}); }

	# Check if user has permission to access the forum.
	my $access = check_board_access($categories, $board);
	if (!$access) { user_error($err{auth_failure}, $user_data{theme}); }

	# Get board data.
	my $board_info = file2array("$cfg{boardsdir}/$board.dat", 1);
	if (!@$board_info) { user_error("$err{no_forums}", $user_data{theme}); }

	my $messages = file2array("$cfg{boardsdir}/$board.txt", 1);
	my $censored = file2array("$cfg{datadir}/censor.txt",   1);
	my $board_name = $board_info->[0];

	# Make notify button.
	my $notification = '';
	if ($cfg{enable_notification} && $user_data{uid} ne $usr{anonuser})
	{
		$notification =
		    qq(&nbsp;<a href="$cfg{pageurl}/forum.$cfg{ext}?op=notify;board=$board"><img src="$cfg{imagesurl}/forum/notify.gif" alt="$msg{notification}" border="0"></a> );
	}

	print_header();
	print_html($user_data{theme}, "$nav{forums} >>> $board_name");

	print <<HTML;
<table width="100%" border="0" cellspacing="1" cellpadding="2">
<tr>
<td><img src="$cfg{imagesurl}/forum/open.gif" width="17" height="15" border="0" alt="">&nbsp;&nbsp;
<a href="$cfg{pageurl}/forum.$cfg{ext}">$nav{forums}</a>
<br>
<img src="$cfg{imagesurl}/forum/tline.gif" width="12" height="12" border="0" alt=""><img src="$cfg{imagesurl}/forum/open.gif" width="17" height="15" border="0" alt="">&nbsp;&nbsp;$board_name</td>
<td align="right" valign="bottom"><a href="$cfg{pageurl}/forum.$cfg{ext}?op=post;board=$board"><img src="$cfg{imagesurl}/forum/new_thread.gif" alt="$msg{new_thread}" border="0"></a>$notification</td>
</tr>
</table>
<table class="bg5" width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td>
<table width="100%" border="0" cellspacing="1" cellpadding="2">
<tr class="tbl_header">
<td width="16">&nbsp;</td>
<td width="15">&nbsp;</td>
<td width="40%"><b>$msg{subjectC}</b></td>
<td width="20%"><b>$msg{started_by}</b></td>
<td width="10%" align="center"><b>$msg{replies}</b></td>
<td width="10%" align="center"><b>$msg{views}</b></td>
<td width="20%" align="center"><b>$msg{last_post}</b></td>
HTML

	# Get current date.
	my $current_date = get_date();

	# Get all topics in this forum.
	my $num_shown = 0;
	my $row_color = qq( class="tbl_row_dark");
	for (my $i = $start; $i <= $#{$messages}; $i++)
	{

		# Alternate the row colors.
		$row_color =
		    ($row_color eq qq( class="tbl_row_dark"))
		    ? qq( class="tbl_row_light")
		    : qq( class="tbl_row_dark");

		my (
			$num,   $subject,     $username, $date, $replies,
			$views, $last_poster, $icon,     $state
		    )
		    = split (/\|/, $messages->[$i]);

		# Get poster's name.
		my $thread_starter_nick = $username;
		if (-r "$cfg{memberdir}/$username.dat")
		{
			my $user_profile = file2array("$cfg{memberdir}/$username.dat", 1);
			$thread_starter_nick =
			    qq(<a href="$cfg{pageurl}/user.$cfg{ext}?op=view_profile;username=$username">$user_profile->[1]</a>);
		}

		# Get last poster's name.
		my $last_poster_nick = $last_poster;
		if (-r "$cfg{memberdir}/$last_poster.dat")
		{
			my $user_profile =
			    file2array("$cfg{memberdir}/$last_poster.dat", 1);
			$last_poster_nick =
			    qq(<a href="$cfg{pageurl}/user.$cfg{ext}?op=view_profile;username=$last_poster">$user_profile->[1]</a>);
		}

		# Check if message is new.
		my $last_viewed_date = read_forum_log($user_data{uid}, $num);

		# Calulate time difference between postdate and current date.
		my $time_diff = calc_time_diff($current_date, $date, 1);

		# Check if post should be marked as new.
		my $new = '';
		if ($last_viewed_date && $time_diff <= $cfg{max_log_days_old} &&
			$user_data{uid} ne $usr{anonuser})
		{
			$new = qq(<img src="$cfg{imagesurl}/forum/new.gif" alt="">);
		}

		# Check for bad words.
		foreach my $censor (@{$censored})
		{
			my ($word, $censored) = split (/\=/, $censor);
			$subject =~ s/$word/$censored/g;
		}

		# Check if thread is hot or not.
		my $type;
		if ($state == 0) { $type = "thread"; }
		if ($replies >= 15 || $views >= 75)  { $type = "hotthread"; }
		if ($replies >= 25 || $views >= 100) { $type = "veryhotthread"; }
		if ($state == 1) { $type = "locked"; }

		# Thread page navigator.
		my $num_messages = $replies + 1;
		my $count        = 0;
		my $pages;
		if ($num_messages > $cfg{max_items_per_page})
		{
			while ($count * $cfg{max_items_per_page} < $num_messages)
			{
				my $view = $count + 1;
				my $strt = ($count * $cfg{max_items_per_page});
				$pages .=
				    qq( [<a href="$cfg{pageurl}/forum.$cfg{ext}?op=view_thread;board=$board;thread=$num;start=$strt">$view</a>]);
				$count++;
			}

			$pages =~ s/\n$//g;
			$pages =
			    qq(( <img src="$cfg{imagesurl}/forum/multipage.gif" alt=""> $pages ));
		}
		else { $pages = ''; }

		# Format date.
		my $formatted_date = format_date($date);

		print <<HTML;
<tr$row_color>
<td width="16"><img src="$cfg{imagesurl}/forum/$type.gif" alt=""></td>
<td width="15"><img src="$cfg{imagesurl}/forum/$icon.gif" alt="" border="0" align="middle"></td>
<td width="40%"><a href="$cfg{pageurl}/forum.$cfg{ext}?op=view_thread;board=$board;thread=$num"><b>$subject</b></a> $new $pages</td>
<td width="20%">$thread_starter_nick</td>
<td width="10%" align="center">$replies</td>
<td width="10%" align="center">$views</td>
<td width="20%" align="center">$formatted_date<br>$msg{by} $last_poster_nick</td>
</tr>
HTML

		$num_shown++;
		if ($num_shown >= $cfg{max_items_per_page}) { last; }
	}

	print <<HTML;
</table>
</td>
</tr>
</table>
<table border="0" width="100%">
<tr>
<td><b>$msg{pagesC}</b>
HTML

	# Make page navigation bar.
	my $num_messages = scalar @{$messages};
	my $count        = 0;
	while ($count * $cfg{max_items_per_page} < $num_messages)
	{
		my $view = $count + 1;
		my $strt = $count * $cfg{max_items_per_page};
		if ($start == $strt) { print "[$view] "; }
		else
		{
			print
			    qq(<a href="$cfg{pageurl}/forum.$cfg{ext}?op=view_board;board=$board;start=$strt">$view</a> );
		}
		$count++;
	}

	print <<HTML;
</td>
<td align="right"><a href="$cfg{pageurl}/forum.$cfg{ext}?op=post;board=$board"><img src="$cfg{imagesurl}/forum/new_thread.gif" alt="$msg{new_thread}" border="0"></a>$notification</td>
</tr>
<tr>
<td colspan="2" align="right" valign="bottom">
<div align="right">
HTML

	# Make forum selector.
	forum_selector();

	print <<HTML;
</td>
</tr>
</table>
HTML

	print_html($user_data{theme}, "$nav{forums} >>> $board_name", 1);
}

# ---------------------------------------------------------------------
# Display a thread.
# ---------------------------------------------------------------------
sub view_thread
{
	my (
		$num,      $subject, $email,       $date,
		$username, $icon,    $ip,          $message,
		$replies,  $views,   $last_poster, $state
	    );

	# Complain about suspicious input.
	if ($board &&
		$board =~ /[\.+\/\\\*\?\~\^\$\@\%\`\"\'\&\;\|\<\>\x00-\x1F]/)
	{
		user_error($err{bad_input});
	}
	if ($thread !~ /^[0-9]+$/) { user_error($err{bad_input}); }
	$board =
	    ($board =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});

	# Get all categories.
	my $categories = file2array("$cfg{boardsdir}/cats.txt", 1);
	if (!@$categories) { user_error($err{no_forums}, $user_data{theme}); }

	# Check if user has permission to access the forum.
	my $access = check_board_access($categories, $board);
	if (!$access) { user_error($err{auth_failure}, $user_data{theme}); }

	# Get thread data.
	if (!-r "$cfg{messagedir}/$thread.txt") { user_error($err{bad_input}, $user_data{theme}); }
	my $messages = file2array("$cfg{messagedir}/$thread.txt", 1);
	if ($#$messages > 0)
	{
		($subject, $date, $username, $icon, $ip, $message) =
		    split (/\|/, $messages->[$#{$messages} - 1]);
	}

	# Log action.
	write_forum_log($user_data{uid}, $thread);

	# Get member ranks.
	my %ranks = load_ranks();

	# Get board name and name of moderators.
	my $board_info = file2array("$cfg{boardsdir}/$board.dat", 1);
	if (!@$board_info) { user_error($err{no_forums}, $user_data{theme}); }
	my $board_name = $board_info->[0];
	my @moderators = split (/ +/, $board_info->[2]);

	# Get board threads.
	my $threads = file2array("$cfg{boardsdir}/$board.txt", 1);
	if (!@$threads) { user_error($err{auth_failure}, $user_data{theme}); }

	# Get censored words.
	my $censored = file2array("$cfg{datadir}/censor.txt", 1);

	# Update view counter.
	sysopen(FH, "$cfg{boardsdir}/$board.txt", O_WRONLY | O_TRUNC)
	    or user_error("$err{not_writable} $cfg{boardsdir}/$board.txt. ($!)",
		$user_data{theme});
	flock(FH, LOCK_EX) if $cfg{use_flock};

	foreach (@{$threads})
	{
		($num,      $subject,     $username, $date, $replies,
		    $views, $last_poster, $icon,     $state)
		    = split (/\|/, $_);
		if ($thread eq $num)
		{
			$views++;
			print FH
			    "$num|$subject|$username|$date|$replies|$views|$last_poster|$icon|$state\n";
		}
		else
		{
			print FH
			    "$num|$subject|$username|$date|$replies|$views|$last_poster|$icon|$state\n";
		}
	}

	close(FH);

	# Check if thread is hot or not.
	my $type;
	for (my $i = 0; $i <= $#{$threads}; $i++)
	{
		($num,      $subject,     $username, $date, $replies,
		    $views, $last_poster, $icon,     $state)
		    = split (/\|/, $threads->[$i]);
		if ($thread eq $num)
		{
			if ($state == 0) { $type = "thread"; }
			if ($replies >= 15 || $views >= 75)  { $type = "hotthread"; }
			if ($replies >= 25 || $views >= 100) { $type = "veryhotthread"; }
			if ($state == 1) { $type = "locked"; }
			$i = scalar @{$threads};
		}
	}

	# Get thread title
	if ($#$messages > 0)
	{
		($subject, $date, $username, $icon, $ip, $message) =
		    split (/\|/, $messages->[0]);
	}

	# Check for bad words.
	foreach my $censor (@{$censored})
	{
		my ($word, $censored) = split (/\=/, $censor);
		$subject =~ s/$word/$censored/g;
	}

	# Make notify button.
	my $notification = '';
	if ($cfg{enable_notification} && $user_data{uid} ne $usr{anonuser})
	{
		$notification =
		    qq(&nbsp;<a href="$cfg{pageurl}/forum.$cfg{ext}?op=notify;board=$board;thread=$thread"><img src="$cfg{imagesurl}/forum/notify.gif" alt="$msg{notification}" border="0"></a> );
	}

	print_header();
	print_html($user_data{theme}, "$nav{forums} >>> $board_name");

	print <<HTML;
<table width="100%" border="0" cellspacing="1" cellpadding="2">
<tr>
<td valign="bottom"><img src="$cfg{imagesurl}/forum/open.gif" width="17" height="15" border="0" alt="">&nbsp;&nbsp;
<a href="$cfg{pageurl}/forum.$cfg{ext}">$nav{forums}</a>
<br>
<img src="$cfg{imagesurl}/forum/tline.gif" width="12" height="12" border="0" alt=""><img src="$cfg{imagesurl}/forum/open.gif" width="17" height="15" border="0" alt="">&nbsp;&nbsp;<a href="$cfg{pageurl}/forum.$cfg{ext}?op=view_board;board=$board">$board_name</a>
<br>
<img src="$cfg{imagesurl}/forum/tline2.gif" width="24" height="12" border="0" alt=""><img src="$cfg{imagesurl}/forum/open.gif" width="17" height="15" border="0" alt="">&nbsp;&nbsp;$subject</td>
<td align="right" valign="bottom"><a href="$cfg{pageurl}/forum.$cfg{ext}?op=print_thread;board=$board;thread=$thread" target="_blank"><img src="$cfg{imagesurl}/forum/print.gif" alt="$msg{print_friendly}" border="0"></a><br>
<a href="$cfg{pageurl}/forum.$cfg{ext}?op=post;board=$board;thread=$thread;start=$start;quote="><img src="$cfg{imagesurl}/forum/reply.gif" alt="$msg{reply}" border="0"></a>$notification</td>
</tr>
</table>
<table class="bg5" width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td>
<table width="100%" border="0" cellspacing="1" cellpadding="2">
<tr class="tbl_header">
<td>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td><img src="$cfg{imagesurl}/forum/$type.gif" alt=""></td>
<td>&nbsp;<b>$msg{authorC}</b></td>
</tr>
</table>
</td>
<td><b>$msg{topicC} $subject</b></td>
</tr>
HTML

	# Show all messages in this thread.
	my $num_shown;
	my $row_color = qq( class="tbl_row_dark");
	for (my $i = $start; $i <= $#{$messages}; $i++)
	{

		# Alternate the row colors.
		$row_color =
		    ($row_color eq qq( class="tbl_row_dark"))
		    ? qq( class="tbl_row_light")
		    : qq( class="tbl_row_dark");

		($subject, $date, $username, $icon, $ip, $message) =
		    split (/\|/, $messages->[$i]);

		if (!$subject) { $subject = "---"; }
		$message =~ s/\n//g;

		# Check if IP address should be viewable.
		if ($user_data{sec_level} ne $usr{admin}) { $ip = $msg{logged}; }

		my $removed = 0;
		if (!(-r "$cfg{memberdir}/$username.dat")) { $removed = 1; }

		my (
			$postinfo,     $signature, $star,
			$ranking,      $url_link,  $email_link,
			$profile_link, $icq_link,  $send_im_link
		    )
		    = '';

		# Display member's profile.
		my ($user_profile, $member_info, $member_pic);
		if ($username ne $usr{anonuser} && $removed == 0)
		{
			if (-r "$cfg{memberdir}/$username.dat")
			{
				$user_profile =
				    file2array("$cfg{memberdir}/$username.dat", 1);

				$username = $user_profile->[1];
				$email    = $user_profile->[2];
			}

			# Display member ranking.
			$ranking =
			    $user_profile->[6] + $user_profile->[11] +
			    $user_profile->[12];
			$member_info = '';
			$star        = '';
			foreach (sort keys %ranks)
			{
				if ($ranking > $_)
				{
					$member_info = $ranks{$_};
					$star        = $star .
					    qq(<img src="$cfg{imagesurl}/forum/star.gif" alt="" border="0">);
				}
			}
			$ranking = $ranking . ' posts';

			# Get special ranks (moderator and admin).
			foreach (@moderators)
			{
				if ($_ eq $username) { $member_info = $usr{mod}; last; }
			}
			if ($user_profile->[7]) { $member_info = $user_profile->[7]; }

			if ($user_data{uid} ne $usr{anonuser} &&
				$username ne $usr{anonuser})
			{

				# Make link to user's homepage.
				$url_link =
				    qq(<a href="$user_profile->[4]" target="_blank"><img src="$cfg{imagesurl}/forum/www.gif" alt="$msg{visit_page} $user_profile->[1]" border="0"></a>);

				# Protect email address.
				my ($email_p, $email_d) = split ("\@", $email);
				my $protected_email = <<"JS";
<script language="javascript"><!--
document.write("<a href=" + "mail" + "to:" + "$email_p" + "@" + "$email_d" + ">" + "<img src='$cfg{imagesurl}/forum/email.gif' alt='$msg{send_email} $username' border='0'>" + "</a>")
//--></script>
JS

				# Make link to send email to user.
				$email_link = qq(&nbsp;&nbsp;$protected_email);

				# Make link to user's profile.
				$profile_link =
				    qq(&nbsp;&nbsp;<a href="$cfg{pageurl}/user.$cfg{ext}?op=view_profile;username=$username"><img src="$cfg{imagesurl}/forum/profile.gif" alt="$msg{view_profile} $user_profile->[1]" border="0"></a>);

				# Make link to send IMs.
				$send_im_link =
				    qq(&nbsp;&nbsp;<a href="$cfg{pageurl}/instant_messenger.$cfg{ext}?op=send_im;to=$username"><img src="$cfg{imagesurl}/forum/message.gif" alt="$msg{send_message_to} $username" border="0"></a>);

				# Make ICQ link.
				if ($user_profile->[8])
				{
					$icq_link =
					    qq(&nbsp;&nbsp;<a href="http://www.icq.com/$user_profile->[8]" target="_blank"><img src="http://wwp.icq.com/scripts/online.dll?icq=$user_profile->[8]&amp;img=5" alt="$msg{send_icq} $user_profile->[8]" border="0"></a>);
				}
				if (!$user_profile->[8]) { $icq_link = ''; }
			}

			# Display the signature.
			$signature = $user_profile->[5];
			$signature =~ s/\&\&/<br>/g;
			$signature = <<HTML;
<br><br><br>
__________________<br>
$signature
HTML
		}

		# Show member picture.
		if ($username ne $usr{anonuser})
		{
			if (!$user_profile->[9]) { $user_profile->[9] = "_nopic.gif"; }
			if ($user_profile->[9] =~ /http:\/\//)
			{
				my ($width, $height);
				if ($cfg{picture_width})
				{
					$width = qq(width="$cfg{picture_width}");
				}
				if ($cfg{picture_height})
				{
					$height = qq(height="$cfg{picture_height}");
				}

				$member_pic =
				    qq(<img src="$user_profile->[9]" $width $height border="0" alt="$username">);
			}
			else
			{
				$member_pic =
				    qq(<img src="$cfg{imagesurl}/avatars/$user_profile->[9]" border="0" alt="">);
			}
		}

		# Disable special infos if user is a guest.
		if ($username eq $usr{anonuser})
		{
			$member_pic  = '';
			$member_info = '';
			$star        = '';
			$signature   = '';
			$ranking     = '';
		}

		# Add signature to message and make UBBC.
		$message = "$message\n$signature";
		$message = do_ubbc($message);


		# Check for bad words.
		foreach my $censor (@{$censored})
		{
			my ($word, $censored) = split (/\=/, $censor);
			$subject =~ s/$word/$censored/g;
			$message =~ s/$word/$censored/g;
		}

		# Format date.
		my $formatted_date = format_date($date);

		print <<HTML;
<tr$row_color>
<td width="140" valign="top"><a name="$i"></a><b>$username</b><br>
$member_pic<br>
<br>
$star<br>
$member_info<br>
$ranking<br></td>
<td valign="top">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr>
<td><img src="$cfg{imagesurl}/forum/$icon.gif" alt=""></td>
<td width="100%">&nbsp;<b>$subject</b></td>
<td align="right" nowrap>&nbsp;<b>$msg{posted_onC}</b> $formatted_date</td>
</tr>
</table>
<hr noshade="noshade" size="1">
$message<br>
</td>
</tr>
<tr$row_color>
<td><img src="$cfg{imagesurl}/forum/ip.gif" alt="$msg{ip_address}" align="top"> $ip</td>
<td>
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr>
<td>
HTML

		if ($user_data{uid} ne $usr{anonuser} && $username ne $usr{anonuser})
		{
			print $url_link . $email_link . $profile_link . $send_im_link .
			    $icq_link;
		}

		print <<HTML;
</td>
<td align="right"><a href="$cfg{pageurl}/forum.$cfg{ext}?op=post;board=$board;thread=$thread;quote=$i;title=post+reply;start=$start"><img src="$cfg{imagesurl}/forum/quote.gif" alt="$msg{quote}" border="0"></a>&nbsp;&nbsp;
HTML

		# Print user actions.
		if ($user_data{uid} ne $usr{anonuser})
		{
			print
			    qq(<a href="$cfg{pageurl}/forum.$cfg{ext}?op=modify;board=$board;thread=$thread;post=$i"><img src="$cfg{imagesurl}/forum/modify.gif" alt="$msg{edit_message}" border="0"></a>&nbsp;&nbsp;<a href="$cfg{pageurl}/forum.$cfg{ext}?op=modify2;board=$board;thread=$thread;post=$i;del=1"><img src="$cfg{imagesurl}/forum/delete.gif" alt="$msg{delete}" border="0"></a>);
		}

		print <<HTML;
</td>
</tr>
</table>
</td>
</tr>
HTML

		$num_shown++;
		if ($num_shown >= $cfg{max_items_per_page}) { last; }
	}

	print <<HTML;
</table>
</td>
</tr>
</table>
<table border="0" width="100%" cellspacing="1" cellpadding="2">
<tr>
<td><b>$msg{pagesC}</b>
HTML

	# Make page navigation bar.
	my $num_messages = scalar @{$messages};
	my $count        = 0;
	while ($count * $cfg{max_items_per_page} < $num_messages)
	{
		my $view = $count + 1;
		my $strt = $count * $cfg{max_items_per_page};
		if ($start == $strt) { print "[$view] "; }
		else
		{
			print
			    qq(<a href="$cfg{pageurl}/forum.$cfg{ext}?op=view_thread;board=$board;thread=$thread;start=$strt">$view</a> );
		}
		$count++;
	}

	print qq(</td>\n<td align="right">);

	# Check if user is moderator.
	my $is_moderator = 0;

	foreach (@moderators)
	{
		if ($_ eq $user_data{uid}) { $is_moderator = 1; last; }
	}

	# Make admin buttons.
	if ($is_moderator == 1 || $user_data{sec_level} eq $usr{admin})
	{
		print
		    qq(<a href="$cfg{pageurl}/forum.$cfg{ext}?op=move_thread;board=$board;thread=$thread"><img src="$cfg{imagesurl}/forum/move.gif" alt="$msg{move_thread}" border="0"></a>&nbsp;<a href="$cfg{pageurl}/forum.$cfg{ext}?op=remove_thread;board=$board;thread=$thread"><img src="$cfg{imagesurl}/forum/remove.gif" alt="$msg{delete_thread}" border="0"></a>&nbsp;<a href="$cfg{pageurl}/forum.$cfg{ext}?op=lock_thread;board=$board;thread=$thread"><img src="$cfg{imagesurl}/forum/lock.gif" alt="$msg{lock_unlock_thread}" border="0"></a>);
	}

	print <<HTML;
</td>
<td align="right"><a href="$cfg{pageurl}/forum.$cfg{ext}?op=post;board=$board;thread=$thread;start=$start;quote="><img src="$cfg{imagesurl}/forum/reply.gif" alt="$msg{reply}" border="0"></a>$notification</td>
</tr>
</table>
<div align="right">
HTML

	# Make forum selector.
	forum_selector();

	print "</div>";

	print_html($user_data{theme}, "$nav{forums} >>> $board_name", 1);
}

# ---------------------------------------------------------------------
# Print formular to add a post.
# ---------------------------------------------------------------------
sub post
{
	if ($user_data{uid} eq $usr{anonuser} && !$cfg{enable_guest_posting})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	my (
		$num,   $subject,     $date,    $username,
		$icon,  $ip,          $message, $replies,
		$views, $last_poster, $state,   $messages
	    );

	# Get board threads.
	my $threads = file2array("$cfg{boardsdir}/$board.txt", 1, 1);

	# Check if thread is locked.
	for (my $i = 0; $i <= $#{$threads}; $i++)
	{
		($num,      $subject,     $username, $date, $replies,
		    $views, $last_poster, $icon,     $state)
		    = split (/\|/, $threads->[$i]);

		if ($thread eq $num && ($state == 1 || $replies < 0))
		{
			$i = scalar @{$threads};
			user_error($err{thread_locked}, $user_data{theme});
		}
	}

	# Display the notification button. 
	my $notification = '';
	if ($cfg{enable_notification} && $user_data{uid} ne $usr{anonuser})
	{
		$notification = <<HTML;
<tr>
<td><b>$msg{notification}</b></td>
<td><input type="checkbox" name="notify" value="x"></td>
</tr>
HTML
	}

	# Get post data.
	my ($subject_form_field, $message_form_field) = '';
	if ($thread)
	{
		$messages = file2array("$cfg{messagedir}/$thread.txt", 1, 1);

		($subject, undef, undef, undef, undef, $message) =
		    split (/\|/, $messages->[0]);

		# Format subject and message.
		$subject =~ s/Re: //g;
		$subject_form_field = "Re: $subject";

		if ($quote ne '')
		{
			my (undef, undef, undef, undef, undef, $message) =
			    split (/\|/, $messages->[$quote]);

			$message_form_field = $message;
			$message_form_field =~ s/\[quote\](\S+?)\[\/quote\]//isg;
			$message_form_field =~ s/\[(\S+?)\]//isg;
			$message_form_field = "\n\n\[quote\]$message\[/quote\]";
			$message_form_field = html_to_text($message_form_field);

			$subject =~ s/Re: //g;
			$subject_form_field = "Re: $subject";
		}
		else { $message_form_field = ''; }
	}
	else
	{
		$subject_form_field = '';
		$message_form_field = '';
	}

	print_header();
	print_html($user_data{theme}, $nav{write_message});

	print <<HTML;
<table width="100%" border="0" cellspacing="0" cellpadding="1">
<tr>
<td><form action="$cfg{pageurl}/forum.$cfg{ext}" method="post" name="creator">
<table border="0">
<tr>
<td><b>$msg{subjectC}</b></td>
<td><input type="text" name="subject" value="$subject_form_field" size="40" maxlength="50"></td>
</tr>
<tr>
<td><b>$msg{symbolC}</b></td>
<td>
HTML

	# Print the UBBC image selector.
	my $ubbc_image_selector = print_ubbc_image_selector();
	print $ubbc_image_selector;

	print <<HTML;
<textarea name="message" rows="10" cols="40">$message_form_field</textarea></td>
</tr>
<tr>
<td><b>$msg{ubbc_tagsC}</b></td>
<td valign="top">
HTML

	# Print the UBBC panel.
	my $ubbc_panel = print_ubbc_panel();
	print $ubbc_panel;

	print <<HTML;
</td>
</tr>
$notification
<tr>
<td align="center" colspan="2"><input type="hidden" name="op" value="post2">
<input type="hidden" name="board" value="$board">
<input type="hidden" name="post" value="$thread">
<input type=submit value="$btn{send_message}">
<input type="reset" value="$btn{reset}"></td>
</tr>
</table>
</form>
</td>
</tr>
</table>
HTML

	# Print message history.
	if ($#$messages > 0)
	{
		print <<HTML;
<br>
<b>$msg{thread_overviewC}</b><br>
<table class="bg5" width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td>
<table width="100%" border="0" cellspacing="1" cellpadding="2">
HTML
		foreach (@{$messages})
		{
			($subject, $date, $username, $icon, $ip, $message) =
			    split (/\|/, $_);

			# Get poster's name.
			my $user_profile;
			if (-r "$cfg{memberdir}/$username.dat")
			{
				$user_profile =
				    file2array("$cfg{memberdir}/$username.dat", 1, 1);
				$username = $user_profile->[1];
			}

			# UBBC formatting.	
			$message = do_ubbc($message);

			# Format date.
			my $formatted_date = format_date($date);

			print <<HTML;
<tr class="tbl_header">
<td>$msg{written_by} $username ($formatted_date)</td>
</tr>
<tr class="tbl_row_light">
<td>$message</td>
</tr>
HTML
		}

		print <<HTML;
</table>
</td>
</tr>
</table>
HTML
	}

	print_html($user_data{theme}, $nav{write_message}, 1);
}

# ---------------------------------------------------------------------
# Add a post.
# ---------------------------------------------------------------------
sub post2
{
	if ($user_data{uid} eq $usr{anonuser} && $cfg{enable_guest_posting} == 0)
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	# Get all categories.
	my $categories = file2array("$cfg{boardsdir}/cats.txt", 1, 1);
	if (!@$categories) { user_error("$err{no_forums}", $user_data{theme}); }

	# Check if user has permission to access the forum.
	my $access = check_board_access($categories, $board);
	if (!$access) { user_error($err{auth_failure}, $user_data{theme}); }

	# Check input.
	user_error($err{enter_subject}, $user_data{theme}) unless ($post_subject);
	user_error($err{enter_text},    $user_data{theme}) unless ($post_message);

	# Format input.
	$post_subject = html_escape($post_subject);
	$post_message = html_escape($post_message);

	if (length($post_subject) > 50)
	{
		$post_subject = substr($post_subject, 0, 50);
	}

	# Get ID for new post.
	my $post_num = '';
	if ($post eq '')
	{
		my $files = dir2array($cfg{messagedir});

		my @files = grep(/\.txt/, @{$files});
		foreach (@files) { $_ =~ s/\.txt//; }
		@files = reverse(sort { $a <=> $b } @files);

		$post_num = $files[0] || 0;
		if ($post_num) { $post_num =~ s/\.txt//; }
		$post_num++;
	}

	# Get board data.
	my $messages = file2array("$cfg{boardsdir}/$board.txt", 1, 1);

	# Get date.
	my $date = get_date();

	# Update board data.
	$board =
	    ($board =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	sysopen(FH, "$cfg{boardsdir}/$board.txt", O_WRONLY | O_TRUNC)
	    or user_error("$err{not_writable} $cfg{boardsdir}/$board.txt. ($!)",
		$user_data{theme});
	flock(FH, LOCK_EX) if $cfg{use_flock};

	# Add a new post to board index.
	if ($post eq '')
	{
		print FH
		    "$post_num|$post_subject|$user_data{uid}|$date|0|0|$user_data{uid}|$post_icon\|0\n";
		foreach (@{$messages}) { print FH "$_\n"; }
	}

	# Add a reply to board index.
	else
	{
		my ($modified_thread, @board_threads);
		for (@{$messages})
		{
			my (
				$num,         $subject, $username,
				$post_date,   $replies, $views,
				$last_poster, $icon,    $state
			    )
			    = split (/\|/, $_);

			$replies++;

			if ($num == $post)
			{
				$modified_thread =
				    "$num|$subject|$username|$date|$replies|$views|$user_data{uid}|$icon|$state\n";
			}
			else { push @board_threads, $_; }
		}

		if ($modified_thread) { print FH $modified_thread; }
		foreach (@board_threads) { print FH "$_\n"; }
	}

	close(FH);

	# Add reply.
	if (-r "$cfg{messagedir}/$post.txt")
	{
		$post =
		    ($post =~ /^([\w.]+)$/) 
		    ? $1
		    : user_error($err{bad_input}, $user_data{theme});
		sysopen(FH, "$cfg{messagedir}/$post.txt", O_WRONLY | O_APPEND)
		    or
		    user_error("$err{not_writable} $cfg{messagedir}/$post.txt. ($!)",
			$user_data{theme});
	}

	# Add new post.
	else
	{
		$post_num =
		    ($post_num =~ /^([\w.]+)$/) 
		    ? $1
		    : user_error($err{bad_input}, $user_data{theme});
		sysopen(FH, "$cfg{messagedir}/$post_num.txt",
			O_WRONLY | O_TRUNC | O_CREAT)
		    or user_error(
			"$err{not_writable} $cfg{messagedir}/$post_num.txt. ($!)",
			$user_data{theme});
	}

	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	print FH
	    "$post_subject|$date|$user_data{uid}|$post_icon|$ENV{REMOTE_ADDR}|$post_message\n";
	close(FH);

	# Get message ID of newly added post.
	my $thread = file2array("$cfg{messagedir}/$post.txt", 1);
	my $count = ($thread) ? $#$thread : 0;

	# Notify users, who are watching this thread and/or board.	
	if (-r "$cfg{messagedir}/$post.mail" || -r "$cfg{boardsdir}/$board.mail")
	{
		notify_users("$cfg{messagedir}/$post.mail",
			"$cfg{pageurl}/forum.$cfg{ext}?op=view_thread;board=$board;thread=$thread;start=$start#$count"
		    );
	}

	# Log action.
	if ($post)     { $thread = $post; }
	if ($post_num) { $thread = $post_num; }

	# Increment user's post count.
	if ($user_data{uid} ne $usr{anonuser})
	{
		my $user_profile =
		    file2array("$cfg{memberdir}/$user_data{uid}.dat", 1);
		$user_profile->[6]++;

		$user_data{uid} =
		    ($user_data{uid} =~ /^([\w.]+)$/) 
		    ? $1
		    : user_error($err{bad_input}, $user_data{theme});
		sysopen(FH, "$cfg{memberdir}/$user_data{uid}.dat", O_WRONLY | O_TRUNC)
		    or user_error($err{user_no_exist}, $user_data{theme});
		flock(FH, LOCK_EX) if $cfg{use_flock};
		for (@{$user_profile}) { print FH "$_\n"; }
		close(FH);
	}

	if ($post)
	{
		print $query->redirect(
			-location => $cfg{pageurl} . '/forum.' . $cfg{ext} .
			'?op=view_thread;board=' . $board . ';thread=' . $thread .
			';start=' . $start . '#' . $count);
	}
	else
	{
		print $query->redirect(
			-location => $cfg{pageurl} . '/forum.' . $cfg{ext} .
			'?op=view_thread;board=' . $board . ';thread=' . $thread);
	}
}

# ---------------------------------------------------------------------
# Print formular to edit a post.
# ---------------------------------------------------------------------
sub modify
{
	if ($user_data{uid} eq $usr{anonuser})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	# Get all categories.
	my $categories = file2array("$cfg{boardsdir}/cats.txt", 1);
	if (!@$categories) { user_error("$err{no_forums}", $user_data{theme}); }

	# Check if user has permission to access the forum.
	my $access = check_board_access($categories, $board);
	if (!$access) { user_error($err{auth_failure}, $user_data{theme}); }

	my (
		$num,   $subject,     $date,    $username,
		$icon,  $ip,          $message, $replies,
		$views, $last_poster, $state
	    );

	# Get board info.
	my $board_info = file2array("$cfg{boardsdir}/$board.dat",   1);
	my $threads    = file2array("$cfg{boardsdir}/$board.txt",   1);
	my $messages   = file2array("$cfg{messagedir}/$thread.txt", 1);

	# Check if thread is locked.
	for (my $i = 0; $i <= $#{$threads}; $i++)
	{
		($num,      $subject,     $username, $date, $replies,
		    $views, $last_poster, $icon,     $state)
		    = split (/\|/, $threads->[$i]);

		if ($thread eq $num && $state == 1)
		{
			$i = scalar @{$threads};
			user_error($err{thread_locked}, $user_data{theme});
		}
	}

	# Get message to be modified.
	($subject, $date, $username, $icon, $ip, $message) =
	    split (/\|/, $messages->[$post]);
	$message = html_to_text($message);

	# Check if user is moderator.
	my @moderators = split (/ +/, $board_info->[2]);
	my $is_moderator = 0;

	foreach (@moderators)
	{
		if ($_ eq $username) { $is_moderator = 1; last; }
	}

	# Check if user has permissions to edit this post.
	if ($user_data{uid} ne $username && $is_moderator != 1 &&
		$user_data{sec_level} ne $usr{admin})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	print_header();
	print_html($user_data{theme}, "$nav{forums} >>> $nav{edit_message}");

	print <<HTML;
<table width="100%" border="0" cellspacing="0" cellpadding="1">
<tr>
<td><form action="$cfg{pageurl}/forum.$cfg{ext}" method="post" name="creator">
<table border="0">
<tr>
<td><b>$msg{subjectC}</b></td>
<td><input type="text" name="subject" value="$subject" size="40" maxlength="50"></font></td>
</tr>
<tr>
<td><b>$msg{symbolC}</b></td>
<td>
HTML

	# Print the UBBC image selector.
	my $ubbc_image_selector = print_ubbc_image_selector($icon);
	print $ubbc_image_selector;

	print <<HTML;
<textarea name="message" rows="10" cols="40">$message</textarea></td>
</tr>
<tr>
<td><b>$msg{ubbc_tagsC}</b></td>
<td valign="top">
HTML

	# Print the UBBC panel.
	my $ubbc_panel = print_ubbc_panel();
	print $ubbc_panel;

	print <<HTML;
</td>
</tr>
<tr>
<td align=center colspan="2"><input type="hidden" name="op" value="modify2">
<input type="hidden" name="board" value="$board">
<input type="hidden" name="thread" value="$thread">
<input type="hidden" name="post" value="$post">
<input type="submit" name="modify" value="$btn{modify}">
<input type="submit" name="delete" value="$btn{delete}"></td>
</tr>
</table>
</form>
</td>
</tr>
</table>
HTML

	print_html($user_data{theme}, "$nav{forums} >>> $nav{edit_message}", 1);
}

# ---------------------------------------------------------------------
# Modify a post.
# ---------------------------------------------------------------------
sub modify2
{

	# Get board info.
	my $board_info = file2array("$cfg{boardsdir}/$board.dat",   1);
	my $threads    = file2array("$cfg{boardsdir}/$board.txt",   1);
	my $messages   = file2array("$cfg{messagedir}/$thread.txt", 1);

	# Check if user is moderator.
	my @moderators = split (/ +/, $board_info->[2]);
	my $is_moderator = 0;

	foreach (@moderators)
	{
		if ($_ eq $user_data{uid}) { $is_moderator = 1; last; }
	}

	# Check if post has replies.
	if ($post == 0 && ($#{$messages} + 1) > 1 && ($delete ne '' || $del == 1))
	{
		user_error($err{delete_replies}, $user_data{theme});
	}

	# Get message to be modified
	my ($subject, $date, $username, $icon, $ip, $message) =
	    split (/\|/, $messages->[$post]);

	# Check if user has permission to edit message.
	if ($post eq $messages->[$post])
	{
		if ($user_data{uid} ne $username
			|| $user_data{sec_level} ne $usr{admin}
			|| $is_moderator != 1
			|| $user_data{uid} eq $usr{anonuser})
		{
			user_error($err{auth_failure}, $user_data{theme});
		}
	}

	# Check if user has permission to delete message.
	if ($del == 1)
	{
		if ($is_moderator != 1 && $user_data{sec_level} ne $usr{admin})
		{
			user_error($err{auth_failure}, $user_data{theme});
		}
	}

	# Update message.
	$thread =
	    ($thread =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	sysopen(FH, "$cfg{messagedir}/$thread.txt", O_WRONLY | O_TRUNC)
	    or user_error("$err{not_writable} $cfg{messagedir}/$thread.txt. ($!)",
		$user_data{theme});
	flock(FH, LOCK_EX) if $cfg{use_flock};

	for (my $i = 0; $i <= $#{$messages}; $i++)
	{
		($subject, $date, $username, $icon, $ip, $message) =
		    split (/\|/, $messages->[$i]);

		if ($post == $i)
		{

			# Modify message.
			if ($modify ne '')
			{

				# Check input.
				user_error($err{enter_subject}, $user_data{theme})
				    unless ($post_subject);
				user_error($err{enter_text}, $user_data{theme})
				    unless ($post_message);

				# Format input.
				$post_subject = html_escape($post_subject);
				$post_message = html_escape($post_message);

				if ($post == 0)
				{

					# Update board index.
					$board =
					    ($board =~ /^([\w.]+)$/) 
					    ? $1
					    : user_error($err{bad_input}, $user_data{theme});
					sysopen(FH2, "$cfg{boardsdir}/$board.txt",
						O_WRONLY | O_TRUNC)
					    or user_error(
						"$err{not_writable} $cfg{boardsdir}/$board.txt. ($!)",
						$user_data{theme});
					flock(FH2, LOCK_EX) if $cfg{use_flock};
					for (my $j = 0; $j <= $#{$threads}; $j++)
					{
						my (
							$num,                $subject_thread,
							$username_thread,    $date_thread,
							$replies_thread,     $views_thread,
							$last_poster_thread, $icon_thread,
							$state_thread
						    )
						    = split (/\|/, $threads->[$j]);

						if ($num == $thread)
						{
							print FH2
							    "$num|$post_subject|$username_thread|$date_thread|$replies_thread|$views_thread|$last_poster_thread|$post_icon|$state_thread\n";
						}
						else { print FH2 "$threads->[$j]\n"; }
					}
					close(FH2);
				}

				# Update message.
				print FH
				    "$post_subject|$date|$username|$post_icon|$ip|$post_message\n";
			}

			# Delete message.
			if ($delete ne '' || $del == 1)
			{

				# Get data of previous post.
				my ($new_poster_thread, $new_date_thread);

				if ($post == $#{$messages})
				{
					(undef, $new_date_thread, $new_poster_thread, undef,
					    undef, undef) =
					    split (/\|/, $messages->[$#$messages - 1]);
				}

				# Update board index.
				$board =
				    ($board =~ /^([\w.]+)$/) 
				    ? $1
				    : user_error($err{bad_input}, $user_data{theme});
				sysopen(FH2, "$cfg{boardsdir}/$board.txt",  O_WRONLY | O_TRUNC)
				    or user_error(
					"$err{not_writable} $cfg{boardsdir}/$board.txt. ($!)",
					$user_data{theme});
				flock(FH2, LOCK_EX) if $cfg{use_flock};
				for (my $j = 0; $j <= $#{$threads}; $j++)
				{
					my (
						$num,                $subject_thread,
						$username_thread,    $date_thread,
						$replies_thread,     $views_thread,
						$last_poster_thread, $icon_thread,
						$state_thread
					    )
					    = split (/\|/, $threads->[$j]);

					# Decrement reply counter.
					$replies_thread--;

					if ($num == $thread)
					{
						print FH2
						    "$num|$subject_thread|$username_thread|$new_date_thread|$replies_thread|$views_thread|$new_poster_thread|$icon_thread|$state_thread\n";
					}
					else { print FH2 "$threads->[$j]\n"; }
				}
				close(FH2);

				# Update user's post count.
				if ($username ne $usr{anonuser})
				{
					my $user_profile =
					    file2array("$cfg{memberdir}/$username.dat", 1);
					$user_profile->[6]--;

					$username =
					    ($username =~ /^([\w.]+)$/) 
					    ? $1
					    : user_error($err{bad_input}, $user_data{theme});
					sysopen(FH2, "$cfg{memberdir}/$username.dat",
						O_WRONLY | O_TRUNC);
					flock(FH2, LOCK_EX) if $cfg{use_flock};
					for (my $i = 0; $i <= $#{$user_profile}; $i++)
					{
						print FH2 "$user_profile->[$i]\n";
					}
					close(FH2);
				}
			}
		}
		else { print FH "$messages->[$i]\n"; }
	}

	close(FH);

	print $query->redirect(
		-location => $cfg{pageurl} . '/forum.' . $cfg{ext} .
		'?op=view_thread;board=' . $board . ';thread=' . $thread . ';start=' .
		$start . '#' . $post);
}

# ---------------------------------------------------------------------
# Print the forum selector.
# ---------------------------------------------------------------------
sub forum_selector
{
	my $files = dir2array($cfg{boardsdir});
	my @cats  = grep(/\.dat/, @{$files});

	# Print list of available forums.
	my $select = '';
	foreach (@cats)
	{
		my ($name, $trash) = split (/\./, $_);
		my $cat = file2array("$cfg{boardsdir}/$_", 1);

		$select = qq($select<option value="$name">$cat->[0]</option>);
	}

	print <<HTML;
<form action="$cfg{pageurl}/forum.$cfg{ext}" method="post">$msg{hop_toC} 
<select name="board">
$select
</select>
<input type="hidden" name="op" value="view_board">
<input type="submit" value="$btn{go}">
</form>
HTML
}

# ---------------------------------------------------------------------
# Print form to subscribe/unsubscribe to notification list.
# ---------------------------------------------------------------------
sub notify
{

	# Check if user has permissions to access this area.
	if ($user_data{sec_level} eq $usr{anonuser})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	# Get board name .
	my $board_info = file2array("$cfg{boardsdir}/$board.dat", 1);
	my $board_name = $board_info->[0];

	my $subcribe_links;
	if ($thread eq '')
	{

		# Get list of user's watching this board.
		my $subscribed;
		my $mails = file2array("$cfg{boardsdir}/$board.mail", 1);
		if ($mails)
		{

			# Check if user has subscribed to this thread.
			foreach (@{$mails})
			{
				$subscribed = ($user_data{email} eq $_) ? 1 : 0;
			}
		}

		$subcribe_links =
		    ($subscribed)
		    ? qq($msg{ask_reply_notify}<br>\n<b><a href="$cfg{pageurl}/forum.$cfg{ext}?op=view_board;board=$board">$nav{yes}</a> - <a href="$cfg{pageurl}/forum.$cfg{ext}?op=notify2;board=$board;state=0">$nav{no}</a></b>)
		    : qq($msg{ask_reply_notify}<br>\n<b><a href="$cfg{pageurl}/forum.$cfg{ext}?op=notify2;board=$board;state=1">$nav{yes}</a> - <a href="$cfg{pageurl}/forum.$cfg{ext}?op=view_board;board=$board">$nav{no}</a></b>);
	}
	else
	{

		# Get list of user's watching this topic.
		my $subscribed;
		my $mails = file2array("$cfg{messagedir}/$thread.mail", 1);
		if ($mails)
		{

			# Check if user has subscribed to this thread.
			foreach (@{$mails})
			{
				$subscribed = ($user_data{email} eq $_) ? 1 : 0;
			}
		}

		$subcribe_links =
		    ($subscribed)
		    ? qq($msg{ask_reply_notify}<br>\n<b><a href="$cfg{pageurl}/forum.$cfg{ext}?op=view_thread;board=$board;thread=$thread">$nav{yes}</a> - <a href="$cfg{pageurl}/forum.$cfg{ext}?op=notify2;board=$board;thread=$thread;state=0">$nav{no}</a></b>)
		    : qq($msg{ask_reply_notify}<br>\n<b><a href="$cfg{pageurl}/forum.$cfg{ext}?op=notify2;board=$board;thread=$thread;state=1">$nav{yes}</a> - <a href="$cfg{pageurl}/forum.$cfg{ext}?op=view_thread;board=$board;thread=$thread">$nav{no}</a></b>);
	}

	print_header();
	print_html($user_data{theme}, "$nav{forums} >>> $board_name");
	print $subcribe_links;
	print_html($user_data{theme}, "$nav{forums} >>> $board_name", 1);
}

# ---------------------------------------------------------------------
# Update notification.
# ---------------------------------------------------------------------
sub notify2
{

	# Check if user has permissions to access this area.
	if ($user_data{sec_level} eq $usr{anonuser})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	# Update subscriber list.
	if ($thread eq '')
	{
		$board =
		    ($board =~ /^([\w.]+)$/) 
		    ? $1
		    : user_error($err{bad_input}, $user_data{theme});
		update_subscriptions("$cfg{boardsdir}/$board.mail", $user_data{email},
			$state);
		print $query->redirect(
			-location => $cfg{pageurl} . '/forum.' . $cfg{ext} .
			'?op=view_board;board=' . $board);
	}
	else
	{
		$thread =
		    ($thread =~ /^([\w.]+)$/) 
		    ? $1
		    : user_error($err{bad_input}, $user_data{theme});
		update_subscriptions("$cfg{messagedir}/$thread.mail",
			$user_data{email}, $state);
		print $query->redirect(
			-location => $cfg{pageurl} . '/forum.' . $cfg{ext} .
			'?op=view_thread;board=' . $board . ';thread=' . $thread);
	}
}

# ---------------------------------------------------------------------
# Print forular to move a thread.
# ---------------------------------------------------------------------
sub move_thread
{

	# Get board info.
	my $cats       = file2array("$cfg{boardsdir}/cats.txt",   1);
	my $board_info = file2array("$cfg{boardsdir}/$board.dat", 1);

	# Check if user is moderator.
	my @moderators = split (/ +/, $board_info->[2]);
	my $is_moderator = 0;

	foreach (@moderators)
	{
		if ($_ eq $user_data{uid}) { $is_moderator = 1; last; }
	}

	# Check if user is authorized to access this area.
	if ($is_moderator != 1 && $user_data{sec_level} ne $usr{admin})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	my $board_list;
	foreach (@{$cats})
	{
		my $cat_info = file2array("$cfg{boardsdir}/$_.cat", 1);

		foreach (@{$cat_info}[2 .. $#$cat_info])
		{

			# Get board name.
			my $board_info = file2array("$cfg{boardsdir}/$_.dat", 1);
			my $board_name = $board_info->[0];

			$board_list .= "<option value=\"$_\">$board_name</option>\n";
		}
	}

	print_header();
	print_html($user_data{theme}, $nav{move_thread});

	print <<HTML;
<form action="$cfg{pageurl}/forum.$cfg{ext}" method="post">
<b>$msg{move_toC}</b> <select name="to_board">
$board_list</select>
<input type="hidden" name="op" value="move_thread2">
<input type="hidden" name="thread" value="$thread">
<input type="hidden" name="from_board" value="$board">
<input type="submit" value="Move">
</form>
HTML

	print_html($user_data{theme}, $nav{move_thread}, 1);
}

# ---------------------------------------------------------------------
# Move a thread.
# ---------------------------------------------------------------------
sub move_thread2
{

	# Get board data.
	my $board_info = file2array("$cfg{boardsdir}/$from_board.dat", 1);

	# Check if user is moderator.
	my @moderators = split (/ +/, $board_info->[2]);
	my $is_moderator = 0;

	foreach (@moderators)
	{
		if ($_ eq $user_data{uid}) { $is_moderator = 1; last; }
	}

	# Check if user is authorized to access this area.
	if ($is_moderator != 1 && $user_data{sec_level} ne $usr{admin})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	if ($from_board eq $to_board)
	{
		user_error($err{bad_input}, $user_data{theme});
	}

	# Get old board threads.
	my $threads = file2array("$cfg{boardsdir}/$from_board.txt", 1);

	# Data integrity check.
	$from_board =
	    ($from_board =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});

	# Update old board index.
	my $move_thread;
	sysopen(FH, "$cfg{boardsdir}/$from_board.txt", O_WRONLY | O_TRUNC)
	    or
	    user_error("$err{not_writable} $cfg{boardsdir}/$from_board.txt. ($!)",
		$user_data{theme});
	flock(FH, LOCK_EX) if $cfg{use_flock};
	for (@{$threads})
	{
		my (
			$num,         $subject, $username,
			$post_date,   $replies, $views,
			$last_poster, $icon,    $state
		    )
		    = split (/\|/, $_);

		if ($num ne $thread) { print FH "$_\n"; }
		else { $move_thread = $_; }
	}
	close(FH);

	# Get new board threads.	
	$threads = file2array("$cfg{boardsdir}/$to_board.txt", 1);

	# Update new board index.
	$to_board =
	    ($to_board =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	sysopen(FH, "$cfg{boardsdir}/$to_board.txt", O_WRONLY | O_TRUNC)
	    or
	    user_error("$err{not_writable} $cfg{boardsdir}/$to_board.txt. ($!)",
		$user_data{theme});
	flock(FH, LOCK_EX) if $cfg{use_flock};

	print FH "$move_thread\n";
	foreach (@{$threads}) { print FH "$_\n"; }

	close(FH);

	print $query->redirect(
		-location => $cfg{pageurl} . '/forum.' . $cfg{ext} .
		'?op=view_thread;board=' . $to_board . ';thread=' . $thread);
}

# ---------------------------------------------------------------------
# Print form to move a thread.
# ---------------------------------------------------------------------
sub remove_thread
{

	# Get board data.
	my $board_info = file2array("$cfg{boardsdir}/$board.dat", 1);

	# Check if user is moderator.
	my @moderators = split (/ +/, $board_info->[2]);
	my $is_moderator = 0;

	foreach (@moderators)
	{
		if ($_ eq $user_data{uid}) { $is_moderator = 1; last; }
	}

	# Check if user is authorized to access this area.
	if ($is_moderator != 1 && $user_data{sec_level} ne $usr{admin})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	print_header();
	print_html($user_data{theme}, $nav{remove_thread});

	print <<HTML;
$msg{confirm_remove}<br>
<a href="$cfg{pageurl}/forum.$cfg{ext}?op=remove_thread2;board=$board;thread=$thread">$nav{yes}</a> - <a href="$cfg{pageurl}/forum.$cfg{ext}?op=view_thread;board=$board;thread=$thread">$nav{no}</a>
HTML

	print_html($user_data{theme}, $nav{remove_thread}, 1);
}

# ---------------------------------------------------------------------
# Remove a thread.
# ---------------------------------------------------------------------
sub remove_thread2
{

	# Get board data.
	my $board_info = file2array("$cfg{boardsdir}/$board.dat", 1);

	# Check if user is moderator.
	my @moderators = split (/ +/, $board_info->[2]);
	my $is_moderator = 0;

	foreach (@moderators)
	{
		if ($_ eq $user_data{uid}) { $is_moderator = 1; last; }
	}

	# Check if user is authorized to access this area.
	if ($is_moderator != 1 && $user_data{sec_level} ne $usr{admin})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	# Get thread.
	my $messages = file2array("$cfg{messagedir}/$thread.txt", 1);

	# Check if thread is empty.
	if (scalar @{$messages} >= 1)
	{
		user_error($err{delete_replies}, $user_data{theme});
	}

	# Get board threads.
	my $threads = file2array("$cfg{boardsdir}/$board.txt", 1);

	# Update board index.
	$board =
	    ($board =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	sysopen(FH, "$cfg{boardsdir}/$board.txt", O_WRONLY | O_TRUNC)
	    or user_error("$err{not_writable} $cfg{boardsdir}/$board.txt. ($!)",
		$user_data{theme});
	flock(FH, LOCK_EX) if $cfg{use_flock};
	for (@{$threads})
	{
		my (
			$num,         $subject, $username,
			$post_date,   $replies, $views,
			$last_poster, $icon,    $state
		    )
		    = split (/\|/, $_);

		if ($num ne $thread) { print FH "$_\n"; }
	}
	close(FH);

	# Delete thread messages and notification database.
	$thread =
	    ($thread =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	unlink("$cfg{messagedir}/$thread.txt");
	unlink("$cfg{messagedir}/$thread.mail");

	print $query->redirect(
		-location => $cfg{pageurl} . '/forum.' . $cfg{ext} .
		'?op=view_board;board=' . $board);
}

# ---------------------------------------------------------------------
# Lock/unlock a thread.
# ---------------------------------------------------------------------
sub lock_thread
{

	# Get board data.
	my $board_info = file2array("$cfg{boardsdir}/$board.dat", 1);

	# Check if user is moderator.
	my @moderators = split (/ +/, $board_info->[2]);
	my $is_moderator = 0;

	foreach (@moderators)
	{
		if ($_ eq $user_data{uid}) { $is_moderator = 1; last; }
	}

	# Check if user is authorized to access this area.
	if ($is_moderator != 1 && $user_data{sec_level} ne $usr{admin})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	# Get board threads.
	my $threads = file2array("$cfg{boardsdir}/$board.txt", 1);

	# Update board index.
	$board =
	    ($board =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	sysopen(FH, "$cfg{boardsdir}/$board.txt", O_WRONLY | O_TRUNC)
	    or user_error("$err{not_writable} $cfg{boardsdir}/$board.txt. ($!)",
		$user_data{theme});
	flock(FH, LOCK_EX) if $cfg{use_flock};
	foreach (@{$threads})
	{
		my (
			$num,         $subject, $username,
			$post_date,   $replies, $views,
			$last_poster, $icon,    $state
		    )
		    = split (/\|/, $_);

		if ($num eq $thread)
		{

			# Unlock thread if is locked.
			if (!$state) { $state = 1; }
			else { $state = 0; }

			print FH
			    "$num|$subject|$username|$post_date|$replies|$views|$last_poster|$icon|$state\n";
		}
		else { print FH "$_\n"; }
	}
	close(FH);

	print $query->redirect(
		-location => $cfg{pageurl} . '/forum.' . $cfg{ext} .
		'?op=view_thread;board=' . $board . ';thread=' . $thread);
}

# ---------------------------------------------------------------------
# Display a printerfriendly version of a thread.
# ---------------------------------------------------------------------
sub print_thread
{
	my ($title, $subject, $username, $date, $message);

	# Complain about suspicious input.
	if ($thread !~ /^[0-9]+$/) { user_error($err{bad_input}); }

	if ($thread)
	{

		# Get thread.
		my $messages = file2array("$cfg{messagedir}/$thread.txt", 1);

		# Get title of thread.
		($title, undef, undef, undef, undef, undef) =
		    split (/\|/, $messages->[0]);

		print_header();
		print <<HTML;
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>
<meta name="Generator" content="YaWPS $VERSION">
<title>$title</title>
</head>

<body bgcolor="#ffffff" text="#000000">
<h1 align="center">$title</h1>
HTML

		# Print messages.
		foreach (@{$messages})
		{
			($subject, $date, $username, undef, undef, $message) =
			    split (/\|/, $_);

			my $user_profile = file2array("$cfg{memberdir}/$username.dat", 1);
			if (@$user_profile) { $username = $user_profile->[1]; }

			# Make UBBC.
			$message = do_ubbc($message);

			# Format date.
			my $formatted_date = format_date($date);

			print <<HTML;
<hr size=2 width="100%">
<h3>$subject</h3>
<b>$msg{written_by} $username ($formatted_date)</b><br>
<p>$message</p>
HTML
		}

		print "</body>\n\n</html>\n";
	}
}

# ---------------------------------------------------------------------
# Check if user has permission to access the forum.
# ---------------------------------------------------------------------
sub check_board_access
{
	my $categories = shift;
	my $board      = shift || '';

	foreach (@{$categories})
	{
		my $cat_info = file2array("$cfg{boardsdir}/$_.cat", 1);

		foreach (@{$cat_info}[1 .. $#$cat_info])
		{
			if ($cat_info->[1] && $cat_info->[1] ne $usr{anonuser} &&
				$cat_info->[1] ne 'anonuser' &&
				$cat_info->[1] ne $usr{user} &&
				$cat_info->[1] ne $user_data{sec_level} && $board ne '' &&
				$_ eq $board && $user_data{sec_level} ne $usr{admin})
			{
				return 0;
			}
		}
	}
	return 1;
}

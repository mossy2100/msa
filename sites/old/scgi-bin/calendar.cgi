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
# $Id: calendar.cgi,v 1.6 2004/02/17 12:20:38 d3m1g0d Exp $
# =====================================================================

# Load necessary modules.
use strict;
use Time::Local;
use lib '.';
use yawps;

# Assign global variables.
use vars qw(
    $query
    $op
    $adjust $this_day $this_month $this_year $title $desc $info $eventid 
    $contact
    %dayinfo
    %user_data
    %user_action
    );

# Create a new CGI object.
$query = new CGI;

# Get the input.
$op     = $query->param('op')     || '';
$adjust = $query->param('adjust') || 0;

($this_month, $this_day, $this_year) = (gmtime(time - $adjust * 60))[4, 3, 5];
$this_year += 1900;

$this_month = $query->param('month') if defined($query->param('month'));
$this_day   = $query->param('day')   if defined($query->param('day'));
$this_year  = $query->param('year')  if defined($query->param('year'));

$title   = $query->param('title');
$desc    = $query->param('desc');
$info    = $query->param('info');
$eventid = $query->param('eventid');
$contact = $query->param('contact');

# Get user profile.
%user_data = authenticate();

# Define possible user actions.
%user_action = (
	view_data    => \&view_data,
	add_event    => \&add_event,
	add_event2   => \&add_event2,
	get_event    => \&get_event,
	remove_event => \&remove_event
    );

# Depending on user action, decide what to do.
if ($user_action{$op}) { $user_action{$op}->(); }
else { display_calendar(); }

# ---------------------------------------------------------------------
# Display the calendar main page.
# ---------------------------------------------------------------------
sub display_calendar
{
	my @months = (
		$months{0}, $months{1}, $months{2},  $months{3},
		$months{4}, $months{5}, $months{6},  $months{7},
		$months{8}, $months{9}, $months{10}, $months{11}
	    );

	my $month_name = $months[$this_month];

	my $this_day_sec = timegm(0, 0, 0, 1, $this_month, $this_year);
	my $this_day_week = (gmtime($this_day_sec))[6];
	my $day_sec       = 24 * 60 * 60;
	my @today         = gmtime(time - $adjust * 60);
	my $today_sec     = timegm(0, 0, 0, $today[3], $today[4], $today[5]);
	my $next_month    = $this_month + 1;
	my $next_year     = $this_year;
	if ($next_month > 11)
	{
		$next_month = 0;
		$next_year  = $this_year + 1;
	}

	my $prev_month = $this_month - 1;
	my $prev_year  = $this_year;
	if ($prev_month < 0)
	{
		$prev_month = 11;
		$prev_year  = $this_year - 1;
	}

	print_header();
	print_html($user_data{theme}, $nav{calendar});

	# Print monthly calendar.
	print <<HTML;
<center><b>$month_name&nbsp;$this_year</b></center>
<table align="center" width="100%">
<tr>
<td><a href="$cfg{pageurl}/calendar.$cfg{ext}?month=$prev_month;year=$prev_year"><b><-</b></a></td>
<td align="right"><a href="$cfg{pageurl}/calendar.$cfg{ext}?month=$next_month;year=$next_year"><b>-></b></a></td>
</tr>
</table>
<p>&nbsp;
<table class="bg5" width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td>
<table width="100%" border="0" cellspacing="1" cellpadding="2">
<tr class="tbl_header">
HTML

	foreach (sort { $a <=> $b } keys %week_days)
	{
		print qq(<td align="center">$week_days{$_}</td>\n);
	}

	if ($this_day_week > 0)
	{
		print
		    qq(</tr>\n<tr class="tbl_row_light">\n<td colspan="$this_day_week"></td>\n);
	}

	my $this_day_month = 1;

	for (; $day_sec <= $this_day_sec + $day_sec * 31;
		$this_day_sec = $this_day_sec + $day_sec)
	{
		last if ((gmtime($this_day_sec))[4] != $this_month);

		($this_day_month, $this_day_week) = (gmtime($this_day_sec))[3, 6];

		if ($this_day_week == 0)
		{
			print qq(</tr>\n<tr class="tbl_row_light">\n);
		}

		print
		    qq(<td width="14%" valign="top"><a href="$cfg{pageurl}/calendar.$cfg{ext}?op=view_data;month=$this_month;day=$this_day_month;year=$this_year">);

		# Count number of events on this day
		my $day_file = $this_year . $this_month . $this_day_month;
		my ($day_events, $last_event);
		$day_events = 0;

		if (-e "$cfg{calendardir}/$day_file.txt")
		{
			sysopen(FH, "$cfg{calendardir}/$day_file.txt", O_RDONLY);
			while (<FH>)
			{
				$_ =~ s/^\s+//;
				$_ =~ s/\s+$//;

				if ($_) {
					$day_events++;
					$last_event = (split (/\|/, $_))[2];
				}
			}
			close FH;
		}

		if ($today_sec == $this_day_sec) { print qq(<b>); }
		if ($day_events > 1)
		{
			print qq($this_day_month: $day_events $msg{events});
		}
		if ($day_events == 1) { print qq($this_day_month: $last_event); }
		if ($day_events == 0) { print qq($this_day_month); }
		if ($today_sec == $this_day_sec) { print qq(</b>); }

		print qq(</a></td>\n);
	}

	my $this_week_days_left = 6 - $this_day_week;
	if ($this_week_days_left > 0)
	{
		print qq(<td colspan="$this_week_days_left"></td>\n);
	}

	print <<HTML;
</tr>
</table>
</td>
</tr>
</table>
<br>
<form method="post" action="$cfg{pageurl}/calendar.$cfg{ext}">
<table border="0" cellpadding="2" cellspacing="1" width="100%">
<tr>
<td align="center"><select name="month">
HTML

	# Print month/year selection.
	my $i = 0;
	for (@months)
	{
		my $selected = $_ eq $months[$this_month] ? ' selected' : '';
		print qq(<option value="$i"$selected>$_</option>\n);
		$i++;
	}

	print qq(</select>\n<select name="year">\n);

	for (2000 .. 2030)
	{
		my $selected = $_ == $this_year ? ' selected' : '';
		print qq(<option value="$_"$selected>$_</option>\n);
	}

	print <<HTML;
</select>
<input type="submit" value="$btn{go}">
</td>
</tr>
</table>
</form>
HTML

	print_html($user_data{theme}, $nav{calendar}, 1);
}

# ---------------------------------------------------------------------
# Display the events for a day.
# ---------------------------------------------------------------------
sub view_data
{
	my $wday      = (gmtime(time - $adjust * 60))[6];
	my $long_date =
	    $week_days{$wday} . ", " . $this_day . " " . $months{$this_month} .
	    " " . $this_year;

	# Get events for this day.
	my (%datas, $hour, $rest);
	my $day_file = $this_year . $this_month . $this_day;

	if (-e "$cfg{calendardir}/$day_file.txt")
	{
		sysopen(FH, "$cfg{calendardir}/$day_file.txt", O_RDONLY);
		while (<FH>)
		{
			chomp;
			my ($id, $rest) = split (/\|/, $_, 2);
			my @fields = split (/\|/, $_);

			$datas{$id} = \@fields;
		}
		close(FH);
	}

	print_header();
	print_html($user_data{theme},
		"$nav{calendar} >>> $msg{events} >>> $long_date");

	# Print day schedule.
	if (keys %datas)
	{
		print <<HTML;
<table class="bg5" width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td>
<table width="100%" border="0" cellspacing="1" cellpadding="2">
HTML

		# Print events
		my $row_color = " class=\"tbl_row_dark\"";
		foreach (keys %datas)
		{

			# Alternate the row colors.
			if ($row_color eq " class=\"tbl_row_dark\"")
			{
				$row_color = " class=\"tbl_row_light\"";
			}
			else { $row_color = " class=\"tbl_row_dark\""; }

			print "<tr$row_color>";
			if ($datas{$_}[1])
			{
				print <<HTML;
<td><b>$datas{$_}[2]:</b></td>
<td><a href=$cfg{pageurl}/calendar.$cfg{ext}?op=get_event;month=$this_month;day=$this_day;year=$this_year;eventid=$datas{$_}[0]>$datas{$_}[3]</a></td>
<td align="right" width="100">$msg{by} <a href=$cfg{pageurl}/user.$cfg{ext}?op=view_profile;username=$datas{$_}[1]>$datas{$_}[1]</a></td>
HTML
			}

			# Show remove link.
			if ($user_data{sec_level} eq $usr{mod}
				|| $user_data{sec_level} eq $usr{admin})
			{
				print
				    qq(<td align="right" width="100"><a href=$cfg{pageurl}/calendar.$cfg{ext}?op=remove_event;month=$this_month;day=$this_day;year=$this_year;eventid=$datas{$_}[0]>$btn{delete}</a></td>);
			}
			print "</tr>\n";
		}

		print <<HTML;
</table>
</td>
</tr>
</table>
HTML
	}

	print
	    qq(<p><a href="$cfg{pageurl}/calendar.$cfg{ext}?op=add_event;month=$this_month;day=$this_day;year=$this_year">$nav{add_event}</a>);

	print_html($user_data{theme},
		"$nav{calendar} >>> $msg{events} >>> $long_date", 1);
}

# ---------------------------------------------------------------------
# Display formula to add an event.
# ---------------------------------------------------------------------
sub add_event
{

	# Check permissions
	if ($user_data{sec_level} ne $usr{mod} &&
		$user_data{sec_level} ne $usr{admin})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	my $wday      = (gmtime(time - $adjust * 60))[6];
	my $long_date =
	    $week_days{$wday} . ", " . $this_day . " " . $months{$this_month} .
	    " " . $this_year;

	print_header();
	print_html($user_data{theme},
		"$nav{calendar} >>> $long_date >>> $nav{add_event}");

	print <<HTML;
<form method="post" action="$cfg{pageurl}/calendar.$cfg{ext}">
<table border="0" cellpadding="5" cellspacing="0">
<tr>
<td><b>$msg{subjectC}</b></td>
<td><input type="text" name="title" size="40" maxlength="100"></td>
</tr>
<tr>
<td valign="top"><b>$msg{summary}</b></td>
<td><textarea name="desc" cols="40" rows="5" maxlength="255"></textarea><br>$msg{keep_it_short}</td>
</tr>
<tr>
<td valign="top"><b>$msg{descriptionC}</b></td>
<td><textarea name="info" cols="40" rows="5" maxlength="255"></textarea></td>
</tr>
<tr>
<td colspan="2"><input type="hidden" name="op" value="add_event2">
<input type="hidden" name="month" value="$this_month">
<input type="hidden" name="day" value="$this_day">
<input type="hidden" name="year" value="$this_year">
<input type="submit" value="$btn{add_link}">
<input type="reset" value="$btn{reset}"></td>
</tr>
</table>
</form>
HTML

	print_html($user_data{theme},
		"$nav{calendar} >>> $long_date >>> $nav{add_event}", 1);
}

# ---------------------------------------------------------------------
# Add an event to database.
# ---------------------------------------------------------------------
sub add_event2
{

	# Check permissions
	if ($user_data{sec_level} ne $usr{mod} &&
		$user_data{sec_level} ne $usr{admin})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	# Check input.
	user_error($err{enter_subject},    $user_data{theme}) unless ($title);
	user_error($err{enter_summary},    $user_data{theme}) unless ($desc);
	user_error($err{enter_event_info}, $user_data{theme}) unless ($info);

	# Format input.
	chomp($title);
	chomp($desc);
	chomp($info);
	$title = html_escape($title);
	$desc  = html_escape($desc);

	# Get entries for this day.
	my (%datas);
	my $day_file = $this_year . $this_month . $this_day;

	# Data integrity check.
	if ($day_file =~ /^([\w.]+)$/) { $day_file = $1; }
	else { user_error($err{bad_input}, $user_data{theme}); }

	if (-e "$cfg{calendardir}/$day_file.txt")
	{
		sysopen(FH, "$cfg{calendardir}/$day_file.txt", O_RDONLY);
		while (<FH>)
		{
			chomp $_;
			my ($id, $rest) = split (/\|/, $_, 2);
			$datas{$id} = $rest;
		}
		close(FH);
	}

	my $new_id = 0;
	while ($datas{$new_id}) { $new_id++; }

	my $event_file = $day_file . '-' . $new_id;

	# Data integrity check.
	if ($event_file =~ /^([\w.-]+)$/) { $event_file = $1; }
	else { user_error($err{bad_input}, $user_data{theme}); }

	# Add entry.
	my $new_entry = join ('|', $user_data{uid}, $title, $desc);
	$datas{$new_id} = $new_entry;

	# Save entries.
	sysopen(FH, "$cfg{calendardir}/$day_file.txt",
		O_WRONLY | O_TRUNC | O_CREAT)
	    or
	    user_error("$err{not_writable} $cfg{calendardir}/$day_file.txt. ($!)",
		$user_data{theme});
	flock(FH, LOCK_EX) if $cfg{use_flock};

	foreach (sort keys %datas)
	{
		if ($datas{$_}) { print FH join ('|', $_, $datas{$_}) . "\n"; }
	}

	close(FH);

	# Write info file for this event.
	sysopen(FH, "$cfg{calendardir}/$event_file.txt",
		O_WRONLY | O_TRUNC | O_CREAT)
	    or user_error(
		"$err{not_writable} $cfg{calendardir}/$event_file.txt. ($!)",
		$user_data{theme});
	flock(FH, LOCK_EX) if $cfg{use_flock};

	print FH $info;
	close(FH);

	print $query->redirect(
		-location => $cfg{pageurl} . '/calendar.' . $cfg{ext} .
		'?op=view_data;month=' . $this_month . ';day=' . $this_day .
		';year=' . $this_year);
}

# ---------------------------------------------------------------------
# Display information about an event.
# ---------------------------------------------------------------------
sub get_event
{
	my $wday      = (gmtime(time - $adjust * 60))[6];
	my $long_date =
	    $week_days{$wday} . ", " . $this_day . " " . $months{$this_month} .
	    " " . $this_year;
	my $day_file = $this_year . $this_month . $this_day;

	# Data integrity check.
	if ($day_file =~ /^([\w.]+)$/) { $day_file = $1; }
	else { user_error($err{bad_input}, $user_data{theme}); }

	my @fields;

	if (-e "$cfg{calendardir}/$day_file.txt")
	{
		sysopen(FH, "$cfg{calendardir}/$day_file.txt", O_RDONLY);
		while (<FH>)
		{
			chomp $_;
			my ($id, $rest) = split (/\|/, $_, 2);
			if ($id == $eventid) { @fields = split (/\|/, $rest); }
		}
		close(FH);
	}

	my $event_file = $day_file . '-' . $eventid;

	# Data integrity check.
	if ($event_file =~ /^([\w.-]+)$/) { $event_file = $1; }
	else { user_error($err{bad_input}, $user_data{theme}); }

	print_header();
	print_html($user_data{theme},
		"$nav{calendar} >>> $long_date >>> $fields[1]");

	print <<HTML;
<table border="0" cellpadding="5" cellspacing="0" width="100%">
<tr>
<td colspan="2" class="texttitle">$fields[1]</td>
</tr>
<tr>
<td width="100"><b>$msg{summary}</b></td>
<td>$fields[2]</td>
</tr>
<tr>
<td width="100"><b>$msg{descriptionC}</b></td>
<td>
HTML
	if (-e "$cfg{calendardir}/$event_file.txt")
	{
		sysopen(FH, "$cfg{calendardir}/$event_file.txt", O_RDONLY);
		while (<FH>) { print; }
		close(FH);
	}
	print <<HTML;
</td>
</tr>
</table>
</form>
HTML

	print_html($user_data{theme},
		"$nav{calendar} >>> $long_date >>> $fields[1]", 1);

}

# ---------------------------------------------------------------------
# Add an event from database.
# ---------------------------------------------------------------------
sub remove_event
{

	# Check permissions
	if ($user_data{sec_level} ne $usr{mod} &&
		$user_data{sec_level} ne $usr{admin})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	my $day_file = $this_year . $this_month . $this_day;

	# Data integrity check.
	if ($day_file =~ /^([\w.]+)$/) { $day_file = $1; }
	else { user_error($err{bad_input}, $user_data{theme}); }

	my (@fields, %events);

	my $dayfile_name = "$cfg{calendardir}/$day_file.txt";
	if (-e "$dayfile_name")
	{
		sysopen(FH, "$dayfile_name", O_RDONLY);
		while (<FH>)
		{
			chomp $_;
			my ($id, $rest) = split (/\|/, $_, 2);
			if ($id == $eventid) { @fields = split (/\|/, $rest); }
			$events{$id} = $rest;
		}
		close FH;

		# Check if user is event creator or site admin.
		if ($user_data{uid} ne $fields[0] &&
			$user_data{sec_level} ne $usr{admin})
		{
			user_error($err{auth_failure}, $user_data{theme});
		}

		my $event_file = "$day_file-$eventid.txt";

		# Data integrity check.
		if ($event_file =~ /^([\w.-]+)$/) { $event_file = $1; }
		else { user_error($err{bad_input}, $user_data{theme}); }

		if (-e "$cfg{calendardir}/$event_file" &&
			(unlink("$cfg{calendardir}/$event_file") == 1))
		{
			sysopen(FH, "$dayfile_name", O_WRONLY | O_TRUNC | O_CREAT);
			foreach (sort keys %events)
			{
				if ($_ != $eventid) { print FH "$_\|$events{$_}\n"; }
			}
			close FH;
		}
		else
		{
			user_error($err{not_writable} . " $cfg{calendardir}/$event_file",
				$user_data{theme});
		}
	}
	else {
		user_error($err{open_file} . " $dayfile_name", $user_data{theme});
	}

	print $query->redirect(
		-location => $cfg{pageurl} . '/calendar.' . $cfg{ext} .
		'?op=view_data;month=' . $this_month . ';day=' . $this_day .
		';year=' . $this_year);
}

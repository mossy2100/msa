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
# $Id: polls.cgi,v 1.13 2004/02/17 12:20:38 d3m1g0d Exp $
# =====================================================================

# Load necessary modules.
use strict;
use lib '.';
use yawps;

# Assign global variables.
use vars qw(
    $query
    $op $answer $id $submitted
    $voted
    %user_data
    %user_action
    );

# Create a new CGI object.
$query = new CGI;

# Get the input.
$op = $query->param('op') || '';
$answer    = $query->param('answer');
$id        = $query->param('id') || '';
$submitted = $query->param('submitted');

# Get user profile.
%user_data = authenticate();

# Check cookie.
$voted = $query->cookie('yawps_poll');

# Define possible user actions.
%user_action = (
	view_poll => \&display_result,
	do_vote   => \&do_vote
    );

# Depending on user action, decide what to do.
if ($user_action{$op}) { $user_action{$op}->(); }
else { display_result(); }

# ---------------------------------------------------------------------
# Poll voting routine.
# ---------------------------------------------------------------------
sub do_vote
{
	if ($answer eq '') { user_error($err{choose_answer}, $user_data{theme}); }

	# Data integrity check.
	if ($id =~ /^([\w.]+)$/) { $id = $1; }
	else { user_error($err{bad_input}, $user_data{theme}); }

	# Check if user has already voted.
	my $cookie_poll;
	if ($voted) { user_error($err{already_voted}, $user_data{theme}); }

	# If not set the cookie and log voter.
	else
	{
		$cookie_poll = $query->cookie(
			-name    => 'yawps_poll',
			-value   => $id,
			-path    => '/',
			-expires => $cfg{cookie_expire}
		    );

		# Get user's IP address
		my $host = $ENV{REMOTE_ADDR};
		if ($ENV{REMOTE_HOST}) { $host = $ENV{REMOTE_HOST}; }

		# Get date.
		my $date     = get_date();
		my $log_file = $id . "_ip.dat";

		my $logs = file2array("$cfg{polldir}/$log_file", 0);

		# Update log.
		sysopen(FH, "$cfg{polldir}/$log_file", O_WRONLY | O_TRUNC | O_CREAT)
		    or user_error("$err{not_writable} $cfg{polldir}/$log_file. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		print FH "$date - $host - $user_data{uid}\n";
		if ($logs) { print FH $logs; }
		close(FH);
	}

	my $answer_file = $id . "_a.dat";

	# Get possible answers for current poll.
	my $poll_answer = file2array("$cfg{polldir}/$answer_file", 1);

	# Update poll results.
	sysopen(FH, "$cfg{polldir}/$answer_file", O_WRONLY | O_TRUNC | O_CREAT)
	    or user_error("$err{not_writable} $cfg{polldir}/$answer_file. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	for (my $i = 0; $i < @{$poll_answer}; $i++)
	{
		if ($i == $answer)
		{

			# Increment answer count.
			my $count = $poll_answer->[$i] + 1;
			print FH "$count\n";
		}
		else { print FH "$poll_answer->[$i]\n"; }
	}
	close(FH);

	# Display the result.
	print_header($cookie_poll);
	display_result();
}

# ---------------------------------------------------------------------
# Display the results of a poll.
# ---------------------------------------------------------------------
sub display_result
{
	if ($id !~ /^[0-9]+$/) { user_error($err{bad_input}, $user_data{theme}); }

	my $question_file = $id . "_q.dat";
	my $answer_file   = $id . "_a.dat";

	# Get all poll datas.
	my $polls         = file2array("$cfg{polldir}/polls.txt",      1);
	my $poll_question = file2array("$cfg{polldir}/$question_file", 1);
	my $poll_answer   = file2array("$cfg{polldir}/$answer_file",   1);
	if (!@$poll_question || !@$poll_answer)
	{
		user_error($err{bad_input}, $user_data{theme});
	}

	# Get title of curent poll.
	my $poll_title;
	foreach (@{$polls})
	{
		my @items = split (/\|/, $_);
		if ($items[0] == $id) { $poll_title = $items[1]; last; }
	}

	# Get total amount of votes.
	my $total_votes = 0;
	if ($poll_answer)
	{
		foreach (@{$poll_answer}) { $total_votes = $total_votes + $_; }
	}

	if ($op eq 'view_poll') { print_header(); }
	print_html($user_data{theme}, $nav{results});

	print <<HTML;
<table align="center" cellpadding="0" cellspacing="0" border="0">
<tr>
<td colspan="2" align="center"><b>$msg{topicC} $poll_title</b></td>
</tr>
<tr>
<td colspan="2">&nbsp;</td>
</tr>
HTML

	# Calculate poll result.
	for (my $i = 0; $i < @{$poll_answer}; $i++)
	{
		my ($percent, $pixel, $a, $b);
		if ($total_votes != 0)
		{
			$pixel = int((($poll_answer->[$i] / $total_votes) * 100) / 2);

			$percent = ($poll_answer->[$i] / $total_votes) * 100;
			my $c = int(10 * ($percent * 10 - int($percent * 10)));

			$b = int(10 * ($percent - int($percent)));
			$a = int($percent);

			if ($c >= 5) { $b++ }
		}
		else { $a = 0; $b = 0; }

		$percent = $a . "." . $b;
		if (!$pixel) { $pixel = 0; }

		print <<HTML;
<tr>
<td>$poll_question->[$i]:</td>
<td><img src="$cfg{imagesurl}/leftbar.gif" width="7" height="14" alt=""><img src="$cfg{imagesurl}/mainbar.gif" width="$pixel" height="14" alt=""><img src="$cfg{imagesurl}/rightbar.gif" width="7" height="14" alt="">&nbsp;&nbsp;($percent%)</td>
</tr>
HTML
	}

	print <<HTML;
<tr>
<td colspan="2">&nbsp;</td>
</tr>
<tr>
<td colspan="2" align="center">$msg{votesC} $total_votes</td>
</tr>
</table>
HTML

	# Print all available polls.
	if ($polls)
	{
		print <<HTML;
<br><br>
<table align="center" cellspacing="2">
<tr>
<td align="center"><b>$msg{other_pollsC}</b></td>
</tr>
HTML
		foreach (@{$polls})
		{
			my @item = split (/\|/, $_);
			print <<HTML;
<tr>
<td>$item[1]</td>
<td align="right"><a href="$cfg{pageurl}/polls.$cfg{ext}?op=view_poll;id=$item[0]">$nav{results}</a></td>
</tr>
HTML
		}
		print "</table>\n";
	}

	print_html($user_data{theme}, $nav{results}, 1);
}

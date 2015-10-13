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
# $Id: recommend.cgi,v 1.13 2004/04/14 08:43:43 d3m1g0d Exp $
# =====================================================================

# Load necessary modules.
use strict;
use lib '.';
use yawps;

# Assign global variables.
use vars qw(
    $query
    $op $sender_name $sender_email $recip_name $recip_email
    %user_data
    %user_action
    );

# Create a new CGI object.
$query = new CGI;

# Get the input.
$op = $query->param('op') || '';
$sender_name  = $query->param('sender_name');
$sender_email = $query->param('sender_email');
$recip_name   = $query->param('recip_name');
$recip_email  = $query->param('recip_email');

# Get user profile.
%user_data = authenticate();

# Define possible user actions.
%user_action = (recommend2 => \&recommend2);

# Depending on user action, decide what to do.
if ($user_action{$op}) { $user_action{$op}->(); }
else { recommend(); }

# ---------------------------------------------------------------------
# Display the recommendation page.
# ---------------------------------------------------------------------
sub recommend
{
	print_header();
	print_html($user_data{theme}, $nav{recommend});

	print <<HTML;
<form method="post" action="$cfg{pageurl}/recommend.$cfg{ext}">
<table border="0" cellspacing="1">
HTML

	# If user is logged in print his name and email address.
	if ($user_data{uid} ne $usr{anonuser})
	{
		print <<HTML;
<tr>
<td><b>$msg{your_nameC}</b></td>
<td><input type="text" size="14" value="$user_data{nick}" disabled><input type="hidden" name="sender_name" value="$user_data{nick}"></td>
</tr>
<tr>
<td><b>$msg{your_emailC}</b></td>
<td><input type="text" size="14" value="$user_data{email}" disabled><input type="hidden" name="sender_email" value="$user_data{email}"></td>
</tr>
HTML
	}
	else
	{
		print <<HTML;
<tr>
<td><b>$msg{your_nameC}</b></td>
<td><input type="text" name="sender_name" size="14" value=""></td>
</tr>
<tr>
<td><b>$msg{your_emailC}</b></td>
<td><input type="text" name="sender_email" size="14" value=""></td>
</tr>
HTML
	}

	print <<HTML;
<tr>
<td><b>$msg{friend_nameC}</b></td>
<td><input type="text" name="recip_name" size="14" value=""></td>
</tr>
<tr>
<td><b>$msg{friend_emailC}</b></td>
<td><input type="text" name="recip_email" size="14" value=""></td>
</tr>
<tr>
<td colspan="2"><input type="hidden" name="op" value="recommend2"><input type="submit" value="$btn{send}"></td>
</tr>
</table>
</form>
HTML

	print_html($user_data{theme}, $nav{recommend}, 1);
}

# ---------------------------------------------------------------------
# Send the recommandation.
# ---------------------------------------------------------------------
sub recommend2
{
	if (!$sender_email || 
	    $sender_email !~ /^[0-9A-Za-z@\._\-]+$/) 
	{ 
		user_error($err{enter_email}, $user_data{theme}); 
	}
	if (!$recip_name || 
	    $recip_name !~ /^[0-9A-Za-z_\- ]+$/) 
	{ 
		user_error($err{enter_receiver}, $user_data{theme}); 
	}
	if (!$recip_email || 
	    $recip_email !~ /^[0-9A-Za-z@\._\-]+$/ || 
	    $recip_email =~ /(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)|(\.$)/  ||
	    $recip_email !~ /\A.+@\[?(\w|[-.])+\.[a-zA-Z]{2,4}|[0-9]{1,4}\]?\Z/)
	{
		user_error($err{enter_receiver_mail}, $user_data{theme});
	}

	my $subject = $inf{site_recommendation} . ": " . $cfg{pagename};
	my $message = <<HTML;
$recip_name,

$sender_name $inf{or} $ENV{REMOTE_ADDR}
$inf{recommend_message}
HTML

	# Send the email to recipient.
	send_email($sender_email, $recip_email, $subject, $message);

	# Log user action.
	my $date = get_date();

	sysopen(FH, "$cfg{datadir}/recommend.log", O_WRONLY | O_APPEND | O_CREAT);
	flock(FH, LOCK_EX) if $cfg{use_flock};
	print FH
	    "$date|$ENV{REMOTE_ADDR}|$sender_name|$sender_email|$recip_name|$recip_email\n";
	close(FH);

	# Print success message.
	print_header();
	print_html($user_data{theme}, $nav{recommend});

	print $inf{recommendation_sent} . " <b>" . $recip_name . " (" .
	    $recip_email . ")</b><br>\n" . $inf{thank_you};

	print_html($user_data{theme}, $nav{recommend}, 1);
}

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
# $Id: instant_messenger.cgi,v 1.14 2004/02/17 12:20:38 d3m1g0d Exp $
# =====================================================================

# Load necessary modules.
use strict;
use lib '.';
use yawps;

# Assign global variables.
use vars qw(
    $query
    $op $quote $id $to $subject $message
    %user_data
    %user_action
    );

# Create a new CGI object.
$query = new CGI;

# Get the input.
$op = $query->param('op') || '';
$quote   = $query->param('quote');
$id      = $query->param('id') || 0;
$to      = $query->param('to') || '';
$subject = $query->param('subject');
$message = $query->param('message');

# Get user profile.
%user_data = authenticate();

# Define possible user actions.
%user_action = (
	send_im   => \&send_im,
	send_im2  => \&send_im2,
	delete_im => \&delete_im
    );

# Depending on user action, decide what to do.
if ($user_action{$op}) { $user_action{$op}->(); }
else { display_im_index(); }

# ---------------------------------------------------------------------
# Display all available instant messages.
# ---------------------------------------------------------------------
sub display_im_index
{

	# Check if user is logged in.
	if ($user_data{uid} eq $usr{anonuser})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	# Get messages in IM inbox.
	my $messages = file2array("$cfg{memberdir}/$user_data{uid}.msg", 1);

	# Get censored words.
	my @censored;
	my $censor = file2array("$cfg{datadir}/censor.txt", 1);

	print_header();
	print_html($user_data{theme}, $nav{im_index});

	# Show the inbox.
	if (scalar @{$messages} == 0) { print $msg{no_messages}; }
	else
	{
		print <<HTML;
<table class="bg5" width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td>
<table width="100%" border="0" cellspacing="1" cellpadding="2">
<tr class="tbl_header">
<td><b>$msg{authorC}</b></td>
<td><b>$msg{subjectC}</b></td>
</tr>
HTML

		my $row_color = qq( class="tbl_row_dark");
		foreach (@{$messages})
		{

			# Alternate the row colors.
			$row_color =
			    ($row_color eq qq( class="tbl_row_dark"))
			    ? qq( class="tbl_row_light")
			    : qq( class="tbl_row_dark");

			my ($message_info, $star, $view, $url, $icq, $signature);
			my ($username, $subject, $date, $message, $id, $unread) =
			    split (/\|/, $_);

			if (!$subject) { $subject = "---"; }

			# Make UBBC.
			$message = do_ubbc($message);

			# Check for censored words.
			foreach my $word (@censored)
			{
				my ($bad_word, $censored) = split (/\=/, $word);
				$message =~ s/$bad_word/$censored/g;
				$subject =~ s/$bad_word/$censored/g;
			}

			# Format date.
			my $formatted_date = format_date($date);

			# Get user profile.
			my $user_profile = file2array("$cfg{memberdir}/$username.dat", 1);
			my $member_info = '';
			if ($user_profile->[7]) { $member_info = $user_profile->[7]; }

			# Print the message.
			print <<HTML;
<tr$row_color>
<td width="140" valign="top" rowspan="2"><b><a href="$cfg{pageurl}/user.$cfg{ext}?op=view_profile;username=$username">$user_profile->[1]</a></b><br>
$member_info</td>
<td valign="top">
<table border="0" width="100%" cellpadding="0" cellspacing="1">
<tr>
<td width="100%">&nbsp;<b>$subject</b></td>
<td align="right" nowrap><b>$msg{written_on}</b> $formatted_date</td>
</tr>
</table>
<hr size="1" noshade>
$message</td>
</tr>
<tr$row_color>
<td>
<table border="0" width="100%" cellpadding="0" cellspacing="1">
<tr>
<td colspan="2" align="right"><a href="$cfg{pageurl}/instant_messenger.$cfg{ext}?op=send_im;id=$id;quote=1;to=$username">$msg{quote}</a>&nbsp;&nbsp;<a href="$cfg{pageurl}/instant_messenger.$cfg{ext}?op=delete_im;id=$id">$msg{delete}</a></td>
</tr>
</table>
</td>
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

	print <<HTML;
<br>
<a href="$cfg{pageurl}/instant_messenger.$cfg{ext}?op=send_im">$nav{send_im}</a>
HTML

	print_html($user_data{theme}, $nav{im_index}, 1);
}

# ---------------------------------------------------------------------
# Display forular to send IMs.
# ---------------------------------------------------------------------
sub send_im
{

	# Check if user is logged in.
	if ($user_data{uid} eq $usr{anonuser})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	my $time_stamp = time;

	# Get messages in IM inbox.
	my $messages = file2array("$cfg{memberdir}/$user_data{uid}.msg", 1);

	# Format the current IM.
	my $subject = '';
	my $message = '';
	if ($messages)
	{
		foreach (@{$messages})
		{
			my (undef, $temp_subject, undef, $temp_message, $mid, undef) =
			    split (/\|/, $_);

			if ($id == $mid && $quote == 1)
			{
				$subject = $temp_subject;

				$temp_message = html_to_text($temp_message);
				$temp_message =~ s/\[quote\](\S+?)\[\/quote\]//isg;
				$temp_message =~ s/\[(\S+?)\]//isg;

				$message = "\n\n[quote\]" . $temp_message . "\[/quote\]";
				last;
			}
		}
	}

	# Generate the UBBC panel.
	my $ubbc_panel = print_ubbc_panel();

	print_header();
	print_html($user_data{theme}, $nav{send_im});

	print <<HTML;
<table width="100%" border="0" cellspacing="0" cellpadding="1">
<tr>
<td><form action="$cfg{pageurl}/instant_messenger.$cfg{ext}" method="post" name="creator">
<table border="0">
<tr>
<td><b>$msg{to_userC}</b></td>
<td><select name="to">
HTML

	# Print list of available users.
	my $members = file2array("$cfg{memberdir}/memberlist.dat", 1);
	my $selected = '';

	foreach (@{$members})
	{
		my $user_profile = file2array("$cfg{memberdir}/$_.dat", 1);
		$selected = ($to eq $_) ? " selected" : '';

		print qq(<option value="$_"$selected>$user_profile->[1]</option>\n);
	}

	print <<HTML;
</select></td>
</tr>
<tr>
<td><b>$msg{subjectC}</b></td>
<td><input type="text" name="subject" value="$subject" size="40" maxlength="50"></td>
</tr>
<tr>
<td valign="top"><b>$msg{textC}</b></td>
<td><script language="javascript" type="text/javascript"><!--
function addCode(anystr) { 
document.creator.message.value+=anystr;
} 
function showColor(color) { 
document.creator.message.value+="[color="+color+"][/color]";
}
// --></script>
<textarea name="message" rows="10" cols="40">$message</textarea></td>
</tr>
<tr>
<td><b>$msg{ubbc_tagsC}</b></td>
<td valign="top">$ubbc_panel</td>
</tr>
<tr>
<td colspan="2"><input type="hidden" name="op" value="send_im2">
<input type="hidden" name="id" value="$time_stamp">
<input type="submit" value="$btn{send_message}">
<input type="reset" value="$btn{reset}"></td>
</tr>
</table>
</form>
</td>
</tr>
</table>
HTML

	print_html($user_data{theme}, $nav{send_im}, 1);
}

# ---------------------------------------------------------------------
# Send an IM.
# ---------------------------------------------------------------------
sub send_im2
{

	# Check input.
	if (!(-r "$cfg{memberdir}/$to.dat"))
	{
		user_error($err{user_no_exist}, $user_data{theme});
	}
	if (!$subject) { user_error($err{enter_subject}, $user_data{theme}); }
	if (!$message) { user_error($err{enter_text},    $user_data{theme}); }

	# Format the input.
	$subject = html_escape($subject);
	$message = html_escape($message);

	# Get the current date.
	my $date = get_date();

	# Get existing messages for recipient user.
	my $messages = file2array("$cfg{memberdir}/$to.msg", 1);

	# Save the new IM.
	if ($to =~ /^([\w.]+)$/) { $to = $1; }
	else { user_error($err{bad_input}, $user_data{theme}); }
	sysopen(FH, "$cfg{memberdir}/$to.msg", O_WRONLY | O_TRUNC | O_CREAT);
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	print FH "$user_data{uid}|$subject|$date|$message|$id|1\n";
	if ($messages)
	{
		foreach (@{$messages}) { print FH "$_\n"; }
	}
	close(FH);

	print $query->redirect(
		-location => $cfg{pageurl} . '/instant_messenger.' . $cfg{ext});
}

# ---------------------------------------------------------------------
# Delete an IM.
# ---------------------------------------------------------------------
sub delete_im
{

	# Check if user is logged in.
	if ($user_data{uid} eq $usr{anonuser})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	# Get messages in IM inbox.
	my $messages = file2array("$cfg{memberdir}/$user_data{uid}.msg", 1);

	# Update IM inbox.
	if ($user_data{uid} =~ /^([\w.]+)$/) { $user_data{uid} = $1; }
	else { user_error($err{bad_input}, $user_data{theme}); }
	sysopen(FH, "$cfg{memberdir}/$user_data{uid}.msg", O_WRONLY | O_TRUNC);
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	foreach (@{$messages})
	{
		my ($from, $subject, $date, $message, $mid, $unread) =
		    split (/\|/, $_);

		if ($id != $mid) { print FH "$_\n"; }
	}
	close(FH);

	print $query->redirect(
		-location => $cfg{pageurl} . '/instant_messenger.' . $cfg{ext});
}

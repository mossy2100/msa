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
# $Id: login.cgi,v 1.19 2004/02/17 12:20:38 d3m1g0d Exp $
# =====================================================================

# Load necessary modules.
use strict;
use lib '.';
use yawps;

# Assign global variables.
use vars qw(
    $query
    $op $username $password $remember $confirm
    %user_data
    %user_action
    );

# Create a new CGI object.
$query = new CGI;

# Get the input.
$op = $query->param('op') || '';
$username = $query->param('username');
$password = $query->param('password');
$remember = $query->param('remember');
$confirm  = $query->param('confirm') || '';

# Get user profile.
%user_data = authenticate();

# Define possible user actions.
%user_action = (
	login2    => \&login2,
	login3    => \&login3,
	logout    => \&logout,
	logout2   => \&logout2,
	reminder  => \&reminder,
	reminder2 => \&reminder2,
	reminder3 => \&reminder3
    );

# Depending on user action, decide what to do.
if ($user_action{$op}) { $user_action{$op}->(); }
else { login(); }

# ---------------------------------------------------------------------
# Display the login page.
# ---------------------------------------------------------------------
sub login
{
	my $failed = shift || 0;
	my $retry_msg =
	    ($failed
	    ? qq(<tr height="30"><td colspan="2">$msg{login_failed}</td></tr>)
	    : '');

	# Check if user is already logged in.
	if ($user_data{uid} ne $usr{anonuser})
	{
		user_error($err{bad_input}, $user_data{theme});
	}

	print_header();
	print_html($user_data{theme}, $nav{login});

	print <<HTML;
<form method="post" action="$cfg{pageurl}/login.$cfg{ext}">
<table border="0" cellspacing="1">
$retry_msg
<tr>
<td>$msg{usernameC}</td>
<td><input type="text" name="username" size="10" maxlength="50"></td>
</tr>
<tr>
<td>$msg{passwordC}</td>
<td><input type="password" name="password" size="10" maxlength="50"></td>
</tr>
<tr>
<td colspan="2"><input type="checkbox" name="remember">&nbsp;$msg{remember_me}</td>
</tr>
<tr>
<td colspan="2"><input type="hidden" name="op" value="login2"><input type="submit" value="$btn{login}"></td>
</tr>
<tr>
<td colspan="2"><br><a href="$cfg{pageurl}/login.$cfg{ext}?op=reminder">$nav{forgot_pass}</a></td>
</tr>
</table>
</form>
HTML

	print_html($user_data{theme}, $nav{login}, 1);
}

# ---------------------------------------------------------------------
# Log on the user.
# ---------------------------------------------------------------------
sub login2
{

	# Data integrity check.
	if ($username =~ /^([\w.]+)$/) { $username = $1; }
	else { user_error($err{bad_input}, $user_data{theme}); }

	# Get user profile.
	my $user_profile = file2array("$cfg{memberdir}/$username.dat", 1);

	if ($user_profile && $password)
	{

		# Encrypt the password.
		my $encrypted_password = crypt($password, substr($username, 0, 2));
		if ($user_profile->[0] ne $encrypted_password)
		{
			user_error($err{wrong_passwd}, $user_data{theme});
		}

		# Check if user is approved.
		if ($cfg{enable_approvals} && !$user_profile->[14] &&
			$user_profile->[7] ne $usr{admin})
		{
			user_error($err{not_approved}, $user_data{theme});
		}

		# Remove forgot password confirmation file (see functions reminder2, reminder3).
		if (-r "$cfg{memberdir}/$username.cfm")
		{
			unlink("$cfg{memberdir}/$username.cfm");
		}

		# Check if user session should be stored in cookie.
		my $expire = $remember ? $cfg{cookie_expire} : 0;

		# Set the cookie.
		my $cookie_username = $query->cookie(
			-name    => 'yawps_uid',
			-value   => $username,
			-path    => '/',
			-expires => $expire
		    );
		my $cookie_password = $query->cookie(
			-name    => 'yawps_pwd',
			-value   => $encrypted_password,
			-path    => '/',
			-expires => $expire
		    );

		# Redirect to the welcome page.
		print $query->redirect(
			-location => $cfg{pageurl} . '/login.' . $cfg{ext} . '?op=login3',
			-cookie   => [$cookie_username, $cookie_password]
		    );
	}
	else { login(1); }
}

# ---------------------------------------------------------------------
# Diplay user's personal page.
# ---------------------------------------------------------------------
sub login3
{

	# Check if user is logged in.
	if ($user_data{uid} eq $usr{anonuser})
	{
		user_error($err{bad_input}, $user_data{theme});
	}

	# Get user profile.
	my $user_profile = file2array("$cfg{memberdir}/$user_data{uid}.dat", 1);
	if (!$user_profile) {
		user_error($err{user_no_exist}, $user_data{theme});
	}

	# Protect email address.
	my ($email_p, $email_d) = split ("\@", $user_profile->[2]);
	my $protected_email = <<"JS";
<script language="javascript"><!--
document.write("<a href=" + "mail" + "to:" + "$email_p" + "@" + "$email_d" + ">" + "$email_p" + " at " + "$email_d" + "</a>")
//--></script>
JS

	print_header();
	print_html($user_data{theme}, $nav{edit_box});

	print <<HTML;
<p class="texttitle">$user_profile->[1], $msg{personal_page}</p>
<b><u>$msg{your_infoC}</u></b><br>
<table>
<td valign="top">
<table>
<tr>
<td><b>$msg{emailC}</b></td>
<td>$protected_email</td>
</tr>
<tr>
<td><b>$msg{websiteC}</b></td>
<td><a href="$user_profile->[4]" target="_blank">$user_profile->[3]</a></td>
</tr>
<tr>
<td><b>$msg{icqC}</b></td>
<td>$user_profile->[8]</td>
</tr>
<tr>
<td><b>$msg{forum_postsC}</b></td>
<td>$user_profile->[6]</td>
</tr>
<tr>
<td><b>$msg{articlesC}</b></td>
<td>$user_profile->[11]</td>
</tr>
<tr>
<td><b>$msg{commentsC}</b></td>
<td>$user_profile->[12]</td>
</tr>
</table>
</td>
HTML
	if ($user_profile->[9])
	{
		print "<td><img src=\"$user_profile->[9]\" alt></td>\n";
	}

	print <<HTML;
</tr>
</table>
<br>
<b><u>$msg{navigationC}</u></b><br>
<table>
<tr>
<td><a href="$cfg{pageurl}/user.$cfg{ext}?op=edit_profile;username=$user_data{uid}">$nav{edit_profile}</a><br>
<a href="$cfg{pageurl}/login.$cfg{ext}?op=logout">$nav{logout}</a><br></td>
</tr>
</table>
HTML

	print_html($user_data{theme}, $nav{edit_box}, 1);
}

# ---------------------------------------------------------------------
# Log off the user.
# ---------------------------------------------------------------------
sub logout
{

	# Empty cookie values.
	my $cookie_username = $query->cookie(
		-name    => 'yawps_uid',
		-value   => '',
		-path    => '/',
		-expires => 'now'
	    );
	my $cookie_password = $query->cookie(
		-name    => 'yawps_pwd',
		-value   => '',
		-path    => '/',
		-expires => 'now'
	    );

	# Redirect to the logout page.
	print $query->redirect(
		-location => $cfg{pageurl} . '/login.' . $cfg{ext} . '?op=logout2',
		-cookie   => [$cookie_username, $cookie_password]
	    );
}

# ---------------------------------------------------------------------
# Display logout page.
# ---------------------------------------------------------------------
sub logout2
{

	# Check if user is logged in.
	if ($user_data{uid} ne $usr{anonuser})
	{
		user_error($err{bad_input}, $user_data{theme});
	}

	# Print the logout page.
	print_header();
	print_html($user_data{theme}, $nav{logout});

	print
	    qq($inf{logged_out}<br>\n<a href="$cfg{pageurl}/index.$cfg{ext}">$nav{click_back}</a>);

	print_html($user_data{theme}, $nav{logout}, 1);
}

# ---------------------------------------------------------------------
# Display a formular, where user can reset his password.
# ---------------------------------------------------------------------
sub reminder
{

	# Check if user is already logged in.
	if ($user_data{uid} ne $usr{anonuser})
	{
		user_error($err{bad_input}, $user_data{theme});
	}

	print_header();
	print_html($user_data{theme}, $nav{reset_pass});

	print <<HTML;
<form method="post" action="$cfg{pageurl}/login.$cfg{ext}">
<table border="0" cellspacing="1">
<tr>
<td>$msg{usernameC} <input type="text" name="username"><input type="submit" value="$btn{send}">
<input type="hidden" name="op" value="reminder2"></td>
</tr>
</table>
</form>
HTML

	print_html($user_data{theme}, $nav{reset_pass}, 1);
}

# ---------------------------------------------------------------------
# Send user confirmation email for resetting password.
# ---------------------------------------------------------------------
sub reminder2
{

	# Read user profile.
	my $user_profile = file2array("$cfg{memberdir}/$username.dat", 1);

	# Generate a confirm code.
	my $confirm = '';
	rand(time ^ $$);
	my @seed = ('a' .. 'z', '0' .. '9');
	for (my $i = 0; $i < 8; $i++) {
		$confirm .= $seed[int(rand($#seed + 1))];
	}

	my $confirm_link =
	    "$cfg{pageurl}/login.cgi?op=reminder3&confirm=$confirm&username=$username";

	# Create file with confirmation code.
	if ($username =~ /^([\w.]+)$/) { $username = $1; }
	else { user_error($err{bad_input}, $user_data{theme}); }
	sysopen(FH, "$cfg{memberdir}/$username.cfm", O_WRONLY | O_TRUNC | O_CREAT)
	    or
	    user_error("$err{not_writable} $cfg{memberdir}/$username.dat. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	print FH "$confirm\n";
	close FH;

	# Generate info email.
	my $subject =
	    "$cfg{pagename} - $msg{confirm_pass_change} $user_profile->[1]";
	my $message = <<EOT;
$inf{hi_you_or} $ENV{REMOTE_ADDR} $inf{requested_that_user} $user_profile->[1] $inf{receive_new_pass} $inf{to_confirm_visit}

<a href="$confirm_link">$msg{confirm_pass_change} $user_profile->[1]</a>
$inf{or}
$confirm_link

$inf{change_required_msg}
EOT

	# Send the email to recipient.
	send_email($cfg{webmaster_email}, $user_profile->[2], $subject, $message);

	print_header();
	print_html($user_data{theme}, $nav{forgot_pass});

	print "$inf{confirmation_sent} <b>$user_profile->[2]</b>";

	print_html($user_data{theme}, $nav{forgot_pass}, 1);
}

# ---------------------------------------------------------------------
# Reset user password.
# ---------------------------------------------------------------------
sub reminder3
{
	if ($username eq '') { user_error($err{enter_name}, $user_data{theme}); }

	# Get user data.
	my $user_profile = file2array("$cfg{memberdir}/$username.dat",  1);
	my $real_confirm = file2scalar("$cfg{memberdir}/$username.cfm", 1);
	if ($confirm ne $real_confirm)
	{
		user_error($err{bad_confirm_code}, $user_data{theme});
	}

	# Generate a password.
	my $password;
	rand(time ^ $$);
	my @seed = ('a' .. 'k', 'm' .. 'n', 'p' .. 'z', '2' .. '9');

	for (my $i = 0; $i < 8; $i++)
	{
		$password .= $seed[int(rand($#seed + 1))];
	}
	my $enc_password = crypt($password, substr($username, 0, 2));

	# Update user database.
	if ($username =~ /^([\w.]+)$/) { $username = $1; }
	else { user_error($err{bad_input}, $user_data{theme}); }
	sysopen(FH, "$cfg{memberdir}/$username.dat", O_WRONLY | O_TRUNC)
	    or
	    user_error("$err{not_writable} $cfg{memberdir}/$username.dat. ($!)",
		$user_data{theme});
	flock(FH, LOCK_EX) if $cfg{use_flock};
	print FH "$enc_password\n";
	foreach (@{$user_profile}) { print FH "$_\n"; }
	close(FH);

	# Generate info email.
	my $subject =
	    $cfg{pagename} . " - " . $msg{password_forC} . $user_profile->[1];
	my $message = <<EOT;
$inf{hi_you_or} $ENV{REMOTE_ADDR} $inf{requested_that_user} $username $inf{receive_new_pass} $inf{user_pass_are}

$msg{usernameC} $username
$msg{passwordC} $password

$msg{statusC} $user_profile->[7]

$inf{change_pass}
EOT

	# Send the email to recipient.
	send_email($cfg{webmaster_email}, $user_profile->[2], $subject, $message);

	# Print info page.
	print_header();
	print_html($user_data{theme}, $nav{reset_pass});

	print "$inf{info_sent} <b>$user_profile->[2]</b>";

	print_html($user_data{theme}, $nav{reset_pass}, 1);
}

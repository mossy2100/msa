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
# $Id: user.cgi,v 1.34 2004/04/13 06:56:53 d3m1g0d Exp $
# =====================================================================

# Load necessary modules.
use strict;
use lib '.';
use yawps;

# Assign global variables.
use vars qw(
    $query
    $op $username $password1 $password2 $nick $email $website $website_url $signature $forum_posts
    $sec_level $icq $member_pic $member_pic_personal $member_pic_personal_check $joined
    $topic_posts $comments $theme $modify $delete
    %user_data
    %user_action
    );

# Create a new CGI object.
$query = new CGI;

# Get the input.
$op = $query->param('op') || '';
$username                  = $query->param('username');
$password1                 = $query->param('password1');
$password2                 = $query->param('password2');
$nick                      = $query->param('nick');
$email                     = $query->param('email');
$website                   = $query->param('website');
$website_url               = $query->param('website_url');
$signature                 = $query->param('signature');
$forum_posts               = $query->param('forum_posts');
$sec_level                 = $query->param('sec_level');
$icq                       = $query->param('icq');
$member_pic                = $query->param('member_pic');
$member_pic_personal       = $query->param('member_pic_personal');
$member_pic_personal_check = $query->param('member_pic_personal_check');
$joined                    = $query->param('joined') || '';
$topic_posts               = $query->param('topic_posts');
$comments                  = $query->param('comments');
$theme                     = $query->param('theme');
$modify                    = $query->param('modify') || '';
$delete                    = $query->param('delete') || '';

# Get user profile.
%user_data = authenticate();

# Define possible user actions.
%user_action = (
	edit_profile  => \&edit_profile,
	edit_profile2 => \&edit_profile2
    );

# Depending on user action, decide what to do.
if ($user_action{$op}) { $user_action{$op}->(); }
else { view_profile(); }

# ---------------------------------------------------------------------
# Display user's profile.
# ---------------------------------------------------------------------
sub view_profile
{

	# Check if user is logged in.
	if ($user_data{uid} eq $usr{anonuser})
	{
		user_error($err{bad_input}, $user_data{theme});
	}

	if ($username =~ /\//)
	{
		user_error($err{bad_username}, $user_data{theme});
	}
	if ($username =~ /\\/)
	{
		user_error($err{bad_username}, $user_data{theme});
	}

	# Get current user profile.
	my $user_profile = file2array("$cfg{memberdir}/$username.dat", 1);

	# User picture.
	my $member_pic;
	if (!$user_profile->[9]) { $user_profile->[9] = '_nopic.gif'; }
	if ($user_profile->[9] =~ /http:\/\//)
	{
		my ($width, $height);
		if ($cfg{picture_width} != 0)
		{
			$width = "width=\"$cfg{picture_width}\"";
		}
		else { $width = ""; }

		if ($cfg{picture_height} != 0)
		{
			$height = "height=\"$cfg{picture_height}\"";
		}
		else { $height = ""; }

		$member_pic =
		    qq(<img src="$user_profile->[9]" $width $height border="0" alt=""></a>);
	}
	else
	{
		$member_pic =
		    qq(<img src="$cfg{imagesurl}/avatars/$user_profile->[9]" border="0" alt=""></a>);
	}

	# Get member ranks.
	my %ranks = load_ranks();

	# Display member ranking.
	my $ranking =
	    $user_profile->[6] + $user_profile->[11] + $user_profile->[12];

	my $member_info = '';
	foreach (sort keys %ranks)
	{
		if ($ranking > $_) { $member_info = $ranks{$_}; }
	}

	my $status = $user_profile->[7] || '';

	# Protect email address.
	my ($email_p, $email_d) = split ("\@", $user_profile->[2]);
	my $protected_email = <<"JS";
<script language="javascript"><!--
document.write("<a href=" + "mail" + "to:" + "$email_p" + "@" + "$email_d" + ">" + "$email_p" + " at " + "$email_d" + "</a>")
//--></script>
JS

	# Format date.
	my $formatted_date = format_date($user_profile->[10]);

	print_header();
	print_html($user_data{theme}, $nav{view_profile});

	print <<HTML;
<table border="0" width="100%" cellspacing="1">
<tr>
<td><div class="texttitle">$user_profile->[1]</div> 
HTML

	# Print link to edit profile and link to send IMs.
	if ($username eq $user_data{uid} || $user_data{sec_level} eq $usr{admin})
	{
		print
		    qq([<a href="$cfg{pageurl}/user.$cfg{ext}?op=edit_profile;username=$username">$nav{edit_profile}</a>]);
	}
	if ($username ne $user_data{uid})
	{
		print
		    qq( [<a href="$cfg{pageurl}/instant_messenger.$cfg{ext}?op=send_im;to=$username">$nav{send_message}</a>]);
	}

	print <<HTML;
</td>
</tr>
<tr>
<td>
<table border="0">
<tr>
<td><b>$msg{nameC}</b></td>
<td>$user_profile->[1]</td>
</tr>
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
<tr>
<td><b>$msg{rankC}</b></td>
<td>$member_info</td>
</tr>
<tr>
<td><b>$msg{statusC}</b></td>
<td>$status</td>
</tr>
<tr>
<td><b>$msg{member_sinceC}</b></td>
<td>$formatted_date</td>
</tr>
<tr>
<td valign="top"><b>$msg{pictureC}</b></td>
<td>$member_pic</td>
</tr>
</table>
</td>
</tr>
</table>
HTML

	print_html($user_data{theme}, $nav{send_im}, 1);
}

# ---------------------------------------------------------------------
# Display formular to edit user's profile.
# ---------------------------------------------------------------------
sub edit_profile
{

	# Check if user is logged in.
	if ($user_data{uid} eq $usr{anonuser})
	{
		user_error($err{bad_input}, $user_data{theme});
	}

	if ($username =~ /\//)
	{
		user_error($err{bad_username}, $user_data{theme});
	}
	if ($username =~ /\\/)
	{
		user_error($err{bad_username}, $user_data{theme});
	}

	# Get current user profile.
	my $user_profile = file2array("$cfg{memberdir}/$username.dat", 1);

	# Check if user has permissions to edit other user's profile.
	if ($user_data{uid} ne $username && $user_data{sec_level} ne $usr{admin})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	my $signature = $user_profile->[5];
	$signature =~ s/\&\&/\n/g;

	print_header();
	print_html($user_data{theme}, $nav{edit_profile});

	print <<HTML;
<table border="0" cellspacing="1">
<tr>
<td><form action="$cfg{pageurl}/user.$cfg{ext}" method="post" name="creator">
<table border="0">
<tr>
<td><b>$msg{usernameC}</b></td>
<td><input type="hidden" name="username" value="$username"><b>$username</b></td>
</tr>
<tr>
<td><b>$msg{passwordC}</b></td>
<td><input type="password" name="password1" size="20" value="$user_profile->[0]">*</td>
</tr>
<tr>
<td><b>$msg{passwordC}</b></td>
<td><input type="password" name="password2" size="20" value="$user_profile->[0]">*</td>
</tr>
<tr>
<td><b>$msg{nameC}</b></td>
<td><input type="text" name="nick" size="40" value="$user_profile->[1]">*</td>
</tr>
<tr>
<td><b>$msg{emailC}</b></td>
<td><input type="text" name="email" size="40" value="$user_profile->[2]">*</td>
</tr>
<tr>
<td><b>$msg{websiteC}</b></td>
<td><input type="text" name="website" size="40" value="$user_profile->[3]"></td>
</tr>
<tr>
<td><b>$msg{urlC}</b></td>
<td><input type="text" name="website_url" size="40" value="$user_profile->[4]"></td>
</tr>
<tr>
<td><b>$msg{icqC}</b></td>
<td><input type="text" name="icq" size="40" value="$user_profile->[8]"></td>
</tr>
<tr>
<td valign="top"><b>$msg{signatureC}</b></td>
<td><textarea name="signature" rows="4" cols="35" wrap="virtual">$signature</textarea></td>
</tr>
<tr>
<td valign="top"><b>$msg{themeC}</b></td>
<td><select name="theme">
HTML

	# Get list of installed themes.
	my $themes = dir2array($cfg{themesdir});

	foreach (sort @{$themes})
	{
		if ($_ eq '.' || $_ eq '..') { next; }

		my ($theme_name, $extension) = split (/\./, $_);
		if (!$extension)
		{
			if ($user_profile->[13] eq $theme_name)
			{
				print
				    qq(<option value="$theme_name" selected>$theme_name</option>\n);
			}
			else
			{
				print qq(<option value="$theme_name">$theme_name</option>\n);
			}
		}
	}

	print <<HTML;
</select>
</td>
</tr>
HTML

	# Get available avatars.
	my $avatars = dir2array("$cfg{imagesdir}/avatars");
	my ($images, $checked, $pic_name, $pic, $http) = ('', '', '', '', '');

	if ($user_profile->[9] eq '') { $user_profile->[9] = "_nopic.gif"; }
	if ($user_profile->[9] =~ /$cfg{imagesurl}\/avatars\/([\w.]+)/)
	{
		$user_profile->[9] = $1;
	}

	foreach (sort @{$avatars})
	{
		my ($name, $extension) = split (/\./, $_);
		$extension = lc($extension);

		if ($_ eq $user_profile->[0]
			|| ($user_profile->[9] =~ m/http:\/\// && $_ eq ''))
		{
			$checked = 'selected';
		}
		if ($extension =~ /gif/i
			|| $extension =~ /jpg/i
			|| $extension =~ /jpeg/i
			|| $extension =~ /png/i)
		{
			if ($_ eq "_nopic.gif")
			{
				$pic  = "_nopic.gif";
				$name = $msg{no_picture};
			}
			$images .= qq(<option value="$_"$checked>$name</option>\n);
		}
	}
	if ($user_profile->[9] =~ m/http:\/\//)
	{
		$pic     = $user_profile->[9];
		$checked = " checked";
		$http    = $user_profile->[9];
	}
	else
	{
		$pic  = $cfg{imagesurl} . "/avatars/" . $user_profile->[9];
		$http = "http://";
	}

	print <<HTML;
<tr>
<td valign="top"><b>$msg{pictureC}</b></td>
<td valign="top">
<table>
<tr>
<td>$msg{use_standard_picC}</td>
</tr>
<tr>
<td><script language="javascript" type="text/javascript">
<!--
function showImage() {
document.images.avatars.src="$cfg{imagesurl}/avatars/"+document.creator.member_pic.options[document.creator.member_pic.selectedIndex].value;
}
// -->
</script>
<select name="member_pic" onChange="showImage()" size="6">
$images</select>
<img src="$pic" name="avatars" border="0" hspace="15"></td>
</tr>
<tr>
<td>$msg{use_own_pictureC}</td>
</tr>
<tr>
<td><input type="checkbox" name="member_pic_personal_check"$checked>
<input type="text" name="member_pic_personal" size="40" value="$http"><br>
$msg{pic_message}</td>
</tr>
</table>
</td>
</tr>
<tr>
<td valign="top"><b>$msg{subscribe_to}</b></td>
<td><table border="0" cellspacing="1">
<tr>
<td width="33%" align="center"><b>$nav{articles}</b></td>
<td width="33%" align="center"><b>$nav{forums}</b></td>
<td width="33%" align="center"><b>$nav{links}</b></td>
</tr>
<tr>
HTML

	# Print subscription box for articles.
	my $topic_cats = file2array("$cfg{topicsdir}/cats.dat", 1);
	print
	    qq(<td width="33%" align="center"><select name="topics_subscr" size="5" multiple>);
	foreach (@{$topic_cats})
	{
		my @item = split (/\|/, $_);
		my $topic_subscribed = file2array("$cfg{topicsdir}/$item[1].mail", 1);
		my $selected =
		    (grep { $_ eq $user_data{email} } @{$topic_subscribed})
		    ? ' selected'
		    : '';
		print qq(<option value="$item[1]"$selected>$item[0]</option>\n);
	}
	print '</select></td><td width="33%" align="center">';

	# Print subscription box for forums.
	my $forum_cats = file2array("$cfg{boardsdir}/cats.txt", 1);
	print qq(<select name="boards_subscr" size="5" multiple>);
	foreach (@{$forum_cats})
	{
		my $cat_info         = file2array("$cfg{boardsdir}/$_.cat",  1);
		my $board_subscribed = file2array("$cfg{boardsdir}/$_.mail", 1);
		my $selected         =
		    (grep { $_ eq $user_data{email} } @{$board_subscribed})
		    ? ' selected'
		    : '';
		print qq(<option value="$_"$selected>$cat_info->[0]</option>\n);
	}
	print '</select></td><td width="33%" align="center">';

	# Print subscription box for links.
	my $links_cats = file2array("$cfg{linksdir}/linkcats.dat", 1);
	print qq(<select name="links_subscr" size="5" multiple>);
	foreach (@{$links_cats})
	{
		my @item = split (/\|/, $_);
		my $link_subscribed = file2array("$cfg{linksdir}/$item[1].mail", 1);
		my $selected =
		    (grep { $_ eq $user_data{email} } @{$link_subscribed})
		    ? ' selected'
		    : '';
		print qq(<option value="$item[1]"$selected>$item[0]</option>\n);
	}
	print '</select></td>';

	print <<HTML;
</tr>
</table></td>
</tr>
HTML

	# Print actions for admins.
	if ($user_data{sec_level} eq $usr{admin})
	{
		my $pos       = '';
		my @userlevel = ($usr{admin}, $usr{mod}, $usr{user});

		if (!$user_profile->[7])
		{
			foreach (@userlevel[0 .. 1])
			{
				$pos =
				    ($user_profile->[7] eq $_)
				    ? qq($pos<option value="$_" selected>$_</option>\n)
				    : qq($pos<option value="$_">$_</option>\n);
			}
			$pos = qq($pos<option value="" selected>$userlevel[2]</option>\n);
		}
		else
		{
			foreach (@userlevel[0 .. 1])
			{
				$pos =
				    ($user_profile->[7] eq $_)
				    ? qq($pos<option value="$_" selected>$_</option>\n)
				    : qq($pos<option value="$_">$_</option>\n);
			}
			$pos = qq($pos<option value="">$userlevel[2]</option>\n);
		}

		print <<HTML;
<tr>
<td><b>$msg{forum_postsC}</b></td>
<td><input type="text" name="forum_posts" size="4" value="$user_profile->[6]"></td>
</tr>
<tr>
<td><b>$msg{articlesC}</b></td>
<td><input type="text" name="topic_posts" size="4" value="$user_profile->[11]"></td>
</tr>
<tr>
<td><b>$msg{commentsC}</b></td>
<td><input type="text" name="comments" size="4" value="$user_profile->[12]"></td>
</tr>
<tr>
<td><b>$msg{rankC}</b></td>
<td><select name="sec_level">
$pos</select></td>
</tr>
<tr>
<td colspan="2">* $msg{required_fields}</td>
</tr>
<tr>
<td colspan="2"><input type="hidden" name="joined" value="$user_profile->[10]">
<input type="hidden" name="forum_posts" value="$user_profile->[6]">
<input type="hidden" name="joined" value="$user_profile->[10]">
<input type="hidden" name="topic_posts" value="$user_profile->[11]">
<input type="hidden" name="comments" value="$user_profile->[12]">
HTML
	}
	else
	{
		print <<HTML;
<tr>
<td colspan="2">* $msg{required_fields}</td>
</tr>
<tr>
<td colspan="2">
HTML
	}

	print <<HTML;
<input type="hidden" name="op" value="edit_profile2">
<input type="submit" name="modify" value="$btn{edit_profile}">
<input type="submit" name="delete" value="$btn{delete_profile}">
</td>
</tr>
</table>
</form>
</td>
</tr>
</table>
HTML

	print_html($user_data{theme}, $nav{edit_profile}, 1);
}

# ---------------------------------------------------------------------
# Update user's profile.
# ---------------------------------------------------------------------
sub edit_profile2
{

	# Check if user is logged in.
	if ($user_data{uid} eq $usr{anonuser})
	{
		user_error($err{bad_input}, $user_data{theme});
	}

	if ($username =~ /\//)
	{
		user_error($err{bad_username}, $user_data{theme});
	}
	if ($username =~ /\\/)
	{
		user_error($err{bad_username}, $user_data{theme});
	}

	if (!$username) { user_error($err{enter_name}, $user_data{theme}); }
	if ($username !~ /^[0-9A-Za-z#%+,-\.:=?@^_]+$/
		|| length($username) < 4
		|| length($username) > 20
		|| $username eq '|'
		|| $username =~ " "
		|| $username eq $usr{admin}
		|| $username eq $usr{mod}
		|| $username eq $usr{user}
		|| $username eq $usr{anonuser})
	{
		user_error($err{bad_username}, $user_data{theme});
	}
	if ($user_data{uid} ne $username && $user_data{sec_level} ne $usr{admin})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}
	if ($user_data{uid} ne 'admin' && $username eq 'admin')
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	# Get current user profile.
	$username =
	    ($username =~ /^([\w.]+)$/) ? $1 : fatal_error($err{bad_input});
	my $user_profile = file2array("$cfg{memberdir}/$username.dat", 1);

	# Update user profile.
	if ($modify ne '')
	{

		# Password validation.
		if ($password1 ne $password2)
		{
			user_error($err{verify_pass}, $user_data{theme});
		}
		if (!$password1) { user_error($err{enter_pass}, $user_data{theme}); }

		my $password;
		if ($password1 eq $user_profile->[0]) { $password = $password1; }
		else { $password = crypt($password1, substr($username, 0, 2)); }

		# Nickname validation.
		if ($nick !~ /^[0-9A-Za-z#%+,-\.:=?@^_]+$/
			|| $nick eq '|'
			|| $nick =~ " "
			|| $nick eq $usr{admin}
			|| $nick eq $usr{mod}
			|| $nick eq $usr{user}
			|| $nick eq $usr{anonuser})
		{
			user_error($err{bad_input}, $user_data{theme});
		}
		if (!$nick) { user_error($err{enter_nick}, $user_data{theme}); }

		# Email validation.
		if (!$email) { user_error($err{enter_email}, $user_data{theme}); }
		if ($email !~ /^[0-9A-Za-z@\._\-]+$/
			|| $email =~ /(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)|(\.$)/)
		{
			user_error($err{bad_input}, $user_data{theme});
		}

		# Picture validation.
		if ($member_pic_personal_check &&
			($member_pic_personal =~ m/\.gif\Z/i
				|| $member_pic_personal =~ m/\.jpg\Z/i
				|| $member_pic_personal =~ m/\.jpeg\Z/i
				|| $member_pic_personal =~ m/\.png\Z/i))
		{
			$member_pic = $member_pic_personal;
		}
		elsif (!$member_pic) { $member_pic = $user_profile->[9]; }
		else { $member_pic = "$cfg{imagesurl}/avatars/$member_pic"; }

		if ($member_pic &&
			$member_pic !~ m^\A[0-9a-zA-Z_\.\#\%\-\:\+\?\$\&\~\.\,\@/]+\Z^)
		{
			user_error($err{bad_input}, $user_data{theme});
		}

		# Signature.
		if (!$signature) { $signature = $msg{default_sig}; }
		$signature =~ s/\n/\&\&/g;
		$signature =~ s/\r//g;

		# Check if user has permissions to modify security level and post count.
		if ($user_data{sec_level} ne $usr{admin})
		{
			$sec_level   = $user_profile->[7];
			$joined      = $user_profile->[10];
			$forum_posts = $user_profile->[6];
			$topic_posts = $user_profile->[11];
			$comments    = $user_profile->[12];
		}

		if ($member_pic eq '_nopic.gif') { $member_pic = ''; }
		if (!$forum_posts) { $forum_posts = 0; }
		if (!$topic_posts) { $topic_posts = 0; }
		if (!$comments)    { $comments    = 0; }

		# Write profile.
		sysopen(FH, "$cfg{memberdir}/$username.dat", O_WRONLY | O_TRUNC)
		    or user_error(
			"$err{not_writable} $cfg{memberdir}/$username.dat. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		print FH join (
			"\n",         $password,    $nick,       $email,
			$website,     $website_url, $signature,  $forum_posts,
			$sec_level,   $icq,         $member_pic, $joined,
			$topic_posts, $comments,    $theme
		    ),
		    "\n";
		if ($user_profile->[14]) { print FH "$user_profile->[14]\n"; }
		close(FH);

		# Update article subscriptions.
		my $tcats = file2array("$cfg{topicsdir}/cats.dat", 1);
		foreach (@{$tcats})
		{
			my (undef, $link) = split (/\|/, $_);
			my $file = ($link =~ /^([\w.]+)$/) ? $1 : next;
			if (grep { $_ eq $file } $query->param('topics_subscr'))
			{
				update_subscriptions("$cfg{topicsdir}/$file.mail", $email, 1);
			}
			if (!(grep { $_ eq $file } $query->param('topics_subscr')) &&
				-w "$cfg{topicsdir}/$file.mail")
			{
				update_subscriptions("$cfg{topicsdir}/$file.mail", $email, 0);
			}
		}

		# Update forums subscriptions.
		my $fcats = file2array("$cfg{boardsdir}/cats.txt", 1);
		foreach (@{$fcats})
		{
			my $file = ($_ =~ /^([\w.]+)$/) ? $1 : next;
			if (grep { $_ eq $file } $query->param('boards_subscr'))
			{
				update_subscriptions("$cfg{boardsdir}/$file.mail", $email, 1);
			}
			if (!(grep { $_ eq $file } $query->param('boards_subscr')) &&
				-w "$cfg{boardsdir}/$file.mail")
			{
				update_subscriptions("$cfg{boardsdir}/$file.mail", $email, 0);
			}
		}

		# Update links subscriptions.
		my $lcats = file2array("$cfg{linksdir}/linkcats.dat", 1);
		foreach (@{$lcats})
		{
			my (undef, $link) = split (/\|/, $_);
			my $file = ($link =~ /^([\w.]+)$/) ? $1 : next;
			if (grep { $_ eq $file } $query->param('links_subscr'))
			{
				update_subscriptions("$cfg{linksdir}/$file.mail", $email, 1);
			}
			if (!(grep { $_ eq $file } $query->param('links_subscr')) &&
				-w "$cfg{linksdir}/$file.mail")
			{
				update_subscriptions("$cfg{linksdir}/$file.mail", $email, 0);
			}
		}

		if ($user_data{uid} eq $username)
		{

			# Set new cookie.
			my $cookie_username = $query->cookie(
				-name    => 'yawps_uid',
				-value   => $user_data{uid},
				-path    => '/',
				-expires => $cfg{cookie_expire}
			    );
			my $cookie_password = $query->cookie(
				-name    => 'yawps_pwd',
				-value   => $password,
				-path    => '/',
				-expires => $cfg{cookie_expire}
			    );

			# Redirect to the welcome page.
			print $query->redirect(
				-location => $cfg{pageurl} . '/login.' . $cfg{ext} .
				'?op=login3',
				-cookie => [$cookie_username, $cookie_password]
			    );
		}
		else
		{
			print $query->redirect(
				-location => $cfg{pageurl} . '/user.' . $cfg{ext} .
				'?op=view_profile;username=' . $username);
		}
	}

	# Delete user.
	elsif ($delete ne '')
	{
		unlink("$cfg{memberdir}/$username.dat");
		unlink("$cfg{memberdir}/$username.msg");
		unlink("$cfg{memberdir}/$username.log");

		my $members = file2array("$cfg{memberdir}/memberlist.dat", 1);

		# Update memberlist.
		sysopen(FH, "$cfg{memberdir}/memberlist.dat", O_WRONLY | O_TRUNC);
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		foreach (@{$members})
		{
			if ($_ ne $username) { print FH "$_\n"; }
		}
		close(FH);

		if ($user_data{uid} eq $username)
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
				-location => $cfg{pageurl} . '/login.' . $cfg{ext} .
				'?op=logout2',
				-cookie => [$cookie_username, $cookie_password]
			    );

		}
		else
		{
			print $query->redirect(
				-location => $cfg{pageurl} . '/index.' . $cfg{ext});
		}
	}
	else { user_error($err{bad_input}, $user_data{theme}); }
}

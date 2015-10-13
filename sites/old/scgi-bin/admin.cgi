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
# $Id: admin.cgi,v 1.45 2004/04/14 10:25:58 d3m1g0d Exp $
# =====================================================================

# Load necessary modules.
use strict;
use lib '.';
use yawps;

# Assign global variables.
use vars qw(
    $query
    $op $start
    $title $message
    $cats $id $name $access
    $descr @mod $modify $delete
    $boards $first $second $number
    $censor
    $question $answer
    $poster_name $poster_email
    $view_num $new_cat $old_cat
    $url
    $pos $status
    %user_data
    %user_action
    );

# Create a new CGI object.
$query = new CGI;

# Get the input.
$op    = $query->param('op')    || '';
$start = $query->param('start') || 0;

$title   = $query->param('title');
$message = $query->param('message');

$cats = $query->param('cats') || '';
$id   = $query->param('id');
$name = $query->param('name');
$access = $query->param('access');

$descr  = $query->param('descr');
@mod    = $query->param('mod');
$modify = $query->param('modify') || '';
$delete = $query->param('delete') || '';

$boards = $query->param('boards');
$first  = $query->param('first');
$second = $query->param('second');
$number = $query->param('number');

$censor = $query->param('censor');

$question = $query->param('question');
$answer   = $query->param('answer');

$poster_name  = $query->param('poster_name');
$poster_email = $query->param('poster_email');

$view_num = $query->param('view_num') || 0;
$new_cat  = $query->param('new_cat');
$old_cat  = $query->param('old_cat');

$url = $query->param('url');

$pos    = $query->param('pos');
$status = $query->param('status') || 0;

# Get user profile.
%user_data = authenticate();

# Define possible user actions.
%user_action = (
	site_config        => \&site_config,
	welcome_msg        => \&welcome_msg,
	welcome_msg2       => \&welcome_msg2,
	ban                => \&ban,
	ban2               => \&ban2,
	set_censor         => \&set_censor,
	set_censor2        => \&set_censor2,
	meta_tags          => \&meta_tags,
	meta_tags2         => \&meta_tags2,
	manage_forum_cats  => \&manage_forum_cats,
	forum_cats_access  => \&forum_cats_access,
	reorder_forum_cats => \&reorder_forum_cats,
	remove_forum_cats  => \&remove_forum_cats,
	create_forum_cats  => \&create_forum_cats,
	manage_boards      => \&manage_boards,
	reorder_boards     => \&reorder_boards,
	reorder_boards2    => \&reorder_boards2,
	modify_board       => \&modify_board,
	create_board       => \&create_board,
	edit_polls         => \&edit_polls,
	create_poll        => \&create_poll,
	edit_poll          => \&edit_poll,
	edit_poll2         => \&edit_poll2,
	edit_poll3         => \&edit_poll3,
	edit_poll4         => \&edit_poll4,
	reset_poll         => \&reset_poll,
	delete_poll        => \&delete_poll,
	verify_news        => \&verify_news,
	verify_news2       => \&verify_news2,
	verify_news3       => \&verify_news3,
	modify_news        => \&modify_news,
	modify_news2       => \&modify_news2,
	modify_news3       => \&modify_news3,
	move_topic         => \&move_topic,
	edit_topic_cats    => \&edit_topic_cats,
	edit_topic_cats2   => \&edit_topic_cats2,
	edit_topic_cats3   => \&edit_topic_cats3,
	edit_link_cats     => \&edit_link_cats,
	edit_link_cats2    => \&edit_link_cats2,
	edit_link_cats3    => \&edit_link_cats3,
	edit_links         => \&edit_links,
	edit_links2        => \&edit_links2,
	edit_links3        => \&edit_links3,
	move_link          => \&move_link,
	user_ranks         => \&user_ranks,
	user_ranks2        => \&user_ranks2,
	view_online        => \&view_online,
	edit_pages         => \&edit_pages,
	edit_pages2        => \&edit_pages2,
	edit_blocks        => \&edit_blocks,
	edit_blocks2       => \&edit_blocks2,
	edit_quotes        => \&edit_quotes,
	add_quote          => \&add_quote,
	delete_quote       => \&delete_quote,
	edit_quote         => \&edit_quote,
	edit_quote2        => \&edit_quote2,
	define_acl         => \&define_acl,
	define_acl2        => \&define_acl2,
	approve_users      => \&approve_users,
	approve_users2     => \&approve_users2,
	enable_modules     => \&enable_modules,
	enable_modules2    => \&enable_modules2
    );

# Check if user has sufficient permissions.
my $req_action = ($op eq '') ? $op = 'admin_index' : $op;
my $access_granted = acl_check($user_data{uid}, $req_action);
if ($access_granted == 0 && $user_data{sec_level} ne $usr{admin})
{
	user_error($err{auth_failure}, $user_data{theme});
}

# Depending on user action, decide what to do.
if ($user_action{$op}) { $user_action{$op}->(); }
else { admin_index(); }

# ---------------------------------------------------------------------
# Display the admin index page.
# ---------------------------------------------------------------------
sub admin_index
{
	my @menu_order = (
		'site_admin',  'forum_admin', 'poll_admin',    'news_admin',
		'links_admin', 'user_admin',  'content_admin', 'module_admin'
	    );
	my %menu = (
		content_admin => ['edit_pages', 'edit_blocks', 'edit_quotes'],
		forum_admin  => ['manage_forum_cats', 'manage_boards'],
		links_admin  => ['edit_link_cats',    'edit_links'],
		module_admin => ['enable_modules'],
		news_admin => ['verify_news', 'modify_news', 'edit_topic_cats'],
		poll_admin => ['edit_polls'],
		site_admin => [
			'site_config', 'welcome_msg', 'define_acl', 'ban',
			'set_censor',  'meta_tags'
		],
		user_admin => ['user_ranks', 'view_online', 'approve_users']
	    );

	# Get list of installed modules.
	my $modules       = dir2array($cfg{modulesdir});
	my $modulechoices = '';
	foreach (sort @{$modules})
	{
		if ($_ eq '.' || $_ eq '..') { next; }

		my ($module_name, $extension) = split (/\./, $_);
		if (!$extension)
		{
			$modulechoices .=
			    qq(<a href="$cfg{modulesurl}/$module_name/admin.$cfg{ext}">Configure '$module_name'</a><br>\n);
		}
	}

	print_header();
	print_html($user_data{theme}, $adm{site_admin});

	print <<HTML;
<table width="100%" class="bg5" border="0" cellspacing="0" cellpadding="0">
<tr>
<td>
<table width="100%" border="0" cellspacing="1" cellpadding="2">
HTML

	for (my $i = 0; $i < @menu_order; $i += 2)
	{
		my $groups = '';
		my $group1 = $menu_order[$i];
		my $group2 = $menu_order[$i + 1];
		$groups .=
		    qq(<td valign="top" width="50%" class="tbl_header"><b>$adm{$group1}</b><br></td>\n);
		if ($i + 1 < @menu_order)
		{
			$groups .=
			    qq(<td valign="top" width="50%" class="tbl_header"><b>$adm{$group2}</b><br></td>\n);
		}
		my $choices1 = '';
		foreach (@{$menu{$group1}})
		{
			$choices1 .=
			    qq(<a href="$cfg{pageurl}/admin.$cfg{ext}?op=$_">$adm{$_}</a><br>\n);
		}

		if ($group1 eq 'module_admin') { $choices1 .= $modulechoices; }
		my $choices2 = '';
		if ($i + 1 < @menu_order)
		{
			foreach (@{$menu{$group2}})
			{
				$choices2 .=
				    qq(<a href="$cfg{pageurl}/admin.$cfg{ext}?op=$_">$adm{$_}</a><br>\n);
			}
		}

		if ($group2 eq 'module_admin') { $choices2 .= $modulechoices; }

		print <<HTML;
<tr class="bg2">
$groups</tr>
<tr class="tbl_row_light">
<td valign="top">
$choices1</td>
<td valign="top">
$choices2</td>
</tr>
HTML
	}

	print <<HTML;
</td>
</tr>
</table></td>
</tr>
</table>
HTML

	print_html($user_data{theme}, $adm{site_admin}, 1);
}

# ---------------------------------------------------------------------
# Show content of yawpsrc.pl.
# ---------------------------------------------------------------------
sub site_config
{
	print_header();
	print_html($user_data{theme}, "$adm{site_admin} >>> $adm{site_config}");
	print <<HTML;
<p>Please modify these settings in yawpsrc.pl!<br><br>
<table width="100%" class="bg5" border="0" cellspacing="0" cellpadding="0">
<tr>
<td>
<table width="100%" border="0" cellspacing="1" cellpadding="2">
<tr class="tbl_header">
<td><b>Variable</b></td>
<td><b>Value</b></td>
HTML

	while (my ($key, $value) = each(%cfg))
	{
		$value = html_escape($value);
		print <<HTML;
<tr>
<td class="tbl_row_light" valign="top"><b>$key</b></td>
<td class="tbl_row_dark" valign="top">$value</td>
</tr>
HTML
	}

	print <<HTML;
</table></td>
</tr>
</table>
HTML
	print_html($user_data{theme}, "$adm{site_admin} >>> $adm{site_config}",
		1);
}

# ---------------------------------------------------------------------
# Formular to edit site welcome message.
# ---------------------------------------------------------------------
sub welcome_msg
{
	my $msg = file2array("$cfg{datadir}/welcomemsg.txt", 1);
	$msg->[0] = html_to_text($msg->[0]);
	$msg->[1] = html_to_text($msg->[1]);

	print_header();
	print_html($user_data{theme}, "$adm{site_admin} >>> $adm{welcome_msg}");
	print <<HTML;
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<table border="0" cellspacing="1" cellpadding="2">
<tr>
<td>Welcome Title:</td>
<td><input type="text" name="title" size="40" maxlength="50" value="$msg->[0]"></td>
</tr>
<tr>
<td valign="top">Welcome Text:</td>
<td><textarea name="message" rows="10" cols="40">$msg->[1]</textarea></td>
</tr>
<tr>
<td colspan="2"><input type="hidden" name="op" value="welcome_msg2"><input type="submit" value="Save">
<input type="reset" value="Reset"></td>
</tr>
</table>
</form>
HTML
	print_html($user_data{theme}, "$adm{site_admin} >>> $adm{welcome_msg}",
		1);
}

# ---------------------------------------------------------------------
# Update site welcome message.
# ---------------------------------------------------------------------
sub welcome_msg2
{
	if (!$title)   { user_error($err{enter_subject}, $user_data{theme}); }
	if (!$message) { user_error($err{enter_text},    $user_data{theme}); }

	# Format input.
	$title   = html_escape($title);
	$message = html_escape($message);

	# Update the message.
	sysopen(FH, "$cfg{datadir}/welcomemsg.txt", O_WRONLY | O_TRUNC | O_CREAT)
	    or user_error("$err{not_writable} $cfg{datadir}/welcomemsg.txt. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	print FH "$title\n";
	print FH "$message\n";
	close(FH);

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=welcome_msg');
}

# ---------------------------------------------------------------------
# Edit user bannings.
# ---------------------------------------------------------------------
sub ban
{

	# Get banned users.
	my $banned = file2array("$cfg{datadir}/ban.txt");

	print_header();
	print_html($user_data{theme}, "$adm{site_admin} >>> $adm{ban}");
	print <<HTML;
<table>
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<tr>
<td><textarea name="name" rows="10" cols="40">@$banned</textarea><br>
One entry per line (you can insert IP addresses, usernames or email adresses).</td>
</tr>
<tr>
<td><input type="submit" value="Edit">
<input type="hidden" name="op" value="ban2"></td>
</tr>
</form>
</table>
HTML
	print_html($user_data{theme}, "$adm{site_admin} >>> $adm{ban}", 1);
}

# ---------------------------------------------------------------------
# Update banlist.
# ---------------------------------------------------------------------
sub ban2
{

	# Update categories.
	$name =~ s/\r//g;
	if ($name)
	{
		sysopen(FH, "$cfg{datadir}/ban.txt", O_WRONLY | O_TRUNC | O_CREAT)
		    or user_error("$err{not_writable} $cfg{datadir}/ban.txt. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		print FH $name;
		close(FH);
	}

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} . '?op=ban');
}

# ---------------------------------------------------------------------
# Edit censor.
# ---------------------------------------------------------------------
sub set_censor
{

	# Get censored words.
	my $censored = file2array("$cfg{datadir}/censor.txt", 1);

	print_header();
	print_html($user_data{theme}, "$adm{forum_admin} >>> $adm{set_censor}");
	print <<HTML;
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<table border="0" width="100%" cellpading="0" cellspacing="0">
<tr>
<td>One word per line in the form: word=w*rd<br>
<textarea cols="20" rows="10" name="censor">
HTML

	# Print the list.
	foreach (@{$censored}) { print "$_\n"; }

	print <<HTML;
</textarea><br>
<input type="hidden" name="op" value="set_censor2">
<input type="submit" value="Save"></td>
</tr>
</table>
</form>
HTML
	print_html($user_data{theme}, "$adm{forum_admin} >>> $adm{set_censor}",
		1);
}

# ---------------------------------------------------------------------
# Update censor.
# ---------------------------------------------------------------------
sub set_censor2
{

	# Update the censorlist.
	sysopen(FH, "$cfg{datadir}/censor.txt", O_WRONLY | O_TRUNC | O_CREAT)
	    or user_error("$err{not_writable} $cfg{datadir}/censor.txt. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	print FH $censor;
	close(FH);

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=set_censor');
}

# ---------------------------------------------------------------------
# Print formular to edit meta tags.
# ---------------------------------------------------------------------
sub meta_tags
{

	# Get meta tags.
	my $metatags = file2array("$cfg{datadir}/meta.txt", 1);
	my $description = $metatags->[0] ? $metatags->[0] : '';
	my $keywords    = $metatags->[1] ? $metatags->[1] : '';
	my $checked = ($metatags->[2] && $metatags->[2] == 1) ? ' checked' : '';

	print_header();
	print_html($user_data{theme}, "$adm{site_admin} >>> $adm{meta_tags}");
	print <<HTML;
<table>
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<tr>
<td>Enter a short site description (100 bytes maximum):<br>
<textarea name="descr" rows="5" cols="40" maxlength="100">$description</textarea></td>
</tr>
<tr>
<td>Enter keywords for search engines (seperate them with commas):<br>
<textarea name="name" rows="5" cols="40">$keywords</textarea></td>
</tr>
<tr>
<td>Use meta tags: <input type="checkbox" name="status"$checked></td>
</tr>
<tr>
<td><input type="submit" value="Edit">
<input type="hidden" name="op" value="meta_tags2"></td>
</tr>
</form>
</table>
HTML
	print_html($user_data{theme}, "$adm{site_admin} >>> $adm{meta_tags}", 1);
}

# ---------------------------------------------------------------------
# Update meta tags.
# ---------------------------------------------------------------------
sub meta_tags2
{

	# Check input.
	chomp($descr);
	chomp($name);
	chomp($status);
	$status = ($status eq "on" || $status == 1) ? ($status = 1) : 0;

	# Update the meta tag db.
	sysopen(FH, "$cfg{datadir}/meta.txt", O_WRONLY | O_TRUNC | O_CREAT)
	    or user_error("$err{not_writable} $cfg{datadir}/meta.txt. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	print FH "$descr\n";
	print FH "$name\n";
	print FH "$status\n";
	close(FH);

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} . '?op=meta_tags');
}

# ---------------------------------------------------------------------
# Manage forum categories.
# ---------------------------------------------------------------------
sub manage_forum_cats
{
	my ($cats_dropdown, $cat_list, $cat_access_list) = ('', '', '');
	my $categories = file2array("$cfg{boardsdir}/cats.txt", 1);

	# Get access permissions foreach category.	
	foreach my $curcat (@{$categories})
	{
		$cats_dropdown .= "<option>$curcat</option>\n";
		$cat_list .= "$curcat\n";

		my $cat_info = file2array("$cfg{boardsdir}/$curcat.cat", 1);
		if (@$cat_info)
		{
			$cat_access_list .= <<HTML;
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post"><tr>
<td>$cat_info->[0]</td><td> <=> </td>
<td><select name="access">
HTML

			$cat_info->[1] =
			    ($cat_info->[1] ne '') ? $cat_info->[1] : 'anonuser';
			foreach (keys %usr)
			{
				my $selected = ($cat_info->[1] eq $usr{$_}) ? ' selected' : '';
				$cat_access_list .=
				    qq(<option value="$usr{$_}"$selected>$usr{$_}</option>\n);
			}
			$cat_access_list .= <<HTML;
</select></td>
<td><input type="hidden" name="op" value="forum_cats_access">
<input type="hidden" name="cats" value="$curcat">
<input type="submit" value="Set Permission"></td>
</tr>
</form>
HTML
		}
	}

	print_header();
	print_html($user_data{theme},
		"$adm{forum_admin} >>> $adm{manage_forum_cats}");
	print <<HTML;
<table width="100%" class="bg5" border="0" cellspacing="0" cellpadding="0">
<tr>
<td><table width="100%" border="0" cellspacing="1" cellpadding="2">
<tr class="tbl_header">
<td><b>Category permissions</b></td>
</tr>
<tr>
<td valign="top" class="tbl_row_light"><table>
$cat_access_list</table></td>
</tr>
<tr class="tbl_header">
<td><b>Reorder categories</b></td>
</tr>
<tr>
<td valign="top" class="tbl_row_light"><form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<textarea name="cats" cols="40" rows="4">$cat_list</textarea><br>
<input type="hidden" name="op" value="reorder_forum_cats">
<input type="submit" value="Change Order">
</form></td>
</tr>
<tr class="tbl_header">
<td><b>Remove category</b></td>
</tr>
<tr>
<td class="tbl_row_light"><form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<select name="cats" size="3">
$cats_dropdown</select><br>
<input type="hidden" name="op" value="remove_forum_cats">
<input type=submit value="Delete">
</form></td>
</tr>
<tr class="tbl_header">
<td><b>Create category</b></td>
</tr>
<tr>
<td class="tbl_row_light"><form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
ID:<br>
<input type="text" size="15" name="id"><br>
Full name:<br>
<input type="text" size="40" name="name"><br>
Access granted for:<br>
<select name="access">
HTML

	foreach (keys %usr) { print qq(<option value="$usr{$_}">$usr{$_}</option>\n); }

	print <<HTML;
</select>
<br>
<input type="hidden" name="op" value="create_forum_cats">
<input type="submit" value="Create">
</form></td>
</tr>
</table></td>
</tr>
</table>
HTML
	print_html($user_data{theme},
		"$adm{site_admin} >>> $adm{manage_forum_cats}", 1);
}

# ---------------------------------------------------------------------
# Update permissions for a category.
# ---------------------------------------------------------------------
sub forum_cats_access
{

	# Get category data.
	$cats =
	    ($cats =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	my $categories = file2array("$cfg{boardsdir}/$cats.cat", 1, 1);
	if ($access eq 'anonuser') { $access = ''; }

	# Update categories.
	sysopen(FH, "$cfg{boardsdir}/$cats.cat", O_WRONLY | O_TRUNC)
	    or user_error("$err{not_writable} $cfg{boardsdir}/cats.txt. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	print FH "$categories->[0]\n$access\n";
	foreach (@{$categories}[2 .. $#$categories]) { print FH "$_\n"; }
	close(FH);

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=manage_forum_cats');
}

# ---------------------------------------------------------------------
# Order forum categories.
# ---------------------------------------------------------------------
sub reorder_forum_cats
{

	# Update categories.
	$cats =~ s/\r//g;
	sysopen(FH, "$cfg{boardsdir}/cats.txt", O_WRONLY | O_TRUNC)
	    or user_error("$err{not_writable} $cfg{boardsdir}/cats.txt. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	print FH $cats;
	close(FH);

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=manage_forum_cats');
}

# ---------------------------------------------------------------------
# Delete forum category.
# ---------------------------------------------------------------------
sub remove_forum_cats
{

	# Get all forum categories.
	my $categories = file2array("$cfg{boardsdir}/cats.txt", 1, 1);
	my $newcatlist = '';
	foreach (@{$categories})
	{
		if ($_ ne $cats) { $newcatlist .= "$_\n"; }
	}

	# Delete board data.
	my $cat_info = file2array("$cfg{boardsdir}/$cats.cat", 1, 1);
	my $cat_name = $cat_info->[0];

	# Delete messages.
	foreach my $cat (@{$cat_info}[2 .. $#$cat_info])
	{
		$cat =
		    ($cat =~ /^([\w.]+)$/) 
		    ? $1
		    : user_error($err{bad_input}, $user_data{theme});
		if ($cat ne $cat_info->[0])
		{
			my $messages = file2array("$cfg{boardsdir}/$cat.txt", 1, 1);

			foreach (@{$messages})
			{
				my ($mid, undef) = split (/\|/, $_);
				$mid =
				    ($mid =~ /^([\w.]+)$/) 
				    ? $1
				    : user_error($err{bad_input}, $user_data{theme});
				unlink("$cfg{messagedir}/$mid.txt");
				unlink("$cfg{messagedir}/$mid.mail");
			}
		}

		unlink("$cfg{boardsdir}/$cat.txt");
		unlink("$cfg{boardsdir}/$cat.mail");
		unlink("$cfg{boardsdir}/$cat.dat");
	}

	$cats =
	    ($cats =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	unlink("$cfg{boardsdir}/$cats.cat");

	# Update categories.
	sysopen(FH, "$cfg{boardsdir}/cats.txt", O_WRONLY | O_TRUNC)
	    or user_error("$err{not_writable} $cfg{boardsdir}/cats.txt. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	print FH $newcatlist;
	close(FH);

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=manage_forum_cats');
}

# ---------------------------------------------------------------------
# Create a new forum category.
# ---------------------------------------------------------------------
sub create_forum_cats
{

	# Check input.
	$id =
	    ($id =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});

	# Get all forum categories.
	my $categories = file2array("$cfg{boardsdir}/cats.txt", 1, 1);

	# Add new category to database.
	sysopen(FH, "$cfg{boardsdir}/cats.txt", O_WRONLY | O_TRUNC | O_CREAT)
	    or user_error("$err{not_writable} $cfg{boardsdir}/cats.txt. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	foreach (@{$categories}) { print FH "$_\n"; }
	print FH $id;
	close(FH);

	# Create new category.
	sysopen(FH, "$cfg{boardsdir}/$id.cat", O_WRONLY | O_TRUNC | O_CREAT)
	    or user_error("$err{not_writable} $cfg{boardsdir}/$id.cat. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	if (grep(/^$access$/, keys %usr) == 1) { print FH "$name\n$access\n"; }
	close(FH);

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=manage_forum_cats');
}

# ---------------------------------------------------------------------
# Manage forums.
# ---------------------------------------------------------------------
sub manage_boards
{
	if (!(-r "$cfg{boardsdir}/cats.txt"))
	{
		user_error("$err{no_forums}", $user_data{theme});
	}

	# Get all admins and moderators.
	my %admin_members = ();
	my $members       = file2array("$cfg{memberdir}/memberlist.dat", 1);

	foreach (@$members)
	{

		# Get member profile.
		my $member_profile = file2array("$cfg{memberdir}/$_.dat", 1);

		if (($member_profile->[7] eq $usr{admin}
			|| $member_profile->[7] eq $usr{mod}))
		{
			$admin_members{$_} = $member_profile->[1];
		}
	}

	# Get all forum categories.
	my $categories = file2array("$cfg{boardsdir}/cats.txt", 1);

	print_header();
	print_html($user_data{theme},
		"$adm{forum_admin} >>> $adm{manage_boards}");

	print <<HTML;
<table width="100%" class="bg5" border="0" cellspacing="0" cellpadding="0">
<tr>
<td><table width="100%" border="0" cellspacing="1" cellpadding="2">
<tr class="tbl_header">
<td><b>Forum name</b></td>
<td><b>Moderator(s)</b></td>
<td><b>Action</b></td>
</tr>
HTML

	# Cycle through the categories.
	foreach (@$categories)
	{
		my $cat_name = '';
		my $cat_info = file2array("$cfg{boardsdir}/$_.cat", 1);
		$cat_name = $cat_info->[0];

		print <<HTML;
<tr class="tbl_row_dark">
<td colspan="3"><a href="$cfg{pageurl}/admin.$cfg{ext}?op=reorder_boards;cats=$_"><b>$cat_name</b></a></td>
</tr>
HTML

		foreach my $i (@$cat_info[2 .. $#$cat_info])
		{

			# Get board name and description.
			my $board_info = file2array("$cfg{boardsdir}/$i.dat", 1);
			my @moderators = split (/ +/, $board_info->[2]);

			print <<HTML;
<tr class="tbl_row_light">
<td valign="top"><form action="$cfg{pageurl}/admin.$cfg{ext}" method="post"><input type="text" name="name" value="@$board_info[0]" size="20"><br>
<textarea name="descr" cols="30" rows="3">@$board_info[1]</textarea>
</td>
<td valign="top"><select name="mod" size="5" multiple>
HTML

			# Print list of admin members.
			foreach my $j (sort keys %admin_members)
			{

				# Check if user is moderator for this forum.
				my $found = 0;
				foreach my $k (@moderators)
				{
					if ($k eq $j) { $found = 1; last; }
				}
				my $selected = ($found == 1) ? ' selected' : '';

				print
				    qq(<option value="$j"$selected>$admin_members{$j}</option>\n);
			}

			print <<HTML;
</select></td>
<td valign="top"><input type="hidden" name="op" value="modify_board">
<input type="hidden" name="id" value="$i">
<input type="hidden" name="cats" value="$_">
<input type="submit" name="modify" value="$btn{modify}">
<input type="submit" name="delete" value="$btn{delete}">
</form></td>
</tr>
HTML
		}

		print <<HTML;
<tr class="tbl_row_dark">
<td colspan="3"><b>Add a new forum in category <a href="$cfg{pageurl}/admin.$cfg{ext}?op=reorder_boards;cats=$_">$cat_name</a></b></td>
</tr>
<tr class="tbl_row_light">
<td valign="top"><form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<table>
<tr>
<td>ID:</td>
<td><input type="text" name="id" size="15"></td>
</tr>
<tr>
<td>Full name:</td>
<td><input type="text" name="name" size="20"></td>
</tr>
<tr>
<td colspan="2">Description:<br>
<textarea name="descr" cols="30" rows="3"></textarea></td>
</tr>
</table></td>
<td valign="top"><select name="mod" size="5" multiple>
HTML

		# Print list of admin members.
		foreach my $i (sort keys %admin_members)
		{
			print qq(<option value="$i">$admin_members{$i}</option>\n);
		}

		print <<HTML;
</select></td>
<td valign="top"><input type="hidden" name="op" value="create_board">
<input type="hidden" name="cats" value="$_">
<input type="submit" value="Add">
</form></td>
</tr>
HTML
	}

	print <<HTML;
</table></td>
</tr>
</table>
HTML

	print_html($user_data{theme}, "$adm{forum_admin} >>> $adm{manage_boards}",
		1);
}

# ---------------------------------------------------------------------
# Order forums in a category.
# ---------------------------------------------------------------------
sub reorder_boards
{

	# Make list of available boards.
	my $board_list;
	my $cat_boards = file2array("$cfg{boardsdir}/$cats.cat", 1);
	foreach (@{$cat_boards}[2 .. $#$cat_boards]) { $board_list .= "$_\n"; }

	print_header();
	print_html($user_data{theme},
		"$adm{forum_admin} >>> $adm{manage_boards} >>> $adm{reorder_boards}");
	print <<HTML;
<table border="0" width="100%" cellpading="0" cellspacing="0">
<tr>
<td valign="top"><form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<b>Current boards:</b><br>
<textarea name="boards" cols="30" rows="4">$board_list</textarea><br>
<input type="hidden" name="op" value="reorder_boards2">
<input type="hidden" name="first" value="$cat_boards->[0]">
<input type="hidden" name="second" value="$cat_boards->[1]">
<input type="hidden" name="cats" value="$cats">
<input type="submit" value="Reorder">
</form></td>
</tr>
</table>
HTML
	print_html($user_data{theme},
		"$adm{forum_admin} >>> $adm{manage_boards} >>> $adm{reorder_boards}",
		1);
}

# ---------------------------------------------------------------------
# Update forum order in a category.
# ---------------------------------------------------------------------
sub reorder_boards2
{

	# Update categories.
	$boards =~ s/\r//g;
	$cats =
	    ($cats =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	sysopen(FH, "$cfg{boardsdir}/$cats.cat", O_WRONLY | O_TRUNC)
	    or user_error("$err{not_writable} $cfg{boardsdir}/$cats.cat. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	print FH "$first\n$second\n";
	print FH $boards;
	close(FH);

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=manage_boards');
}

# ---------------------------------------------------------------------
# Modify a forum or create a forum.
# ---------------------------------------------------------------------
sub modify_board
{

	# Modify a board.
	$id =
	    ($id =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	if ($modify ne '')
	{

		# Update board data.
		sysopen(FH, "$cfg{boardsdir}/$id.dat", O_WRONLY | O_TRUNC)
		    or user_error("$err{not_writable} $cfg{boardsdir}/$id.dat. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		print FH "$name\n$descr\n@mod\n";
		close(FH);

		print $query->redirect(
			-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
			'?op=manage_boards');
	}

	# Delete a board.
	elsif ($delete ne '')
	{

		# Get board data.
		my $categories = file2array("$cfg{boardsdir}/$cats.cat", 1);
		my $cat_list   = '';

		foreach (@{$categories})
		{
			if ($_ ne $id) { $cat_list .= "$_\n"; }
		}

		# Update category data.
		$cats =
		    ($cats =~ /^([\w.]+)$/) 
		    ? $1
		    : user_error($err{bad_input}, $user_data{theme});
		sysopen(FH, "$cfg{boardsdir}/$cats.cat", O_WRONLY | O_TRUNC)
		    or
		    user_error("$err{not_writable} $cfg{boardsdir}/$cats.cat. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		print FH $cat_list;
		close(FH);

		# Get all messages in this forum.	
		my $messages = file2array("$cfg{boardsdir}/$id.txt", 1);

		print_header();
		print_html($user_data{theme},
			"$adm{forum_admin} >>> $adm{manage_boards} >>> $adm{delete_board}"
		    );

		# Remove all board data.
		foreach (@{$messages})
		{
			my ($mid, undef) = split (/\|/, $_);
			$mid =
			    ($mid =~ /^([\w.]+)$/) 
			    ? $1
			    : user_error($err{bad_input}, $user_data{theme});
			unlink("$cfg{messagedir}/$mid.txt");
			unlink("$cfg{messagedir}/$mid.mail");

			print "Removing message $mid...<br>";
		}

		print "Removing board datafiles...<br>";

		unlink("$cfg{boardsdir}/$id.dat");
		unlink("$cfg{boardsdir}/$id.txt");

		print "Done!";

		print_html($user_data{theme},
			"$adm{forum_admin} >>> $adm{manage_boards} >>> $adm{delete_board}",
			1);
	}
	else { user_error($err{bad_input}, $user_data{theme}); }
}

# ---------------------------------------------------------------------
# Create a new forum.
# ---------------------------------------------------------------------
sub create_board
{

	# Check input.
	if (!@mod) { @mod = 'admin'; }

	# Get category data.
	my $categories = file2array("$cfg{boardsdir}/$cats.cat", 1);

	# Update category data.
	$cats =
	    ($cats =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	sysopen(FH, "$cfg{boardsdir}/$cats.cat", O_WRONLY | O_TRUNC | O_CREAT)
	    or user_error("$err{not_writable} $cfg{boardsdir}/$cats.cat. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	foreach (@{$categories}) { print FH "$_\n"; }
	print FH "$id\n";
	close(FH);

	# Create new board.
	$id =
	    ($id =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	sysopen(FH, "$cfg{boardsdir}/$id.dat", O_WRONLY | O_TRUNC | O_CREAT);
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	print FH "$name\n$descr\n@mod\n";
	close(FH);

	# Create message db for the new board.
	sysopen(FH, "$cfg{boardsdir}/$id.txt", O_WRONLY | O_TRUNC | O_CREAT);
	close(FH);

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=manage_boards');
}

# ---------------------------------------------------------------------
# Display all polls.
# ---------------------------------------------------------------------
sub edit_polls
{

	# Get all available polls.
	my $polls = file2array("$cfg{polldir}/polls.txt", 1);
	my $num   = 1;

	print_header();
	print_html($user_data{theme}, "$adm{forum_admin} >>> $adm{edit_polls}");

	if (@$polls)
	{

		# Get ID of new poll.
		my $name;
		($num, $name) = split (/\|/, $polls->[0]);
		$num++;

		print <<HTML;
<table width="100%" class="bg5" border="0" cellspacing="0" cellpadding="0">
<tr>
<td><table width="100%" border="0" cellspacing="1" cellpadding="2">
<tr class="tbl_header">
<td><b>Title</b></td>
<td><b>Action</b></td>
HTML

		# Print user actions.
		foreach (@{$polls})
		{
			my @item = split (/\|/, $_);
			print <<HTML;
<tr class="bg2">
<td><a href="$cfg{pageurl}/polls.$cfg{ext}?op=view_poll;id=$item[0]">$item[1]</a></td>
<td>[<a href="$cfg{pageurl}/admin.$cfg{ext}?op=edit_poll;id=$item[0]">Edit</a>] [<a href="$cfg{pageurl}/admin.$cfg{ext}?op=delete_poll;id=$item[0]">Delete</a>] [<a href="$cfg{pageurl}/admin.$cfg{ext}?op=reset_poll;id=$item[0]">Reset</a>]</td>
</tr>
HTML
		}

		print <<HTML;
</table></td>
</tr>
</table>
HTML
	}

	print
	    qq(<p><a href="$cfg{pageurl}/admin.$cfg{ext}?op=create_poll;id=$num"><b>Create a new poll</b></a>);
	print_html($user_data{theme}, "$adm{forum_admin} >>> $adm{edit_polls}",
		1);
}

# ---------------------------------------------------------------------
# Create a poll.
# ---------------------------------------------------------------------
sub create_poll
{

	# Get all available polls.
	$id =
	    ($id =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	my $polls = file2array("$cfg{polldir}/polls.txt", 1);

	# Update poll index.
	sysopen(FH, "$cfg{polldir}/polls.txt", O_RDWR | O_CREAT)
	    or user_error("$err{not_writable} $cfg{polldir}/polls.txt. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	print FH "$id|Welcome to the Pollbooth\n";
	if (@$polls)
	{
		foreach (@{$polls}) { print FH "$_\n"; }
	}
	close(FH);

	# Generate question db.
	my $file = $id . "_q.dat";
	sysopen(FH, "$cfg{polldir}/$file", O_WRONLY | O_TRUNC | O_CREAT)
	    or user_error("$err{not_writable} $cfg{polldir}/$file. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	print FH "Option 1\n";
	close(FH);

	# Generate answer db.
	$file = $id . "_a.dat";
	sysopen(FH, "$cfg{polldir}/$file", O_WRONLY | O_TRUNC | O_CREAT)
	    or user_error("$err{not_writable} $cfg{polldir}/$file. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	print FH "0\n";
	close(FH);

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=edit_poll;id=' . $id);
}

# ---------------------------------------------------------------------
# Print formular to edit a poll.
# ---------------------------------------------------------------------
sub edit_poll
{

	# Get pollname.
	my $polls = file2array("$cfg{polldir}/polls.txt", 1);
	my ($pid, $poll_name, $title);
	for (@{$polls})
	{
		($pid, $poll_name) = split (/\|/, $_);
		$title = ($id == $id) ? $poll_name : '';
	}

	# Get questions.
	my $qfile     = $id . '_q.dat';
	my $questions = file2array("$cfg{polldir}/$qfile", 1);

	print_header();
	print_html($user_data{theme},
		"$adm{forum_admin} >>> $adm{edit_polls} >>> $adm{edit_poll}");
	print <<HTML;
<table>
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<tr>
<td>Question:</td>
<td><input type="text" name="question" size="40" value="$title"></td>
<td><input type="hidden" name="op" value="edit_poll2">
<input type="hidden" name="id" value="$id">
<input type="submit" name="modify" value="$btn{modify}"></td>
</tr>
</form>
HTML

	# Print choices.
	foreach (my $i = 0; $i <= $#{$questions}; $i++)
	{
		print <<HTML;
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<tr>
<td>Answer $i:</td>
<td><input type="text" name="answer" size="40" value="$questions->[$i]"></td>
<td><input type="hidden" name="op" value="edit_poll3">
<input type="hidden" name="id" value="$id">
<input type="hidden" name="pos" value="$i">
<input type="submit" name="modify" value="$btn{modify}">
<input type="submit" name="delete" value="$btn{delete}"></td>
</tr>
</form>
HTML
	}

	# Print option to add a new choice.
	print <<HTML;
</table>
</form>
<hr size="1">
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<table>
<tr>
<td>Add an answer: <input type="text" name="answer" size="40">
<input type="hidden" name="op" value="edit_poll4">
<input type="hidden" name="id" value="$id">
<input type="submit" name="modify" value="Add"></td>
</tr>
</table>
HTML

	print_html($user_data{theme},
		"$adm{forum_admin} >>> $adm{edit_polls} >>> $adm{edit_poll}", 1);
}

# ---------------------------------------------------------------------
# Change poll title.
# ---------------------------------------------------------------------
sub edit_poll2
{

	# Get all available polls.
	my $polls = file2array("$cfg{polldir}/polls.txt", 1);

	# Update title.
	sysopen(FH, "$cfg{polldir}/polls.txt", O_WRONLY | O_TRUNC)
	    or user_error("$err{not_writable} $cfg{polldir}/polls.txt. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	foreach (@{$polls})
	{
		my ($pid, $poll_name) = split (/\|/, $_);
		if ($pid == $id) { print FH "$pid|$question\n"; }
		else { print FH "$_\n"; }
	}
	close(FH);

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=edit_poll;id=' . $id);
}

# ---------------------------------------------------------------------
# Modify a choice.
# ---------------------------------------------------------------------
sub edit_poll3
{

	# Get q&a.
	$id =
	    ($id =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	my $qfile     = $id . '_q.dat';
	my $afile     = $id . '_a.dat';
	my $questions = file2array("$cfg{polldir}/$qfile", 1);
	my $answers   = file2array("$cfg{polldir}/$afile", 1);

	# Delete a question.
	if ($delete ne '')
	{

		# Update questions.
		sysopen(FH, "$cfg{polldir}/$qfile", O_WRONLY | O_TRUNC)
		    or user_error("$err{not_writable} $cfg{polldir}/$qfile. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		for (my $i = 0; $i <= $#{$questions}; $i++)
		{
			if ($pos != $i) { print FH "$questions->[$i]\n"; }
		}
		close(FH);

		# Update answers.
		sysopen(FH, "$cfg{polldir}/$afile", O_WRONLY | O_TRUNC)
		    or user_error("$err{not_writable} $cfg{polldir}/$afile. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		for (my $i = 0; $i <= $#{$answers}; $i++)
		{
			if ($pos != $i) { print FH "$answers->[$i]\n"; }
		}
		close(FH);
	}

	# Modify a question.
	if ($modify ne '')
	{
		sysopen(FH, "$cfg{polldir}/$qfile", O_WRONLY | O_TRUNC)
		    or user_error("$err{not_writable} $cfg{polldir}/$qfile. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		for (my $i = 0; $i <= $#{$questions}; $i++)
		{
			if ($pos == $i) { print FH "$answer\n"; }
			else { print FH "$questions->[$i]\n"; }
		}
		close(FH);
	}

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=edit_poll;id=' . $id);
}

# ---------------------------------------------------------------------
# Add a new choice to poll.
# ---------------------------------------------------------------------
sub edit_poll4
{

	# Get q&a.
	$id =
	    ($id =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	my $qfile     = $id . '_q.dat';
	my $afile     = $id . '_a.dat';
	my $questions = file2array("$cfg{polldir}/$qfile", 1);
	my $answers   = file2array("$cfg{polldir}/$afile", 1);

	# Add a new question.
	sysopen(FH, "$cfg{polldir}/$qfile", O_WRONLY | O_TRUNC)
	    or user_error("$err{not_writable} $cfg{polldir}/$qfile. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	foreach (@{$questions}) { print FH "$_\n"; }
	print FH "$answer\n";
	close(FH);

	# Add a new answer.
	sysopen(FH, "$cfg{polldir}/$afile", O_WRONLY | O_TRUNC)
	    or user_error("$err{not_writable} $cfg{polldir}/$afile. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	foreach (@{$answers}) { print FH "$_\n"; }
	print FH "0\n";
	close(FH);

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=edit_poll;id=' . $id);
}

# ---------------------------------------------------------------------
# Reset a poll.
# ---------------------------------------------------------------------
sub reset_poll
{

	# Get answer file.
	$id =
	    ($id =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	my $afile   = $id . '_a.dat';
	my $answers = file2array("$cfg{polldir}/$afile", 1);

	# Reset all voting counts to zero.
	sysopen(FH, "$cfg{polldir}/$afile", O_WRONLY | O_TRUNC)
	    or user_error("$err{not_writable} $cfg{polldir}/$afile. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	foreach (@{$answers}) { print FH "0\n"; }
	close(FH);

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=edit_polls');
}

# ---------------------------------------------------------------------
# Delete a poll.
# ---------------------------------------------------------------------
sub delete_poll
{

	# Get all polls.
	$id =
	    ($id =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	my $polls = file2array("$cfg{polldir}/polls.txt", 1);

	# Update poll index.
	sysopen(FH, "$cfg{polldir}/polls.txt", O_WRONLY | O_TRUNC)
	    or user_error("$err{not_writable} $cfg{polldir}/polls.txt. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	foreach (@{$polls})
	{
		my ($pid, $poll_name) = split (/\|/, $_);
		if ($id != $pid) { print FH "$_\n"; }
	}
	close(FH);

	# Remove poll files.
	my $qfile = $cfg{polldir} . '/' . $id . '_q.dat';
	my $afile = $cfg{polldir} . '/' . $id . '_a.dat';
	unlink($qfile);
	unlink($afile);

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=edit_polls');
}

# ---------------------------------------------------------------------
# Print index of pending articles.
# ---------------------------------------------------------------------
sub verify_news
{

	# Get all pending topics.
	my $pending = file2array("$cfg{topicsdir}/newarticles.dat", 1);
	if (!@$pending) { user_error($msg{no_waiting_news}, $user_data{theme}); }

	# Get topic categories.
	my $topic_cats = file2array("$cfg{topicsdir}/cats.dat", 1);

	print_header();
	print_html($user_data{theme}, "$adm{news_admin} >>> $adm{verify_news}");
	print <<HTML;
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post" name="item_list" 
onSubmit="if (!(item_list.action[0].checked || item_list.action[1].checked)) return false">
<table width="100%" class="bg5" border="0" cellspacing="0" cellpadding="0">
<tr>
<td><table width="100%" border="0" cellspacing="1" cellpadding="2">
<tr class="tbl_header">
<td width="50"><b>ID</b></td>
<td><b>Category</b></td>
<td><b>Subject</b></td>
<td><b>Date</b></td>
<td><b>Poster</b></td>
HTML

	# Print list of pending articles.
	for (my $i = 0; $i <= $#{$pending}; $i++)
	{
		my ($pid, $category, $subject, $poster, $date, $message) =
		    split (/\|/, $pending->[$i]);

		# Make category selection drop down menu.
		my $cats_select = '';
		foreach (@{$topic_cats})
		{
			my ($cat_name, $cat_link) = split (/\|/, $_);
			my $selected = ($category eq $cat_link) ? ' selected' : '';
			$cats_select .=
			    qq(<option value="$cat_link"$selected>$cat_name</option>\n);
		}

		# Get poster's nick.
		my $user_profile = file2array("$cfg{memberdir}/$poster.dat", 1);

		# Format date.
		my $formatted_date = format_date($date);

		print <<HTML;
<tr class="bg2">
<td><input type="checkbox" name="id" value=$pid> $pid</td>
<td><select name="cats">$cats_select</select></td>
<td><a href="$cfg{pageurl}/admin.$cfg{ext}?op=verify_news2;cats=$category;id=$pid">$subject</a>
<input type="hidden" name="title" value="$subject">
<input type="hidden" name="message" value="$message"></td>
<td>$formatted_date</td>
<td><a href="$cfg{pageurl}/user.$cfg{ext}?op=view_profile;username=$poster">$user_profile->[1]</a>
<input type="hidden" name="poster_name" value="$poster"></td>
</tr>
HTML
	}

	print <<HTML;
</table></td>
</tr>
</table>
<script language="javascript" type="text/javascript"><!-- 
function checkAll(val) {
al=document.item_list; len=al.elements.length; var i=0;
for (i=0; i<len; i++) { if (al.elements[i].name=='id') { al.elements[i].checked=val; } } 
}
//--></script>
<small><a href="javascript:checkAll(1)">Check All</a> - <a href="javascript:checkAll(0)">Clear All</a></small>
<p><b>Action:</b><br>
<input type="radio" name="action" value="delete"> Delete selected<br>
<input type="radio" name="action" value="modify"> Publish selected<br>
<input type="hidden" name="from_op" value="verify_news">
<input type="hidden" name="op" value="verify_news3">
<input type="submit" value="Update"><input type="reset" value="Reset">
</form>
HTML
	print_html($user_data{theme}, "$adm{news_admin} >>> $adm{verify_news}",
		1);
}


# ---------------------------------------------------------------------
# Display form to edit pending article.
# ---------------------------------------------------------------------
sub verify_news2
{

	# Get all pending topics.
	my $pending = file2array("$cfg{topicsdir}/newarticles.dat", 1);
	if (!@$pending) { user_error($msg{no_waiting_news}, $user_data{theme}); }

	# Get topic categories.
	my $topic_cats = file2array("$cfg{topicsdir}/cats.dat", 1);

	# Make category selction drop down menu.
	my $cats_select = '';
	foreach (@{$topic_cats})
	{
		my ($cat_name, $cat_link) = split (/\|/, $_);
		my $selected = ($cats eq $cat_link) ? ' selected' : '';
		$cats_select .=
		    qq(<option value="$cat_link"$selected>$cat_name</option>\n);
	}

	print_header();
	print_html($user_data{theme}, "$adm{news_admin} >>> $adm{verify_news}");

	# Get article details.
	my @article_data;
	foreach (@{$pending})
	{
		my ($pid, $category, $subject, $poster, $date, $message) =
		    split (/\|/, $_);

		if ($pid == $id)
		{

			# Format message and date.
			$message = html_to_text($message);
			my $formatted_date = format_date($date);

			# Get poster's nick.
			my $user_profile = file2array("$cfg{memberdir}/$poster.dat", 1);

			print <<HTML;
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<table>
<tr>
<td colspan="2"><b>Article from $formatted_date:</b><input type="hidden" name="postdate" value="$date"></td>
</tr>
<tr>
<td>$msg{nameC}</td>
<td><a href="$cfg{pageurl}/user.$cfg{ext}?op=view_profile;username=$poster">$user_profile->[1]</a></td>
</tr>
<tr>
<td>$msg{categoryC}</td>
<td>
<select name="cats">$cats_select</select></td>
</tr>
<td>$msg{subjectC}</td>
<td><input type="text" name="title" size="40" maxlength="50" value="$subject"></td>
</tr>
<tr>
<td valign="top">$msg{textC}</td>
<td>
<textarea name="message" rows="10" cols="40">$message</textarea><br></td>
</tr>
<td colspan="2" align="center"><input type="hidden" name="op" value="verify_news3">
<input type="hidden" name="id" value="$pid">
<input type="hidden" name="poster_name" value="$poster">
<input type="submit" name="modify" value="$btn{publish}">
<input type="submit" name="delete" value="$btn{delete}"></td>
</tr>
</table>
</form>
HTML
			last;
		}
	}

	print_html($user_data{theme}, "$adm{news_admin} >>> $adm{verify_news}",
		1);
}

# ---------------------------------------------------------------------
# Verify or delete pending news.
# ---------------------------------------------------------------------
sub verify_news3
{
	my $num = 0;
	foreach $id ($query->param('id'))
	{

		# Get all pending topics.
		my $pending = file2array("$cfg{topicsdir}/newarticles.dat", 1);

		# Publish article.
		if (($modify ne '') || ($query->param('action') eq 'modify'))
		{

			# Check and format input.
			if (!$title)
			{
				user_error($err{enter_subject}, $user_data{theme});
			}
			if (!$message) {
				user_error($err{enter_text}, $user_data{theme});
			}
			$title   = html_escape($title);
			$message = html_escape($message);

			# Get ID of new topic.
			my $files = dir2array($cfg{articledir});
			my @files = grep(/\.txt/, @{$files});
			foreach (@files) { $_ =~ s/\.txt//; }
			@files = reverse(sort { $a <=> $b } @files);
			my $post_num = $files[0] || 0;
			if ($post_num) { $post_num =~ s/\.txt//; }
			$post_num++;

			foreach (@{$pending})
			{
				my (
					$tid,     $tcategory, $tsubject,
					$tposter, $tdate,     $tmessage
				    )
				    = split (/\|/, $_);

				if ($tid == $id)
				{

					# Data integrity check.
					$cats =
					    ($query->param_fetch('cats')->[$num] =~ /^([\w.]+)$/)
					    ? $1
					    : user_error($err{bad_input}, $user_data{theme});
					$post_num =
					    ($post_num =~ /^([\w.]+)$/) 
					    ? $1
					    : user_error($err{bad_input}, $user_data{theme});

					# Get date.
					my $date = get_date();

					# Add topic to article category index.
					my $topics = file2array("$cfg{topicsdir}/$cats.cat", 1);
					sysopen(FH,
						"$cfg{topicsdir}/$cats.cat",
						O_WRONLY | O_TRUNC | O_CREAT
					    );
					if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
					if ($query->param('from_op'))
					{
						print FH "$post_num|$tsubject|$tposter|$date|0|0\n";
					}
					else
					{
						print FH "$post_num|$title|$poster_name|$date|0|0\n";
					}
					foreach (@{$topics}) { print FH "$_\n"; }
					close(FH);

					# Save topic.
					sysopen(FH,
						"$cfg{articledir}/$post_num.txt",
						O_WRONLY | O_TRUNC | O_CREAT
					    );
					if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
					if ($query->param('from_op'))
					{
						print FH "$tsubject|$tposter|$date|$tmessage\n";
					}
					else { print FH "$title|$poster_name|$date|$message\n"; }
					close(FH);

					# Update article count of user who posted news.
					$poster_name =
					    ($poster_name =~ /^([\w.]+)$/) 
					    ? $1
					    : user_error($err{bad_input}, $user_data{theme});
					my $user_profile =
					    file2array("$cfg{memberdir}/$poster_name.dat", 1);
					$user_profile->[11]++;

					sysopen(FH, "$cfg{memberdir}/$poster_name.dat",
						O_WRONLY | O_TRUNC)
					    or user_error(
						"$err{not_writable} $cfg{memberdir}/$poster_name.dat. ($!)",
						$user_data{theme});
					if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
					foreach (@{$user_profile}) { print FH "$_\n"; }
					close(FH);

					# Notify users, who are watching this category.
					if (-r "$cfg{topicsdir}/$cats.mail")
					{
						notify_users($cats,
							"$cfg{pageurl}/topics.$cfg{ext}?op=view_topic;cat=$cats;id=$post_num"
						    );
					}
				}
			}

			# Update the pending news db.
			sysopen(FH, "$cfg{topicsdir}/newarticles.dat", O_WRONLY | O_TRUNC)
			    or user_error(
				"$err{not_writable} $cfg{topicsdir}/newarticles.dat. ($!)",
				$user_data{theme});
			if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
			foreach (@{$pending})
			{
				my ($tid, undef, undef, undef, undef, undef) =
				    split (/\|/, $_);
				if ($tid != $id) { print FH "$_\n"; }
			}
			close(FH);
		}

		# Export to RDF-file.
		rdf_export();

		# Delete the submitted article.
		if (($delete ne '') || ($query->param('action') eq 'delete'))
		{
			sysopen(FH, "$cfg{topicsdir}/newarticles.dat", O_WRONLY | O_TRUNC)
			    or user_error(
				"$err{not_writable} $cfg{topicsdir}/newarticles.dat. ($!)",
				$user_data{theme});
			if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
			foreach (@{$pending})
			{
				my ($tid, undef, undef, undef, undef, undef) =
				    split (/\|/, $_);
				if ($tid != $id) { print FH "$_\n"; }
			}
			close(FH);
		}
		$num++;
	}

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=verify_news');
}

# ---------------------------------------------------------------------
# Print index of available articles.
# ---------------------------------------------------------------------
sub modify_news
{

	# Get all topic cats.
	my $topic_cats = file2array("$cfg{topicsdir}/cats.dat", 1);

	# Make dropdown menu with all cats.
	my ($cat, $cat_selector) = ('', '');
	foreach (@{$topic_cats})
	{
		my ($cat_name, $cat_link) = split (/\|/, $_);
		if ($cats eq $cat_link)
		{
			$cat_selector .=
			    qq(<option value="$cat_link" selected>$cat_name</option>\n);
			$cat = $cat_name;
		}
		else
		{
			$cat_selector .=
			    qq(<option value="$cat_link">$cat_name</option>\n);
		}
	}

	print_header();
	print_html($user_data{theme}, "$adm{news_admin} >>> $adm{modify_news}");
	print <<HTML;
<table>
<tr>
<td valign="top">Choose article category: </td>
<td valign="top"><form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<select name="cats">$cat_selector</select>
<input type="hidden" name="op" value="modify_news">
<input type="submit" value="$btn{go}">
</form></td>
</tr>
</table><br>
HTML

	# Print article list.
	if ($cats ne '')
	{
		print <<HTML;
<b>Articles in category "$cat":</b><br>
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post" name="item_list" 
onSubmit="if (!(item_list.op[0].checked || item_list.op[1].checked)) return false">
<table width="100%" class="bg5" border="0" cellspacing="0" cellpadding="0">
<tr>
<td><table width="100%" border="0" cellspacing="1" cellpadding="2">
<tr class="tbl_header">
<td width="50"><b>ID</b></td>
<td><b>Subject</b></td>
<td><b>Date</b></td>
<td><b>Poster</b></td>
HTML

		my $articles = file2array("$cfg{topicsdir}/$cats.cat", 1);
		foreach (@{$articles})
		{
			my ($tid, $subject, $poster, $postdate, $comments, $views) =
			    split (/\|/, $_);

			# Get poster's nick.
			my $user_profile = file2array("$cfg{memberdir}/$poster.dat", 1);

			# Format date.
			my $formatted_date = format_date($postdate);

			print <<HTML;
<tr class="bg2">
<td><input type="checkbox" name="id" value=$tid> $tid</td>
<td><a href="$cfg{pageurl}/admin.$cfg{ext}?op=modify_news2;cats=$cats;id=$tid">$subject</a></td>
<td>$formatted_date</td>
<td><a href="$cfg{pageurl}/user.$cfg{ext}?op=view_profile;username=$poster">$user_profile->[1]</a></td>
</tr>
HTML
		}

		print <<HTML;
</table></td>
</tr>
</table>
<script language="javascript" type="text/javascript"><!-- 
function checkAll(val) {
al=document.item_list; len=al.elements.length; var i=0;
for (i=0; i<len; i++) { if (al.elements[i].name=='id') { al.elements[i].checked=val; } } 
}
//--></script>
<small><a href="javascript:checkAll(1)">Check All</a> - <a href="javascript:checkAll(0)">Clear All</a></small>
<p><b>Action:</b><br>
<input type="radio" name="op" value="modify_news3"> Delete selected<br>
<input type="radio" name="op" value="move_topic"> Move selected<br>
<b>Move to:</b><br>
<select name="new_cat">$cat_selector</select><br>
<input type="hidden" name="cats" value="$cats">
<input type="hidden" name="delete" value="1">
<input type="hidden" name="from_op" value="modify_news">
<input type="hidden" name="old_cat" value="$cats">
<input type="submit" value="Update"><input type="reset" value="Reset">
</form>
HTML
	}
	print_html($user_data{theme}, "$adm{news_admin} >>> $adm{modify_news}",
		1);
}

# ---------------------------------------------------------------------
# Print formular to edit a topic article.
# ---------------------------------------------------------------------
sub modify_news2
{

	# Get article data.
	my $messages = file2array("$cfg{articledir}/$id.txt", 1);

	print_header();
	print_html($user_data{theme}, "$adm{news_admin} >>> $adm{modify_news}");
	print qq(<table width="100%">\n);

	# Print article with comments.
	for (my $i = 0; $i <= $#{$messages}; $i++)
	{
		my ($subject, $poster, $postdate, $text) =
		    split (/\|/, $messages->[$i]);

		# Get last poster's name.
		my $user_profile = file2array("$cfg{memberdir}/$poster.dat", 1);

		# Format text and date.
		$text = html_to_text($text);
		my $formatted_date = format_date($postdate);

		print <<HTML;
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<tr>
<td valign="top">$user_profile->[1]<br>
$formatted_date</td>
<td><input type="text" name="title" value="$subject" size="30"><br>
<textarea name="message" rows="3" cols="40">$text</textarea></td>
<td><input type="hidden" name="op" value="modify_news3">
<input type="hidden" name="cats" value="$cats">
<input type="hidden" name="id" value="$id">
<input type="hidden" name="view_num" value="$i">
<input type="submit" name="modify" value="$btn{modify}"><br>
<input type="submit" name="delete" value="$btn{delete}"></td>
</tr>
<tr>
<td colspan="3"><hr size="1"></td>
</tr>
</form>
HTML
	}

	# Make dropdown menu with all categories.
	my $cat;
	my $topic_cats = file2array("$cfg{topicsdir}/cats.dat", 1);
	foreach (@{$topic_cats})
	{
		my ($cat_name, $cat_link) = split (/\|/, $_);
		if ($cats eq $cat_link)
		{
			$cat .=
			    qq(<option value="$cat_link" selected>$cat_name</option>\n);
		}
		else { $cat .= qq(<option value="$cat_link">$cat_name</option>\n); }
	}

	# Print menu to move topics.
	print <<HTML;
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<tr>
<td colspan="3">Move to: <select name="new_cat">
$cat</select>
<input type="hidden" name="op" value="move_topic">
<input type="hidden" name="id" value="$id">
<input type="hidden" name="old_cat" value="$cats">
<input type="submit" value="Move"></td>
</tr>
</form>
</table>
HTML
	print_html($user_data{theme}, "$adm{news_admin} >>> $adm{modify_news}",
		1);
}

# ---------------------------------------------------------------------
# Edit or delete a topic message.
# ---------------------------------------------------------------------
sub modify_news3
{
	foreach $id ($query->param('id'))
	{

		# Get article data.
		my $messages = file2array("$cfg{articledir}/$id.txt", 1);

		# Check if there are comments attached.
		my $count = scalar @{$messages};
		if ($view_num == 0 && $count > 1 && $delete ne '')
		{
			user_error($err{delete_replies}, $user_data{theme});
		}

		# Data integrity check.
		$id =
		    ($id =~ /^([\w.]+)$/) 
		    ? $1
		    : user_error($err{bad_input}, $user_data{theme});
		$cats =
		    ($cats =~ /^([\w.]+)$/) 
		    ? $1
		    : user_error($err{bad_input}, $user_data{theme});

		# Update topic.
		sysopen(FH, "$cfg{articledir}/$id.txt", O_WRONLY | O_TRUNC)
		    or user_error("$err{not_writable} $cfg{articledir}/$id.txt. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		for (my $i = 0; $i <= $#{$messages}; $i++)
		{
			if ($view_num == $i)
			{
				my ($subject, $poster, $postdate, $text) =
				    split (/\|/, $messages->[$i]);

				# Modify topic entry.
				if ($modify ne '')
				{

					# Format input.
					chomp($title);
					chomp($message);
					$title   = html_escape($title);
					$message = html_escape($message);

					# Update category index if this is initial message.
					if ($view_num == 0)
					{

						# Get category index.
						my $topic_cats =
						    file2array("$cfg{topicsdir}/$cats.cat", 1);

						# Update index.
						sysopen(FH2, "$cfg{topicsdir}/$cats.cat",
							O_WRONLY | O_TRUNC)
						    or user_error(
							"$err{not_writable} $cfg{topicsdir}/$cats.cat. ($!)",
							$user_data{theme});
						flock(FH2, LOCK_EX) if $cfg{use_flock};
						foreach (@{$topic_cats})
						{
							my @cat = split (/\|/, $_);
							if ($cat[0] == $id)
							{
								print FH2
								    "$cat[0]|$title|$cat[2]|$cat[3]|$cat[4]|$cat[5]\n";
							}
							else { print FH2 "$_\n"; }
						}
						close(FH2);
					}
					print FH "$title|$poster|$postdate|$message\n";
				}

				# Delete topic entry.
				if ($delete ne '')
				{

					# Get category index.
					my $topic_cats =
					    file2array("$cfg{topicsdir}/$cats.cat", 1);

					# Update index.
					sysopen(FH2, "$cfg{topicsdir}/$cats.cat",
						O_WRONLY | O_TRUNC)
					    or user_error(
						"$err{not_writable} $cfg{topicsdir}/$cats.cat. ($!)",
						$user_data{theme});
					flock(FH2, LOCK_EX) if $cfg{use_flock};
					foreach (@{$topic_cats})
					{
						my @cat = split (/\|/, $_);

						# If message is a comment, update comment counter.
						if ($cat[0] == $id)
						{
							if ($view_num != 0)
							{

								# Decrement comment counter.
								$cat[6]--;
								print FH2
								    "$cat[0]|$cat[1]|$cat[2]|$cat[3]|$cat[4]|$cat[5]\n";
							}
						}
						else { print FH2 "$_\n"; }
					}
					close(FH2);

					# Decrement poster's article/comment count.
					my $user_profile =
					    file2array("$cfg{memberdir}/$poster.dat", 1);
					if ($view_num == 0) { $user_profile->[11]--; }
					else { $user_profile->[12]--; }

					# Update poster profile.
					$poster =
					    ($poster =~ /^([\w.]+)$/) 
					    ? $1
					    : user_error($err{bad_input}, $user_data{theme});
					sysopen(FH2, "$cfg{memberdir}/$poster.dat",
						O_WRONLY | O_TRUNC);
					flock(FH2, LOCK_EX) if $cfg{use_flock};
					foreach (@{$user_profile}) { print FH2 "$_\n"; }
					close(FH2);
				}
			}
			else { print FH "$messages->[$i]\n"; }
		}
		close(FH);
	}

	# Export to RDF-file.
	rdf_export();

	if ($query->param('from_op'))
	{
		print $query->redirect(
			-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
			'?op=modify_news;cats=' . $cats);
	}
	else
	{
		print $query->redirect(
			-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
			'?op=modify_news2;cats=' . $cats . ';id=' . $id);
	}
}

# ---------------------------------------------------------------------
# Move a topic.
# ---------------------------------------------------------------------
sub move_topic
{

	# Data integrity check.
	if ($old_cat eq $new_cat)
	{
		user_error($err{bad_input}, $user_data{theme});
	}
	$old_cat =
	    ($old_cat =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	$new_cat =
	    ($new_cat =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});

	# Remove topic from old category.
	foreach $id ($query->param('id'))
	{
		my $move_topic;
		my $old_topics = file2array("$cfg{topicsdir}/$old_cat.cat", 1);
		sysopen(FH, "$cfg{topicsdir}/$old_cat.cat", O_WRONLY | O_TRUNC)
		    or user_error(
			"$err{not_writable} $cfg{topicsdir}/$old_cat.cat. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		for (@{$old_topics})
		{
			my ($tid, undef, undef, undef, undef, undef) = split (/\|/, $_);

			if ($tid ne $id) { print FH "$_\n"; }
			else { $move_topic = $_; }
		}
		close(FH);

		# Save topic in new category.
		my $new_topics = file2array("$cfg{topicsdir}/$new_cat.cat", 1);
		sysopen(FH, "$cfg{topicsdir}/$new_cat.cat",
			O_WRONLY | O_TRUNC | O_CREAT)
		    or user_error(
			"$err{not_writable} $cfg{topicsdir}/$new_cat.cat. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		print FH "$move_topic\n";
		foreach (@{$new_topics}) { print FH "$_\n"; }
		close(FH);
	}

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=modify_news');
}

# ---------------------------------------------------------------------
# Print list of all topic categories.
# ---------------------------------------------------------------------
sub edit_topic_cats
{

	# Get all topic cats.
	my $topic_cats = file2array("$cfg{topicsdir}/cats.dat", 1);

	print_header();
	print_html($user_data{theme},
		"$adm{news_admin} >>> $adm{edit_topic_cats}");
	print "<table>\n";

	# Print list of all available categories.
	foreach (@{$topic_cats})
	{
		my @item = split (/\|/, $_);
		print <<HTML;
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<tr>
<td><img src="$cfg{imagesurl}/topics/$item[1].gif" alt="$cfg{imagesurl}/topics/$item[1].gif"></td>
<td valign="top"><table>
<tr>
<td>Description:</td>
<td><input type="text" name="descr" value="$item[0]"></td>
</tr>
<tr>
<td>Category:</td>
<td><input type="text" name="cats" value="$item[1]"></td>
</tr>
</table></td>
<td valign="top"><input type="hidden" name="op" value="edit_topic_cats2">
<input type="hidden" name="old_cat" value="$item[1]">
<input type="submit" name="modify" value="$btn{modify}"><br>
<input type="submit" name="delete" value="$btn{delete}"></td>
</tr>
</form>
HTML
	}

	# Print panel to add a new category.
	print <<HTML;
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<tr>
<td colspan="2"><hr size="1">
<b>Add Category</b></td>
</tr>
<tr>
<td>Description:</td>
<td><input type="text" name="descr"></td>
</tr>
<tr>
<td>Category:</td>
<td><input type="text" name="cats"></td>
</tr>
<tr>
<td colspan="2"><input type="hidden" name="op" value="edit_topic_cats3">
<input type="submit" value="Create"></td>
</tr>
</form>
</table>
HTML
	print_html($user_data{theme},
		"$adm{news_admin} >>> $adm{edit_topic_cats}", 1);
}

# ---------------------------------------------------------------------
# Update or delete a topic category.
# ---------------------------------------------------------------------
sub edit_topic_cats2
{

	# Data integrity check.
	$cats =
	    ($cats =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});

	# Get all topic cats.
	my $topic_cats = file2array("$cfg{topicsdir}/cats.dat", 1);

	# Modify category.
	if ($modify ne '')
	{
		chomp($old_cat);

		# Update main category index.
		sysopen(FH, "$cfg{topicsdir}/cats.dat", O_WRONLY | O_TRUNC)
		    or user_error("$err{not_writable} $cfg{topicsdir}/cats.dat. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		foreach (@{$topic_cats})
		{
			my ($cat_name, $cat_link) = split (/\|/, $_);
			if ($old_cat eq $cat_link) { print FH "$descr|$cats\n"; }
			else { print FH "$_\n"; }
		}
		close(FH);

		# Update category.
		$old_cat =
		    ($old_cat =~ /^([\w.]+)$/) 
		    ? $1
		    : user_error($err{bad_input}, $user_data{theme});
		my $old_topics = file2array("$cfg{topicsdir}/$old_cat.cat", 1);
		sysopen(FH, "$cfg{topicsdir}/$cats.cat", O_WRONLY | O_TRUNC | O_CREAT)
		    or
		    user_error("$err{not_writable} $cfg{topicsdir}/$cats.cat. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		foreach (@{$old_topics}) { print FH "$_\n"; }
		close(FH);

		if ($cats ne $old_cat) { unlink("$cfg{topicsdir}/$old_cat.cat"); }
	}

	# Delete topic category.
	if ($delete ne '')
	{
		chomp($cats);

		# Get data of category.
		my $topics = file2array("$cfg{topicsdir}/$cats.cat", 1);

		# Exit if there are topics in this category.
		if (@$topics) { user_error($err{delete_replies}, $user_data{theme}); }

		# Update main category index.
		sysopen(FH, "$cfg{topicsdir}/cats.dat", O_WRONLY | O_TRUNC)
		    or user_error("$err{not_writable} $cfg{topicsdir}/cats.dat. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		foreach (@{$topic_cats})
		{
			my ($cat_name, $cat_link) = split (/\|/, $_);
			if ($cats ne $cat_link) { print FH "$_\n"; }
		}
		close(FH);

		unlink("$cfg{topicsdir}/$cats.cat");
	}

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=edit_topic_cats');
}

# ---------------------------------------------------------------------
# Create a topic category.
# ---------------------------------------------------------------------
sub edit_topic_cats3
{

	# Get all topic categories.
	my $topic_cats = file2array("$cfg{topicsdir}/cats.dat", 1);

	# Update main category index.
	sysopen(FH, "$cfg{topicsdir}/cats.dat", O_WRONLY | O_TRUNC | O_CREAT)
	    or user_error("$err{not_writable} $cfg{topicsdir}/cats.dat. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	print FH "$descr|$cats\n";
	foreach (@{$topic_cats}) { print FH "$_\n"; }
	close(FH);

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=edit_topic_cats');
}

# ---------------------------------------------------------------------
# Display all link categories.
# ---------------------------------------------------------------------
sub edit_link_cats
{

	# Get all link categories.
	my $link_cats = file2array("$cfg{linksdir}/linkcats.dat", 1);

	print_header();
	print_html($user_data{theme},
		"$adm{news_admin} >>> $adm{edit_link_cats}");
	print "<table>\n";

	# Print all categories.
	foreach (@{$link_cats})
	{
		my @item = split (/\|/, $_);
		$item[2] = html_to_text($item[2]);

		print <<HTML;
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<tr>
<td colspan="2" valign="top"><table>
<tr>
<td>Name:</td>
<td><input type="text" name="name" value="$item[0]">
<input type="hidden" name="cats" value="$item[1]"></td>
</tr>
<tr>
<td valign="top">Description:</td>
<td><textarea name="descr" rows="10" cols="40">$item[2]</textarea></td>
</tr>
</table></td>
<td valign="top"><input type="hidden" name="op" value="edit_link_cats2">
<input type="hidden" name="old_cat" value="$item[1]">
<input type="submit" name="modify" value="$btn{modify}"><br>
<input type="submit" name="delete" value="$btn{delete}"></td>
</tr>
</form>
HTML
	}

	# Print panel to create a new category.
	print <<HTML;
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<tr>
<td colspan="2"><hr size="1">
<b>Add Category</b></td>
</tr>
<tr>
<td>Name:</td>
<td><input type="text" name="name"></td>
</tr>
<tr>
<td>ID:</td>
<td><input type="text" name="cats"></td>
</tr>
<tr>
<td valign="top">Description:</td>
<td><textarea name="descr" rows="10" cols="40"></textarea></td>
</tr>
<tr>
<td colspan="2"><input type="hidden" name="op" value="edit_link_cats3">
<input type="submit" value="Create"></td>
</tr>
</form>
</table>
HTML

	print_html($user_data{theme}, "$adm{news_admin} >>> $adm{edit_link_cats}",
		1);
}

# ---------------------------------------------------------------------
# Modify or delete a link category.
# ---------------------------------------------------------------------
sub edit_link_cats2
{

	# Check and format input.
	if (!$name || !$descr) { user_error($err{bad_input}, $user_data{theme}); }
	$name  = html_escape($name);
	$descr = html_escape($descr);

	# Data integrity check.
	$cats =
	    ($cats =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	$old_cat =
	    ($old_cat =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});

	# Get all link categories.
	my $link_cats = file2array("$cfg{linksdir}/linkcats.dat", 1);

	# Mofify link category.
	if ($modify ne '')
	{
		chomp($old_cat);

		# Update main link category.
		sysopen(FH, "$cfg{linksdir}/linkcats.dat", O_WRONLY | O_TRUNC)
		    or
		    user_error("$err{not_writable} $cfg{linksdir}/linkcats.dat. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		foreach (@{$link_cats})
		{
			my @item = split (/\|/, $_);
			if ($old_cat eq $item[1]) { print FH "$name|$cats|$descr\n"; }
			else { print FH "$_\n"; }
		}
		close(FH);

		# Rename category.
		if ($old_cat ne $cats && -w "$cfg{linksdir}/$old_cat.dat")
		{
			my $old_links = file2array("$cfg{linksdir}/$old_cat.dat", 1);
			sysopen(FH, "$cfg{linksdir}/$cats.dat",
				O_WRONLY | O_TRUNC | O_CREAT)
			    or user_error(
				"$err{not_writable} $cfg{linksdir}/$cats.dat. ($!)",
				$user_data{theme});
			if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
			foreach (@{$old_links}) { print FH "$_\n"; }
			close(FH);

			if ($cats ne $old_cat) { unlink("$cfg{linksdir}/$old_cat.dat"); }
		}
	}

	# Delete link category.
	if ($delete ne '')
	{
		chomp($cats);

		# Update main link category.
		sysopen(FH, "$cfg{linksdir}/linkcats.dat", O_WRONLY | O_TRUNC)
		    or
		    user_error("$err{not_writable} $cfg{linksdir}/linkcats.dat. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		foreach (@{$link_cats})
		{
			my @item = split (/\|/, $_);
			if ($old_cat ne $item[1]) { print FH "$_\n"; }
		}
		close(FH);

		unlink("$cfg{linksdir}/$old_cat.dat");
	}

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=edit_link_cats');
}

# ---------------------------------------------------------------------
# Create a link category.
# ---------------------------------------------------------------------
sub edit_link_cats3
{

	# Check and format input.
	user_error($err{bad_input}, $user_data{theme})
	    if (!$name
		|| !$descr
		|| !$cats
		|| $cats !~ /^[0-9A-Za-z#%+,-\.:=?@^_]+$/);
	$name  = html_escape($name);
	$descr = html_escape($descr);

	# Update main link category.
	my $link_cats = file2array("$cfg{linksdir}/linkcats.dat", 1);
	sysopen(FH, "$cfg{linksdir}/linkcats.dat", O_WRONLY | O_TRUNC | O_CREAT)
	    or user_error("$err{not_writable} $cfg{linksdir}/linkcats.dat. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	foreach (@{$link_cats}) { print FH "$_\n"; }
	print FH "$name|$cats|$descr\n";
	close(FH);

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=edit_link_cats');
}

# ---------------------------------------------------------------------
# Display all available links.
# ---------------------------------------------------------------------
sub edit_links
{

	# Get all link categories.
	my $link_cats = file2array("$cfg{linksdir}/linkcats.dat", 1);

	# Make dropdown menu with all cats.
	my ($cat, $cat_selector) = ('', '');
	foreach (@{$link_cats})
	{
		my ($cat_name, $cat_link) = split (/\|/, $_);
		if ($cats eq $cat_link)
		{
			$cat_selector .=
			    qq(<option value="$cat_link" selected>$cat_name</option>\n);
			$cat = $cat_name;
		}
		else
		{
			$cat_selector .=
			    qq(<option value="$cat_link">$cat_name</option>\n);
		}
	}

	print_header();
	print_html($user_data{theme}, "$adm{news_admin} >>> $adm{edit_links}");
	print <<HTML;
<table>
<tr>
<td valign="top">Choose link category: </td>
<td valign="top"><form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<select name="cats">
$cat_selector</select>
<input type="hidden" name="op" value="edit_links">
<input type="submit" value="$btn{go}">
</form></td>
</tr>
</table><br>
HTML

	# Print links in selected category.
	if ($cats ne '')
	{
		print <<HTML;
<b>Links in category "$cat":</b><br>
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post" name="item_list" 
onSubmit="if (!(item_list.op[0].checked || item_list.op[1].checked)) return false">
<table width="100%" class="bg5" border="0" cellspacing="0" cellpadding="0">
<tr>
<td><table width="100%" border="0" cellspacing="1" cellpadding="2">
<tr class="tbl_header">
<td width="50"><b>ID</b></td>
<td><b>Name</b></td>
<td><b>Description</b></td>
<td><b>Date</b></td>
<td><b>Poster</b></td>
HTML

		# Print links.
		my $links = file2array("$cfg{linksdir}/$cats.dat", 1);
		;
		foreach (@{$links})
		{
			my (
				$lid,   $link_name, $url,
				$descr, $postdate,  $link_poster,
				$hits,  $votes,     $rate
			    )
			    = split (/\|/, $_);

			# Get poster profile.
			my $user_profile =
			    file2array("$cfg{memberdir}/$link_poster.dat", 1);

			# Format date.
			my $formatted_date = format_date($postdate);

			print <<HTML;
<tr class="bg2">
<td><input type="checkbox" name="id" value=$lid>  $lid</a></td>
<td><a href="$cfg{pageurl}/admin.$cfg{ext}?op=edit_links2;cats=$cats;id=$lid">$link_name</a></td>
<td>$descr <small><a href="$url" target="_blank">view</a></small></td>
<td>$formatted_date</td>
<td><a href="$cfg{pageurl}/user.$cfg{ext}?op=view_profile;username=$link_poster">$user_profile->[1]</a></td>
</tr>
HTML
		}

		print <<HTML;
</td>
</tr>
</table></td>
</tr>
</table>
<script language="javascript" type="text/javascript"><!-- 
function checkAll(val) {
al=document.item_list; len=al.elements.length; var i=0;
for (i=0; i<len; i++) { if (al.elements[i].name=='id') { al.elements[i].checked=val; } } 
}
//--></script>
<small><a href="javascript:checkAll(1)">Check All</a> - <a href="javascript:checkAll(0)">Clear All</a></small>
<p><b>Action:</b><br>
<input type="radio" name="op" value="edit_links3"> Delete selected<br>
<input type="radio" name="op" value="move_link"> Move selected<br>
<b>Move to:</b><br>
<select name="new_cat">$cat_selector</select><br>
<input type="hidden" name="cats" value="$cats">
<input type="hidden" name="delete" value="1">
<input type="hidden" name="from_op" value="modify_news">
<input type="hidden" name="old_cat" value="$cats">
<input type="submit" value="Update"><input type="reset" value="Reset">
</form>
HTML
	}
	print_html($user_data{theme}, "$adm{news_admin} >>> $adm{edit_links}", 1);
}

# ---------------------------------------------------------------------
# Print formular to edit a link.
# ---------------------------------------------------------------------
sub edit_links2
{

	# Get data of category.
	my $links = file2array("$cfg{linksdir}/$cats.dat", 1);

	print_header();
	print_html($user_data{theme}, "$adm{news_admin} >>> $adm{edit_links}");
	print qq(<table width="100%">);

	foreach (@{$links})
	{
		my ($lid, $link_name, $url, $descr, $postdate, $link_poster, $hits) =
		    split (/\|/, $_);

		if ($id == $lid)
		{

			# Get poster profile.
			my $user_profile =
			    file2array("$cfg{memberdir}/$link_poster.dat", 1);

			# Format description and date.
			$descr = html_to_text($descr);
			my $formatted_date = format_date($postdate);

			print <<HTML;
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<tr>
<td valign="top">From <a href="$cfg{pageurl}/user.$cfg{ext}?op=view_profile;username=$link_poster">$user_profile->[1]</a>:<br>
($formatted_date)</td>
<td><input type="text" name="name" value="$link_name" size="30"><br>
<input type="text" name="url" value="$url" size="30"><br>
<textarea name="descr" rows="3" cols="40">$descr</textarea></td>
<td><input type="hidden" name="op" value="edit_links3">
<input type="hidden" name="cats" value="$cats">
<input type="hidden" name="id" value="$id">
<input type="submit" name="modify" value="$btn{modify}"><br>
<input type="submit" name="delete" value="$btn{delete}"></td>
</tr>
<tr>
<td colspan="3"><hr size="1"></td>
</tr>
</form>
HTML
		}
	}

	# Get all link categories.
	my $link_cats = file2array("$cfg{linksdir}/linkcats.dat", 1);
	;

	# Make dropdown menu with all categories.
	my ($cat, $catname);
	foreach (@{$link_cats})
	{
		my ($cat_name, $cat_link, $cat_descr) = split (/\|/, $_);
		if ($cats eq $cat_link)
		{
			$cat .=
			    qq(<option value="$cat_link" selected>$cat_name</option>\n);
			$catname = $cat_name;
		}
		else { $cat .= qq(<option value="$cat_link">$cat_name</option>\n); }
	}

	# Print panel to move link to another category.
	print <<HTML;
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<tr>
<td colspan="3">Move to: <select name="new_cat">
$cat</select>
<input type="hidden" name="op" value="move_link">
<input type="hidden" name="id" value="$id">
<input type="hidden" name="old_cat" value="$cats">
<input type="submit" value="Move"></td>
</tr>
</form>
</table>
HTML
	print_html($user_data{theme}, "$adm{news_admin} >>> $adm{edit_links}", 1);
}

# ---------------------------------------------------------------------
# Modify or delete a link.
# ---------------------------------------------------------------------
sub edit_links3
{

	# Get data of category.
	$cats =
	    ($cats =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	my $links = file2array("$cfg{linksdir}/$cats.dat", 1);

	# Modify link. 
	if ($modify ne '')
	{

		# Check and format input.
		if (!$name || !$descr || !$url)
		{
			user_error($err{bad_input}, $user_data{theme});
		}
		$name  = html_escape($name);
		$descr = html_escape($descr);

		sysopen(FH, "$cfg{linksdir}/$cats.dat", O_WRONLY | O_TRUNC)
		    or user_error("$err{not_writable} $cfg{linksdir}/$cats.dat. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		foreach (@{$links})
		{
			my (
				$lid,        $link_name, $link_url,
				$link_descr, $postdate,  $link_poster,
				$hits,       $votes,     $rate
			    )
			    = split (/\|/, $_);
			if ($lid == $id)
			{
				print FH
				    "$lid|$name|$url|$descr|$postdate|$link_poster|$hits|$votes|$rate\n";
			}
			else { print FH "$_\n"; }
		}
		close(FH);
	}

	# Delete link.
	if ($delete ne '')
	{
		sysopen(FH, "$cfg{linksdir}/$cats.dat", O_WRONLY | O_TRUNC)
		    or user_error("$err{not_writable} $cfg{linksdir}/$cats.dat. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		foreach (@{$links})
		{
			my (
				$lid,      $link_name,   $url, $descr,
				$postdate, $link_poster, $hits
			    )
			    = split (/\|/, $_);
			if (!(grep { $lid eq $_ } $query->param('id')))
			{
				print FH "$_\n";
			}
		}
		close(FH);
	}

	if ($query->param('from_op'))
	{
		print $query->redirect(
			-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
			'?op=edit_links;cats=' . $cats);
	}
	else
	{
		print $query->redirect(
			-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
			'?op=edit_links2;cats=' . $cats . ';id=' . $id);
	}
}

# ---------------------------------------------------------------------
# Move a link.
# ---------------------------------------------------------------------
sub move_link
{

	# Data integrity check.
	if ($old_cat eq $new_cat)
	{
		user_error($err{bad_input}, $user_data{theme});
	}
	$old_cat =
	    ($old_cat =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	$new_cat =
	    ($new_cat =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});

	# Get old and category index.
	foreach $id ($query->param('id'))
	{
		my $old_links = file2array("$cfg{linksdir}/$old_cat.dat", 1);
		my $new_links = file2array("$cfg{linksdir}/$new_cat.dat", 1);

		# Get ID for link in new category.
		my $new_id = 0;
		if (@$new_links)
		{
			($new_id,  undef, undef, undef, undef,
			    undef, undef, undef, undef)
			    = split (/\|/, $new_links->[0]);
		}
		else { $new_id = 0; }
		$new_id++;

		# Remove link from old category.
		my $move_link;
		sysopen(FH, "$cfg{linksdir}/$old_cat.dat", O_WRONLY | O_TRUNC)
		    or
		    user_error("$err{not_writable} $cfg{linksdir}/$old_cat.dat. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		foreach (@{$old_links})
		{
			my (
				$lid,        $link_name, $url,
				$link_descr, $postdate,  $link_poster,
				$hits,       $votes,     $rate
			    )
			    = split (/\|/, $_);
			if ($lid != $id) { print FH "$_\n"; }
			else
			{
				$move_link =
				    "$new_id|$link_name|$url|$link_descr|$postdate|$link_poster|$hits|$votes|$rate\n";
			}
		}
		close(FH);

		# Insert link into new category.
		sysopen(FH, "$cfg{linksdir}/$new_cat.dat",
			O_WRONLY | O_TRUNC | O_CREAT)
		    or
		    user_error("$err{not_writable} $cfg{linksdir}/$new_cat.dat. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		print FH $move_link;
		foreach (@{$new_links}) { print FH "$_\n"; }
		close(FH);
	}

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=edit_links');
}

# ---------------------------------------------------------------------
# Edit user ranks.
# ---------------------------------------------------------------------
sub user_ranks
{
	my %ranks = load_ranks();

	print_header();
	print_html($user_data{theme}, "$adm{user_admin} >>> $adm{user_ranks}");
	print "<table>\n";

	my $num = 0;
	foreach (sort keys %ranks)
	{
		print <<HTML;
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<tr>
<td>More than <input type="text" name="second" size="4" value="$_"> posts </td>
<td width=50 align="center"><=></td>
<td><input type="text" name="first" value="$ranks{$_}">&nbsp;<input type="submit" value="Edit">
<input type="hidden" name="op" value="user_ranks2">
<input type="hidden" name="number" value="$num"></td>
</tr>
</form>
HTML
		$num++;
	}

	print <<HTML;
<form action="$cfg{pageurl}/admin.$cfg{ext}?op=user_ranks2" method="post">
<tr>
<td>More than <input type="text" name="second" size="4" value=""> posts </td>
<td width=50 align="center"><=></td>
<td><input type="text" name="first" value="">&nbsp;<input type="submit" value="Edit">
<input type="hidden" name="op" value="user_ranks2">
<input type="hidden" name="number" value="$num">
</tr>
</form>
</table>
HTML
	print_html($user_data{theme}, "$adm{user_admin} >>> $adm{user_ranks}", 1);
}

# ---------------------------------------------------------------------
# Update user ranks.
# ---------------------------------------------------------------------
sub user_ranks2
{
	my %ranks;
	if (($first !~ /^[ \t]*$/) && ($second !~ /^[ \t]*$/))
	{
		$ranks{$second} = $first;
	}

	my $num = 0;
	sysopen(FH, "$cfg{memberdir}/membergroups.dat", O_RDONLY)
	    or
	    user_error("$err{open_file} $cfg{memberdir}/membergroups.dat. ($!)",
		$user_data{theme});
	while (<FH>)
	{
		chomp $_;
		my @bits = split (/\|/, $_);
		if ($num != $number) { $ranks{$bits[0]} = $bits[1]; }
		$num++;
	}
	close FH;

	sysopen(FH, "$cfg{memberdir}/membergroups.dat",
		O_WRONLY | O_TRUNC | O_CREAT)
	    or user_error(
		"$err{not_writable} $cfg{memberdir}/membergroups.dat. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	foreach (sort keys %ranks) { print FH "$_\|$ranks{$_}\n"; }
	close FH;

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=user_ranks');
}

# ---------------------------------------------------------------------
# Show online users.
# ---------------------------------------------------------------------
sub view_online
{

	# Get online users.
	my $online_users = file2array("$cfg{datadir}/log.dat", 1);

	print_header();
	print_html($user_data{theme}, "$adm{user_ranks} >>> $adm{view_online}");
	print <<HTML;
<table width="100%" class="bg5" border="0" cellspacing="0" cellpadding="0">
<tr>
<td><table width="100%" border="0" cellspacing="1" cellpadding="2">
<tr class="tbl_header">
<td><b>User</b></td>
<td><b>Last online</b></td>
</tr>
HTML

	# Print log.
	foreach (@{$online_users})
	{
		my ($user_name, $user_value) = split (/\|/, $_);

		# Format date.
		my $formatted_date = format_date($user_value);

		# Guest.
		if ($user_name =~ /\./)
		{
			print <<HTML;
<tr class="bg2">
<td>$usr{anonuser} ($user_name)</td>
<td>$formatted_date</td>
</tr>
HTML
		}

		# Registered member.
		else
		{
			my $user_profile =
			    file2array("$cfg{memberdir}/$user_name.dat", 1);

			print <<HTML;
<tr class="bg2">
<td><a href="$cfg{pageurl}/user.$cfg{ext}?op=view_profile;username=$user_name">$user_profile->[1]</a></td>
<td>$formatted_date</td>
</tr>
HTML
		}
	}

	print <<HTML;
</table>
</td>
</tr>
</table>
HTML
	print_html($user_data{theme}, "$adm{user_ranks} >>> $adm{view_online}",
		1);
}

# ---------------------------------------------------------------------
# Edit a user generated page.
# ---------------------------------------------------------------------
sub edit_pages
{

	# Get all pages.
	my $pages = file2array("$cfg{pagesdir}/pages.dat", 1);
	my ($page, $page_name, $page_content) = ('', '', '');

	# Make dropdown menu with all pages.
	if (@$pages)
	{
		foreach (@{$pages})
		{
			my @item = split (/\|/, $_);
			if ($id && $id eq $item[0])
			{
				$page .=
				    qq(<option value="$item[0]" selected>$item[1]</option>\n);
				$page_name = $item[1];
			}
			else {
				$page .= qq(<option value="$item[0]">$item[1]</option>\n);
			}
		}
	}

	print_header();
	print_html($user_data{theme}, "$adm{content_admin} >>> $adm{edit_pages}");
	print <<HTML;
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<table>
<tr>
<td valign="top">Choose a page to edit: </td>
<td valign="top"><select name="id">
<option value=""></option>
$page</select>
<input type="hidden" name="op" value="edit_pages">
<input type="submit" value="$btn{go}"></td>
</tr>
<tr>
<td colspan="2">Or <a href="$cfg{pageurl}/admin.$cfg{ext}?op=edit_pages;id=new">create a new page</a></td>
</tr>
</table>
</form>
<hr size="1">
HTML

	# Print page content.
	if ($id)
	{
		if ($id ne "new")
		{

			# Get page content.
			$page_content = file2scalar("$cfg{pagesdir}/$id.txt", 1);
			$page_content =
			    ($page_content) ? html_to_text($page_content) : '';
		}

		print <<HTML;
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<table>
<tr>
<td>Title:</td>
<td><input type="text" name="title" size="40" value="$page_name"></td>
</tr>
<tr>
<td>Content:</td>
<td><textarea name="message" cols="40" rows="10">$page_content</textarea></td>
</tr>
<tr>
<td colspan="2">You can include this page anywhere by linking to the following URL:<br>
<i>$cfg{pageurl}/pages.$cfg{ext}?id=$id</i></td>
<tr>
<td colspan="2"><input type="hidden" name="op" value="edit_pages2">
<input type="hidden" name="id" value="$id">
<input type="submit" name="modify" value="$btn{modify}">
HTML

		if ($id ne "new")
		{
			print
			    qq(<input type="submit" name="delete" value="$btn{delete}">);
		}

		print <<HTML;
</td>
</tr>
</table>
</form>
HTML
	}
	print_html($user_data{theme}, "$adm{content_admin} >>> $adm{edit_pages}",
		1);
}

# ---------------------------------------------------------------------
# Update a user generated page.
# ---------------------------------------------------------------------
sub edit_pages2
{

	# Modify page. 
	my $pages = file2array("$cfg{pagesdir}/pages.dat", 1);
	if ($modify ne '')
	{

		# Check and format input.
		if (!$title || !$message)
		{
			user_error($err{bad_input}, $user_data{theme});
		}
		$message = html_escape($message);

		# Create a new page.
		if ($id eq "new")
		{

			# Get ID of new page.
			my $files = dir2array($cfg{pagesdir});
			my @files = grep(/\.txt/, @{$files});
			foreach (@files) { $_ =~ s/\.txt//; }
			@files = reverse(sort { $a <=> $b } @files);
			my $post_num = $files[0] || 0;
			$post_num =~ s/\.txt//;
			$id = $post_num + 1;

			# Update page index.
			sysopen(FH, "$cfg{pagesdir}/pages.dat",
				O_WRONLY | O_APPEND | O_CREAT)
			    or user_error(
				"$err{not_writable} $cfg{pagesdir}/pages.dat. ($!)",
				$user_data{theme});
			if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
			print FH "$id|$title\n";
			close(FH);
		}

		# Modify a page.
		else
		{

			# Update page index.
			sysopen(FH, "$cfg{pagesdir}/pages.dat", O_WRONLY | O_TRUNC)
			    or user_error(
				"$err{not_writable} $cfg{pagesdir}/pages.dat. ($!)",
				$user_data{theme});
			if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
			foreach (@{$pages})
			{
				my @item = split (/\|/, $_);
				if ($id == $item[0]) { print FH "$id|$title\n"; }
				else { print FH "$_\n"; }
			}
			close(FH);
		}

		# Update/create page content.
		$id =
		    ($id =~ /^([\w.]+)$/) 
		    ? $1
		    : user_error($err{bad_input}, $user_data{theme});
		sysopen(FH, "$cfg{pagesdir}/$id.txt", O_WRONLY | O_TRUNC | O_CREAT)
		    or user_error("$err{not_writable} $cfg{pagesdir}/$id.txt. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		print FH $message;
		close(FH);

		print $query->redirect(
			-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
			'?op=edit_pages;id=' . $id);
	}

	# Delete page. 
	if ($delete ne '')
	{

		# Update page index.
		sysopen(FH, "$cfg{pagesdir}/pages.dat", O_WRONLY | O_TRUNC)
		    or user_error("$err{not_writable} $cfg{pagesdir}/pages.dat. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		foreach (@{$pages})
		{
			my @item = split (/\|/, $_);
			if ($id != $item[0]) { print FH "$_\n"; }
		}
		close(FH);

		# Remove data file.
		$id =
		    ($id =~ /^([\w.]+)$/) 
		    ? $1
		    : user_error($err{bad_input}, $user_data{theme});
		unlink("$cfg{pagesdir}/$id.txt");

		print $query->redirect(
			-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
			'?op=edit_pages');
	}
}

# ---------------------------------------------------------------------
# Edit a user generated block.
# ---------------------------------------------------------------------
sub edit_blocks
{

	# Get all blocks.
	my $blocks = file2array("$cfg{blocksdir}/blocks.dat", 1);
	my ($block, $block_name, $block_pos, $checked) = ('', '', '', '');
	if (!$id) { $id = ''; }

	if (@$blocks)
	{

		# Make dropdown menu with all blocks, get position and status of block.
		foreach (@{$blocks})
		{
			my @item = split (/\|/, $_);
			if ($id eq $item[0])
			{
				$block .=
				    qq(<option value="$item[0]" selected>$item[1]</option>\n);
				$block_name = $item[1];
				$block_pos  = $item[2];
				$checked    = ($item[3] == 1) ? ' checked' : '';
			}
			if ($id ne $item[0])
			{
				$block .= qq(<option value="$item[0]">$item[1]</option>\n);
			}
		}
	}

	print_header();
	print_html($user_data{theme},
		"$adm{content_admin} >>> $adm{edit_blocks}");
	print <<HTML;
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<table>
<tr>
<td valign="top">Choose a block to edit: </td>
<td valign="top"><select name="id">
<option value=""></option>
$block</select>
<input type="hidden" name="op" value="edit_blocks">
<input type="submit" value="$btn{go}"></td>
</tr>
<tr>
<td colspan="2">Or <a href="$cfg{pageurl}/admin.$cfg{ext}?op=edit_blocks;id=new">create a new block</a></td>
</tr>
</table>
</form>
<hr size="1">
HTML

	# Print links in selected category.
	if ($id)
	{

		# Get block content.
		my $block_content;
		if ($id ne "new")
		{
			$block_content = file2scalar("$cfg{blocksdir}/$id.txt", 1);
		}
		else { $block_name = ''; }

		print <<HTML;
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<table>
<tr>
<td>Position of block:</td>
<td><select name="pos">
HTML

		# Make dropdown menu with possible positions.
		my @pos = ("header", "footer");
		if ($id ne "new")
		{
			foreach (@pos)
			{
				if ($block_pos eq $_)
				{
					print qq(<option value="$_" selected>$_</option>\n);
				}
				else { print qq(<option value="$_">$_</option>\n) }
			}
		}
		else
		{
			foreach (@pos) { print qq(<option value="$_">$_</option>\n); }
		}

		print <<HTML;
</select>
</td>
<tr>
<td>Title:</td>
<td><input type="text" name="title" size="40" value="$block_name"></td>
</tr>
<tr>
<td>Content:</td>
<td><textarea name="message" cols="40" rows="10">$block_content</textarea></td>
</tr>
<tr>
<td>Make block active:</td>
<td><input type="checkbox" name="status"$checked></td>
</tr>
<td colspan="2"><input type="hidden" name="op" value="edit_blocks2">
<input type="hidden" name="id" value="$id">
<input type="submit" name="modify" value="$btn{modify}">
HTML
		if ($id ne "new")
		{
			print
			    qq(<input type="submit" name="delete" value="$btn{delete}">);
		}

		print <<HTML;
</td>
</tr>
</table>
</form>
HTML
	}
	print_html($user_data{theme}, "$adm{content_admin} >>> $adm{edit_blocks}",
		1);
}

# ---------------------------------------------------------------------
# Update a block.
# ---------------------------------------------------------------------
sub edit_blocks2
{

	# Update block.
	my $blocks = file2array("$cfg{blocksdir}/blocks.dat", 1);
	if ($modify ne '')
	{

		# Check and format input.
		if (!$title || !$message || !$pos)
		{
			user_error($err{bad_input}, $user_data{theme});
		}
		$status = ($status eq 'on' || $status == 1) ? 1 : 0;

		# Create a new block.
		if ($id eq "new")
		{

			# Get ID of new page.
			my $files = dir2array($cfg{blocksdir});
			my @files = grep(/\.txt/, @{$files});
			foreach (@files) { $_ =~ s/\.txt//; }
			@files = reverse(sort { $a <=> $b } @files);
			my $post_num = $files[0] || 0;
			$post_num =~ s/\.txt//;
			$id = $post_num + 1;

			# Update page index.
			sysopen(FH, "$cfg{blocksdir}/blocks.dat",
				O_WRONLY | O_APPEND | O_CREAT)
			    or user_error(
				"$err{not_writable} $cfg{blocksdir}/blocks.dat. ($!)",
				$user_data{theme});
			if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
			print FH "$id|$title|$pos|$status\n";
			close(FH);
		}

		# Modify a block.
		else
		{

			# Update page index.
			sysopen(FH, "$cfg{blocksdir}/blocks.dat", O_WRONLY | O_TRUNC)
			    or user_error(
				"$err{not_writable} $cfg{blocksdir}/blocks.dat. ($!)",
				$user_data{theme});
			if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
			foreach (@{$blocks})
			{
				my @item = split (/\|/, $_);
				if ($id == $item[0]) { print FH "$id|$title|$pos|$status\n"; }
				else { print FH "$_\n"; }
			}
			close(FH);
		}

		# Update/create block content.
		$id =
		    ($id =~ /^([\w.]+)$/) 
		    ? $1
		    : user_error($err{bad_input}, $user_data{theme});
		sysopen(FH, "$cfg{blocksdir}/$id.txt", O_WRONLY | O_TRUNC | O_CREAT)
		    or user_error("$err{not_writable} $cfg{blocksdir}/$id.txt. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		print FH $message;
		close(FH);
		print $query->redirect(
			-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
			'?op=edit_blocks;id=' . $id);
	}

	# Delete page. 
	if ($delete ne '')
	{

		# Update page index.
		sysopen(FH, "$cfg{blocksdir}/blocks.dat", O_WRONLY | O_TRUNC)
		    or
		    user_error("$err{not_writable} $cfg{blocksdir}/blocks.dat. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		foreach (@{$blocks})
		{
			my @item = split (/\|/, $_);
			if ($id != $item[0]) { print FH "$_\n"; }
		}
		close(FH);

		# Remove data file.
		$id =
		    ($id =~ /^([\w.]+)$/) 
		    ? $1
		    : user_error($err{bad_input}, $user_data{theme});
		unlink("$cfg{blocksdir}/$id.txt");

		print $query->redirect(
			-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
			'?op=edit_blocks');
	}
}

# ---------------------------------------------------------------------
# Display all quotes.
# ---------------------------------------------------------------------
sub edit_quotes
{

	# Get all available quotes.
	my $quotes = file2array("$cfg{datadir}/quotes.dat", 1);

	print_header();
	print_html($user_data{theme},
		"$adm{content_admin} >>> $adm{edit_quotes}");

	if (@$quotes)
	{
		my $count = scalar @{$quotes};

		print <<HTML;
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post" name="item_list">
<table width="100%" class="bg5" border="0" cellspacing="0" cellpadding="0">
<tr>
<td><table width="100%" border="0" cellspacing="1" cellpadding="2">
<tr class="tbl_header">
<td width="50"><b>ID</b></td>
<td><b>Quote</b></td>
<td><b>By</b></td>
<td><b>Date</b></td>
HTML

		foreach (@{$quotes})
		{
			my @item = split (/\|/, $_);

			# Format date.
			$item[4] = format_date($item[4]);

			print <<HTML;
<tr class="bg2">
<td><input type="checkbox" name="del_item" value=$item[0]> $item[0]</td>
<td><a href="$cfg{pageurl}/admin.$cfg{ext}?op=edit_quote;id=$item[0]">$item[1]</a></td>
<td>$item[2]</td>
<td>$item[4]</td>
</td>
</tr>
HTML
		}

		print <<HTML;
</table></td>
</tr>
</table>
<script language="javascript" type="text/javascript"><!-- 
function checkAll(val) {
al=document.item_list; len=al.elements.length; var i=0;
for (i=0; i<len; i++) { if (al.elements[i].name=='del_item') { al.elements[i].checked=val; } } 
}
//--></script>
<small><a href="javascript:checkAll(1)">Check All</a> - <a href="javascript:checkAll(0)">Clear All</a></small><br>
<input type="hidden" name="op" value="delete_quote">
<input type="submit" value="$btn{delete}">
</form>
<p align="right">$count quotes available.
<hr size="1">
HTML
	}

	# Print panel to create a new category.
	print <<HTML;
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<table width="100%" border="0" cellspacing="1" cellpadding="2">
<tr>
<td colspan="2"><b>Add Quote</b></td>
</tr>
<tr>
<td valign="top">Quote:</td>
<td><textarea name="descr" rows="5" cols="40" maxlength="250"></textarea></td>
</tr>
<tr>
<td>By:</td>
<td><input type="text" name="name" maxlength="50"></td>
</tr>
<tr>
<td colspan="2"><input type="hidden" name="op" value="add_quote">
<input type="submit" value="Create"></td>
</tr>
</table>
</form>
HTML
	print_html($user_data{theme}, "$adm{content_admin} >>> $adm{edit_quotes}",
		1);
}

# ---------------------------------------------------------------------
# Add a quote.
# ---------------------------------------------------------------------
sub add_quote
{

	# Check and format input.
	if (!$name)  { user_error($err{enter_subject},     $user_data{theme}); }
	if (!$descr) { user_error($err{enter_description}, $user_data{theme}); }
	if (length($descr) > 250) { $descr = substr($descr, 0, 250); }
	chomp($name);
	chomp($descr);
	$name  = html_escape($name);
	$descr = html_escape($descr);

	# Get current date.
	my $date = get_date();

	# Get ID of new quote.
	my $quotes = file2array("$cfg{datadir}/quotes.dat", 1);
	my @items = split (/\|/, $quotes->[0]);
	my $qid = $items[0] + 1;

	# Add quote to database.
	sysopen(FH, "$cfg{datadir}/quotes.dat", O_WRONLY | O_TRUNC | O_CREAT);
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	print FH "$qid|$descr|$name|$user_data{uid}|$date\n";
	foreach (@{$quotes}) { print FH "$_\n"; }
	close(FH);

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=edit_quotes');
}

# ---------------------------------------------------------------------
# Delete a quote.
# ---------------------------------------------------------------------
sub delete_quote
{

	# Update quote database.
	my $quotes = file2array("$cfg{datadir}/quotes.dat", 1);
	sysopen(FH, "$cfg{datadir}/quotes.dat", O_WRONLY | O_TRUNC)
	    or user_error("$err{not_writable} $cfg{datadir}/quotes.dat. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	foreach (@{$quotes})
	{
		my @item = split (/\|/, $_);
		if (!(grep { $item[0] eq $_ } $query->param('del_item')))
		{
			print FH "$_\n";
		}
	}
	close(FH);

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=edit_quotes');
}

# ---------------------------------------------------------------------
# Print formular to edit a quote.
# ---------------------------------------------------------------------
sub edit_quote
{

	# Get quote data.
	my $quotes = file2array("$cfg{datadir}/quotes.dat", 1);
	my @item;
	for (@{$quotes})
	{
		@item = split (/\|/, $_);
		if ($item[0] == $id) { last; }
	}
	$item[1] = html_to_text($item[1]);

	# Print panel to create a new category.
	print_header();
	print_html($user_data{theme},
		"$adm{content_admin} >>> $adm{edit_quotes} >>> $adm{edit_quote}");
	print <<HTML;
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<table width="100%" border="0" cellspacing="1" cellpadding="2">
<tr>
<td valign="top">Quote:</td>
<td><textarea name="descr" rows="5" cols="40" maxlength="250">$item[1]</textarea></td>
</tr>
<tr>
<td>By:</td>
<td><input type="text" name="name" maxlength="50" value="$item[2]"></td>
</tr>
<tr>
<td colspan="2"><input type="hidden" name="op" value="edit_quote2">
<input type="hidden" name="id" value="$item[0]">
<input type="submit" value="Update"></td>
</tr>
</table>
</form>
HTML
	print_html($user_data{theme},
		"$adm{content_admin} >>> $adm{edit_quotes} >>> $adm{edit_quote}", 1);
}

# ---------------------------------------------------------------------
# Update a quote.
# ---------------------------------------------------------------------
sub edit_quote2
{

	# Check and format input.
	if (!$name)  { user_error($err{enter_subject},     $user_data{theme}); }
	if (!$descr) { user_error($err{enter_description}, $user_data{theme}); }
	if (length($descr) > 250) { $descr = substr($descr, 0, 250); }
	chomp($name);
	chomp($descr);
	$name  = html_escape($name);
	$descr = html_escape($descr);

	# Update quote.
	my $quotes = file2array("$cfg{datadir}/quotes.dat", 1);
	sysopen(FH, "$cfg{datadir}/quotes.dat", O_WRONLY | O_TRUNC)
	    or user_error("$err{not_writable} $cfg{datadir}/quotes.dat. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	for (@{$quotes})
	{
		my ($qid, $q_text, $q_name, $q_poster, $q_date) = split (/\|/, $_);
		if ($qid == $id) { print FH "$qid|$descr|$name|$q_poster|$q_date\n"; }
		else { print FH "$qid|$q_text|$q_name|$q_poster|$q_date\n"; }
	}
	close(FH);

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=edit_quote;id=' . $id);
}

# ---------------------------------------------------------------------
# Manage site access control list.
# ---------------------------------------------------------------------
sub define_acl
{

	# Get all admins and moderators.
	my %admin_members = ();
	my $members = file2array("$cfg{memberdir}/memberlist.dat", 1);
	foreach (@{$members})
	{
		if ($_ ne 'admin')
		{
			my $member_profile = file2array("$cfg{memberdir}/$_.dat", 1);
			if (($member_profile->[7] eq $usr{admin}
				|| $member_profile->[7] eq $usr{mod}))
			{
				$admin_members{$_} = $member_profile->[1];
			}
		}
	}

	# Generate select list for every area.
	$user_action{admin_index} = 'admin_index';
	my $member_list = '';

	foreach my $area (sort keys %user_action)
	{
		$member_list .= <<HTML;
<tr>
<td class="tbl_row_light" width="100" valign="top"><b>$area</b></td>
<td class="tbl_row_dark"><select name="auth_members-$area" size="3" multiple>
HTML
		foreach my $i (sort keys %admin_members)
		{

			# Check if user has access to area.
			my $area_access_granted = acl_check($i, $area);
			my $selected = ($area_access_granted != 0) ? ' selected' : '';

			$member_list .=
			    qq(<option value="$i"$selected>$admin_members{$i}</option>\n);
		}

		$member_list .= qq(</select></td>\n</tr>\n);
	}

	print_header();
	print_html($user_data{theme}, "$adm{site_admin} >>> $adm{define_acl}");
	print <<HTML;
<p><b>Note:</b> The user with "admin" ID has access to all pages!<br>
<br>
<form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<table width="100%" class="bg5" border="0" cellspacing="0" cellpadding="0">
<tr>
<td><table width="100%" border="0" cellspacing="1" cellpadding="2">
<tr class="tbl_header">
<td><b>Area</b></td>
<td><b>Authorized Admins, Moderators</b></td>
</tr>
$member_list
<tr>
<td colspan="2" class="tbl_row_light"><input type="hidden" name="op" value="define_acl2">
<input type="submit" value="Update">
<input type="reset" value="Reset"></td>
</tr>
</table></td>
</tr>
</table>
</form>
HTML
	print_html($user_data{theme}, "$adm{site_admin} >>> $adm{define_acl}", 1);
}

# ---------------------------------------------------------------------
# Update site access control list.
# ---------------------------------------------------------------------
sub define_acl2
{

	# Process input.
	my %selected_area = ();
	sysopen(FH, "$cfg{datadir}/acl.dat", O_WRONLY | O_TRUNC | O_CREAT)
	    or user_error("$err{not_writable} $cfg{datadir}/quotes.dat. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	foreach ($query->param)
	{

		# Filter unwanted input.
		if ($_ eq 'op') { next; }
		my ($prefix, $input_name) = split (/-/, $_);

		print FH $input_name . "|" . join (' ', $query->param($_)) . "\n";
	}
	close(FH);

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=define_acl');
}

# ---------------------------------------------------------------------
# Approve users.
# ---------------------------------------------------------------------
sub approve_users
{

	# Get all users.
	my $members = file2array("$cfg{memberdir}/memberlist.dat", 1);

	print_header();
	print_html($user_data{theme}, "$adm{user_admin} >>> $adm{approve_users}");
	print <<HTML;
<table width="100%" class="bg5" border="0" cellspacing="0" cellpadding="0">
<tr>
<td><form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<input type="hidden" name="op" value="approve_users2">
<table border="0" width="100%" cellspacing="1" cellpadding="2">
<tr class="tbl_header">
<td width="100%"><b>$msg{usernameC}</b></td>
<td><b>$msg{approved}</b></td>
</tr>
HTML

	# Get member profile.
	foreach (@{$members})
	{
		my $member_profile = file2array("$cfg{memberdir}/$_.dat", 1);
		my $checked = ($member_profile->[14]) ? ' checked' : '';

		print <<HTML;
<tr class="tbl_row_light">
<td width="100%"><a href="$cfg{pageurl}/user.$cfg{ext}?op=view_profile;username=$_">$_</a></td>
<td align="center"><input type="checkbox" name="aprv_$_"$checked></td>
</tr>
HTML
	}

	print <<HTML;
<tr class="tbl_row_light"><td colspan="2" align="center">
<input type="submit" value="$btn{modify}"></td>
</tr>
</table>
</form></td>
</tr>
</table>
HTML

	print_html($user_data{theme}, "$adm{user_admin} >>> $adm{approve_users}",
		1);
}

# ---------------------------------------------------------------------
# Modify user approvals.
# ---------------------------------------------------------------------
sub approve_users2
{

	# Get member profile.
	my $members = file2array("$cfg{memberdir}/memberlist.dat", 1);
	foreach (@{$members})
	{
		my $member_profile = file2array("$cfg{memberdir}/$_.dat", 1);
		my $username       = $member_profile->[1];

		# Is member approved?
		$_ =
		    ($_ =~ /^([\w.]+)$/) 
		    ? $1
		    : user_error($err{bad_input}, $user_data{theme});
		my $approved = $query->param("aprv_$_") || '0';
		$member_profile->[14] =
		    ($approved eq 'on' || $member_profile->[7] eq $usr{admin}) 
		    ? 1
		    : 0;

		sysopen(FH, "$cfg{memberdir}/$_.dat", O_WRONLY | O_TRUNC)
		    or user_error("$err{not_writable} $cfg{memberdir}/$_.dat. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		foreach (@{$member_profile}) { print FH "$_\n"; }
		close(FH);
	}

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=approve_users');
}

# ---------------------------------------------------------------------
# Enable/disable modules.
# ---------------------------------------------------------------------
sub enable_modules
{

	# Get modules and settings.
	my $modules = dir2array($cfg{modulesdir}, 1);
	my $mods_enabled = file2array("$cfg{modulesavedir}/enabled.dat", 1, 1);
	my ($module, $form);

	print_header();
	print_html($user_data{theme},
		"$adm{module_admin} >>> $adm{enable_modules}");
	print <<HTML;
<table width="100%" class="bg5" border="0" cellspacing="0" cellpadding="0">
<tr>
<td><form action="$cfg{pageurl}/admin.$cfg{ext}" method="post">
<input type="hidden" name="op" value="enable_modules2">
<table border="0" width="100%" cellspacing="1" cellpadding="2">
<tr class="tbl_header">
<td width="100%"><b>$msg{module}</b></td>
<td><b>$msg{enabled}</b></td>
</tr>
HTML

	foreach (@{$modules})
	{
		if (m/^\.{1,2}$/) { next; }
		my ($m, $extension) = split (/\./, $_);
		if ($extension) { next; }
		my $checked = (grep(/^$m$/, @{$mods_enabled}) ? ' checked' : '');

		print <<HTML;
<tr class="tbl_row_light">
<td width="100%"><a href="$cfg{pageurl}/modules/$m/index.$cfg{ext}">$m</a></td>
<td align="center"><input type="checkbox" name="enbl_$m"$checked></td>
</tr>
HTML
	}

	print <<HTML;
<tr class="tbl_row_light"><td colspan="2" align="center">
<input type="submit" value="$btn{modify}"></td>
</tr>
</table>
</form></td>
</tr>
</table>
HTML
	print_html($user_data{theme},
		"$adm{module_admin} >>> $adm{enable_modules}", 1);
}

# ---------------------------------------------------------------------
# Commit module enable/disable changes.
# ---------------------------------------------------------------------
sub enable_modules2
{

	# Get modules and settings.
	my $modules = dir2array($cfg{modulesdir}, 1);
	my $mods_enabled = file2array("$cfg{modulesavedir}/enabled.dat", 1, 1);
	my ($module, $enabled);

	# Open file for writing new settings
	sysopen(FH, "$cfg{modulesavedir}/enabled.dat",
		O_WRONLY | O_TRUNC | O_CREAT)
	    or
	    user_error("$err{not_writable} $cfg{modulesavedir}/enabled.dat ($!)",
		$user_data{theme});
	foreach (@{$modules})
	{
		if (m/^\.{1,2}$/) { next; }
		my ($m, $extension) = split (/\./, $_);
		if ($extension) { next; }

		# Update module status.
		$_ =
		    ($_ =~ /^([\w.]+)$/) 
		    ? $1
		    : user_error($err{bad_input}, $user_data{theme});
		$enabled = $query->param("enbl_$module") || '0';

		#if ($enabled) { print FH $m, "\n"; }
		if ($enabled)
		{
			if (!-d "$cfg{modulesavedir}/$module")
			{
				mkdir("$cfg{modulesavedir}/$module", 0777)
				    or
				    user_error("$err{32} $cfg{modulesavedir}/$module. ($!)",
					$user_data{theme});
			}
			print FH $m, "\n";
		}
	}
	close FH;

	print $query->redirect(
		-location => $cfg{pageurl} . '/admin.' . $cfg{ext} .
		'?op=enable_modules');
}

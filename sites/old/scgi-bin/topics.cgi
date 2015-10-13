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
# $Id: topics.cgi,v 1.22 2004/02/28 10:47:14 d3m1g0d Exp $
# =====================================================================

# Load necessary modules.
use strict;
use lib '.';
use yawps;

# Assign global variables.
use vars qw(
    $query
    $op $cat $id $start $article_subject $article_text $state
    %user_data
    %user_action
    );

# Create a new CGI object.
$query = new CGI;

# Get the input.
$op  = $query->param('op')  || '';
$cat = $query->param('cat') || '';
$id  = $query->param('id')  || '';
$start           = $query->param('start');
$article_subject = $query->param('subject');
$article_text    = $query->param('message');
$state           = $query->param('state');

# Get user profile.
%user_data = authenticate();

# Define possible user actions.
%user_action = (
	view_cat     => \&display_cat,
	view_topic   => \&display_topic,
	write_news   => \&write_news,
	write_news2  => \&write_news2,
	comment_news => \&comment_news,
	notify       => \&notify,
	notify2      => \&notify2,
	print_topic  => \&print_topic
    );

# Depending on user action, decide what to do.
if ($user_action{$op}) { $user_action{$op}->(); }
else { display_cats(); }

# ---------------------------------------------------------------------
# Display all topic categories.
# ---------------------------------------------------------------------
sub display_cats
{

	# Get data of all categories.
	my $cats = file2array("$cfg{topicsdir}/cats.dat", 1);
	if (!@$cats) { user_error($err{auth_failure}, $user_data{theme}); }

	print_header();
	print_html($user_data{theme}, $nav{articles});

	print <<HTML;
<table border="0" cellpadding="3" cellspacing="0" width="100%">
<tr>
<td align="center">$msg{list_articles}</td>
</tr>
</table>
<br>
<table border="0" cellpadding="3" cellspacing="0" width="100%">
<tr>
HTML

	# Print categories.
	my $count = 0;
	foreach (@{$cats})
	{
		my @item = split (/\|/, $_);
		print
		    qq(<td align="center" valign="bottom"><a href="$cfg{pageurl}/topics.$cfg{ext}?op=view_cat;cat=$item[1]"><img src="$cfg{imagesurl}/topics/$item[1].gif" border="0" alt="$item[0]"></a><br>\n<b>$item[0]</b></td>\n);
		$count++;
		if ($count == 3)
		{
			print "</tr>\n<tr>\n";
			$count = 0;
		}
	}
	print <<HTML;
<td colspan="3"></td>
</tr>
</table>
HTML
	print_html($user_data{theme}, $nav{articles}, 1);
}

# ---------------------------------------------------------------------
# Display a topic category.
# ---------------------------------------------------------------------
sub display_cat
{

	# Complain about suspicious input.
	if ($cat && $cat =~ /[\.+\/\\\*\?\~\^\$\@\%\`\"\'\&\;\|\<\>\x00-\x1F]/)
	{
		user_error($err{bad_input}, $user_data{theme});
	}

	# Get category data.
	my $cats  = file2array("$cfg{topicsdir}/cats.dat", 1);
	my $datas = file2array("$cfg{topicsdir}/$cat.cat", 1);
	if (!@$cats || !@$datas) { user_error($err{32}, $user_data{theme}); }

	# Get name of category.
	my $cat_name;
	foreach (@{$cats})
	{
		my ($name, $link) = split (/\|/, $_);
		if ($cat eq $link) { $cat_name = $name; }
	}

	print_header();
	print_html($user_data{theme}, "$nav{articles} >>> $cat_name");

	print <<HTML;
<table align="center" border="0" cellpadding="3" cellspacing="0">
<tr>
<td align="center"><b>$msg{articles_in_cat} "$cat_name"</b></td>
</tr>
</table>
<br>
HTML

	print qq(<table border="0" cellpadding="1" cellspacing="0" width="100%">);

	# Initialize page navigation.
	if (!$start) { $start = 0; }

	# Cycle through category and display all entries.
	my $num_shown = 0;
	for (my $i = $start; $i <= $#$datas; $i++)
	{
		my ($num, $title, $user, $date, $comments, $views) =
		    split (/\|/, $datas->[$i]);

		# Get current nick of poster.
		my $user_profile = file2array("$cfg{memberdir}/$user.dat", 1);

		# Comments counter format.
		my $comments_count;
		if ($comments == 1) { $comments_count = 1 . ' ' . $msg{comment}; }
		elsif ($comments == -1) {
			$comments_count = 0 . ' ' . $msg{comments};
		}
		else { $comments_count = $comments . ' ' . $msg{comments}; }
		if (!$views) { $views = 0; }

		# Format date.
		my $formatted_date = format_date($date);

		print <<HTML;
<tr>
<td><img src="$cfg{imagesurl}/urlgo.gif" border="0" alt="">&nbsp;&nbsp;<a href="$cfg{pageurl}/topics.$cfg{ext}?op=view_topic;cat=$cat;id=$num">$title</a></td>
</tr>
<tr>
<td>$msg{written_by} <a href="$cfg{pageurl}/user.$cfg{ext}?op=view_profile;username=$user">$user_profile->[1]</a> $msg{on} $formatted_date</td>
</tr>
<tr>
<td>$cat_name ($views $msg{reads}, $comments_count)</td>
</tr>
<tr>
<td>&nbsp;</td>
</tr>
HTML

		$num_shown++;
		if ($num_shown >= $cfg{max_items_per_page}) { $i = scalar @{$datas}; }
	}

	print "</table>";

	# Make jumpbar.
	if ($num_shown >= $cfg{max_items_per_page})
	{
		print qq(<hr noshade="noshade" size="1">\n$msg{pagesC} );
		my $num_topics = scalar @{$datas};
		my $count      = 0;

		while (($count * $cfg{max_items_per_page}) < $num_topics)
		{
			my $viewc = $count + 1;
			my $strt  = ($count * $cfg{max_items_per_page});
			if ($start == $strt) { print "[$viewc] "; }
			elsif ($strt == 0)
			{
				print
				    qq(<a href="$cfg{pageurl}/topics.$cfg{ext}">$viewc</a> );
			}
			else
			{
				print
				    qq(<a href="$cfg{pageurl}/topics.$cfg{ext}?op=view_cat;cat=$cat;start=$strt">$viewc</a> );
			}
			$count++;
		}
	}

	print
	    qq(<center><a href="$cfg{pageurl}/topics.$cfg{ext}?op=notify;cat=$cat">$nav{subscribe_cat}</a></center>);

	print_html($user_data{theme}, "$nav{articles} >>> $cat_name", 1);
}

# ---------------------------------------------------------------------
# Display a topic.
# ---------------------------------------------------------------------
sub display_topic
{

	# Complain about suspicious input.
	if ($cat && $cat =~ /[\.+\/\\\*\?\~\^\$\@\%\`\"\'\&\;\|\<\>\x00-\x1F]/)
	{
		user_error($err{bad_input}, $user_data{theme});
	}
	if ($id !~ /^[0-9]+$/) { user_error($err{bad_input}, $user_data{theme}); }

	# Get data of all categories.
	my $cats = file2array("$cfg{topicsdir}/cats.dat", 1);
	if (!@$cats) { user_error($err{32}, $user_data{theme}); }

	# Get category full name.
	my $cat_name;
	foreach (@{$cats})
	{
		my ($name, $link) = split (/\|/, $_);
		if ($cat eq $link) { $cat_name = $name; }
	}

	my $articles = file2array("$cfg{topicsdir}/$cat.cat", 1);

	# Increment view counter for this topic.
	$cat =
	    ($cat =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	sysopen(FH, "$cfg{topicsdir}/$cat.cat", O_RDWR)
	    || user_error("$err{not_writable} $cfg{topicsdir}/$cat.cat",
		$user_data{theme});
	flock(FH, LOCK_SH) if $cfg{use_flock};
	foreach (@{$articles})
	{
		my ($num, $subject, $poster, $postdate, $comments, $views) =
		    split (/\|/, $_);
		if ($id eq $num)
		{
			$views++;
			print FH "$num|$subject|$poster|$postdate|$comments|$views\n";
		}
		else {
			print FH "$num|$subject|$poster|$postdate|$comments|$views\n";
		}
	}
	close(FH);

	# Get topic data.
	my $topic_data = file2array("$cfg{articledir}/$id.txt", 1);
	my ($subject, $poster, $date, $message) = split (/\|/, @{$topic_data}[0]);
	my $user_profile = file2array("$cfg{memberdir}/$poster.dat", 1);
	my $formatted_date = format_date($date);
	$message = do_ubbc($message);

	print_header();
	print_html($user_data{theme}, "$nav{articles} >>> $cat_name");

	print <<HTML;
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
<td class="texttitle">$subject</td>
</tr>
<tr>
<td class="textsmall">$cat_name: $formatted_date $msg{by} <a href="$cfg{pageurl}/user.$cfg{ext}?op=view_profile;username=$poster">$user_profile->[1]</a></td>
</tr>
<tr>
<td>&nbsp;</td>
</tr>
<tr>
<td valign="top"><img src="$cfg{imagesurl}/topics/$cat.gif" border="0" align="right" vspace="5" alt="$cat_name">
$message</td>
</tr>
</table>
<br>
<div align="right"><a href="$cfg{pageurl}/topics.$cfg{ext}?op=print_topic;cat=$cat;id=$id" target="_blank">$msg{print_article}</a><br>
<a href="$cfg{pageurl}/topics.$cfg{ext}?op=notify;cat=$cat;id=$id">$nav{subscribe_cat}</a></div>
HTML

	if (scalar @{$topic_data} > 1)
	{
		print
		    qq(<p align="center" class="cat">$msg{article_commentsC}\n<p>$msg{comment_disclaimer});

		# Print the comments.
		foreach (@{$topic_data}[1 .. $#$topic_data])
		{
			($subject, $poster, $date, $message) = split (/\|/, $_);
			my $user_profile = file2array("$cfg{memberdir}/$poster.dat", 1);
			my $formatted_date = format_date($date);
			$message = do_ubbc($message);

			if ($#$topic_data < 1) { }
			else
			{
				print <<HTML;
<hr noshade="noshade" size="1">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
<td class="texttitle">$subject</td>
</tr>
<tr>
<td class="textsmall">$formatted_date $msg{by} <a href="$cfg{pageurl}/user.$cfg{ext}?op=view_profile;username=$poster">$user_profile->[1]</a></td>
</tr>
<tr>
<td>&nbsp;</td>
</tr>
<tr>
<td valign="top">$message</td>
</tr>
</table>
HTML
			}
		}
	}
	else
	{
		print
		    qq(<p align="center" class="cat">$msg{article_commentsC}\n<p>$msg{no_comments});
	}

	# Print the comment post box.
	if ($user_data{uid} ne $usr{anonuser})
	{

		# Print the UBBC panel.
		my $ubbc_panel = print_ubbc_panel();

		print <<HTML;
<hr noshade="noshade" size="1">
<form action="$cfg{pageurl}/topics.$cfg{ext}" method="post" name="creator">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
<td align="center" colspan="2" class="cat">$msg{write_commentC}</td>
</tr>
<tr>
<td><b>$msg{nameC}</b></td>
<td>$user_data{uid}<input type="hidden" name="name" value="$user_data{uid}"><input type="hidden" name="email" value="$user_data{email}"></td>
</tr>
<tr>
<td><b>$msg{subjectC}</b></td>
<td><input type="text" name="subject" size="40" maxlength="50"></td>
</tr>
<tr>
<td><b>$msg{textC}</b></td>
<td><script language="javascript" type="text/javascript"><!--
function addCode(anystr) { document.creator.message.value+=anystr; } 
function showColor(color) { document.creator.message.value+="[color="+color+"][/color]"; }
// --></script>
<textarea name="message" rows="10" cols="40"></textarea></td>
</tr>
<tr>
<td><b>$msg{'156'}</b></td>
<td valign="top">$ubbc_panel</td>
</tr>
<tr>
<td colspan="2"><input type="hidden" name="op" value="comment_news">
<input type="hidden" name="id" value="$id">
<input type="hidden" name="cat" value="$cat">
<input type="submit" value="$btn{post_comment}">
<input type="reset" value="$btn{reset}"></td>
</tr>
</table>
</form>
HTML
	}

	print_html($user_data{theme}, "$nav{articles} >>> $cat_name", 1);
}

# ---------------------------------------------------------------------
# Display a forular to write news.
# ---------------------------------------------------------------------
sub write_news
{

	# Check if user is logged in.
	if ($user_data{uid} eq $usr{anonuser}
		|| $cfg{enable_user_articles} == 0 && $user_data{uid} ne $usr{admin})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	# Get data of all categories.
	my $cats = file2array("$cfg{topicsdir}/cats.dat", 1);
	if (!@$cats) { user_error($err{32}, $user_data{theme}); }

	# Print list of available categories
	my $cats_select;
	foreach (@{$cats})
	{
		my ($name, $link) = split (/\|/, $_);
		$cats_select .= qq(<option value="$link">$name</option>\n);
	}

	# Print the UBBC panel.
	my $ubbc_panel = print_ubbc_panel();

	# Check if news should be verified by admins.
	my $approved = 0;
	if ($user_data{sec_level} eq $usr{admin}) { $approved = 1; }

	print_header();
	print_html($user_data{theme}, $nav{write_article});

	print <<HTML;
<form action="$cfg{pageurl}/topics.$cfg{ext}" method="post" name="creator">
<table border="0" cellspacing="1">
<tr>
<td><b>$msg{nameC}</b></td>
<td>$user_data{uid}</td>
</tr>
<tr>
<td><b>$msg{categoryC}</b></td>
<td><select name="cat">$cats_select</select></td>
</tr>
<tr>
<td><b>$msg{subjectC}</b></td>
<td><input type="text" name="subject" size="40" maxlength="50"></td>
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
<textarea name="message" rows="10" cols="40"></textarea></td>
</tr>
<tr>
<td><b>$msg{'156'}</b></td>
<td valign="top">$ubbc_panel</td>
</tr>
<tr>
<td colspan="2"><input type="hidden" name="op" value="write_news2">
<input type="hidden" name="approved" value="$approved">
<input type="submit" value="$btn{send_message}">
<input type="reset" value="$btn{reset}"></td>
</tr>
</table>
</form>
HTML

	print_html($user_data{theme}, $nav{write_article}, 1);
}

# ---------------------------------------------------------------------
# Add news.
# ---------------------------------------------------------------------
sub write_news2
{

	# Check if user is logged in.
	if ($user_data{uid} eq $usr{anonuser}
		|| $cfg{enable_user_articles} == 0 && $user_data{uid} ne $usr{admin})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	# Check input.
	if (!$article_subject)
	{
		user_error($err{enter_subject}, $user_data{theme});
	}
	if (!$article_text) { user_error($err{enter_text}, $user_data{theme}); }

	# Get all available articles.
	my $files = dir2array($cfg{articledir});
	my @files = grep(/\.txt/, @{$files});
	foreach (@files) { $_ =~ s/\.txt//; }
	@files = reverse(sort { $a <=> $b } @files);

	# Get ID of new article.
	my $mid = $files[0] || 0;
	if ($mid) { $mid =~ s/\.txt//; }
	$mid++;

	# Format input.
	chomp($article_subject);
	chomp($article_text);
	$article_subject = html_escape($article_subject);
	$article_text    = html_escape($article_text);

	# Get date.
	my $date = get_date();

	# Make article viewable to public.
	if ($user_data{sec_level} eq $usr{admin})
	{
		my $articles = file2array("$cfg{topicsdir}/$cat.cat", 1);
		$cat =
		    ($cat =~ /^([\w.]+)$/) 
		    ? $1
		    : user_error($err{bad_input}, $user_data{theme});
		$mid =
		    ($mid =~ /^([\w.]+)$/) 
		    ? $1
		    : user_error($err{bad_input}, $user_data{theme});

		# Save headline to category database.
		sysopen(FH, "$cfg{topicsdir}/$cat.cat", O_WRONLY | O_TRUNC | O_CREAT)
		    or user_error("$err{open_file} $cfg{topicsdir}/$cat.cat. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		print FH "$mid|$article_subject|$user_data{uid}|$date|0|0\n";
		if ($articles)
		{
			foreach (@{$articles}) { print FH "$_\n"; }
		}
		close(FH);

		# Save article text.
		sysopen(FH, "$cfg{articledir}/$mid.txt", O_WRONLY | O_TRUNC | O_CREAT)
		    or
		    user_error("$err{not_writable} $cfg{articledir}/$mid.txt. ($!)",
			$user_data{theme});
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		print FH "$article_subject|$user_data{uid}|$date|$article_text\n";
		close(FH);

		# Update article count for user.
		my $user_profile =
		    file2array("$cfg{memberdir}/$user_data{uid}.dat", 1);
		$user_profile->[11]++;

		$user_data{uid} =
		    ($user_data{uid} =~ /^([\w.]+)$/) 
		    ? $1
		    : user_error($err{bad_input}, $user_data{theme});
		sysopen(FH, "$cfg{memberdir}/$user_data{uid}.dat",
			O_WRONLY | O_TRUNC);
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		foreach (@{$user_profile}) { print FH "$_\n"; }
		close(FH);

		# Export to RDF-file.
		rdf_export();

		# Notify users, who are watching this category.
		if (-r "$cfg{topicsdir}/$cat.mail")
		{
			notify_users("$cfg{topicsdir}/$cat.mail",
				"$cfg{pageurl}/topics.$cfg{ext}?op=view_topic;cat=$cat;id=$mid"
			    );
		}
	}

	# Store article in the pending articles database.
	else
	{

		# Get all available pending articles.
		my $articles = file2array("$cfg{topicsdir}/newarticles.dat", 1);
		my $num      = 1;

		# Get ID for the new article.
		if ($articles)
		{
			($num, undef, undef, undef, undef, undef) =
			    split (/\|/, $articles->[0]);
			$num++;
		}

		# Update pending article database.
		sysopen(FH, "$cfg{topicsdir}/newarticles.dat",
			O_WRONLY | O_TRUNC | O_CREAT);
		if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
		print FH
		    "$num|$cat|$article_subject|$user_data{uid}|$date|$article_text\n";
		foreach (@{$articles}) { print FH "$_\n"; }
		close(FH);
	}

	# Print success info.
	print_header();
	print_html($user_data{theme}, $nav{articles});

	print '<b>' . $nav{success} . "</b><br>\n" . $inf{submission_saved};

	print_html($user_data{theme}, $nav{write_article}, 1);
}

# ---------------------------------------------------------------------
# Add comments.
# ---------------------------------------------------------------------
sub comment_news
{

	# Check if user is logged in.
	if ($user_data{uid} eq $usr{anonuser})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	# Check input.
	if (!$article_subject)
	{
		user_error($err{enter_subject}, $user_data{theme});
	}
	if (!$article_text) { user_error($err{enter_text}, $user_data{theme}); }

	# Get category topics.
	my $topics = file2array("$cfg{topicsdir}/$cat.cat", 1);

	# Format input.
	chomp($article_subject);
	chomp($article_text);
	$article_subject = html_escape($article_subject);
	$article_text    = html_escape($article_text);

	# Get date.
	my $date = get_date();

	# Update category database.
	$cat =
	    ($cat =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	sysopen(FH, "$cfg{topicsdir}/$cat.cat", O_WRONLY | O_TRUNC | O_CREAT)
	    || error("$err{not_writable} $cfg{topicsdir}/$cat.dat",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	foreach (@{$topics})
	{
		my ($mid, $subject, $poster, $postdate, $comments, $views) =
		    split (/\|/, $_);

		if ($mid eq $id)
		{
			$comments++;
			print FH "$mid|$subject|$poster|$postdate|$comments|$views\n";
		}
		else {
			print FH "$mid|$subject|$poster|$postdate|$comments|$views\n";
		}
	}
	close(FH);

	# Save comment to article database.
	$id =
	    ($id =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	sysopen(FH, "$cfg{articledir}/$id.txt", O_WRONLY | O_APPEND | O_CREAT)
	    || error("$err{not_writable} $cfg{articledir}/$id.txt",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	print FH "$article_subject|$user_data{uid}|$date|$article_text\n";
	close(FH);

	# Update user's comments count.
	my $user_profile = file2array("$cfg{memberdir}/$user_data{uid}.dat", 1);
	$user_profile->[12]++;

	$user_data{uid} =
	    ($user_data{uid} =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	sysopen(FH, "$cfg{memberdir}/$user_data{uid}.dat", O_WRONLY | O_TRUNC);
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	foreach (@{$user_profile}) { print FH "$_\n"; }
	close(FH);

	# Notify users, who are watching this topic.
	if (-r "$cfg{articledir}/$id.mail")
	{
		notify_users("$cfg{articledir}/$id.mail",
			"$cfg{pageurl}/topics.$cfg{ext}?op=view_topic;cat=$cat;id=$id");
	}

	print $query->redirect(
		-location => $cfg{pageurl} . '/topics.' . $cfg{ext} .
		'?op=view_topic;cat=' . $cat . ';id=' . $id);
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

	# Get data of all categories.
	my $cats = file2array("$cfg{topicsdir}/cats.dat", 1);
	if (!@$cats) { user_error($err{32}, $user_data{theme}); }

	# Get name of category.
	my $cat_name;
	foreach (@{$cats})
	{
		my ($name, $link) = split (/\|/, $_);
		if ($cat eq $link) { $cat_name = $name; }
	}

	my $subcribe_links;
	if ($id eq '')
	{

		# Check if user has subscribed to this category.
		my $subscribed = 0;
		my $mails = file2array("$cfg{topicsdir}/$cat.mail", 1);
		if ($mails)
		{
			foreach (@{$mails})
			{
				$subscribed = ($user_data{email} eq $_) ? 1 : 0;
			}
		}

		$subcribe_links =
		    ($subscribed)
		    ? qq($msg{ask_reply_notify}<br>\n<b><a href="$cfg{pageurl}/topics.$cfg{ext}?op=view_cat;cat=$cat">$nav{yes}</a> - <a href="$cfg{pageurl}/topics.$cfg{ext}?op=notify2;cat=$cat;state=0">$nav{no}</a></b>)
		    : qq($msg{ask_reply_notify}<br>\n<b><a href="$cfg{pageurl}/topics.$cfg{ext}?op=notify2;cat=$cat;state=1">$nav{yes}</a> - <a href="$cfg{pageurl}/topics.$cfg{ext}?op=view_cat;cat=$cat">$nav{no}</a></b>);
	}
	else
	{

		# Check if user has subscribed to this topic.
		my $subscribed = 0;
		my $mails = file2array("$cfg{articledir}/$id.mail", 1);
		if ($mails)
		{
			foreach (@{$mails})
			{
				$subscribed = ($user_data{email} eq $_) ? 1 : 0;
			}
		}

		$subcribe_links =
		    ($subscribed)
		    ? qq($msg{ask_reply_notify}<br>\n<b><a href="$cfg{pageurl}/topics.$cfg{ext}?op=view_cat;cat=$cat;id=$id">$nav{yes}</a> - <a href="$cfg{pageurl}/topics.$cfg{ext}?op=notify2;cat=$cat;id=$id;state=0">$nav{no}</a></b>)
		    : qq($msg{ask_reply_notify}<br>\n<b><a href="$cfg{pageurl}/topics.$cfg{ext}?op=notify2;cat=$cat;id=$id;state=1">$nav{yes}</a> - <a href="$cfg{pageurl}/topics.$cfg{ext}?op=view_cat;cat=$cat;id=$id">$nav{no}</a></b>)
	}

	print_header();
	print_html($user_data{theme}, "$nav{articles} >>> $cat_name");
	print $subcribe_links;
	print_html($user_data{theme}, "$nav{articles} >>> $cat_name", 1);
}

# ---------------------------------------------------------------------
# Update news notification.
# ---------------------------------------------------------------------
sub notify2
{

	# Check if user has permissions to access this area.
	if ($user_data{sec_level} eq $usr{anonuser})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	# Update subscriber list.
	if ($id eq '')
	{
		$cat =
		    ($cat =~ /^([\w.]+)$/) 
		    ? $1
		    : user_error($err{bad_input}, $user_data{theme});
		update_subscriptions("$cfg{topicsdir}/$cat.mail", $user_data{email},
			$state);
		print $query->redirect(
			-location => $cfg{pageurl} . '/topics.' . $cfg{ext} .
			'?op=view_cat;cat=' . $cat);
	}
	else
	{
		$id =
		    ($id =~ /^([\w.]+)$/) 
		    ? $1
		    : user_error($err{bad_input}, $user_data{theme});
		update_subscriptions("$cfg{articledir}/$id.mail", $user_data{email},
			$state);
		print $query->redirect(
			-location => $cfg{pageurl} . '/topics.' . $cfg{ext} .
			'?op=view_cat;cat=' . $cat . ';id=' . $id);
	}
}

# ---------------------------------------------------------------------
# Display a printerfriendly version of an article.
# ---------------------------------------------------------------------
sub print_topic
{

	# Complain about suspicious input.
	if ($cat && $cat =~ /[\.+\/\\\*\?\~\^\$\@\%\`\"\'\&\;\|\<\>\x00-\x1F]/)
	{
		user_error($err{bad_input});
	}
	if ($id !~ /^[0-9]+$/) { user_error($err{bad_input}); }

	# Get article data.
	my $article = file2array("$cfg{articledir}/$id.txt", 1);
	if (!$article) { user_error($err{bad_input}, $user_data{theme}); }
	my ($title, $username, $date, $message) = split (/\|/, $article->[0]);

	# Get current nick of poster.
	my $user_profile = file2array("$cfg{memberdir}/$username.dat", 1);

	# Format text.
	$message = do_ubbc($message);
	my $formatted_date = format_date($date);

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
<h3>$title</h3>
<b>$msg{written_by} $user_profile->[1] ($formatted_date)</b><br>
<p>$message</p>
HTML

	if (scalar @{$article} > 1)
	{
		print qq(<p align="center"><b>$msg{article_commentsC}</b></p>);

		# Print the comments.
		foreach (@{$article}[1 .. $#$article])
		{
			my @item = split (/\|/, $_);

			# Get current nick of poster.
			my $user_profile2 = file2array("$cfg{memberdir}/$item[1].dat", 1);
			$user_profile2->[3] = do_ubbc($user_profile2->[3]);
			$item[2] = format_date($item[2]);

			print <<HTML;
<hr size="1" width="100%">
<h3>$item[0]</h3>
<b>$msg{written_by} $user_profile2->[1] ($item[2])</b><br>
<p>$item[3]</p>
HTML
		}
	}
	print "</body>\n</html>\n";
}

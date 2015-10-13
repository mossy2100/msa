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
# $Id: links.cgi,v 1.20 2004/02/28 09:10:06 d3m1g0d Exp $
# =====================================================================

# Load necessary modules.
use strict;
use lib '.';
use yawps;

# Assign global variables.
use vars qw(
    $query
    $op $cat $id $start $link_title $link_url $link_desc $link_rate $state
    @votes
    %user_data
    %user_action
    );

# Create a new CGI object.
$query = new CGI;

# Get the input.
$op  = $query->param('op')  || '';
$cat = $query->param('cat') || '';
$id  = $query->param('id');
$start      = $query->param('start');
$link_title = $query->param('title');
$link_url   = $query->param('url');
$link_desc  = $query->param('desc');
$link_rate  = $query->param('rate');
$state      = $query->param('state');

# Get user profile.
%user_data = authenticate();

# Check cookie.
@votes = $query->cookie('yawps_link');

# Define possible user actions.
%user_action = (
	view_cat    => \&display_cat,
	view_link   => \&display_link,
	add_link    => \&add_link,
	add_link2   => \&add_link2,
	report_link => \&report_link,
	rate_link   => \&rate_link,
	rate_link2  => \&rate_link2,
	notify      => \&notify,
	notify2     => \&notify2
    );

# Depending on user action, decide what to do.
if ($user_action{$op}) { $user_action{$op}->(); }
else { display_cats(); }

# ---------------------------------------------------------------------
# Display all links categories.
# ---------------------------------------------------------------------
sub display_cats
{

	# Get data of all categories.
	my $cats = file2array("$cfg{linksdir}/linkcats.dat", 1);

	print_header();
	print_html($user_data{theme}, $nav{links});

	print <<HTML;
<table align="center" border="0" cellpadding="3" cellspacing="0">
<tr>
HTML

	# Cycle through the categories.
	my $total_links_count = 0;
	my $count             = 0;

	if ($cats)
	{
		foreach (@{$cats})
		{
			my @item = split (/\|/, $_);
			my $links = file2array("$cfg{linksdir}/$item[1].dat", 1);

			if ($links)
			{
				my $links_count = scalar @{$links};

				print <<HTML;
<td valign="top" width="50%">
<table>
<tr>
<td colspan="2"><b><a href="$cfg{pageurl}/links.$cfg{ext}?op=view_cat;cat=$item[1]">$item[0]</a></b> <i>($links_count)</i></td>
</tr>
<tr>
<td width="20">&nbsp;</td>
<td>$item[2]</td>
</tr>
</table>
</td>
HTML
				$total_links_count = $total_links_count + $links_count;
			}
			else
			{
				print <<HTML;
<td valign="top" width="50%">
<table>
<tr>
<td colspan="2"><b>$item[0]</b> <i>(0)</i></td>
</tr>
<tr>
<td width="20">&nbsp;</td>
<td>$item[2]</td>
</tr>
</table>
</td>
HTML
			}

			$count++;
			if ($count == 2)
			{
				print "</tr>\n<tr>";
				$count = 0;
			}
		}

		my $message =
		    ($total_links_count == 1)
		    ? $msg{there_is} . " <b>1</b> " . $msg{link_in_db}
		    : $msg{there_are} . " <b>" . $total_links_count . "</b> " .
		    $msg{links_in_db};

		print <<HTML;
<td></td>
</tr>
</table>
<br>
<table align="center" border="0" cellpadding="3" cellspacing="0">
<tr>
<td align="center">$message</td>
</tr>
<tr>
<td></td>
</tr>
HTML

		if ($user_data{uid} ne $usr{anonuser})
		{
			print <<HTML;
<tr>
<td align="center"><a href="$cfg{pageurl}/links.$cfg{ext}?op=add_link">$nav{add_link}</a></td>
</tr>
HTML
		}
	}

	print "</table>\n";

	print_html($user_data{theme}, $nav{links}, 1);
}

# ---------------------------------------------------------------------
# Display a link category.
# ---------------------------------------------------------------------
sub display_cat
{

	# Complain about suspicious input.
	if ($cat && $cat =~ /[\.+\/\\\*\?\~\^\$\@\%\`\"\'\&\;\|\<\>\x00-\x1F]/)
	{
		user_error($err{bad_input});
	}

	# Get data of all categories.
	my $cats = file2array("$cfg{linksdir}/linkcats.dat", 1);
	if (!@$cats) { user_error($err{bad_input}, $user_data{theme}); }

	# Get category name.
	my $linkcat_name;
	foreach (@{$cats})
	{
		my ($name, $link, undef) = split (/\|/, $_);
		if ($cat eq $link) { $linkcat_name = $name; }
	}

	# Get data of current category.	
	my $curcat = file2array("$cfg{linksdir}/$cat.dat", 1);

	print_header();
	print_html($user_data{theme}, "$nav{links} >>> $linkcat_name");

	print qq(<table border="0" cellpadding="5" cellspacing="0" width="100%">);

	# Initialize page navigation.
	if (!$start) { $start = 0; }

	# Cycle through category and display all entries.
	my $num_shown = 0;
	for (my $i = $start; $i < @{$curcat}; $i++)
	{
		my (
			$lid,        $name, $url,   $desc, $date,
			$linkposter, $hits, $votes, $rate
		    )
		    = split (/\|/, $curcat->[$i]);

		my $average_rate = 0;
		if ($votes && $rate)
		{
			$average_rate = sprintf("%.2f", ($rate / $votes));
		}

		# Get last poster's name.
		my $user_profile = file2array("$cfg{memberdir}/$linkposter.dat", 1);

		# Format date.
		my $formatted_date = format_date($date);

		print <<HTML;
<tr>
<td><img src="$cfg{imagesurl}/urlgo.gif" border="0" alt="">&nbsp;&nbsp;<b><a href="$cfg{pageurl}/links.$cfg{ext}?op=view_link;cat=$cat;id=$lid" target="_blank">$name</a></b><br>
$msg{descriptionC} $desc<br>
$msg{written_by} <a href="$cfg{pageurl}/user.$cfg{ext}?op=view_profile;username=$linkposter">$user_profile->[1]</a><br>
$msg{submitted_onC} $formatted_date<br>
$msg{hits}: $hits, $msg{votes}: $votes, $msg{average_rating}: $average_rate<br>
<small><a href="$cfg{pageurl}/links.$cfg{ext}?op=rate_link;cat=$cat;id=$lid">$nav{rate_link}</a> | <a href="$cfg{pageurl}/links.$cfg{ext}?op=report_link;cat=$cat;id=$lid">$nav{link_defect}</a></small></td>
</tr>
<tr>
<td>&nbsp;</td>
</tr>
HTML
		$num_shown++;
		if ($num_shown >= $cfg{max_items_per_page}) { last; }
	}

	print "</table>";

	# Make jumpbar.
	if ($num_shown >= $cfg{max_items_per_page})
	{
		print qq(<hr noshade="noshade" size="1">\n$msg{pagesC} );
		my $num_links = scalar @{$curcat};

		my $count = 0;
		while (($count * $cfg{max_items_per_page}) < $num_links)
		{
			my $viewc = $count + 1;
			my $strt  = ($count * $cfg{max_items_per_page});
			if ($start == $strt) { print "[$viewc] "; }
			else
			{
				print
				    qq(<a href="$cfg{pageurl}/links.$cfg{ext}?op=view_cat;cat=$cat;start=$strt">$viewc</a> );
			}
			$count++;
		}
	}

	print <<HTML;
<br>
<table align="center" border="0" cellpadding="3" cellspacing="0">
HTML

	# Print add-link link if user has perms.
	if ($user_data{uid} ne $usr{anonuser})
	{
		print <<HTML;
<tr>
<td align="center"><a href="$cfg{pageurl}/links.$cfg{ext}">$nav{categories}</a> | <a href="$cfg{pageurl}/links.$cfg{ext}?op=add_link;cat=$cat">$nav{add_link}</a></td>
</tr>
HTML
	}
	else
	{
		print <<HTML;
<tr>
<td align="center"><a href="$cfg{pageurl}/links.$cfg{ext}">$nav{categories}</a></td>
</tr>
HTML
	}
	print
	    qq(</table>\n<p align="center"><a href="$cfg{pageurl}/links.$cfg{ext}?op=notify;cat=$cat">$nav{subscribe_cat}</a></center>);

	print_html($user_data{theme}, "$nav{links} >>> $linkcat_name", 1);
}

# ---------------------------------------------------------------------
# Redirect to link URL.
# ---------------------------------------------------------------------
sub display_link
{

	# Get data of current category.
	my $curcat = file2array("$cfg{linksdir}/$cat.dat", 1);

	# Get URL of selected link and increment hits counter.
	my $location;

	$cat =
	    ($cat =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	sysopen(FH, "$cfg{linksdir}/$cat.dat", O_WRONLY | O_TRUNC)
	    or user_error("$err{not_writable} $cfg{linksdir}/$cat.dat. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	foreach (@{$curcat})
	{
		my (
			$lid,        $name, $url,   $desc, $date,
			$linkposter, $hits, $votes, $rate
		    )
		    = split (/\|/, $_);
		if ($lid == $id)
		{
			$location = $url;
			$hits     = $hits + 1;

			print FH
			    "$lid|$name|$url|$desc|$date|$linkposter|$hits|$votes|$rate\n";
		}
		else { print FH "$_\n"; }
	}
	close(FH);

	# Redirect to URL.
	print $query->redirect(-location => $location);
}

# ---------------------------------------------------------------------
# Display formular to add a link.
# ---------------------------------------------------------------------
sub add_link
{

	# Check if user is logged in.
	if ($user_data{uid} eq $usr{anonuser})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	# Get link categories.
	my $cats = file2array("$cfg{linksdir}/linkcats.dat", 1);

	# Get available link categories.
	my $select;
	foreach (@{$cats})
	{
		my @item = split (/\|/, $_);
		my $selected = ($cat eq $item[1]) ? 'selected' : '';

		$select .= qq(<option $selected value="$item[1]">$item[0]</option>\n);
	}

	print_header();
	print_html($user_data{theme}, "$nav{links} >>> $nav{add_link}");

	print <<HTML;
<form method="post" action="$cfg{pageurl}/links.$cfg{ext}">
<table border="0" cellpadding="5" cellspacing="0">
<tr>
<td><b>$msg{website_nameC}</b></td>
<td><input type="text" name="title" size="40" maxlength="100"></td>
</tr>
<tr>
<td><b>$msg{website_urlC}</b></td>
<td><input type="text" name="url" size="40" maxlength="100" value="http://"></td>
</tr>
<tr>
<td><b>$msg{categoryC}</b></td>
<td><select name="cat">$select</select><td>
</tr>
<tr>
<td valign="top"><b>$msg{descriptionC}</b></td>
<td><textarea name="desc" cols="40" rows="5" maxlength="255"></textarea><br>$msg{'255_chars_max'}</td>
</tr>
<tr>
<td colspan="2"><input type="hidden" name="op" value="add_link2">
<input type="submit" value="$btn{add_link}">
<input type="reset" value="$btn{reset}"></td>
</tr>
</table>
</form>
HTML

	print_html($user_data{theme}, "$nav{links} >>> $nav{add_link}", 1);
}

# ---------------------------------------------------------------------
# Add a link.
# ---------------------------------------------------------------------
sub add_link2
{

	# Check if user is logged in.
	if ($user_data{uid} eq $usr{anonuser})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	if (!$link_title) { user_error($err{enter_subject}, $user_data{theme}); }
	if (!$link_url)   { user_error($err{enter_url},     $user_data{theme}); }
	if (!$link_desc)
	{
		user_error($err{enter_description}, $user_data{theme});
	}

	# Format input.
	if (length($link_desc) > 255) { $link_desc = substr($link_desc, 0, 255); }
	chomp($link_desc);
	chomp($link_title);
	chomp($link_url);
	$link_title = html_escape($link_title);
	$link_desc  = html_escape($link_desc);

	# Get current date.
	my $date = get_date();

	# Get category data.
	my $curcat = file2array("$cfg{linksdir}/$cat.dat", 1);

	# Get ID of new link.
	my $lid = scalar @{$curcat} + 1;

	# Add link to database.
	$cat =
	    ($cat =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	sysopen(FH, "$cfg{linksdir}/$cat.dat", O_WRONLY | O_TRUNC | O_CREAT);
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	print FH
	    "$lid|$link_title|$link_url|$link_desc|$date|$user_data{uid}|0|0|0\n";
	foreach (@{$curcat}) { print FH "$_\n"; }
	close(FH);

	# Notify users, who are watching this category.
	if (-r "$cfg{linksdir}/$cat.mail")
	{
		notify_users("$cfg{linksdir}/$cat.mail",
			"$cfg{pageurl}/links.$cfg{ext}?op=view_cat;cat=$cat");
	}

	print $query->redirect(
		-location => $cfg{pageurl} . '/links.' . $cfg{ext} .
		'?op=view_cat;cat=' . $cat);
}

# ---------------------------------------------------------------------
# Report a link to site admin.
# ---------------------------------------------------------------------
sub report_link
{

	# Check if user is logged in.
	if ($user_data{uid} eq $usr{anonuser})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	# Send the email to recipient.
	my $subject = "Broken link: " . $cfg{pagename};
	my $message = <<HTML;
$user_data{nick} $inf{or} $ENV{REMOTE_ADDR} has reported a possible broken link:
$cfg{pageurl}/links.$cfg{ext}?op=view_link;cat=$cat;id=$id
Please take appropriate action.

HTML
	send_email($user_data{email}, $cfg{webmaster_email}, $subject, $message);

	print_header();
	print_html($user_data{theme}, "$nav{links} >>> Report broken link");

	print
	    "Thank you for your submission. It will be verified by the site administrator as soon as possible.";

	print_html($user_data{theme}, "$nav{links} >>> Report broken link", 1);
}

# ---------------------------------------------------------------------
# Print formular to rate a link.
# ---------------------------------------------------------------------
sub rate_link
{

	# Check if user is logged in.
	if ($user_data{uid} eq $usr{anonuser})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	# Get data of current category.
	my $curcat = file2array("$cfg{linksdir}/$cat.dat", 1);

	# Get link data.
	my ($lid, $name, $url, $desc, $date, $linkposter, $hits, $votes, $rate);
	foreach (@{$curcat})
	{
		($lid,           $name, $url,   $desc, $date,
		    $linkposter, $hits, $votes, $rate)
		    = split (/\|/, $_);
		if ($lid == $id) { last; }
	}

	my $average_rate = 0;
	if ($votes && $rate) {
		$average_rate = sprintf("%.2f", ($rate / $votes));
	}

	# Format date.
	my $formatted_date = format_date($date);

	# Print rating scale.
	my $rate_option;
	for (my $i = 10; $i > 0; $i--)
	{
		$rate_option .= qq(<option value="$i">$i</option>\n);
	}

	print_header();
	print_html($user_data{theme}, "$nav{links} >>> $nav{rate_link}");

	print <<HTML;
<form method="post" action="$cfg{pageurl}/links.$cfg{ext}">
<table border="0" cellpadding="2" cellspacing="0">
<tr>
<td><b>$msg{website_nameC}</b></td>
<td>$name</td>
</tr>
<tr>
<td><b>$msg{website_urlC}</b></td>
<td><a href="$cfg{pageurl}/links.$cfg{ext}?op=view_link;cat=$cat;id=$lid" target="_blank">$url</a></td>
</tr>
<tr>
<td valign="top"><b>$msg{descriptionC}</b></td>
<td>$desc</td>
</tr>
<tr>
<td valign="top"><b>$msg{submitted_onC}</b></td>
<td>$formatted_date</td>
</tr>
<tr>
<td valign="top"><b>$msg{hits}</b></td>
<td>$hits</td>
</tr>
<tr>
<td valign="top"><b>$msg{votes}:</b></td>
<td>$votes</td>
</tr>
<tr>
<td valign="top"><b>$msg{average_rating}:</b></td>
<td>$average_rate</td>
</tr>
<tr>
<td colspan="2"><hr size="1">
$msg{rate_linkC}
<select name="rate">$rate_option</select>
</td>
</tr>
<tr>
<td colspan="2"><input type="hidden" name="op" value="rate_link2">
<input type="hidden" name="cat" value="$cat">
<input type="hidden" name="id" value="$lid">
<input type="submit" value="$btn{rate_link}"></td>
</tr>
</table>
</form>
HTML

	print_html($user_data{theme}, "$nav{links} >>> $nav{rate_link}", 1);
}

# ---------------------------------------------------------------------
# Rate a link.
# ---------------------------------------------------------------------
sub rate_link2
{

	# Check if user is logged in.
	if ($user_data{uid} eq $usr{anonuser})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	# Check if user has already voted.
	my $this_link = $cat . "_" . $id;
	for (0 .. $#votes)
	{
		my @fields = split (/\|/, $votes[$_]);
		foreach (@fields)
		{
			if ($_ eq $this_link)
			{
				user_error($err{already_voted}, $user_data{theme});
			}
		}
	}

	# If not set the cookie and add rating.
	push (my @new_votes, join ("|", @votes, $this_link));
	my $cookie_link = $query->cookie(
		-name    => 'yawps_link',
		-value   => @new_votes,
		-path    => '/',
		-expires => $cfg{cookie_expire}
	    );

	# Get data of current category.
	my $curcat = file2array("$cfg{linksdir}/$cat.dat", 1);

	# Get URL of selected link and increment hits counter.
	$cat =
	    ($cat =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	sysopen(FH, "$cfg{linksdir}/$cat.dat", O_WRONLY | O_TRUNC)
	    or user_error("$err{not_writable} $cfg{linksdir}/$cat.dat. ($!)",
		$user_data{theme});
	if ($cfg{use_flock}) { flock(FH, LOCK_EX); }
	foreach (@{$curcat})
	{
		my (
			$lid,        $name, $url,   $desc, $date,
			$linkposter, $hits, $votes, $rate
		    )
		    = split (/\|/, $_);
		if ($lid == $id)
		{
			$votes = $votes + 1;
			$rate  = $rate + $link_rate;

			print FH
			    "$lid|$name|$url|$desc|$date|$linkposter|$hits|$votes|$rate\n";
		}
		else { print FH "$_\n"; }
	}
	close(FH);

	# Display a success message.
	print_header($cookie_link);
	print_html($user_data{theme}, "$nav{links} >>> $nav{rate_link}");

	print $inf{rating_message};

	print_html($user_data{theme}, "$nav{links} >>> $nav{rate_link}", 1);
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

	# Get list of user's watching this category.
	my $mails = file2array("$cfg{linksdir}/$cat.mail", 1);

	# Check if user has subscribed to this category.
	my $subscribed = 0;
	if ($mails)
	{
		foreach (@{$mails})
		{
			$subscribed = ($user_data{email} eq $_) ? 1 : 0;
		}
	}

	# Get data of all categories.
	my $cats = file2array("$cfg{linksdir}/linkcats.dat", 1);
	if (!@$cats) { user_error($err{32}, $user_data{theme}); }

	# Get name of category.
	my $cat_name;
	foreach (@{$cats})
	{
		my ($name, $link) = split (/\|/, $_);
		if ($cat eq $link) { $cat_name = $name; }
	}

	print_header();
	print_html($user_data{theme}, "$nav{forums} >>> $cat_name");
	if ($subscribed)
	{
		print
		    qq($msg{ask_reply_notify}<br>\n<b><a href="$cfg{pageurl}/links.$cfg{ext}?op=view_cat;cat=$cat">$nav{yes}</a> - <a href="$cfg{pageurl}/links.$cfg{ext}?op=notify2;cat=$cat;state=0">$nav{no}</a></b>);
	}
	else
	{
		print
		    qq($msg{ask_reply_notify}<br>\n<b><a href="$cfg{pageurl}/links.$cfg{ext}?op=notify2;cat=$cat;state=1">$nav{yes}</a> - <a href="$cfg{pageurl}/links.$cfg{ext}?op=view_cat;cat=$cat">$nav{no}</a></b>);
	}
	print_html($user_data{theme}, "$nav{forums} >>> $cat_name", 1);
}

# ---------------------------------------------------------------------
# Update links notification.
# ---------------------------------------------------------------------
sub notify2
{

	# Check if user has permissions to access this area.
	if ($user_data{sec_level} eq $usr{anonuser})
	{
		user_error($err{auth_failure}, $user_data{theme});
	}

	# Update subscriber list.
	$cat =
	    ($cat =~ /^([\w.]+)$/) 
	    ? $1
	    : user_error($err{bad_input}, $user_data{theme});
	update_subscriptions("$cfg{linksdir}/$cat.mail", $user_data{email},
		$state);

	print $query->redirect(
		-location => $cfg{pageurl} . '/links.' . $cfg{ext} .
		'?op=view_cat;cat=' . $cat);
}

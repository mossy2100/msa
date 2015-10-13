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
# $Id: memberlist.cgi,v 1.17 2004/02/17 12:20:38 d3m1g0d Exp $
# =====================================================================

# Load necessary modules.
use strict;
use lib '.';
use yawps;

# Create a new CGI object.
my $query = new CGI;

# Get the input.
my $sort  = $query->param('sort')  || 3;
my $start = $query->param('start') || 0;

# Get user profile.
my %user_data = authenticate();

# Check if user is logged in.
if ($user_data{uid} eq $usr{anonuser})
{
	user_error($err{bad_input}, $user_data{theme});
}

print_header();
print_html($user_data{theme}, $nav{member_list});

# Get names of all members and count them.
my $members = file2array("$cfg{memberdir}/memberlist.dat", 1);
my $members_count = scalar @{$members};

# Get data of the latest member.
my $latest_member_profile =
    file2array("$cfg{memberdir}/$members->[$#$members].dat", 1);

# Get online users by name.
my $online_users = '';
my $log = file2array("$cfg{datadir}/log.dat", 1);
foreach (@{$log})
{
	my ($name, $value) = split (/\|/, $_);
	if ($name !~ /\./)
	{

		# Get profile of every member.
		my $member_profile = file2array("$cfg{memberdir}/$name.dat", 1);

		# Print the online users.
		$online_users =
		    qq($online_users <a href="$cfg{pageurl}/user.$cfg{ext}?op=view_profile;username=$name">$member_profile->[1]</a> );
	}
}

# Get every member's data.
my $id = 0;
my @member;
foreach my $i (@{$members})
{

	# Get member profile.
	my $member_profile = file2array("$cfg{memberdir}/$i.dat", 1);
	$id++;

	# Make string for nickname sorting.
	my $sort_name = lc $member_profile->[1];

	# Calculate total amount of posts for current member.
	my $posts =
	    $member_profile->[6] + $member_profile->[11] + $member_profile->[12];

	# Get member rank.
	my %ranks = load_ranks();
	my $rank  = '';
	foreach (sort keys %ranks)
	{
		if ($posts > $_) { $rank = $ranks{$_}; }
	}

	push (
		@member,
		join (
			"|",                  $member_profile->[1],
			$member_profile->[2], $member_profile->[10],
			$member_profile->[8], $rank,
			$posts,               $member_profile->[7],
			$id,                  $sort_name,
			$i
		)
	    );
}

# Sort members.
my (@data, @sorted, @sorted_members);
for (0 .. $#member)
{
	my @fields = split (/\|/, $member[$_]);
	for my $i (0 .. $#fields) { $data[$_][$i] = $fields[$i]; }
}

# Sort entries.
if ($sort == 1)
{
	@sorted = sort { $a->[8] cmp $b->[8] } @data;
}  # Sort by username (sort_name).
if ($sort == 2)
{
	@sorted = sort { $a->[1] cmp $b->[1] } @data;
}  # Sort by email.
if ($sort == 3)
{
	@sorted = sort { $a->[7] <=> $b->[7] } @data;
}  # Sort by member since (id).
if ($sort == 4)
{
	@sorted = sort { $a->[3] <=> $b->[3] } @data;
}  # Sort by ICQ.
if ($sort == 5 || $sort == 6)
{
	@sorted = reverse sort { $a->[5] <=> $b->[5] } @data;
}  # Sort by rank/posts.
if ($sort == 7)
{
	@sorted = reverse sort { $a->[6] cmp $b->[6] } @data;
}  # Sort by function.

for (@sorted)
{
	my $sorted_row = join ("|", @$_);
	push (@sorted_members, $sorted_row);
}

print <<HTML;
<table border="0" width="100%" cellspacing="1">
<tr>
<td valign="top">$msg{newest_memberC} <a href="$cfg{pageurl}/user.$cfg{ext}?op=view_profile;username=$members->[$#$members]">$latest_member_profile->[1]</a><br>
$msg{member_countC} $members_count<br>
$msg{online_countC} $online_users</td>
</tr>
</table>
<br><br>
<table class="bg5" width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td>
<table width="100%" border="0" cellspacing="1" cellpadding="2">
<tr class="tbl_header">
<td><a href="$cfg{pageurl}/memberlist.$cfg{ext}?sort=1;start=$start"><b>$msg{nameC}</b></a></td>
<td><a href="$cfg{pageurl}/memberlist.$cfg{ext}?sort=2;start=$start"><b>$msg{emailC}</b></a></td>
<td><a href="$cfg{pageurl}/memberlist.$cfg{ext}?sort=3;start=$start"><b>$msg{member_sinceC}</b></a></td>
<td><a href="$cfg{pageurl}/memberlist.$cfg{ext}?sort=4;start=$start"><b>$msg{icqC}</b></a></td>
<td><a href="$cfg{pageurl}/memberlist.$cfg{ext}?sort=5;start=$start"><b>$msg{rankC}</b></a></td>
<td><a href="$cfg{pageurl}/memberlist.$cfg{ext}?sort=6;start=$start"><b>$msg{postsCC}</b></a></td>
<td><a href="$cfg{pageurl}/memberlist.$cfg{ext}?sort=7;start=$start"><b>$msg{functionC}</b></a></td>
</tr>
HTML

# Print the memberlist.
my $num_shown = 0;
if ($members)
{
	my $row_color = qq( class="tbl_row_dark");
	for (my $i = $start; $i < @sorted_members; $i++)
	{
		my (
			$nick,  $email, $since, $icq,       $rank,
			$posts, $funct, $id,    $sort_name, $name
		    )
		    = split (/\|/, $sorted_members[$i]);

		if ($icq)
		{
			$icq =
			    qq(<a href="http://www.icq.com/$icq" target="_blank"><img src="http://wwp.icq.com/scripts/online.dll?icq=$icq&amp;img=5" alt="$icq" border="0"></a>);
		}

		# Alternate the row colors.
		$row_color =
		    ($row_color eq qq( class="tbl_row_dark"))
		    ? qq( class="tbl_row_light")
		    : qq( class="tbl_row_dark");

		# Protect email address.
		my ($email_p, $email_d) = split ("\@", $email);
		my $protected_email = <<"JS";
<script language="javascript"><!--
document.write("<a href=" + "mail" + "to:" + "$email_p" + "@" + "$email_d" + ">" + "$email_p" + " at " + "$email_d" + "</a>")
//--></script>
JS

		# Format date.
		my $formatted_date = format_date($since);

		print <<HTML;
<tr$row_color>
<td><a href="$cfg{pageurl}/user.$cfg{ext}?op=view_profile;username=$name">$nick</a></td>
<td>$protected_email</td>
<td>$formatted_date</td>
<td>$icq</td>
<td>$rank</td>
<td>$posts</td>
<td>$funct</td>
</tr>
HTML

		$num_shown++;
		if ($num_shown >= $cfg{max_items_per_page}) { $i = @sorted_members; }
	}
}

print <<HTML;
</table></td>
</tr>
</table><br>
<b>$msg{pagesC}</b> 
HTML

# Make page navigation bar.
my $num_members = @sorted_members;
my $count       = 0;
while ($count * $cfg{max_items_per_page} < $num_members)
{
	my $view = $count + 1;
	my $strt = $count * $cfg{max_items_per_page};
	if ($start == $strt) { print "[$view] "; }
	else
	{
		print
		    qq(<a href="$cfg{pageurl}/memberlist.$cfg{ext}?sort=$sort;start=$strt">$view</a> );
	}
	$count++;
}

print_html($user_data{theme}, $nav{member_list}, 1);

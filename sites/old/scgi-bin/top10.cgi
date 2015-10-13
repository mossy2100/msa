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
# $Id: top10.cgi,v 1.16 2004/02/28 09:09:07 d3m1g0d Exp $
# =====================================================================

# Load necessary modules.
use strict;
use lib '.';
use yawps;

# Get user profile.
my %user_data = authenticate();

# Print the top list.
print_header();
print_html($user_data{theme}, $nav{statistics});

print "<b>$msg{most_read_news}</b><br>\n<ul>\n";
top10_articles(0);
print "</ul>\n<b>$msg{most_comment_news}</b><br>\n<ul>\n";
top10_articles(1);
print "</ul>\n<b>$msg{popular_news_cats}</b><br>\n<ul>\n";
top10_article_cats();
print "</ul>\n<b>$msg{most_read_posts}</b><br>\n<ul>\n";
top10_forumposts(0);
print "</ul>\n<b>$msg{most_comment_posts}</b><br>\n<ul>\n";
top10_forumposts(1);
print "</ul>\n<b>$msg{most_active_forums}</b><br>\n<ul>\n";
top10_forumposts_cats();
print "</ul>\n<b>$msg{most_popular_links}</b><br>\n<ul>\n";
top10_links();
print "</ul>\n<b>$msg{popular_link_cats}</b><br>\n<ul>\n";
top10_links_cats();
print "</ul>\n<b>$msg{most_voted_polls}</b><br>\n<ul>\n";
top10_polls();
print "</ul>\n<b>$msg{most_active_users}</b><br>\n<ul>\n";
top10_users();
print "</ul>\n";

print_html($user_data{theme}, $nav{statistics}, 1);

# ---------------------------------------------------------------------
# Display most read articles (sorted by views or sorted by comments).
# ---------------------------------------------------------------------
sub top10_articles
{
	my $type = shift;

	# Get article data.
	my (@articles, @data, @sorted_articles);
	my $cats = dir2array($cfg{topicsdir});
	my @cats = grep(/\.cat/, @{$cats});

	# Cycle through the categories.
	foreach my $cat (@{$cats})
	{
		my $topic_data = file2array("$cfg{topicsdir}/$cat", 1);
		$cat =~ s/\.cat$//;

		foreach (@{$topic_data})
		{
			my ($num, $subject, undef, undef, $comments, $views) =
			    split (/\|/, $_);

			# If counting most viewed articles.
			if ($type != 1)
			{
				if ($views && $views != 0)
				{
					push (@articles,
						join ('|', $num, $subject, $views, $cat));
				}
			}

			# If counting most commented articles.
			else
			{
				if ($comments && $comments != 0)
				{
					push (@articles,
						join ('|', $num, $subject, $comments, $cat));
				}
			}
		}
	}

	for (0 .. $#articles)
	{
		my @fields = split (/\|/, $articles[$_]);
		for my $i (0 .. $#fields) { $data[$_][$i] = $fields[$i]; }
	}

	# Sort the articles.
	my @sorted = reverse sort { $a->[2] <=> $b->[2] } @data;
	for (@sorted)
	{
		my $sorted_row = join ('|', @$_);
		push (@sorted_articles, $sorted_row);
	}

	# Print the top 10.
	foreach (my $i = 0;
		$i < @sorted_articles && $i < 10;
		$i++
	    )
	{
		my ($num, $subject, $count, $cat) =
		    split (/\|/, $sorted_articles[$i]);
		print
		    qq(<li><a href="$cfg{pageurl}/topics.$cfg{ext}?op=view_topic;cat=$cat;id=$num">$subject</a> ($count)</li>\n);
	}
}

# ---------------------------------------------------------------------
# Display most active article categories.
# ---------------------------------------------------------------------
sub top10_article_cats
{
	my (@article_cats, @data, @sorted_article_cats);
	my $cats = file2array("$cfg{topicsdir}/cats.dat", 1);

	# Cycle through the categories.
	foreach (@{$cats})
	{
		my @item = split (/\|/, $_);
		my $articles = file2array("$cfg{topicsdir}/$item[1].cat", 1);
		my $count    = scalar @{$articles};

		if ($count != 0)
		{
			push (@article_cats, join ('|', $item[0], $item[1], $count));
		}
	}

	for (0 .. $#article_cats)
	{
		my @fields = split (/\|/, $article_cats[$_]);
		for my $i (0 .. $#fields) { $data[$_][$i] = $fields[$i]; }
	}

	# Sort the article categories.
	my @sorted = reverse sort { $a->[2] <=> $b->[2] } @data;
	for (@sorted)
	{
		my $sorted_row = join ("|", @$_);
		push (@sorted_article_cats, $sorted_row);
	}

	# Print the top 10.
	foreach (my $i = 0;
		$i < @sorted_article_cats && $i < 10;
		$i++
	    )
	{
		my ($name, $link, $count) = split (/\|/, $sorted_article_cats[$i]);
		print
		    qq(<li><a href="$cfg{pageurl}/topics.$cfg{ext}?op=view_cat;cat=$link">$name</a> ($count)</li>);
	}
}

# ---------------------------------------------------------------------
# Display most read forumposts (sorted by views or sorted by replies).
# ---------------------------------------------------------------------
sub top10_forumposts
{
	my $type = shift;

	# Get post data.
	my (@posts, @data, @sorted_posts);
	my $cats = file2array("$cfg{boardsdir}/cats.txt", 1);

	# Cycle through the categories.
	foreach (@{$cats})
	{
		my $cat_info = file2array("$cfg{boardsdir}/$_.cat", 1);

		foreach my $board (@{$cat_info}[2 .. $#$cat_info])
		{
			my $threads = file2array("$cfg{boardsdir}/$board.txt", 1);

			foreach my $thread (@{$threads})
			{
				my (
					$num,   $subject, undef, undef, $replies,
					$views, undef,    undef, undef
				    )
				    = split (/\|/, $thread);

				# If counting most viewed posts.
				if ($type != 1)
				{
					if ($views && $views != 0)
					{
						push (@posts,
							join ('|', $num, $board, $subject, $views));
					}
				}

				# If counting most commented posts.
				else
				{
					if ($replies && $replies != 0)
					{
						push (@posts,
							join ('|', $num, $board, $subject, $replies));
					}
				}
			}
		}
	}

	for (0 .. $#posts)
	{
		my @fields = split (/\|/, $posts[$_]);
		for my $i (0 .. $#fields) { $data[$_][$i] = $fields[$i]; }
	}

	# Sort the posts.
	my @sorted = reverse sort { $a->[3] <=> $b->[3] } @data;
	for (@sorted)
	{
		my $sorted_row = join ("|", @$_);
		push (@sorted_posts, $sorted_row);
	}

	# Print the top 10.
	foreach (my $i = 0;
		$i < @sorted_posts && $i < 10;
		$i++
	    )
	{
		my ($num, $board, $subject, $count) = split (/\|/, $sorted_posts[$i]);
		print
		    qq~<li><a href="$cfg{pageurl}/forum.$cfg{ext}?op=view_thread;board=$board;thread=$num">$subject</a> ($count)</li>~;
	}
}

# ---------------------------------------------------------------------
# Display most active forum categories.
# ---------------------------------------------------------------------
sub top10_forumposts_cats
{
	my (@boards, @data, @sorted_boards);
	my $cats = file2array("$cfg{boardsdir}/cats.txt", 1);

	# Cycle through the categories.
	foreach my $cat (@{$cats})
	{
		my $cat_info = file2array("$cfg{boardsdir}/$cat.cat", 1);

		foreach my $board (@{$cat_info}[2 .. $#$cat_info])
		{

			# Get board name and post count.
			my $board_desc     = file2array("$cfg{boardsdir}/$board.dat", 1);
			my $board_messages = file2array("$cfg{boardsdir}/$board.txt", 1);

			# Get count of messages in every thread.
			my $message_count = 0;
			foreach (@{$board_messages})
			{
				my (
					undef, undef, undef, undef, $replies,
					undef, undef, undef, undef, undef
				    )
				    = split (/\|/, $_);

				$message_count++;
				$message_count += $replies;
			}

			if ($message_count != 0)
			{
				push (@boards,
					join ('|', $board_desc->[0], $board, $message_count));
			}
		}
	}

	for (0 .. $#boards)
	{
		my @fields = split (/\|/, $boards[$_]);
		for my $i (0 .. $#fields) { $data[$_][$i] = $fields[$i]; }
	}

	# Sort the boards.
	my @sorted = reverse sort { $a->[2] <=> $b->[2] } @data;
	for (@sorted)
	{
		my $sorted_row = join ("|", @$_);
		push (@sorted_boards, $sorted_row);
	}

	# Print the top 10.
	for (my $i = 0; $i < @sorted_boards && $i < 10; $i++)
	{
		my ($name, $board, $count) = split (/\|/, $sorted_boards[$i]);
		print
		    qq(<li><a href="$cfg{pageurl}/forum.$cfg{ext}?op=view_board;board=$board">$name</a> ($count)</li>);
	}
}

# ---------------------------------------------------------------------
# Display most viewed links.
# ---------------------------------------------------------------------
sub top10_links
{
	my (@links, @data, @sorted_links);

	# Get all link categories.
	my $cats = file2array("$cfg{linksdir}/linkcats.dat", 1);

	# Cycle through the categories.
	foreach (@{$cats})
	{
		my @item = split (/\|/, $_);
		my $content = file2array("$cfg{linksdir}/$item[1].dat", 1);

		foreach (@{$content})
		{
			my ($id, $name, $url, undef, undef, undef, $count) =
			    split (/\|/, $_);
			$count = ($count eq '') ? 0 : $count;

			if ($count != 0)
			{
				push (@links, join ('|', $item[1], $id, $name, $count));
			}
		}
	}

	for (0 .. $#links)
	{
		my @fields = split (/\|/, $links[$_]);
		for my $i (0 .. $#fields) { $data[$_][$i] = $fields[$i]; }
	}

	# Sort the links.
	my @sorted = reverse sort { $a->[3] <=> $b->[3] } @data;
	for (@sorted)
	{
		my $sorted_row = join ("|", @$_);
		push (@sorted_links, $sorted_row);
	}

	# Print the top 10.
	for (my $i = 0; $i < @sorted_links && $i < 10; $i++)
	{
		my ($cat, $id, $name, $count) = split (/\|/, $sorted_links[$i]);
		print
		    qq(<li><a href="$cfg{pageurl}/links.$cfg{ext}?op=view_link;cat=$cat;id=$id" target="_blank">$name</a> ($count)</li>);
	}
}

# ---------------------------------------------------------------------
# Display most active link categories.
# ---------------------------------------------------------------------
sub top10_links_cats
{
	my (@links, @data, @sorted_link_cats);

	# Get all link categories.
	my $cats = file2array("$cfg{linksdir}/linkcats.dat", 1);

	# Cycle through the categories.
	foreach (@{$cats})
	{
		my @item = split (/\|/, $_);
		my $cat_count = file2array("$cfg{linksdir}/$item[1].dat", 1);
		$cat_count = scalar @{$cat_count};

		push (@links, join ('|', $item[0], $item[1], $cat_count));
	}

	for (0 .. $#links)
	{
		my @fields = split (/\|/, $links[$_]);
		for my $i (0 .. $#fields) { $data[$_][$i] = $fields[$i]; }
	}

	# Sort the links.
	my @sorted = reverse sort { $a->[2] <=> $b->[2] } @data;
	for (@sorted)
	{
		my $sorted_row = join ("|", @$_);
		push (@sorted_link_cats, $sorted_row);
	}

	# Print the top 10.
	for (my $i = 0; $i < @sorted_link_cats && $i < 10; $i++)
	{
		my ($name, $cat, $count) = split (/\|/, $sorted_link_cats[$i]);
		print
		    qq(<li><a href="$cfg{pageurl}/links.$cfg{ext}?op=view_cat;cat=$cat">$name</a> ($count)</li>);
	}
}

# ---------------------------------------------------------------------
# Display polls with the most votes.
# ---------------------------------------------------------------------
sub top10_polls
{
	my (@polls, @data, @sorted_polls);

	# Get all polls.
	my $all_polls = file2array("$cfg{polldir}/polls.txt", 1);

	# Cycle through the categories.
	foreach (@{$all_polls})
	{
		my @item = split (/\|/, $_);
		my $poll_data = file2array("$cfg{polldir}/$item[0]_a.dat", 1);
		my $count     = 0;

		foreach my $i (@{$poll_data}) { $count += $i; }
		if ($count != 0)
		{
			push (@polls, join ('|', $item[0], $item[1], $count));
		}
	}

	for (0 .. $#polls)
	{
		my @fields = split (/\|/, $polls[$_]);
		for my $i (0 .. $#fields) { $data[$_][$i] = $fields[$i]; }
	}

	# Sort the polls.
	my @sorted = reverse sort { $a->[2] <=> $b->[2] } @data;
	for (@sorted)
	{
		my $sorted_row = join ("|", @$_);
		push (@sorted_polls, $sorted_row);
	}

	# Print the top 10.
	for (my $i = 0; $i < @sorted_polls && $i < 10; $i++)
	{
		my ($id, $name, $count) = split (/\|/, $sorted_polls[$i]);
		print
		    qq(<li><a href="$cfg{pageurl}/polls.$cfg{ext}?op=view_poll;id=$id">$name</a> ($count)</li>);
	}
}

# ---------------------------------------------------------------------
# Display most active users (sorted by articles and forumposts).
# ---------------------------------------------------------------------
sub top10_users
{
	my (@members, @data, @sorted_members);

	# Get all members.
	my $memberlist = file2array("$cfg{memberdir}/memberlist.dat", 1);

	# Get every member's user profile.
	foreach (@{$memberlist})
	{
		my $user_profile = file2array("$cfg{memberdir}/$_.dat", 1);
		my $count =
		    $user_profile->[6] + $user_profile->[11] + $user_profile->[12];

		if ($count != 0)
		{
			push (@members, join ("|", $_, $user_profile->[1], $count));
		}
	}

	for (0 .. $#members)
	{
		my @fields = split (/\|/, $members[$_]);
		for my $i (0 .. $#fields) { $data[$_][$i] = $fields[$i]; }
	}

	# Sort the members.
	my @sorted = reverse sort { $a->[2] <=> $b->[2] } @data;
	for (@sorted)
	{
		my $sorted_row = join ("|", @$_);
		push (@sorted_members, $sorted_row);
	}

	# Print the top 10.
	for (my $i = 0; $i < @sorted_members && $i < 10; $i++)
	{
		my ($id, $name, $posts) = split (/\|/, $sorted_members[$i]);
		print
		    qq(<li><a href="$cfg{pageurl}/user.$cfg{ext}?op=view_profile;username=$id">$name</a> ($posts)</li>);
	}
}

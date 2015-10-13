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
# $Id: pages.cgi,v 1.12 2004/02/17 12:20:38 d3m1g0d Exp $
# =====================================================================

# Load necessary modules.
use strict;
use lib '.';
use yawps;

# Create a new CGI object.
my $query = new CGI;

# Get user profile.
my %user_data = authenticate();

# Get the input.
my $id = $query->param('id');
if ($id !~ /^[0-9]+$/) { user_error($err{bad_input}, $user_data{theme}); }

# Get page headline.
my $headline;
my $cats = file2array("$cfg{pagesdir}/pages.dat", 1);
if (!@$cats) { user_error($err{bad_input}, $user_data{theme}); }

foreach (@{$cats})
{
	my ($name, $value) = split (/\|/, $_);
	if ($name == $id) { $headline = $value; }
}

# Get page content.
my $page_content = file2scalar("$cfg{pagesdir}/$id.txt", 1);
if (!$page_content) { user_error($err{bad_input}, $user_data{theme}); }
$page_content = do_ubbc($page_content);

print_header();
print_html($user_data{theme}, $headline);
print $page_content;
print_html($user_data{theme}, $headline, 1);

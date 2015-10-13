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
# $Id: help.cgi,v 1.9 2004/02/17 12:20:38 d3m1g0d Exp $
# =====================================================================

# Load necessary modules.
use strict;
use lib '.';
use yawps;

# Create a new CGI object.
my $query = new CGI;

# Get the input.
my $topic = $query->param('topic');

# Get user profile.
my %user_data = authenticate();

print_header();
print_html($user_data{theme}, $nav{help});

# Get the help topic for referer.
my $found;
foreach my $i (keys %hlp)
{
	if ($topic eq $i) { $found = $hlp{$i}; last; }
}

# Print help.
if (defined $found) { print $found; }
else { print $msg{no_help_found}; }

print_html($user_data{theme}, $nav{help}, 1);

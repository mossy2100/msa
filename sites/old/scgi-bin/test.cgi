#!/usr/bin/perl
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
# $Id: test.cgi,v 1.4 2004/02/17 12:20:38 d3m1g0d Exp $
# =====================================================================

use strict;
my $is_sendmail = 'Cannot locate sendmail';

my @send_mail = qw(
    /usr/sbin/sendmail
    /sbin/sendmail
    /usr/bin/sendmail
    /bin/sendmail
    /usr/lib/sendmail
    /lib/sendmail
    /usr/slib/sendmail
    /slib/sendmail
    /usr/sendmail
    /sendmail
    sendmail
    /var/qmail/bin/qmail-inject
    );

eval { require 5 };
my $is_perl = $@ ? 'No' : 'Yes';
my $perl_version = $];

eval { require CGI };
my $is_cgi = $@ ? 'No' : 'Yes';
my $cgi_version = $CGI::VERSION;

my $script = $0;
$script =~ s/.*\/(.*?)/$1/;

my $path = $ENV{'DOCUMENT_ROOT'};
if (eval { require Cwd; })
{
	use Cwd;
	$path = cwd();
}

for (@send_mail) { $is_sendmail = $_ if (-e $_); }

print "Content-Type: text/html\n\n";
print <<HTML;
<html>
<head>
<title>Environment Checker</title>
</head>
<body>
<b>Environment Checker</b>
<hr>
<table cellspacing="5">
<tr>
<td>Is Perl Version 5 or above installed?</td><td><b>$is_perl</b></td>
</tr>
<tr>
<td>Version of Perl running on this server:</td><td><b>$perl_version</b></td>
</tr>
<tr>
<td>Is the CGI.pm module installed?</td><td><b>$is_cgi</b></td>
</tr>
<tr>
<td>Version of CGI running on this server:</td><td><b>$cgi_version</b></td>
</tr>
<tr>
<td>Full path to this script:</td><td><b>$path</b></td>
</tr>
<tr>
<td>Sendmail Path:</td><td><b>$is_sendmail</b></td>
</tr>
</table>
</body>
</html>
HTML

exit;

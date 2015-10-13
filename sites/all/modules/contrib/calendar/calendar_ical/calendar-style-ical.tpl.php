<?php
/**
 * $title
 *   The name of the calendar.
 *
 * If you are editing this file, remember that all output lines generated by it
 * must end with DOS-style \r\n line endings, and not Unix-style \n, in order to
 * be compliant with the iCal spec (see http://tools.ietf.org/html/rfc5545#section-3.1)
 */
if (empty($method)) {
  $method = 'PUBLISH';
}
print "BEGIN:VCALENDAR\r\n";
print "VERSION:2.0\r\n";
print "METHOD:$method\r\n";
if (!empty($calname)) {
  print "X-WR-CALNAME;VALUE=TEXT:$calname\r\n";
}
print "PRODID:-//Drupal iCal API//EN\r\n";
// Note that $rows already has the right line endings and needs no more.
print "$rows";
print "END:VCALENDAR\r\n";

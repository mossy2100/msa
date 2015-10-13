<?php
//using the date() function
$time = date("F jS Y, h:iA"); 
//$remote_addr is PHP variable to get ip address
$ip = $REMOTE_ADDR;  
//$http_referer is PHP variable to get referer
$referer = $HTTP_REFERER;  
//$http_user_agent is PHP variable for browser
$browser = $HTTP_USER_AGENT;  
//what page they came from
$page = $_SERVER['REQUEST_URI'];
//use the fopen() function
$fp = fopen("log.html",  "a");  
//using the fputs() function
fputs($fp, "
<b>Time:</b> $time,
<b>IP:</b> $ip,
<b>Referer:</b> $referer,<br>
<b>Browser:</b> $browser,
<b>Page:</b> $page
<br> ");
fclose($fp);  
?>


*** Documentation for pdf-validation server
*** 25. Jul 2006 Sebastian Held <sebastian.held@uni-duisburg-essen.de>


ABOUT

The validation server is a web service, which accepts pdf-files and passes them over to a validating backend (e.g. PitStop from Enfocus).


PREREQUISITES

- PitStop from Enfocus Software (http://www.enfocus.com)
- php enabled webserver


PRINCIPLE of OPERATION

The file validatePDF.php is the main server component. It accepts XML RPC calls to validate a pdf.
validatePDF.php need not to be on the same server than iaprcommence, but there should be a relatively fast connection between them.
Every time a user uploads a new paper or changes an existing one using iaprcommence, validatePDF.php is called to validate this pdf file.
For the time of sending the pdf to validatePDF.php, iaprcommence will freeze (that's why you will need a fast connection).
After receiving the pdf file, validatePDF.php puts it into the hotdir of PitStop. A new process is spawn (validatePDF.php calling itself using an appropriate URL), which waits for completition of the validation process (PitStop's job).
If validatePDF.php detects the completition of PitStop's validation process, it calls a callback function to announce the result to iaprcommence.


SETUP of SERVER

1) install PitStop (I'm using wine) and setup the hotdir structure. Test PitStop by putting a pdf file into the hotdir. This should dissappear soon and result files should be created.
2) copy validatePDF.php, reportTooLarge.pdf and the utils/ folder to your webserver. Directory is not important, but remember it.
3) edit validatePDF.php. Change the email address, to get notified if a problem arises and enable debugging. Enter the path to PitStop's hotdir.
4) start a browser and enter the following URL (change it accordingly1)
   http://www.example.com/Commence.svn/server/validatePDF.php?debug=true
   Refer to section EXAMPLE DEBUGGING OUTPUT for an example
5) have an eye on PitStop and on the hotdir directory structure
   e.g. (unix) watch -n 1 find /var/spool/pitstop
   For a maximum of 10 sec there should be a file (e.g. commence_ijvw62) in the input folder. PitStop processes it and creates some output files (e.g. commence_ijvw62_log.pdf).
   The input file is removed and after some more time validatePDF.php will remove the output files of PitStop.
6) if everything is ok, disable debugging


EXAMPLE DEBUGGING OUTPUT
-START------------------------------------------------START-
VISUAL DEBUGGING:

availability of xml implementations:
Array
(
    [internal] => 1
    [pear] => 1
)

processing the following XML request:
<?xml version="1.0"?>
<methodCall>
<methodName>validatePDF</methodName>
<params>
<param>
<value><base64>ZGVidWdnaW5nLi4u</base64></value>
</param>
<param>
<value><int>12</int></value>
</param>
<param>
<value><string></string></value>
</param>
<param>
<value><int>0</int></value>
</param>
</params>
</methodCall>

==> using XML_RPC (pear)

 Warning: Cannot modify header information - headers already sent by (output started at /srv/www/Commence_TEST/htdocs/Commence.svn/server/validatePDF.php:95) in /usr/share/php/XML/RPC/Server.php on line 182
 
 Warning: Cannot modify header information - headers already sent by (output started at /srv/www/Commence_TEST/htdocs/Commence.svn/server/validatePDF.php:95) in /usr/share/php/XML/RPC/Server.php on line 183
     1
-END----------------------------------------------------END-
The warnings can be ignored and will not be present, if only the internal xml implementation is available.


SETUP of IAPRCOMMENCE

1) Log into iaprcommence as an administrator. Go to General Admin/change Settings.
2) Enable the check box "Enable validating of submitted PDF-files".
3) Choose "remote via XML-RPC" as "Select the method used to validate the PDF"
4) Enter the server URL (e.g. http://www.example.com/Commence/server/validatePDF.php)
5) Click "Submit"


TEST of IAPRCOMMENCE

Add a new paper or modify an existing one (you need to upload a paper). After a successful commit, the initial validation state will be "unknown".
If everything is working, PitStop will validate the submitted file (have an eye on the hotdir) and a callback routine will be called, which in turn updates the database.
This may take a minute or so. After that, the state will change to "VALID" or "invalid". In addition a report is available, which states the errors.






REMARKS

Do not use XML_RPC version 1.4.8 it seems to be broken.



DEBUGGING

 - change Commence/includes/main_fns.php line 1205 (function validatePDF)
   from $client->setDebug(0);
   to $client->setDebug(1);
   now, after an add or update of a paper, debugging info is visible in the browser
 - enable $DEBUG in Commence/server/validatePDF_callback.php
 - enable $DEBUG in validatePDF.php on your validation server

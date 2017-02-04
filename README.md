<h1>Installation Instructions</h1>

<h2>Edit files</h2>
Before uploading, the following files must be edited: 
-includes / config.php 
-install / seotracker.pl

The access data for the mySQL DB must be entered in both files. Also minor adjustments, such as the URL to the tracker.

<h2>Upload files</h2>
Upload files to the desired directory

<h2>Import mySQL dump</h2> 
In the install / folder is the full.sql - this file is imported into your own database
Move PERL script

<h2>Move PERL script</h2>
The seotracker.pl should be renamed or moved so that it can not be installed / found in the default directory. If this is forgotten, everyone will be able to download this file with the access data if they know the URL to the tracker. Make another directory and grab it there!

<h2>Apply Cronjob</h2>
The point is important. It must be created a Cronjob, which is run EVERY HOUR, in the simplest case looks like this: 
0 * * * * perl /absoluter/pfad/zur/perldra//seotracker.pl> / possibly / a / log / file .log 2 &> 1

Here the script is executed every hour. The absolute paths have to be adjusted naturally. What should also be ensured: - Executability of the file for the user who runs the cronjob 
-General availability of the above PERL packages 
-The absolute path, not the URL to the file or relative paths must be entered 
-Service can start little

<h3>Hints</h3>
The script is intended for people who run their own server and have SSH access there. Some web hosters can also run PERL files. Who rents a managed server, who may have to talk with the operator so the packages are installed. If you are interested, you can copy the PERL script with PHP and CURL. Then the program will also run on any web host that allows normal PHP cronjobs!

<h3>Screenshots</h3>
<img src="https://www.damianschwyrz.de/wp-content/uploads/2014/09/ranking.png">
<img src="https://www.damianschwyrz.de/wp-content/uploads/2014/09/Keyword-Index-Keyword-Tracker.jpg">

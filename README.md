# ndoutils-web
Mini PHP based web interface for Nagios NDOUtils add-on
<br /><br />
This is VERY basic.  It will return nagios host and service data based on your post.  For example:
<br />
wget -qO- --no-check-certificate --user nagios_web_user --password nagios_web_user_pass --post-data="host=randomhost&format=json" https://localhost/nagios/host_status.php
<br /><br />
This will connect to the nagios site (assumes you have a cert, assumes you have basic HTTP auth), and posts a randomehost, requesting json format.  Various bits of data will be returned in json format.  Other supported formats are xml, plaintext.  For plaintext, just use "format=" with no entry.
<br /><br />
I've added injection protection using OWASP based recommendations.
<br /><br />
Do something simlar for services:
<br />
wget -qO- --no-check-certificate --user nagios_web_user --password nagios_web_user_pass --post-data="host=randomhost&service=httpd&format=json" https://localhost/nagios/service_status.php
<br /><br />
Remember, these are examples.  Again, this is basic, it could be easily changed to support other people's needs.  My only intention was for an easy dashboard plugin that didn't constantly read status.dat.
<br /><br />
More later, when I have the free time.

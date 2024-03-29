==                  ==
 Gladius DB v0.8
==                  ==
http://gladius.sourceforge.net/

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
version 2 as published by the Free Software Foundation

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program (file GPL.TXT); if not, write to the Free
Software Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA
or download it from http://www.gnu.org/licenses/gpl.txt

== Disclaimer ==

Some parts of Gladius DB are still incomplete. Please download the
manual or view the online manual to understand all of its features
and capabilities.

== Bug reporting ==

http://sourceforge.net/tracker/?group_id=173938&atid=867348

Thank you.

== Download ==

http://sourceforge.net/project/showfiles.php?group_id=173938

== Documentation

You can also download the Official Manual from the above location,
under each release's download page.

The online documentation (not always up-to-date) is available at

http://gladius.sourceforge.net/docs/

== Usage ==

Gladius DB engine can be used as stand-alone (including gladius.php)
or with ADODB lite ( http://adodblite.sourceforge.net/index.php )
using the supplied driver gladius_driver.inc.

If your databases' folder is not the gladius.php's folder, set the
variable $GLADIUS_DB_ROOT before including gladius.php or calling
ADODB's AdoNewConnection(). Example:

<?php
  
  include 'gladius.php';
  
  $G = new Gladius();
  
  $G->SetDBRoot('path/to/database_directories') or die;
  
  $G->SelectDB('db_directory_or_path_to_db');
  
  $G->Query('CREATE TABLE hypsin ( base FLOAT, root FLOAT)');

?>

The above example will create a table named 'hypsin' with two fields
of type float in the folder 'database_name', which must already exist.

== Subversion repository ==

Checkout the latest version of the sources from

https://svn.sourceforge.net/svnroot/gladius/trunk

== Contact ==

You can contact me via email legolas558 _AT_ users.sourceforge.net
or via the message form http://sourceforge.net/sendmessage.php?touser=799761

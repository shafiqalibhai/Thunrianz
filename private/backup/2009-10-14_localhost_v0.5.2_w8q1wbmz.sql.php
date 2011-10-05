/* @DKDB 0.5.2 @GID 5 <?php if (@$my->gid< 5)die; ?> */
/* @DKDB 0.5.2 @GID 5 <?php if (@$my->gid< 5)die; ?> */
/* DKDB PREFIX = #k7xp1_ */

DROP TABLE #k7xp1_banners;

CREATE TABLE #k7xp1_banners (
id INTEGER AUTO_INCREMENT,
name VARCHAR(50) NOT NULL,
imp INTEGER NOT NULL DEFAULT 0,
imphits INTEGER NOT NULL DEFAULT 0,
hits INTEGER NOT NULL DEFAULT 0,
imageurl TEXT NOT NULL,
clickurl TEXT NOT NULL,
blanktarget INTEGER NOT NULL DEFAULT 1,
published INTEGER NOT NULL DEFAULT 0,
bannercode TEXT,
PRIMARY KEY (id) 
);

INSERT INTO #k7xp1_banners (id,name,imp,imphits,hits,imageurl,clickurl,blanktarget,published,bannercode) VALUES (1,'Test banner',0,23,0,'test_banner.png','http://www.laniuscms.org/',1,1,'');

DROP TABLE #k7xp1_categories;

CREATE TABLE #k7xp1_categories (
id INTEGER AUTO_INCREMENT,
parent_id INTEGER NOT NULL DEFAULT 0,
component_id INTEGER NOT NULL DEFAULT 0,
name VARCHAR(255) NOT NULL,
image VARCHAR(255) NOT NULL DEFAULT '',
image_position VARCHAR(10) NOT NULL DEFAULT 'left',
section VARCHAR(20) NOT NULL,
description TEXT,
ordering INTEGER NOT NULL DEFAULT 0,
access INTEGER NOT NULL DEFAULT 9,
editgroup INTEGER NOT NULL DEFAULT 3,
count INTEGER NOT NULL DEFAULT 0,
PRIMARY KEY (id) 
);

INSERT INTO #k7xp1_categories (id,parent_id,component_id,name,image,image_position,section,description,ordering,access,editgroup,count) VALUES (1,0,0,'topmenu','','','com_menu','',0,1,3,0);

INSERT INTO #k7xp1_categories (id,parent_id,component_id,name,image,image_position,section,description,ordering,access,editgroup,count) VALUES (2,0,0,'Butterflies collection','hamadryas_februa.jpg','left','com_gallery','My butterflies collection',1,0,3,1);

INSERT INTO #k7xp1_categories (id,parent_id,component_id,name,image,image_position,section,description,ordering,access,editgroup,count) VALUES (3,0,0,'mainmenu','','','com_menu','',1,0,3,0);

INSERT INTO #k7xp1_categories (id,parent_id,component_id,name,image,image_position,section,description,ordering,access,editgroup,count) VALUES (4,0,0,'General Content','','left','1','',2,9,3,0);

INSERT INTO #k7xp1_categories (id,parent_id,component_id,name,image,image_position,section,description,ordering,access,editgroup,count) VALUES (7,0,0,'Lanius CMS News','laniuscms.png','left','2','Lanius CMS related News',2,0,1,1);

INSERT INTO #k7xp1_categories (id,parent_id,component_id,name,image,image_position,section,description,ordering,access,editgroup,count) VALUES (8,0,0,'General News','topiceditorial.png','left','2','Other General news related to the website',1,0,1,0);

INSERT INTO #k7xp1_categories (id,parent_id,component_id,name,image,image_position,section,description,ordering,access,editgroup,count) VALUES (22,0,0,'Lanius CMS Sites','','left','com_weblinks','These are some websites which are devoted to Lanius CMS',1,0,3,2);

INSERT INTO #k7xp1_categories (id,parent_id,component_id,name,image,image_position,section,description,ordering,access,editgroup,count) VALUES (24,0,0,'usermenu','','','com_menu','',0,1,3,0);

INSERT INTO #k7xp1_categories (id,parent_id,component_id,name,image,image_position,section,description,ordering,access,editgroup,count) VALUES (25,0,0,'servicemenu','','','com_menu','',0,1,3,0);

INSERT INTO #k7xp1_categories (id,parent_id,component_id,name,image,image_position,section,description,ordering,access,editgroup,count) VALUES (26,0,0,'This Lanius CMS installation was','','','com_polls','',1,0,3,6);

INSERT INTO #k7xp1_categories (id,parent_id,component_id,name,image,image_position,section,description,ordering,access,editgroup,count) VALUES (29,0,0,'General questions','','left','com_faq','',10,0,3,7);

INSERT INTO #k7xp1_categories (id,parent_id,component_id,name,image,image_position,section,description,ordering,access,editgroup,count) VALUES (30,0,0,'Public downloads','','left','com_downloads','',1,0,3,0);

INSERT INTO #k7xp1_categories (id,parent_id,component_id,name,image,image_position,section,description,ordering,access,editgroup,count) VALUES (31,0,0,'Lanius CMS installation','laniuscms.png','left','com_faq','',15,0,3,1);

INSERT INTO #k7xp1_categories (id,parent_id,component_id,name,image,image_position,section,description,ordering,access,editgroup,count) VALUES (32,0,0,'Add-ons installation','topicgeneral.png','left','com_faq','',14,0,3,2);

INSERT INTO #k7xp1_categories (id,parent_id,component_id,name,image,image_position,section,description,ordering,access,editgroup,count) VALUES (33,0,0,'Database','topiceditorial.png','left','com_faq','',16,0,3,3);

INSERT INTO #k7xp1_categories (id,parent_id,component_id,name,image,image_position,section,description,ordering,access,editgroup,count) VALUES (34,0,0,'Miscellanous newsflashes','','left','1','',17,0,3,1);

DROP TABLE #k7xp1_components;

CREATE TABLE #k7xp1_components (
id INTEGER AUTO_INCREMENT,
name VARCHAR(50) NOT NULL,
link TEXT NOT NULL,
menuid INTEGER NOT NULL DEFAULT 0,
parent INTEGER NOT NULL DEFAULT 0,
admin_menu_link TEXT NOT NULL,
admin_menu_alt VARCHAR(255) NOT NULL,
option_link TEXT NOT NULL,
ordering INTEGER NOT NULL DEFAULT 0,
iscore INTEGER NOT NULL DEFAULT 0,
admin_access INTEGER NOT NULL DEFAULT 5,
PRIMARY KEY (id) 
);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (2,'Content','',0,0,'','','com_content',0,1,3);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (3,'Weblinks','option=weblinks',0,0,'','Manage the Weblinks','com_weblinks',0,0,3);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (4,'Items','',0,3,'com_option=weblinks&option=items','Add and Remove links','com_weblinks',0,0,3);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (5,'Categories','',0,3,'com_option=weblinks&option=categories','Add and Remove link categories','com_weblinks',0,0,3);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (9,'Polls','option=polls',0,0,'com_option=polls','Manage the polls','com_polls',0,0,3);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (10,'Search','option=search',0,0,'','','com_search',0,1,9);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (12,'Login','option=login',0,0,'','','com_login',0,1,9);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (14,'Banners','',0,0,'com_option=banner','Manage the Banners','com_banner',0,0,4);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (18,'Frontpage','option=frontpage',0,0,'','','com_frontpage',0,1,3);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (20,'User','option=user',0,0,'','','com_user',0,1,5);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (21,'File Manager','',0,0,'com_option=fm','File Manager','com_fm',0,0,5);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (23,'Manage Comments','',0,0,'com_option=comment','Manage Comments','com_comment',0,0,3);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (24,'Google Sitemap','',0,0,'com_option=gositemap','Google Sitemap','com_gositemap',0,0,4);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (25,'Sitemap','option=sitemap',0,0,'','Sitemap','com_sitemap',0,0,9);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (26,'Event Manager','option=event',0,0,'com_option=event','Event Manager','com_event',0,0,3);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (27,'FAQ','option=faq',0,0,'com_option=faq','FAQ','com_faq',0,0,3);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (28,'Questions','',0,27,'com_option=faq&option=questions','Questions','com_faq',0,0,3);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (29,'Categories','',0,27,'com_option=faq&option=categories','Categories','com_faq',0,0,3);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (30,'Gallery','option=gallery',0,0,'com_option=gallery','Gallery','com_gallery',0,0,3);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (31,'All images','',0,30,'com_option=gallery&option=items','All images','com_gallery',0,0,3);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (32,'Categories','',0,30,'com_option=gallery&option=categories','Categories','com_gallery',0,0,3);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (33,'Guestbook','option=guestbook',0,0,'com_option=guestbook','Guestbook','com_guestbook',0,0,3);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (34,'Downloads Manager','option=downloads',0,0,'com_option=downloads','Downloads Manager','com_downloads',0,0,3);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (35,'All Downloads','',0,34,'com_option=downloads&option=items','All Downloads','com_downloads',0,0,3);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (36,'Categories','',0,34,'com_option=downloads&option=categories','Categories','com_downloads',0,0,3);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (37,'Forum','option=forum',0,0,'com_option=forum','Forum','com_forum',0,0,3);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (38,'Main categories','',0,37,'com_option=forum&option=categories&sec_id=0','Main forum categories','com_forum',0,0,3);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (39,'Sections','',0,37,'com_option=forum&option=sections','Forum sections','com_forum',0,0,3);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (40,'Manage components','',0,0,'','','com_components',0,1,5);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (41,'Manage modules','',0,0,'','','com_modules',0,1,4);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (42,'System','',0,0,'','','com_system',0,1,4);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (43,'Subsites manager','',0,0,'','','com_subsites',0,1,5);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (44,'Templates manager','',0,0,'','','com_templates',0,1,4);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (45,'Languages manager','',0,0,'','','com_language',0,1,4);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (46,'Menu editor','',0,0,'','','com_menu',0,1,4);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (48,'Messages','option=message',0,0,'','','com_message',0,1,9);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (49,'Start','',0,0,'','','com_start',0,1,3);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (50,'Manage drabots','',0,0,'','','com_drabots',0,1,4);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (51,'Global configuration','',0,0,'','','com_config',0,1,4);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (52,'Tarball Backup','',0,0,'','','com_backup',0,1,4);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (53,'Admin Templates manager','',0,0,'','','com_admintemplates',0,1,4);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (55,'Massmail','',0,0,'','','com_massmail',0,1,4);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (56,'Patch','',0,0,'','','com_patch',0,1,5);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (57,'Registration','option=registration',0,0,'','','com_registration',0,1,9);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (58,'Syndicate','option=syndicate',0,0,'','','com_syndicate',0,0,9);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (59,'Database manager','',0,0,'','','com_database',0,1,4);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (60,'About','option=about',0,0,'','','com_about',0,1,0);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (62,'File browser','',0,0,'','','com_fb',0,1,9);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (64,'Remote blog','',0,0,'','','com_remoteblog',0,0,9);

INSERT INTO #k7xp1_components (id,name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES (65,'Service','',0,0,'','','com_service',0,1,9);

DROP TABLE #k7xp1_forum_categories;

CREATE TABLE #k7xp1_forum_categories (
id INTEGER AUTO_INCREMENT,
parent_id INTEGER NOT NULL DEFAULT 0,
name VARCHAR(255) NOT NULL,
description TEXT NOT NULL,
locked INTEGER NOT NULL DEFAULT 0,
moderators VARCHAR(255) NOT NULL,
ordering INTEGER NOT NULL DEFAULT 0,
access INTEGER NOT NULL DEFAULT 9,
editgroup INTEGER NOT NULL DEFAULT 1,
checked_out INTEGER NOT NULL DEFAULT 0,
topic_count INTEGER NOT NULL DEFAULT 0,
post_count INTEGER NOT NULL DEFAULT 0,
PRIMARY KEY (id) 
);

INSERT INTO #k7xp1_forum_categories (id,parent_id,name,description,locked,moderators,ordering,access,editgroup,checked_out,topic_count,post_count) VALUES (1,0,'Newbies Forum','This is an example forum',0,'',1,0,1,1,1,0);

DROP TABLE #k7xp1_forum_posts;

CREATE TABLE #k7xp1_forum_posts (
id INTEGER AUTO_INCREMENT,
thread_id INTEGER NOT NULL DEFAULT 0,
parent_id INTEGER NOT NULL DEFAULT 0,
catid INTEGER NOT NULL DEFAULT 0,
name VARCHAR(100) NOT NULL,
userid INTEGER NOT NULL DEFAULT 0,
subject VARCHAR(255) NOT NULL,
message LONGTEXT,
time INTEGER NOT NULL,
ip TEXT NOT NULL,
PRIMARY KEY (id) 
);

INSERT INTO #k7xp1_forum_posts (id,thread_id,parent_id,catid,name,userid,subject,message,time,ip) VALUES (1,1,0,1,'Lanius CMS Team',0,'Welcome to the Newbies Forum','Welcome to the Newbies Forum of this new [URL=http://www.laniuscms.org/]Lanius CMS[/URL] installation.

If you are an unregistered user, [URL=index.php?option=registration]register an account[/URL].

Registed users can [URL=index.php?option=forum&task=newtopic&catid=1]create new topics[/URL], reply to existing ones and receive email notifications; through registration you will also be able to use all the public services of this website.
',1170345129,'127.0.0.1');

DROP TABLE #k7xp1_forum_topics;

CREATE TABLE #k7xp1_forum_topics (
id INTEGER AUTO_INCREMENT,
catid INTEGER NOT NULL DEFAULT 0,
name VARCHAR(100) NOT NULL,
userid INTEGER NOT NULL DEFAULT 0,
subject VARCHAR(255) NOT NULL,
time INTEGER NOT NULL,
locked INTEGER NOT NULL DEFAULT 0,
sticked INTEGER NOT NULL DEFAULT 0,
ordering INTEGER NOT NULL DEFAULT 0,
checked_out INTEGER NOT NULL DEFAULT 0,
post_count INTEGER NOT NULL DEFAULT 0,
post_list TEXT,
hits INTEGER NOT NULL DEFAULT 0,
PRIMARY KEY (id) 
);

INSERT INTO #k7xp1_forum_topics (id,catid,name,userid,subject,time,locked,sticked,ordering,checked_out,post_count,post_list,hits) VALUES (1,1,'Lanius CMS Team',0,'Welcome to the Newbies Forum',1170345129,0,0,0,0,0,'_1_',0);

DROP TABLE #k7xp1_forum_notifies;

CREATE TABLE #k7xp1_forum_notifies (
id INTEGER AUTO_INCREMENT,
topic_id INTEGER NOT NULL,
user_id INTEGER NOT NULL,
PRIMARY KEY (id) 
);

DROP TABLE #k7xp1_forum_users;

CREATE TABLE #k7xp1_forum_users (
id INTEGER AUTO_INCREMENT,
posts INTEGER NOT NULL DEFAULT 0,
image VARCHAR(255) NOT NULL DEFAULT '',
location VARCHAR(100) NOT NULL DEFAULT '',
signature VARCHAR(255) NOT NULL DEFAULT '',
information TEXT NOT NULL,
url TEXT NOT NULL,
PRIMARY KEY (id) 
);

DROP TABLE #k7xp1_content;

CREATE TABLE #k7xp1_content (
id INTEGER AUTO_INCREMENT,
title VARCHAR(100) NOT NULL,
title_alias VARCHAR(100) NOT NULL,
introtext LONGTEXT,
bodytext LONGTEXT,
sectionid INTEGER NOT NULL DEFAULT 0,
mask INTEGER NOT NULL DEFAULT 0,
catid INTEGER NOT NULL DEFAULT 0,
created INTEGER NOT NULL,
modified INTEGER NOT NULL,
userid INTEGER NOT NULL DEFAULT 0,
created_by_alias VARCHAR(100) NOT NULL,
published INTEGER NOT NULL DEFAULT 0,
frontpage INTEGER NOT NULL DEFAULT 0,
ordering INTEGER,
metakey TEXT,
metadesc TEXT,
access INTEGER NOT NULL DEFAULT 0,
hits INTEGER NOT NULL DEFAULT 0,
PRIMARY KEY (id) 
);

INSERT INTO #k7xp1_content (id,title,title_alias,introtext,bodytext,sectionid,mask,catid,created,modified,userid,created_by_alias,published,frontpage,ordering,metakey,metadesc,access,hits) VALUES (1,'Welcome to Lanius CMS','Welcome to Lanius CMS','<div align="center"><h1>Welcome to Lanius CMS</h1><br /><img src="media/common/logo.png" border="0" alt="Lanius CMS Logo" /><h2><em>Amazing and powerful</em></h2></div><br /><p>Lanius CMS is a light-weight Content Managment System (CMS) with one of the broadest host compatibilities and performances around. It is perfect for those that only want to run small websites, although capable of handling larger sites. It can be easily backed up with its own SQL database and/or TAR.GZ site backup.</p><p>Read as many articles as you can to get a full feel of what Lanius CMS is all about. Lanius CMS is open source and <em>available to everybody for free</em>! </p><h3>Lanius CMS applications</h3><p>Here is an example of possible Lanius CMS applications:</p><ul><li>Personal or family homepages</li><li>Community-based portals</li><li>Magazines and newspapers</li><li>Non-profit and organizational websites</li><li>School and church websites</li><li>Corporate intranets and extranets</li><li>Corporate websites or portals</li><li>Government applications </li></ul><h3>Lanius CMS community</h3>If you are experiencing troubles at using Lanius CMS you can benefit from an ever-growing active community of friendly users and developers with plenty of support for newbies at <a href="http://www.laniuscms.org/forums" target="_blank">Lanius CMS Forums</a>. <p>&nbsp;</p>','<h1>Lanius CMS overview</h1><p>Lanius CMS topmost features are:</p><ul><li>security</li><li>speed</li><li>ease of use</li><li>flexibility</li><li>ease of customization </li></ul><h3>Security</h3><p>Lanius CMS has a tight security layer and strict PHP coding policy that grants top security in all client/server operations. New modules, components and drabots are always verified for security and certified by the Lanius CMS Team. </p><h3>Multiple databases support</h3><p>Lanius CMS&nbsp;can run with <strong>many</strong> database systems (see list below) thanks to <a rel="nofollow" href="http://adodblite.sourceforge.net/" title="http://adodblite.sourceforge.net" target="_blank">adoDB lite</a>; if your host does not provide any professional database system, the <a rel="nofollow" href="http://sourceforge.net/projects/gladius" title="http://sourceforge.net/projects/gladius" target="_blank">Gladius DB</a> system is embedded and always available for simple text-based flatfile database use. A great feature for limited hosts where a professional database system may not be available by default or would cost more money to implement. </p><ul><li>MySQL </li><li>Frontbase </li><li>MaxDB </li><li>miniSQL </li><li>MSSQL </li><li>MSSQL Pro </li><li>MySQLi </li><li>MySQLt </li><li>PostgresSQL </li><li>PostgresSQL64 </li><li>PostgresSQL7 </li><li>SQLite </li><li>Sybase </li></ul><p><sub>* additional drivers&nbsp;<a href="http://downloads.sourceforge.net/laniuscms/adodb_lite_drivers_pack.tar.gz" target="_blank">available here</a></sub></p><br />{pagebreak} </p><h3>Works anywhere </h3><p>Lanius CMS has particular signifigance for limited/shared hostings and for PHP hosts where some functions are disabled; Lanius CMS will work on any PHP4.3+ installation and will always take advantage of PHP4/PHP5 libraries where available. </p><h3>Users hierarchy</h3><p>Lanius CMS has a hierarchical diversification of its users which takes in account the role of each; the pyramid starts with the Administrator and goes down till the simple website visitor, allowing a full customization of each user level&#39;s services and access. </p><h3>W3C standards compliance</h3><p>Lanius CMS output pages are W3C standards compliant (this statement will be true before version 1.0), allowing the widest users range compatibility. </p><p>{pagebreak}</p><h3>Multi language</h3><p>The user language will be automatically recognized and each page will be correctly served in the correct language. All content can also be made multi-language (this statement will be true before version 1.0). </p><h3>Highly customizable</h3><p>Creating your own add-on is a matter of minutes! Read our Tutorials and learn how to do it yourself. Your PHP code can be easily packed in Installation packages and delivered worldwide! Each add-on can be customized in its own style through the usage of CSS stylesheets. </p><h3>Full backup and restore</h3><p>Lanius CMS can be fully backed up and restored, both files and database, and downloaded locally using the TarBackup feature, stored safely and are then ready to upload and restore when needed. </p><p>{pagebreak}</p><h3>Subsites</h3><p>Lanius CMS can contain any number of subsites and each subsite will not replicate the CMS files wasting your available space! Child subsites can be fully customized and assigned to different managers. </p><h3>Lanius CMS availability</h3><p>Lanius CMS is free, open, and available to all under the GPL license.&nbsp;&nbsp;&nbsp;</p><br /><h3>Security</h3><p>Lanius CMS has a tight security layer and strict PHP coding policy that implements tight security in all client/server operations. New modules, components and drabots are always verified for security and certified by the Lanius CMS Team.</p><p>{pagebreak}</p><h3>Users hierarchy</h3><p>Lanius CMS has a hierarchical diversification of its users which takes into account the role of each; the pyramid starts with the Administrator and goes down till the simple website visitor, allowing a full customization of each user level&#39;s of services and access.</p><h3>W3C standards compliance</h3><p>Lanius CMS output pages are W3C standards compliant (<em>this statement will be true with version 1.0</em>), allowing the widest range of user&nbsp;compatibility.</p><h3>Multi language</h3><p>The users language, when selected, will be automatically loaded and each page will be correctly presented in the correct language. All content can also be made multi-language (<em>this statement will be true before version 1.0</em>) <a href="http://wiki.laniuscms.org/index.php/Administration/System/Languages_manager" target="_blank">translating your own language</a>.</p><p>{pagebreak}</p><h3>Highly customizable</h3><p>Creating your own add-on takes only a matter of minutes! Read our Tutorials and learn how to do it yourself. Your PHP code can be easily packed in installation packages and delivered worldwide! <a href="http://wiki.laniuscms.org/index.php/XML_formats" title="Installation package" target="_blank">Installation packages</a> Each add-on can be customized in its own style through the usage of CSS stylesheets.</p><h3>Full backups</h3><p>Lanius CMS can be fully backed up, both files and database, and downloaded locally using the embedded PHP TarBackup feature.</p><h3>Subsites</h3><p>Lanius CMS can contain <strong>any</strong> number of subsites and each subsite will <strong>not</strong> replicate the CMS files wasting your available space! Child subsites can be fully customized and assigned to different managers.</p>',2,57,7,1150840800,1179994876,0,'The Lanius CMS Team',1,1,20,'Lanius,CMS,database,system,language,security,own,PHP,embedded,available,allowing,users,Gladius,flavours,DB,simple,user,standards,pagebreak,fully,databases,customized,subsites,files,support,professional,New,supports,major,file,minutes,top,flat,powerful,served,world,layer,compatibility','Multiple databases support Lanius CMS can run with any database system (see below list) thanks to adoDB lite ; if you do not have a professional database system, Gladius DB is embedded and ready to use for simple text-based flatfile database support',0,0);

INSERT INTO #k7xp1_content (id,title,title_alias,introtext,bodytext,sectionid,mask,catid,created,modified,userid,created_by_alias,published,frontpage,ordering,metakey,metadesc,access,hits) VALUES (2,'Current events','Current events','<p align="center">Welcome to the demo website installed by default with Lanius CMS!<br />Begin customizing your brand new Lanius CMS using the <a href="admin.php">admin backend</a></p>','',1,255,34,1150840828,1150840828,1,'The Lanius CMS Team',1,0,0,'','',0,0);

DROP TABLE #k7xp1_content_comment;

CREATE TABLE #k7xp1_content_comment (
id INTEGER AUTO_INCREMENT,
comment_id INTEGER NOT NULL DEFAULT 0,
title VARCHAR(255) NOT NULL,
name VARCHAR(50) NOT NULL,
comment VARCHAR(255) NOT NULL,
date INTEGER NOT NULL,
published INTEGER NOT NULL DEFAULT 1,
private INTEGER NOT NULL DEFAULT 0,
PRIMARY KEY (id) 
);

DROP TABLE #k7xp1_content_frontpage;

CREATE TABLE #k7xp1_content_frontpage (
id INTEGER AUTO_INCREMENT,
ordering INTEGER NOT NULL DEFAULT 0,
PRIMARY KEY (id) 
);

INSERT INTO #k7xp1_content_frontpage (id,ordering) VALUES (1,0);

DROP TABLE #k7xp1_rating;

CREATE TABLE #k7xp1_rating (
id INTEGER AUTO_INCREMENT,
itemid INTEGER NOT NULL,
rating_sum INTEGER NOT NULL DEFAULT 0,
rating_count INTEGER NOT NULL DEFAULT 0,
lastip TEXT NOT NULL,
component VARCHAR(20) NOT NULL DEFAULT '',
PRIMARY KEY (id) 
);

DROP TABLE #k7xp1_downloads;

CREATE TABLE #k7xp1_downloads (
id INTEGER AUTO_INCREMENT,
catid INTEGER NOT NULL DEFAULT 0,
title VARCHAR(255) NOT NULL,
url TEXT NOT NULL,
image_url TEXT NOT NULL,
website TEXT NOT NULL,
description TEXT,
filesize INTEGER NOT NULL,
add_date INTEGER NOT NULL,
mod_date INTEGER NOT NULL,
down_date INTEGER NOT NULL DEFAULT 0,
hits INTEGER NOT NULL DEFAULT 0,
ordering INTEGER NOT NULL DEFAULT 0,
published INTEGER NOT NULL DEFAULT 0,
userid INTEGER NOT NULL DEFAULT 0,
author VARCHAR(255) NOT NULL,
flags INTEGER NOT NULL DEFAULT 1,
PRIMARY KEY (id) 
);

DROP TABLE #k7xp1_drabots;

CREATE TABLE #k7xp1_drabots (
id INTEGER AUTO_INCREMENT,
name VARCHAR(100) NOT NULL,
type VARCHAR(30) NOT NULL,
element VARCHAR(100) NOT NULL,
showon VARCHAR(255) NOT NULL DEFAULT '',
access INTEGER NOT NULL DEFAULT 0,
ordering INTEGER NOT NULL DEFAULT 0,
iscore INTEGER NOT NULL DEFAULT 0,
params TEXT,
PRIMARY KEY (id) 
);

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (1,'Vote Drabot','content','dravote','_2_',9,0,1,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (2,'Search Links Drabot','search','searchlinks','',0,2,1,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (3,'Comment Drabot','content','dracom','',0,3,1,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (4,'Search Comment Drabot','search','searchcomment','',0,5,1,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (5,'Search Events Drabot','search','searchevent','',0,6,1,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (6,'Search FAQs Drabot','search','searchfaq','',0,7,1,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (7,'Download Drabot','content','dradown','',9,8,1,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (8,'Language alternate links','core','draaltlinks','',0,9,1,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (9,'Downloads rating','download','dradownvote','',0,10,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (10,'Search downloads drabot','search','searchdownload','',0,11,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (11,'Wrapper Drabot','content','drawrapper','',9,12,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (12,'Search gallery drabot','search','searchgallery','',0,13,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (13,'LDAP authentication drabot','core','auth_ldap','',9,14,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (14,'PHP mailer','mail','phpmail','',0,15,9,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (15,'BSD mailer','mail','bsdmail','',9,16,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (16,'SMTP mailer','mail','smtpmail','',9,17,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (17,'Sendmail mailer','mail','smmail','',9,18,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (18,'DB session','core','dbsession','',9,19,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (20,'Native Midas/MSHTML WYSIWYG editor','editor','native_editor','',9,21,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (21,'FCK Editor','editor','fckeditor','',0,22,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (22,'Admin javascript menu','admin_menu','jsmenu','',3,23,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (23,'Admin CSS menu','admin_menu','cssmenu','',9,24,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (24,'Syslog logger','logger','syslog','',9,25,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (25,'File logger','logger','filelog','',9,26,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (26,'DB mailer','mail','dbmail','',9,27,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (27,'Mailbox mailer','mail','mailbox','',9,28,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (28,'Content email cloaker','content','ctmailcloak','',9,29,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (29,'LDAP registration drabot','core','reg_ldap','',9,30,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (30,'Account filtering drabot','core','reg_filter','',0,31,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (31,'Message user options','core','msg_options','',0,32,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (32,'FAQ admin news','admin_news','faq_news','',0,33,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (33,'Downloads admin news','admin_news','downloads_news','',0,34,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (34,'Gallery admin news','admin_news','gallery_news','',0,35,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (35,'Weblinks admin news','admin_news','weblinks_news','',0,36,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (36,'Stream drabot','content','drastream','',9,37,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (37,'Forum profile options','core','forum_profile','',0,38,0,'');

INSERT INTO #k7xp1_drabots (id,name,type,element,showon,access,ordering,iscore,params) VALUES (38,'CAPTCHA Security Images','captcha','captchasi','',0,39,0,'');

DROP TABLE #k7xp1_event;

CREATE TABLE #k7xp1_event (
id INTEGER AUTO_INCREMENT,
title VARCHAR(255) NOT NULL,
description TEXT,
sdate INTEGER NOT NULL DEFAULT 0,
edate INTEGER NOT NULL DEFAULT 0,
period INTEGER NOT NULL DEFAULT 0,
published INTEGER NOT NULL DEFAULT 0,
access INTEGER NOT NULL DEFAULT 0,
userid INTEGER NOT NULL DEFAULT 0,
PRIMARY KEY (id) 
);

DROP TABLE #k7xp1_faq;

CREATE TABLE #k7xp1_faq (
id INTEGER AUTO_INCREMENT,
catid INTEGER NOT NULL DEFAULT 0,
question TEXT,
answer TEXT,
published INTEGER NOT NULL DEFAULT 0,
ordering INTEGER NOT NULL DEFAULT 0,
userid INTEGER NOT NULL DEFAULT 0,
created INTEGER NOT NULL DEFAULT 0,
PRIMARY KEY (id) 
);

INSERT INTO #k7xp1_faq (id,catid,question,answer,published,ordering,userid,created) VALUES (2,33,'Is it possible to switch between databases in an easy way?','The SQL backup format of Lanius CMS is compatible within Lanius CMS with any database system. You can easily switch between any database with a few clicks, the CMS will automatically export the data from your database (flatfile or whatever) and import it in the new one, so that migrations between different database systems are painless. ',1,1,0,1219830462);

INSERT INTO #k7xp1_faq (id,catid,question,answer,published,ordering,userid,created) VALUES (1,33,'If I start a website with a flatfile db is it much more difficult to upgrade the data than if I would use MySQL (or such)?','No, it will be possible to easily switch between databases, see relative question ',1,2,0,1219830462);

INSERT INTO #k7xp1_faq (id,catid,question,answer,published,ordering,userid,created) VALUES (3,31,'How do I reinstall without loosing all my data?','Use the database backup feature (System -&gt; Database -&gt; Manage Backups), first make a Backup and then save it locally. On the next installation you can get back all your data online simply restoring that file. 

If you are not going to reinstall with a newer version you could perform a &quot;Tarball backup&quot; and restore the entire website instead. ',1,3,0,1219830462);

INSERT INTO #k7xp1_faq (id,catid,question,answer,published,ordering,userid,created) VALUES (4,32,'How can I install a new language?','From System -&gt; Language manager choose New and install it as any other package',1,5,0,1219830462);

INSERT INTO #k7xp1_faq (id,catid,question,answer,published,ordering,userid,created) VALUES (5,32,'How can I install a Limbo/Mambo/Joomla template?','You can''t. Other CMSes templates are not supported, however we are working on automatic conversion tools and they should be available very soon. ',1,4,0,1219830462);

INSERT INTO #k7xp1_faq (id,catid,question,answer,published,ordering,userid,created) VALUES (6,33,'I restored my database backup and I can no more login as admin. Help!','Click the &quot;forgot your password&quot; link or else go to edit manually the SQL file and remove the &quot;INSERT INTO dk_users...&quot; line of the admin user account. This will force the CMS to use the currently logged in administrator account after the restore.',1,7,0,1219830462);

INSERT INTO #k7xp1_faq (id,catid,question,answer,published,ordering,userid,created) VALUES (8,29,'How can I update Lanius CMS?','Click on System -&gt; Online updates and select the patch you need. If you can''t update through those automatic updates, just make an SQL or a Tarball backup (with attached SQL backup), reinstall and then restore it.',1,9,0,1219830462);

INSERT INTO #k7xp1_faq (id,catid,question,answer,published,ordering,userid,created) VALUES (9,29,'How can I translate Lanius CMS to a new language?','Before starting, are you sure that the translation for your language has not already been developed or worse is in development? Check the translation tasks list, if your language is not there you may start the actual translation, but first ask to the Lanius CMS Team! So the translation task will be assigned to you. You can find more on our documentation, and also don''t forget to visit the forums',1,8,0,1219830462);

INSERT INTO #k7xp1_faq (id,catid,question,answer,published,ordering,userid,created) VALUES (10,29,'How can I activate a language?','You can''t, just because the language is always active! Enable the Select language module and/or see the tutorials about language translation and testing',1,10,0,1219830462);

INSERT INTO #k7xp1_faq (id,catid,question,answer,published,ordering,userid,created) VALUES (11,29,'The admin backend is still in English. Why don''t I see my own language?','When you created the admin user that language was not installed. Now (if it is installed!) see the below answer. ',1,11,0,1219830462);

INSERT INTO #k7xp1_faq (id,catid,question,answer,published,ordering,userid,created) VALUES (12,29,'How can I change an user''s language?','Once logged in click on &quot;Your details&quot; and change the language. If you are an user of Administrator level you can also go to System -&gt; Users -&gt; Manage users to perform the same operation. ',1,12,0,1219830462);

INSERT INTO #k7xp1_faq (id,catid,question,answer,published,ordering,userid,created) VALUES (13,29,'How do I add a Youtube video?','Modules -&gt; Manage Modules -&gt; New (mod_stream) and fill in the new streaming module properties. ',1,13,0,1219830462);

INSERT INTO #k7xp1_faq (id,catid,question,answer,published,ordering,userid,created) VALUES (14,29,'How do I create a static page','To create static pages:
<ul>
<li>disable vote and comment drabots from the admin backend drabots menu (they will be disabled for all content items)</li>
<li>have a content item in an unpublished content section, for example the default &quot;General Content&quot;</li>
<li>go to Menu -&gt; mainmenu and create a new menu item of type &quot;Content item&quot;</li>
<li>select your content item from there and...</li>
</ul>
You have a static content page
',1,29,0,1219830462);

DROP TABLE #k7xp1_gallery;

CREATE TABLE #k7xp1_gallery (
id INTEGER AUTO_INCREMENT,
catid INTEGER NOT NULL DEFAULT 0,
title VARCHAR(50) NOT NULL,
description TEXT,
url TEXT NOT NULL,
date INTEGER NOT NULL,
hits INTEGER NOT NULL DEFAULT 0,
published INTEGER NOT NULL DEFAULT 0,
ordering INTEGER NOT NULL DEFAULT 0,
userid INTEGER NOT NULL DEFAULT 0,
PRIMARY KEY (id) 
);

INSERT INTO #k7xp1_gallery (id,catid,title,description,url,date,hits,published,ordering,userid) VALUES (1,2,'Hamadryas februa','This specie is particularly intricate in its patterning and color of the flies.','hamadryas_februa.jpg',1177308618,0,1,1,0);

DROP TABLE #k7xp1_gallery_category;

CREATE TABLE #k7xp1_gallery_category (
id INTEGER AUTO_INCREMENT,
gallery_path TEXT,
thumbs_path TEXT,
PRIMARY KEY (id) 
);

INSERT INTO #k7xp1_gallery_category (id,gallery_path,thumbs_path) VALUES (2,'media/gallery/butterflies/','media/gallery/thumbs/butterflies/');

DROP TABLE #k7xp1_guestbook;

CREATE TABLE #k7xp1_guestbook (
id INTEGER AUTO_INCREMENT,
name VARCHAR(50) NOT NULL,
email VARCHAR(50) NOT NULL,
url TEXT NOT NULL,
country VARCHAR(50) NOT NULL,
title VARCHAR(255) NOT NULL,
message TEXT,
reply TEXT,
ip TEXT NOT NULL,
date INTEGER NOT NULL,
PRIMARY KEY (id) 
);

DROP TABLE #k7xp1_menu;

CREATE TABLE #k7xp1_menu (
id INTEGER AUTO_INCREMENT,
menutype VARCHAR(255) NOT NULL,
name VARCHAR(100) NOT NULL,
link TEXT,
link_type VARCHAR(50) NOT NULL,
parent INTEGER NOT NULL DEFAULT 0,
componentid INTEGER NOT NULL DEFAULT 0,
sublevel INTEGER NOT NULL DEFAULT 0,
ordering INTEGER NOT NULL DEFAULT 0,
browsernav INTEGER NOT NULL DEFAULT 0,
access INTEGER NOT NULL DEFAULT 9,
params TEXT,
PRIMARY KEY (id) 
);

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (2,'mainmenu','Weblinks','index.php?option=weblinks','component',0,3,0,13,0,0,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (3,'mainmenu','Contact','index.php?option=message','component',0,48,0,16,0,0,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (10,'mainmenu','News','index.php?option=content&task=section&id=2','cs',0,2,0,3,0,0,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (22,'mainmenu','Home','index.php?option=frontpage','component',0,18,0,2,0,0,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (35,'usermenu','Administration','admin.php','url',0,0,0,0,0,3,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (36,'mainmenu','Search','index.php?option=search','component',0,10,0,17,0,0,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (37,'usermenu','My user profile','index.php?option=user','component',0,20,0,1,0,1,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (38,'usermenu','Submit news','index.php?option=content&task=new','url',0,0,0,2,0,1,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (39,'usermenu','Submit a weblink','index.php?option=weblinks&task=new','url',0,0,0,4,0,1,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (42,'topmenu','News','10','cl',0,0,0,3,0,0,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (43,'topmenu','Contact','3','cl',0,0,0,2,0,0,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (44,'topmenu','Links','2','cl',0,0,0,1,0,0,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (45,'topmenu','Home','22','cl',0,0,0,4,0,0,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (46,'mainmenu','Events','index.php?option=event','component',0,26,0,18,0,0,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (47,'mainmenu','Sitemap','index.php?option=sitemap','component',0,25,0,22,0,0,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (48,'mainmenu','FAQ','index.php?option=faq','component',0,27,0,23,0,0,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (49,'mainmenu','Gallery','index.php?option=gallery','component',0,30,0,19,0,0,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (50,'mainmenu','Downloads','index.php?option=downloads','component',0,34,0,20,0,0,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (51,'mainmenu','Guestbook','index.php?option=guestbook','component',0,33,0,24,0,0,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (52,'mainmenu','Forum','index.php?option=forum','component',0,37,0,21,0,0,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (53,'usermenu','Submit a download','index.php?option=downloads&task=new','url',0,0,0,3,0,1,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (54,'usermenu','Submit an image','index.php?option=gallery&task=new','url',0,0,0,5,0,1,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (55,'servicemenu','Login','index.php?option=login','component',0,12,0,1,0,0,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (56,'mainmenu','Registration','index.php?option=registration','component',0,57,0,6,0,0,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (57,'mainmenu','Polls','index.php?option=polls','component',0,9,0,3,0,0,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (58,'servicemenu','Syndicate','index2.php?option=syndicate&no_html=1','component',0,58,0,3,0,0,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (59,'servicemenu','Banners','index.php?option=banner','component',0,14,0,3,0,0,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (60,'servicemenu','User','index.php?option=user','component',0,20,0,3,0,0,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (61,'servicemenu','About','index.php?option=about','component',0,60,0,3,0,0,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (63,'servicemenu','File browser','index2.php?option=fb','component',0,62,0,3,0,1,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (64,'servicemenu','Remote blog server instance','index2.php?option=remoteblog&no_html=1','component',0,64,0,3,0,9,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (65,'servicemenu','Lanius CMS services','','component',0,65,0,3,0,0,'');

INSERT INTO #k7xp1_menu (id,menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES (66,'usermenu','My inbox','index.php?option=message&task=inbox','component',0,21,0,2,0,0,'');

DROP TABLE #k7xp1_messages;

CREATE TABLE #k7xp1_messages (
id INTEGER AUTO_INCREMENT,
userid INTEGER NOT NULL DEFAULT 0,
name VARCHAR(50) NOT NULL,
email VARCHAR(50) NOT NULL,
message_subject VARCHAR(255),
message_text LONGTEXT,
cdate INTEGER NOT NULL DEFAULT 0,
PRIMARY KEY (id) 
);

DROP TABLE #k7xp1_receipts;

CREATE TABLE #k7xp1_receipts (
id INTEGER AUTO_INCREMENT,
userid INTEGER NOT NULL DEFAULT 0,
message_id INTEGER NOT NULL DEFAULT 0,
status INTEGER NOT NULL DEFAULT 0,
PRIMARY KEY (id) 
);

DROP TABLE #k7xp1_modules;

CREATE TABLE #k7xp1_modules (
id INTEGER AUTO_INCREMENT,
title TEXT,
message TEXT,
ordering INTEGER NOT NULL DEFAULT 0,
position VARCHAR(10) NOT NULL,
module VARCHAR(50) NOT NULL,
access INTEGER NOT NULL DEFAULT 0,
showtitle INTEGER NOT NULL DEFAULT 1,
showon TEXT,
params TEXT,
iscore INTEGER NOT NULL DEFAULT 0,
instance INTEGER NOT NULL DEFAULT 0,
PRIMARY KEY (id) 
);

INSERT INTO #k7xp1_modules (id,title,message,ordering,position,module,access,showtitle,showon,params,iscore,instance) VALUES (1,'Main menu','',1,'left','mod_menu',0,1,'','a:3:{s:10:"menu_style";s:8:"vertical";s:8:"menutype";s:8:"mainmenu";s:12:"custom_class";s:0:"";}',1,0);

INSERT INTO #k7xp1_modules (id,title,message,ordering,position,module,access,showtitle,showon,params,iscore,instance) VALUES (2,'Newsflash','',5,'top','mod_newsflash',0,1,'_22_','a:2:{s:12:"custom_class";s:0:"";s:5:"catid";s:2:"34";}',0,0);

INSERT INTO #k7xp1_modules (id,title,message,ordering,position,module,access,showtitle,showon,params,iscore,instance) VALUES (3,'Polls','',7,'right','mod_polls',0,1,'_22_','',0,0);

INSERT INTO #k7xp1_modules (id,title,message,ordering,position,module,access,showtitle,showon,params,iscore,instance) VALUES (4,'Content','',9,'user1','mod_content',9,1,'_22_','',1,0);

INSERT INTO #k7xp1_modules (id,title,message,ordering,position,module,access,showtitle,showon,params,iscore,instance) VALUES (5,'Search','',8,'user4','mod_search',0,0,'','',0,0);

INSERT INTO #k7xp1_modules (id,title,message,ordering,position,module,access,showtitle,showon,params,iscore,instance) VALUES (7,'Select template','',10,'left','mod_template',9,1,'','',1,0);

INSERT INTO #k7xp1_modules (id,title,message,ordering,position,module,access,showtitle,showon,params,iscore,instance) VALUES (8,'Login','',2,'left','mod_login',0,1,'','',0,0);

INSERT INTO #k7xp1_modules (id,title,message,ordering,position,module,access,showtitle,showon,params,iscore,instance) VALUES (9,'Top menu','',12,'user3','mod_menu',0,0,'','a:3:{s:8:"menutype";s:7:"topmenu";s:10:"menu_style";s:9:"flat_list";s:12:"custom_class";s:7:"topmenu";}',2,0);

INSERT INTO #k7xp1_modules (id,title,message,ordering,position,module,access,showtitle,showon,params,iscore,instance) VALUES (10,'Syndicate','',18,'left','mod_syndicate',0,1,'','',0,0);

INSERT INTO #k7xp1_modules (id,title,message,ordering,position,module,access,showtitle,showon,params,iscore,instance) VALUES (11,'Stats','',15,'left','mod_stats',9,1,'','',0,0);

INSERT INTO #k7xp1_modules (id,title,message,ordering,position,module,access,showtitle,showon,params,iscore,instance) VALUES (12,'Latest News','',14,'user1','mod_latest_news',0,1,'','a:4:{s:4:"desc";s:1:"0";s:5:"count";s:1:"5";s:5:"catid";s:1:"7";s:12:"custom_class";s:0:"";}',0,0);

INSERT INTO #k7xp1_modules (id,title,message,ordering,position,module,access,showtitle,showon,params,iscore,instance) VALUES (13,'NewsFeed','',19,'left','mod_newsfeed',0,0,'','',0,0);

INSERT INTO #k7xp1_modules (id,title,message,ordering,position,module,access,showtitle,showon,params,iscore,instance) VALUES (14,'Whos Online','',17,'right','mod_whosonline',9,1,'_22_','',2,0);

INSERT INTO #k7xp1_modules (id,title,message,ordering,position,module,access,showtitle,showon,params,iscore,instance) VALUES (17,'Popular','',4,'user2','mod_popular',0,1,'','a:4:{s:4:"desc";s:1:"0";s:5:"count";s:1:"5";s:5:"catid";s:1:"7";s:12:"custom_class";s:0:"";}',0,0);

INSERT INTO #k7xp1_modules (id,title,message,ordering,position,module,access,showtitle,showon,params,iscore,instance) VALUES (21,'Banner','',13,'banner','mod_banner',0,0,'','',0,0);

INSERT INTO #k7xp1_modules (id,title,message,ordering,position,module,access,showtitle,showon,params,iscore,instance) VALUES (22,'Archive','',16,'right','mod_archive',0,1,'_22_','',0,0);

INSERT INTO #k7xp1_modules (id,title,message,ordering,position,module,access,showtitle,showon,params,iscore,instance) VALUES (25,'User Menu','',3,'left','mod_menu',1,1,'','a:3:{s:10:"menu_style";s:8:"vertical";s:8:"menutype";s:8:"usermenu";s:12:"custom_class";s:0:"";}',2,0);

INSERT INTO #k7xp1_modules (id,title,message,ordering,position,module,access,showtitle,showon,params,iscore,instance) VALUES (26,'Event Calendar','',20,'right','mod_eventcal',0,1,'_22_','',0,0);

INSERT INTO #k7xp1_modules (id,title,message,ordering,position,module,access,showtitle,showon,params,iscore,instance) VALUES (27,'Media player','',24,'left','mod_stream',9,1,'','',0,0);

INSERT INTO #k7xp1_modules (id,title,message,ordering,position,module,access,showtitle,showon,params,iscore,instance) VALUES (28,'Change language','',23,'left','mod_lang',0,1,'','',1,0);

INSERT INTO #k7xp1_modules (id,title,message,ordering,position,module,access,showtitle,showon,params,iscore,instance) VALUES (29,'Debug','',21,'debug','mod_debug',5,0,'','',1,0);

INSERT INTO #k7xp1_modules (id,title,message,ordering,position,module,access,showtitle,showon,params,iscore,instance) VALUES (30,'Downloads','',25,'right','mod_downloads',9,1,'','',0,0);

INSERT INTO #k7xp1_modules (id,title,message,ordering,position,module,access,showtitle,showon,params,iscore,instance) VALUES (31,'W3C validations','',26,'left','mod_validate',0,0,'','a:4:{s:10:"show_xhtml";s:1:"1";s:13:"xhtml_version";s:3:"1.0";s:8:"show_css";s:1:"0";s:11:"css_version";s:3:"2.1";}',0,0);

INSERT INTO #k7xp1_modules (id,title,message,ordering,position,module,access,showtitle,showon,params,iscore,instance) VALUES (32,'Wrapper module','',27,'left','mod_wrapper',9,0,'','',1,0);

INSERT INTO #k7xp1_modules (id,title,message,ordering,position,module,access,showtitle,showon,params,iscore,instance) VALUES (33,'Get Firefox','',28,'left','mod_getff',0,0,'','',0,0);

DROP TABLE #k7xp1_polls_data;

CREATE TABLE #k7xp1_polls_data (
id INTEGER AUTO_INCREMENT,
pollid INTEGER NOT NULL DEFAULT 0,
polloption VARCHAR(255) NOT NULL,
hits INTEGER NOT NULL DEFAULT 0,
PRIMARY KEY (id) 
);

INSERT INTO #k7xp1_polls_data (id,pollid,polloption,hits) VALUES (1,26,'Fast! It is already installed!',0);

INSERT INTO #k7xp1_polls_data (id,pollid,polloption,hits) VALUES (2,26,'A piece of cake',0);

INSERT INTO #k7xp1_polls_data (id,pollid,polloption,hits) VALUES (3,26,'Not straight-forward but I worked it out',0);

INSERT INTO #k7xp1_polls_data (id,pollid,polloption,hits) VALUES (4,26,'I had no idea and got somebody else to do it',0);

DROP TABLE #k7xp1_polls_votes;

CREATE TABLE #k7xp1_polls_votes (
id INTEGER AUTO_INCREMENT,
userid INTEGER NOT NULL DEFAULT 0,
ip TEXT NOT NULL,
pollid INTEGER NOT NULL DEFAULT 0,
date INTEGER NOT NULL,
PRIMARY KEY (id) 
);

DROP TABLE #k7xp1_sections;

CREATE TABLE #k7xp1_sections (
id INTEGER AUTO_INCREMENT,
title VARCHAR(255) NOT NULL,
name VARCHAR(255) NOT NULL,
image VARCHAR(100) NOT NULL DEFAULT '',
image_position VARCHAR(10) NOT NULL DEFAULT 'left',
description TEXT,
published INTEGER NOT NULL DEFAULT 0,
ordering INTEGER NOT NULL DEFAULT 0,
access INTEGER NOT NULL DEFAULT 0,
count INTEGER NOT NULL DEFAULT 0,
PRIMARY KEY (id) 
);

INSERT INTO #k7xp1_sections (id,title,name,image,image_position,description,published,ordering,access,count) VALUES (1,'General Content','General Content','','left','',1,0,0,2);

INSERT INTO #k7xp1_sections (id,title,name,image,image_position,description,published,ordering,access,count) VALUES (2,'News','News','topicgeneral.png','left','This is the main news section with the various news categories given below',1,1,0,2);

DROP TABLE #k7xp1_simple_stats;

CREATE TABLE #k7xp1_simple_stats (
id INTEGER AUTO_INCREMENT,
ip TEXT NOT NULL,
date INTEGER NOT NULL DEFAULT 0,
count INTEGER NOT NULL DEFAULT 0,
uid INTEGER NOT NULL DEFAULT 0,
PRIMARY KEY (id) 
);

INSERT INTO #k7xp1_simple_stats (id,ip,date,count,uid) VALUES (1,'',0,0,0);

DROP TABLE #k7xp1_subsites;

CREATE TABLE #k7xp1_subsites (
id INTEGER AUTO_INCREMENT,
subpath VARCHAR(255),
PRIMARY KEY (id) 
);

DROP TABLE #k7xp1_users;

CREATE TABLE #k7xp1_users (
id INTEGER AUTO_INCREMENT,
name VARCHAR(50) NOT NULL,
username VARCHAR(25) NOT NULL,
email VARCHAR(100) NOT NULL,
password VARCHAR(100) NOT NULL,
clear_password VARCHAR(100) NOT NULL DEFAULT '',
published INTEGER NOT NULL DEFAULT 1,
gid INTEGER NOT NULL DEFAULT 1,
registerDate INTEGER NOT NULL,
lastvisitDate INTEGER NOT NULL,
lang VARCHAR(2) NOT NULL DEFAULT '',
timezone VARCHAR(100) DEFAULT '',
PRIMARY KEY (id) 
);

INSERT INTO #k7xp1_users (id,name,username,email,password,clear_password,published,gid,registerDate,lastvisitDate,lang,timezone) VALUES (42,'Webmaster','admin','webmaster@example.com','5f4dcc3b5aa765d61d8327deb882cf99','',1,5,1255540886,1255540886,'','');

DROP TABLE #k7xp1_weblinks;

CREATE TABLE #k7xp1_weblinks (
id INTEGER AUTO_INCREMENT,
catid INTEGER NOT NULL DEFAULT 0,
title VARCHAR(50) NOT NULL,
url TEXT NOT NULL,
description TEXT,
date INTEGER NOT NULL,
hits INTEGER NOT NULL DEFAULT 0,
published INTEGER NOT NULL DEFAULT 0,
ordering INTEGER NOT NULL DEFAULT 0,
userid INTEGER NOT NULL DEFAULT 0,
PRIMARY KEY (id) 
);

INSERT INTO #k7xp1_weblinks (id,catid,title,url,description,date,hits,published,ordering,userid) VALUES (1,22,'Lanius CMS SourceForge Project site','http://www.laniuscms.org/','This is the official Lanius CMS website for all your news and support. So for everything related to Lanius CMS come here.',0,0,1,0,0);

INSERT INTO #k7xp1_weblinks (id,catid,title,url,description,date,hits,published,ordering,userid) VALUES (2,22,'Lanius CMS official documentation','http://www.laniuscms.org/docs/','This is the official online documentation facility',0,0,1,2,0);

DROP TABLE #k7xp1_links;

CREATE TABLE #k7xp1_links (
id INTEGER AUTO_INCREMENT,
rel VARCHAR(50) NOT NULL,
type VARCHAR(50) NOT NULL,
title VARCHAR(255) NOT NULL,
href VARCHAR(255) NOT NULL,
access INTEGER NOT NULL DEFAULT 0,
PRIMARY KEY (id) 
);

INSERT INTO #k7xp1_links (id,rel,type,title,href,access) VALUES (1,'alternate','application/rss+xml','Home','index2.php?option=syndicate&no_html=1&Itemid=58',0);

INSERT INTO #k7xp1_links (id,rel,type,title,href,access) VALUES (2,'sitemap','application/xml','Sitemap','sitemap.xml',0);

INSERT INTO #k7xp1_links (id,rel,type,title,href,access) VALUES (3,'EditURI','application/rsd+xml','RSD','index2.php?option=remoteblog&no_html=1&Itemid=64',0);

DROP TABLE #k7xp1_packages;

CREATE TABLE #k7xp1_packages (
id INTEGER AUTO_INCREMENT,
type VARCHAR(10) NOT NULL DEFAULT '',
name VARCHAR(255) NOT NULL,
version VARCHAR(50) NOT NULL,
PRIMARY KEY (id) 
);

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (1,'core','Lanius CMS','0.5.2 r1668');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (2,'drabot','drawrapper','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (3,'drabot','searchcomment','1.0.0');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (4,'drabot','dracom','1.0.5');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (5,'drabot','dradown','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (6,'drabot','searchfaq','4.5.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (7,'drabot','dravote','4.5.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (8,'drabot','searchgallery','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (9,'drabot','searchdownload','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (10,'drabot','draaltlinks','0.2');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (11,'drabot','searchevent','1.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (12,'module','mod_downloads','0.2');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (13,'module','mod_login','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (14,'module','mod_popular','0.2');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (15,'module','mod_wrapper','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (16,'module','mod_poll','0.2');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (17,'module','mod_latest_news','0.2');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (18,'module','mod_banner','0.2');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (19,'module','mod_archive','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (20,'module','mod_stats','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (21,'module','mod_newsfeed','0.2');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (22,'module','mod_eventcal','0.2');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (23,'module','mod_whosonline','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (24,'module','mod_syndicate','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (25,'module','mod_stream','0.2');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (26,'module','mod_lang','1.0');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (27,'module','mod_newsflash','0.3');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (28,'module','mod_search','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (29,'module','mod_validate','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (30,'module','mod_menu','0.2');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (31,'component','com_faq','1.0');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (32,'component','com_content','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (33,'component','com_guestbook','2.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (34,'component','com_frontpage','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (35,'component','com_event','2.0');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (36,'component','com_forum','0.4');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (37,'component','com_login','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (38,'component','com_downloads','0.3');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (39,'component','com_gallery','0.5');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (40,'component','com_syndicate','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (41,'component','com_search','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (42,'component','com_weblinks','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (43,'component','com_registration','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (44,'component','com_wrapper','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (45,'drabot','smmail','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (46,'drabot','bsdmail','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (47,'drabot','cssmenu','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (48,'drabot','auth_ldap','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (49,'drabot','fckeditor','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (50,'drabot','phpmail','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (51,'drabot','native_editor','0.2');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (52,'drabot','dbsession','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (53,'drabot','smtpmail','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (54,'drabot','jsmenu','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (55,'module','mod_getff','1.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (56,'component','com_service','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (57,'drabot','syslog','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (58,'drabot','filelog','0.2');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (59,'drabot','dbmail','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (60,'drabot','mailbox','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (61,'content','ctmailcloak','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (62,'drabot','reg_ldap','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (63,'drabot','reg_filter','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (64,'drabot','msg_options','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (65,'drabot','faq_news','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (66,'drabot','downloads_news','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (67,'drabot','gallery_news','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (68,'drabot','weblinks_news','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (69,'drabot','drastream','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (70,'drabot','forum_profile','0.1');

INSERT INTO #k7xp1_packages (id,type,name,version) VALUES (71,'drabot','captchasi','0.1');

DROP TABLE #k7xp1_sessions;

CREATE TABLE #k7xp1_sessions (
id INTEGER AUTO_INCREMENT,
data TEXT,
modified INTEGER,
PRIMARY KEY (id) 
);

DROP TABLE #k7xp1_mail_queue;

CREATE TABLE #k7xp1_mail_queue (
id INTEGER AUTO_INCREMENT,
queued INTEGER NOT NULL,
recipient VARCHAR(255) NOT NULL,
subject TEXT,
body LONGTEXT,
headers TEXT,
_from VARCHAR(255) NOT NULL,
cc TEXT,
bcc TEXT,
sent INTEGER NOT NULL DEFAULT 0,
PRIMARY KEY (id) 
);

DROP TABLE #k7xp1_view_filter;

CREATE TABLE #k7xp1_view_filter (
id INTEGER AUTO_INCREMENT,
userid INTEGER NOT NULL,
component VARCHAR(255) NOT NULL,
task VARCHAR(255) NOT NULL,
params TEXT,
PRIMARY KEY (id) 
);

DROP TABLE #k7xp1_contacts;

CREATE TABLE #k7xp1_contacts (
id INTEGER AUTO_INCREMENT,
userid INTEGER NOT NULL,
flags INTEGER NOT NULL DEFAULT 0,
PRIMARY KEY (id) 
);

INSERT INTO #k7xp1_contacts (userid,flags) VALUES (42,3);

DROP TABLE #k7xp1_auth_users;

CREATE TABLE #k7xp1_auth_users (
id INTEGER AUTO_INCREMENT,
userid INTEGER NOT NULL,
authkey VARCHAR(255) NOT NULL,
created INTEGER NOT NULL,
PRIMARY KEY (id) 
);


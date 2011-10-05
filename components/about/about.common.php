<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Lanius CMS Team about page
# @author legolas558
#

function about_page() {
	global $d_db, $d_subpath, $my;

	$thispath = $d_subpath.'docs/';

?><table width="60%" border="0" align="center" cellpadding="5" cellspacing="1">
  <tr>
    <td><h2>Lanius CMS <?php echo cms_version(($my->gid>=4)); ?></h2></td>
  </tr>
  <tr>
    <td class="tabbody"><table width="100%" border="0" cellspacing="0" cellpadding="5">
        <tr>
          <td width="32%"><a href="http://www.laniuscms.org/" target="_blank" title="Lanius CMS Official Site"><img src="<?php echo $d_subpath; ?>media/common/logo.png" width="140" border="0" alt="Lanius CMS logo" /></a></td>
          <td width="68%" align="center"><?php echo _MANIFEST_DESC; ?></td>
        </tr>
      </table>
      <table width="100%" border="0" cellpadding="5" cellspacing="1">
        <tr>
          <td colspan="3"><h3><?php echo _ABOUT_TEAM; ?></h3></td>
        </tr>
        <tr>
		<td width="10%">Daniele C.</td>
		<td width="18%">Lead developer</td><td width="75%">v0.1-v0.5 main development</td>
        </tr>
        <tr>
          <td>consc198</td>
          <td>QA team manager</td><td>quality assurance testing</td>
        </tr>
        <tr>
          <td>R. Savarese</td>
          <td>UI consultant, CSS/Templates team manager</td><td>official templates, general layout, UI advices, CSS consultant</td>
        </tr>
        <tr>
          <td colspan="3"><h3><?php echo _ABOUT_TESTERS; ?></h3></td>
        </tr>
        <tr>
          <td colspan="3"><a target="_blank" href="http://sourceforge.net/users/trex1512/">Terry Fage</a>, <a  target="_blank" href="http://sourceforge.net/users/franky2004/">franky2004</a>, <a target="_blank" href="http://sourceforge.net/users/andieb/">andieb</a>, <a href="http://sourceforge.net/users/tashunka01/">Giorgio G.</a>, <a target="_blank" href="http://sourceforge.net/users/carlmccall/">Carl McCall</a>, <a target="_blank" href="http://sourceforge.net/users/maxibot/">maxibot</a>, <a  target="_blank" href="http://sourceforge.net/users/billyv/">billyv</a>, <a href="http://sourceforge.net/users/awrog/">awrog</a>, <a target="_blank" href="http://sourceforge.net/users/helig/">helig</a>, <a target="_blank" href="http://sourceforge.net/users/atishae/">atishae</a>, <a target="_blank" href="http://sourceforge.net/users/rasto_s/">rasto_s</a>, <a target="_blank" href="http://sourceforge.net/users/consc198/">consc198</a>  <?php echo _ABOUT_AND_MANY_OTHERS; ?>
		  </td>
        </tr>
        <tr>
          <td colspan="3"><h3><?php echo _ABOUT_OTHER_DEV_CON; ?></h3></td>
        </tr>
        <tr>
          <td colspan="3"><a target="_blank" href="<?php echo $thispath; ?>credits.txt">See credits record file</a></td>
		</tr>
        <tr>
          <td colspan="3"><h3><?php echo _MANIFEST_MW; ?></h3></td>
        </tr>
	<?php
		
		function generate_info($url, $label, $desc) {
		?><tr>
          <td width="33%"><a target="_blank" href="<?php echo $url; ?>"><?php echo $label; ?></a></td>
          <td colspan="2"><?php echo $desc; ?></td>
        </tr>
		<?php
		}
		
		switch ($d_db) {
			case 'gladius':
				generate_info('http://gladius.sourceforge.net/', 'Gladius DB', 'embedded flatfile database engine');
			break;
			case 'sqlite':
			case 'sqlitepo':
				generate_info('http://www.sqlite.org/', 'SQLite', 'a self-contained, embeddable, zero-configuration SQL database engine');
			break;
			case 'mysql':
			case 'mysqli':
			case 'mysqlt':
				generate_info('http://www.mysql.com/', 'MySQL', 'a multithreaded, multi-user SQL database management system');
			break;
			case 'postgres':
			case 'postgres7':
			case 'postgres8':
			case 'postgres64':
				generate_info('http://www.postgresql.org/', 'PostgreSQL', 'the world\'s most advanced open source database');
			break;
			case 'sybase':
			case 'sybase_ase':
				generate_info('http://www.sybase.com/', 'Sybase', 'Sybase database management system');
				break;
			default:
				generate_info('http://www.google.com/search?ie=UTF-8&oe=UTF-8&sourceid=navclient&gfns=1&q='.rawurlencode($d_db), $d_db, '-');
		}
		?><tr>
          <td width="33%"><a target="_blank" href="http://adodblite.sourceforge.net/">AdoDB lite</a></td>
          <td colspan="2">database abstraction layer</td>
        </tr><?php //TODO: 'OnCredits' event here ?>
        <tr>
          <td width="33%"><a target="_blank" href="http://anyxml.sourceforge.net/">anyXML</a></td>
          <td colspan="2">XML abstraction layer (PHP5 SimpleXML/PHP4 expat/MiniXML)</td>
        </tr>
        <tr>
          <td width="33%"><a target="_blank" href="http://jscook.sourceforge.net/JSCookMenu/">JSCookMenu</a></td>
          <td colspan="2">admin backend javascript menus </td>
        </tr>
        <tr>
          <td width="33%"><a target="_blank" href="http://www.phpconcept.net">PCLZip/PCLTar</a></td>
          <td colspan="2">archiving libraries</td>
        </tr>
        <tr>
          <td width="33%"><a target="_blank" href="http://phptarbackup.sourceforge.net/">PHP TarBackup</a></td>
          <td colspan="2">tarball site-wide backup feature</td>
        </tr>
        <tr>
          <td width="33%"><a target="_blank" href="http://www.white-hat-web-design.co.uk/articles/php-captcha.php">Captcha Security Images</a></td>
          <td colspan="2">CAPTCHA generation</td>
        </tr>
        <tr>
          <td width="33%"><a target="_blank" href="http://www.silverstripe.com/tree-control/">SilverStripe Tree Control</a></td>
          <td colspan="2">CSS/JavaScript tree for the dynamic sitemap</td>
        </tr>
        <tr>
          <td width="33%"><a target="_blank" href="http://sourceforge.net/projects/jscalendar">JSCalendar v1.0</a></td>
          <td colspan="2">DHTML popup calendar</td>
        </tr>
	<?php if (strnatcmp(phpversion(), '5')>=0) { ?>
        <tr>
          <td width="33%"><a target="_blank" href="http://www.digitaljunkies.ca/dompdf">DOMPDF class</a></td>
          <td colspan="2">PDF output generation</td>
        </tr><?php } ?>
        <tr>
          <td width="33%"><a target="_blank" href="http://phpxmlrpc.sourceforge.net/">XML-RPC implementation</a></td>
          <td colspan="2">XML-RPC PHP implementation used for remote services</td>
        </tr>
        <tr>
          <td width="33%"><a target="_blank" href="http://www.bioinformatics.org/phplabware/internal_utilities/htmLawed/index.php">htmLawed</a></td>
          <td colspan="2">PHP script to filter &amp; purify HTML code</td>
        </tr>
        <tr>
          <td width="33%"><a target="_blank" href="http://www.gerd-riesselmann.net/examples/testprogress.html">Javascript Progress Bar</a></td>
          <td colspan="2">A progress bar to show password quality</td>
        </tr>
        <tr>
          <td colspan="3">&nbsp;</td>
        </tr>
      </table></td>
  </tr>
</table>
<?php } ?>
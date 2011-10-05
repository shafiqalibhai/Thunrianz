<?php
/* (C) 2006 by legolas558
	SQL job splitter class
	Licensed under GPL
	http://sourceforge.net/projects/laniuscms/

 * This program is free software and open source software; you can redistribute
 * it and/or modify it under the terms of the GNU General Public License
 * version 2 as published by the Free Software Foundation
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA  or visit
 * http://www.gnu.org/licenses/gpl.html
 */

define('__SJS_INDEX', 0);
define('__SJS_TOTAL', 1);
define('__SJS_ERRORS', 2);

class SQLJobSplitter {
var $progress;
var $finished;
var $_prefix = '#__';

function SQLJobSplitter(&$var, $total) {
	$this->progress =& $var;
	if (empty($this->progress))
		$this->_init($total);
	$this->finished = ($this->progress[__SJS_TOTAL] == $this->progress[__SJS_INDEX]);
}

function _init($total) {
	$this->progress = array(__SJS_INDEX => 0, __SJS_TOTAL => $total, __SJS_ERRORS => 0);
}

function ExecuteJobs(&$queries, $prefix, $next_step_cb, $max_time) {

	$start = mt_float();
	
	$this->_prefix = $prefix;

	if ($this->ExecuteJob($queries))
		return $this->_finalize($next_step_cb);
		
	while (!$this->ExecuteJob($queries)) {
		if (time()-$start>$max_time)
			break;
	}
	return $this->_finalize($next_step_cb);
}

function _finalize($next_step_cb) {
	return $next_step_cb($this->finished, $this->progress[__SJS_INDEX], $this->progress[__SJS_TOTAL]);
}

function ExecuteJob(&$queries) {
	global $conn;
	$i=$this->progress[__SJS_INDEX];
//	echo 'Executing query #'.$i.'<br>';
//	echo $queries[$i].'<br/>';

	if (!@$conn->PrefixExecute($queries[$i], $this->_prefix)) {
		$sty = ' style="color:red"';
		$this->progress[__SJS_ERRORS]++;
	} else $sty='';
	if ($sty!=='')
		echo '<pre>'.xhtml_safe($queries[$i]).'</pre>';
	echo '<p'.$sty.'>'.$conn->ErrorMsg().'</p>';

	next($queries);
	++$i;
	$this->progress[__SJS_INDEX] = $i;
	$this->finished = ($i >= $this->progress[__SJS_TOTAL]);
	return $this->finished;
}

function CurrentJob() {
	return $this->progress[__SJS_INDEX];
}

function Errors() {
	return $this->progress[__SJS_ERRORS];
}

function Count() {
	return $this->progress[__SJS_TOTAL];
}


}

?>

<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function record_html($query,$num) {
	global $conn;
	$body = '<h3>'.$query.'</h3>';
	// try to execute the query
	$rs=@$conn->RawExecute($query);
	if ($rs===false) $body .= '<p style="color:red">';
	else $body.='<p>';
	// show the result message
	$body .= $conn->ErrorMsg();
	$body .=   '</p>';
	// if it was a boolean operation (currently only FALSE with adoDB lite) then finish
	if (is_bool($rs))
		return $body;
	// return number of affected rows
	$body .=  _DB_QUERY_AFF.': '.$conn->Affected_Rows().' - ';
	// count the resultset rows
	$row_total = $rs->RecordCount();
	// if there are no rows, exit
	if (!$row_total) return $body;
	// show some stats about the result table
	$body .=  _DB_QUERY_ROWS.': '.$row_total.' - ';
	$body .=  _DB_QUERY_FIELDS.': '.$rs->FieldCount();
	$body.= '<table  class="tbldata">';
	$body .= "<thead><tr>\n";
	// get the rows
	$rsa = $rs->GetArray();	$rs = null;
	foreach(array_keys($rsa[0]) as $col)
		$body.='<th class="result_head">'.$col."</th>\n";
	$body .= "</tr></thead>\n";
	// display the full table
	reset($rsa);
	foreach($rsa as $row) {
		$body .="<tbody><tr>\n";
		foreach($row as $val)
			$body .= '<td valign="top">'.xhtml_safe($val)."</td>\n";
		$body .= "</tbody></tr>\n";
	} $rsa = null;
	$body .= '</table>';
	return $body;
}

function database_page($query) {
	global $conn;
	$gui=new ScriptedUI();
	$gui->add("form","adminform");
	$gui->add("com_header",_DB_QUERY);
	$gui->add("tab_head");
	$gui->add("tab_simple","",_DB_QUERY);
	$gui->add("textarea_big","query",_DB_QUERY_FIELD,$query,null);
	$button_arr = array(array('name'=>_DB_QUERY_EXC, 'onclick'=>'javascript:document.adminform.submit()'),
	    		   array('name'=>_DB_QUERY_CLEAR, 'onclick'=>'javascript:document.adminform.query.value=\'\';') );
	$gui->add("buttons","","",$button_arr);
	$gui->add("tab_end");
	
	if (isset($query)) {
		$query_arr = split_sql($query);
		if (count($query_arr)) {
			$body='';
			$i=0;
			foreach ($query_arr as $query) {
				$body .= record_html($query, $i++);
			}
		} else
			$body = '<em>'._DB_NO_QUERIES.'</em>';
		$gui->add('spacer');
		$gui->add("tab_head");
		$gui->add("tab_simple","",_DB_QUERY_RESULT);
		$gui->add("html","","","<tr><td>$body</td></tr>");
		$gui->add("tab_end");
	}

	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

function manage_sql() {
global $d_root,$d_private;
$gui=new ScriptedUI();
$gui->add("form","adminform", '','admin.php?com_option=database');
$gui->add("com_header",_DB_BACKUP_HEAD);

$table_head = array ( array('title'=>'#' , 'val'=>'id' , 'len'=>'1%','align'=>'center') ,
					  array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'1%','align'=>'center') ,
					  array('title'=>_NAME,'val'=>'id','len'=>'70%','info'=>_DOWNLOAD,'ilink'=>
					  'admin2.php?com_option=database&task=download&fn=ivar1&no_comp=1','ivar1'=>'fname') ,
					  array('title'=>_DKDB_FORMAT,'val'=>'format','len'=>'15%','align'=>'center'),
					  array('title'=>_CDATE,'val'=>'created','len'=>'15%','date'=>'1','align'=>'center'),
					  array('title'=>_SIZE,'val'=>'size','len'=>'15%','align'=>'center') ,
					 );
$backup_dir=$d_root.$d_private."backup/";
$table_data=read_dir($backup_dir,"file",true, array('php', 'sql', 'gz'));
$c=count($table_data);
$sorted_data = array();
for($i=0;$i<$c;$i++) {
//	$ext = file_ext($table_data[$i]['path']);
	// fetch the table record
	$rec = $table_data[$i];
	// get the DKDB header
	$hdr = open_dkdb($rec['path']);
	if (!isset($hdr))
		$rec['format'] = _SE_ACCESS_DENIED;
	else {
		if (!isset($hdr[0]))
			$rec['format'] = _NA;
		else {
			$rec['format'] = 'v'.$hdr[1];
			if ($hdr[3] === true)
				$rec['format'] .= ' (SVN)';
		}
	}
	// get the next available timestamp-index
	$ts = $rec['created'];
	while (isset($sorted_data[$ts])) {
		$ts++;
	}
	// fix encoding
	$rec['fname'] = rawurlencode($rec['id']);
	// assign the new record
	$sorted_data[$ts] = $rec;
} $table_data = null;
krsort($sorted_data);
$gui->add("data_table_arr","maintable",$table_head,array_values($sorted_data));
$gui->add("end_form");
$gui->generate();
}

?>
<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

	echo '<tr><td colspan="2"><input type="hidden" name="boxchecked" value="0" />';
	echo '<table width="100%" id="datatable" class="tbldata" border="0" cellpadding="0" cellspacing="0">';
	echo '<colgroup>';
	foreach($desc as $row) {
		$span='';
		if($row['val']=='ordering') {
			$span="span='3'";
			$this->has_ordering = true;
		}else {
			if ($row['val'] == 'userid')
				$this->has_owner = true;
			else if ($row['val'] == 'access')
				$this->has_access = true;
			echo "<col width='".$row['len']."' $span />";
		}
	}
	echo "</colgroup>\n";
	/* output the table col headers */
//	$order_select_defined=false;
	echo "<thead><tr>";
	$f_nav_select='';
	foreach($desc as $row) {
		if (!isset($row['align']))
			$align='left';
		else
			$align =$row['align'];
		$span='';
		if ($row['val']== 'ordering') {
	 		$span=" colspan='3' ";
//			$order_select_defined=true;
		}

		echo "<th align='$align' $span >";
		switch ($row['title']) {
			case "radio":
				//L: wtf?
				echo '&nbsp;';break;
			case "checkbox":
				echo '<input type="checkbox"  name="toggleAll" title="'._IFC_CHECKBOX.'" onclick="ToggleAll(this);" />'; break ;
			default:
				echo $row['title'];
		}
		switch ($row['title']) {
			case 'radio':
			case 'checkbox':
			case '#':
			case _PUBLISHED:
			case _ORDERING:
				// just skip these values
			break;
			default:
			$this->_nav_select[$row['val']] = $row['title'];
			$f_nav_select.='<option value="'.
						$row['val'].'" '.(($this->enable_filter && isset($this->filter_var) && $this->filter_var==$row['val'])?"selected=\"selected\"":"").' >'.$row['title'].'</option>'."\n";
		}
		echo "</th>";
	}
	echo "</tr>\n</thead><tbody>";
	// build the filter navigation bar
	$f_nav='';
	if ($this->enable_filter) {
//enabled sql filter for admin only
if ($my->is_admin()) {
		$f_nav_cond="<select name=\"filter_type\" class=\"tf\">";
		$f_type_arr=array(_LIKE=>"LI","&gt;"=>"GT","&lt;"=>"LT","="=>"EQ");
		foreach($f_type_arr as $f_var=>$f_val) {
			$f_nav_cond.="<option value=\"$f_val\" ".
				((isset($this->filter_type) && $this->filter_type==$f_val)?"selected":"")." >$f_var</option>";
		}
		$f_nav_cond.="</select>";
		$f_nav.='<a name="filter"></a>
	                                <select name="filter_var" class="tf">'.$f_nav_select.'</select> '.$f_nav_cond.'
	                                <input type="text" name="filter_str" value="'.(isset($this->filter_str)?$this->filter_str:"").'" class="tf" size="10" />';
}									
									
	                       $f_nav.='<input type="button" value="'._IFC_FILTER_GO.'" onclick="lcms_st(\'filter\')" class = "tf" /><br /><a href="#filter" onclick="filter.cycleTab(\'filtertab2\');">'._IFC_FILTER_HIDE.'</a>';
						   global $d;
						   
	if ($this->has_ordering) {
		// create the ordering direction dropdown list
		$ors = array(
			array('name' => 'ASC', 'value' => 'ASC'),
			array('name' => 'DESC', 'value' => 'DESC')
		);
		$ors = select($ors, $this->filter_ord);
		$f_nav = _ORDERING.':&nbsp;'._create_select('filter_ord', $ors).'<br />'.$f_nav;
	}
	
	$f_nav = "<div id=\"filterpage1\" class=\"pagetext\" >".$f_nav."</div>
	                                <div id=\"filterpage2\" class=\"pagetext\" ><a href='#filter' onclick=\"filter.cycleTab('filtertab1');\">"._IFC_FILTER_SHOW."</a> ".(($task=="filter")?"<a href=\"javascript:void()\" onclick=\"lcms_st('disable_filter')\" >"._IFC_FILTER_DISABLE."</a>":"")."</div>
	               <input type='hidden' id='filtertab1' name='x1' value='x1' />\n
	               <input type='hidden' id='filtertab2' name='x2' value='x2' />\n
			".$d->script("filter.cycleTabC('filtertab2')",'','');

	$c_nav_filter="";

// 	if ($task=="filter") {
	$tvalues=false;
	if (strlen($this->filter_str)) {
                                foreach($value as $vrow ) {
                                        $eval_str='';
                                        $eval_res=false;
                                        switch($this->filter_type) {
                                                case "LI" :
							$eval_res = (stristr($vrow[$this->filter_var], $this->filter_str)!==false);
							break;
                                                case "GT" :
							$eval_res=($vrow[$this->filter_var]>$this->filter_str);
							break;
                                                case "LT" :
							$eval_res=($vrow[$this->filter_var]<$this->filter_str);
							break;
                                                case "EQ" :
							$eval_res=($vrow["$this->filter_var"]==$this->filter_str);
                                                }
                                                if($eval_res)
							$tvalues[]=$vrow;
				}
				$value=$tvalues; $tvalues = null;
                                $c_nav_filter="&task=filter&filter_type=".$this->filter_type."&filter_var=".$this->filter_var."&filter_str=".$this->filter_str;
				}
//			}
	} else $c_nav_filter = '';	// if filter is disabled
	// build the pages
	$c_nav='';
	//RFC
	if (!is_array($value)) {
		_finish_ui(0, 1, $f_nav, $c_nav);
		return;
	}
	$row_total=count($value);
	$page_url=$this->form_url;
	if (!isset($this->table_page))
		$this->table_page=1;
	global $d_show_count;
	$num_pages=sprintf("%d",$row_total/$d_show_count);
	if (($row_total % $d_show_count) > 0)
		$num_pages+=1;
		
	// finished modifying $table_page
	if (strlen($this->form_url))
		echo '<tr><td><input type="hidden" value="'.$this->table_page.'" name="table_page" id="table_page" /></td></tr>';
	
	$value = array_slice($value,(($this->table_page-1)*$d_show_count),$d_show_count);
	$first=_NAV_FIRST.' |';
	$previous=_NAV_PREV.' |';
	$last='| '._NAV_LAST;
	$next='| '._NAV_NEXT;

	$row_num=1+($this->table_page-1)*$d_show_count;
                if($this->table_page>1)$first="<a href='$page_url$c_nav_filter'>"._NAV_FIRST."</a> | ";
                if($this->table_page>1) {
			if ($this->table_page>2)
				$s = "&amp;table_page=".($this->table_page-1);
			else $s = '';
			$previous="<a href='$page_url".$s."$c_nav_filter' >"._NAV_PREV."</a> | ";
		}
                if($this->table_page<$num_pages)$next=" | <a href='$page_url&amp;table_page=".($this->table_page+1)."$c_nav_filter' >"._NAV_NEXT."</a>";
                if($this->table_page<$num_pages)$last=" | <a href='$page_url&amp;table_page=".$num_pages."$c_nav_filter'>"._NAV_LAST."</a>";
                $page_links='';

                for($pn=1 ; $pn<=$num_pages ; $pn ++) {
                        if($pn == $this->table_page)$page_links.=" $pn ";
                        else {
				if ($pn>1)
					$s = '&amp;table_page='.$pn;
				else $s = '';
				$page_links.=" <a href='$page_url".$s."$c_nav_filter'>$pn</a> ";
			}
		}
                $c_nav = "<div class=\"contentnav\"> $first $previous $page_links $next $last  </div>";

		/* finally output the values */
		foreach ($value as $vrow ) {
			$row_color = ($row_num%2) ? 'wbg' : 'gbg';
			echo "\n<tr id=\"ui_row".$row_num.'" class="'.$row_color.'">';
			foreach($desc as $col_index => $row) {
                                if(!isset($row['align']) )$align='left';
                                else $align =$row['align'];
                                echo "<td align='$align'>";

                                switch ($row['title']) {
					case "radio" :
						echo '<input id="rb'.$row_num.'" type="radio" name="cid[]" value="'.$vrow[$row['val']].'" onclick="isChecked(this.checked);" ';
						if (isset($vrow['disabled'])) {
							if ($vrow['disabled'])
								echo ' disabled="disabled"';
						}
						echo '/>'; break;
					case "checkbox" :
						echo '<input id="cb'.$row_num.'" name="cid[]" value="'.$vrow[$row['val']].'" type="checkbox" onclick="isChecked(this.checked);Toggle(this);" />'; 
					break ;
					case _PUBLISHED :
						global $d_subpath;
						$_IMG_M = array('publish_x', 'tick', 'publish_r', '', 'categories');
						$_ALT_M = array(_UNPUBLISHED, _SUBMITTED, _SUBMITTED, '', _AMENU_CONTENT_ARCHIVE);
						$iv = (int)$vrow[$row['val']];
						global $d_atemplate;
						echo '<img src="'.$d_subpath.'admin/templates/'.$d_atemplate.
							'/images/'.$_IMG_M[$iv].'.png" border="0" alt="'.$_ALT_M[$iv].'" />';
				break ;
			case _ORDERING :
				$up='&nbsp;';$down='&nbsp;';
//				$orderup=($this->order==1)?"orderup":"orderdown";
//				$orderdown=($this->order==1)?"orderdown":"orderup";
				global $d_subpath;
				if($row_num!=1)
					$up='<a href="#reorder" onclick="return listItemTask(\'cb'.$row_num.'\',\'orderup\')"><img src="'.$d_subpath.'admin/templates/default/images/arrowup.png" border="0" alt="Move up"/></a>';
				if($row_num!=$row_total)$down='<a href="#reorder" onclick="return listItemTask(\'cb'.$row_num.'\',\'orderdown\')"><img src="'.$d_subpath.'admin/templates/default/images/arrowdown.png" border="0" alt="Move down"/></a>';
				echo "$up</td><td>$down</td><td>".$this->get_order_field($vrow['id'],$row_num,$vrow[$row['val']]);
				break ;
			case _ACCESS :
			case _EDITGROUP:
				echo access_bygid($vrow[$row['val']]);
			break;
			case _FRONTPAGE :
				$fp_arr = array(_NO,_YES);
				echo $fp_arr[ $vrow[$row['val']] ];
			break;
			case "#" :
				echo $row_num;
				break;
			default:
				$bval='';
//						echo '<label for="rb'.$row_num.'">;
				if(isset($row['bval']))
					$bval = " (".$vrow[$row['bval']].")";
				$links='';$linke='';
				// craft the proper i-link
				if(isset($row['ilink']) ) {
					$explore = isset($row['explore'])?$row['explore']:'';
					$ilink=$row['ilink'];
					for($m=1;$m<10;$m++) {
						$i_var='ivar'.$m;
						if (isset($row[$i_var]) ) {
							$ilink=str_replace($i_var,$vrow[$row[$i_var]],$ilink);
							$explore=str_replace($i_var,$vrow[$row[$i_var]],$explore);
						}
						else break;
					}
					// add the table_page, if any
					if ($this->table_page!=1)
						// will be HTML-encoded later
						$ilink.='&table_page='.$this->table_page;
					// create the hyperlink
					$links='<a id="a'.$col_index.'_'.$row_num.'" href="'.xhtml_safe($ilink).'">'; $linke='</a>';
				} else $explore = '';
				//hlink is for hyperlinks in templates info (authorUrl...)
				if(isset($row['hlink']) && isset($vrow[$row['hlink']])) {
					$links='<a href="'.
						xhtml_safe($vrow[$row['hlink']]).'" target="_blank">'; $linke='</a>';
				}
				//RFC: what is blink for?
				if(isset($row['blink']) && isset($vrow[$row['blink']])) {
					$links='<a href="'.
						xhtml_safe($vrow[$row['blink']]).'" target="_blank">'; $linke='</a>';
				}
				//mlink = mail link
				if(isset($row['mlink']) && isset($vrow[$row['mlink']])) {
					$links='<a href="mailto:'.$vrow[$row['mlink']].'">'; $linke='</a>';
				}
				if (isset($row['explore']))
					$linke .= '&nbsp;&nbsp;<a href="'.xhtml_safe($explore).'">'.
						'<small>['._BROWSE.']</small>'.'</a>';
				echo '<label for="cb'.$row_num.'">';
				if(isset($row['info'])) {
					echo $vrow[$row['val']].'</label> '.$bval.$links."[".$row['info']."]".$linke;
				} else {
					//L: the below should be fixed
					if(isset($row['date'])){
						if ($vrow[$row['val']])
							echo lc_strftime(_DATE_FORMAT_EXTENDED, $vrow[$row['val']]);
						else
							echo _NA;
					} else
						echo $links.$vrow[$row['val']].$bval.$linke.'&nbsp;';
					echo '</label> ';
				}
		}
		echo "</td>";
	}
	echo "</tr>\n";
	++$row_num;
}
_finish_ui($row_num-1, count($desc), $f_nav, $c_nav);

echo '</td></tr>';

?>
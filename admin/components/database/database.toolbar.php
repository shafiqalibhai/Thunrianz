<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$d__help_context = 'Administration/System/Database_manager';

switch ($option) {
	case 'query':
		$d__help_context = 'Administration/System/Database_query';
		$toolbar->add('back');
		break;
	default:
	case 'manage_sql':
		switch($task) {
			case "upload_gui":
				$toolbar->add("upload");
				$toolbar->add("cancel");
			break;

			case 'restore_sql':
			case 'rebase':
				$toolbar->add_custom(_TB_BACK, '', 'location.href=\'admin.php?com_option=database&option=manage_sql\'');
			break;
			default:
				if ($option=='query')
					break;				
				if (shift_timeout(250)<30)
					$warning = _WARNING_NOT_ENOUGH_TIME."\n\n";
				else
					$warning = '';
				
				if (!is__writable($d_root.$d_private.'backup/'))
					$warning2 = sprintf(_WARNING_CREATE_N_DOWNLOAD, '::/'.$d_private.'backup/')."\n\n";
				else $warning2 = '';

				$toolbar->add_custom(_DB_REBASE,'rebase',"if (confirm('".
									js_enc($warning._DB_REBASE_CONFIRM).
									"')) ui_lcms_st('rebase');");
				$toolbar->add_split();
				$toolbar->add_custom(_TB_CREATE,'backup_sql',"if (confirm('".
									js_enc($warning.$warning2._BACKUP_CONFIRM).
									"')) {if (confirm('".js_enc(_BACKUP_ASK_COMPRESS)."'))
										ui_lcms_st('backup_sql_gz');
									else ui_lcms_st('backup_sql'); }");
				$toolbar->add_alt(_TB_CREATE_COMPRESSED, 'backup_sql_gz');
				$toolbar->add_custom(_DATABASE_RESTORE,"restore_sql",
				"if (confirm('".js_enc($warning._RESTORE_CONFIRM)."')) lcms_st('restore_sql')");
				$toolbar->add('upload_gui');
				$toolbar->add("delete");
			break;
		}
	break;
}

?>
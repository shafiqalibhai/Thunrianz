<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Events system classes
# @author legolas558
#
# allows drabots triggering and parameters class

class param_class {
	var $params = array();

	function param_class($txt = '') {
		if (strlen($txt))
			$this->params = unserialize($txt);
	}

	function get($var,$default=NULL) {
		if(!isset($this->params[$var])) return $default;
		else return $this->params[$var];
	}

	//RFC
	function def($var,$default=NULL) {
		if (!isset($this->params[$var])){
			$this->params[$var]=$default;
			return false;
		}
		return true;
	}
	
	function save() {
		return serialize($this->params);
	}

}

class DrabotHandler {
	var $_events;
//	var $_lists=null;
	var $_bots=array();
	//TODO: what is this for?
//	var $_loading=null;
	var $_enabled;

	function DrabotHandler($enabled = true) {
		$this->_events = array();
		$this->_enabled = $enabled;
	}
	
	function _fetch_drabots($group) {
		global $conn, $access_sql;
		return $conn->SelectArray('#__drabots', 'id,type,element,showon,access,ordering,params', " WHERE type='$group' $access_sql ORDER BY ordering");
	}
	
	## load core/mail bot groups, without internationalization files and without sectionid/showon checks
	function loadCoreBotGroup( $group) {
		if (!$this->_enabled)
			return 0;
		if (isset($this->_bots[$group]))
			return count($this->_bots[$group]);
		global $d_root;
		$_DRABOTS =& $this;
		$this->_bots[$group] = array();
		foreach($this->_fetch_drabots($group) as $bot) {
			$this->_bots[$group][$bot['element']] = $bot;
			$bot_path = $d_root.'drabots/'.$bot['element'].'.php';
			require_once $bot_path;
		}
		return count($this->_bots[$group]);
	}

	// returns the number of drabots available for specified group
	// $sectionid behaviour should be fixed
	function loadBotGroup( $group , $sectionid='') {
		if (!$this->_enabled)
			return 0;
		global $conn,$d_root,$access_sql,$my;
		// shortcut available only for core drabots
		// add a pseudo-global instance in the context of soon being included drabots
		$_DRABOTS =& $this;
		$rsa = $this->_fetch_drabots($group);
		// reset the array when using $sectionid
		//WARNING: and what if sectionid is sometimes specified and sometimes not specified?
		$this->_bots[$group] = array();
		foreach($rsa as $bot) {
			if ( ($bot['showon']===''
				|| strstr($bot['showon'],'_'.$sectionid.'_')
				|| strstr($bot['showon'],'_0_'))
				) {
				$this->_bots[$group][$bot['element']] = $bot;
				$bot_path = $d_root.'drabots/'.$bot['element'].'.php';

				$path = bot_lang($my->lang, $bot['element']);
				if (file_exists($path)) {
					include_once $path;
				}
				//TODO: drabots could invalidate cache
				require_once $bot_path;
			}
		}
		return count($this->_bots[$group]);
	}
	function registerFunction( $event, $func ) {
		$this->_events[$event][] = $func;
	}
	function trigger_ob( $event, $args = array(), $max = null) {
		ob_start();
		$r = $this->trigger($event, $args, $max);
		return array(ob_get_clean(), $r);
	}
	function trigger( $event, $args=array(), $max = null) {
		if ($max == -1)
			$result = null;
		else
			$result = array();
		// failsafe return exitpoint
		if (!$this->_enabled)
			return $result;

		// execute all the hooks and return the results in an array
		if ( isset($this->_events[$event]) ) {
			if (!isset($max)) {
				foreach ($this->_events[$event] as $func) {
					$result[] = call_user_func_array($func, $args);
				}
			} else {
				// execute till a non-null result is found and return it
				if ($max == -1) {
//					$result = null;
					foreach ($this->_events[$event] as $func) {
						$result = call_user_func_array($func, $args);
						if (isset($result))
							break;
					}
				} else {
					$i = 0;
					foreach ($this->_events[$event] as $func) {
						$result[] = call_user_func_array($func, $args);
						if (++$i==$max)
							break;
					}
				}
			}
		}
		return $result;
	}
	
	// NOT YET USED
	## retrieve the full record of the selected drabot
	function GetBot($group, $bot) {
		return $this->_bots[$group][$bot];
	}
	
	function GetBotParameters($group, $bot) {
		if (!$this->_enabled)
			return new param_class();
		return new param_class( $this->_bots[$group][$bot]['params'] );
	}
	
	function AddCSS($bot) {
		global $d, $d_template;
		$d->add_css_once('drabots/css/'.$bot.'.style.css', true);
		$d->add_css_once('templates/'.$d_template.'/drabots/'.$bot.'.style.css', true);
	}
}

?>

<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Simple statistics class
# @author legolas558
#
# this class provides the simple statistics mechanism
# rewritten by legolas558
# the first row holds total visits data
# the rows with id>1 are inherent to today's visits
# today's visits are normalized every 24 hours

class  Stats {

	var $total_visits;
	var $today_visits;
	var $total_online;
	var $guests;
	var $members;
	var $resting_time;

	function Stats() {
		global $conn,$time,$my;
		
		$this->resting_time = $time - 60 * 10;
		
		// fetch the total visits
		$row = $conn->SelectRow('#__simple_stats', 'date,count',  " WHERE id=1");
		$this->total_visits = $row['count'];
		// if this is a new day then normalize all previous records
		if ( $this->day($row['date']) != $this->day($time) ) {
			$this->FetchTodayVisits();
			$this->total_visits += $this->today_visits;
			$this->today_visits = 0;
			$conn->Delete('#__simple_stats', ' WHERE id>1');
			$conn->Update('#__simple_stats', "date=$time,count=".$this->total_visits,
						" WHERE id=1");
		} else {
			$this->FetchTodayVisits();
		}
		
		if (!$my->is_admin()) {		// if the user is admin then we do not count his hits
			// get the unique record for this IP
			$row = $conn->SelectRow('#__simple_stats', 'id,date', " WHERE id>1 AND ip='".$my->GetIP()."'");

			if (!count($row)) {	// there are no visits from this IP
				if (!$my->gid)
					$uid = 0;
				else
					$uid = $my->id;
				// create a new record for this user
				$conn->Execute( "INSERT INTO #__simple_stats (ip,date,count,uid) VALUES ('".
														$my->GetIP()."', $time, 1, $uid)" );
				++$this->today_visits;
			} else {			// the IP is already recorded
				if($row['date'] < $this->resting_time )	// update the time every 10 minutes
					$conn->Execute("UPDATE #__simple_stats SET date=$time WHERE id=".$row['id']);
			}
		}
	}
	
	function FetchTodayVisits() {
		global $conn;
		// get the non-normalized records
		$rs=$conn->Execute("SELECT id FROM #__simple_stats WHERE id>1");
		$this->today_visits = $rs->RecordCount();
	}
	
	function TodayVisits() {
		if (!isset($this->today_visits))
			$this->FetchTodayVisits();
		return $this->today_visits;
		
	}
	
	function TotalVisits() {
		return $this->total_visits;
	}
	
	function TotalOnline() {
		global $conn, $time;
		if (!isset($this->total_online)) {
			$rs=$conn->Execute("SELECT id FROM #__simple_stats WHERE id>1 and date>=".$this->resting_time);
			$this->total_online = $rs->RecordCount();
		}
		return $this->total_online;
	}
	
	function day($ts) {
		return (int)($ts/86400);
	}

	// this is an example of collaborative code (one function collaborates with another)
	function GuestsOnline() {
		global $time;
		if (isset($this->guests))
			return $this->guests;
		else if (isset($this->members))
			return $this->TotalOnline()-$this->members;
		global $conn;
		// always subtract the first record, the total accumulator
		$this->guests = $conn->Count('SELECT COUNT(*) FROM #__simple_stats WHERE id>1 and uid=0 and date>='.$this->resting_time);
		return $this->guests;
	}
	
	// administrator users are always invisible
	function MembersOnline() {
		global $time;
		if (isset($this->members))
			return $this->members;
		else if (isset($this->guests))
			return $this->TotalOnline()-$this->guests;
		global $conn;
		$rs = $conn->Execute('SELECT id FROM #__simple_stats WHERE id>1 and uid>0 and date>='.$this->resting_time);
		$this->members = $rs->RecordCount();
		return $this->members;
	}
}

?>
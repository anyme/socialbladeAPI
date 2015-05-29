<?php

require_once "phpQuery/phpQuery.php";

class SocialBladeApi {
	protected $data;
	public function getData($url) {
		$this->data = array();
		$this->loadUrl($url);
		$size = pq(".TableMonthlyStats:nth-child(4n+3)")->size();

		if(pq(".TableMonthlyStats")->length == 0) {
			return false;
		}

		//get subs
		for($i = 0; $i < $size; ++$i) {
			$this->data[$i]["subs_var"] = intval(str_replace(",","",pq(".TableMonthlyStats:nth-child(4n+3):eq(" . $i . ") div:first")->text()));
			$this->data[$i]["subs"]     = intval(str_replace(",","",pq(".TableMonthlyStats:nth-child(4n+3):eq(" . $i . ") div:last")->text()));
		}

		// get date
		for($i = 0; $i < $size; ++$i) {
			$date = new DateTime(pq(".TableMonthlyStats:nth-child(4n+2):eq(" . $i . ") div:first")->text());
		   	$this->data[$i]["date"] = $date->format('Y-m-d');
		}

		// get subs
		for($i = 0; $i < $size; ++$i) {
			$this->data[$i]["views_var"] = doubleval(str_replace(',', '', pq(".TableMonthlyStats:nth-child(4n):eq(" . $i . ") div:first")->text()));
		   	$this->data[$i]["views"]     = doubleval(str_replace(',', '', pq(".TableMonthlyStats:nth-child(4n):eq(" . $i . ") div:last")->text()));
		}
	}
	public function getMonthlySummary($username) {
		if ($username == "") {
			return false;
		}
		$this->getData("http://socialblade.com/youtube/user/" . urlencode($username) . "/monthly");

		if (!isset($this->data)) {
			return false;
		}

		$summary = array('views' => 0, 'views_var' => 0, 'subs' => 0, 'subs_var' => 0, 'date' => 0);

		$last = sizeof($this->data) - 1;
		echo "$last\n";

		if ($last < 0) { return false; }
		
		if ($last == 1 && $this->data[$last]['views'] == 0) {
			echo "[SOCIALBLADE] not up to date\n";
			--$last;
		}

		if ($last == 0) {
			$summary['views_var'] = $this->data[$last]['views_var'];
			$summary['subs_var']  = $this->data[$last]['subs_var'];
		} else {
			$summary['views_var'] = $this->data[$last]['views'] - $this->data[0]['views'];
			$summary['subs_var']  = $this->data[$last]['subs'] - $this->data[0]['subs'];
		}
		$summary['views'] = $this->data[$last]['views'];
		$summary['subs']  = $this->data[$last]['subs'];
		$summary['date']  = $this->data[$last]['date'];
		
		return $summary;

	}

	public function get30DaysData($username) {
		if ($username == "") {
			return false;
		}
		return $this->getData("http://socialblade.com/youtube/user/" . urlencode($username) . "/monthly");
	}

	public function getWeeklySummary($username) {
		if ($username == "") {
			return false;
		}

		$this->getData("http://socialblade.com/youtube/user/" . urlencode($username) . "/monthly");

		if (!isset($this->data)) {
			return false;
		}

		$summary = array('views' => 0, 'views_var' => 0, 'subs' => 0, 'subs_var' => 0, 'date' => 0);

		$last = sizeof($this->data) - 1;

		if ($last < 0) { return false; }

		if ($last == 1 && $this->data[$last]['views'] == 0) {
			echo "[SOCIALBLADE] not up to date\n";
			--$last;
		}

		if ($last == 0) {
			$summary['views_var'] = $this->data[$last]['views_var'];
			$summary['subs_var']  = $this->data[$last]['subs_var'];
		} else if ($last > 5) {
			$summary['views_var'] = $this->data[$last]['views'] - $this->data[$last - 6]['views'];
			$summary['subs_var']  = $this->data[$last]['subs'] - $this->data[$last - 6]['subs'];
		} 

		$summary['views'] = $this->data[$last]['views'];
		$summary['subs']  = $this->data[$last]['subs'];
		$summary['date']  = $this->data[$last]['date'];
		
		return $summary;
	}

	protected function loadUrl($url) {
        $file = file_get_contents($url);
        phpQuery::newDocumentHTML($file);
    }

}
?>

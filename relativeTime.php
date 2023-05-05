<?php

// https://gist.github.com/benplum/5233006

define("SECOND", 1);
define("MINUTE", 60 * SECOND);
define("HOUR", 60 * MINUTE);
define("DAY", 24 * HOUR);
define("MONTH", 30 * DAY);

function relativeTime($time) {
	if (!is_numeric($time)) {
		$time = strtotime($time);
	}
	if ($time > time()) {
		return false;
	}

	$delta = strtotime(date('r')) - $time;

	if ($delta < 2 * MINUTE) {
		return "1 min";
	} else if ($delta < 45 * MINUTE) {
		return floor($delta / MINUTE) . " mins";
	} else if ($delta < 90 * MINUTE) {
		return "1 hour ago";
	} else if ($delta < 24 * HOUR) {
		return floor($delta / HOUR) . " hours";
	} else if ($delta < 48 * HOUR) {
		return "yesterday";
	} else if ($delta < 30 * DAY) {
		return floor($delta / DAY) . " days";
	} else if ($delta < 12 * MONTH) {
		$months = floor($delta / DAY / 30);
		return $months <= 1 ? "1 month" : $months . " months";
	} else {
		$years = floor($delta / DAY / 365);
		return $years <= 1 ? "1 year" : $years . " years";
	}
}


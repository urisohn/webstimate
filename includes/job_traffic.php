<?php

define('JOB_TRAFFIC_LOG', '/home/urisoh5/uploaded_data/webstimate.org/job_traffic.log');
define('JOB_TRAFFIC_MAX_MINUTE', 3);
define('JOB_TRAFFIC_MAX_HOUR', 60);

function job_traffic_message() {
	return 'We are experiencing above normal traffic, please come back later.';
}

function job_traffic_read_timestamps() {
	if (!file_exists(JOB_TRAFFIC_LOG)) {
		return array();
	}
	$lines = file(JOB_TRAFFIC_LOG, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	if ($lines === false) {
		return array();
	}
	$now = time();
	$timestamps = array();
	foreach ($lines as $line) {
		$ts = (int) trim($line);
		if ($ts > $now - 3600) {
			$timestamps[] = $ts;
		}
	}
	return $timestamps;
}

function job_traffic_counts($timestamps = null) {
	if ($timestamps === null) {
		$timestamps = job_traffic_read_timestamps();
	}
	$now = time();
	$minute_count = 0;
	$hour_count = count($timestamps);
	foreach ($timestamps as $ts) {
		if ($ts > $now - 60) {
			$minute_count++;
		}
	}
	return array(
		'minute' => $minute_count,
		'hour' => $hour_count,
	);
}

function job_traffic_is_busy() {
	$counts = job_traffic_counts();
	return $counts['minute'] >= JOB_TRAFFIC_MAX_MINUTE
		|| $counts['hour'] >= JOB_TRAFFIC_MAX_HOUR;
}

function job_traffic_check_or_die($back_url = 'index.php') {
	if (!job_traffic_is_busy()) {
		return;
	}
	$msg = htmlspecialchars(job_traffic_message(), ENT_QUOTES, 'UTF-8');
	$back = htmlspecialchars($back_url, ENT_QUOTES, 'UTF-8');
	die(
		"<div class='container' style='margin-top:40px;font-family:sans-serif'>".
		"<div class='alert alert-warning' style='padding:15px;background:#fcf8e3;border:1px solid #faebcc;color:#8a6d3b'>$msg</div>".
		"<a href='$back'>Back</a>".
		"</div>"
	);
}

function job_traffic_record() {
	$now = time();
	$dir = dirname(JOB_TRAFFIC_LOG);
	if (!is_dir($dir)) {
		if (!mkdir($dir, 0755, true)) {
			return;
		}
	}
	$fp = fopen(JOB_TRAFFIC_LOG, 'c+');
	if ($fp === false) {
		return;
	}
	if (!flock($fp, LOCK_EX)) {
		fclose($fp);
		return;
	}
	$contents = stream_get_contents($fp);
	$timestamps = array();
	if ($contents !== false && $contents !== '') {
		foreach (explode("\n", $contents) as $line) {
			$ts = (int) trim($line);
			if ($ts > $now - 3600) {
				$timestamps[] = $ts;
			}
		}
	}
	$timestamps[] = $now;
	ftruncate($fp, 0);
	rewind($fp);
	fwrite($fp, implode("\n", $timestamps) . "\n");
	flock($fp, LOCK_UN);
	fclose($fp);
}

function job_traffic_check_and_record_or_die($back_url = 'index.php') {
	job_traffic_check_or_die($back_url);
	job_traffic_record();
}

<?php

function interprobe_origname_path($dir_data, $file) {
	return $dir_data . $file . '.origname';
}

function interprobe_legacy_origname_path($dir_data, $time) {
	return $dir_data . $time . '.origname';
}

function interprobe_looks_like_temp_filename($name) {
	return (bool) preg_match('/^\d+\.[A-Za-z0-9]+$/', $name);
}

function interprobe_save_original_filename($dir_data, $file, $time, $user_file) {
	$user_file = basename($user_file);
	file_put_contents(interprobe_origname_path($dir_data, $file), $user_file);
	file_put_contents(interprobe_legacy_origname_path($dir_data, $time), $user_file);
}

function interprobe_read_origname_file($path) {
	if (!file_exists($path)) {
		return '';
	}
	return trim(file_get_contents($path));
}

function interprobe_resolve_original_filename($dir_data, $file, $time, $post_original = null, $session_original = null) {
	$candidates = array(
		interprobe_origname_path($dir_data, $file),
		interprobe_legacy_origname_path($dir_data, $time),
	);
	foreach ($candidates as $path) {
		$stored = interprobe_read_origname_file($path);
		if ($stored !== '') {
			return basename($stored);
		}
	}

	if ($post_original !== null && $post_original !== '') {
		$post_original = basename($post_original);
		if (!interprobe_looks_like_temp_filename($post_original)) {
			return $post_original;
		}
	}
	if ($session_original !== null && $session_original !== '') {
		$session_original = basename($session_original);
		if (!interprobe_looks_like_temp_filename($session_original)) {
			return $session_original;
		}
	}

	$extension = pathinfo($file, PATHINFO_EXTENSION);
	if ($extension !== '') {
		return 'your_data_file.' . $extension;
	}
	return 'your_data_file';
}

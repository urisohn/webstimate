<?php

function interprobe_origname_paths($dir_data, $dir, $file, $time) {
	return array(
		$dir_data . $file . '.origname',
		$dir_data . $time . '.origname',
		$dir . $file . '.origname',
		$dir . $time . '.origname',
		$dir . $time . '.upload_name',
	);
}

function interprobe_looks_like_temp_filename($name) {
	return (bool) preg_match('/^\d+\.[A-Za-z0-9]+$/', $name);
}

function interprobe_pick_original_filename($name) {
	if ($name === null || $name === '') {
		return '';
	}
	$name = basename($name);
	if ($name === '' || interprobe_looks_like_temp_filename($name)) {
		return '';
	}
	return $name;
}

function interprobe_save_original_filename($dir_data, $dir, $file, $time, $user_file) {
	$user_file = basename($user_file);
	foreach (interprobe_origname_paths($dir_data, $dir, $file, $time) as $path) {
		file_put_contents($path, $user_file);
	}
}

function interprobe_read_origname_file($path) {
	if (!file_exists($path)) {
		return '';
	}
	return trim(file_get_contents($path));
}

function interprobe_store_original_filename_in_session($name) {
	if ($name === '') {
		return;
	}
	$_SESSION['original_file'] = $name;
	$_SESSION['user_upload_filename'] = $name;
}

function interprobe_get_original_filename($dir_data, $dir, $file, $time, $post_original = null) {
	$sources = array(
		$post_original,
		isset($_SESSION['original_file']) ? $_SESSION['original_file'] : null,
		isset($_SESSION['user_upload_filename']) ? $_SESSION['user_upload_filename'] : null,
	);
	foreach ($sources as $source) {
		$name = interprobe_pick_original_filename($source);
		if ($name !== '') {
			interprobe_store_original_filename_in_session($name);
			return $name;
		}
	}

	foreach (interprobe_origname_paths($dir_data, $dir, $file, $time) as $path) {
		$name = interprobe_pick_original_filename(interprobe_read_origname_file($path));
		if ($name !== '') {
			interprobe_store_original_filename_in_session($name);
			return $name;
		}
	}

	if (!empty($_SESSION['original_file'])) {
		return basename($_SESSION['original_file']);
	}
	if (!empty($_SESSION['user_upload_filename'])) {
		return basename($_SESSION['user_upload_filename']);
	}

	return interprobe_pick_original_filename($file);
}

function interprobe_saved_upload_filename($dir_data, $dir, $file, $time) {
	if (!empty($_SESSION['user_upload_filename'])) {
		return basename($_SESSION['user_upload_filename']);
	}
	if (!empty($_POST['original_file'])) {
		$name = basename($_POST['original_file']);
		if ($name !== '' && !interprobe_looks_like_temp_filename($name)) {
			return $name;
		}
	}
	if (!empty($_SESSION['original_file'])) {
		$name = basename($_SESSION['original_file']);
		if ($name !== '' && !interprobe_looks_like_temp_filename($name)) {
			return $name;
		}
	}
	foreach (interprobe_origname_paths($dir_data, $dir, $file, $time) as $path) {
		$stored = interprobe_read_origname_file($path);
		if ($stored !== '') {
			return basename($stored);
		}
	}
	return '';
}

function interprobe_inject_import_filename($r_code, $filename) {
	if ($filename === '') {
		return $r_code;
	}
	$quoted = '"' . str_replace(array('\\', '"'), array('\\\\', '\\"'), $filename) . '"';
	return preg_replace(
		'/import\(\s*["\']\s*["\']\s*\)/',
		'import(' . $quoted . ')',
		$r_code,
		1
	);
}

// Backwards-compatible alias used by older calls.
function interprobe_resolve_original_filename($dir_data, $file, $time, $post_original = null, $session_original = null) {
	$dir = isset($_SESSION['dir']) ? $_SESSION['dir'] : '';
	if ($session_original !== null && $session_original !== '' && empty($_SESSION['original_file'])) {
		interprobe_store_original_filename_in_session(basename($session_original));
	}
	return interprobe_get_original_filename($dir_data, $dir, $file, $time, $post_original);
}

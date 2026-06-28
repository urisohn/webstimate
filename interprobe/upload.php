<?php
session_start();
require_once __DIR__ . '/../includes/turnstile.php';
verify_turnstile_or_die('index.php');
require_once __DIR__ . '/../includes/job_traffic.php';
job_traffic_check_or_die('index.php');
require_once __DIR__ . '/../includes/upload_limits.php';
require_once __DIR__ . '/../includes/interprobe_upload.php';

$upload_errors = array(
	UPLOAD_ERR_INI_SIZE   => "file exceeds upload_max_filesize in php.ini",
	UPLOAD_ERR_FORM_SIZE  => "file exceeds MAX_FILE_SIZE in the form",
	UPLOAD_ERR_PARTIAL    => "file was only partially uploaded",
	UPLOAD_ERR_NO_FILE    => "no file was uploaded",
	UPLOAD_ERR_NO_TMP_DIR => "missing PHP temp folder",
	UPLOAD_ERR_CANT_WRITE => "failed to write file to disk",
	UPLOAD_ERR_EXTENSION  => "upload blocked by a PHP extension"
);

if (!isset($_FILES["fileToUpload"])) {
	die("No file was submitted. Please go back and choose a file.");
}

$upload_error = $_FILES["fileToUpload"]["error"];
if ($upload_error !== UPLOAD_ERR_OK) {
	$detail = isset($upload_errors[$upload_error]) ? $upload_errors[$upload_error] : "unknown error";
	die("Upload failed: $detail (code $upload_error).");
}

$user_file = basename($_FILES["fileToUpload"]["name"]);
$file_type = pathinfo($user_file, PATHINFO_EXTENSION);
if ($file_type === "") {
	die("Sorry, the uploaded file has no extension. Please use .csv, .xlsx, .sav, etc.");
}

$file_size = $_FILES["fileToUpload"]["size"];
if (upload_too_large($file_size)) {
	die(upload_too_large_message_html('index.php'));
}

$time=time();
$dir_data = '/home/urisoh5/uploaded_data/webstimate.org/interprobe/temp/';
$dir =      '/home/urisoh5/public_html/webstimate.org/interprobe/temp/';
$file = $time.".".$file_type;
$target = $dir_data.$file;

if (!is_dir($dir_data)) {
	if (!mkdir($dir_data, 0755, true)) {
		die("Sorry, the upload folder does not exist and could not be created: $dir_data");
	}
}
if (!is_dir($dir)) {
	if (!mkdir($dir, 0755, true)) {
		die("Sorry, the output folder does not exist and could not be created: $dir");
	}
}

if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target)) {
	$_SESSION['dir_data'] = $dir_data;
	$_SESSION['dir'] = $dir;
	$_SESSION['file'] = $file;
	$_SESSION['original_file'] = $user_file;
	$_SESSION['user_upload_filename'] = $user_file;
	$_SESSION['time'] = $time;
	$_SESSION['extension'] = $file_type;
	interprobe_save_original_filename($dir_data, $dir, $file, $time, $user_file);
	echo ('<meta http-equiv="refresh" content="0; url=configure.php">');
} else {
	die("Sorry, there was an error saving your file to: $target");
}
?>

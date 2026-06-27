<?php

define('UPLOAD_MAX_BYTES', 15 * 1024 * 1024);
define('UPLOAD_MAX_LABEL', '15 mb');
define('UPLOAD_MAX_MESSAGE', 'This free online app allows datasets up to 15 mb.');

function upload_too_large($file_size) {
	return $file_size > UPLOAD_MAX_BYTES;
}

function upload_too_large_message_html($back_url = 'index.php') {
	$msg = htmlspecialchars(UPLOAD_MAX_MESSAGE, ENT_QUOTES, 'UTF-8');
	$back = htmlspecialchars($back_url, ENT_QUOTES, 'UTF-8');
	return $msg . ' <a href="' . $back . '">Back</a>';
}

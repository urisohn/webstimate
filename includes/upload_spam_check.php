<?php

function verify_upload_spam_or_die($back_url = 'index.php') {
	$honeypot = isset($_POST['website_url']) ? trim($_POST['website_url']) : '';
	if ($honeypot !== '') {
		die(
			'Upload rejected. <a href="' . htmlspecialchars($back_url, ENT_QUOTES, 'UTF-8') . '">Back</a>'
		);
	}
}

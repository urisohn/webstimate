<?php

function turnstile_load_config() {
	static $loaded = false;
	if ($loaded) {
		return;
	}
	$config = __DIR__ . '/turnstile_config.php';
	if (!file_exists($config)) {
		die(
			'Turnstile is not configured. Copy includes/turnstile_config.php.example to ' .
			'includes/turnstile_config.php on the server and add your Cloudflare keys.'
		);
	}
	require $config;
	if (!defined('TURNSTILE_SITE_KEY') || !defined('TURNSTILE_SECRET_KEY') ||
		TURNSTILE_SITE_KEY === '' || TURNSTILE_SECRET_KEY === '' ||
		TURNSTILE_SITE_KEY === 'YOUR_SITE_KEY_HERE' ||
		TURNSTILE_SECRET_KEY === 'YOUR_SECRET_KEY_HERE') {
		die('Turnstile keys are missing or still set to placeholders in includes/turnstile_config.php.');
	}
	$loaded = true;
}

function turnstile_site_key() {
	turnstile_load_config();
	return TURNSTILE_SITE_KEY;
}

function verify_turnstile_or_die($back_url = 'index.php') {
	turnstile_load_config();

	$token = isset($_POST['cf-turnstile-response']) ? trim($_POST['cf-turnstile-response']) : '';
	if ($token === '') {
		die(
			'Verification required. Please go back and complete the security check. ' .
			'<a href="' . htmlspecialchars($back_url, ENT_QUOTES, 'UTF-8') . '">Back</a>'
		);
	}

	$post_fields = array(
		'secret' => TURNSTILE_SECRET_KEY,
		'response' => $token,
	);
	if (!empty($_SERVER['REMOTE_ADDR'])) {
		$post_fields['remoteip'] = $_SERVER['REMOTE_ADDR'];
	}

	$response = false;
	if (function_exists('curl_init')) {
		$ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$response = curl_exec($ch);
		curl_close($ch);
	} else {
		$context = stream_context_create(array(
			'http' => array(
				'method' => 'POST',
				'header' => "Content-type: application/x-www-form-urlencoded\r\n",
				'content' => http_build_query($post_fields),
				'timeout' => 10,
			),
		));
		$response = @file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, $context);
	}

	if ($response === false) {
		die(
			'Could not verify security check. Please try again. ' .
			'<a href="' . htmlspecialchars($back_url, ENT_QUOTES, 'UTF-8') . '">Back</a>'
		);
	}

	$result = json_decode($response, true);
	if (empty($result['success'])) {
		die(
			'Security verification failed. Please go back and try again. ' .
			'<a href="' . htmlspecialchars($back_url, ENT_QUOTES, 'UTF-8') . '">Back</a>'
		);
	}
}

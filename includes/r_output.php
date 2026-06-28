<?php

function sanitize_r_output_for_display($text) {
	if ($text === false || $text === null) {
		return '';
	}
	$text = (string) $text;
	if ($text === '') {
		return '';
	}

	$replacements = array(
		'/home/urisoh5/public_html/webstimate.org/' => '[app]/',
		'/home/urisoh5/uploaded_data/webstimate.org/' => '[uploads]/',
		'/home/urisoh5/' => '[server]/',
		'/usr/local/R/library' => '[R library]',
		'/usr/lib64/R/library' => '[R library]',
	);
	$text = str_replace(array_keys($replacements), array_values($replacements), $text);

	$text = preg_replace('#/(?:home|usr|var|tmp|opt)(?:/[\w._-]+)+#', '[path]', $text);
	$text = preg_replace('#[A-Za-z]:\\\\(?:[\w._-]+\\\\)*[\w._-]*#', '[path]', $text);

	return $text;
}

function trim_r_startup_banner($text) {
	if (preg_match('/\n> /', $text, $match, PREG_OFFSET_CAPTURE)) {
		return ltrim(substr($text, $match[0][1]));
	}
	return $text;
}

function read_r_batch_output($rout_file, $exec_output = array()) {
	$parts = array();

	if ($rout_file !== '' && file_exists($rout_file)) {
		$content = file_get_contents($rout_file);
		if ($content !== false && trim($content) !== '') {
			$parts[] = trim_r_startup_banner($content);
		}
	}

	if (is_array($exec_output) && count($exec_output) > 0) {
		$joined = trim(implode("\n", $exec_output));
		if ($joined !== '') {
			$parts[] = trim_r_startup_banner($joined);
		}
	}

	$combined = trim(implode("\n\n", $parts));
	if ($combined === '') {
		return '';
	}

	return sanitize_r_output_for_display($combined);
}

function r_output_panel_html($body_html, $extra_class = '') {
	$class = 'output-panel';
	if ($extra_class !== '') {
		$class .= ' ' . $extra_class;
	}
	return '<div class="' . $class . '" style="background:#f4f5f7;border:1px solid #e3e6ea;border-radius:4px;padding:16px 18px;margin-top:16px;">' .
		$body_html .
		'</div>';
}

function r_error_output_html($r_output) {
	if ($r_output === '') {
		return '';
	}
	return r_output_panel_html(
		'<pre class="output-panel-pre">' .
		htmlspecialchars($r_output, ENT_QUOTES, 'UTF-8') .
		'</pre>',
		'output-panel-error'
	);
}

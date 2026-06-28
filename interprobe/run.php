<?
session_start();
require_once __DIR__ . '/../includes/job_traffic.php';
require_once __DIR__ . '/../includes/r_output.php';
require_once __DIR__ . '/../includes/interprobe_upload.php';
job_traffic_check_and_record_or_die('configure.php');
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<head>
  <title>Johnson-Neyman 2.0: Online App for Nonlinear Probing of Interactions - Results</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <style>
    .jumbotron h1 { font-size: 26px; line-height: 1.35; font-weight: 600; }
    .output-panel {
      background: #f4f5f7;
      border: 1px solid #e3e6ea;
      border-radius: 4px;
      padding: 16px 18px;
      margin-top: 16px;
    }
    .output-panel-interprobe {
      font-size: 16px;
      line-height: 1.55;
      color: #222;
    }
    .output-panel-interprobe .output-panel-pre {
      font-family: inherit;
      font-size: inherit;
      line-height: inherit;
    }
    .output-panel-r-code {
      font-size: 12px;
      line-height: 1.55;
      color: #444;
    }
    .output-panel-r-code h3 {
      margin: 0 0 12px;
      font-size: 14px;
      font-weight: 600;
      color: #333;
    }
    .output-panel-error {
      font-size: 16px;
      line-height: 1.55;
      color: #222;
    }
    .output-panel-error-msg {
      margin-bottom: 12px;
    }
    .output-panel-pre {
      margin: 0;
      padding: 0;
      border: none;
      background: none;
      white-space: pre-wrap;
      word-break: break-word;
      font-family: Consolas, "Courier New", monospace;
    }
    .output-panel-r-code .output-panel-pre {
      font-size: 12px;
      color: #444;
    }
    .output-panel-meta {
      margin: 0 0 10px;
      font-size: 13px;
      color: #666;
    }
  </style>
</head>
<body>
<?

$file      = $_SESSION['file'];
$dir       = $_SESSION['dir'];
$dir_data  = $_SESSION['dir_data'];
$time      = $_SESSION['time'];
$extension = $_SESSION['extension'];
$variables = isset($_SESSION['variables']) ? $_SESSION['variables'] : array();

$original_file = interprobe_resolve_original_filename(
	$dir_data,
	$file,
	$time,
	isset($_POST['original_file']) ? $_POST['original_file'] : null,
	isset($_SESSION['original_file']) ? $_SESSION['original_file'] : null
);

$y = isset($_POST['y']) ? $_POST['y'] : '';
$x = isset($_POST['x']) ? $_POST['x'] : '';
$z = isset($_POST['z']) ? $_POST['z'] : '';
$covariates = isset($_POST['cov']) && is_array($_POST['cov']) ? $_POST['cov'] : array();
$cov_linear = isset($_POST['cov_linear']) && is_array($_POST['cov_linear']) ? $_POST['cov_linear'] : array();
$model_type = (isset($_POST['model_type']) && $_POST['model_type'] === 'linear') ? 'linear' : 'gam';

function valid_var_name($name) {
	return preg_match('/^[A-Za-z][A-Za-z0-9._]*$/', $name);
}

function model_summary_html($model_type) {
	$label = ($model_type === 'linear') ? 'linear regression' : 'GAM';
	return 'Model: ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '.<BR><BR>';
}

function covariate_summary_html($covariates, $cov_linear, $model_type = 'gam') {
	if (empty($covariates)) {
		return '';
	}
	$parts = array();
	foreach ($covariates as $cov) {
		if ($model_type === 'linear' || in_array($cov, $cov_linear, true)) {
			$parts[] = htmlspecialchars($cov, ENT_QUOTES, 'UTF-8');
			if ($model_type === 'gam' && in_array($cov, $cov_linear, true)) {
				$parts[count($parts) - 1] .= ' (linear)';
			}
		} else {
			$parts[] = 's(' . htmlspecialchars($cov, ENT_QUOTES, 'UTF-8') . ')';
		}
	}
	return 'Covariates: ' . implode(', ', $parts) . '.<BR><BR>';
}

function r_quote($name) {
	return '"' . str_replace('"', '\\"', $name) . '"';
}

function r_groundhog_lines($pkgs) {
	$pkg_list = implode("', '", $pkgs);
	return array(
		"library('groundhog')",
		"pkgs=c('" . $pkg_list . "')",
		"groundhog.library(pkgs,'2026-06-27')",
	);
}

function r_commands_text($y, $x, $z, $data_filename, $model_type, $covariates, $cov_linear) {
	$save_as = 'interprobe_plot.png';
	$pkgs = array('rio', 'statuser');
	if ($model_type === 'gam' && !empty($covariates)) {
		$pkgs[] = 'mgcv';
	}
	$lines = r_groundhog_lines($pkgs);
	$lines[] = 'data.imported <- import(' . r_quote($data_filename) . ')';
	$lines[] = '';

	if (empty($covariates) && $model_type === 'gam') {
		$lines[] = 'interprobe(';
		$lines[] = '  x = ' . r_quote($x) . ',';
		$lines[] = '  z = ' . r_quote($z) . ',';
		$lines[] = '  y = ' . r_quote($y) . ',';
		$lines[] = '  data = data.imported,';
		$lines[] = '  save.as = ' . r_quote($save_as);
		$lines[] = ')';
	} elseif (empty($covariates) && $model_type === 'linear') {
		$lines[] = 'interprobe(';
		$lines[] = '  x = ' . r_quote($x) . ',';
		$lines[] = '  z = ' . r_quote($z) . ',';
		$lines[] = '  y = ' . r_quote($y) . ',';
		$lines[] = '  data = data.imported,';
		$lines[] = '  model = linear,';
		$lines[] = '  save.as = ' . r_quote($save_as);
		$lines[] = ')';
	} elseif ($model_type === 'gam') {
		$lines[] = 'source("build_gam.r")';
		$cov_parts = array();
		foreach ($covariates as $cov) {
			$cov_parts[] = r_quote($cov);
		}
		$lines[] = 'covs <- c(' . implode(', ', $cov_parts) . ')';
		$linear_parts = array();
		foreach ($covariates as $cov) {
			$linear_parts[] = in_array($cov, $cov_linear, true) ? 'TRUE' : 'FALSE';
		}
		$lines[] = 'cov_linear <- c(' . implode(', ', $linear_parts) . ')';
		$lines[] = 'fit <- build_interprobe_gam(';
		$lines[] = '  y = ' . r_quote($y) . ',';
		$lines[] = '  x = ' . r_quote($x) . ',';
		$lines[] = '  z = ' . r_quote($z) . ',';
		$lines[] = '  data = data.imported,';
		$lines[] = '  covs = covs,';
		$lines[] = '  cov_linear = cov_linear';
		$lines[] = ')';
		$lines[] = 'interprobe(';
		$lines[] = '  model = fit,';
		$lines[] = '  x = ' . r_quote($x) . ',';
		$lines[] = '  z = ' . r_quote($z) . ',';
		$lines[] = '  y = ' . r_quote($y) . ',';
		$lines[] = '  data = data.imported,';
		$lines[] = '  save.as = ' . r_quote($save_as);
		$lines[] = ')';
	} else {
		$lines[] = 'source("build_gam.r")';
		$cov_parts = array();
		foreach ($covariates as $cov) {
			$cov_parts[] = r_quote($cov);
		}
		$lines[] = 'covs <- c(' . implode(', ', $cov_parts) . ')';
		$lines[] = 'fit <- build_interprobe_linear(';
		$lines[] = '  y = ' . r_quote($y) . ',';
		$lines[] = '  x = ' . r_quote($x) . ',';
		$lines[] = '  z = ' . r_quote($z) . ',';
		$lines[] = '  data = data.imported,';
		$lines[] = '  covs = covs';
		$lines[] = ')';
		$lines[] = 'interprobe(';
		$lines[] = '  model = fit,';
		$lines[] = '  x = ' . r_quote($x) . ',';
		$lines[] = '  z = ' . r_quote($z) . ',';
		$lines[] = '  y = ' . r_quote($y) . ',';
		$lines[] = '  data = data.imported,';
		$lines[] = '  save.as = ' . r_quote($save_as);
		$lines[] = ')';
	}

	return implode("\n", $lines);
}

function r_commands_panel_html($r_commands_text) {
	$body =
		'<h3>Reproducible R Code Produced</h3>' .
		'<pre class="output-panel-pre">' . htmlspecialchars($r_commands_text, ENT_QUOTES, 'UTF-8') . '</pre>';
	return r_output_panel_html($body, 'output-panel-r-code');
}

function interprobe_output_panel_html($console_text, $statuser_version_label) {
	$meta = '';
	if ($statuser_version_label !== '') {
		$meta = '<p class="output-panel-meta">' . htmlspecialchars($statuser_version_label, ENT_QUOTES, 'UTF-8') . '</p>';
	}
	$body = $meta .
		'<pre class="output-panel-pre">' . htmlspecialchars($console_text, ENT_QUOTES, 'UTF-8') . '</pre>';
	return r_output_panel_html($body, 'output-panel-interprobe');
}

function die_alert($msg) {
	die("<div class='container'><BR><div class='alert alert-danger'>$msg</div></div></body></html>");
}

function die_run_error($msg, $r_output = '') {
	$error_body = '<div class="output-panel-error-msg">' . $msg . '</div>';
	if ($r_output !== '') {
		$error_body .= '<pre class="output-panel-pre">' .
			htmlspecialchars($r_output, ENT_QUOTES, 'UTF-8') .
			'</pre>';
	}
	die(
		"<div class='jumbotron text-center'><h1>Johnson-Neyman 2.0: Online App for Nonlinear Probing of Interactions</h1></div>".
		"<div class='container'>".
		r_output_panel_html($error_body, 'output-panel-error').
		"<BR><a href='configure.php' class='btn btn-default'>Go back</a>".
		"</div></body></html>"
	);
}

if ($y === '' || $x === '' || $z === '') {
	die_alert("Please select a dependent variable, focal predictor, and moderator.");
}
if ($y === $x || $y === $z || $x === $z) {
	die_alert("Dependent, focal predictor, and moderator must be three different variables.");
}
if (!in_array($y, $variables) || !in_array($x, $variables) || !in_array($z, $variables)) {
	die_alert("Invalid variable selection. Please go back and try again.");
}
if (!preg_match('/^[A-Za-z][A-Za-z0-9._]*$/', $y) ||
	!preg_match('/^[A-Za-z][A-Za-z0-9._]*$/', $x) ||
	!preg_match('/^[A-Za-z][A-Za-z0-9._]*$/', $z)) {
	die_alert("Variable names contain invalid characters.");
}

$covariates = array_values(array_unique($covariates));
foreach ($covariates as $cov) {
	if (!valid_var_name($cov) || !in_array($cov, $variables, true)) {
		die_alert("Invalid covariate selection. Please go back and try again.");
	}
	if ($cov === $y || $cov === $x || $cov === $z) {
		die_alert("Covariates cannot be the same as the dependent, focal, or moderator variable.");
	}
}
if ($model_type === 'linear') {
	$cov_linear = array();
} else {
	$cov_linear = array_values(array_unique($cov_linear));
	foreach ($cov_linear as $cov) {
		if (!in_array($cov, $covariates, true)) {
			die_alert("Invalid linear covariate selection. Please go back and try again.");
		}
	}
}

$y_r = addslashes($y);
$x_r = addslashes($x);
$z_r = addslashes($z);

$png_file = $time.".png";
$console_file = $dir."console_".$time.".txt";
$statuser_version_file = $dir."statuser_".$time.".txt";
$data_path = $dir_data.$file;
$png_path = $dir.$png_file;
$rout_file = $dir.$time."_interprobe.Rout";
$batch_script = $dir.$time."_interprobe";
$build_gam_r = '/home/urisoh5/public_html/webstimate.org/interprobe/build_gam.r';

if (empty($covariates) && $model_type === 'gam') {
	$interprobe_call = <<<RCODE
	interprobe(
		x="$x_r",
		z="$z_r",
		y="$y_r",
		data=data.imported,
		save.as="$png_path"
	)
RCODE;
} elseif (empty($covariates) && $model_type === 'linear') {
	$interprobe_call = <<<RCODE
	interprobe(
		x="$x_r",
		z="$z_r",
		y="$y_r",
		data=data.imported,
		model=linear,
		save.as="$png_path"
	)
RCODE;
} elseif ($model_type === 'gam') {
	$cov_r_parts = array();
	foreach ($covariates as $cov) {
		$cov_r_parts[] = '"'.addslashes($cov).'"';
	}
	$cov_linear_r_parts = array();
	foreach ($covariates as $cov) {
		$cov_linear_r_parts[] = in_array($cov, $cov_linear, true) ? 'TRUE' : 'FALSE';
	}
	$cov_r = 'c('.implode(', ', $cov_r_parts).')';
	$cov_linear_r = 'c('.implode(', ', $cov_linear_r_parts).')';

	$interprobe_call = <<<RCODE
	source("$build_gam_r")
	library(mgcv)
	covs <- $cov_r
	cov_linear <- $cov_linear_r
	fit <- build_interprobe_gam(
		y="$y_r",
		x="$x_r",
		z="$z_r",
		data=data.imported,
		covs=covs,
		cov_linear=cov_linear
	)
	interprobe(
		model=fit,
		x="$x_r",
		z="$z_r",
		y="$y_r",
		data=data.imported,
		save.as="$png_path"
	)
RCODE;
} else {
	$cov_r_parts = array();
	foreach ($covariates as $cov) {
		$cov_r_parts[] = '"'.addslashes($cov).'"';
	}
	$cov_r = 'c('.implode(', ', $cov_r_parts).')';

	$interprobe_call = <<<RCODE
	source("$build_gam_r")
	covs <- $cov_r
	fit <- build_interprobe_linear(
		y="$y_r",
		x="$x_r",
		z="$z_r",
		data=data.imported,
		covs=covs
	)
	interprobe(
		model=fit,
		x="$x_r",
		z="$z_r",
		y="$y_r",
		data=data.imported,
		save.as="$png_path"
	)
RCODE;
}

$rcode = <<<RCODE
	.libPaths("/usr/local/R/library")
	library(rio)
	library(statuser)
	writeLines(as.character(packageVersion("statuser")), "$statuser_version_file")

	setwd("$dir")
	data.imported=import("$data_path")
	if (!exists("data.imported")) stop("Sorry, R was unable to read the file $file")

	sink("$console_file")
$interprobe_call
	sink()
RCODE;

file_put_contents($batch_script, $rcode);

if (file_exists($png_path)) unlink($png_path);
if (file_exists($console_file)) unlink($console_file);
if (file_exists($statuser_version_file)) unlink($statuser_version_file);
if (file_exists($rout_file)) unlink($rout_file);

chdir($dir);
exec("/usr/bin/R --no-save CMD BATCH ".$time."_interprobe 2>&1", $exec_output, $exec_code);

if (!file_exists($png_path)) {
	die_run_error(
		"Something went wrong running interprobe on your data. Please check your variable selection and try again. ".
		"Email <a href='mailto:urisohn@gmail.com'>Uri</a> if you think something is broken.",
		read_r_batch_output($rout_file, $exec_output)
	);
}

$console_text = "";
if (file_exists($console_file)) {
	$console_text = file_get_contents($console_file);
}
$r_commands_text = r_commands_text(
	$y, $x, $z, $original_file, $model_type, $covariates, $cov_linear
);

$statuser_version_label = "statuser";
if (file_exists($statuser_version_file)) {
	$statuser_ver = trim(file_get_contents($statuser_version_file));
	if ($statuser_ver !== "") {
		$statuser_version_label = "statuser v" . htmlspecialchars($statuser_ver, ENT_QUOTES, 'UTF-8');
	}
}
?>

<div class="jumbotron text-center">
  <h1>Johnson-Neyman 2.0: Online App for Nonlinear Probing of Interactions</h1>
</div>

<div class="container">
	<h2><b>Results</b></h2>
	<font size='4'>
		Probing the interaction of <b><?echo htmlspecialchars($x);?></b> &times;
		<b><?echo htmlspecialchars($z);?></b> on <b><?echo htmlspecialchars($y);?></b>.<BR><BR>
		<?echo model_summary_html($model_type); ?>
		<?echo covariate_summary_html($covariates, $cov_linear, $model_type); ?>
		<img src="temp/<?echo $png_file;?>" width="1000"><BR><BR>
	</font>

	<h2><b>Output</b></h2>
	<?echo interprobe_output_panel_html($console_text, $statuser_version_label); ?>
	<?echo r_commands_panel_html($r_commands_text); ?>
</div>

<?
if (file_exists(".RData")) unlink(".RData");
?>
</body>
</html>

<?
session_start();
require_once __DIR__ . '/../includes/job_traffic.php';
job_traffic_check_and_record_or_die('configure.php');
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<head>
  <title>Johnson-Neyman 2.0: Online App for Nonlinear Probing of Interactions - Results</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <style>.jumbotron h1 { font-size: 26px; line-height: 1.35; font-weight: 600; }</style>
</head>
<body>
<?

$file      = $_SESSION['file'];
$dir       = $_SESSION['dir'];
$dir_data  = $_SESSION['dir_data'];
$time      = $_SESSION['time'];
$extension = $_SESSION['extension'];
$variables = isset($_SESSION['variables']) ? $_SESSION['variables'] : array();

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

function r_commands_display($y, $x, $z, $png_file, $model_type, $covariates, $cov_linear, $build_gam_r) {
	$save_as = 'temp/' . $png_file;
	$lines = array('library(rio)', 'library(statuser)', 'data.imported <- import("your_data_file")', '');

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
		$lines[] = 'source(' . r_quote($build_gam_r) . ')';
		$lines[] = 'library(mgcv)';
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
		$lines[] = 'source(' . r_quote($build_gam_r) . ')';
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

	return "--- R commands used ---\n\n" . implode("\n", $lines) . "\n\n--- interprobe output ---\n\n";
}

function die_alert($msg) {
	die("<div class='container'><BR><div class='alert alert-danger'>$msg</div></div></body></html>");
}

function die_run_error($msg) {
	die(
		"<div class='jumbotron text-center'><h1>Johnson-Neyman 2.0: Online App for Nonlinear Probing of Interactions</h1></div>".
		"<div class='container'>".
		"<div class='alert alert-danger'>$msg</div>".
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
		"Email <a href='mailto:urisohn@gmail.com'>Uri</a> if you think something is broken."
	);
}

$console_text = "";
if (file_exists($console_file)) {
	$console_text = file_get_contents($console_file);
}
$console_text = r_commands_display(
	$y, $x, $z, $png_file, $model_type, $covariates, $cov_linear, $build_gam_r
) . $console_text;

$statuser_version_label = "(statuser)";
if (file_exists($statuser_version_file)) {
	$statuser_ver = trim(file_get_contents($statuser_version_file));
	if ($statuser_ver !== "") {
		$statuser_version_label = "(statuser v".htmlspecialchars($statuser_ver, ENT_QUOTES, 'UTF-8').")";
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

	<h2><b>Console output</b> <?echo $statuser_version_label;?></h2>
	<textarea readonly rows="20" style="width:100%; font-family:monospace;"><?echo htmlspecialchars($console_text);?></textarea>
	<BR>
	<font size="2" color="gray">(R commands used, then output from interprobe() when running in R)</font>
</div>

<?
if (file_exists(".RData")) unlink(".RData");
?>
</body>
</html>

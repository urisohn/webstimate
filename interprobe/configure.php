<?
session_start();
error_reporting(E_ALL);
require_once __DIR__ . '/../includes/r_output.php';
require_once __DIR__ . '/../includes/interprobe_upload.php';
?>
<head>
  <title>Johnson-Neyman 2.0: Online App for Nonlinear Probing of Interactions</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<style>
body { color: #333; }
.jumbotron { padding-top: 28px; padding-bottom: 28px; margin-bottom: 0; background: #f7f9fc; border-bottom: 1px solid #e3e8ef; }
.jumbotron h1 { font-size: 26px; line-height: 1.35; font-weight: 600; letter-spacing: -0.3px; }
.configure-panel { max-width: 640px; margin: 0 auto; padding: 28px 15px 32px; }
.configure-intro-row {
	display: flex;
	justify-content: space-between;
	align-items: center;
	gap: 12px;
	margin-bottom: 18px;
}
.configure-intro { font-size: 19.2px; line-height: 1.5; margin: 0; font-weight: bold; flex: 1; }
.var-table { width: 100%; margin-bottom: 0; }
.var-table th, .var-table td { padding: 6px 8px; text-align: center; vertical-align: middle; }
.var-table th { white-space: normal; line-height: 1.25; font-size: 13px; }
.var-table td:first-child { text-align: left; white-space: nowrap; max-width: 140px; overflow: hidden; text-overflow: ellipsis; }
.var-table label { display: block; margin: 0; cursor: pointer; font-weight: normal; }
.regression-option {
	display: flex;
	align-items: center;
	gap: 6px;
	margin-top: 12px;
	font-size: 13px;
	color: #777;
	font-weight: normal;
}
.regression-option label {
	margin: 0;
	cursor: pointer;
	font-weight: normal;
}
.regression-info-btn {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 18px;
	height: 18px;
	padding: 0;
	border: 1px solid #bbb;
	border-radius: 50%;
	background: #f5f5f5;
	color: #666;
	font-size: 12px;
	font-weight: 600;
	line-height: 1;
	cursor: pointer;
	vertical-align: middle;
}
.regression-info-btn:hover,
.regression-info-btn:focus {
	background: #e8e8e8;
	border-color: #999;
	color: #444;
	outline: none;
}
.regression-info-panel {
	display: none;
	margin-top: 10px;
	padding: 10px 12px;
	font-size: 13px;
	line-height: 1.5;
	color: #555;
	background: #f8f9fa;
	border: 1px solid #e0e0e0;
	border-radius: 4px;
	max-width: 520px;
}
.regression-info-panel.visible { display: block; }
.var-table .cov-col { display: none; }
.configure-actions { margin-top: 16px; }
.configure-actions .btn-primary { min-width: 120px; }
.var-table.cov-visible .cov-col { display: table-cell; }
#varTable.model-linear .cov-linear-col { display: none !important; }
</style>
</head>
<body>
<?

$file     = $_SESSION['file'];
$dir      = $_SESSION['dir'];
$dir_data = $_SESSION['dir_data'];
$time     = $_SESSION['time'];
$extension= $_SESSION['extension'];

$original_file = interprobe_get_original_filename($dir_data, $dir, $file, $time);
interprobe_store_original_filename_in_session($original_file);

if (!file_exists($dir_data.$file)) {
	die("Sorry, the file was not uploaded. Please try again.");
}

$rcode = <<<RCODE
	source("/home/urisoh5/public_html/webstimate.org/interprobe/vars.r")
	vars("$time","$extension")
RCODE;

$vars_file = $dir_data."vars_".$file;

file_put_contents($dir.$time."_vars", $rcode);
chdir($dir);
$rout_file = $dir.$time."_vars.Rout";
exec("/usr/bin/R --no-save CMD BATCH ".$time."_vars 2>&1", $exec_output, $exec_code);

if (!file_exists($vars_file)) {
	$r_output = read_r_batch_output($rout_file, $exec_output);
	die(
		"<div class='container'><div class='alert alert-danger'><font size='5'>Sorry, your file did not upload, or the app could not read it. The app relies on the RIO package in R and can read .csv, .xlsx, .sav, among ".
		"many other formats. See <a href='https://cran.r-project.org/web/packages/rio/vignettes/rio.html'>supported formats</a>.<BR><BR>".
		"If you think something is broken, please let Uri know (urisohn@gmail.com)</font></div>".
		r_error_output_html($r_output).
		"<BR><a href='index.php' class='btn btn-default'>Go back</a></div>"
	);
}

$variables = array();
$f = fopen($vars_file, "r");
$header = fgetcsv($f);
while (($row = fgetcsv($f)) !== false) {
	if (isset($row[0]) && $row[0] !== "") {
		$variables[] = $row[0];
	}
}
fclose($f);
$_SESSION['variables'] = $variables;
$show_covariates = count($variables) > 3;
?>

<div class="jumbotron text-center">
	<h1>Johnson-Neyman 2.0: Online App for Nonlinear Probing of Interactions</h1>
</div>

<div class="container">
<div class="configure-panel">
<div class="configure-intro-row">
<p class="configure-intro">Select one variable for each role.</p>
<? if ($show_covariates) { ?>
<button type="button" id="addCovariatesBtn" class="btn btn-default btn-sm">Add covariates</button>
<? } ?>
</div>

<form method="post" action="run.php" id="configureForm">
<input type="hidden" name="model_type" id="modelType" value="gam">
<input type="hidden" name="original_file" value="<? echo htmlspecialchars($original_file, ENT_QUOTES, 'UTF-8'); ?>">
<table class="table table-striped var-table" id="varTable">
	<tr>
		<th>Variable</th>
		<th>Dependent<br>(y)</th>
		<th>Focal predictor<br>(x)</th>
		<th>Moderator<br>(z)</th>
<? if ($show_covariates) { ?>
		<th class="cov-col">Covariate</th>
		<th class="cov-col cov-linear-col">Linear</th>
<? } ?>
	</tr>
<?
foreach ($variables as $var) {
	$safe = htmlspecialchars($var, ENT_QUOTES, 'UTF-8');
	$attr = htmlspecialchars($var, ENT_QUOTES, 'UTF-8');
	echo "<tr class='var-row' data-var='$attr'>";
	echo "<td>$safe</td>";
	echo "<td><label><input type='radio' name='y' value='$safe' class='role-select'></label></td>";
	echo "<td><label><input type='radio' name='x' value='$safe' class='role-select'></label></td>";
	echo "<td><label><input type='radio' name='z' value='$safe' class='role-select'></label></td>";
	if ($show_covariates) {
		echo "<td class='cov-col'><label><input type='checkbox' name='cov[]' value='$safe' class='cov-select'></label></td>";
		echo "<td class='cov-col cov-linear-col'><label><input type='checkbox' name='cov_linear[]' value='$safe' class='cov-linear' disabled></label></td>";
	}
	echo "</tr>\n";
}
?>
</table>
<div class="configure-actions">
<input type="submit" name="submit" value="Run" class="btn btn-primary btn-lg">
<div class="regression-option">
	<label><input type="checkbox" id="runRegressionInstead"> Run regression instead of GAM</label>
	<button type="button" class="regression-info-btn" id="regressionInfoBtn" title="Click for info" aria-label="Click for info">?</button>
</div>
<div class="regression-info-panel" id="regressionInfoPanel">
	If you want to run the legacy approach to probing interactions, relying on arbitrary linearity assumptions, check the box. This can be useful when contrasting results to those in published papers, or when exploring nonlinearities in a new paper.
</div>
</div>
</form>
</div>
</div>

<script>
(function () {
	var varTable = document.getElementById("varTable");
	var modelType = document.getElementById("modelType");
	var runRegressionInstead = document.getElementById("runRegressionInstead");
	var regressionInfoBtn = document.getElementById("regressionInfoBtn");
	var regressionInfoPanel = document.getElementById("regressionInfoPanel");

	function syncModelType() {
		if (runRegressionInstead.checked) {
			modelType.value = "linear";
			varTable.classList.add("model-linear");
			document.querySelectorAll(".cov-linear").forEach(function (input) {
				input.checked = false;
			});
		} else {
			modelType.value = "gam";
			varTable.classList.remove("model-linear");
		}
		if (typeof updateAllRowCovStates === "function") {
			updateAllRowCovStates();
		}
	}

	runRegressionInstead.addEventListener("change", syncModelType);
	syncModelType();

	regressionInfoBtn.addEventListener("click", function () {
		regressionInfoPanel.classList.toggle("visible");
	});

<? if ($show_covariates) { ?>
	function rowForInput(input) {
		return input.closest("tr");
	}

	function roleSelectedOnRow(row) {
		return row.querySelector(".role-select:checked") !== null;
	}

	function updateRowCovState(row) {
		var cov = row.querySelector(".cov-select");
		var linear = row.querySelector(".cov-linear");
		if (!cov) return;
		if (roleSelectedOnRow(row)) {
			cov.checked = false;
			cov.disabled = true;
			if (linear) {
				linear.checked = false;
				linear.disabled = true;
			}
		} else {
			cov.disabled = false;
			if (linear) {
				linear.disabled = !cov.checked || modelType.value === "linear";
			}
		}
	}

	window.updateAllRowCovStates = function () {
		document.querySelectorAll(".var-row").forEach(updateRowCovState);
	};

	function clearRolesForVar(varName) {
		document.querySelectorAll(".role-select").forEach(function (input) {
			if (input.value === varName) {
				input.checked = false;
			}
		});
		updateAllRowCovStates();
	}

	document.querySelectorAll(".role-select").forEach(function (input) {
		input.addEventListener("change", updateAllRowCovStates);
	});

	document.querySelectorAll(".cov-select").forEach(function (input) {
		input.addEventListener("change", function () {
			var row = rowForInput(input);
			var linear = row.querySelector(".cov-linear");
			if (input.checked) {
				clearRolesForVar(input.value);
			} else if (linear) {
				linear.checked = false;
				linear.disabled = true;
			}
			updateRowCovState(row);
		});
	});

	document.getElementById("addCovariatesBtn").addEventListener("click", function () {
		varTable.classList.add("cov-visible");
		this.style.display = "none";
		updateAllRowCovStates();
	});

	updateAllRowCovStates();
<? } ?>
})();
</script>
</body>

<?
session_start();
error_reporting(E_ALL);
?>
<head>
  <title>Johnson-Neyman 2.0: Online App for Nonlinear Probing of Interactions</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<style>
body { color: #333; }
.jumbotron { padding-top: 28px; padding-bottom: 28px; margin-bottom: 0; background: #f7f9fc; border-bottom: 1px solid #e3e8ef; }
.jumbotron h1 { font-size: 26px; line-height: 1.35; font-weight: 600; letter-spacing: -0.3px; }
.configure-panel { max-width: 520px; margin: 0 auto; padding: 28px 15px 32px; }
.configure-intro { font-size: 16px; line-height: 1.5; margin: 0 0 18px; }
.var-table { width: 100%; margin-bottom: 0; }
.var-table th, .var-table td { padding: 6px 8px; text-align: center; vertical-align: middle; }
.var-table th { white-space: normal; line-height: 1.25; font-size: 13px; }
.var-table td:first-child { text-align: left; white-space: nowrap; max-width: 140px; overflow: hidden; text-overflow: ellipsis; }
.var-table label { display: block; margin: 0; cursor: pointer; font-weight: normal; }
.var-table .cov-linear-label { font-size: 12px; color: #666; }
.var-table-toolbar {
	display: flex;
	justify-content: flex-end;
	align-items: center;
	gap: 12px;
	margin-bottom: 8px;
	flex-wrap: wrap;
}
.model-toggle-box {
	display: flex;
	align-items: center;
	gap: 10px;
	font-size: 13px;
	color: #444;
	padding: 8px 14px;
	border: 1px solid #ccc;
	border-radius: 8px;
	background: #fafbfc;
}
.model-toggle-box .toggle-label { font-weight: 600; color: #333; white-space: nowrap; }
.model-toggle-box .toggle-option {
	white-space: nowrap;
	color: #999;
	font-weight: 400;
	transition: color 0.2s, font-weight 0.2s;
}
.model-toggle-box .toggle-option.active { color: #337ab7; font-weight: 600; }
.mac-toggle {
	position: relative;
	display: inline-block;
	width: 44px;
	height: 26px;
	margin: 0;
	vertical-align: middle;
}
.mac-toggle input {
	opacity: 0;
	width: 0;
	height: 0;
}
.mac-slider {
	position: absolute;
	cursor: pointer;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: #ccc;
	border-radius: 26px;
	transition: background-color 0.2s;
}
.mac-slider:before {
	position: absolute;
	content: "";
	height: 22px;
	width: 22px;
	left: 2px;
	bottom: 2px;
	background-color: white;
	border-radius: 50%;
	transition: transform 0.2s;
	box-shadow: 0 1px 3px rgba(0,0,0,0.25);
}
.mac-toggle input:checked + .mac-slider {
	background-color: #34c759;
}
.mac-toggle input:checked + .mac-slider:before {
	transform: translateX(18px);
}
.mac-toggle input:focus-visible + .mac-slider {
	box-shadow: 0 0 0 2px #fff, 0 0 0 4px #337ab7;
}
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
exec("/usr/bin/R --no-save CMD BATCH ".$time."_vars");

if (!file_exists($vars_file)) {
	die(
		"<div class='container'><div class='alert alert-danger'><font size='5'>Sorry, your file did not upload, or the app could not read it. The app relies on the RIO package in R and can read .csv, .xlsx, .sav, among ".
		"many other formats. See <a href='https://cran.r-project.org/web/packages/rio/vignettes/rio.html'>supported formats</a>.<BR><BR>".
		"If you think something is broken, please let Uri know (urisohn@gmail.com)</font></div></div>"
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
<p class="configure-intro">Select one variable for each role, then click Run.</p>

<form method="post" action="run.php" id="configureForm">
<input type="hidden" name="model_type" id="modelType" value="gam">
<div class="var-table-toolbar">
	<div class="model-toggle-box">
		<span class="toggle-label">Probe interaction with:</span>
		<span class="toggle-option" id="toggleRegression">Regression</span>
		<label class="mac-toggle" title="Switch between regression and GAM">
			<input type="checkbox" id="modelGamToggle" checked aria-label="Use GAM instead of regression">
			<span class="mac-slider"></span>
		</label>
		<span class="toggle-option active" id="toggleGam">GAM</span>
	</div>
<? if ($show_covariates) { ?>
	<button type="button" id="addCovariatesBtn" class="btn btn-default btn-sm">Add covariates</button>
<? } ?>
</div>
<table class="table table-striped var-table" id="varTable">
	<tr>
		<th>Variable</th>
		<th>Dependent<br>(y)</th>
		<th>Focal<br>predictor<br>(x)</th>
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
		echo "<td class='cov-col cov-linear-col'><label class='cov-linear-label'><input type='checkbox' name='cov_linear[]' value='$safe' class='cov-linear' disabled> linear</label></td>";
	}
	echo "</tr>\n";
}
?>
</table>
<div class="configure-actions">
<input type="submit" name="submit" value="Run" class="btn btn-primary btn-lg">
</div>
</form>
</div>
</div>

<script>
(function () {
	var varTable = document.getElementById("varTable");
	var modelType = document.getElementById("modelType");
	var modelGamToggle = document.getElementById("modelGamToggle");

	var toggleRegression = document.getElementById("toggleRegression");
	var toggleGam = document.getElementById("toggleGam");

	function syncModelType() {
		if (modelGamToggle.checked) {
			modelType.value = "gam";
			varTable.classList.remove("model-linear");
		} else {
			modelType.value = "linear";
			varTable.classList.add("model-linear");
			document.querySelectorAll(".cov-linear").forEach(function (input) {
				input.checked = false;
			});
		}
		toggleRegression.classList.toggle("active", !modelGamToggle.checked);
		toggleGam.classList.toggle("active", modelGamToggle.checked);
		if (typeof updateAllRowCovStates === "function") {
			updateAllRowCovStates();
		}
	}

	modelGamToggle.addEventListener("change", syncModelType);
	syncModelType();

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

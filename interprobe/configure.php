<?
session_start();
error_reporting(E_ALL);
?>
<head>
  <title>Johnson-Neyman 2.0: Online App for Nonlinear Probing of Interactions</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<style>
.jumbotron h1 { font-size: 26px; line-height: 1.35; font-weight: 600; }
.var-table { width: auto; max-width: 100%; }
.var-table th, .var-table td { padding: 6px 10px; text-align: center; vertical-align: middle; }
.var-table th { white-space: normal; line-height: 1.25; font-size: 13px; }
.var-table td:first-child { text-align: left; white-space: nowrap; }
.var-table label { display: block; margin: 0; cursor: pointer; font-weight: normal; }
.var-table .cov-linear-label { font-size: 12px; color: #666; }
.var-table-toolbar { text-align: right; margin-bottom: 8px; }
.var-table .cov-col { display: none; }
.var-table.cov-visible .cov-col { display: table-cell; }
</style>
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
<font size='4'>Select one variable for each role, then click Run.<BR><BR></font>

<form method="post" action="run.php" id="configureForm">
<? if ($show_covariates) { ?>
<div class="var-table-toolbar">
	<button type="button" id="addCovariatesBtn" class="btn btn-default btn-sm">Add covariates</button>
</div>
<? } ?>
<table class="table table-striped var-table" id="varTable">
	<tr>
		<th>Variable</th>
		<th>Dependent<br>(y)</th>
		<th>Focal<br>predictor<br>(x)</th>
		<th>Moderator<br>(z)</th>
<? if ($show_covariates) { ?>
		<th class="cov-col">Covariate</th>
		<th class="cov-col">Linear</th>
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
		echo "<td class='cov-col'><label class='cov-linear-label'><input type='checkbox' name='cov_linear[]' value='$safe' class='cov-linear' disabled> linear</label></td>";
	}
	echo "</tr>\n";
}
?>
</table>
<BR>
<input type="submit" name="submit" value="Run" class="btn btn-primary">
</form>
</div>

<? if ($show_covariates) { ?>
<script>
(function () {
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
				linear.disabled = !cov.checked;
			}
		}
	}

	function updateAllRowCovStates() {
		document.querySelectorAll(".var-row").forEach(updateRowCovState);
	}

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
		document.getElementById("varTable").classList.add("cov-visible");
		this.style.display = "none";
		updateAllRowCovStates();
	});

	updateAllRowCovStates();
})();
</script>
<? } ?>

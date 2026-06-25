<?
session_start();
error_reporting(E_ALL);
?>
<head>
  <title>Johnson-Neyman 2.0: Online App for Nonlinear Probing of Interactions</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<style>
.jumbotron h1 { font-size: 26px; line-height: 1.35; font-weight: 600; }
.var-table { width: auto; }
.var-table th, .var-table td { white-space: nowrap; padding: 6px 12px; text-align: center; }
.var-table label { display: block; margin: 0; cursor: pointer; font-weight: normal; }
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
?>

<div class="jumbotron text-center">
	<h1>Johnson-Neyman 2.0: Online App for Nonlinear Probing of Interactions</h1>
</div>

<div class="container">
<font size='4'>Select one variable for each role, then click Run.<BR><BR></font>

<form method="post" action="run.php">
<table class="table table-striped var-table">
	<tr>
		<th>Variable</th>
		<th>Dependent (y)</th>
		<th>Focal predictor (x)</th>
		<th>Moderator (z)</th>
	</tr>
<?
foreach ($variables as $var) {
	$safe = htmlspecialchars($var, ENT_QUOTES, 'UTF-8');
	echo "<tr>";
	echo "<td>$safe</td>";
	echo "<td><label><input type='radio' name='y' value='$safe'></label></td>";
	echo "<td><label><input type='radio' name='x' value='$safe'></label></td>";
	echo "<td><label><input type='radio' name='z' value='$safe'></label></td>";
	echo "</tr>\n";
}
?>
</table>
<BR>
<input type="submit" name="submit" value="Run" class="btn btn-primary">
</form>
</div>

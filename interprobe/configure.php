<?
session_start();
$version=file_get_contents("version.txt");
error_reporting(E_ALL);
?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
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
$rout_file = $dir.$time."_vars.Rout";

file_put_contents($dir.$time."_vars", $rcode);
chdir($dir);
exec("/usr/bin/R --no-save CMD BATCH ".$time."_vars");

if (!file_exists($vars_file)) {
	$rout_text = file_exists($rout_file) ? file_get_contents($rout_file) : "R log file not found: ".$rout_file;
	die(
		"<div class='container'><font size='5'>Sorry, your file did not upload, or the app could not read it. The app relies on the RIO package in R and can read .csv, .xlsx, .sav, among ".
		"many other formats. See <a href='https://cran.r-project.org/web/packages/rio/vignettes/rio.html'>supported formats</a>.<BR><BR>".
		"<h3>R batch log</h3>".
		"<textarea readonly rows='20' style='width:100%; font-family:monospace;'>".htmlspecialchars($rout_text, ENT_QUOTES, 'UTF-8')."</textarea>".
		"<BR><BR>If you think something is broken, please let Uri know (urisohn@gmail.com)</font></div>"
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
	<h1>Interprobe</h1>
	<h2>(version <?echo $version;?>)</h2>
</div>

<div class="container">
<font size='4'>Select one variable for each role, then click Run.<BR><BR></font>

<form method="post" action="run.php">
<table class="table table-striped">
	<tr>
		<th><center>Variable</center></th>
		<th><center>Dependent (y)</center></th>
		<th><center>Focal predictor (x)</center></th>
		<th><center>Moderator (z)</center></th>
	</tr>
<?
foreach ($variables as $var) {
	$safe = htmlspecialchars($var, ENT_QUOTES, 'UTF-8');
	echo "<tr>";
	echo "<td><center>$safe</center></td>";
	echo "<td><center><input type='radio' name='y' value='$safe'></center></td>";
	echo "<td><center><input type='radio' name='x' value='$safe'></center></td>";
	echo "<td><center><input type='radio' name='z' value='$safe'></center></td>";
	echo "</tr>\n";
}
?>
</table>
<BR>
<input type="submit" name="submit" value="Run" class="btn btn-primary">
</form>
</div>

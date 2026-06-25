<?
session_start();
$version=file_get_contents("version.txt");
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<head>
  <title>Interprobe v<?echo $version;?> - Results</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
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

function die_alert($msg) {
	die("<div class='container'><BR><div class='alert alert-danger'>$msg</div></div></body></html>");
}

function die_run_error($msg, $dir, $time) {
	$rout_file = $dir.$time."_interprobe.Rout";
	$console_file = $dir."console_".$time.".txt";
	$rout_text = "";
	$console_text = "";

	if (file_exists($rout_file)) {
		$rout_text = file_get_contents($rout_file);
	} else {
		$rout_text = "R log file not found: ".$rout_file;
	}
	if (file_exists($console_file)) {
		$console_text = file_get_contents($console_file);
	}

	$rout_html = htmlspecialchars($rout_text, ENT_QUOTES, 'UTF-8');
	$console_html = htmlspecialchars($console_text, ENT_QUOTES, 'UTF-8');
	$console_block = "";
	if ($console_text !== "") {
		$console_block = "<h3>Console output</h3><textarea readonly rows='12' style='width:100%; font-family:monospace;'>".$console_html."</textarea><BR><BR>";
	}

	die(
		"<div class='jumbotron text-center'><h1>Interprobe</h1></div>".
		"<div class='container'>".
		"<div class='alert alert-danger'>$msg</div>".
		$console_block.
		"<h3>R batch log</h3>".
		"<textarea readonly rows='20' style='width:100%; font-family:monospace;'>".$rout_html."</textarea>".
		"<BR><BR><a href='configure.php' class='btn btn-default'>Go back</a>".
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

$y_r = addslashes($y);
$x_r = addslashes($x);
$z_r = addslashes($z);

$png_file = $time.".png";
$console_file = $dir."console_".$time.".txt";
$data_path = $dir_data.$file;
$png_path = $dir.$png_file;
$rout_file = $dir.$time."_interprobe.Rout";
$batch_script = $dir.$time."_interprobe";

$rcode = <<<RCODE
	.libPaths("/usr/local/R/library")
	library(rio)
	library(statuser)

	setwd("$dir")
	data.imported=import("$data_path")
	if (!exists("data.imported")) stop("Sorry, R was unable to read the file $file")

	sink("$console_file")
	sink(type="message", split=TRUE)
	interprobe(
		x="$x_r",
		z="$z_r",
		y="$y_r",
		data=data.imported,
		save.as="$png_path"
	)
	sink()
	sink(type="message")
RCODE;

file_put_contents($batch_script, $rcode);

if (file_exists($png_path)) unlink($png_path);
if (file_exists($console_file)) unlink($console_file);
if (file_exists($rout_file)) unlink($rout_file);

chdir($dir);
exec("/usr/bin/R --no-save CMD BATCH ".$time."_interprobe 2>&1", $exec_output, $exec_code);

if (!file_exists($png_path)) {
	die_run_error(
		"Something went wrong running interprobe on your data. Please check your variable selection and try again. ".
		"Email <a href='mailto:urisohn@gmail.com'>Uri</a> if you think something is broken.",
		$dir,
		$time
	);
}

$console_text = "";
if (file_exists($console_file)) {
	$console_text = file_get_contents($console_file);
}
?>

<div class="jumbotron text-center">
  <h1>Interprobe</h1>
  <h2>(version <?echo $version;?>)</h2>
</div>

<div class="container">
	<h2><b>Results</b></h2>
	<font size='4'>
		Probing the interaction of <b><?echo htmlspecialchars($x);?></b> &times;
		<b><?echo htmlspecialchars($z);?></b> on <b><?echo htmlspecialchars($y);?></b>.<BR><BR>
		<img src="temp/<?echo $png_file;?>" width="1000"><BR><BR>
	</font>

	<h2><b>Console output</b></h2>
	<textarea readonly rows="20" style="width:100%; font-family:monospace;"><?echo htmlspecialchars($console_text);?></textarea>
	<BR><BR>
	<h2><b>R batch log</b></h2>
	<textarea readonly rows="20" style="width:100%; font-family:monospace;"><?
	if (file_exists($dir.$time."_interprobe.Rout")) {
		echo htmlspecialchars(file_get_contents($dir.$time."_interprobe.Rout"));
	}
	?></textarea>
</div>

<?
if (file_exists(".RData")) unlink(".RData");
?>
</body>
</html>

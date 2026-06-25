<?
	$version=file_get_contents("version.txt");
?>

<head>
  <title>Interprobe v<?echo $version;?></title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<body>

<div class="jumbotron text-center">
  <h1>Interprobe</h1>
  <h2>(version <?echo $version;?>)</h2>
</div>

<div class="container">
<font size='5'>
This app probes and visualizes interactions using <code>statuser::interprobe()</code>
(Simonsohn 2024). It estimates a GAM predicting the dependent variable from the focal
predictor and moderator, then plots simple slopes (spotlights) and Johnson-Neyman curves.<BR><BR>
</font>

<div class="row">
	<div class="col-sm-6">
		<font size='4'>
		Upload a dataset, select three variables (outcome, focal predictor, moderator),
		and click Run to see the combined figure and console output.
		</font>
	</div>
	<div class="col-sm-6">
		<form action="upload.php" method="post" enctype="multipart/form-data">
			<font size='4'><BR>Upload your data file:<BR>
			<input type="file" name="fileToUpload" id="fileToUpload">
			<input type="submit" value="Upload" name="Submit" class="btn btn-success"><BR>
			<font size='3'>No file handy? Download this <a href="example.csv">example datafile</a> and upload it.</font>
			<div class="alert alert-danger">
				<h4 class="alert-heading">Data privacy information.</h4>
				<font size='2'>
				Uploaded data is deleted within 72 hours. Files are saved unencrypted in a public
				folder but given a temporary name, so they are hard to find but not impossible to locate.
				For confidential data, run the analysis locally in R instead of uploading here.
				</font>
			</div>
		</form>
	</div>
</div>

<?
	$dir1 = "/home/urisoh5/uploaded_data/webstimate.org/interprobe/temp/";
	foreach (glob($dir1."*") as $file) {
		if (filemtime($file) < time() - 24*3*60) {
			unlink($file);
		}
	}

	$dir2 = "./temp/";
	foreach (glob($dir2."*") as $file) {
		if (filemtime($file) < time() - 60*3*24) {
			unlink($file);
		}
	}
?>
<hr><font size='1' color='gray'><center>Thanks for using Interprobe</center>
</body>
</html>

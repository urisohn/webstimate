<head>
  <title>Interprobe</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<body>

<div class="jumbotron text-center">
  <h1>Interprobe</h1>
</div>

<div class="container">
<div class="row">
	<div class="col-sm-6">
		<font size='4'>
		This online app allows you to run GAM probing of interactions, computing GAM Simple Slopes and GAM Johnson-Neyman.<BR><BR>

		To proceed you:<BR>
		1) upload the data,<BR>
		2) Select a focal predictor, moderator, and dependent variable from the list of variables in it<BR>
		3) Click "Run" button<BR>
		4) You get publication ready figures with the probing<BR><BR>

		The server runs the function <code>interprobe</code> from the R package <code>statuser</code>.<BR>
		The results you get here will be identical to those you would obtain using R (with the same version of all software involved).<BR><BR>

		For a tutorial see<BR>
		Montealegre &amp; Simonsohn (2026) "Johnson-Neyman 2.0", under review, <i>Journal of Consumer Research</i><BR><BR>

		For background see<BR>
		Simonsohn (2024) "Interacting with Curves", <i>Advances in Methods and Practices in Psychological Science</i>.
		<a href="https://doi.org/10.1177/25152459231207787">https://doi.org/10.1177/25152459231207787</a>
		</font>
	</div>
	<div class="col-sm-6">
		<form action="upload.php" method="post" enctype="multipart/form-data">
			<font size='4'><BR>Upload your data file:<BR>
			<input type="file" name="fileToUpload" id="fileToUpload">
			<input type="submit" value="Upload" name="Submit" class="btn btn-success"><BR>
			<font size='3'>No file handy? Download this <a href="example.csv">example datafile</a> and upload it.</font>
		</form>
	</div>
</div>

<div class="row" style="margin-top: 40px;">
	<div class="col-sm-8 col-sm-offset-2">
		<div class="alert alert-danger text-center">
			<h4 class="alert-heading">Data privacy information.</h4>
			<font size='2'>
			Uploaded data is deleted within 72 hours. Files are saved unencrypted in a public
			folder but given a temporary name, so they are hard to find but not impossible to locate.
			For confidential data, run the analysis locally in R instead of uploading here.
			</font>
		</div>
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

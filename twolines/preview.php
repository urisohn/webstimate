<?
		session_start();
	#Get version
		$version=file_get_contents("version.txt");

// Notificar todos los errores de PHP (ver el registro de cambios)
error_reporting(E_ALL);
?><link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"><?

#Set file name and folder
	$file     = $_SESSION['file'];
	$dir	  = $_SESSION['dir'];
	$dir_data = $_SESSION['dir_data'];
	$time     = $_SESSION['time'];
	$extension= $_SESSION['extension'];


#Verify file exists
	if (!file_exists($dir_data.$file)) die ("Sorry, for an unknown reason the file $file was not uploaded. Please let me, Uri Simonsohn, know if you think somethign is broken (urisohn@gmail.com)");
		
#Run the rcode
	$rcode = <<<RCODE
	#Welcome to R syntax
		source("/home/urisoh5/public_html/webstimate.org/twolines/preview.r")
		preview("$time","$extension")
	#end R Syntax;
RCODE;
	

//Save  the R code as a text file
	file_put_contents($dir.$time, $rcode);
//Execute it
	chdir($dir); 
	exec("/usr/bin/R --no-save CMD BATCH $time ");

	
//Verify R Code executed (here i check if the preview file was generated, if not, die
	#$calc_txt = file($tmp."Calculations_".$time.".txt");
	#if ($calc_txt=="") die ("Sorry. We cannort report <i>p</i>-curve results because R generated an error while processing the tests you submitted. <BR>Please check your input, common errors include submitting tests that are all p>.05, and impossible degrees of freedom (e.g., F(0,100)=4.41).");

//Now read the preview table and show it.
	
	echo ("<title>Two-Lines App $version</title>");
	?>
	<div class="jumbotron text-center">
		<h1>Two-lines test</h1>
		<?echo ("<title>Version $version</title>");?>
	</div>
	
	<div class='container'>
	<?
	#Change directory
		chdir($dir_data); 
	#See if preview file was created, which means R can read it, if not, die()
		if (!file_exists("preview_$file")) die ("<font size='5'>Sorry, your file did not upload, perhaps you used a format the app does not understand. The app relies on the RIO package in R and can read .csv, .xlsx, .sav, among ".
		 "many other formats, but cannot read everything. You can check out <a href='https://cran.r-project.org/web/packages/rio/vignettes/rio.html'>here</a> if your format is supported.<BR><BR>If you think there is a problem ".
		 "with the app, please let Uri know (urisohn@gmail.com)") ;
    
	#If we got this far, the file worked, show it ?>
	
	<font size='4'>These are the first 5 rows of data you uploaded, if they look ok, proceed:<BR>
	<table class='table table-striped'>
	<?
	
	if (!file_exists("preview_$file")) die ("Sorry, an unknown reason the file preview_$file could not be loaded");
	$f = fopen("preview_$file", "r");
	while (($line = fgetcsv($f)) !== false) {
        echo "<tr>";
        foreach ($line as $cell) {
        echo "<td><center>" . htmlspecialchars($cell) . "</td></center>";
       }
     echo "</tr>\n";
	}
	fclose($f);
	echo "\n</table>";
	?>
	<BR>
	<div class='row'>
	<div class='col-sm-6'>
		<h2>Instructions</h2>
			To test if x has u-shaped effect on y, run: y~x<BR>
			To control for x2 linearly, run: y~x+x2<BR>
			To test if x2  has a u-shaped effect on y controlling for x, run: y~x2+x<BR>
			To control for x1*x2 interaction: run: y~x+x1*x2<BR>
			<font size='3'>(so: the first predictor is tested for u-shapedness)</font>
		</div>
		
		<div class='col-sm-6'>
			<form method='post' action='run.php'>
				<BR><BR>Enter the regression model you would like to run<BR>
					<input type="text" size='45' name='f' value="y~x1+x2">
					<input type='submit' name='submit' value='Run' class='btn btn-primary btn-sm'>
				</form>
		</div>
		
		</div>
				
			
		
	
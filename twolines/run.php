<?
session_start();
	#Get version
		$version=file_get_contents("version.txt");


?><head>
  <title>Two lines v<?echo $version;?> - Results</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
 <head>
<body>
<?
	// Notificar todos los errores de PHP (ver el registro de cambios)
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
	?><link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"><?
	
 #Set file name and folder
	$file     = $_SESSION['file'];
	$dir	  = $_SESSION['dir'];
	$dir_data  =$_SESSION['dir_data'];
	$time     = $_SESSION['time'];
	$extension= $_SESSION['extension'];
	$f        = $_POST['f'];
	
#Run the rcode
	$rcode = <<<RCODE
	#Welcome to R syntax
		#Load the libraries and functions
			library(rio,lib.loc="/usr/lib64/R/library" ) #library to convert and accept multiple format
			library(readr,lib.loc="/usr/lib64/R/library" ) #library to convert and accept multiple format
		    source("/home/urisoh5/public_html/webstimate.org/twolines/twolines.R")
	
	#Set working directory, temp folder
			setwd('/home/urisoh5/public_html/webstimate.org/twolines/temp/')
		#Read it
			data.imported=import("/home/urisoh5/uploaded_data/webstimate.org/twolines/temp/$file")
		#die() if file was not loaded in R
			if(!exists("data.imported")) stop("Sorry, R was unable to read the file $file")
			#attach(a)
			
		#Run two lines
			r=twolines($f,pngfile="$time.png",data=data.imported)  #f is the model entered by the user
		#Save results into a .csv to read below
			a=r\$bx1
			b=r\$bx2
			slope0=a+2*b*r\$minx
			ymost=r\$y.most
			xmost=r\$x.most
			midflat=r\$midflat
			midz1=r\$midz1
			midz2=r\$midz2
  		    ratio=midz1/(midz1+midz2)
			minx=r\$minx
		    xc=r\$xc
		    calc  =as.data.frame(t(c(a,b,minx,slope0,xmost,ymost,midflat,midz1,midz2,ratio,xc)))
			colnames(calc)=c("a","b","minx","slope0","xmost","ymost","midflat","midz1","midz2","ratio","xc")  
      		write.csv(calc,"calc_$time.csv")
		#Save glm1 and glm2 results	
			sink("glm_$time.txt")  ## switch standard output to a file
				cat("RESULT 1: First interrupted regression, we obtain coefficient for xlow1 from here (blue line):\n\n")
					summary(r\$glm1)
				cat("Note:  The standard errors above are homoskedasticity assuming, the two-lines test",
					" uses  heteroskedastic robust ('HC1'). Those are reported below (with z rather than t tests)\n")
					r\$rob1
				cat("==========================================================================================")
				cat("\n\nRESULT 2: Second interrupted regression, we obtain coefficient for xhigh2 from here (red line):\n\n")
					summary(r\$glm2)
				cat("Now, heteroskedastic robust:\n")
					r\$rob2
				cat("==========================================================================================")
			sink()    
			
		#end R Syntax;
RCODE;

	
//PENDING, VALIDATE OUTPUT AND GIVE ERROR MESSAGE IF SOMETHIGN GOES WRONG

//Save  the R code as a text file
	file_put_contents($dir.$time."_twolines", $rcode);

//Change directory
	chdir($dir); 

//If results files already exist (timestamp was already used to generate other charts), delete them
	$calc_file_name="calc_$time.csv";
	if (file_exists($calc_file_name)) unlink($calc_file_name);	

//Execute it
	exec("/usr/bin/R  --no-save CMD BATCH ".$time."_twolines");
	

//Read the calculations into an array called calc[] 
	    $calc_file_name="calc_$time.csv";
		if (!file_exists($calc_file_name)) die ("<div class='container'><BR><div class='alert alert-danger'>Something went wrong, R was unable to run the model you entered.".
								" Please go back and check your syntax.".
								"<BR>Please email <a href='mailto:urisohn@gmail.com'>Uri</a> if you think something is wrong.</div></div>");
			$file=fopen($calc_file_name,"r");
			$calc = array();
			$header = null;
			while ($row = fgetcsv($file)) {
				if ($header === null) {
					$header = $row;
					continue;
				}
			$calc = array_combine($header, $row);
			}
			
		#Calc: a, b, slope0, xmost ymost, midflat midz1 midz2 ratio xc
		#print_r($calc);
		
		
#REport results ?>
	<head>
		<title>Two lines: results v<?echo $version?>;</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	</head>
	<body>

<div class="jumbotron text-center">
  <h1>Two-lines test</h1>
   <h2>(version <?echo $version;?>)</h2>
</div>

	
	<div class='container'>
		<h2><b>Results.</h2></b>
		<font size='4'>
			You just run the model <?echo $f?> on the data you uploaded, testing whether the effect of the first predictor is u-shaped (or inverted u-shaped) on the dependent variable.
			The results are summarized in this figure:<BR><BR>
				<?echo('<img src="temp/'.$time.'.png" width="800">');?><BR><BR>
				The R Code used to run the test is available <a href='twolines.R'>here</a>.
		<HR>
	
		<h2><b>Calculations step by step</h2></b>
			Here we explain in detail how the two-lines test was run<BR><BR>
			We will refer to the predictor hypothesized to have a u-shaped effect as x, and the dependent variable as y.<BR><BR>
			To test if the effect of x is u-shaped (or inverted u-shaped) on y, the two-lines test procedure did the following (covariates are not mentioned below, but if you included them, they were used):<BR>
			<ol>
				<li>Run a quadratic regression of the form y=ax+bx<sup>2</sup></li>
				<li>The results were a=<?echo (round($calc['a'],3));?> and b=<?echo (round($calc['b'],3));?>. With these values one gets the implied slope (a+2bx)
					at the lowest observed value of x (x<sub>min</sub>=<?echo (round($calc['minx'],3));?>). If that slope is negative at that point, the two-lines test considers a u-shape, if 
					it is positive, an inverted u-shape. Here the quadratic implies the slope of <?echo (round($calc['slope0'],3));?> at the lowest x value of x=<?echo (round($calc['minx'],3));?>, 
					<? if ($calc['slope0']<0) echo (" negative, thus it tested for a u-shape.");
					   if ($calc['slope0']>0) echo (" positive, thus it tested for an <i>inverted</i> u-shape.");?>
				<li>Estimated a spline (smoothed scatterplot) model, y=f(x). See gray dashed line in chart above. Note that if you included covariates in
					the model you typed in, <?echo $f?>, these were included when estimating that spline also.</li>
				<li>Among the middle 80% of x-values (between 10th and 90th percentile), the most extreme fitted y-value was identified: <i>y<sub>max</sub>=<?echo (round($calc['ymost'],3));?></i>, 
					which corresponds to x=<?echo (round($calc['xmost'],3));?>.</li>
				<li>All x-values associated with a fitted y within a standard error of y<sub>max</sub> were identified: <i>x<sub>flat</sub>.</i></li> 
				<li>The median x-value in x<sub>flat</sub> was identified, <?echo (round($calc['midflat'],3));?></li>
				<li>A interrupted regression was estimated with that midpoint value as the breakpoint (with heteroskedasticity-robust standard errors (using 'HC3' by default, switching to 'HC1' if a NA is produced))</li>
				<li>The resulting z-values (b/se) for the two slopes were z<sub>1</sub>=<?echo (round($calc['midz1'],3));?> and z<sub>2</sub>=<?echo (round($calc['midz2'],3));?>.</li>
				<li>Using these zs we compute the following ratio, z<sub>1</sub>/(z<sub>1</sub>+z<sub>2</sub>)=<?echo (round($calc['ratio'],3));?>, which is the percentile of the x-value within 
				   <i>x<sub>flat</sub></i> used as the breakpoint for the final interrupted regression, whose results are depicted in the figure above, xc=<?echo (round($calc['xc'],3));?>.
			</ol>
				<h2><b>Additional results</h2></b>
		    After setting a breakpoint, the two-lines test runs two interrupted regressions, one which includes the breakpoint in the first segment, then one which includes it in the second. 
			This is done to increase power when the predictor hypothesized to have a u-shaped effect is discrete. The first (blue) line depicted in the figure is the first line in the first 
			interrupted regression, the second (red) line is the second line in the second interrupted regression.<BR>
			You can see the full results for these two regressions <a href="<? echo ("temp/glm_".$time.".txt");?>">here</a>. Note that if you have a continuous predictor both regressions could be 
			identical.
			<hr>
		
	
	
<?php
	#delete .RData
	unlink(".RData");
	
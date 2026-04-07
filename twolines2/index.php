<?
	#Get version
		$version=file_get_contents("version.txt");
?>

<style>
.mycontent-left {
  border-right: 1px solid dodgerblue;
}


@media ( min-width: 768px ) {
    .grid-divider {
        position: relative;
        padding: 0;
    }
    .grid-divider>[class*='col-'] {
        position: static;
    }
    .grid-divider>[class*='col-']:nth-child(n+2):before {
        content: "";
        border-left: 1px solid #DDD;
        position: absolute;
        top: 0;
        bottom: 0;
    }
    .col-padding {
        padding: 0 15px;
    }
}
</style>

	


<head>
  <title>Two lines v<?echo $version;?></title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
 <head>
<body>

<div class="jumbotron text-center">
  <h1>Two-lines test</h1>
   <h2>(version <?echo $version;?> - beta testing)</h2> 
</div>
  
  
<div class="container">
<?# <center><div class="alert alert-danger"><font size='8'>CURRENTLY (2017 11 28) UPDATING, PLEASE DO NOT USE</FONT><br></div></center>?>
<font size='3'><font color='gray'>Last updated: 2018 04 20 (see <a href='changes.php'>how the app has changed</a> over time</font>)</font><BR><BR>

		
<font size='5'>
This app runs the u-shape test introduced by Simonsohn (2017 <a href="https://papers.ssrn.com/sol3/papers.cfm?abstract_id=3021690">.pdf</a>).<BR>

<font size='4'>
		In particular, it estimates an interrupted regression, that is, a regression with two separate slopes, for the 
		predictor hypothesized to have a u-shaped effect. The breakpoint is set using the "Robin Hood" algorithm, 
		seeking to obtain higher power to detect a u-shape if it is present. If the resulting two slopes have opposite sign, 
		and are individually statistically significant, the test rejects the null hypothesis that
		there is no u-shaped (nor inverted u-shaped) effect.<BR><hR></font>
</font>


<div class="row">
	<div class='col-sm-5 mycontent-left '>
	
		<BR>
		<font size='4'>If you know R you may want to: </font>
		<BR>
		<a href='example.r'><button class='btn-primary'>See example</button></a></font>
		<a href='http://webstimate.org/twolines/twolines.R'><button class='btn-success'>Download the R Code</button></a></font>
		
		
		
		</font><BR><BR><BR>
		
		
	</div>
	<div class='col-sm-1'>
	</div>
	<div class='col-sm-6'>
		
	
	
	<form action="upload.php" method="post" enctype="multipart/form-data">
    <font size='4' color='black'><BR>If you don't know R, or you're feeling lazy. Use this web app<BR> 
	
	<font size='4'><input type="file" name="fileToUpload" id="fileToUpload" >
	<input type="submit" value="Upload" name="Submit" class='btn btn-success'><BR><BR><BR>
	<font size='3'>If you don't have a file but want to check things out, download this <a href='example.csv'>datafile</a>  and then upload it.</a>
	</form>
	
	</div>
</div>





<?

	#DELETE FILES OLDER THAN 3 days
		$dir1 = "/home/urisoh5/uploaded_data/webstimate.org/twolines/temp/"; /** define the directory **/
		foreach (glob($dir1."*") as $file) 			 {   /*** cycle through all files in the directory ***/
			if (filemtime($file) < time() - 24*3*60) { /*** if file is OLDER THAN A WEEK delete it ***/
			unlink($file);
			} #End if()
			} #End for each() loop
		
		$dir2 = "./temp/"; /** define the directory **/
		foreach (glob($dir2."*") as $file) 			 {   /*** cycle through all files in the directory ***/
			if (filemtime($file) < time() - 24*3*60) { /*** if file is OLDER THAN A WEEK delete it ***/
			unlink($file);
			} #End if()
			} #End for each() loop
		

?>
<hr><font size='1' color='gray'><center>Thanks for using the two-lines test</center>
</body>
</html>

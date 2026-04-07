<?
	#Get version
		$version=file_get_contents("version.txt");
?>



<head>
  <title>Two lines v<?echo $versison;?></title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
 <head>
<body>

<div class="jumbotron text-center">
  <h1>Two-lines test</h1>
   <h2>(changes to the app)</h2> 
</div>
  
  
<div class="container">
<?# <center><div class="alert alert-danger"><font size='8'>CURRENTLY (2017 11 28) UPDATING, PLEASE DO NOT USE</FONT><br></div></center>?>

<font size='5'>
<center><B>How has the app changed?</B></center>
<BR><BR>
<font color='gray' size='4'>
	v0.34 - 2018 04 20<BR>
	<ul>
		<li>Extended previous fix to models with covariates</li></ul>
	<BR>

	v0.33 - 2018 04 02<BR>
	<ul>
		<li>Fixed bug that would lead the code to crash if the dependent variable was not named "y" (!).<BR>
		Thanks to Joseph Reiff (UCLA PhD student) for identifying the bug and the line that was gneerating it</ul>
	<BR>
	v0.32 - 2018 03 20<BR>
	<ul>
		<li>Instead of relying always on HC1 for hetorskedasticity correction, goes back to 'HC3' by default and switches to HC1 only if a NA value is generated.</li>
	</ul>
	<BR>
	v0.31 - 2018 03 20<BR>
	<ul>
	<li>It was realized that using HC3 to estimate heteroskedasticity robust standard errors  creates NA 
		values under some circumstances leading the app to crash. For a few hours the app was swtiched to relying on HC1 instead HC3. But see App 0.32.<BR>
	<font size='2'>Special thanks to Nathan Carter for sharing with me the data that were leading the app to crash, allowing me to identify the bug.</font></li>
	<li>Fixed bug counting number of unique x values (used to prevent the mgcv:gam() function from crashing when x has few possible values)</li>
	</ul>
	<BR>
	v0.3 - 2018 01 24<BR>
	<ul>
	<li>Rewrote most of code to simplify syntax. Intead of u(x) to indicate the variable that is being tested for u-shapedness, now always the first variable is.</li>
	<li>Moved from ShinyApp to standalone PHP server, running R in the background (this app is no longer a ShinyApp)</li>
	<li>The interrupted regression now computes heteroskedasticity robust standard errors, addressing concerns shared via Andrew Gelman by Yair Heller.
	For more information read  <u><a href='http://datacolada.org/62#pd_2017_11_02'>post-scriptum</a></u> to the blogpost DataColada[62], or go straight to the
	<u><a href='http://datacolada.org/wp-content/uploads/2017/09/2017-11-03-u-shape-discussion-with-Yair-Heller-1.r'>R Code</a></U> discussing the problem and solution.</li>
	</ul>
	
	 <BR><BR>
	v0.1 - 2017 ?? ??<BR>
	<ul>
	<li>Details lost to history</li>
	</ul>
	 
	
	
</font>
	
<?php
#PHP error reporting
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
exec("/usr/bin/Rscript '/home/urisoh5/public_html/webstimate.org/twolines/test.r'");
echo ("got here");


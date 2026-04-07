



	#library(rio,lib.loc="/home/urisoh5/R/lib" ) #library to convert and accept multiple format
	#library(readr,lib.loc="/home/urisoh5/R/lib" ) #library to convert and accept multiple format
	
	
	library(rio,lib.loc="/usr/lib64/R/library" ) #library to convert and accept multiple format
	library(readr,lib.loc="/usr/lib64/R/library" ) #library to convert and accept multiple format
	

	preview=function(time1,extension1) {
	#time: is the identifier for teh file that was uploaded, seconds since 1/1/1970
	#extension is whethe it is .csv, .txt, etc.
	
	#Set working directory, temp folder
		setwd('/home/urisoh5/uploaded_data/webstimate.org/twolines/temp/')
	#Generate file1: name of file to read
		file1=paste0(time1,".",extension1);
		file1
	#Read it
		#a=read.table(file1)
		a=import(file1)
		a
		
	#Set name of preview file
		preview_file=paste0("preview_",file1)
		preview_file
	#Save first 5 rows to be read by .php
		write.csv(a[1:5,],preview_file)
	}

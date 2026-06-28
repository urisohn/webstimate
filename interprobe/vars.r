
	.libPaths("/usr/local/R/library")
	library(rio)

	vars=function(time1, extension1) {
		setwd('/home/urisoh5/uploaded_data/webstimate.org/interprobe/temp/')
		file1=paste0(time1, ".", extension1)
		a=import(file1)
		vars_file=paste0("vars_", file1)
		write.csv(
			data.frame(
				variable=names(a),
				nux=sapply(a, function(v) length(unique(v)))
			),
			vars_file,
			row.names=FALSE
		)
	}

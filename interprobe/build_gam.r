build_interprobe_gam <- function(y, x, z, data, covs = character(), cov_linear = logical(), k = 10) {
	nux <- length(unique(data[[x]]))
	if (nux == 2) {
		base <- paste(y, "~", x, "+ s(", z, ",k=", k, ") + ti(", z, ",by=", x, ",k=", k, ")", sep = "")
	} else {
		base <- paste(y, "~ s(", x, ",k=", k, ") + s(", z, ",k=", k, ") + ti(", x, ",", z, ",k=", k, ")", sep = "")
	}
	cov_terms <- character()
	if (length(covs) > 0) {
		if (length(cov_linear) == 0) {
			cov_linear <- rep(FALSE, length(covs))
		}
		for (i in seq_along(covs)) {
			if (isTRUE(cov_linear[i])) {
				cov_terms <- c(cov_terms, covs[i])
			} else {
				cov_terms <- c(cov_terms, paste0("s(", covs[i], ",k=", k, ")"))
			}
		}
	}
	rhs <- sub("^[^~]+~\\s*", "", base)
	if (length(cov_terms) > 0) {
		rhs <- paste(c(rhs, cov_terms), collapse = " + ")
	}
	fo <- as.formula(paste(y, "~", rhs))
	mgcv::gam(fo, data = data, method = "REML")
}

prepare_interprobe_linear_data <- function(data, y, x, z, covs = character()) {
	vars <- unique(c(y, x, z, covs))
	for (v in vars) {
		col <- data[[v]]
		if (is.factor(col) || is.character(col)) {
			num <- suppressWarnings(as.numeric(as.character(col)))
			if (!any(is.na(num))) {
				data[[v]] <- num
			}
		}
	}
	data
}

build_interprobe_linear <- function(y, x, z, data, covs = character()) {
	data <- prepare_interprobe_linear_data(data, y, x, z, covs)
	rhs <- paste(x, "*", z)
	if (length(covs) > 0) {
		rhs <- paste(rhs, paste(covs, collapse = " + "), sep = " + ")
	}
	fo <- as.formula(paste(y, "~", rhs))
	if (requireNamespace("estimatr", quietly = TRUE)) {
		statuser::lm2(fo, data = data)
	} else {
		stats::lm(fo, data = data)
	}
}

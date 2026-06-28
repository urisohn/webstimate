#!/bin/bash
# Delete uploaded temp files older than 30 minutes.
# Preserves index.php and .gitkeep in each temp folder.

MAX_AGE_MIN=30

DIRS=(
	/home/urisoh5/uploaded_data/webstimate.org/interprobe/temp
	/home/urisoh5/public_html/webstimate.org/interprobe/temp
	/home/urisoh5/uploaded_data/webstimate.org/twolines/temp
	/home/urisoh5/public_html/webstimate.org/twolines/temp
)

for dir in "${DIRS[@]}"; do
	if [ ! -d "$dir" ]; then
		continue
	fi
	find "$dir" -maxdepth 1 -type f -mmin +"$MAX_AGE_MIN" \
		! \( -name 'index.php' -o -name '.gitkeep' \) \
		-delete
done

# senki/gct-converter
A simple command line converter utility between [Morpheus](https://software.broadinstitute.org/morpheus/) [GCT](http://software.broadinstitute.org/cancer/software/genepattern/file-formats-guide#GCT) (heatmap) and CSV file, which can be imported as a join/pivot table in any RDB.  
Tested with huge, multi-gigabyte files.

## Install
Download `gct-converter.phar` from the [latest release](https://github.com/senki/gct-converter/releases/latest) and set permission if needed `chmod +x gct-converter.phar`.

Alternatively, download/clone this repo and install dependencies: `composer install`.

## Usage

**To convert GCT to CSV:**  
`$ gct-converter.phar to:join <filename>`  
The output file is named `[original_path/file_base_name]-join.csv`

**To convert CSV to GCT:**  
`$ gct-converter.phar to:matrix <filename>`  
The output file is named `[original_path/file_base_name]-matrix.gct`

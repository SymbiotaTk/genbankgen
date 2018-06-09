<?php
function usage($name) {
	echo "USAGE: ".$name." <CSS file> <ID>";
	exit;
}

if (count($argv) != 3) {
	return usage($argv[0]);
}

$ID=$argv[2];
$IN=$argv[1];
$TMP="_".str_replace(array(".css"),array(".scss"),$IN);
$OUT="_".$IN;

$File=file_get_contents($IN);
$Content="#$ID { $File }";
file_put_contents($TMP,$Content);

$cmd = system("which sassc");
exec($cmd." $TMP $OUT");

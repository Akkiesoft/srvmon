<?php
require_once('config.php');

$json  = json_decode(file_get_contents($serverList), true);
$count = 0;
foreach ($json['servers'] as $line)
{
	if (intval($line['enabled']))
		$count++;
}

$time = time();
$out  = "{\"date\":\"" . $time . "\",\"servers\":[]}";
file_put_contents($tmpfile, $out);

foreach ($json['servers'] as $line) {
	$enabled = intval($line['enabled']);
	if (!$enabled) continue;

	$line['file']  = $tmpfile;
	// RESERVE(for verify for the tmpfile)
	// $line['time']  = $time;
	$line['count'] = $count;

	$server_json = json_encode($line);
	$server_json = "\"" . preg_replace("/\"/", "'", $server_json) . "\"";
	system(	"/usr/bin/php /var/www/html/ilomon/get_info.php " .
		$server_json .
		" > /dev/null &");
}
?>

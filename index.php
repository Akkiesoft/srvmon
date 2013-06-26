<?php
	$json = json_decode(file_get_contents('serverinfo.json'));
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>利用状況</title>
  <style>
*	{font-size:9pt;}
h1	{margin:10px 0;font-size:20pt;}
.powerOn		{background-color:lightgreen;}
.powerOff		{background-color:red;}
.powerUnknown	{background-color:gray;}
.powerFailed	{background-color:orange;}
  </style>
</head>
<body>
<h1>利用状況</h1>
<p><?php print date('Y/m/d H:i:s', intval($json->date)); ?> 現在</p>
<table border="1">
  <tr>
    <th>名前(もしくはIP)</th>
    <th>電源</th>
    <th>電力モード</th>
    <th>現在の消費電力</th>
    <th>平均消費電力</th>
    <th>最大消費電力</th>
    <th>最小消費電力</th>
<!--    <th>取得日時</th>-->
    <th>製品名</th>
    <th>BIOSバージョン</th>
    <th>管理モジュール名</th>
    <th>管理モジュールバージョン</th>
  </tr>
<?php
	$powerTotal = 0;

	foreach ($json->servers as $line) {
		if (preg_match('/\.[0-9]$/', $line->ipAddress)) {
			continue;
		}

		if ($line->enabledstate == 'enabled') {
			$power = 'On';
		} else if ($line->enabledstate == 'disabled') {
			$power = 'Off';
		} else {
			$power = 'Unknown';
			if ($line->result == 'Connection Failed') {
				$power = 'Failed';
				$line->name = '(Connection Failed)';
			} else if ($line->result == 'Authencation Failed') {
				$power = 'Failed';
				$line->name = '(Authencation Failed)';
			}
		}
//		$date = ($line->date) ? date('m/d H:i', intval($line->date)) : '';

		$fwAddress = (isset($line->fwHostName)) ? $line->fwHostName : $line->fwIPAddress;
		$presentPower = $line->presentPower;
		$averagePower = $line->averagePower;
		$maximumPower = $line->maximumPower;
		$minimumPower = $line->minimumPower;
		print <<<EOM
  <tr>
    <td><a href="http://{$line->fwIPAddress}/">{$fwAddress}</a></td>
    <td class="power{$power}">{$power}</td>
    <td>{$line->powerProfile}</td>
    <td>{$presentPower}</td>
    <td>{$averagePower}</td>
    <td>{$maximumPower}</td>
    <td>{$minimumPower}</td>
<!--    <td>{$date}</td>-->
    <td>{$line->productName}</td>
    <td>{$line->biosVersion}({$line->biosDate})</td>
    <td>{$line->fwName}</td>
    <td>{$line->fwVersion}({$line->fwDate})</td>
  </tr>
EOM;
		$powerTotal += intval(preg_replace('/ Watts*/', '', $presentPower));
	}
?>
  <tr>
    <td>合計</td>
    <td></td>
    <td></td>
    <td><?php print $powerTotal; ?> Watts</td><td></td>
  </tr>
</table>
<p><a href="serverinfo.json">生JSONデータ</a></p>
</body>
</html>

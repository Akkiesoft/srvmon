<?php
/* iLO */
function getIloInfo($ip, $user, $pass) {
	$params['fwIPAddress'] = $ip;
	
	$connection = ssh_Connect($ip, $user, $pass);
	if ($connection == 1) {
		$params['result'] = 'Connection Failed';
		return $params;
	} else if ($connection == 2) {
		$params['result'] = 'Authentication Failed';
		return $params;
	}
	$params['result'] = 'Success';
	$params['date']   = time();

	$table = array(
		'name'		=> 'fwName',
		'date'		=> 'fwDate',
		'version'	=> 'fwVersion',
		'license'	=> 'fwLicense',
		'Hostname'	=> 'fwHostName',
		'HostName'	=> 'fwHostName',
		'DomainName'	=> 'fwDomainName',
	);
	$param  = getIloData($connection, 'show /map1', $table);
		$params = array_merge($params, $param);
	$param  = getIloData($connection, 'show /map1/firmware1', $table);
		$params = array_merge($params, $param);
	$param  = getIloData($connection, 'show /map1/dnsendpt1', $table);
		$params = array_merge($params, $param);
	$table = array(
		'name'			=> 'productName',
		'product_id'		=> 'productId',
		'number'		=> 'serialNumber',
		'oemhp_PresentPower'	=> 'presentPower',
		'oemhp_AveragePower'	=> 'averagePower',
		'oemhp_AvgPower'	=> 'averagePower',
		'oemhp_MinPower'	=> 'minimumPower',
		'oemhp_MaxPower'	=> 'maximumPower',
		'enabledstate'		=> 'enabledstate',
		'oemhp_powerreg'	=> 'powerProfile'
	);
	$param = getIloData($connection, 'show /system1', $table);
	$params = array_merge($params, $param);

	if (preg_match('/iLO\ 2 Standard/', $params['fwName'])) {
		/* iLO2でoemhp_PresentPowerを取得 */
		ssh_sendCommand($connection, 'cd /system1');
		$param  = getIloData($connection, 'show oemhp_PresentPower', $table);
		$params = array_merge($params, $param);
		ssh_sendCommand($connection, 'cd /');
	}
	if (preg_match('/iLO\ 3/', $params['fwName']) ||
	    preg_match('/iLO\ 4/', $params['fwName'])    ) {
		/* iLO3/iLO4: 電源情報は別途取得する必要がある */
		/* !! Standardは未検証 !! */
		$param  = getIloData($connection, 'show /system1/oemhp_power1', $table);
		$params = array_merge($params, $param);
	}

	$table = array(
		'date'		=> 'biosDate',
		'version'	=> 'biosVersion'
	);
	$param  = getIloData($connection, 'show /system1/firmware1', $table);
	$params = array_merge($params, $param);

	return $params;
}

function getIloData($connection, $command, $table) {
	$data = ssh_sendCommand($connection, $command);
	$parsedData = iLO_parseData($data, $table);
	return $parsedData;
}

function iLO_ParseData($data, $table) {
	$data = explode("\n", $data);
	$flag = 0;
	$out = '';
	foreach($data as $line) {
		$line = preg_replace('/(\x1B\x5BD)/', '', $line);	/* HP's bug? */
		$line = trim($line);
		if ($line == '') {continue;}
		if ($line == 'Properties') {
			$flag = 1;
			continue;
		} else if ($line == "Power Monitoring commands aren't supported") {
			$line = 'oemhp_PresentPower=Not Supported';
		} else if ($line == 'Verbs' || $line == 'An iLO 2 License key is required.') {
			$flag = 0;
			break;
		}
		if ($flag) {
			$line = explode("=", $line);
			$line[0] = trim($line[0]);
			$line[1] = trim($line[1]);
			$key = (isset($table[$line[0]])) ? $table[$line[0]] : '';
			if ($key) {
				$out[$key] = $line[1];
			}
		}
	}
	return $out;
}
?>

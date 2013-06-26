<?php
/* OA */
function getOAInfo($ip, $user, $pass) {
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
	$params['enabledstate'] = 'enabled';

	$table = array(
		'Power Mode'		=> 'powerMode',
		'Dynamic Power'		=> 'dynamicPower',
		'Set Power Limit'	=> 'setPowerLimit',
		'Power Capacity'	=> 'powerCapacity',
		'Power Available'	=> 'powerAvailable',
		'Power Allocated'	=> 'powerAllocated',
		'Present Power'		=> 'presentPower',
		'Power Limit'		=> 'powerLimit',
	);
	$param  = parseOAInfo($connection, 'show power', $table);
	$params = array_merge($params, $param);

	$table = array(
		'Product Name'		=> 'productName',
		'Part Number'		=> 'productId',
		'Serial Number'		=> 'serialNumber',
		'Firmware Ver.'		=> 'fwVersion',
	);
	$param  = parseOAInfo($connection, 'show oa info', $table);
	$params = array_merge($params, $param);
	$params['fwName'] = $params['productName'];
	$fwVer = explode(" ", $params['fwVersion']);
	$params['fwDate'] = $fwVer[1] . ' ' . $fwVer[2] . ' ' . $fwVer[3];
	$params['fwVersion'] = $fwVer[0];

	$table = array(
		'Name'			=> 'fwHostName',
	);
	$param  = parseOAInfo($connection, 'show oa status', $table);
	$params = array_merge($params, $param);

	return $params;
}

function parseOAInfo($connection, $command, $table) {
	$data = ssh_sendCommand($connection, $command);
	$data = explode("\n", $data);
	foreach($data as $line) {
		$line = trim($line);
		if (!preg_match('/\:/', $line)) {continue;}
		$line = explode(":", $line);
		$line[0] = trim($line[0]);
		$line[1] = trim($line[1]);
		$key = (isset($table[$line[0]])) ? $table[$line[0]] : '';
		if ($key) {
			$out[$key] = $line[1];
		}
	}
	return $out;
}
?>

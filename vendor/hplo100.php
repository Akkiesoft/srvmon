<?php
/* LO100 */
function getLO100Info($ip, $user, $pass) {
	$params['fwIPAddress'] = $ip;
	$params['fwName'] = 'LightsOut-100';
	$params['presentPower'] = 'Not Supported';
	
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
		'oemhp_hostname' 		=> 'fwHostName',
		'oemhp_serial_number'	=> 'serialNumber',
		'license'				=> 'fwLicense',
		'fwversion'				=> 'fwVersion',
		'name'					=> 'productName'
	);
	$param  = getIloData($connection, 'show /map1', $table);
	$params = array_merge($params, $param);
	$param  = getIloData($connection, 'show /map1/firmware', $table);
	$params = array_merge($params, $param);
	$param  = getIloData($connection, 'show /map1/nic1', $table);
	$params = array_merge($params, $param);
	$param  = getIloData($connection, 'show /system1', $table);
	$params = array_merge($params, $param);

	$table = array(
		'oemhp_reading' => 'enabledstate'
	);
	$param = getIloData($connection, 'show /system1/oemhp_sensors/oemhp_sensor_num252lun2', $table);
	if ($param['enabledstate'] == '1') {
		$param['enabledstate'] = 'enabled';
	}
	else if ($param['enabledstate'] == '32') {
		$param['enabledstate'] = 'disabled';
	}
	else {
		$param['enabledstate'] = 'unknown';
	}
	$params = array_merge($params, $param);

	return $params;
}
?>

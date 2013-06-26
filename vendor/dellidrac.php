<?php
function getIdracInfo($ip, $user, $pass) {
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

	$table  = array( 'Reservation' => 'presentPower' );
	$param  = getIdracData($connection, 'show /system1/sp1/pwrutilmgtsvc1/pwrcurr1', $table);
	$param['presentPower'] .= ' Watts';
	$params = array_merge($params, $param);
	if (0 < $params['presentPower']) {
		$params['enabledstate'] = 'enabled';
	}

	$table  = array( 'MetricValue' => 'averagePower' );
	$param  = getIdracData($connection, 'show /admin1/system1/sp1/metricsvc1/amd3/amv1', $table);
	$param['averagePower'] .= ' Watts';
	$params = array_merge($params, $param);

	$table  = array( 'MetricValue' => 'maximumPower' );
	$param  = getIdracData($connection, 'show /admin1/system1/sp1/metricsvc1/amd1/amv2', $table);
	$param['maximumPower'] .= ' Watts';
	$params = array_merge($params, $param);

	$table  = array( 'MetricValue' => 'minimumPower' );
	$param  = getIdracData($connection, 'show /admin1/system1/sp1/metricsvc1/amd2/amv2', $table);
	$param['minimumPower'] .= ' Watts';
	$params = array_merge($params, $param);

	$table  = array(
		'DomainName' => 'fwDomain',
		'RequestedHostname' => 'fwHostName'
	 );
	$param  = getIdracData($connection, 'show /admin1/system1/sp1/settings1/ipsettings1/dnssettings1', $table);
	$param['fwHostName'] .= "." . $param['fwDomain'];
	unset($param['fwDomain']);
	$params = array_merge($params, $param);

	$table  = array(
		'SerialNumber'	=> 'serialNumber',
		'Model'			=> 'productName',
	);
	$param  = getIdracData($connection, 'show /hdwr1/chassis1', $table);
	$params = array_merge($params, $param);

	$params['fwName'] = 'iDRAC';

	return $params;
}

function getIdracData($connection, $command, $table) {
	$data = ssh_sendCommand($connection, $command);
	$parsedData = iDRAC_parseData($data, $table);
	return $parsedData;
}

function iDRAC_ParseData($data, $table) {
	$data = explode("\n", $data);
	$flag = 0;
	$out = array();
	foreach($data as $line) {
		$line = trim($line);
		if ($line == '') {continue;}
		if ($line == 'properties') {
			$flag = 1;
			continue;
		} else if ($line == 'associations') {
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

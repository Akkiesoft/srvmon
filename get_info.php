<?php

/*  設定ファイル読む  */
require_once('config.php');

/* ------------------------------------------------------------------- */
/*  必要な処理モノを呼んだり定義したり                                 */
/* ------------------------------------------------------------------- */
require_once('vendor/hpilo.php');
require_once('vendor/hplo100.php');
require_once('vendor/hpoa.php');
require_once('vendor/dellidrac.php');

function ssh_Connect($ip, $user, $pass)
{
global $data;
	$connection = @ssh2_connect($ip, 22);
	if (!$connection)
	{
		return 1;
	}
	if (@ssh2_auth_password($connection, $user, $pass) === FALSE)
	{
		return 2;
	}
	return $connection;
}

function ssh_sendCommand($connection, $command)
{
	$stream = ssh2_exec($connection, $command);
	stream_set_blocking($stream, true);
	$data = @stream_get_contents($stream);
	fclose($stream);
	return $data;
}
/* ------------------------------------------------------------------- */

/*  コマンドの引数解釈  */
/*  JSONのシングルクォート版を元に戻す  */
$usage = "usage: get_info.php <JSON>\n\n";
if (isset($argv[1]) && $argv[1])
{
	$json = preg_replace("/\'/", "\"", $argv[1]);
	$data = json_decode($json);
	if (!(	isset($data->type) && isset($data->ip) &&
		isset($data->username) && isset($data->password) ))
	{
		echo $usage;
		exit();
	}
}
else
{
	echo $usage;
	exit();
}

/*  サーバーの種類ごとに処理を実行  */
switch ($data->type)
{
	case 'dellidrac':
		$out = getIdracInfo($data->ip, $data->username, $data->password);
		break;
	case 'hpoa':
		$out = getOAInfo($data->ip, $data->username, $data->password);
		break;
	case 'hpilo':
		$out = getIloInfo($data->ip, $data->username, $data->password);
		break;
	case 'hplo100':
		$out = getLO100Info($data->ip, $data->user, $data->password);
		break;
	default:
		echo "Invalid type";
		exit();
}

/*  ファイル書き出し  */
$fp = fopen($data->file, "r+");
if (flock($fp, LOCK_EX)) {
	/*  全部読み込んでJSONをデコード  */
	$tmpjson = fread($fp, filesize($data->file));
	$tmpdata = json_decode($tmpjson, true);
	/*  デコードしたJSONに取得したデータを追記してJSONエンコード  */
	$tmpdata['servers'][] = $out;
	$tmpjson = json_encode($tmpdata);
	/*  書き出し  */
	rewind($fp);
	ftruncate($fp, 0);
	fwrite($fp, $tmpjson);
	fflush($fp);
	flock($fp, LOCK_UN);
} else {
	/*  これはひどい手抜き(適当にスリープして何回かリトライするとか？)  */
	die('hogeeeeeeeeee!!');
}
fclose($fp);

/*   自身が最後の書き込みだった時の後処理  */
if (count($tmpdata['servers']) == $data->count)
{
	/*  ソート  */
	$out = array(
		'date'    => $tmpdata['date'],
		'servers' => array()
	);
	$list = json_decode(file_get_contents($serverList));
	foreach ($list->servers as $listline)
	{
		if (!$listline->enabled) { continue; }
		foreach ($tmpdata['servers'] as $cnt=>$dataline)
		{
			if ($listline->ip == $dataline['fwIPAddress'])
			{
				$out['servers'][] = $dataline;
				unset($tmpdata['servers'][$cnt]);
				break;
			}
		}
	}

	/*  正式なファイルに出力、一時ファイルを削除  */
	file_put_contents($outpath, json_encode($out));
	unlink($data->file);
}

?>
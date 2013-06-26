<?php
/*  PHPの実行ファイルパス  */
$php_bin = '/usr/bin/php';

/*  ベースとなるディレクトリ  */
$basedir = '/var/www/html/srvmon/';

/*  サーバー一覧  */
$serverList = $basedir . 'list.json';

/*  収集中の一時ファイル名  */
$tmpfile = $basedir . 'serverinfo.json.tmp';

/*  収集されたデータのファイル名  */
$outpath = $basedir . 'serverinfo.json';
?>
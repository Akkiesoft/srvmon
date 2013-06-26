#Server monitor
##なにこれ
サーバーの管理ボードに搭載されているSMCLP (Server Management Command Line Protocol)を利用して、サーバーの情報および消費電力を収集するツールです。
複数のサーバーベンダーに対応しており、ベンダーによって異なる出力結果を仕様が統一されたJSONデータとして出力することが可能です。
###取得できるもの
管理ボードの種類によって取得可能なものは異なりますが、だいたい以下の値が取れます。
* BIOSや管理ボードのバージョン情報
* サーバーの製品・シリアル番号
* 管理ボードの各種情報
* 消費電力

###使いどこ
* サーバーの電源の状態や消費電力の把握による節電対策管理
* サーバーのシリアル番号管理
* サーバー・管理ボードのバージョン管理

###対応サーバー
以下の管理ボードに対応しています。
* HP Lights-Out 100 (ML110G6など)
* HP c-Class Onboard Administrator (c3000/c7000)
* HP Integrated Lights Out 2 / 3 (HP ProLiant G5〜G7サーバー)
* Dell iDRAC 6 (Dell PowerEdge R610など)
また、今後以下のサーバーに対応予定です。
* HP Integrated Lights Out 4 (HP ProLiant Gen8サーバー)
* Dell iDRAC 7 (Dell PowerEdge R620など)

##実行にひつようなもの
PHPが必要です。また、PECLからssh2パッケージを取得・インストールする必要があります。

##設定
以下のファイルを編集します。
* config.php: 各種ファイルのパスを指定
* list.json: 取得したいサーバーの情報を記述

##実行
crawler.phpを実行します。しばらくすると、config.phpで指定したパスに収集結果のJSONが出力されます。

##各種情報
###list.jsonのパラメータ
list.jsonには、"servers":[]の配列にサーバー情報を記述します。
```
  {
    "type":"hplo100",
    "ip":"192.168.20.51",
    "username":"admin",
    "password":"admin",
    "enabled":"1"
  }
```
* type: サーバーの種類を記述します。
```
hplo100: HP Lights-Out 100
hpilo: HP iLO 2/3
hpoa: HP c-Class Onboard Administrator
dellidrac: Dell iDRAC (iDRA6のみ対応)
```
* ip: 接続先IPアドレス
* username: 接続先ユーザー名
* password: 接続先パスワード
* enabled: 設定を有効にする場合は1。一時的に無効にしたい場合は0にします。

###取得されたデータの解説
iLO2 サーバーに対して取得をした実行結果について解説します。ベンダーによって取得できるパラメータの数は異なります。
```
    "fwIPAddress": iLO2のIPアドレス
    "result": 取得結果
    "date": 取得日時
    "fwName": 管理ボード名
    "fwLicense": iLOの場合、ライセンスキーが登録されていればライセンスキーが記入される
    "fwVersion": 管理ボードのバージョン
    "fwDate": 管理ボードのバージョンのリリース日
    "fwHostName": 管理ボードのホスト名
    "fwDomainName": 管理ボードのドメイン名
    "productName": サーバーの製品名
    "serialNumber": サーバーのシリアル番号
    "productId": サーバーの製品番号
    "enabledstate": サーバーの電源の状態(値はSMCLPの値に準じます)
    "powerProfile": サーバーのパワープロファイル
    "presentPower": 現在の消費電力
    "averagePower": 平均の消費電力
    "maximumPower": 最大時の消費電力
    "minimumPower": 最小時の消費電力
    "biosVersion": BIOSのバージョン
    "biosDate": BIOSのリリース日
```
###特定サーバーの問題に関する情報
* iLO2 日本語ファームウェアのバージョン2.12および2.15では、SSH接続時にiLO2が応答を停止することがあります。英語版を利用するか、2.20以降のファームウェアにアップデートすることで解消されます。

##ライセンス
MIT Lisenceで。

##なんかあったら
@Akkiesoft まで。
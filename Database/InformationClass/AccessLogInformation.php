<?php
/**
 * アクセスログ情報一覧クラス
 */
class AccessLogInformation {
    public $UserId;
    public $MacAddress;
    public $IpAddress;
    public $BrowserInformation;  // ブラウザ種類
    public $Url;
    public $HttpReferrer;        // アクセスしてきたURL
    public $Operation;           // アクセスした操作
    public $AccessDate;          // アクセス日
}
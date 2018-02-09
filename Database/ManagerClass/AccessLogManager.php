<?php
/**
 * アクセスログ登録クラス
 */
require_once (dirname(__FILE__) . '/DatabaseManager.php');

class AccessLogManager {
    public function RegisterAccessLog ($accessLogInformation) {
        $DatabaseManager = new DatabaseManager();
        $DatabaseManager->InsertAccessLog($accessLogInformation);
    }
}
<?php
/**
 * データベースにアクセスできるクラス
 */
require_once (dirname(__FILE__) . "/../InformationClass/DatabaseInformation.php");
require_once (dirname(__FILE__) . "/../InformationClass/LicenceInformation.php");
require_once (dirname(__FILE__) . "/../InformationClass/OptionInformation.php");

class DatabaseManager {
    private $myPdo;

    // データベース接続
    private function ConnectDatabase () {
        $DatabaseInformation = new DatabaseInformation();
        try {
            $this->myPdo = new PDO('mysql:host=' . $DatabaseInformation->HostName . ';dbname=' . $DatabaseInformation->DatabaseName  . ';charset=utf8',
                $DatabaseInformation->UserName, $DatabaseInformation->UserPassword, array(PDO::ATTR_EMULATE_PREPARES => false));
        } catch(PDOException $e) {
            print('データベース接続エラー'.$e->getMessage());
            throw $e;
        }
    }

    // データベース切断
    private function DisconnectDatabase () {
        unset($this->myPdo);
    }

    // ライセンス情報取得
    public function GetLicenceInformation ($licenceRequestInformation) {
        try {
            $this->ConnectDatabase();
            $stmt = $this->myPdo->prepare('SELECT * FROM licence LEFT OUTER JOIN product ON product.product_id = licence.product_id WHERE user_id = :userId AND user_password = :userPassword');
            $stmt->bindParam(':userId', $licenceRequestInformation->UserId, PDO::PARAM_STR);
            $stmt->bindParam(':userPassword', $licenceRequestInformation->Password, PDO::PARAM_STR);
            $stmt->execute();
            $licenceData = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($licenceData) {
                $LicenceInformation = new LicenceInformation();
                $LicenceInformation->UserId = $licenceRequestInformation->UserId;
                $LicenceInformation->Password = $licenceRequestInformation->Password;
                $LicenceInformation->ProductId = $licenceData['product_id'];
                $LicenceInformation->ProductName = $licenceData['product_name'];
                $LicenceInformation->BeginDate = $licenceData['licence_begin_date'];
                $LicenceInformation->EndDate = $licenceData['licence_end_date'];
                $LicenceInformation->MacAddress = $licenceRequestInformation->MacAddress;
                $LicenceInformation->InstallCount = $licenceRequestInformation->InstallCount;
                $stmt = $this->myPdo->prepare('SELECT COUNT(*) FROM licence_option WHERE user_id = :userId');
                $stmt->bindParam(':userId', $licenceRequestInformation->UserId, PDO::PARAM_STR);
                $stmt->execute();
                $optionCount = $stmt->fetchColumn();
                if ($optionCount != 0) {
                    $stmt = $this->myPdo->prepare('SELECT * FROM licence_option LEFT OUTER JOIN product_option ON product_option.product_option_id = licence_option.product_option_id WHERE user_id = :userId');
                    $stmt->bindParam(':userId', $licenceRequestInformation->UserId, PDO::PARAM_STR);
                    $stmt->execute();
                    $optionArray = array();
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $OptionInformation = new OptionInformation();
                        $OptionInformation->OptionId = $row['product_option_id'];
                        $OptionInformation->OptionName = $row['product_option_name'];
                        $OptionInformation->BeginDate = $row['option_begin_date'];
                        $OptionInformation->EndDate = $row['option_end_date'];
                        $optionArray[] = $OptionInformation;
                    }
                    $LicenceInformation->OptionInformation = $optionArray;
                }
                $result = $LicenceInformation;
            } else {
                $result = false;
            }
            $this->DisconnectDatabase();
            return $result;
        } catch(PDOException $e) {
            print('ライセンス情報取得エラー'.$e->getMessage());
            throw $e;
        }
    }

    public function GetLicenceCount ($licenceRequestInformation) {
        try {
            $this->connectDatabase();
            $stmt = $this->myPdo->prepare("SELECT number_of_contract_licence, number_of_current_authentication_licence FROM licence WHERE user_id = :userId");
            $stmt->bindParam(':userId', $licenceRequestInformation->UserId, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $licenceCount = array(
                "contractLicenceCount" => $result['number_of_contract_licence'],
                "currentAuthenticateLicenceCount" => $result['number_of_current_authentication_licence']
            );
            $this->disconnectDatabase();
            return $licenceCount;
        } catch (PDOException $e) {
            print('ライセンス数取得エラー' . $e->getMessage());
            throw $e;
        }
    }

    // ライセンス解除
    public function GetReleaseLicenceInformation ($licenceReleaseInformation) {
        try {
            $this->ConnectDatabase();
            $stmt = $this->myPdo->prepare('SELECT * FROM licence WHERE user_id = :userId AND user_password = :userPassword');
            $stmt->bindParam(':userId', $licenceRequestInformation->UserId, PDO::PARAM_STR);
            $stmt->bindParam(':userPassword', $licenceRequestInformation->Password, PDO::PARAM_STR);
            $stmt->execute();
            $licenceData = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($licenceData) {
                $LicenceInformation = new LicenceInformation();
                $LicenceInformation->UserId = $licenceRequestInformation->UserId;
                $LicenceInformation->Password = $licenceRequestInformation->Password;
                $LicenceInformation->ProductId = $licenceData['product_id'];
                $LicenceInformation->BeginDate = $licenceData['licence_begin_date'];
                $LicenceInformation->EndDate = $licenceData['licence_end_date'];
                $LicenceInformation->MacAddress = $licenceRequestInformation->MacAddress;
                $LicenceInformation->InstallCount = $licenceRequestInformation->InstallCount;
                $result = $LicenceInformation;
            } else {
                $result = false;
            }
            $this->DisconnectDatabase();
            return $result;
        } catch(PDOException $e) {
            print('ライセンス情報取得エラー'.$e->getMessage());
            throw $e;
        }
    }

    // ライセンス数カウント変更
    public function UpdateCurrentLicenceCount ($userId, $updateFlag) {
        try {
            $this->ConnectDatabase();
            // カウントアップか確認
            // (0がカウントアップ、1がカウントダウン)
            if ($updateFlag == 0) {
                $stmt = $this->myPdo->prepare('UPDATE licence SET number_of_current_authentication_licence = number_of_current_authentication_licence + 1 WHERE user_id = :userId');
            } else if ($updateFlag == 1) {
                $stmt = $this->myPdo->prepare('UPDATE licence SET number_of_current_authentication_licence = number_of_current_authentication_licence - 1 WHERE user_id = :userId');                
            }
            $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
            $stmt->execute();
            $this->DisconnectDatabase();
        } catch(PDOException $e) {
            print('カウントアップエラー'.$e->getMessage());
            throw $e;
        }
    }

    // アクセスログ登録
    public function InsertAccessLog ($accessLogInformation) {
        try {
            $this->ConnectDatabase();
            $stmt = $this->myPdo->prepare("INSERT INTO customer_access_log(user_id, mac_address, ip_address, browser_information, url, http_referrer, operation, access_date)
                                            VALUES (:userId, :macAddress, :ipAddress, :browserInformation, :url, :httpReferrer, :operation, :accessDate)");
            $stmt->bindParam(':userId', $accessLogInformation->UserId, PDO::PARAM_STR);
            $stmt->bindParam(':macAddress', $accessLogInformation->MacAddress, PDO::PARAM_STR);
            $stmt->bindParam(':ipAddress', $accessLogInformation->IpAddress, PDO::PARAM_STR);
            $stmt->bindParam(':browserInformation', $accessLogInformation->BrowserInformation, PDO::PARAM_STR);
            $stmt->bindParam(':url', $accessLogInformation->Url, PDO::PARAM_STR);
            $stmt->bindParam(':httpReferrer', $accessLogInformation->HttpReferrer, PDO::PARAM_STR);
            $stmt->bindParam(':operation', $accessLogInformation->Operation, PDO::PARAM_STR);
            $stmt->bindParam(':accessDate', $accessLogInformation->AccessDate, PDO::PARAM_STR);
            $stmt->execute();
            $this->DisconnectDatabase();
        } catch (PDOException $e) {
            print('登録失敗' . $e->getMessage());
            throw $e;
        }
    }
}
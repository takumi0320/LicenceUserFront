<?php
/**
 * ライセンス情報の取得クラス
 * (画面とデータベースの架け橋となるクラス)
 */
require_once (dirname(__FILE__) . "/DatabaseManager.php");
require_once (dirname(__FILE__) . "/EncryptManager.php");
require_once (dirname(__FILE__) . "/AccessLogManager.php");
require_once (dirname(__FILE__) . "/../InformationClass/RequestLicenceInformation.php");
require_once (dirname(__FILE__) . "/../InformationClass/ReleaseLicenceInformation.php");

class LicenceManager {
    // ライセンス認証
    public function AuthenticateLicence ($requestLicenceList, $accessLogInformation) {
        // 暗号文を復号
        $requestLicenceInformation = $this->DecryptRequestLicenceInformation($requestLicenceList);
        if (!$requestLicenceInformation) {
            return false;
        }
        $DatabaseManager = new DatabaseManager();
        // データベースからライセンス情報取得
        $licenceInformation = $DatabaseManager->GetLicenceInformation($requestLicenceInformation);
        // ライセンス数の上限と現在のライセンス認証数を取得
        $licenceCount = $DatabaseManager->GetLicenceCount($requestLicenceInformation);
        // ライセンス情報の有無確認
        if ($licenceInformation != false) {
            // ライセンス数が上限を超えていないか確認
            if ($licenceCount["contractLicenceCount"] > $licenceCount['currentAuthenticateLicenceCount']) {
                $DatabaseManager->UpdateCurrentLicenceCount($requestLicenceInformation->UserId, 0);
                $accessLogInformation->UserId = $requestLicenceInformation->UserId;
                $accessLogInformation->MacAddress = $requestLicenceInformation->MacAddress;
                $accessLogInformation->Operation = "ライセンス認証";
                $accessLogManager = new AccessLogManager();
                $accessLogManager->RegisterAccessLog($accessLogInformation);
                $encryptLicenceInformation = $this->EncryptLicenceInformation($licenceInformation);
                $fileName = $this->IssueLicenceFile($encryptLicenceInformation);
                header('Location: ./authenticateCompleteLicence.php?file=' . $fileName);
                exit();
            } else {
                header('Location: ./authenticateError.php');
                exit();
            }
        } else {
            header('Location: ./notFoundLicenceFile.php');
            exit();
        }
        return true;
    }

    // ライセンス更新
    public function RenewLicence ($requestLicenceList, $accessLogInformation) {
        // 暗号文を復号
        $requestLicenceInformation = $this->DecryptRequestLicenceInformation($requestLicenceList);
        if (!$requestLicenceInformation) {
            return false;
        }
        $DatabaseManager = new DatabaseManager();
        // データベースからライセンス情報取得
        $licenceInformation = $DatabaseManager->GetLicenceInformation($requestLicenceInformation);
        // ライセンス情報の有無確認
        if ($licenceInformation != false) {
            $accessLogInformation->UserId = $requestLicenceInformation->UserId;
            $accessLogInformation->MacAddress = $requestLicenceInformation->MacAddress;
            $accessLogInformation->Operation = "ライセンス更新";
            $accessLogManager = new AccessLogManager();
            $accessLogManager->RegisterAccessLog($accessLogInformation);
            $encryptLicenceInformation = $this->EncryptLicenceInformation($licenceInformation);
            $fileName = $this->IssueLicenceFile($encryptLicenceInformation);
            header('Location: ./renewCompleteLicence.php?file=' . $fileName);
            exit();
        } else {
            header('Location: ./renewError.php');
            exit();
        }
        return true;
    }

    // ライセンス解除
    public function ReleaseLicence ($releaseLicenceList, $accessLogInformation) {
        // 暗号文を復号
        $releaseLicenceInformation = $this->DecryptReleaseLicenceInformation($releaseLicenceList);
        if (!$releaseLicenceInformation) {
            return false;
        }
        $DatabaseManager = new DatabaseManager();
        // データベースからライセンス情報取得
        $licenceInformation = $DatabaseManager->GetLicenceInformation($releaseLicenceInformation);
        // ライセンス情報の有無確認
        if ($licenceInformation != false) {
            $DatabaseManager->UpdateCurrentLicenceCount($releaseLicenceInformation->UserId, 1);
            $accessLogInformation->UserId = $releaseLicenceInformation->UserId;
            $accessLogInformation->MacAddress = $releaseLicenceInformation->MacAddress;
            $accessLogInformation->Operation = "ライセンス解除";
            $accessLogManager = new AccessLogManager();
            $accessLogManager->RegisterAccessLog($accessLogInformation);
            header('Location: ./releaseCompleteLicence.php');
            exit();
        } else {
            header('Location: ./notFoundLicenceFile.php');
            exit();
        }
        return true;
    }

    // ライセンス情報暗号化
    private function EncryptLicenceInformation ($licenceInformation) {
        $EncryptManager = new EncryptManager();
        $array = array();
        $counter = 0;
        foreach ($licenceInformation as $licenceKey => $licenceValue) {
            if ($licenceKey != "OptionInformation") {
                if ($licenceKey != "Password") {
                    $licenceInformation->$licenceKey = $EncryptManager->Encrypt($licenceValue);
                } else {
                    $licenceInformation->$licenceKey = $licenceValue;
                }
            } else {
                for ($arrayCount = 0; count($licenceInformation->$licenceKey) > $arrayCount; $arrayCount++) {
                    array_push($array, $licenceInformation->$licenceKey[$arrayCount]);
                    $licenceInformation->$licenceKey[$arrayCount]->OptionId = $EncryptManager->Encrypt($licenceInformation->$licenceKey[$arrayCount]->OptionId);
                    $licenceInformation->$licenceKey[$arrayCount]->OptionName = $EncryptManager->Encrypt($licenceInformation->$licenceKey[$arrayCount]->OptionName);
                    $licenceInformation->$licenceKey[$arrayCount]->BeginDate = $EncryptManager->Encrypt($licenceInformation->$licenceKey[$arrayCount]->BeginDate);
                    $licenceInformation->$licenceKey[$arrayCount]->EndDate = $EncryptManager->Encrypt($licenceInformation->$licenceKey[$arrayCount]->EndDate);
                    array_push($array, $licenceInformation->$licenceKey[$arrayCount]);
                }
            }
        }
        return $licenceInformation;
    }

    // ライセンス申請情報復号
    private function DecryptRequestLicenceInformation ($requestLicenceList) {
        $errorFlag = true; // true時はエラーがない
        $counter = 0; // 要素数確認カウンター
        $EncryptManager = new EncryptManager();
        $RequestLicenceInformation = new RequestLicenceInformation();
        foreach ($requestLicenceList as $key => $value) {
            $result = $EncryptManager->Decrypt($value);
            // 中身が空だとエラー
            if ($result == NULL || $result == "") {
                $errorFlag = false;
            }
            switch ($key) {
                case 'userid' :
                    $RequestLicenceInformation->UserId = $result;
                    break;
                case 'password' :
                    $RequestLicenceInformation->Password = $value;
                    break;
                case 'macaddress' :
                    $RequestLicenceInformation->MacAddress = $result;
                    break;
                case 'installcount' :
                    $RequestLicenceInformation->InstallCount = (Int)$result;
                    break;
                default :
                    // 存在しないタグだとエラー
                    $errorFlag = false;
                    break;
            }
            $counter++;
        }
        // エラーが存在するとfalseを返す
        if ($errorFlag) {
            // カウンターの数が4つの必要要素と等しくなければfalseを返す
            if ($counter == 4) {
                return $RequestLicenceInformation;
            } else {
                return false;
            }
        } else {
            return $errorFlag;
        }
    }

    // ライセンス解除情報復号
    private function DecryptReleaseLicenceInformation ($releaseLicenceList) {
        $errorFlag = true; // true時はエラーがない
        $counter = 0; // 要素数確認カウンター
        $EncryptManager = new EncryptManager();
        $ReleaseLicenceInformation = new ReleaseLicenceInformation();
        foreach ($releaseLicenceList as $key => $value) {
            $result = $EncryptManager->Decrypt($value);
            // 中身が空だとエラー
            if ($result == NULL || $result == "") {
                $errorFlag = false;
            }
            switch ($key) {
                case 'userid' :
                    $ReleaseLicenceInformation->UserId = $result;
                    break;
                case 'password' :
                    $ReleaseLicenceInformation->Password = $value;
                    break;
                case 'macaddress' :
                    $ReleaseLicenceInformation->MacAddress = $result;
                    break;
                default :
                    // 存在しないタグだとエラー
                    $errorFlag = false;
                    break;
            }
            $counter++;
        }
        // エラーが存在するとfalseを返す
        if ($errorFlag) {
            // カウンターの数が3つの必要要素と等しくなければfalseを返す
            if ($counter == 3) {
                return $ReleaseLicenceInformation;
            } else {
                return false;
            }
        } else {
            return $errorFlag;
        }
    }

    // ライセンス認証・更新ファイル生成
    private function IssueLicenceFile ($licenceInformation) {
        $this->UnlinkFile();
        // DOMの作成
        $dom = new DomDocument('1.0', 'shift_jis');
        $dom->formatOutput = true;
        $licenceInfo = $dom->appendChild($dom->createElement('licenceinfo'));
        $licenceInfo->appendChild($dom->createElement('userid', $licenceInformation->UserId));
        $licenceInfo->appendChild($dom->createElement('password', $licenceInformation->Password));
        $licenceInfo->appendChild($dom->createElement('productid', $licenceInformation->ProductId));
        $licenceInfo->appendChild($dom->createElement('productname', $licenceInformation->ProductName));
        $licenceInfo->appendChild($dom->createElement('begindate', $licenceInformation->BeginDate));
        $licenceInfo->appendChild($dom->createElement('enddate', $licenceInformation->EndDate));
        $licenceInfo->appendChild($dom->createElement('macaddress', $licenceInformation->MacAddress));
        $licenceInfo->appendChild($dom->createElement('installcount', $licenceInformation->InstallCount));
        if (count($licenceInformation->OptionInformation) != 0) {
            $licenceOptions = $licenceInfo->appendChild($dom->createElement('licenceoptions'));
            for ($optionCount = 0; $optionCount < count($licenceInformation->OptionInformation); $optionCount++) {
                $option = $licenceOptions->appendChild($dom->createElement('option'));
                $option->appendChild($dom->createElement('optionid', $licenceInformation->OptionInformation[$optionCount]->OptionId));
                $option->appendChild($dom->createElement('optionname', $licenceInformation->OptionInformation[$optionCount]->OptionName));
                $option->appendChild($dom->createElement('begindate', $licenceInformation->OptionInformation[$optionCount]->BeginDate));
                $option->appendChild($dom->createElement('enddate', $licenceInformation->OptionInformation[$optionCount]->EndDate));
            }
        }
        $xmlData = preg_replace("/\r\n|\r|\n/", "\r\n", $dom->saveXML());
        $fileName = md5(uniqid(rand()));
        file_put_contents(dirname(__FILE__) . '/../../tmp/' . $fileName, $xmlData);
        return $fileName;
    }

    // 一定期間経った認証・申請ファイルの削除
    private function UnlinkFile () {
        //削除期限
        $expire = strtotime("30 minutes ago");
        //ディレクトリ
        $dir = dirname(__FILE__) . '/../../tmp/';
        $list = scandir($dir);
        foreach ($list as $value) {
            $file = $dir . $value;
            if (!is_file($file) || $value === "readme.md") continue;
            $mod = filemtime($file);
            if ($mod < $expire) {
                unlink($file);
            }
        }
    }

}
<?php
require_once (dirname(__FILE__) . '/Database/ManagerClass/LicenceManager.php');
if (isset($_FILES['uploadFile'])) {
    // XMLファイルを読み込んでjson形式に変換
    $xmlToJson = json_encode(simplexml_load_file($_FILES['uploadFile']['tmp_name']));
    // json形式をPHPの連想配列に変換
    $data = json_decode($xmlToJson, true);
    include "./include/accessLog.php";
    $LicenceManager = new LicenceManager();
    $result = $LicenceManager->RenewLicence($data, $accessLogInformation);
    if (!$result) {
        header('Location: ./fileError.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <?php include './meta-data.html'; ?>
</head>
<body class="upload">
    <?php include './header.html' ?>
    <div class="container text-center">
        <div class="page-title">
            <h2>ライセンス更新方法</h2>
        </div>
        <form name="renewLicenceForm" action="./renewLicence.php" method="post" enctype="multipart/form-data">
            <div class="big-button-space">
                <p>1.システムのライセンス更新申請ファイル選択</p>
                <div>
                    <input type="file" id="upload-file" name="uploadFile" style="display:none;" onchange="$('#fake-input-file').val('ファイル名:　' + $(this).prop('files')[0].name)">
                    <button type="button" class="btn btn-success btn-large big-button" value="ファイル選択" onClick="$('#upload-file').click();">ライセンス更新申請<br>ファイル選択</button>
                </div>
                <div>
                    <input id="fake-input-file" readonly type="text" value="">
                </div>
            </div>
            <div class="big-button-space">
                <p>2.ライセンス更新ファイル発行ボタンを選択</p>
                <button class="btn btn-info btn-large big-button" type="button" onclick="fileExtensionCheck();">ライセンス更新<br>ファイル発行</button>
            </div>
        </form>
        <div class="go-back">
            <a href="./" class="btn btn-default">戻る</a>
        </div>

    </div>
    <script src="./js/jquery-1.12.4.min.js"></script>
    <script src="./bootstrap/js/bootstrap.min.js"></script>
    <script src="./js/renewLicence.js"></script>
</body>
</html>

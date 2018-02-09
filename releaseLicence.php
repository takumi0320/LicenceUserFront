<?php
require_once (dirname(__FILE__) . '/Database/ManagerClass/LicenceManager.php');
if (isset($_FILES['uploadFile'])) {
    // XMLファイルを読み込んでjson形式に変換
    $xmlToJson = json_encode(simplexml_load_file($_FILES['uploadFile']['tmp_name']));
    // json形式をPHPの連想配列に変換
    $data = json_decode($xmlToJson, true);
    include "./include/accessLog.php";
    $LicenceManager = new LicenceManager();
    $result = $LicenceManager->ReleaseLicence($data, $accessLogInformation);
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
            <h2>ライセンス解除方法</h2>
        </div>
        <form name="releaseLicenceForm" action="./releaseLicence.php" method="post" enctype="multipart/form-data">
            <div class="big-button-space">
                <p>1.システムのライセンス解除ファイル選択</p>
                <div>
                    <input type="file" id="upload-file" name="uploadFile" style="display:none;" onchange="$('#fake-input-file').val('ファイル名:　' + $(this).prop('files')[0].name)">
                    <button type="button" class="btn btn-success btn-large big-button" value="ファイル選択" onClick="$('#upload-file').click();">ライセンス解除<br>ファイル選択</button>
                </div>
                <div>
                    <input id="fake-input-file" readonly type="text" value="">
                </div>
            </div>
            <div class="big-button-space">
                <p>2.ライセンス解除ボタンを選択</p>
                <button class="btn btn-danger btn-large big-release-button" type="button" onclick="fileExtensionCheck();">ライセンス解除</button>
            </div>
        </form>
        <div class="go-back">
            <a href="./" class="btn btn-default">戻る</a>
        </div>

    </div>
    <script src="./js/jquery-1.12.4.min.js"></script>
    <script src="./bootstrap/js/bootstrap.min.js"></script>
    <script src="./js/releaseLicence.js"></script>
</body>
</html>

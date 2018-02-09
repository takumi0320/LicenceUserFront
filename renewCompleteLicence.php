<?php
if (isset($_GET['file'])) {
    $fileName = htmlspecialchars($_GET['file'], ENT_QUOTES, "UTF-8");
} else {
    header('Location: ./renewError.php');
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <?php include './meta-data.html'; ?>
</head>
<body class="download">
    <?php include './header.html' ?>
    <div class="container text-center">
        <div class="page-title">
            <h2>更新ファイルダウンロード中...</h2>
            <p class="licence-message">
                更新ファイルを発行し、ダウンロードしています。<br>
                ダウンロード後は、認証システムの「認証ボタン」からファイルを選択してください。
            </p>
        </div>
        <div class="re-download-message">
            <a href="./renewFileDownload.php?file=<?php echo $fileName; ?>">ダウンロードが始まらない場合</a>
        </div>
        <div class="go-home">
            <a href="./" class="btn btn-default">ホームへ</a>
        </div>
    </div>
    <script src="./js/jquery-1.12.4.min.js"></script>
    <script src="./bootstrap/js/bootstrap.min.js"></script>
    <script id="script" src="./js/renewCompleteLicence.js" data-file-name='<?php echo json_encode($fileName); ?>'></script>
</body>
</html>

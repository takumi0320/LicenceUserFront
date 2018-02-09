<!DOCTYPE html>
<html lang="ja">
<head>
    <?php include './meta-data.html'; ?>
</head>
<body>
    <?php include './header.html' ?>
    <div class="container text-center">
        <div class="row col-menu">
            <p>ライセンス認証ファイルの発行はこちら</p>
            <a href="./authenticateLicence.php" class="btn btn-success btn-large main-btn">ライセンス認証</a>
        </div>
        <div class="row col-menu">
            <p>ライセンス更新ファイルの発行はこちら</p>
            <a href="./renewLicence.php" class="btn btn-info btn-large main-btn">ライセンス更新</a>
        </div>
        <div class="row col-menu">
            <p>ライセンス解除ファイルの発行はこちら</p>
            <a href="./releaseLicence.php" class="btn btn-danger btn-large main-btn">ライセンス解除</a>
        </div>
    </div>
<script src="./js/jquery-1.12.4.min.js"></script>
<script src="./bootstrap/js/bootstrap.min.js"></script>
</body>
</html>

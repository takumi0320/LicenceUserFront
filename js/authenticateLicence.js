// ファイル拡張子チェック
function fileExtensionCheck () {
    var uploadedFile = document.getElementById("upload-file").value;
    if (uploadedFile.match(/ライセンス申請ファイル.xml$/)) {
        document.authenticateLicenceForm.submit();
    } else {
        window.location = "./fileError.php";
    }
}
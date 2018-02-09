// ファイル拡張子チェック
function fileExtensionCheck () {
    var uploadedFile = document.getElementById("upload-file").value;
    if (uploadedFile.match(/ライセンス解除ファイル.xml$/)) {
        document.releaseLicenceForm.submit();
    } else {
        window.location = "./fileError.php";
    }
}
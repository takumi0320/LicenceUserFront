window.onload = function () {
    var $script = $('#script');
    var fileName = JSON.parse($script.attr('data-file-name'));
    window.location.href = './authenticateFileDownload.php?file=' + fileName;
}
window.onload = function () {
    var $script = $('#script');
    var fileName = JSON.parse($script.attr('data-file-name'));
    console.log(fileName);
    window.location.href = './renewFileDownload.php?file=' + fileName;
}
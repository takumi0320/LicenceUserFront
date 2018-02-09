<?php
$filePath = "./tmp/" . htmlspecialchars($_GET['file'], ENT_QUOTES, "UTF-8");
$fileName = "ライセンス認証ファイル.xml";
header('Content-Type: application/xml');
header('Content-Length: ' . filesize($filePath));
header('Content-Disposition: attachment; filename="' . $fileName . '"');
readfile($filePath);
?>
<?php
set_time_limit(0);
header('Content-Type: text/html; charset=utf-8');

$host = 'https://borys';
$root = __DIR__;
$dir  = $root . '/wp-content/uploads/';

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

foreach ($it as $f) {
    if (!$f->isFile()) continue;
    $ext = strtolower($f->getExtension());
    if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) continue;

    $url = $host . str_replace($root, '', $f->getPathname());

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "$code | $url <br>";
    flush();
}

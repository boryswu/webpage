<?php
// 获取网站的 HTML 内容
$url = 'https://borys'; // 替换为你的网站 URL
$html = file_get_contents($url);

// 使用 DOMDocument 解析
$dom = new DOMDocument();
@$dom->loadHTML($html);

// 获取所有 img 标签
$images = $dom->getElementsByTagName('img');

// 遍历所有 img 标签
foreach ($images as $image) {
    $src = $image->getAttribute('src');
    // 处理相对路径
    if (!preg_match('/^https?:\/\//', $src)) {
       baseUrl = parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST);
        $src = $baseUrl . '/' . ltrim($src, '/');
    }

    // 检查图片是否存在
    $headers = @get_headers($src);
    if ($headers && strpos($headers[0], '200')!== false) {
        echo "图片 $src 存在。<br>";
    } else {
        echo "图片 $src 不存在。<br>";
    }
}
?>

<?php
// 配置：防超时、扩内存
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');
header('Content-Type: text/html; charset=utf-8');

// 自动获取网站根目录 + 定位 wp-content 目录
$web_root = $_SERVER['DOCUMENT_ROOT'];
$target_dir = $web_root . '/wp-content/uploads/'; // 核心：只扫描这个目录
$domain = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]";

// 支持的图片格式
$allow_ext = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
$image_list = [];

// 递归扫描 wp-content 所有图片
function scan_images($dir) {
    global $allow_ext, $image_list, $web_root, $domain;
    
    $handle = @opendir($dir);
    if(!$handle) return;

    while(($file = readdir($handle)) !== false) {
        if($file == '.' || $file == '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $file;

        // 递归子目录
        if(is_dir($path)) {
            scan_images($path);
        } else {
            // 筛选图片
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if(in_array($ext, $allow_ext)) {
                // 生成URL
                $relative = str_replace($web_root, '', $path);
                $relative = str_replace('\\', '/', $relative);
                $url = $domain . $relative;
                
                // 检查是否存在
                $exists = file_exists($path) && is_readable($path);
                
                $image_list[] = [
                    'path' => $path,
                    'url' => $url,
                    'exists' => $exists
                ];
            }
        }
    }
    closedir($handle);
}

// 开始扫描
scan_images($target_dir);

// 统计
$total = count($image_list);
$success = 0;
$fail = 0;
foreach($image_list as $img) {
    $img['exists'] ? $success++ : $fail++;
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>wp-content 图片检测</title>
    <style>
        body{background:#f5f5f5;font-family:微软雅黑;margin:20px}
        .container{max-width:1200px;margin:0 auto;background:#fff;padding:20px;border-radius:8px}
        .stats{display:flex;gap:20px;margin:20px 0}
        .stat{padding:10px 20px;color:#fff;border-radius:5px}
        .total{background:#007cba}
        .success{background:#00a32a}
        .fail{background:#dc3545}
        table{width:100%;border-collapse:collapse;margin-top:10px}
        th,td{padding:10px;border:1px solid #ddd}
        th{background:#007cba;color:#fff}
        .ok{color:#00a32a}
        .error{color:#dc3545}
        a{color:#007cba}
    </style>
</head>
<body>
<div class="container">
    <h2>WordPress wp-content 目录图片检测报告</h2>
    <div class="stats">
        <div class="stat total">总图片：<?=$total?></div>
        <div class="stat success">正常：<?=$success?></div>
        <div class="stat fail">失效：<?=$fail?></div>
    </div>
    <table>
        <tr>
            <th>序号</th>
            <th>图片URL</th>
            <th>服务器路径</th>
            <th>状态</th>
        </tr>
        <?php foreach($image_list as $k=>$img):?>
        <tr>
            <td><?=$k+1?></td>
            <td><a href="<?=$img['url']?>" target="_blank"><?=$img['url']?></a></td>
            <td><?=$img['path']?></td>
            <td class="<?=$img['exists']?'ok':'error'?>"><?=$img['exists']?'✅正常':'❌失效'?></td>
        </tr>
        <?php endforeach;?>
    </table>
</div>
</body>
</html>

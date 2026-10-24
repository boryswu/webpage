<?php
/**
 * WordPress Uploads目录PDF文件检测脚本
 * 功能：检测wp-content/uploads/下所有PDF文件是否存在，输出文件名和状态
 */

// 1. 配置区（无需修改，自动适配WordPress路径）
define('WP_ROOT', __DIR__); // 当前脚本所在目录为网站根目录
define('UPLOADS_DIR', WP_ROOT . '/wp-content/uploads/'); // Uploads目录路径
define('PDF_EXTENSION', 'pdf'); // 要检测的文件类型

// 2. 初始化变量
$pdf_files = []; // 存储所有PDF文件
$error_files = []; // 存储不存在/不可读的PDF文件
$success_count = 0; // 有效文件计数
$total_count = 0; // 总文件计数

// 3. 递归遍历目录函数
function scanPdfFiles($dir, &$results, $extension) {
    // 打开目录
    $iterator = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
    
    // 遍历所有文件
    foreach ($files as $file) {
        // 只检测文件，排除目录
        if ($file->isFile()) {
            // 获取文件后缀（小写）
            $file_ext = strtolower($file->getExtension());
            // 匹配PDF文件
            if ($file_ext === $extension) {
                $file_info = [
                    'filename' => $file->getFilename(), // 文件名（含后缀）
                    'real_path' => $file->getRealPath(), // 完整物理路径
                    'web_path' => str_replace(WP_ROOT, '', $file->getRealPath()), // 网站相对路径
                    'file_exists' => file_exists($file->getRealPath()), // 文件是否存在
                    'is_readable' => is_readable($file->getRealPath()), // 文件是否可读
                    'file_size' => round($file->getSize() / 1024, 2) . ' KB' // 文件大小
                ];
                $results[] = $file_info;
            }
        }
    }
}

// 4. 执行检测
header('Content-Type: text/html; charset=utf-8');
echo "<div style='max-width: 1200px; margin: 20px auto; padding: 20px; font-family: Arial;'>";
echo "<h1 style='color: #2d8cf0; border-bottom: 2px solid #eee; padding-bottom: 10px;'>WordPress Uploads目录PDF文件检测报告</h1>";

// 检查Uploads目录是否存在
if (!is_dir(UPLOADS_DIR)) {
    echo "<p style='color: #ff4d4f; font-size: 16px;'>❌ 错误：Uploads目录不存在！路径：" . UPLOADS_DIR . "</p>";
    echo "</div>";
    exit;
}

// 开始扫描PDF文件
scanPdfFiles(UPLOADS_DIR, $pdf_files, PDF_EXTENSION);
$total_count = count($pdf_files);

// 5. 输出检测结果
if ($total_count === 0) {
    echo "<p style='color: #faad14; font-size: 16px;'>⚠️ 未在Uploads目录下找到任何PDF文件</p>";
} else {
    echo "<p style='font-size: 16px;'>✅ 共找到 <strong style='color: #2d8cf0;'>{$total_count}</strong> 个PDF文件，检测结果如下：</p>";
    echo "<table style='width: 100%; border-collapse: collapse; margin-top: 15px;'>";
    echo "<tr style='background: #f5f7fa;'>
            <th style='border:1px solid #ddd; padding:12px; text-align:left;'>序号</th>
            <th style='border:1px solid #ddd; padding:12px; text-align:left;'>PDF文件名</th>
            <th style='border:1px solid #ddd; padding:12px; text-align:left;'>文件大小</th>
            <th style='border:1px solid #ddd; padding:12px; text-align:left;'>状态</th>
            <th style='border:1px solid #ddd; padding:12px; text-align:left;'>网站相对路径</th>
          </tr>";

    // 遍历所有PDF文件
    foreach ($pdf_files as $index => $pdf) {
        $serial = $index + 1;
        $status_color = $pdf['file_exists'] && $pdf['is_readable'] ? '#52c41a' : '#ff4d4f';
        $status_text = $pdf['file_exists'] && $pdf['is_readable'] ? '✅ 文件正常' : '❌ 文件损坏/不存在';
        
        // 统计异常文件
        if (!$pdf['file_exists'] || !$pdf['is_readable']) {
            $error_files[] = $pdf;
        } else {
            $success_count++;
        }

        echo "<tr>
                <td style='border:1px solid #ddd; padding:10px;'>{$serial}</td>
                <td style='border:1px solid #ddd; padding:10px; font-weight:bold;'>{$pdf['filename']}</td>
                <td style='border:1px solid #ddd; padding:10px;'>{$pdf['file_size']}</td>
                <td style='border:1px solid #ddd; padding:10px; color:{$status_color};'>{$status_text}</td>
                <td style='border:1px solid #ddd; padding:10px; font-size:12px;'>{$pdf['web_path']}</td>
              </tr>";
    }
    echo "</table>";
}

// 6. 输出统计总结
echo "<div style='margin-top: 20px; padding: 15px; background: #f5f7fa; border-radius: 5px;'>";
echo "<h3 style='margin:0 0 10px 0;'>📊 检测总结</h3>";
echo "<p>总PDF文件数：{$total_count}</p>";
echo "<p style='color: #52c41a;'>正常可访问文件：{$success_count}</p>";
echo "<p style='color: #ff4d4f;'>异常/不存在文件：" . count($error_files) . "</p>";
echo "</div>";

// 7. 输出异常文件列表（如果有）
if (!empty($error_files)) {
    echo "<div style='margin-top: 15px; padding: 15px; background: #fff2f0; border: 1px solid #ffccc7; border-radius: 5px;'>";
    echo "<h3 style='margin:0 0 10px 0; color: #ff4d4f;'>⚠️ 异常PDF文件列表</h3>";
    foreach ($error_files as $error) {
        echo "<p style='margin:5px 0;'>• {$error['filename']} (路径：{$error['web_path']})</p>";
    }
    echo "</div>";
}

echo "</div>";
?>

<?php
// 启动会话
session_start();

// 登录配置
$loginEnabled = true; // 是否启用登录
$adminUsername = 'oaoo'; // 管理员用户名
$adminPassword = '$2y$10$LlmQWnCCG8USmq4USWKZoek0qtTjJImilCpBk38psZR4G0TCbVKHG'; // 管理员密码
$useHashedPassword = true; // 是否使用哈希密码  https://uutool.cn/php-password/ 生成哈希值

// 显示配置
$showFileExtension = false; // 是否显示文件后缀名
$useSidebarFileTypeIcons = false; // 是否为侧栏下的文件显示不同的图标
$useContentAreaFileTypeIcons = true; // 是否为contentArea下的文件显示不同的图标

// 支持的文件格式
$supportedFileTypes = [
    'md',    // Markdown文件
    'doc',   // Word文档
    'docx',  // Word文档
    'xls',   // Excel表格
    'xlsx',  // Excel表格
    'pdf',   // PDF文档
    'txt',   // 文本文件
    'jpg',   // 图片
    'jpeg',  // 图片
    'png',   // 图片
    'gif',   // 图片
    'webp'   // 图片
];

// 排除的文件和文件夹
$excludeItems = [
    '.git',       // Git版本控制文件夹
    'node_modules', // Node.js依赖文件夹
    '.DS_Store',   // macOS系统文件
    'Thumbs.db',    // Windows系统文件
	'prism', // 文件夹
];

// 处理登录请求
if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $loginSuccess = false;
    if ($username === $adminUsername) {
        if ($useHashedPassword) {
            // 使用哈希密码验证
            $loginSuccess = password_verify($password, $adminPassword);
        } else {
            // 使用明文密码验证
            $loginSuccess = ($password === $adminPassword);
        }
    }
    
    if ($loginSuccess) {
        $_SESSION['logged_in'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $loginError = '用户名或密码错误';
    }
}

// 处理登出请求
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['logged_in']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// 安全处理目录遍历
$root = __DIR__;

// 处理搜索请求
if (isset($_GET['action']) && $_GET['action'] === 'search' && isset($_POST['query'])) {
    $query = $_POST['query'];
    $results = [];
    
    // 递归搜索文件
    function searchFilesRecursive($dir, $query, &$results, $root) {
        $items = @scandir($dir);
        if (!$items) return;
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            $relativePath = str_replace($root, '', $path);
            $relativePath = ltrim($relativePath, DIRECTORY_SEPARATOR);
            $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
            
            if (is_dir($path)) {
                // 搜索文件夹名
                if (stripos($item, $query) !== false) {
                    $results[] = [
                        'path' => $relativePath,
                        'name' => $item,
                        'type' => 'folder'
                    ];
                }
                // 递归搜索子文件夹
                searchFilesRecursive($path, $query, $results, $root);
            } elseif (in_array(strtolower(pathinfo($item, PATHINFO_EXTENSION)), $GLOBALS['supportedFileTypes'])) {
                // 搜索文件名
                $matchInName = stripos($item, $query) !== false;
                // 搜索文件内容（仅对文本文件）
                $matchInContent = false;
                $extension = strtolower(pathinfo($item, PATHINFO_EXTENSION));
                $textExtensions = ['md', 'txt', 'js', 'css'];
                
                if (in_array($extension, $textExtensions)) {
                    $content = @file_get_contents($path);
                    $matchInContent = $content !== false && stripos($content, $query) !== false;
                }
                
                if ($matchInName || $matchInContent) {
                    $results[] = [
                        'path' => $relativePath,
                        'name' => $item,
                        'type' => 'file',
                        'matchInName' => $matchInName,
                        'matchInContent' => $matchInContent
                    ];
                }
            }
        }
    }
    
    // 开始搜索
    searchFilesRecursive($root, $query, $results, $root);
    
    // 返回搜索结果
    header('Content-Type: application/json');
    echo json_encode($results);
    exit;
}

// 检查登录状态
if ($loginEnabled && !isset($_SESSION['logged_in'])) {
    // 显示登录页面
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>登录 - 文档系统</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
	<link rel="stylesheet" href="/prism/prism.css" type="text/css" media="all">
	<link rel="stylesheet" href="/prism/prism-tool.css" type="text/css" media="all">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            }
            
            body {
                background: #f5f7fa;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }
            
            .login-container {
                background: white;
                padding: 40px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                width: 100%;
                max-width: 400px;
            }
            
            .login-header {
                text-align: center;
                margin-bottom: 30px;
            }
            
            .login-header h1 {
                color: #4a90e2;
                font-size: 24px;
                margin-bottom: 10px;
            }
            
            .login-header p {
                color: #666;
                font-size: 14px;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .form-group label {
                display: block;
                margin-bottom: 8px;
                color: #333;
                font-size: 14px;
            }
            
            .form-group input {
                width: 100%;
                padding: 12px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 14px;
            }
            
            .form-group input:focus {
                outline: none;
                border-color: #4a90e2;
            }
            
            .error-message {
                background: #f8d7da;
                color: #721c24;
                padding: 10px;
                border-radius: 4px;
                margin-bottom: 20px;
                font-size: 14px;
            }
            
            .login-button {
                width: 100%;
                padding: 12px;
                background: #4a90e2;
                color: white;
                border: none;
                border-radius: 4px;
                font-size: 16px;
                cursor: pointer;
                transition: background 0.2s;
            }
            
            .login-button:hover {
                background: #3a80d2;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="login-header">
                <h1><i class="fas fa-lock"></i> 登录</h1>
                <p>请输入用户名和密码以访问文档系统</p>
            </div>
            
            <?php if (isset($loginError)): ?>
                <div class="error-message">
                    <?php echo $loginError; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-group">
                    <label for="username">用户名</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">密码</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" name="login" class="login-button">登录</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$requestPath = $_GET['file'] ?? '';
$fullPath = realpath($root . '/' . $requestPath);

// 防止目录穿越攻击
if ($fullPath === false || strpos($fullPath, $root) !== 0) {
    $fullPath = $root;
}

// 优化：增加全局执行超时控制（紧急兜底）
set_time_limit(10); // 整体请求超时设为10秒

// 全局缓存数组
$remoteContentCache = [];

// 获取远程内容的函数（优化版）
function getRemoteContent($url) {
    global $remoteContentCache;
    
    // 检查缓存
    $cacheKey = md5($url);
    if (isset($remoteContentCache[$cacheKey])) {
        return $remoteContentCache[$cacheKey];
    }
    
    // 安全校验：限制URL协议，防止非法请求
    $allowedSchemes = ['http', 'https'];
    $urlParts = parse_url($url);
    if (!isset($urlParts['scheme']) || !in_array($urlParts['scheme'], $allowedSchemes)) {
        return false;
    }

    // 尝试使用cURL（强化超时）
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1); // 进一步缩短超时（1秒）
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0.5); // 连接超时0.5秒
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2); // 限制重定向次数
        curl_setopt($ch, CURLOPT_NOSIGNAL, true); // 兼容Windows系统超时设置
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        $content = curl_exec($ch);
        
        // 捕获curl错误
        if (curl_errno($ch)) {
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        
        if ($content !== false && !empty($content)) {
            // 存入缓存
            $remoteContentCache[$cacheKey] = $content;
            return $content;
        }
    }
    
    // 尝试使用file_get_contents（强化超时）
    if (ini_get('allow_url_fopen')) {
        $context = stream_context_create([
            'http' => [
                'timeout' => 1, // 1秒超时
                'max_redirects' => 2,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);
        $content = @file_get_contents($url, false, $context);
        if ($content !== false && !empty($content)) {
            // 存入缓存
            $remoteContentCache[$cacheKey] = $content;
            return $content;
        }
    }
    
    return false;
}

// 递归生成目录树
function buildTree($dir, $root, $currentPath = '', $level = 0) {
    $result = [];
    // 优化：限制递归深度，防止栈溢出
    if ($level > 10) return $result;
    
    $items = @scandir($dir); // 增加@屏蔽无权限错误
    if (!$items) return $result;
    
    $folders = [];
        $files = [];
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            // 排除指定的文件和文件夹
            if (in_array($item, $GLOBALS['excludeItems'])) {
                continue;
            }
            
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            $relativePath = $currentPath . '/' . $item;
            
            if (is_dir($path)) {
                $folders[] = [
                    'name' => $item,
                    'type' => 'dir',
                    'path' => ltrim($relativePath, '/'),
                    'level' => $level,
                    'children' => buildTree($path, $root, $relativePath, $level + 1)
                ];
            } elseif (in_array(strtolower(pathinfo($item, PATHINFO_EXTENSION)), $GLOBALS['supportedFileTypes'])) {
                $files[] = [
                    'name' => $item,
                    'type' => 'file',
                    'path' => ltrim($relativePath, '/'),
                    'level' => $level
                ];
            }
        }
        
        // 合并文件夹和文件，文件夹在前，文件在后
        $result = array_merge($folders, $files);
    return $result;
}

$tree = buildTree($root, $root);

// 读取并解析markdown文件
$content = '';
if (is_file($fullPath) && pathinfo($fullPath, PATHINFO_EXTENSION) === 'md') {
    $rawContent = @file_get_contents($fullPath); // 增加@屏蔽文件读取错误
    if ($rawContent === false) {
        $content = '<div class="error">无法读取本地文件</div>';
    } else {
        // 优化：限制正则替换匹配次数，防止无限循环
        // 先处理代码块，将[include:***]和[file:***]标签在代码块中进行转义
        $codeBlocks = [];
        $rawContent = preg_replace_callback('/```(.*?)```/s', function($matches) use (&$codeBlocks) {
            $code = $matches[1];
            $codeBlocks[] = $code;
            return '```' . count($codeBlocks) . '```';
        }, $rawContent);
        
        // 解析[include:url]格式
        $rawContent = preg_replace_callback('/\[include:([^\]]+)\]/i', function($matches) {
            $includeStr = $matches[1];
            $parts = explode(' ', $includeStr);
            $remoteUrl = $parts[0];
            $params = [];
            
            // 优化：限制参数解析数量
            $maxParams = 10;
            $paramCount = 0;
            for ($i = 1; $i < count($parts) && $paramCount < $maxParams; $i++, $paramCount++) {
                $param = $parts[$i];
                if (strpos($param, 'lines=') === 0) {
                    $params['lines'] = substr($param, 6);
                } elseif (strpos($param, 'tail=') === 0) {
                    $params['tail'] = substr($param, 5);
                } elseif (strpos($param, 'range=') === 0) {
                    $range = substr($param, 6);
                    list($params['rangeStart'], $params['rangeEnd']) = explode('-', $range);
                } elseif (strpos($param, 'from=') === 0) {
                    $params['from'] = trim(substr($param, 5), '"\'');
                } elseif (strpos($param, 'to=') === 0) {
                    $params['to'] = trim(substr($param, 3), '"\'');
                }
            }
            
            $remoteContent = getRemoteContent($remoteUrl);
            if ($remoteContent !== false) {
                // 优化：内容处理增加边界检查
                $contentLines = explode("\n", $remoteContent);
                if (isset($params['lines']) && is_numeric($params['lines'])) {
                    $lines = intval($params['lines']);
                    $remoteContent = implode("\n", array_slice($contentLines, 0, max(1, min($lines, 1000)))); // 限制最大行数
                } elseif (isset($params['tail']) && is_numeric($params['tail'])) {
                    $tail = intval($params['tail']);
                    $remoteContent = implode("\n", array_slice($contentLines, -max(1, min($tail, 1000))));
                } elseif (isset($params['rangeStart'], $params['rangeEnd']) && is_numeric($params['rangeStart']) && is_numeric($params['rangeEnd'])) {
                    $start = max(0, intval($params['rangeStart']) - 1);
                    $end = max($start + 1, intval($params['rangeEnd']));
                    $remoteContent = implode("\n", array_slice($contentLines, $start, min($end - $start, 1000)));
                } elseif (isset($params['from'], $params['to'])) {
                    $startPos = strpos($remoteContent, $params['from']);
                    if ($startPos !== false) {
                        $endPos = strpos($remoteContent, $params['to'], $startPos + strlen($params['from']));
                        if ($endPos !== false) {
                            $remoteContent = substr($remoteContent, $startPos, min($endPos - $startPos + strlen($params['to']), 100000)); // 限制内容大小
                        }
                    }
                }
                return $remoteContent;
            } else {
                return '<div class="error">无法加载远程文件: ' . htmlspecialchars($remoteUrl) . '</div>';
            }
        }, $rawContent, 50); // 限制最多替换50个include标签，防止卡死
        
        // 解析[file:url]格式
        $rawContent = preg_replace_callback('/\[file:([^\]\s]+)(\s+height=(\d+px))?\]/i', function($matches) {
            $url = $matches[1];
            $base64Url = base64_encode($url);
            return '<iframe src="https://file.kkview.cn/onlinePreview?url=' . $base64Url . '&key=000"></iframe>';
        }, $rawContent);
        
        // 恢复代码块
        $rawContent = preg_replace_callback('/```(\d+)```/s', function($matches) use ($codeBlocks) {
            $index = intval($matches[1]) - 1;
            return isset($codeBlocks[$index]) ? '```' . $codeBlocks[$index] . '```' : $matches[0];
        }, $rawContent);
        
        // 检查是否存在Parsedown.php
        $parsedownPath = $root . '/Parsedown.php';
        if (file_exists($parsedownPath)) {
            // 使用Parsedown解析markdown
            require_once $parsedownPath;
            $parsedown = new Parsedown();
            $content = $parsedown->text($rawContent);
        } else {
            // 简单markdown解析
            $content = $rawContent;
            $content = preg_replace('/^# (.*?)$/m', '<h1>$1</h1>', $content);
            $content = preg_replace('/^## (.*?)$/m', '<h2>$1</h2>', $content);
            $content = preg_replace('/^### (.*?)$/m', '<h3>$1</h3>', $content);
            $content = preg_replace('/^#### (.*?)$/m', '<h4>$1</h4>', $content);
            $content = preg_replace('/^##### (.*?)$/m', '<h5>$1</h5>', $content);
            $content = preg_replace('/^###### (.*?)$/m', '<h6>$1</h6>', $content);
            $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);
            $content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content);
            $content = preg_replace('/^- (.*?)$/m', '<li>$1</li>', $content);
            $content = preg_replace('/(<li>.*?<\/li>)+/s', '<ul>$0</ul>', $content); // 修复列表正则
            $content = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $content);
            $content = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2">$1</a>', $content);
            // 用p标签包裹段落内容，替代nl2br
            $content = preg_replace('/^(?!<h[1-6]>)(?!<ul>)(?!<pre>)(?!<iframe>)(.*?)$/m', '<p>$1</p>', $content);
            // 移除多余的空p标签
            $content = preg_replace('/<p>\s*<\/p>/', '', $content);
        }
    }
} elseif (is_dir($fullPath)) {
    // 生成文件夹内容列表
    $folderName = basename($fullPath);
    $content = '<div class="folder-content">';
    
    // 分离文件和图片
    $folders = [];
    $files = [];
    $images = [];
    
    $items = @scandir($fullPath);
    if ($items) {
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            // 排除指定的文件和文件夹
            if (in_array($item, $GLOBALS['excludeItems'])) {
                continue;
            }
            
            $itemPath = $fullPath . DIRECTORY_SEPARATOR . $item;
            $relativePath = str_replace($root, '', $itemPath);
            $relativePath = ltrim($relativePath, DIRECTORY_SEPARATOR);
            $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
            
            if (is_dir($itemPath)) {
                $folders[] = ['type' => 'folder', 'name' => $item, 'path' => $relativePath];
            } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'md') {
                $displayName = $showFileExtension ? $item : pathinfo($item, PATHINFO_FILENAME);
                $files[] = ['type' => 'md', 'name' => $displayName, 'path' => $relativePath];
            } elseif (in_array(strtolower(pathinfo($item, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $displayName = $showFileExtension ? $item : pathinfo($item, PATHINFO_FILENAME);
                $images[] = ['name' => $displayName, 'path' => $relativePath];
            } elseif (in_array(strtolower(pathinfo($item, PATHINFO_EXTENSION)), ['doc', 'docx', 'xls', 'xlsx', 'pdf', 'txt'])) {
                $displayName = $showFileExtension ? $item : pathinfo($item, PATHINFO_FILENAME);
                $files[] = ['type' => 'file', 'name' => $displayName, 'path' => $relativePath];
            }
        }
    }
    
    // 合并文件夹和文件，文件夹在前，文件在后
    $files = array_merge($folders, $files);
    
    // 生成面包屑导航
    $breadcrumb = '<div class="breadcrumb">';
    $breadcrumb .= '<a href="?file="><i class="fas fa-home"></i> 首页</a>';
    
    $relativePath = str_replace($root, '', $fullPath);
    $relativePath = ltrim($relativePath, DIRECTORY_SEPARATOR);
    $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
    
    if (!empty($relativePath)) {
        $pathParts = explode('/', $relativePath);
        $currentPath = '';
        
        foreach ($pathParts as $part) {
            $currentPath .= $part . '/';
            $breadcrumb .= ' <span class="breadcrumb-separator">/</span> ';
            $breadcrumb .= '<a href="?file=' . rtrim($currentPath, '/') . '">' . htmlspecialchars($part) . '</a>';
        }
    }
    
    $breadcrumb .= '</div>';
    $content .= $breadcrumb;
    
    // 显示文件列表
    if (!empty($files)) {
        $content .= '<div class="file-list">';
        foreach ($files as $file) {
            if ($file['type'] === 'folder') {
                $content .= '<div class="file-item folder-item" data-path="' . htmlspecialchars($file['path']) . '"><i class="fas fa-folder"></i><span>' . htmlspecialchars($file['name']) . '</span></div>';
            } elseif ($file['type'] === 'md') {
                $iconClass = $GLOBALS['useContentAreaFileTypeIcons'] ? 'fab fa-markdown' : 'fas fa-file-alt';
                $content .= '<div class="file-item md-item" data-path="' . htmlspecialchars($file['path']) . '"><i class="' . $iconClass . '"></i><span>' . htmlspecialchars($file['name']) . '</span></div>';
            } elseif ($file['type'] === 'file') {
                $iconClass = 'fas fa-file';
                
                if ($GLOBALS['useContentAreaFileTypeIcons']) {
                    $extension = strtolower(pathinfo($file['path'], PATHINFO_EXTENSION));
                    switch ($extension) {
                        case 'doc':
                        case 'docx':
                            $iconClass = 'fas fa-file-word';
                            break;
                        case 'ppt':
                        case 'pptx':
                            $iconClass = 'fas fa-file-powerpoint';
                            break;
                        case 'xls':
                        case 'xlsx':
                            $iconClass = 'fas fa-file-excel';
                            break;
                        case 'pdf':
                            $iconClass = 'fas fa-file-pdf';
                            break;
                        case 'js':
                        case 'css':
                            $iconClass = 'fas fa-file-code';
                            break;
                    }
                }
                
                $content .= '<div class="file-item other-item" data-path="' . htmlspecialchars($file['path']) . '"><i class="' . $iconClass . '"></i><span>' . htmlspecialchars($file['name']) . '</span></div>';
            }
        }
        $content .= '</div>';
    }
    
    // 显示图片列表
    if (!empty($images)) {
        $content .= '<div id="lightgallery" class="image-list">';
        foreach ($images as $image) {
            $content .= '<div class="image-item" data-src="' . htmlspecialchars($image['path']) . '"><img src="' . htmlspecialchars($image['path']) . '" alt="' . htmlspecialchars($image['name']) . '"><span>' . htmlspecialchars($image['name']) . '</span></div>';
        }
        $content .= '</div>';
    }
    
    $content .= '</div>';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文档系统</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/prism/prism.css" type="text/css" media="all">
    <link rel="stylesheet" href="/prism/prism-tool.css" type="text/css" media="all">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery.js@1.4.0/dist/css/lightgallery.min.css">
    <script src="https://cdn.jsdelivr.net/npm/lightgallery.js@1.4.0/dist/js/lightgallery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lg-thumbnail.js@1.2.0/dist/lg-thumbnail.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lg-fullscreen.js@1.2.0/dist/lg-fullscreen.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lg-zoom.js@1.3.0/dist/lg-zoom.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lg-autoplay.js@1.0.0/dist/lg-autoplay.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lg-share.js@1.2.0/dist/lg-share.min.js"></script>

    <style>
        /* 原有样式保留，新增加载状态样式 */
        :root {
            --color-primary: #c0c5ca;
            --color-primary-light: #c0c5ca40;
            --color-text: #3d425c;
            --color-text-light: #666;
            --color-border: #ddd;
            --color-bg: #fff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
			color: var(--color-text);
			text-decoration: none !important;
        }
        
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            display: flex;
            align-items: center;
            padding: 0 20px;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
			justify-content: space-between;
        }
        
        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            margin-right: 10px;
        }
        
        .sidebar-toggle:hover {
            opacity: 0.8;
        }
        
        .header h1 {
            font-size: 20px;
            font-weight: 500;
        }
        
        .search-container {
            margin-left: 20px;
            flex: 1;
            max-width: 400px;
        }
        
        .search-box {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        #searchInput {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--color-border);
            border-radius: 4px;
            background: white;
            color: var(--color-text);
            font-size: 14px;
        }
        
        #searchInput::placeholder {
            color: var(--color-text-light);
        }
        
        #searchInput:focus {
            outline: none;
            border-color: var(--color-primary);
        }
        
        #searchButton {
            position: absolute;
            right: 5px;
            background: none;
            border: none;
            color: var(--color-text-light);
            cursor: pointer;
            padding: 5px;
        }
        
        #searchButton:hover {
            color: var(--color-primary);
        }
        
        .search-results {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 0 0 4px 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            width: 350px;
			max-height: 400px;
            overflow-y: auto;
            z-index: 1001;
            display: none;
        }
        
        .search-results.show {
            display: block;
        }
        
        .search-result-item {
            padding: 10px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .search-result-item:hover {
            background: #f5f5f5;
        }
        
        .search-result-item:last-child {
            border-bottom: none;
        }
        
        .search-result-item i {
            margin-right: 8px;
            color: var(--color-primary);
        }
		.header-left{
            display: flex;
            align-items: center;
			}
        .header-right {
            margin-left: 20px;
            display: flex;
            align-items: center;
        }
        
        /* 面包屑导航样式 */
        .breadcrumb {
            margin-bottom: 10px;
            padding: 10px 0;
        }
        ol{padding-left: 20px;}
        .breadcrumb a {
            color: var(--color-primary);
            text-decoration: none;
            display: inline-block;
            margin-right: 5px;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .breadcrumb i {
            margin-right: 5px;
        }
        
        .breadcrumb-separator {
            color: var(--color-text-light);
            margin: 0 5px;
        }
        
        .logout-button {
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background 0.2s;
        }
        
        .logout-button:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .logout-button i {
            margin-right: 5px;
        }
        
        .container {
            display: flex;
            margin-top: 60px;
            height: calc(100vh - 60px);
        }
        
        .sidebar {
            width: 280px;
            border-right: 1px solid var(--color-border);
            overflow-y: auto;
            padding: 20px 0;
            transition: transform 0.3s ease-in-out, width 0.3s ease-in-out, border-right 0.3s ease-in-out;
            background: white;
        }
        
        .sidebar.hidden {
            transform: translateX(-100%);
            width: 0;
            overflow: hidden;
            border-right: none;
        }
        
        .content {
            flex: 1;
            padding: 5px 20px;
            overflow-y: auto;
            background: var(--color-bg);
        }
        
        .tree-list {
            list-style: none;
            padding-left: 0;
            position: relative;
        }
        
        .tree-item {
            position: relative;
            padding: 3px 10px;
            cursor: pointer;
            transition: background 0.2s;
            padding-left: calc(15px + var(--level, 0) * 24px + 12px);
            display: flex;
            align-items: center;
			font-size: 0.9rem;
        }
        
        .tree-item::before {
            content: '';
            position: absolute;
            left: calc(var(--level, 0) * 24px + 4px);
            top: 0;
            width: 0;
            height: 100%;
            border-left: 1px dashed var(--color-border);
        }
        
        .tree-item::after {
            content: '';
            position: absolute;
            left: calc(var(--level, 0) * 24px + 4px);
            top: 15px;
            width: 18px;
            height: 0;
            border-top: 1px dashed var(--color-border);
        }
        
        .tree-item:last-child::before {
            height: 15px;
        }
        
        .tree-item[style*="--level: 0"]::before,
        .tree-item[style*="--level: 0"]::after {
            display: none;
        }
        
        .tree-item[style*="--level: 0"] {
            padding-left: 15px;
        }
        
        .tree-item:not([style*="--level: 0"]) {
            padding-left: calc(15px + var(--level, 0) * 24px + 12px);
        }
        
        .tree-item:not([style*="--level: 0"])::before,
        .tree-item:not([style*="--level: 0"])::after {
            left: calc(var(--level, 0) * 24px + 4px);
        }
        
        .tree-children {
            list-style: none;
            display: none;
            position: relative;
        }
        

        
        .tree-item:hover {
            background: var(--color-primary-light);
        }
        
        .tree-item.active {
            background: var(--color-primary);
            color: white;
        }
        
        .tree-folder {
            font-weight: 600;
            color: var(--color-text);
        }

        .tree-icon {
            margin-right: 8px;
            width: 16px;
            display: inline-block;
            transition: transform 0.2s;
        }
        
        .tree-folder .tree-icon {
            color: #f39c12;
        }
        
        .toc-container {
            position: relative;
        }
        
        .toc-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .toc-item {
            margin: 0;
            position: relative;
            padding-left: calc(15px + var(--level, 0) * 24px + 30px);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .toc-item::before {
            content: '';
            position: absolute;
            left: calc(var(--level, 0) * 24px + 4px);
            top: 0;
            width: 0;
            height: 100%;
            border-left: 1px dashed var(--color-border);
        }
        
        .toc-item::after {
            content: '';
            position: absolute;
            left: calc(var(--level, 0) * 24px + 4px);
            top: 15px;
            width: 35px;
            height: 0;
            border-top: 1px dashed var(--color-border);
        }
        
        .toc-item:last-child::before {
            height: 18px;
        }
        
        .toc-item a {
            text-decoration: none;
            color: var(--color-text);
            font-size: 0.8rem;
            display: block;
            padding: 3px 0;
            transition: background-color 0.2s, color 0.2s;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .toc-item a:hover {
            background-color: var(--color-primary-light);
            color: var(--color-primary);
			border-radius: 4px;
        }
        
        .tree-folder.open .tree-arrow {
            transform: rotate(90deg);
        }
        
        .tree-children.open {
            display: block;
        }
        
        .content h1 {
            margin-bottom: 20px;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 10px;
        }
        
        .content h2 {
            margin: 25px 0 15px;
        }
        
        .content h3 {
            margin: 20px 0 10px;
        }
        
        .content p {
            margin-bottom: 15px;
            line-height: 1.6;
        }
        
        .content ul {
            margin: 15px 0;
            padding-left: 30px;
        }
        
        .content li {
            margin-bottom: 8px;
            line-height: 1.6;
        }

        
        .content code {
            font-family: "Consolas", "Monaco", monospace;
            color: var(--color-code);
        }
        
        .content a {
            color: var(--color-primary);
            text-decoration: none;
        }
        
        .content a:hover {
            text-decoration: underline;
        }
        
        .welcome {
            text-align: center;
            margin-top: 100px;
            color: var(--color-text-light);
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border: 1px solid #f5c6cb;
        }
        
        /* 新增：加载状态样式 */
        .loading {
            text-align: center;
            padding: 50px 0;
            color: var(--color-text-light);
        }
        .loading i {
            font-size: 24px;
            margin-bottom: 10px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* iframe 样式 */
        iframe {
            width: 100%;
            height: calc(100vh - 120px);
            border: none;
        }
        
        /* 文件夹内容样式 */
        .folder-content h2 {
            margin-bottom: 20px;
            color: var(--color-text);
        }
        
        .file-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .file-item {
            background: var(--color-primary-light);
            border: 1px solid var(--color-border);
            border-radius: 5px;
            padding: 5px 10px;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .file-item:hover {
            background: rgba(74, 144, 226, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .file-item i {
            margin-right: 10px;
            color: var(--color-primary);
        }
        
        .folder-item i {
            color: #f39c12;
        }
		.fa-file-alt:before {
			color: #b6aea0;
		}
        
        .file-item span {
            font-size: 0.9rem;
            color: var(--color-text);
        }
        
        /* 图片列表样式 */
        .image-list {
            margin-top: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            width: 100%;
        }
        
        /* 图片文件项样式 */
        .image-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            background: var(--color-primary-light);
            border-radius: 5px;
            padding: 5px;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .image-item:hover {
            background: rgba(74, 144, 226, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .image-item img {
            width: 100%;
            max-height: 120px;
            object-fit: cover;
            border-radius: 3px;
        }
        
        .image-item span {
            font-size: 12px;
            color: var(--color-text);
            word-break: break-all;
			padding: 5px 0;
        }
        
        @media (max-width: 768px) {
            .sidebar-toggle {
                display: block;
            }
            
            .container {
                position: relative;
            }
            
            .sidebar {
                position: fixed;
                top: 60px;
                left: 0;
                bottom: 0;
                width: 280px;
                height: calc(100vh - 60px);
                border-right: 1px solid #dee2e6;
                border-bottom: none;
                transform: translateX(-100%);
                z-index: 999;
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .content {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
		<button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <a href="/">文档</a>
		</div>
        <?php if (isset($_SESSION['logged_in'])): ?>
        <div class="header-right">
			<div class="search-container">
				<div class="search-box">
					<input type="text" id="searchInput" placeholder="搜索文件...">
					<button id="searchButton"><i class="fas fa-search"></i></button>
					<div id="searchResults" class="search-results"></div>
				</div>
			</div>		
            <a href="?action=logout" class="logout-button">
                <i class="fas fa-sign-out-alt"></i> 退出
            </a>
        </div>
        <?php endif; ?>
    </div>

    <div class="container">
        <div class="sidebar">
            <ul class="tree-list" id="treeRoot">
                <?php
                function renderTree($items) {
                    global $showFileExtension, $useSidebarFileTypeIcons;
                    foreach ($items as $item) {
                        $level = $item['level'] ?? 0;
                        $path = htmlspecialchars($item['path']);
                        $name = htmlspecialchars($item['name']);
                        if ($item['type'] === 'dir') {
                            echo "<li data-path='$path' style='--level: $level' class='tree-item tree-folder'>";
                            echo '<i class="fas fa-folder tree-icon"></i>';
                            echo $name;
                            echo '</li>';
                            echo "<ul style='--level: $level' class='tree-children'>";
                            renderTree($item['children']);
                            echo '</ul>';
                        } else {
                            $displayName = $showFileExtension ? $name : pathinfo($name, PATHINFO_FILENAME);
                            $iconClass = 'fas fa-file-alt';
                            
                            if ($useSidebarFileTypeIcons) {
                                $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                                switch ($extension) {
                                    case 'doc':
                                    case 'docx':
                                        $iconClass = 'fas fa-file-word';
                                        break;
                                    case 'ppt':
                                    case 'pptx':
                                        $iconClass = 'fas fa-file-powerpoint';
                                        break;
                                    case 'xls':
                                    case 'xlsx':
                                        $iconClass = 'fas fa-file-excel';
                                        break;
                                    case 'pdf':
                                        $iconClass = 'fas fa-file-pdf';
                                        break;
                                    case 'md':
                                        $iconClass = 'fab fa-markdown';
                                        break;
                                    case 'js':
                                    case 'css':
                                        $iconClass = 'fas fa-file-code';
                                        break;
                                }
                            }
                            
                            echo "<li data-path='$path' style='--level: $level' class='tree-item tree-file'>";
                            echo '<i class="' . $iconClass . ' tree-icon"></i>';
                            echo $displayName;
                            echo '</li>';
                        }
                    }
                }
                renderTree($tree);
                ?>
            </ul>
        </div>

        <div class="content" id="contentArea">
            <?php echo $content; ?>
        </div>
    </div>
    <script>
        // 目录折叠/展开交互
        document.querySelectorAll('.tree-folder').forEach(folder => {
            folder.addEventListener('click', () => {
                const children = folder.nextElementSibling;
                children.classList.toggle('open');
                folder.classList.toggle('open');
                
                const folderIcon = folder.querySelector('.fas');
                if (folder.classList.contains('open')) {
                    folderIcon.classList.remove('fa-folder');
                    folderIcon.classList.add('fa-folder-open');
                } else {
                    folderIcon.classList.remove('fa-folder-open');
                    folderIcon.classList.add('fa-folder');
                }
                
                // 显示文件夹内容
                showFolderContent(folder.dataset.path);
            });
        });
        
        // 显示文件夹内容
        function showFolderContent(path) {
            // 清除选中状态
            document.querySelectorAll('.tree-item').forEach(item => item.classList.remove('active'));
            
            // 显示加载状态
            const contentArea = document.getElementById('contentArea');
            contentArea.innerHTML = '<div class="loading"><i class="fas fa-spinner"></i><p>加载中...</p></div>';
            
            // 关闭移动端侧边栏
            if (window.innerWidth <= 768) {
                document.querySelector('.sidebar').classList.remove('open');
            }
            
            // 使用AJAX加载文件夹内容
            const xhr = new XMLHttpRequest();
            xhr.open('GET', '?file=' + encodeURIComponent(path), true);
            xhr.timeout = 3000;
            xhr.onload = function() {
                if (xhr.status === 200) {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(xhr.responseText, 'text/html');
                        const newContent = doc.querySelector('.content').innerHTML;
                        contentArea.innerHTML = newContent;
                        // 更新地址栏
                        history.pushState({path: path}, '', '?file=' + encodeURIComponent(path));
                        // 为新生成的文件项添加点击事件
                        addFileItemListeners();
                        // 应用Prism代码高亮
                        if (typeof Prism !== 'undefined') {
                            Prism.highlightAll();
                            // 手动初始化prism-tool.js功能
                            const codeBlocks = document.querySelectorAll('pre > code');
                            if (codeBlocks.length) {
                                codeBlocks.forEach(codeEl => {
                                    const preEl = codeEl.parentElement;
                                    // 跳过非PRE标签或已包裹的代码块
                                    if (preEl.tagName !== 'PRE' || preEl.parentElement?.classList.contains('prism-wrap')) return;
                                    
                                    // 先应用Prism行号
                                    preEl.classList.add('line-numbers');
                                    
                                    // 手动创建line-numbers-rows元素
                                    const codeText = codeEl.innerText;
                                    const lineCount = codeText.split('\n').length;
                                    const lineNumbersRows = document.createElement('span');
                                    lineNumbersRows.className = 'line-numbers-rows';
                                    lineNumbersRows.setAttribute('aria-hidden', 'true');
                                    lineNumbersRows.innerHTML = new Array(lineCount).fill('<span></span>').join('');
                                    codeEl.appendChild(lineNumbersRows);
                                    
                                    // 创建包裹容器
                                    const wrapEl = document.createElement('div');
                                    wrapEl.classList.add('prism-wrap');
                                    // 通过模板字符串生成toolbar内容
                                    const langClass = Array.from(codeEl.classList).find(cls => cls.startsWith('language-'));
                                    const langText = langClass ? langClass.replace('language-', '').toUpperCase() : 'CODE';
                                    const toolbarHtml = `
                                      <div class="prism-toolbar">
                                        <span class="prism-lang"><span></span><span></span><span></span>${langText}</span>
                                        <div class="prism-toolbar-btns">
                                          <span class="prism-collapse" aria-label="折叠">折叠 </span>
                                          <span class="prism-copy" aria-label="复制">复制</span>
                                        </div>
                                      </div>
                                    `;
                                    
                                    // 插入toolbar到DOM
                                    wrapEl.insertAdjacentHTML('beforeend', toolbarHtml);
                                    // 获取生成的按钮元素并绑定事件
                                    const toolbarEl = wrapEl.querySelector('.prism-toolbar');
                                    const collapseBtn = toolbarEl.querySelector('.prism-collapse');
                                    const copyBtn = toolbarEl.querySelector('.prism-copy');

                                    // 绑定折叠事件
                                    collapseBtn.addEventListener('click', () => {
                                        const isCollapsed = wrapEl.classList.toggle('prism-collapsed');
                                        collapseBtn.textContent = isCollapsed ? '展开' : '折叠';
                                        collapseBtn.setAttribute('aria-label', isCollapsed ? '展开' : '折叠');
                                    });
                                    // 绑定复制事件
                                    copyBtn.addEventListener('click', async () => {
                                        try {
                                            // 处理代码文本（去除末尾空格、过滤指定文本行）
                                            const codeText = codeEl.innerText.split('\n')
                                              .map(line => line.trimEnd())
                                              .filter(line => {
                                                const trimmedLine = line.trim();
                                                return trimmedLine && !['封面', '最新文章', '首页'].some(txt => trimmedLine.includes(txt));
                                              })
                                              .join('\n');

                                            await navigator.clipboard.writeText(codeText);
                                            const originalText = copyBtn.textContent;
                                            copyBtn.textContent = '复制成功';
                                            setTimeout(() => {
                                                copyBtn.textContent = originalText;
                                            }, 2000);
                                        } catch (err) {
                                            console.error('复制失败：', err);
                                            const originalText = copyBtn.textContent;
                                            copyBtn.textContent = '复制失败';
                                            setTimeout(() => {
                                                copyBtn.textContent = originalText;
                                            }, 2000);
                                        }
                                    });

                                    // 插入包裹容器并移动pre元素
                                    preEl.parentNode.insertBefore(wrapEl, preEl);
                                    wrapEl.appendChild(preEl);
                                    
                                    // 添加必要的CSS样式
                                    const style = document.createElement('style');
                                    style.textContent = `
                                        .prism-wrap .line-numbers-rows {
                                            position: absolute;
                                            pointer-events: none;
                                            top: 0;
                                            font-size: 100%;
                                            left: -3.8em;
                                            width: 3em;
                                            letter-spacing: -1px;
                                            border-right: 1px solid #999;
                                            -webkit-user-select: none;
                                            -moz-user-select: none;
                                            -ms-user-select: none;
                                            user-select: none;
                                        }
                                        .prism-wrap .line-numbers-rows > span {
                                            display: block;
                                            counter-increment: linenumber;
                                        }
                                        .prism-wrap .line-numbers-rows > span:before {
                                            content: counter(linenumber);
                                            color: #999;
                                            display: block;
                                            padding-right: 0.8em;
                                            text-align: right;
                                        }
                                    `;
                                    document.head.appendChild(style);
                                });
                            }
                        }
                    } else {
                        contentArea.innerHTML = '<div class="error">加载失败：HTTP状态码 ' + xhr.status + '</div>';
                    }
            };
            xhr.ontimeout = function() {
                contentArea.innerHTML = '<div class="error">加载超时：请求超过3秒未响应</div>';
            };
            xhr.onerror = function() {
                contentArea.innerHTML = '<div class="error">加载失败：网络错误</div>';
            };
            xhr.send();
        }

        // 加载文件内容的函数
        function loadFile(path) {
            // 清除选中状态
            document.querySelectorAll('.tree-item').forEach(item => item.classList.remove('active'));
            // 高亮当前文件
            const fileElement = document.querySelector(`.tree-file[data-path="${path}"]`);
            if (fileElement) {
                fileElement.classList.add('active');
            }
            
            // 关闭移动端侧边栏
            if (window.innerWidth <= 768) {
                document.querySelector('.sidebar').classList.remove('open');
            }
            
            // 检查文件扩展名
            const fileExtension = path.split('.').pop().toLowerCase();
            
            // 如果是md文件，使用AJAX加载
            if (fileExtension === 'md') {
                // 显示加载状态
                const contentArea = document.getElementById('contentArea');
                contentArea.innerHTML = '<div class="loading"><i class="fas fa-spinner"></i><p>加载中...</p></div>';
                
                // 中止之前的请求
                if (pendingXHR) {
                    pendingXHR.abort();
                    pendingXHR = null;
                }
                
                // 使用AJAX加载文件内容（增加超时和错误处理）
                const xhr = new XMLHttpRequest();
                xhr.open('GET', '?file=' + encodeURIComponent(path), true);
                xhr.timeout = 3000; // 前端超时3秒
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(xhr.responseText, 'text/html');
                        const newContent = doc.querySelector('.content').innerHTML;
                        contentArea.innerHTML = newContent;
                        // 更新地址栏
                        history.pushState({path: path}, '', '?file=' + encodeURIComponent(path));
                        // 为新生成的文件项添加点击事件
                        addFileItemListeners();
                        // 应用Prism代码高亮
                        if (typeof Prism !== 'undefined') {
                            Prism.highlightAll();
                            // 手动初始化prism-tool.js功能
                            const codeBlocks = document.querySelectorAll('pre > code');
                            if (codeBlocks.length) {
                                codeBlocks.forEach(codeEl => {
                                    const preEl = codeEl.parentElement;
                                    // 跳过非PRE标签或已包裹的代码块
                                    if (preEl.tagName !== 'PRE' || preEl.parentElement?.classList.contains('prism-wrap')) return;
                                    
                                    // 先应用Prism行号
                                    preEl.classList.add('line-numbers');
                                    
                                    // 手动创建line-numbers-rows元素
                                    const codeText = codeEl.innerText;
                                    const lineCount = codeText.split('\n').length;
                                    const lineNumbersRows = document.createElement('span');
                                    lineNumbersRows.className = 'line-numbers-rows';
                                    lineNumbersRows.setAttribute('aria-hidden', 'true');
                                    lineNumbersRows.innerHTML = new Array(lineCount).fill('<span></span>').join('');
                                    codeEl.appendChild(lineNumbersRows);
                                    
                                    // 创建包裹容器
                                    const wrapEl = document.createElement('div');
                                    wrapEl.classList.add('prism-wrap');
                                    // 通过模板字符串生成toolbar内容
                                    const langClass = Array.from(codeEl.classList).find(cls => cls.startsWith('language-'));
                                    const langText = langClass ? langClass.replace('language-', '').toUpperCase() : 'CODE';
                                    const toolbarHtml = `
                                      <div class="prism-toolbar">
                                        <span class="prism-lang"><span></span><span></span><span></span>${langText}</span>
                                        <div class="prism-toolbar-btns">
                                          <span class="prism-collapse" aria-label="折叠">折叠 </span>
                                          <span class="prism-copy" aria-label="复制">复制</span>
                                        </div>
                                      </div>
                                    `;
                                    
                                    // 插入toolbar到DOM
                                    wrapEl.insertAdjacentHTML('beforeend', toolbarHtml);
                                    // 获取生成的按钮元素并绑定事件
                                    const toolbarEl = wrapEl.querySelector('.prism-toolbar');
                                    const collapseBtn = toolbarEl.querySelector('.prism-collapse');
                                    const copyBtn = toolbarEl.querySelector('.prism-copy');

                                    // 绑定折叠事件
                                    collapseBtn.addEventListener('click', () => {
                                        const isCollapsed = wrapEl.classList.toggle('prism-collapsed');
                                        collapseBtn.textContent = isCollapsed ? '展开' : '折叠';
                                        collapseBtn.setAttribute('aria-label', isCollapsed ? '展开' : '折叠');
                                    });
                                    // 绑定复制事件
                                    copyBtn.addEventListener('click', async () => {
                                        try {
                                            // 处理代码文本（去除末尾空格、过滤指定文本行）
                                            const codeText = codeEl.innerText.split('\n')
                                              .map(line => line.trimEnd())
                                              .filter(line => {
                                                const trimmedLine = line.trim();
                                                return trimmedLine && !['封面', '最新文章', '首页'].some(txt => trimmedLine.includes(txt));
                                              })
                                              .join('\n');

                                            await navigator.clipboard.writeText(codeText);
                                            const originalText = copyBtn.textContent;
                                            copyBtn.textContent = '复制成功';
                                            setTimeout(() => {
                                                copyBtn.textContent = originalText;
                                            }, 2000);
                                        } catch (err) {
                                            console.error('复制失败：', err);
                                            const originalText = copyBtn.textContent;
                                            copyBtn.textContent = '复制失败';
                                            setTimeout(() => {
                                                copyBtn.textContent = originalText;
                                            }, 2000);
                                        }
                                    });

                                    // 插入包裹容器并移动pre元素
                                    preEl.parentNode.insertBefore(wrapEl, preEl);
                                    wrapEl.appendChild(preEl);
                                    
                                    // 添加必要的CSS样式
                                    const style = document.createElement('style');
                                    style.textContent = `

                                        .prism-wrap .line-numbers-rows {
                                            position: absolute;
                                            pointer-events: none;
                                            top: 0;
                                            font-size: 100%;
                                            left: -3.8em;
                                            width: 3em;
                                            letter-spacing: -1px;
                                            border-right: 1px solid #999;
                                            -webkit-user-select: none;
                                            -moz-user-select: none;
                                            -ms-user-select: none;
                                            user-select: none;
                                        }
                                        .prism-wrap .line-numbers-rows > span {
                                            display: block;
                                            counter-increment: linenumber;
                                        }
                                        .prism-wrap .line-numbers-rows > span:before {
                                            content: counter(linenumber);
                                            color: #999;
                                            display: block;
                                            padding-right: 0.8em;
                                            text-align: right;
                                        }
                                    `;
                                    document.head.appendChild(style);
                                });
                            }
                        }
                        
                        // 生成TOC目录
                        generateTOC();
                    } else {
                        contentArea.innerHTML = '<div class="error">加载失败：HTTP状态码 ' + xhr.status + '</div>';
                    }
                    // 清除pendingXHR
                    if (pendingXHR === xhr) {
                        pendingXHR = null;
                    }
                };
                // 超时处理
                xhr.ontimeout = function() {
                    contentArea.innerHTML = '<div class="error">加载超时：请求超过3秒未响应</div>';
                    // 清除pendingXHR
                    if (pendingXHR === xhr) {
                        pendingXHR = null;
                    }
                };
                // 网络错误处理
                xhr.onerror = function() {
                    contentArea.innerHTML = '<div class="error">加载失败：网络错误</div>';
                    // 清除pendingXHR
                    if (pendingXHR === xhr) {
                        pendingXHR = null;
                    }
                };
                // 中止处理
                xhr.onabort = function() {
                    // 清除pendingXHR
                    if (pendingXHR === xhr) {
                        pendingXHR = null;
                    }
                };
                xhr.send();
                pendingXHR = xhr;
                return xhr;
            } else {
                // 非md文件，使用file.kkview.cn在线预览服务
                const contentArea = document.getElementById('contentArea');
                // 获取当前页面的基础URL
                const baseUrl = window.location.origin + window.location.pathname;
                // 构建完整的文件URL
                const fileUrl = baseUrl.replace('index.php', '') + path;
                // 对文件URL进行base64编码
                const base64Url = btoa(unescape(encodeURIComponent(fileUrl)));
                // 使用file.kkview.cn在线预览服务
                contentArea.innerHTML = '<iframe src="https://file.kkview.cn/onlinePreview?url=' + base64Url + '&key=000"></iframe>';
                // 更新地址栏
                history.pushState({path: path}, '', '?file=' + encodeURIComponent(path));
            }
        }
        
        // 为文件项添加点击事件
        function addFileItemListeners() {
            // 为markdown文件项添加点击事件
            document.querySelectorAll('.file-item.md-item').forEach(item => {
                item.addEventListener('click', () => {
                    const filePath = item.dataset.path;
                    loadFile(filePath);
                });
            });
            
            // 为其他文件项添加点击事件（使用file.kkview.cn在线预览服务）
            document.querySelectorAll('.file-item.other-item').forEach(item => {
                item.addEventListener('click', () => {
                    const filePath = item.dataset.path;
                    
                    // 显示iframe
                    const contentArea = document.getElementById('contentArea');
                    // 获取当前页面的基础URL
                    const baseUrl = window.location.origin + window.location.pathname;
                    // 构建完整的文件URL
                    const fileUrl = baseUrl.replace('index.php', '') + filePath;
                    // 对文件URL进行base64编码
                    const base64Url = btoa(unescape(encodeURIComponent(fileUrl)));
                    // 使用file.kkview.cn在线预览服务
                    contentArea.innerHTML = '<iframe src="https://file.kkview.cn/onlinePreview?url=' + base64Url + '&key=000"></iframe>';
                    
                    // 更新地址栏
                    history.pushState({path: filePath}, '', '?file=' + encodeURIComponent(filePath));
                });
            });
            
            // 为文件夹项添加点击事件
            document.querySelectorAll('.file-item.folder-item').forEach(item => {
                item.addEventListener('click', () => {
                    const folderPath = item.dataset.path;
                    showFolderContent(folderPath);
                });
            });
            
            // 初始化lightgallery
            if (document.getElementById('lightgallery')) {
                lightGallery(document.getElementById('lightgallery'), {
                    selector: '.image-item',
                    speed: 500,
                    download: true,
                    zoom: true,
                    fullScreen: true,
                    thumbnail: true,
                    animateThumb: true,
                    showThumbByDefault: true,
                    controls: true,
                    showNav: true,
                    showCloseIcon: true,
                    counter: true,
                    thumbPosition: 'bottom',
                    thumbWidth: 100,
                    thumbHeight: 70,
                    thumbMargin: 10,
                    exThumbImage: 'data-src',
                    autoplay: false,
                    share: true,
                    actualSize: true
                });
            }
        }
        
        // 页面加载时为文件项添加点击事件
        window.addEventListener('DOMContentLoaded', function() {
            addFileItemListeners();
        });

        // 点击文件加载内容（优化版）
        document.querySelectorAll('.tree-file').forEach(file => {
            file.addEventListener('click', (e) => {
                e.stopPropagation();
                const path = file.dataset.path;
                loadFile(path);
            });
        });

        // 处理浏览器前进后退
        window.addEventListener('popstate', (e) => {
            if (e.state && e.state.path) {
                loadFile(e.state.path);
            }
        });

        // 页面加载时根据URL参数加载文件
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const fileParam = urlParams.get('file');
            if (fileParam) {
                // 尝试加载URL参数指定的文件
                const fileElement = document.querySelector(`.tree-file[data-path="${fileParam}"]`);
                if (fileElement) {
                    // 展开包含该文件的所有父文件夹
                    let parent = fileElement.parentElement;
                    while (parent && parent.classList.contains('tree-children')) {
                        parent.classList.add('open');
                        const folder = parent.previousElementSibling;
                        if (folder && folder.classList.contains('tree-folder')) {
                            folder.classList.add('open');
                            const folderIcon = folder.querySelector('.fas.fa-folder');
                            if (folderIcon) {
                                folderIcon.classList.remove('fa-folder');
                                folderIcon.classList.add('fa-folder-open');
                            }
                        }
                        parent = parent.parentElement.parentElement;
                    }
                    loadFile(fileParam);
                }
            }
        });
        
        // 生成TOC目录
        function generateTOC() {
            // 清除旧的TOC
            const oldTOC = document.querySelector('.toc-container');
            if (oldTOC) {
                oldTOC.remove();
            }
            
            // 获取内容区域中的标题
            const headings = document.querySelectorAll('.content h1, .content h2, .content h3, .content h4, .content h5, .content h6');
            
            // 如果没有标题，不生成TOC
            if (headings.length === 0) {
                return;
            }
            
            // 创建TOC容器
            const tocContainer = document.createElement('div');
            tocContainer.className = 'toc-container';
            
            // 获取当前激活的tree-file元素
            const activeFile = document.querySelector('.tree-file.active');
            
            // 继承activeFile的--level变量
            if (activeFile) {
                const level = activeFile.style.getPropertyValue('--level');
                if (level) {
                    tocContainer.style.setProperty('--level', level);
                }
            }
            
            tocContainer.innerHTML = '<ul class="toc-list"></ul>';
            
            // 获取TOC列表
            const tocList = tocContainer.querySelector('.toc-list');
            
            // 为每个标题生成TOC项
            headings.forEach((heading, index) => {
                // 为标题添加id（如果没有的话）
                if (!heading.id) {
                    heading.id = 'heading-' + index;
                }
                
                // 创建TOC项
                const tocItem = document.createElement('li');
                tocItem.className = 'toc-item';
                
                // 继承容器的--level变量
                const level = tocContainer.style.getPropertyValue('--level');
                if (level) {
                    tocItem.style.setProperty('--level', level);
                }
                
                // 创建TOC链接
                const tocLink = document.createElement('a');
                tocLink.href = '#' + heading.id;
                tocLink.textContent = heading.textContent;
                tocLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    document.getElementById(heading.id).scrollIntoView({ behavior: 'smooth' });
                });
                
                // 添加到TOC列表
                tocItem.appendChild(tocLink);
                tocList.appendChild(tocItem);
            });
            
            // 将TOC添加到当前激活的tree-file元素之后
            if (activeFile) {
                // 检查activeFile后面是否已经有tree-children
                const nextSibling = activeFile.nextElementSibling;
                if (nextSibling && nextSibling.classList.contains('tree-children')) {
                    // 如果有tree-children，将TOC插入到tree-children之前
                    activeFile.parentNode.insertBefore(tocContainer, nextSibling);
                } else {
                    // 如果没有tree-children，直接将TOC插入到activeFile之后
                    activeFile.parentNode.insertBefore(tocContainer, activeFile.nextSibling);
                }
            } else {
                // 如果没有激活的文件，将TOC添加到侧边栏底部
                const sidebar = document.querySelector('.sidebar');
                sidebar.appendChild(tocContainer);
            }
        }
        
        // 侧边栏切换功能
        document.getElementById('sidebarToggle').addEventListener('click', () => {
            const sidebar = document.querySelector('.sidebar');
            if (window.innerWidth <= 768) {
                // 移动端：切换 open 类
                sidebar.classList.toggle('open');
            } else {
                // 电脑端：切换 hidden 类
                sidebar.classList.toggle('hidden');
            }
        });
        
        // 点击内容区域关闭侧边栏（仅移动端）
        document.querySelector('.content').addEventListener('click', (e) => {
            // 确保点击的不是文件项
            if (!e.target.closest('.file-item')) {
                const sidebar = document.querySelector('.sidebar');
                if (window.innerWidth <= 768 && sidebar.classList.contains('open')) {
                    sidebar.classList.remove('open');
                }
            }
        });

        // 搜索功能
        const searchInput = document.getElementById('searchInput');
        const searchButton = document.getElementById('searchButton');
        const searchResults = document.getElementById('searchResults');
        
        // 搜索逻辑
        function searchFiles(query) {
            if (!query.trim()) {
                searchResults.classList.remove('show');
                return;
            }
            
            // 显示加载状态
            searchResults.innerHTML = '<div class="search-result-item">搜索中...</div>';
            searchResults.classList.add('show');
            
            // 发送AJAX请求搜索文件内容
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '?action=search', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const results = JSON.parse(xhr.responseText);
                        displaySearchResults(results);
                    } catch (e) {
                        console.error('搜索结果解析失败:', e);
                        searchResults.innerHTML = '<div class="search-result-item">搜索失败</div>';
                    }
                } else {
                    searchResults.innerHTML = '<div class="search-result-item">搜索失败</div>';
                }
            };
            xhr.onerror = function() {
                searchResults.innerHTML = '<div class="search-result-item">网络错误</div>';
            };
            xhr.send('query=' + encodeURIComponent(query));
        }
        
        // 显示搜索结果
        function displaySearchResults(results) {
            searchResults.innerHTML = '';
            
            if (results.length === 0) {
                const noResult = document.createElement('div');
                noResult.className = 'search-result-item';
                noResult.textContent = '没有找到匹配的文件';
                searchResults.appendChild(noResult);
            } else {
                results.forEach(result => {
                    const item = document.createElement('div');
                    item.className = 'search-result-item';
                    
                    const icon = document.createElement('i');
                    if (result.type === 'file') {
                        const extension = result.name.split('.').pop().toLowerCase();
                        let iconClass = 'fas fa-file-alt';
                        
                        switch (extension) {
                            case 'doc':
                            case 'docx':
                                iconClass = 'fas fa-file-word';
                                break;
                            case 'ppt':
                            case 'pptx':
                                iconClass = 'fas fa-file-powerpoint';
                                break;
                            case 'xls':
                            case 'xlsx':
                                iconClass = 'fas fa-file-excel';
                                break;
                            case 'pdf':
                                iconClass = 'fas fa-file-pdf';
                                break;
                            case 'md':
                                iconClass = 'fab fa-markdown';
                                break;
                            case 'js':
                            case 'css':
                                iconClass = 'fas fa-file-code';
                                break;
                            case 'txt':
                                iconClass = 'fas fa-file-alt';
                                break;
                            case 'jpg':
                            case 'jpeg':
                            case 'png':
                            case 'gif':
                            case 'webp':
                                iconClass = 'fas fa-file-image';
                                break;
                        }
                        
                        icon.className = iconClass;
                    } else {
                        icon.className = 'fas fa-folder';
                    }
                    item.appendChild(icon);
                    
                    const name = document.createTextNode(result.name);
                    item.appendChild(name);
                    
                    // 添加匹配信息
                    if (result.type === 'file' && (result.matchInName || result.matchInContent)) {
                        const matchInfo = document.createElement('span');
                        matchInfo.style.fontSize = '12px';
                        matchInfo.style.color = 'var(--color-text-light)';
                        matchInfo.style.marginLeft = '8px';
                        
                        if (result.matchInName && result.matchInContent) {
                            matchInfo.textContent = '(文件名和内容匹配)';
                        } else if (result.matchInName) {
                            matchInfo.textContent = '(文件名匹配)';
                        } else {
                            matchInfo.textContent = '(内容匹配)';
                        }
                        
                        item.appendChild(matchInfo);
                    }
                    
                    // 点击跳转到对应文件
                    item.addEventListener('click', () => {
                        if (result.type === 'file') {
                            loadFile(result.path);
                        } else {
                            showFolderContent(result.path);
                        }
                        searchResults.classList.remove('show');
                        searchInput.value = '';
                    });
                    
                    searchResults.appendChild(item);
                });
            }
            
            searchResults.classList.add('show');
        }
        
        // 搜索按钮点击事件
        searchButton.addEventListener('click', () => {
            searchFiles(searchInput.value);
        });
        
        // 搜索输入框键盘事件
        searchInput.addEventListener('keyup', (e) => {
            if (e.key === 'Enter') {
                searchFiles(searchInput.value);
            } else if (e.key === 'Escape') {
                searchResults.classList.remove('show');
            } else {
                // 防抖处理
                clearTimeout(window.searchTimeout);
                window.searchTimeout = setTimeout(() => {
                    searchFiles(searchInput.value);
                }, 300);
            }
        });
        
        // 点击页面其他地方关闭搜索结果
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.search-box')) {
                searchResults.classList.remove('show');
            }
        });
        
        // 优化：阻止AJAX请求重复发送（防抖）
        let pendingXHR = null;
    </script>
    <script src="/prism/prism-tool.js"></script>	
    <script src="/prism/prism.js"></script>

</body>
</html>
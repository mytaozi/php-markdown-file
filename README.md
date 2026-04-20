# 文档浏览系统

一个基于PHP的轻量级文档浏览系统，支持Markdown文件查看、远程内容包含、图片预览等功能。

## 功能特性

- 📁 目录树导航，支持无限级嵌套目录
- 📄 Markdown文件解析与渲染
- 🔗 远程内容包含功能（`[include:url]`）
- 🖼️ 图片预览功能
- 📊 文件列表网格布局
- 🔒 登录认证系统
- 📱 响应式设计，支持移动端
- 🎨 现代化UI设计，浅蓝色主题
- 🚀 高性能，支持远程内容缓存
- 🛡️ 安全防护，防止目录穿越攻击
- 📱 侧边栏切换功能，电脑端和移动端都支持
- 📄 可配置是否显示文件后缀名
- 🎨 统一的iframe样式管理
- 🖼️ 支持lightgallery.js灯箱插件，提供更好的图片预览体验

## 安装步骤

1. **克隆仓库**
   ```bash
   git clone <repository-url>
   cd <repository-directory>
   ```

2. **配置环境**
   - 确保服务器安装了PHP 5.5或更高版本
   - 确保启用了`allow_url_fopen`或`curl`扩展（用于远程内容包含）

3. **配置系统**
   编辑`index.php`文件，修改以下配置：
   ```php
   // 登录配置
   $loginEnabled = true; // 是否启用登录
   $adminUsername = 'admin'; // 管理员用户名
   $adminPassword = '123456'; // 管理员密码
   
   // 显示配置
   $showFileExtension = false; // 是否显示文件后缀名
   ```

4. **启动服务器**
   - 使用内置PHP服务器：
     ```bash
     php -S localhost:8000
     ```
   - 或部署到Apache/Nginx等Web服务器

5. **访问系统**
   打开浏览器访问：`http://localhost:8000/index.php`

## 使用方法

### 基本操作
- **浏览目录**：点击左侧目录树中的文件夹
- **查看文件**：点击左侧目录树中的Markdown文件
- **预览图片**：点击文件夹中的图片缩略图，会使用lightgallery.js灯箱插件打开，支持图片导航、缩放、全屏等功能
- **退出系统**：点击右上角的"登出"按钮
- **切换侧边栏**：点击顶部的菜单按钮（三横线图标）可以在电脑端隐藏/显示侧边栏，在移动端显示/隐藏侧边栏

### Markdown扩展语法

1. **远程内容包含**
   - **使用方法**：
     ```markdown
     [include:https://example.com/file.md]
     [include:https://example.com/file.md lines=10]  # 只包含前10行
     [include:https://example.com/file.md range=1-20]  # 包含第1-20行
     [include:https://example.com/file.md tail=5]  # 只包含最后5行
     [include:https://example.com/file.md from="start" to="end"]  # 包含从start到end的内容
     ```
   - **解析效果**：远程文件的内容会被嵌入到当前Markdown文件中，并按照Markdown格式渲染。

2. **文件嵌入**
   - **使用方法**：
     ```markdown
     [file:https://example.com/document.pdf]
     [file:https://example.com/document.pdf height=600px]  # 设置高度为600px
     ```
   - **解析效果**：生成一个iframe标签，使用第三方在线预览服务（file.kkview.cn）显示文件内容，默认宽度为100%，高度为`calc(100vh - 120px)`。

### Parsedown.php支持

- **有Parsedown.php**：
  - 提供更高级的Markdown解析功能
  - 支持更多Markdown语法特性
  - 渲染效果更美观

- **没有Parsedown.php**：
  - 使用内置的简单Markdown解析器
  - 支持基本的Markdown语法（标题、加粗、斜体、列表、代码块、链接）
  - 功能有限但足够使用

> 提示：如果需要更完整的Markdown支持，建议下载Parsedown.php并放在同一目录下。

## 目录结构

```html
├── index.php        # 主程序文件
├── README.md        # 本文档
└── Parsedown.php    # Markdown解析器（可选）
```

## 注意事项

1. **安全设置**
   - 默认登录凭据为 `admin` / `password`，请在生产环境中修改
   - 系统已内置目录穿越防护

2. **性能优化**
   - 远程内容会被缓存，提高重复访问速度
   - 所有请求都有超时限制，防止系统卡死

3. **依赖项**
   - 可选：`Parsedown.php` - 提供更高级的Markdown解析
   - 外部：Font Awesome图标库（通过CDN加载）

## 技术栈

- **后端**：PHP 5.5+
- **前端**：HTML5, CSS3, JavaScript
- **Markdown解析**：内置简单解析器或Parsedown
- **UI框架**：纯CSS（无依赖）

## 许可证

MIT License

## 贡献

欢迎提交Issue和Pull Request来改进这个项目！
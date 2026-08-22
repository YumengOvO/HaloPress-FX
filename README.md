# HaloPress-FX

中文 | [English](README.en.md)

[![Build](https://github.com/YumengOvO/HaloPress-FX/actions/workflows/build.yml/badge.svg)](https://github.com/YumengOvO/HaloPress-FX/actions/workflows/build.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759b?logo=wordpress)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4?logo=php&logoColor=white)](https://www.php.net/)

HaloPress-FX 是一款 WordPress 前台点击动画插件，为网站添加《蔚蓝档案》风格的点击光环、粒子碎片和光标拖尾。插件基于 [`ba-click-fx`](https://github.com/CialloKing/ba-click-fx) 动画引擎封装，无需修改主题，安装启用后即可全站生效。

[![访客计数](https://count.yumengovo.com/@HaloPress-FX)](https://count.yumengovo.com/@HaloPress-FX)

## 功能特点

- 鼠标点击与触摸轻点时显示光环和粒子动画
- 鼠标按住拖动时显示连续光标拖尾
- 可选“移动时始终显示拖尾”
- 点击动画与拖尾可以独立开关
- 支持主题颜色、尺寸、透明度及动画速度调整
- 提供性能优先、平衡和高清三档画质预设
- 提供 WebGL2、WebGPU、Canvas 2D 和 Bloom 高级设置
- WebGL2 不可用时自动回退到兼容渲染路径
- 无活动动画时停止逐帧渲染，减少空闲资源占用
- Canvas 不参与页面命中测试，不会遮挡菜单、链接或表单

## WordPress 集成规则

- 动画仅在网站前台加载
- 不在 `wp-admin` 后台加载
- 不在 WordPress 登录、注册和找回密码页面加载
- 默认全站启用，无需编辑主题文件
- 兼容传统主题和区块主题

### 移动端行为

移动端默认保留浏览器原生滚动和缩放：

- 触摸轻点仍会显示点击动画
- 正常滑动不会强制维持完整拖尾
- 自动忽略“移动时始终显示拖尾”设置
- 使用 `touch-action: auto`，不会抢占页面滚动手势

### 移动端浏览器滑动时为什么没有轨迹拖尾？

HaloPress-FX 默认优先保留 WordPress 页面的原生滚动。浏览器接管触摸滑动后可能发送 `pointercancel`，当前拖尾会随之结束，但轻点动画仍然可用。

底层动画引擎支持通过 `touchAction: 'none'` 禁止默认手势，从而在任意滑动方向持续显示拖尾；HaloPress-FX 不启用该模式，因为它会阻止页面的正常滚动和缩放。插件前台始终使用 `touch-action: auto`。

## 环境要求

- WordPress 6.0 或更高版本
- PHP 7.4 或更高版本
- 支持现代 Canvas API 的浏览器
- 推荐启用 WebGL2 以获得完整视觉效果

WordPress 服务器运行插件时不需要安装 Node.js。Node.js 仅用于从源码构建动画资源和安装包。

## 安装方法

### 使用发布包

1. 从 GitHub Releases 下载 `halopress-fx-x.y.z.zip`。
2. 登录 WordPress 后台。
3. 打开“插件 → 安装插件 → 上传插件”。
4. 选择 ZIP 文件并完成安装。
5. 启用 HaloPress-FX。
6. 前往“设置 → HaloPress-FX”调整动画。

发布 ZIP 中的 `halopress-fx.php`、`assets/` 和 `includes/` 直接位于压缩包根目录，可以由 WordPress 直接识别。

### 从源码构建

需要 Node.js 18 或更高版本：

```bash
git clone https://github.com/YumengOvO/HaloPress-FX.git
cd HaloPress-FX
npm ci
npm run package:wordpress
```

生成的安装包位于：

```text
releases/halopress-fx-0.1.3.zip
```

## 后台设置

插件设置页位于“设置 → HaloPress-FX”。

### 基础设置

- 总开关
- 点击特效开关
- 光标拖尾开关
- 移动时始终显示拖尾
- 移动端开关
- 主题颜色
- 特效大小
- 整体透明度
- 点击动画速度
- 拖尾消散速度
- 画质预设
- 恢复默认设置

### 高级设置

高级参数默认折叠，适合需要自行控制渲染和性能的用户：

- 特效渲染后端
- Bloom 后端
- 渲染模式
- 最大设备像素比（DPR）
- 主题颜色映射
- 输出合成方式
- 覆盖层透明度和色彩补偿
- 宿主混合模式
- 浅色背景轮廓
- 输入采样率
- WebGPU HDR 偏好

### 动画引擎兼容说明

HaloPress-FX 保留了上游动画引擎的高级配置语义：

- 当前内置引擎对应 `ba-click-fx@1.2.29`；若单独开发动画引擎，可使用 `npm install ba-click-fx@1.2.29` 安装相同版本。
- `maxDpr` 表示最大设备像素比，默认 1；高清预设会显式提高该值。
- `inputSamplingRate` 接受 `0` 或 `1..1000` Hz，`0` 表示不限频；例如 `inputSamplingRate: 30` 会把移动输入限制为每秒 30 次采样。
- WebGPU 可用不代表 HDR 已启用，只有 `resolvedWebGPUOutputMode === 'extended'` 才说明浏览器成功建立了 `rgba16float + toneMapping: extended` 输出。
- 展示页的“UI HDR”是演示站点私有功能，不属于 WordPress 插件前台功能；相关 CSS 能力检测可能涉及 `dynamic-range-limit: no-limit`。
- 颜色映射支持兼容模式 `hue-only` 和相对映射模式 `relative-oklch`。

## 开发命令

```bash
# 启动上游动画演示页
npm run dev

# 构建动画引擎并同步到 WordPress 插件
npm run build:wordpress

# 验证插件文件和移动端初始化行为
npm run test:wordpress

# 生成可上传 WordPress 的 ZIP
npm run package:wordpress

# 执行动画引擎快速回归测试
npm run check:fast
```

打包脚本会检查 ZIP 根目录，若插件入口被错误包在额外的 `halopress-fx/` 文件夹内，构建会直接失败。

## 项目结构

```text
HaloPress-FX/
├── src/                              # ba-click-fx 动画引擎源码
├── scripts/                          # 引擎和 WordPress 插件构建脚本
├── test/                             # 动画引擎与插件行为测试
├── wordpress/
│   └── halopress-fx/
│       ├── halopress-fx.php          # WordPress 插件入口
│       ├── includes/
│       │   ├── class-halopress-fx-admin.php
│       │   ├── class-halopress-fx-frontend.php
│       │   └── class-halopress-fx-settings.php
│       ├── assets/
│       │   ├── css/admin.css
│       │   └── js/
│       │       ├── admin.js
│       │       ├── ba-click-fx.iife.js
│       │       └── halopress-fx.js
│       ├── readme.txt
│       ├── LICENSE
│       └── THIRD_PARTY_NOTICES.md
└── package.json
```

## 安全与配置处理

- 设置页仅允许具有 `manage_options` 权限的管理员访问
- 使用 WordPress Settings API 和 Nonce 保护保存请求
- 所有布尔值、数值、颜色和枚举配置均在服务端清洗和限制范围
- 前台配置通过 `wp_json_encode()` 安全传递给初始化脚本
- 插件不会请求远程动画资源，运行时资源随插件本地加载

## 上游项目与署名

HaloPress-FX 的动画核心来源于 CialloKing 的 [`ba-click-fx`](https://github.com/CialloKing/ba-click-fx)，并保留其 MIT 许可证和第三方声明。

`ba-click-fx` 的早期版本曾参考以下 MIT 许可项目：

- [`DoomVoss/BASpark`](https://github.com/DoomVoss/BASpark)
- [`VanillaNahida/BA-Spark-Cursor`](https://github.com/VanillaNahida/BA-Spark-Cursor)

完整说明请参阅 [THIRD_PARTY_NOTICES.md](THIRD_PARTY_NOTICES.md) 以及插件目录中的第三方声明。

## 许可证

本项目使用 [MIT License](LICENSE) 发布。

《蔚蓝档案》及相关视觉元素的商标和版权归其各自权利人所有。本项目是非官方开源项目，与游戏开发商或发行商无隶属关系。

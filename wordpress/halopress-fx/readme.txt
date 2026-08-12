=== HaloPress-FX ===
Contributors: halopress-fx
Tags: click effect, cursor trail, animation, canvas, webgl
Requires at least: 6.0
Requires PHP: 7.4
Stable tag: 0.1.0
License: MIT
License URI: https://opensource.org/licenses/MIT

为 WordPress 网站前台添加点击光环、粒子碎片与光标拖尾动画。

== Description ==

HaloPress-FX 将 ba-click-fx 动画引擎封装为无需修改主题的 WordPress 插件。

* 仅在网站前台加载，不进入 wp-admin 或登录页面。
* 点击特效和光标拖尾可以独立开关。
* 支持主题颜色、尺寸、透明度和动画速度设置。
* 提供性能、平衡和高清画质预设。
* 移动端保留原生滚动，轻点仍会显示点击动画。
* 默认使用 WebGL2，并在能力不足时自动回退。

== Installation ==

1. 在 GitHub Releases 下载 `halopress-fx-x.y.z.zip`。
2. 进入 WordPress 后台的“插件 → 安装插件 → 上传插件”。
3. 上传 ZIP 并启用。
4. 在“设置 → HaloPress-FX”中调整动画。

== Frequently Asked Questions ==

= 会影响链接、菜单或表单点击吗？ =

不会。动画 Canvas 不参与页面命中测试。

= 为什么移动端滑动时没有完整拖尾？ =

插件优先保留浏览器原生滚动。触摸轻点会显示动画，页面滑动不会强制抢占手势。

== Changelog ==

= 0.1.0 =

* 首个 WordPress 插件版本。
* 新增前台加载、基础设置、高级设置和移动端滚动兼容。

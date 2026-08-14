# HaloPress-FX

[中文](README.md) | English

[![Build](https://github.com/YumengOvO/HaloPress-FX/actions/workflows/build.yml/badge.svg)](https://github.com/YumengOvO/HaloPress-FX/actions/workflows/build.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759b?logo=wordpress)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4?logo=php&logoColor=white)](https://www.php.net/)

HaloPress-FX is a WordPress frontend click-animation plugin that adds *Blue Archive*-inspired click halos, particle fragments, and cursor trails to your website. It wraps the [`ba-click-fx`](https://github.com/CialloKing/ba-click-fx) animation engine and works across the entire site as soon as it is installed and activated, without requiring theme modifications.

[![Visitor Count](https://count.yumengovo.com/@HaloPress-FX)](https://count.yumengovo.com/@HaloPress-FX)

## Features

- Displays halo and particle animations on mouse clicks and touch taps
- Displays a continuous cursor trail while the pointer is held down and dragged
- Optional “always show trail while moving” mode
- Click animations and cursor trails can be enabled independently
- Adjustable theme color, size, opacity, and animation speed
- Performance, Balanced, and High Quality presets
- Advanced WebGL2, WebGPU, Canvas 2D, and Bloom settings
- Automatically falls back to a compatible rendering path when WebGL2 is unavailable
- Stops frame-by-frame rendering when no animation is active to reduce idle resource usage
- Keeps the canvas out of page hit testing so it never blocks menus, links, or forms

## WordPress Integration

- Loads animations only on the site frontend
- Does not load in the `wp-admin` dashboard
- Does not load on WordPress login, registration, or password-reset pages
- Enabled site-wide by default, with no theme-file edits required
- Compatible with both classic and block themes

### Mobile Behavior

Native browser scrolling and zooming remain enabled on mobile by default:

- Touch taps still display click animations
- Normal swipes do not force a full cursor trail
- The “always show trail while moving” setting is automatically ignored
- Uses `touch-action: auto` and does not capture page-scrolling gestures

### Why does dragging fail to leave a trail in a mobile browser?

HaloPress-FX prioritizes native scrolling on WordPress pages. When the browser takes control of a touch swipe, it may send `pointercancel`, which ends the current trail while leaving tap animations available.

The underlying animation engine can use `touchAction: 'none'` to disable default gestures and keep the trail visible in every swipe direction. HaloPress-FX does not enable this mode because it would prevent normal page scrolling and zooming. The plugin frontend always uses `touch-action: auto`.

## Requirements

- WordPress 6.0 or later
- PHP 7.4 or later
- A browser with modern Canvas API support
- WebGL2 is recommended for the full visual experience

Node.js is not required on the WordPress server when running the plugin. It is used only to build the animation assets and installation package from source.

## Installation

### Using a Release Package

1. Download `halopress-fx-x.y.z.zip` from GitHub Releases.
2. Sign in to the WordPress dashboard.
3. Open “Plugins → Add New Plugin → Upload Plugin.”
4. Select the ZIP file and complete the installation.
5. Activate HaloPress-FX.
6. Go to “Settings → HaloPress-FX” to customize the animation.

The release ZIP places `halopress-fx.php`, `assets/`, and `includes/` directly in the archive root so WordPress can recognize the plugin immediately.

### Building from Source

Node.js 18 or later is required:

```bash
git clone https://github.com/YumengOvO/HaloPress-FX.git
cd HaloPress-FX
npm ci
npm run package:wordpress
```

The generated installation package is located at:

```text
releases/halopress-fx-0.1.2.zip
```

## Admin Settings

The plugin settings page is available under “Settings → HaloPress-FX.”

### Basic Settings

- Master switch
- Click-effect switch
- Cursor-trail switch
- Always show trail while moving
- Mobile switch
- Theme color
- Effect size
- Overall opacity
- Click-animation speed
- Trail fade speed
- Quality preset
- Restore default settings

### Advanced Settings

Advanced parameters are collapsed by default and are intended for users who want direct control over rendering and performance:

- Effect rendering backend
- Bloom backend
- Rendering mode
- Maximum device pixel ratio (DPR)
- Theme color mapping
- Output compositing method
- Overlay opacity and color compensation
- Host blend mode
- Light-background outline
- Input sampling rate
- WebGPU HDR preference

### Animation Engine Compatibility

HaloPress-FX preserves the advanced configuration semantics of the upstream animation engine:

- The bundled engine currently corresponds to `ba-click-fx@1.2.29`. If you are developing against the animation engine separately, install the matching version with `npm install ba-click-fx@1.2.29`.
- `maxDpr?: number, // default 1` defines the maximum device pixel ratio. The High Quality preset explicitly raises this value.
- `inputSamplingRate` accepts `0` or `1..1000` Hz, where `0` means unlimited. For example, `inputSamplingRate: 30` limits movement input to 30 samples per second.
- WebGPU availability does not mean HDR is enabled. Only `resolvedWebGPUOutputMode === 'extended'` confirms that the browser successfully created an `rgba16float + toneMapping: extended` output.
- The demo's UI HDR controls are demo-only and are not part of the WordPress plugin frontend. Related CSS capability checks may involve `dynamic-range-limit: no-limit`.
- Color mapping supports the compatibility mode `hue-only` and the relative mapping mode `relative-oklch`.

## Development Commands

```bash
# Start the upstream animation demo
npm run dev

# Build the animation engine and sync it to the WordPress plugin
npm run build:wordpress

# Verify plugin files and mobile initialization behavior
npm run test:wordpress

# Generate a ZIP that can be uploaded to WordPress
npm run package:wordpress

# Run the animation engine's fast regression checks
npm run check:fast
```

The packaging script checks the ZIP root and fails the build if the plugin entry point is incorrectly nested inside an extra `halopress-fx/` directory.

## Project Structure

```text
HaloPress-FX/
├── src/                              # ba-click-fx animation engine source
├── scripts/                          # Engine and WordPress plugin build scripts
├── test/                             # Animation engine and plugin behavior tests
├── wordpress/
│   └── halopress-fx/
│       ├── halopress-fx.php          # WordPress plugin entry point
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

## Security and Configuration Handling

- The settings page is accessible only to administrators with the `manage_options` capability
- Saving settings is protected with the WordPress Settings API and nonces
- All Boolean, numeric, color, and enumerated settings are sanitized and constrained on the server
- Frontend configuration is passed safely to the initialization script through `wp_json_encode()`
- The plugin does not request remote animation assets; all runtime assets are bundled and loaded locally

## Upstream Project and Attribution

The animation core in HaloPress-FX comes from CialloKing's [`ba-click-fx`](https://github.com/CialloKing/ba-click-fx) and retains its MIT license and third-party notices.

Early versions of `ba-click-fx` referenced the following MIT-licensed projects:

- [`DoomVoss/BASpark`](https://github.com/DoomVoss/BASpark)
- [`VanillaNahida/BA-Spark-Cursor`](https://github.com/VanillaNahida/BA-Spark-Cursor)

See [THIRD_PARTY_NOTICES.md](THIRD_PARTY_NOTICES.md) and the third-party notice in the plugin directory for full details.

## License

This project is released under the [MIT License](LICENSE).

*Blue Archive* and its related visual elements are trademarks and copyrighted works of their respective owners. This is an unofficial open-source project and is not affiliated with the game's developer or publisher.

import { existsSync, readFileSync, statSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const rootDir = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const pluginDir = join(rootDir, 'wordpress', 'halopress-fx');
const requiredFiles = [
  'halopress-fx.php',
  'includes/class-halopress-fx-settings.php',
  'includes/class-halopress-fx-admin.php',
  'includes/class-halopress-fx-frontend.php',
  'assets/js/ba-click-fx.iife.js',
  'assets/js/halopress-fx.js',
  'assets/js/admin.js',
  'assets/css/admin.css',
  'LICENSE',
  'THIRD_PARTY_NOTICES.md',
  'readme.txt',
];

for (const relativePath of requiredFiles) {
  const absolutePath = join(pluginDir, relativePath);
  if (!existsSync(absolutePath) || statSync(absolutePath).size === 0) {
    throw new Error(`Missing or empty WordPress plugin file: ${relativePath}`);
  }
}

const bootstrap = readFileSync(join(pluginDir, 'halopress-fx.php'), 'utf8');
const frontend = readFileSync(
  join(pluginDir, 'includes', 'class-halopress-fx-frontend.php'),
  'utf8',
);
const initializer = readFileSync(
  join(pluginDir, 'assets', 'js', 'halopress-fx.js'),
  'utf8',
);
const engine = readFileSync(
  join(pluginDir, 'assets', 'js', 'ba-click-fx.iife.js'),
  'utf8',
);

const versionMatch = bootstrap.match(/Version:\s*([0-9]+\.[0-9]+\.[0-9]+)/);
const constantMatch = bootstrap.match(/HALOPRESS_FX_VERSION',\s*'([^']+)'/);
if (!versionMatch || !constantMatch || versionMatch[1] !== constantMatch[1]) {
  throw new Error('Plugin header version and HALOPRESS_FX_VERSION must match.');
}

if (!frontend.includes("add_action('wp_enqueue_scripts'")) {
  throw new Error('Frontend assets must be registered through wp_enqueue_scripts.');
}
if (frontend.includes("add_action('admin_enqueue_scripts'")) {
  throw new Error('Frontend loader must not enqueue the animation in wp-admin.');
}
if (!initializer.includes("touchAction: 'auto'")) {
  throw new Error('The public initializer must preserve native mobile scrolling.');
}
if (!initializer.includes('&& !touchPrimary')) {
  throw new Error('Always-on trails must be disabled for primary touch input.');
}
if (!engine.includes('BAClickFX')) {
  throw new Error('The synchronized engine bundle does not expose BAClickFX.');
}

console.log(`WordPress plugin verified (v${versionMatch[1]}).`);

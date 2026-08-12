import { copyFileSync, existsSync, mkdirSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const rootDir = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const sourceBundle = join(rootDir, 'dist', 'ba-click-fx.iife.js');
const pluginAssetDir = join(
  rootDir,
  'wordpress',
  'halopress-fx',
  'assets',
  'js',
);

if (!existsSync(sourceBundle)) {
  throw new Error(`Missing engine bundle: ${sourceBundle}`);
}

mkdirSync(pluginAssetDir, { recursive: true });
copyFileSync(
  sourceBundle,
  join(pluginAssetDir, 'ba-click-fx.iife.js'),
);

console.log('WordPress engine bundle synchronized.');

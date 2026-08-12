import { existsSync, mkdirSync, rmSync } from 'node:fs';
import { spawnSync } from 'node:child_process';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const rootDir = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const wordpressDir = join(rootDir, 'wordpress');
const pluginDir = join(wordpressDir, 'halopress-fx');
const bootstrapPath = join(pluginDir, 'halopress-fx.php');
const bootstrap = await import('node:fs').then(({ readFileSync }) =>
  readFileSync(bootstrapPath, 'utf8'));
const version = bootstrap.match(/Version:\s*([0-9]+\.[0-9]+\.[0-9]+)/)?.[1];

if (!version) {
  throw new Error('Unable to read the HaloPress-FX plugin version.');
}

const releaseDir = join(rootDir, 'releases');
const archivePath = join(releaseDir, `halopress-fx-${version}.zip`);
mkdirSync(releaseDir, { recursive: true });

if (existsSync(archivePath)) {
  rmSync(archivePath);
}

let result;
if (process.platform === 'win32') {
  const escapedSource = join(pluginDir, '*').replaceAll("'", "''");
  const escapedArchive = archivePath.replaceAll("'", "''");
  result = spawnSync(
    'powershell.exe',
    [
      '-NoProfile',
      '-NonInteractive',
      '-Command',
      `Compress-Archive -Path '${escapedSource}' -DestinationPath '${escapedArchive}' -CompressionLevel Optimal`,
    ],
    { cwd: rootDir, stdio: 'inherit' },
  );
} else {
  result = spawnSync(
    'zip',
    ['-q', '-r', archivePath, '.'],
    { cwd: pluginDir, stdio: 'inherit' },
  );
}

if (result.error) {
  throw result.error;
}
if (result.status !== 0 || !existsSync(archivePath)) {
  throw new Error(`Failed to create plugin archive: ${archivePath}`);
}

const listResult = process.platform === 'win32'
  ? spawnSync('tar.exe', ['-tf', archivePath], { encoding: 'utf8' })
  : spawnSync('unzip', ['-Z1', archivePath], { encoding: 'utf8' });
if (listResult.error || listResult.status !== 0) {
  throw listResult.error ?? new Error('Unable to inspect the plugin archive.');
}

const archiveEntries = listResult.stdout
  .split(/\r?\n/u)
  .map(entry => entry.replace(/^\.\//u, '').replaceAll('\\', '/'))
  .filter(Boolean);
if (!archiveEntries.includes('halopress-fx.php')) {
  throw new Error('Plugin bootstrap must be located at the archive root.');
}
if (archiveEntries.some(entry => entry.startsWith('halopress-fx/'))) {
  throw new Error('Plugin archive contains an unexpected halopress-fx wrapper directory.');
}

console.log(`Created ${archivePath}`);

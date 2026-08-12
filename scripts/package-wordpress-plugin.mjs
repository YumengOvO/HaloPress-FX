import { existsSync, mkdirSync, readFileSync, rmSync } from 'node:fs';
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
const archiveSources = [
  'assets',
  'includes',
  'halopress-fx.php',
  'LICENSE',
  'readme.txt',
  'THIRD_PARTY_NOTICES.md',
];
mkdirSync(releaseDir, { recursive: true });

function readZipEntries(filePath) {
  const archive = readFileSync(filePath);
  const endHeaderSize = 22;
  const searchStart = Math.max(0, archive.length - endHeaderSize - 0xffff);
  let endOffset = -1;

  for (let offset = archive.length - endHeaderSize; offset >= searchStart; offset -= 1) {
    if (archive.readUInt32LE(offset) === 0x06054b50) {
      endOffset = offset;
      break;
    }
  }

  if (endOffset < 0) {
    throw new Error('Unable to find the ZIP end-of-central-directory record.');
  }

  const entryCount = archive.readUInt16LE(endOffset + 10);
  const centralDirectoryOffset = archive.readUInt32LE(endOffset + 16);
  if (entryCount === 0xffff || centralDirectoryOffset === 0xffffffff) {
    throw new Error('ZIP64 archives are not supported by the package validator.');
  }

  const entries = [];
  let offset = centralDirectoryOffset;
  for (let index = 0; index < entryCount; index += 1) {
    if (archive.readUInt32LE(offset) !== 0x02014b50) {
      throw new Error(`Invalid ZIP central-directory entry at offset ${offset}.`);
    }

    const nameLength = archive.readUInt16LE(offset + 28);
    const extraLength = archive.readUInt16LE(offset + 30);
    const commentLength = archive.readUInt16LE(offset + 32);
    const nameStart = offset + 46;
    entries.push(archive.toString('utf8', nameStart, nameStart + nameLength));
    offset = nameStart + nameLength + extraLength + commentLength;
  }

  return entries;
}

if (existsSync(archivePath)) {
  rmSync(archivePath);
}

let result;
if (process.platform === 'win32') {
  result = spawnSync(
    'tar.exe',
    ['-a', '-cf', archivePath, ...archiveSources],
    { cwd: pluginDir, stdio: 'inherit' },
  );
} else {
  result = spawnSync(
    'zip',
    ['-q', '-r', archivePath, ...archiveSources],
    { cwd: pluginDir, stdio: 'inherit' },
  );
}

if (result.error) {
  throw result.error;
}
if (result.status !== 0 || !existsSync(archivePath)) {
  throw new Error(`Failed to create plugin archive: ${archivePath}`);
}

const rawArchiveEntries = readZipEntries(archivePath);
if (rawArchiveEntries.some(entry => entry.includes('\\'))) {
  throw new Error('Plugin archive contains Windows path separators. ZIP entries must use forward slashes.');
}
if (rawArchiveEntries.some(entry => entry === '.' || entry === './' || entry.startsWith('./'))) {
  throw new Error('Plugin archive contains an explicit dot directory. Entries must start at the archive root.');
}
if (rawArchiveEntries.some(entry =>
  entry.startsWith('/')
  || /^[a-z]:/iu.test(entry)
  || entry.split('/').includes('..'))) {
  throw new Error('Plugin archive contains an unsafe absolute or parent path.');
}

const archiveEntries = rawArchiveEntries
  .filter(Boolean);

const requiredArchiveFiles = [
  'halopress-fx.php',
  'includes/class-halopress-fx-settings.php',
  'includes/class-halopress-fx-admin.php',
  'includes/class-halopress-fx-frontend.php',
  'assets/js/ba-click-fx.iife.js',
  'assets/js/halopress-fx.js',
  'assets/js/admin.js',
  'assets/css/admin.css',
];
for (const requiredFile of requiredArchiveFiles) {
  if (!archiveEntries.includes(requiredFile)) {
    throw new Error(`Plugin archive is missing the required file: ${requiredFile}`);
  }
}
if (archiveEntries.some(entry => entry.startsWith('halopress-fx/'))) {
  throw new Error('Plugin archive contains an unexpected halopress-fx wrapper directory.');
}

console.log(`Created ${archivePath}`);

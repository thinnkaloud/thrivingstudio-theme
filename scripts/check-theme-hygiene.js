#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const forbiddenFiles = [
  'DEBUG-CSS-LOADING.php',
  'emergency-restore.php',
  'error_log',
  'fix-critical-error.php',
  'simple-fix.php',
  'template-parts/error_log',
  'test-php.php',
  'test-working.php',
];

// Backup copies like foo.php.backup are served as plain text and leak source.
const backupPattern = /\.(backup|broken|disabled|bak|orig)$/;
const skipDirs = new Set(['.git', 'node_modules']);

function findBackups(dir) {
  return fs.readdirSync(dir, { withFileTypes: true }).flatMap((entry) => {
    if (skipDirs.has(entry.name)) {
      return [];
    }
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      return findBackups(full);
    }
    return backupPattern.test(entry.name) ? [path.relative(root, full)] : [];
  });
}

const found = forbiddenFiles
  .filter((file) => fs.existsSync(path.join(root, file)))
  .concat(findBackups(root));

if (found.length > 0) {
  console.error('Forbidden debug/repair files are present:');
  for (const file of found) {
    console.error(`- ${file}`);
  }
  process.exit(1);
}

console.log('Theme hygiene check passed.');

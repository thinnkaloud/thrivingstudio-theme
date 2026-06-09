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

const found = forbiddenFiles.filter((file) => fs.existsSync(path.join(root, file)));

if (found.length > 0) {
  console.error('Forbidden debug/repair files are present:');
  for (const file of found) {
    console.error(`- ${file}`);
  }
  process.exit(1);
}

console.log('Theme hygiene check passed.');

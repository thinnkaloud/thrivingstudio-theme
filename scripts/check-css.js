#!/usr/bin/env node
/**
 * CSS Build Status Checker
 * 
 * Checks if CSS source file is newer than the compiled build file
 * and provides instructions to rebuild if needed.
 */

const fs = require('fs');
const path = require('path');

const frontendPath = path.join(__dirname, '../frontend');
const cssSource = path.join(frontendPath, 'index.css');
const cssBuild = path.join(frontendPath, 'build.css');

function checkCSSStatus() {
    console.log('\n🔍 Checking CSS build status...\n');
    
    // Check if source file exists
    if (!fs.existsSync(cssSource)) {
        console.error('❌ CSS source file not found:', cssSource);
        process.exit(1);
    }
    
    // Check if build file exists
    if (!fs.existsSync(cssBuild)) {
        console.warn('⚠️  Build file not found:', cssBuild);
        console.log('💡 Run: npm run build:css\n');
        process.exit(1);
    }
    
    // Get modification times
    const sourceTime = fs.statSync(cssSource).mtime;
    const buildTime = fs.statSync(cssBuild).mtime;
    
    // Calculate difference
    const diff = sourceTime - buildTime;
    const minutes = Math.round(diff / 60000);
    
    if (diff > 0) {
        // Source is newer
        console.log('⚠️  CSS source is newer than build!');
        console.log(`   Source: ${sourceTime.toLocaleString()}`);
        console.log(`   Build:  ${buildTime.toLocaleString()}`);
        console.log(`   Difference: ${minutes} minute${minutes !== 1 ? 's' : ''}\n`);
        console.log('💡 Run: npm run build:css\n');
        process.exit(1);
    } else if (diff < 0) {
        // Build is newer (normal case)
        console.log('✅ CSS build is up to date!');
        console.log(`   Build:  ${buildTime.toLocaleString()}`);
        console.log(`   Source: ${sourceTime.toLocaleString()}\n`);
        process.exit(0);
    } else {
        // Same time (very unlikely)
        console.log('✅ CSS build is up to date!');
        process.exit(0);
    }
}

checkCSSStatus();


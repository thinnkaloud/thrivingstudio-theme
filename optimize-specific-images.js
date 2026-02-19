#!/usr/bin/env node

/**
 * Optimize Specific Images Script for ThrivingStudio Theme
 * Targets the specific large images causing performance issues
 */

const fs = require('fs');
const path = require('path');

console.log('🎯 Optimizing Specific Large Images...\n');

// The specific large images identified in your dashboard
const targetImages = [
    {
        name: 'Clarity Pinterest Inspired (8)',
        id: 3057,
        expectedSize: 2.11 * 1024 * 1024, // 2.11 MB
        filename: 'Clarity-Pinterest-Inspired-8.png'
    },
    {
        name: 'Clarity Pinterest Inspired (14)',
        id: 3063,
        expectedSize: 1.98 * 1024 * 1024, // 1.98 MB
        filename: 'Clarity-Pinterest-Inspired-14.png'
    },
    {
        name: 'Clarity Pinterest Inspired (9)',
        id: 3061,
        expectedSize: 1.94 * 1024 * 1024, // 1.94 MB
        filename: 'Clarity-Pinterest-Inspired-9.png'
    },
    {
        name: 'Screenshot 2025-03-04 at 12.37.55 AM',
        id: 2935,
        expectedSize: 2.28 * 1024 * 1024, // 2.28 MB
        filename: 'Screenshot-2025-03-04-at-12.37.55-AM.png'
    },
    {
        name: 'Screenshot 2025-03-04 at 12.35.59 AM',
        id: 2936,
        expectedSize: 2.05 * 1024 * 1024, // 2.05 MB
        filename: 'Screenshot-2025-03-04-at-12.35.59-AM.png'
    },
    {
        name: 'Clarity Pinterest Inspired (5)',
        id: 3059,
        expectedSize: 1.64 * 1024 * 1024, // 1.64 MB
        filename: 'Clarity-Pinterest-Inspired-5.png'
    },
    {
        name: 'Screenshot 2025-03-04 at 12.35.59 AM (1)',
        id: 2937,
        expectedSize: 1.49 * 1024 * 1024, // 1.49 MB
        filename: 'Screenshot-2025-03-04-at-12.35.59-AM-1.png'
    }
];

console.log('📋 Target Images to Optimize:');
targetImages.forEach((img, index) => {
    console.log(`${index + 1}. ${img.name} (ID: ${img.id}) - Expected: ${(img.expectedSize / (1024 * 1024)).toFixed(2)} MB`);
});
console.log('');

// Function to format file size
function formatFileSize(bytes) {
    const sizes = ['B', 'KB', 'MB', 'GB'];
    if (bytes === 0) return '0 B';
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
}

// Function to find WordPress uploads directory
function findWordPressUploads() {
    // Try to find wp-content/uploads from current directory
    let currentDir = process.cwd();
    
    while (currentDir !== '/' && currentDir !== '') {
        const wpContentPath = path.join(currentDir, 'wp-content', 'uploads');
        if (fs.existsSync(wpContentPath)) {
            return wpContentPath;
        }
        currentDir = path.dirname(currentDir);
    }
    
    return null;
}

// Function to search for images in uploads directory
function findImagesInUploads(uploadsDir) {
    const foundImages = [];
    
    function searchDirectory(dir, year, month) {
        if (!fs.existsSync(dir)) return;
        
        const items = fs.readdirSync(dir);
        
        for (const item of items) {
            const fullPath = path.join(dir, item);
            const stat = fs.statSync(fullPath);
            
            if (stat.isDirectory()) {
                // Recursively search subdirectories
                searchDirectory(fullPath, year, month);
            } else if (stat.isFile() && item.toLowerCase().endsWith('.png')) {
                // Check if this matches any of our target images
                for (const target of targetImages) {
                    if (item.includes(target.filename.replace('.png', '')) || 
                        item.includes(`ID-${target.id}`) ||
                        stat.size > target.expectedSize * 0.8) { // Within 20% of expected size
                        
                        foundImages.push({
                            ...target,
                            foundPath: fullPath,
                            actualSize: stat.size,
                            year: year,
                            month: month
                        });
                        break;
                    }
                }
            }
        }
    }
    
    // Search common upload directory structures
    const years = ['2025', '2024', '2023'];
    const months = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
    
    for (const year of years) {
        for (const month of months) {
            const yearMonthDir = path.join(uploadsDir, year, month);
            if (fs.existsSync(yearMonthDir)) {
                searchDirectory(yearMonthDir, year, month);
            }
        }
    }
    
    return foundImages;
}

// Function to create WebP version
async function createWebPVersion(imagePath, targetImage) {
    const webpPath = imagePath.replace('.png', '.webp');
    
    try {
        // Check if Sharp is available
        let sharp;
        try {
            sharp = require('sharp');
        } catch (e) {
            console.log(`⚠️  Sharp not available for ${targetImage.name}, skipping...`);
            return null;
        }
        
        console.log(`🔄 Converting: ${path.basename(imagePath)} → ${path.basename(webpPath)}`);
        
        await sharp(imagePath)
            .webp({ 
                quality: 85,
                effort: 6,
                nearLossless: false
            })
            .toFile(webpPath);
        
        const webpSize = fs.statSync(webpPath).size;
        const savings = targetImage.actualSize - webpSize;
        const savingsPercent = Math.round((savings / targetImage.actualSize) * 100);
        
        console.log(`✅ Successfully converted: ${targetImage.name}`);
        console.log(`   📊 Size comparison:`);
        console.log(`      PNG:  ${formatFileSize(targetImage.actualSize)}`);
        console.log(`      WebP: ${formatFileSize(webpSize)}`);
        console.log(`      💾 Saved: ${formatFileSize(savings)} (${savingsPercent}%)\n`);
        
        return {
            success: true,
            originalSize: targetImage.actualSize,
            webpSize: webpSize,
            savings: savings,
            savingsPercent: savingsPercent
        };
        
    } catch (error) {
        console.error(`❌ Failed to convert ${targetImage.name}: ${error.message}\n`);
        return { success: false, error: error.message };
    }
}

// Main function
async function main() {
    console.log('🔍 Searching for WordPress uploads directory...');
    
    const uploadsDir = findWordPressUploads();
    if (!uploadsDir) {
        console.log('❌ Could not find WordPress uploads directory');
        console.log('💡 Make sure you\'re running this script from your WordPress theme directory');
        return;
    }
    
    console.log(`✅ Found uploads directory: ${uploadsDir}\n`);
    
    console.log('🔍 Searching for target images...');
    const foundImages = findImagesInUploads(uploadsDir);
    
    if (foundImages.length === 0) {
        console.log('❌ No target images found in uploads directory');
        console.log('💡 The images might be in a different location or have different names');
        return;
    }
    
    console.log(`✅ Found ${foundImages.length} target images:\n`);
    
    let totalOriginalSize = 0;
    let totalWebpSize = 0;
    let totalSavings = 0;
    let convertedCount = 0;
    
    // Convert each found image
    for (const image of foundImages) {
        totalOriginalSize += image.actualSize;
        
        const result = await createWebPVersion(image.foundPath, image);
        
        if (result && result.success) {
            totalWebpSize += result.webpSize;
            totalSavings += result.savings;
            convertedCount++;
        }
    }
    
    // Summary
    if (convertedCount > 0) {
        console.log('📊 Conversion Summary:');
        console.log(`   Images converted: ${convertedCount}/${foundImages.length}`);
        console.log(`   Total original size: ${formatFileSize(totalOriginalSize)}`);
        console.log(`   Total WebP size: ${formatFileSize(totalWebpSize)}`);
        console.log(`   Total savings: ${formatFileSize(totalSavings)} (${Math.round((totalSavings / totalOriginalSize) * 100)}%)\n`);
        
        console.log('🎉 Image optimization completed!');
        console.log('\n📝 Next steps:');
        console.log('1. Test your website performance');
        console.log('2. Run PageSpeed Insights again to see improvements');
        console.log('3. Consider replacing PNG references with WebP in your content');
        console.log('4. Remove original PNG files after confirming WebP works');
        
        console.log('\n💡 Expected performance improvement:');
        console.log(`   Page size reduction: ~${formatFileSize(totalSavings)}`);
        console.log(`   Performance score: 65 → 80+ (estimated)`);
        
    } else {
        console.log('❌ No images were successfully converted');
        console.log('💡 Check if Sharp is installed: npm install sharp');
    }
}

// Run the optimization
main().catch(console.error);

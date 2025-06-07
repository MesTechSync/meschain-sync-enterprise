#!/usr/bin/env node

/**
 * MesChain-Sync Build Optimizer
 * CSS/JS Minification and Build Process
 * Created: June 4, 2025 - Alt Görev 3: Performance Optimization
 */

const fs = require('fs');
const path = require('path');

class BuildOptimizer {
    constructor() {
        this.stats = {
            filesProcessed: 0,
            originalSize: 0,
            minifiedSize: 0,
            compressionRatio: 0
        };
        
        this.sourceDir = path.join(__dirname, '../');
        this.distDir = path.join(__dirname, '../dist');
        
        console.log('🚀 MesChain-Sync Build Optimizer Starting...');
        this.init();
    }
    
    init() {
        this.createDistDirectory();
        this.processFiles();
        this.generateReport();
    }
    
    createDistDirectory() {
        if (!fs.existsSync(this.distDir)) {
            fs.mkdirSync(this.distDir, { recursive: true });
            console.log('📁 Created dist directory:', this.distDir);
        }
        
        // Create subdirectories
        const subdirs = ['css', 'js', 'html', 'images'];
        subdirs.forEach(dir => {
            const dirPath = path.join(this.distDir, dir);
            if (!fs.existsSync(dirPath)) {
                fs.mkdirSync(dirPath, { recursive: true });
            }
        });
    }
    
    processFiles() {
        console.log('⚡ Processing files for optimization...');
        
        // Process HTML files
        this.processDirectory('FRONTEND_COMPONENTS', '.html', this.minifyHTML.bind(this));
        this.processDirectory('MARKETPLACE_UIS', '.html', this.minifyHTML.bind(this));
        
        // Process CSS files
        this.processDirectory('STYLES', '.css', this.minifyCSS.bind(this));
        
        // Process JS files
        this.processDirectory('FRONTEND_COMPONENTS', '.js', this.minifyJS.bind(this));
        this.processDirectory('MARKETPLACE_UIS', '.js', this.minifyJS.bind(this));
    }
    
    processDirectory(dirName, extension, processor) {
        const dirPath = path.join(this.sourceDir, dirName);
        
        if (!fs.existsSync(dirPath)) {
            console.log(`⚠️  Directory not found: ${dirName}`);
            return;
        }
        
        const files = fs.readdirSync(dirPath).filter(file => file.endsWith(extension));
        
        files.forEach(file => {
            const filePath = path.join(dirPath, file);
            processor(filePath, file);
        });
    }
    
    minifyHTML(filePath, fileName) {
        try {
            const content = fs.readFileSync(filePath, 'utf8');
            const originalSize = content.length;
            
            // HTML minification
            let minified = content
                // Remove HTML comments (but keep conditional comments)
                .replace(/<!--(?!\s*(?:\[if [^\]]+]|<!|>))[\s\S]*?-->/g, '')
                // Remove extra whitespace between tags
                .replace(/>\s+</g, '><')
                // Remove extra whitespace in text content
                .replace(/\s{2,}/g, ' ')
                // Remove leading/trailing whitespace
                .trim();
            
            // Extract and minify inline CSS
            minified = minified.replace(/<style[^>]*>([\s\S]*?)<\/style>/gi, (match, css) => {
                const minifiedCSS = this.minifyCSS(css, true);
                return match.replace(css, minifiedCSS);
            });
            
            // Extract and minify inline JavaScript
            minified = minified.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi, (match, js) => {
                // Skip external scripts
                if (match.includes('src=')) return match;
                const minifiedJS = this.minifyJS(js, true);
                return match.replace(js, minifiedJS);
            });
            
            const minifiedSize = minified.length;
            const savings = originalSize - minifiedSize;
            const compressionRatio = ((savings / originalSize) * 100).toFixed(1);
            
            // Write minified file
            const outputPath = path.join(this.distDir, 'html', fileName.replace('.html', '.min.html'));
            fs.writeFileSync(outputPath, minified);
            
            console.log(`📄 ${fileName}: ${originalSize} → ${minifiedSize} bytes (${compressionRatio}% smaller)`);
            
            this.updateStats(originalSize, minifiedSize);
            
        } catch (error) {
            console.error(`❌ Error minifying HTML ${fileName}:`, error.message);
        }
    }
    
    minifyCSS(input, isInline = false) {
        let content = typeof input === 'string' ? input : fs.readFileSync(input, 'utf8');
        let fileName = isInline ? 'inline-css' : path.basename(input);
        
        try {
            const originalSize = content.length;
            
            // CSS minification
            let minified = content
                // Remove comments
                .replace(/\/\*[\s\S]*?\*\//g, '')
                // Remove extra whitespace
                .replace(/\s+/g, ' ')
                // Remove whitespace around selectors and properties
                .replace(/\s*{\s*/g, '{')
                .replace(/;\s*/g, ';')
                .replace(/}\s*/g, '}')
                .replace(/,\s*/g, ',')
                .replace(/:\s*/g, ':')
                // Remove last semicolon before }
                .replace(/;}/g, '}')
                // Remove whitespace at start/end
                .trim();
            
            if (isInline) {
                return minified;
            }
            
            const minifiedSize = minified.length;
            const savings = originalSize - minifiedSize;
            const compressionRatio = ((savings / originalSize) * 100).toFixed(1);
            
            // Write minified file
            const outputPath = path.join(this.distDir, 'css', fileName.replace('.css', '.min.css'));
            fs.writeFileSync(outputPath, minified);
            
            console.log(`🎨 ${fileName}: ${originalSize} → ${minifiedSize} bytes (${compressionRatio}% smaller)`);
            
            this.updateStats(originalSize, minifiedSize);
            
        } catch (error) {
            console.error(`❌ Error minifying CSS ${fileName}:`, error.message);
        }
    }
    
    minifyJS(input, isInline = false) {
        let content = typeof input === 'string' ? input : fs.readFileSync(input, 'utf8');
        let fileName = isInline ? 'inline-js' : path.basename(input);
        
        try {
            const originalSize = content.length;
            
            // JavaScript minification (basic)
            let minified = content
                // Remove single line comments (but preserve URLs)
                .replace(/(?:^|\n|\r)\s*\/\/.*$/gm, '')
                // Remove multi-line comments
                .replace(/\/\*[\s\S]*?\*\//g, '')
                // Remove extra whitespace
                .replace(/\s+/g, ' ')
                // Remove whitespace around operators
                .replace(/\s*([{}();,=+\-*/<>!&|])\s*/g, '$1')
                // Remove whitespace at start/end
                .trim();
            
            if (isInline) {
                return minified;
            }
            
            const minifiedSize = minified.length;
            const savings = originalSize - minifiedSize;
            const compressionRatio = ((savings / originalSize) * 100).toFixed(1);
            
            // Write minified file
            const outputPath = path.join(this.distDir, 'js', fileName.replace('.js', '.min.js'));
            fs.writeFileSync(outputPath, minified);
            
            console.log(`⚡ ${fileName}: ${originalSize} → ${minifiedSize} bytes (${compressionRatio}% smaller)`);
            
            this.updateStats(originalSize, minifiedSize);
            
        } catch (error) {
            console.error(`❌ Error minifying JS ${fileName}:`, error.message);
        }
    }
    
    updateStats(originalSize, minifiedSize) {
        this.stats.filesProcessed++;
        this.stats.originalSize += originalSize;
        this.stats.minifiedSize += minifiedSize;
    }
    
    generateReport() {
        const totalSavings = this.stats.originalSize - this.stats.minifiedSize;
        this.stats.compressionRatio = ((totalSavings / this.stats.originalSize) * 100).toFixed(1);
        
        const report = `
# MesChain-Sync Build Optimization Report
Generated: ${new Date().toISOString()}

## Summary
- Files Processed: ${this.stats.filesProcessed}
- Original Size: ${this.formatBytes(this.stats.originalSize)}
- Minified Size: ${this.formatBytes(this.stats.minifiedSize)}
- Total Savings: ${this.formatBytes(totalSavings)}
- Compression Ratio: ${this.stats.compressionRatio}%

## Performance Impact
- Estimated Load Time Improvement: ${Math.round(this.stats.compressionRatio * 0.8)}%
- Bandwidth Savings: ${this.formatBytes(totalSavings)} per page load
- CDN Cost Reduction: ~${Math.round(this.stats.compressionRatio)}%

## Next Steps
1. Implement Gzip compression on server
2. Add image optimization (WebP conversion)
3. Setup HTTP/2 server push
4. Enable browser caching headers

## Files Location
Minified files are available in: ${this.distDir}
        `;
        
        const reportPath = path.join(this.distDir, 'optimization-report.md');
        fs.writeFileSync(reportPath, report);
        
        console.log('\n📊 OPTIMIZATION COMPLETE!');
        console.log('='.repeat(50));
        console.log(`Files Processed: ${this.stats.filesProcessed}`);
        console.log(`Original Size: ${this.formatBytes(this.stats.originalSize)}`);
        console.log(`Minified Size: ${this.formatBytes(this.stats.minifiedSize)}`);
        console.log(`Total Savings: ${this.formatBytes(totalSavings)} (${this.stats.compressionRatio}%)`);
        console.log(`Report saved: ${reportPath}`);
        console.log('='.repeat(50));
    }
    
    formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
}

// Run the build optimizer
if (require.main === module) {
    new BuildOptimizer();
}

module.exports = BuildOptimizer;

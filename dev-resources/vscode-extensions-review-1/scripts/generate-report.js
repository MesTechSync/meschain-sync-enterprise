const fs = require('fs');
const path = require('path');

// Define the paths for the reviews and categories
const reviewsDir = path.join(__dirname, '../src/reviews');
const categoriesDir = path.join(__dirname, '../src/categories');

// Function to read files from a directory
const readFilesFromDir = (dir) => {
    return fs.readdirSync(dir).map(file => {
        const filePath = path.join(dir, file);
        return {
            name: file.replace('.md', ''),
            content: fs.readFileSync(filePath, 'utf-8')
        };
    });
};

// Generate the report
const generateReport = () => {
    const reviews = readFilesFromDir(reviewsDir);
    const categories = readFilesFromDir(categoriesDir);

    let reportContent = '# VS Code Extensions Review Report\n\n';

    reportContent += '## Recommended Extensions\n\n';

    categories.forEach(category => {
        reportContent += `### ${category.name.replace(/-/g, ' ').toUpperCase()}\n`;
        reportContent += `${category.content}\n\n`;
    });

    reportContent += '## Detailed Reviews\n\n';

    reviews.forEach(review => {
        reportContent += `### ${review.name.replace(/-/g, ' ').toUpperCase()}\n`;
        reportContent += `${review.content}\n\n`;
    });

    // Write the report to a file
    fs.writeFileSync(path.join(__dirname, '../report.md'), reportContent);
    console.log('Report generated successfully!');
};

// Execute the report generation
generateReport();
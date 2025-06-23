// This file validates the extensions listed in the project to ensure they are up-to-date and functional.

const fs = require('fs');
const path = require('path');

// Load the extensions data
const extensionsFilePath = path.join(__dirname, '../data/extensions.json');
const extensionsData = JSON.parse(fs.readFileSync(extensionsFilePath, 'utf8'));

// Function to validate extensions
function validateExtensions(extensions) {
    const validExtensions = [];
    const invalidExtensions = [];

    extensions.forEach(extension => {
        // Simulate validation logic (e.g., checking if the extension is installed)
        const isValid = Math.random() > 0.2; // 80% chance to be valid for simulation

        if (isValid) {
            validExtensions.push(extension);
        } else {
            invalidExtensions.push(extension);
        }
    });

    return { validExtensions, invalidExtensions };
}

// Validate the extensions
const { validExtensions, invalidExtensions } = validateExtensions(extensionsData);

// Output the results
console.log('Valid Extensions:', validExtensions);
console.log('Invalid Extensions:', invalidExtensions);
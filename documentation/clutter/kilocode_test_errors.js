// Kilo Code AI Agent - Error Detection Test File
// Bu dosya Kilo Code'un hata tespit yeteneklerini test etmek için oluşturulmuştur

// KASITLI HATALAR - Kilo Code bunları tespit etmeli:

// 1. Syntax Error - Missing closing parenthesis (FIXED)
function testFunction() {
    console.log("Test function");
}

// 2. Undefined variable
let result = undefinedVariable + 5;

// 3. Unreachable code
function unreachableTest() {
    return "test";
    console.log("This will never execute"); // Unreachable code
}

// 4. Missing semicolon (not always an error but style issue)
let x = 10
let y = 20

// 5. Unused variable
let unusedVariable = "I am not used anywhere";

// 6. Type mismatch (for TypeScript-like checking)
let numberVar = 42;
numberVar = "This should be a number"; // Type mismatch

// 7. Empty catch block
try {
    riskyOperation();
} catch (error) {
    // Empty catch block - bad practice
}

// 8. Missing function declaration
someFunction(); // Function not defined

// 9. Infinite loop potential
function infiniteLoop() {
    while (true) {
        // No break condition
    }
}

// 10. Memory leak potential
let leakyArray = [];
setInterval(() => {
    leakyArray.push(new Date());
    // Array grows infinitely
}, 1000);

console.log("Kilo Code Error Detection Test Complete");

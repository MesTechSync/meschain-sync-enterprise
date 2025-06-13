module.exports = {
    env: {
        browser: true,
        es2021: true,
        node: true
    },
    globals: {
        Chart: 'readonly',
        signalR: 'readonly',
        ApexCharts: 'readonly',
        Tailwind: 'readonly'
    },
    extends: [
        'eslint:recommended'
    ],
    parserOptions: {
        ecmaVersion: 12,
        sourceType: 'module'
    },
    rules: {
        // Kod kalitesi kuralları
        'no-console': 'warn',
        'no-unused-vars': 'warn',
        'semi': ['error', 'always'],
        'quotes': ['error', 'single'],
        'eqeqeq': 'error',
        'no-eval': 'error',
        'no-implied-eval': 'error',
        'no-new-func': 'error',
        
        // Güvenlik kuralları
        'no-script-url': 'error',
        'no-unsafe-innerHTML': 'off',
        
        // Best practices
        'curly': 'error',
        'dot-notation': 'error',
        'no-multi-spaces': 'error',
        'no-trailing-spaces': 'error',
        'no-multiple-empty-lines': ['error', { max: 2 }],
        
        // ES6+ kuralları
        'prefer-const': 'error',
        'no-var': 'error',
        'arrow-spacing': 'error',
        'template-curly-spacing': 'error'
    },
    ignorePatterns: [
        'node_modules/',
        'dist/',
        'build/',
        'src/',
        '**/*.min.js',
        '**/*.tsx',
        '**/*.ts',
        'upload/admin/view/javascript/meschain-react/',
        '**/*.backup.*'
    ]
}; 
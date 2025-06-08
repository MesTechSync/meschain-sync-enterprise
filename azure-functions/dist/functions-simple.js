"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.test = exports.health = void 0;
const functions_1 = require("@azure/functions");
// Health Check Function
async function health(request, context) {
    context.log('Health check endpoint called');
    return {
        status: 200,
        jsonBody: {
            status: 'healthy',
            timestamp: new Date().toISOString(),
            service: 'MesChain-Sync Enterprise SignalR Functions',
            version: '1.0.0'
        }
    };
}
exports.health = health;
functions_1.app.http('health', {
    methods: ['GET'],
    authLevel: 'anonymous',
    handler: health
});
// Test function
async function test(request, context) {
    return {
        status: 200,
        jsonBody: { message: 'Test successful' }
    };
}
exports.test = test;
functions_1.app.http('test', {
    methods: ['GET'],
    authLevel: 'anonymous',
    handler: test
});
//# sourceMappingURL=functions-simple.js.map
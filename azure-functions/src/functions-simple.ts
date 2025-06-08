import { app, HttpRequest, HttpResponseInit, InvocationContext } from '@azure/functions';

// Health Check Function
export async function health(request: HttpRequest, context: InvocationContext): Promise<HttpResponseInit> {
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

app.http('health', {
    methods: ['GET'],
    authLevel: 'anonymous',
    handler: health
});

// Test function
export async function test(request: HttpRequest, context: InvocationContext): Promise<HttpResponseInit> {
    return {
        status: 200,
        jsonBody: { message: 'Test successful' }
    };
}

app.http('test', {
    methods: ['GET'],
    authLevel: 'anonymous',
    handler: test
});

import { HttpRequest, HttpResponseInit, InvocationContext } from '@azure/functions';
export declare function health(request: HttpRequest, context: InvocationContext): Promise<HttpResponseInit>;
export declare function test(request: HttpRequest, context: InvocationContext): Promise<HttpResponseInit>;

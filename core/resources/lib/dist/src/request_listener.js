/**
 * Process all requests before sending them from the client to the server.
 *
 * RequestProcessor
 */
export class RequestListener {
    constructor(middleware) {
        const rootOpen = XMLHttpRequest.prototype.open;
        const rootSend = XMLHttpRequest.prototype.send;
        const rootFetch = window.fetch;
        XMLHttpRequest.prototype.open = function (method, url, async = true, user = null, password = null) {
            const result = middleware({ url, method, async, user, password });
            rootOpen.apply(this, [result.method, result.url, result.async, result.user, result.password]);
        };
        XMLHttpRequest.prototype.send = function (...args) {
            rootSend.apply(this, args);
        };
        window.fetch = function (input, init) {
            var _a, _b;
            let url = input instanceof Request ? input.url : input;
            const result = middleware({ url, method: (_a = init === null || init === void 0 ? void 0 : init.method) !== null && _a !== void 0 ? _a : 'GET', async: true, user: null, password: null });
            if (input instanceof Request) {
                input = new Request(result.url, Object.assign({ method: (_b = result === null || result === void 0 ? void 0 : result.method) !== null && _b !== void 0 ? _b : 'GET', body: result.body || (init === null || init === void 0 ? void 0 : init.body) }, init));
            }
            else {
                input = result.url;
            }
            return rootFetch(input, init);
        };
    }
}

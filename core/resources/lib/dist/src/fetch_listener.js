var __rest = (this && this.__rest) || function (s, e) {
    var t = {};
    for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0)
        t[p] = s[p];
    if (s != null && typeof Object.getOwnPropertySymbols === "function")
        for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) {
            if (e.indexOf(p[i]) < 0 && Object.prototype.propertyIsEnumerable.call(s, p[i]))
                t[p[i]] = s[p[i]];
        }
    return t;
};
/**
 * Process all fetch requests before sending them from the client to the server.
 *
 * RequestProcessor
 */
export class FetchListener {
    constructor(middleware) {
        if (window.fetchListenerAttached) {
            throw new Error('Only one FetchListener can be attached to the window object. ' +
                'If you are using multiple FetchListeners, please combine them into one.');
        }
        window.fetchListenerAttached = true;
        const fetch = window.fetch;
        window.fetch = function (input, init) {
            const url = input instanceof Request ? input.url : input.toString();
            let contextInit = init || {};
            console.log('Request intercepted', Object.assign({ url }, contextInit));
            const _a = middleware(Object.assign({ url }, contextInit)), { url: mwResultUrl } = _a, mwResultInit = __rest(_a, ["url"]);
            console.log('Request processed', mwResultUrl, mwResultInit);
            if (input instanceof Request) {
                return fetch(new Request(mwResultUrl, Object.assign(Object.assign({}, mwResultInit), { body: contextInit.body })));
            }
            return fetch(mwResultUrl, mwResultInit);
        };
    }
}

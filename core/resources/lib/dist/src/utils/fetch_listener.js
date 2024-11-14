var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
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
 * Process all fetch requests before sending them from the client to the server using monkey patching.
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
            return __awaiter(this, void 0, void 0, function* () {
                var _a, _b;
                const url = input instanceof Request ? input.url : input.toString();
                let contextInit = init || {};
                const _c = middleware(Object.assign({ url }, contextInit)), { url: mwResultUrl } = _c, mwResultInit = __rest(_c, ["url"]);
                if (input instanceof Request) {
                    const response = yield fetch(new Request(mwResultUrl, Object.assign(Object.assign({}, mwResultInit), { body: contextInit.body })));
                    //fetchCallbacks are one-time use
                    if ((_a = window.fetchCallbacks) === null || _a === void 0 ? void 0 : _a.length) {
                        window.fetchCallbacks.forEach((callback) => {
                            callback(Object.assign({ url }, contextInit));
                        });
                        window.fetchCallbacks = [];
                    }
                    return response;
                }
                const response_1 = yield fetch(mwResultUrl, mwResultInit);
                if ((_b = window.fetchCallbacks) === null || _b === void 0 ? void 0 : _b.length) {
                    window.fetchCallbacks.forEach((callback_1) => {
                        callback_1(Object.assign({ url }, contextInit));
                    });
                    window.fetchCallbacks = [];
                }
                return response_1;
            });
        };
    }
}

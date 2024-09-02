type RequestContext = {
    url: string;
} & RequestInit;

/**
 * Process all fetch requests before sending them from the client to the server.
 *
 * RequestProcessor
 */
export class FetchListener {
    constructor(
        middleware: (context: RequestContext) => RequestContext
    ) {
        if (window.fetchListenerAttached) {
            throw new Error(
                'Only one FetchListener can be attached to the window object. ' +
                'If you are using multiple FetchListeners, please combine them into one.'
            );
        }

        window.fetchListenerAttached = true;

        const fetch = window.fetch;

        window.fetch = function (input: Request | URL | string, init?: RequestInit) {
            const url: string = input instanceof Request ? input.url : input.toString();
            let contextInit: RequestInit = init || {};

            console.log('Request intercepted', {
                url,
                ...contextInit,
            });

            const {url: mwResultUrl, ...mwResultInit} = middleware({url, ...contextInit});
            console.log('Request processed', mwResultUrl, mwResultInit);

            if (input instanceof Request) {
                return fetch(new Request(mwResultUrl, {
                    ...mwResultInit,
                    body: contextInit.body,
                }));
            }

            return fetch(mwResultUrl, mwResultInit);
        };
    }
}

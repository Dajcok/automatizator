type RequestContext = {
    url: string;
} & RequestInit;

/**
 * Process all fetch requests before sending them from the client to the server using monkey patching.
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

        window.fetch = async function (input: Request | URL | string, init?: RequestInit) {
            const url: string = input instanceof Request ? input.url : input.toString();
            let contextInit: RequestInit = init || {};

            const {url: mwResultUrl, ...mwResultInit} = middleware({url, ...contextInit});

            if (input instanceof Request) {
                const response = await fetch(new Request(mwResultUrl, {
                    ...mwResultInit,
                    body: contextInit.body,
                }));

                //fetchCallbacks are one-time use
                if (window.fetchCallbacks?.length) {
                    window.fetchCallbacks.forEach((callback) => {
                        callback({url, ...contextInit});
                    });
                    window.fetchCallbacks = [];
                }

                return response;
            }

            const response_1 = await fetch(mwResultUrl, mwResultInit);

            if (window.fetchCallbacks?.length) {
                window.fetchCallbacks.forEach((callback_1) => {
                    callback_1({url, ...contextInit});
                });
                window.fetchCallbacks = [];
            }

            return response_1;
        };
    }
}

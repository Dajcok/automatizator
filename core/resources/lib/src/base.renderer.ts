import {AxiosInstance} from "axios";
import {translate} from "./translate";

export abstract class BaseRenderer {
    protected newScriptEls: HTMLScriptElement[] = [];
    protected newLinkEls: HTMLLinkElement[] = [];
    private authToken: string | null = null;

    constructor(
        protected readonly axios: AxiosInstance,
        protected readonly proxyUrl: string,
    ) {
        if (!axios.defaults.baseURL) {
            throw new Error('axios baseURL is not defined');
        }
    }

    /**
     * This method replaces all script, img and link urls with the absolute URLs to point to the core server.
     * It also removes the script and link elements from the html and returns them separately.
     * Lastly it returns only the html content of the form.
     *
     * @param html
     */
    protected processHTML(
        html: string,
    ): {
        html: string,
        scriptEls: HTMLScriptElement[],
        linkEls: HTMLLinkElement[]
    } {
        const appendWithProxyUrl = (originalUrl: string) => {
            if (originalUrl.startsWith('file://')) {
                originalUrl = originalUrl.substring(7);
            } else if (originalUrl.startsWith('http://localhost')) {
                originalUrl = originalUrl.substring(21);
            } else if (originalUrl.startsWith('https://business-idocs.web.app')) {
	    	originalUrl = originalUrl.substring(30);
	    }

            if (!this.proxyUrl) {
                return this.axios.defaults.baseURL + originalUrl;
            }

            return this.proxyUrl + originalUrl;
        };

        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        const scriptEls = Array.from(doc.querySelectorAll('script[src]')) as HTMLScriptElement[];
        const linkEls = Array.from(doc.querySelectorAll('link[href]')) as HTMLLinkElement[];
        const imgEls = Array.from(doc.querySelectorAll('img[src]')) as HTMLImageElement[];

        scriptEls.forEach(el => {
            el.src = appendWithProxyUrl(el.src);
            el.remove();
        });

        linkEls.forEach(el => {
            el.href = appendWithProxyUrl(el.href);
            el.remove();
        });

        imgEls.forEach(el => {
            el.src = appendWithProxyUrl(el.src);
        });

        const rootFormEl = doc.getElementById('xforms-form');

        if (!rootFormEl) {
            throw new Error('Root form element not found');
        }

        return {
            html: rootFormEl.outerHTML,
            scriptEls,
            linkEls,
        };
    }

    /**
     * This method loads all the scripts and links into the document
     *
     * @param scriptEls
     * @param linkEls
     * @private
     */
    protected async loadResources(scriptEls: HTMLScriptElement[], linkEls: HTMLLinkElement[]) {
        const loadScriptsSequentially = (scripts: HTMLScriptElement[], index = 0): Promise<void> => {
            return new Promise<void>((resolve, reject) => {
                if (index >= scripts.length) {
                    resolve();
                    return;
                }

                const oldScriptEl = scripts[index];
                const newScriptEl = document.createElement('script');

                newScriptEl.src = oldScriptEl.src;
                newScriptEl.type = oldScriptEl.type || 'text/javascript';
                newScriptEl.defer = oldScriptEl.defer;

                newScriptEl.onload = async () => {
                    try {
                        await loadScriptsSequentially(scripts, index + 1);
                        resolve();
                    } catch (error) {
                        reject(error);
                    }
                };

                newScriptEl.onerror = () => {
                    reject(new Error(`Failed to load script: ${newScriptEl.src}`));
                };

                document.head.appendChild(newScriptEl);
                this.newScriptEls.push(newScriptEl);
            });
        }

        Array.from(linkEls).forEach(oldLinkEl => {
            const newLinkEl = document.createElement('link');
            newLinkEl.href = oldLinkEl.href;
            newLinkEl.rel = oldLinkEl.rel;
            newLinkEl.media = oldLinkEl.media;
            document.head.appendChild(newLinkEl);

            this.newLinkEls.push(newLinkEl);
        });

        await loadScriptsSequentially(Array.from(scriptEls));
    }


    /**
     * This method renders orbeon page in the given container.
     * - It first fetches the html content of the page
     * - Then it processes the html content using processHTML method
     *
     * @param container
     * @param pageUrl
     * @param beforeRenderCb
     */
    public async render(container: HTMLElement, pageUrl: string, beforeRenderCb?: (container: HTMLElement) => void): Promise<void> {
        if(!this.authToken) {
            throw new Error('No auth token provided. You need to call authenticate method first');
        }

        const response = await this.axios.get(pageUrl, {
            headers: {
                'Content-Type': 'application/json',
                'Authorization': this.authToken,
            }
        }).catch((e: any) => {
            console.error('Error fetching form', e);
            throw e;
        });

        const {html, scriptEls, linkEls} = this.processHTML(response.data);

        if (beforeRenderCb) {
            beforeRenderCb(container);
        }

        //We need to insert HTML before loading resources, because the resources might depend on the HTML
        container.innerHTML = html;

        try {
            await this.loadResources(scriptEls, linkEls);
        } catch (e) {
            console.error('Error loading resources', e);
        }

        requestAnimationFrame(() => {
            translate();
        });
    }

    /**
     * This method cleans up the container by removing all the children and the script and link elements
     * This should be called after the form is no longer needed
     * @param container
     */
    public cleanup(container: HTMLElement): void {
        container.innerHTML = '';

        this.newScriptEls.forEach(el => {
            el.remove();
        });

        this.newLinkEls.forEach(el => {
            el.remove();
        });

        this.newScriptEls = [];
        this.newLinkEls = [];
    }

    public authenticate(authToken: string) {
        if(!authToken) {
            throw new Error('No auth token provided');
        }

        this.authToken = authToken;

        console.log('Auth token set');
    }
}

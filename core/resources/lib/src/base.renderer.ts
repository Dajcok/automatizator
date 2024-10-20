import {AxiosInstance} from "axios";
import {translate} from "./translate";

export abstract class BaseRenderer {
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
    protected loadResources(scriptEls: HTMLScriptElement[], linkEls: HTMLLinkElement[]) {
        function loadScriptsSequentially(scripts: HTMLScriptElement[], index = 0) {
            if (index >= scripts.length) return;

            const oldScriptEl = scripts[index];
            const newScriptEl = document.createElement('script');
            newScriptEl.src = oldScriptEl.src;
            newScriptEl.type = oldScriptEl.type || 'text/javascript';
            newScriptEl.defer = oldScriptEl.defer;

            newScriptEl.onload = () => {
                loadScriptsSequentially(scripts, index + 1);
            };

            document.head.appendChild(newScriptEl);
        }

        Array.from(linkEls).forEach(oldLinkEl => {
            const newLinkEl = document.createElement('link');
            newLinkEl.href = oldLinkEl.href;
            newLinkEl.rel = oldLinkEl.rel;
            newLinkEl.media = oldLinkEl.media;
            document.head.appendChild(newLinkEl);
        });
        loadScriptsSequentially(Array.from(scriptEls));
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
        const response = await this.axios.get(pageUrl, {
            headers: {
                'Content-Type': 'application/json',
            }
        }).catch((e: any) => {
            console.error('Error fetching form', e);
            throw e;
        });

        const {html, scriptEls, linkEls} = this.processHTML(response.data);

        try {
            this.loadResources(scriptEls, linkEls);
        } catch (e) {
            console.error('Error loading resources', e);
        }

        if (beforeRenderCb) {
            beforeRenderCb(container);
        }

        container.innerHTML = html;

        requestAnimationFrame(() => {
            translate();
        });
    }
}

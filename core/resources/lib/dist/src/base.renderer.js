var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
import { translate } from "./translate";
export class BaseRenderer {
    constructor(axios, proxyUrl) {
        this.axios = axios;
        this.proxyUrl = proxyUrl;
        this.newScriptEls = [];
        this.newLinkEls = [];
        this.authToken = null;
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
    processHTML(html) {
        const appendWithProxyUrl = (originalUrl) => {
            if (originalUrl.startsWith('file://')) {
                originalUrl = originalUrl.substring(7);
            }
            else if (originalUrl.startsWith('http://localhost')) {
                originalUrl = originalUrl.substring(21);
            }
            if (!this.proxyUrl) {
                return this.axios.defaults.baseURL + originalUrl;
            }
            return this.proxyUrl + originalUrl;
        };
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const scriptEls = Array.from(doc.querySelectorAll('script[src]'));
        const linkEls = Array.from(doc.querySelectorAll('link[href]'));
        const imgEls = Array.from(doc.querySelectorAll('img[src]'));
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
    loadResources(scriptEls, linkEls) {
        return __awaiter(this, void 0, void 0, function* () {
            const loadScriptsSequentially = (scripts, index = 0) => {
                return new Promise((resolve, reject) => {
                    if (index >= scripts.length) {
                        resolve();
                        return;
                    }
                    const oldScriptEl = scripts[index];
                    const newScriptEl = document.createElement('script');
                    newScriptEl.src = oldScriptEl.src;
                    newScriptEl.type = oldScriptEl.type || 'text/javascript';
                    newScriptEl.defer = oldScriptEl.defer;
                    newScriptEl.onload = () => __awaiter(this, void 0, void 0, function* () {
                        try {
                            yield loadScriptsSequentially(scripts, index + 1);
                            resolve();
                        }
                        catch (error) {
                            reject(error);
                        }
                    });
                    newScriptEl.onerror = () => {
                        reject(new Error(`Failed to load script: ${newScriptEl.src}`));
                    };
                    document.head.appendChild(newScriptEl);
                    this.newScriptEls.push(newScriptEl);
                });
            };
            Array.from(linkEls).forEach(oldLinkEl => {
                const newLinkEl = document.createElement('link');
                newLinkEl.href = oldLinkEl.href;
                newLinkEl.rel = oldLinkEl.rel;
                newLinkEl.media = oldLinkEl.media;
                document.head.appendChild(newLinkEl);
                this.newLinkEls.push(newLinkEl);
            });
            yield loadScriptsSequentially(Array.from(scriptEls));
        });
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
    render(container, pageUrl, beforeRenderCb) {
        return __awaiter(this, void 0, void 0, function* () {
            if (!this.authToken) {
                throw new Error('No auth token provided. You need to call authenticate method first');
            }
            const response = yield this.axios.get(pageUrl, {
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': this.authToken,
                }
            }).catch((e) => {
                console.error('Error fetching form', e);
                throw e;
            });
            const { html, scriptEls, linkEls } = this.processHTML(response.data);
            if (beforeRenderCb) {
                beforeRenderCb(container);
            }
            //We need to insert HTML before loading resources, because the resources might depend on the HTML
            container.innerHTML = html;
            try {
                yield this.loadResources(scriptEls, linkEls);
            }
            catch (e) {
                console.error('Error loading resources', e);
            }
            requestAnimationFrame(() => {
                translate();
            });
        });
    }
    /**
     * This method cleans up the container by removing all the children and the script and link elements
     * This should be called after the form is no longer needed
     * @param container
     */
    cleanup(container) {
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
    authenticate(authToken) {
        this.authToken = authToken;
    }
}

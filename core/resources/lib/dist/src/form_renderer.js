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
export class FormRenderer {
    constructor(axios) {
        this.axios = axios;
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
     * @private
     */
    processFormHTML(html) {
        const appendWithBaseUrl = (originalUrl) => {
            if (originalUrl.startsWith('file://')) {
                originalUrl = originalUrl.substring(7);
            }
            return this.axios.defaults.baseURL + originalUrl;
        };
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const scriptEls = Array.from(doc.querySelectorAll('script[src]'));
        const linkEls = Array.from(doc.querySelectorAll('link[href]'));
        const imgEls = Array.from(doc.querySelectorAll('img[src]'));
        scriptEls.forEach(el => {
            el.src = appendWithBaseUrl(el.src);
            el.remove();
        });
        linkEls.forEach(el => {
            el.href = appendWithBaseUrl(el.href);
            el.remove();
        });
        imgEls.forEach(el => {
            el.src = appendWithBaseUrl(el.src);
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
        function loadScriptsSequentially(scripts, index = 0) {
            if (index >= scripts.length)
                return;
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
    render(app, form, container) {
        return __awaiter(this, void 0, void 0, function* () {
            const response = yield this.axios.post(`api/of/definition/${app}/${form}/render`, {}, {
                headers: {
                    'Content-Type': 'application/json',
                }
            }).catch(e => {
                console.error('Error fetching form', e);
                throw e;
            });
            const { html, scriptEls, linkEls } = this.processFormHTML(response.data);
            try {
                this.loadResources(scriptEls, linkEls);
            }
            catch (e) {
                console.error('Error loading resources', e);
            }
            //Attach classes to the container that are required by Orbeon
            container.classList.add('orbeon');
            container.classList.add('xforms-disable-alert-as-tooltip');
            container.classList.add('yui-skin-sam');
            container.innerHTML = html;
            requestAnimationFrame(() => {
                translate();
            });
        });
    }
}

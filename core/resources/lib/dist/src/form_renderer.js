import { BaseRenderer } from "./base.renderer";
export class FormRenderer extends BaseRenderer {
    processHTML(html) {
        const appendWithProxyUrl = (originalUrl) => {
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
    render(container, pageUrl) {
        return super.render(container, pageUrl, (container) => {
            //Attach classes to the container that are required by Orbeon
            container.classList.add('orbeon');
            container.classList.add('xforms-disable-alert-as-tooltip');
            container.classList.add('yui-skin-sam');
        });
    }
}

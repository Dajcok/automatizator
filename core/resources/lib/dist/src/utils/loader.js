/**
 * Used to load scripts from the given URL.
 * Useful for commonjs transpiled modules stored in the cloud.
 *
 * @param url
 * @param useCredentials
 */
export function loadScript(url, useCredentials = false) {
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = url;
        script.type = 'text/javascript';
        script.async = true;
        if (useCredentials) {
            script.crossOrigin = 'use-credentials';
        }
        script.onload = () => resolve();
        script.onerror = () => reject(new Error(`Failed to load script ${url}`));
        document.head.appendChild(script);
    });
}
/**
 * Used to load styles from the given URL.
 * Useful for commonjs transpiled modules stored in the cloud.
 *
 * @param url
 * @param useCredentials
 */
export function loadStyle(url, useCredentials = false) {
    return new Promise((resolve, reject) => {
        const link = document.createElement('link');
        link.href = url;
        link.rel = 'stylesheet';
        if (useCredentials) {
            link.crossOrigin = 'use-credentials';
        }
        link.onload = () => resolve();
        link.onerror = () => reject(new Error(`Failed to load style ${url}`));
        document.head.appendChild(link);
    });
}

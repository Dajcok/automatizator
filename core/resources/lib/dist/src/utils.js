/**
 * Process the given URL and return the absolute URL with the base URL prepended.
 * @param url
 * @param baseURL
 */
export const processUrl = (url, baseURL) => {
    const path = url.startsWith('/') ? url : '/' + url;
    return `${baseURL}${path}`;
};
/**
 * Extract script and link URLs from the given HTML container.
 * @param container
 */
export const extractResourceUrls = (container) => {
    const scriptSrcs = Array.from(container.querySelectorAll('script[src]')).map(script => script.getAttribute('src') || '');
    const linkHrefs = Array.from(container.querySelectorAll('link[href]')).map(link => link.getAttribute('href') || '');
    return { scriptSrcs, linkHrefs };
};
/**
 * Update image sources in the given container to use absolute URLs.
 * @param container
 * @param baseURL
 */
export const updateImageSrcs = (container, baseURL) => {
    const images = container.querySelectorAll('img[src]');
    images.forEach(image => {
        const src = image.getAttribute('src');
        if (src && !src.startsWith('http')) {
            image.setAttribute('src', processUrl(src, baseURL));
        }
    });
};

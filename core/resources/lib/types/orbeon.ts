/**
 * Type definitions for Orbeon Form Runner JavaScript API.
 * More informations about the API at https://doc.orbeon.com/form-runner/link-embed/javascript-api
 * More informations about the offlien mode at https://doc.orbeon.com/form-runner/link-embed/offline-embedding-api
 */

export type Orbeon = {
    fr: {
        API: {
            embedForm: (
                container: HTMLElement,
                context: string,
                app: string,
                form: string,
                mode: 'new' | 'edit' | 'view',
                documentId?: string,
                queryString?: string,
                headers?: Headers,
            ) => void;
        }
    }
}

export interface RenderOptions {
    appName: string;
    formName: string;
    formVersion: number;
    mode: 'new' | 'edit' | 'view';
    documentId?: string;
    queryString?: string;
    headers?: Headers;
    /**
     * If formData is defined, it must be a string containing form data in XML format.
     * This is the equivalent of performing an HTTP POST when online.
     */
    formData?: string;
}

export interface SubmissionRequest {
    method: string;
    url: URL;
    headers: Headers;
    body?: Uint8Array | ReadableStream<Uint8Array> | null
}

export interface SubmissionResponse {
    statusCode: number;
    headers: Headers;
    body?: Uint8Array | ReadableStream<Uint8Array> | null;
}

// Interface to be implemented by the embedder to support offline submissions
export interface SubmissionProvider {
    submit(req: SubmissionRequest): SubmissionResponse;

    submitAsync(req: SubmissionRequest): Promise<SubmissionResponse>;
}

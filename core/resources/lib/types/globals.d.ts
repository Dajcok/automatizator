import {Orbeon} from "./orbeon";
import {FormRenderer} from "../src/form_renderer";
import {FetchListener} from "../src/utils/fetch_listener";

declare global {
    interface Window {
        ORBEON?: Orbeon;
        fetchListener?: FetchListener;
        formRenderer?: FormRenderer;
        fetchListenerAttached?: boolean;
    }
}

export {};

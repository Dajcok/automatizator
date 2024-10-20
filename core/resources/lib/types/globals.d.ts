import {Orbeon} from "./orbeon";
import {FormRenderer} from "../src/form.renderer";
import {FetchListener} from "../src/utils/fetch_listener";
import {FormBuilderRenderer} from "../src/form_builder.renderer";

declare global {
    interface Window {
        ORBEON?: Orbeon;
        fetchListener?: FetchListener;
        formRenderer?: FormRenderer;
        fetchListenerAttached?: boolean;
        formBuilderRenderer?: FormBuilderRenderer;
    }
}

export {};

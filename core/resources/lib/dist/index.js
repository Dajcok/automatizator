import { FormRenderer } from "./src/form.renderer";
import axios from "axios";
import { FetchListener } from "./src/utils/fetch_listener";
import { loadStyle } from "./src/utils/loader";
import { FormBuilderRenderer } from "./src/form_builder.renderer";
const CORE_URL = "http://localhost:8001";
// const CORE_URL = "https://idocs2.klimekdeveloper.sk/services/core";
loadStyle(CORE_URL + '/css/core.css');
window.fetchListener = new FetchListener((context) => {
    if (context.url.startsWith('/')) {
        context.url = CORE_URL + context.url;
    }
    context.headers = Object.assign(Object.assign({}, context.headers), { 'Cache-Control': 'default', 'Pragma': 'public' });
    return context;
});
const _axios = axios.create({
    baseURL: CORE_URL,
    withCredentials: true,
});
window.formRenderer = new FormRenderer(_axios, CORE_URL);
window.formBuilderRenderer = new FormBuilderRenderer(_axios, CORE_URL);

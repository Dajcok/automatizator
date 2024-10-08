import { FormRenderer } from "./form_renderer";
import axios from "axios";
import { FetchListener } from "./fetch_listener";
import { loadStyle } from "./loader";
const CORE_URL = "http://localhost:8000";
loadStyle(CORE_URL + '/css/core.css');
window.fetchListener = new FetchListener((context) => {
    console.log('Request intercepted', context);
    if (context.url.startsWith('/')) {
        context.url = CORE_URL + context.url;
    }
    return context;
});
window.formRenderer = new FormRenderer(axios.create({
    baseURL: CORE_URL,
    withCredentials: true,
}));

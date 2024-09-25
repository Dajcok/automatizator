import {FormRenderer} from "./src/form_renderer";
import axios from "axios";
import {FetchListener} from "./src/utils/fetch_listener";
import {loadStyle} from "./src/utils/loader";

const PROXY_URL = "http://localhost:8001";
const CORE_URL = "http://localhost:8000";

loadStyle(CORE_URL + '/css/core.css');

window.fetchListener = new FetchListener(
    (context) => {
        console.log('Request intercepted', context);

        if (context.url.startsWith('/')) {
            context.url = PROXY_URL + context.url;
        }

        return context;
    },
);

window.formRenderer = new FormRenderer(
    axios.create({
        baseURL: CORE_URL as string,
        withCredentials: true,
    }),
    PROXY_URL,
);


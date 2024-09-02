import {SubmissionProvider as ISubmissionProvider, SubmissionRequest, SubmissionResponse} from '../types/orbeon';
import {AxiosInstance} from "axios";

//TODO: Implement the SubmissionProvider interface

export class SubmissionProvider implements ISubmissionProvider {
    constructor(
        private readonly axios: AxiosInstance,
    ) {
        if(!axios.defaults.baseURL) {
            throw new Error('axios baseURL is not defined');
        }
    }

    submit(req: SubmissionRequest): SubmissionResponse {
        return {
            statusCode: 200,
            headers: new Headers(),
        }
    }

    async submitAsync(req: SubmissionRequest): Promise<SubmissionResponse> {
        return {
            statusCode: 200,
            headers: new Headers(),
        }
    }
}

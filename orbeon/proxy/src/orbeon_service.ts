import axios, { AxiosInstance, AxiosResponse } from "axios";

export class OrbeonService {
    private client: AxiosInstance;

    constructor(client: AxiosInstance) {
        this.client = client;
    }

    private getAuthHeaderFromUser(): Record<string, string> {
        const user = {
            username: "anonymous",
            groups: ["public"],
            roles: [{ name: "public" }],
            organization: [],
        };

        return {
            "Orbeon-Credentials": JSON.stringify({
                username: user.username || "anonymous",
                groups: user.groups || ["public"],
                roles: user.roles || [{ name: "public" }],
                organizations: user.organization || [],
            }),
        };
    }

    async getResource(path: string, session: string): Promise<any> {
        try {
            const response: AxiosResponse = await this.client.get(
                `/orbeon/${path}`,
                {
                    headers: {
                        Cookie: `JSESSIONID=${session}`,
                    },
                },
            );

            return {
                content: response.data,
                contentType: response.headers["content-type"],
            };
        } catch (error) {
            throw new OrbeonException("Error fetching resource", 500, error);
        }
    }

    async postResource(
        path: string,
        session: string,
        body: string,
    ): Promise<any> {
        try {
            const response: AxiosResponse = await this.client.post(
                `/orbeon/${path}`,
                body,
                {
                    headers: {
                        Cookie: `JSESSIONID=${session}`,
                        "Content-Type": "application/xml",
                    },
                },
            );

            return {
                content: response.data,
                contentType: response.headers["content-type"],
            };
        } catch (error) {
            throw new OrbeonException("Error posting resource", 500, error);
        }
    }
}

export class OrbeonException extends Error {
    public detail: string = "";

    constructor(
        message: string,
        public code: number = 500,
        public originalError?: any,
    ) {
        super(message);
        this.name = "OrbeonException";
    }

    withDetail(detail: string): this {
        this.detail = detail;
        return this;
    }

    static fromHttpStatusCode(statusCode: number): OrbeonException {
        switch (statusCode) {
            case 401:
            case 440:
                return OrbeonException.unauthorized();
            case 404:
                return OrbeonException.formNotFound();
            case 403:
                return OrbeonException.forbidden();
            default:
                return OrbeonException.unexpectedError();
        }
    }

    static formNotFound(): OrbeonException {
        return new OrbeonException("Form not found", 404);
    }

    static forbidden(): OrbeonException {
        return new OrbeonException("Forbidden", 403);
    }

    static unauthorized(): OrbeonException {
        return new OrbeonException("Unauthorized", 401);
    }

    static unexpectedError(): OrbeonException {
        return new OrbeonException("Unexpected error", 500);
    }
}

export default new OrbeonService(axios.create({
    baseURL: "http://localhost:8080",
}));
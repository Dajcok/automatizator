import express, { Request, Response } from "express";
import orbeonService from "./orbeon_service";
import cors from "cors";
import cookieParser from "cookie-parser";

const app = express();
const port = 8002;

app.use(cors({
    credentials: true,
    origin: "http://localhost:8081",
}));
app.use(cookieParser());

app.get("/", (req: Request, res: Response) => {
    res.send("Hello, TypeScript with Express!");
});

app.listen(port, () => {
    console.log(`Server is running at http://localhost:${port}`);
});

app.use('/orbeon/*', express.text({ type: 'application/xml' }));

app.get("/orbeon/*", async (req: Request, res: Response) => {
    const path = req.params[0]

    try {
        const resource = await orbeonService.getResource(path, req.headers);

        Object.entries(resource.headers).forEach(([key, value]) => {
            if (value) {
                res.setHeader(key, value as string);
            }
        });

        res.end(resource.content);
    } catch (error) {
        console.error(error);
        res.status(500).send((error as Error).message);
    }
});

app.post("/orbeon/*", async (req: Request, res: Response) => {
    const path = req.params[0];

    try {
        const resource = await orbeonService.postResource(path, req.cookies.JSESSIONID, req.body);

        res.header("Content-Type", resource.contentType).send(resource.content);
    } catch (error) {
        console.error(error);
        res.status(500).send((error as Error).message);
    }
});
import express, { Request, Response } from "express";
import orbeonService from "./orbeon_service";
import cors from "cors";
import cookieParser from "cookie-parser";
import bodyParser from 'body-parser';
import bodyParserXml from 'body-parser-xml';

const app = express();
const port = 8001;

app.use(cors({
    credentials: true,
    origin: "null",
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
        const resource = await orbeonService.getResource(path, req.cookies.JSESSIONID);

        res.header("Content-Type", resource.contentType).send(resource.content);
    } catch (error) {
        res.status(500).send((error as Error).message);
    }
});

app.post("/orbeon/*", async (req: Request, res: Response) => {
    const path = req.params[0];

    console.log(req.body);

    try {
        const resource = await orbeonService.postResource(path, req.cookies.JSESSIONID, req.body);

        res.header("Content-Type", resource.contentType).send(resource.content);
    } catch (error) {
        res.status(500).send((error as Error).message);
    }
});
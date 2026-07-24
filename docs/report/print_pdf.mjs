import path from "node:path";
import { pathToFileURL } from "node:url";
import { createRequire } from "node:module";

const require = createRequire(
  "file:///C:/Users/user/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright/package.json",
);
const { chromium } = require("playwright");

const [htmlPath, pdfPath] = process.argv.slice(2);
if (!htmlPath || !pdfPath) {
  throw new Error("Usage: node print_pdf.mjs input.html output.pdf");
}

const browser = await chromium.launch({
  executablePath: "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe",
  headless: true,
});
try {
  const page = await browser.newPage();
  await page.goto(pathToFileURL(path.resolve(htmlPath)).href, { waitUntil: "networkidle" });
  await page.emulateMedia({ media: "print" });
  await page.pdf({
    path: path.resolve(pdfPath),
    format: "A4",
    printBackground: true,
    displayHeaderFooter: true,
    headerTemplate: "<span></span>",
    footerTemplate:
      '<div style="width:100%;text-align:center;font-family:Times New Roman;font-size:10px;color:#000;"><span class="pageNumber"></span></div>',
    margin: { top: "3cm", right: "3cm", bottom: "3cm", left: "4cm" },
    preferCSSPageSize: true,
  });
  console.log(`PDF=${path.resolve(pdfPath)}`);
} finally {
  await browser.close();
}

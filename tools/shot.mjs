// Captura rápida de uma zona: node shot.mjs <caminho> <largura> <scrollY> <saida> [altura]
import { chromium } from '@playwright/test';
import { chromiumPath } from './browser.mjs';
const [path, w, y, out, h] = process.argv.slice(2);
const b = await chromium.launch({ executablePath: chromiumPath() });
const p = await b.newPage({ viewport: { width: +w, height: +(h || 900) } });
await p.goto((process.env.BM_URL || 'http://localhost:8090') + path, { waitUntil: 'networkidle' });
await p.evaluate((y) => window.scrollTo(0, y), +y);
await p.waitForTimeout(900);
await p.screenshot({ path: out });
await b.close();

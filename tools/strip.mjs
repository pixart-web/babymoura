// Tira de capturas lado a lado (vista móvel), para rever uma página inteira: node strip.mjs <caminho> <largura> <saida> [colunas]
import { chromium } from '@playwright/test';
import { chromiumPath } from './browser.mjs';
const [path, w, out, cols = '5'] = process.argv.slice(2);
const b = await chromium.launch({ executablePath: chromiumPath() });
const p = await b.newPage({ viewport: { width: +w, height: 844 }, reducedMotion: 'reduce' });
await p.goto((process.env.BM_URL || 'http://localhost:8090') + path, { waitUntil: 'networkidle' });
const H = await p.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 500) { window.scrollTo(0, y); await new Promise(r => setTimeout(r, 30)); } window.scrollTo(0,0); return document.body.scrollHeight; });
await p.waitForTimeout(300);
const buf = await p.screenshot({ fullPage: true });
const n = +cols, seg = Math.ceil(H / n);
const q = await b.newPage({ viewport: { width: (+w + 10) * n, height: seg } });
await q.setContent(`<body style="margin:0;background:#888;display:flex;gap:10px">${Array.from({length:n},(_,i)=>`<div style="width:${w}px;height:${seg}px;overflow:hidden;position:relative"><img src="data:image/png;base64,${buf.toString('base64')}" style="position:absolute;top:${-i*seg}px;left:0"></div>`).join('')}</body>`);
await q.waitForTimeout(500);
await q.screenshot({ path: out, fullPage: true });
await b.close();

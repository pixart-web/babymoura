// Capturas reais de todas as páginas em 360, 390, 768, 1024 e 1440 px,
// com verificação de erros de consola, imagens partidas e scroll horizontal.
// Uso: BM_URL=http://localhost:8090 node screenshots.mjs [--only=/creche/] [--widths=390,1440]
import { chromium } from '@playwright/test';
import { mkdirSync, writeFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { chromiumPath } from './browser.mjs';

const here = dirname(fileURLToPath(import.meta.url));
const base = process.env.BM_URL || 'http://localhost:8090';
const out = join(here, '..', 'docs', 'capturas');
mkdirSync(out, { recursive: true });

const arg = (k) => (process.argv.find((a) => a.startsWith(`--${k}=`)) || '').split('=')[1];
const pages = arg('only') ? [arg('only')] : ['/', '/a-babymoura/', '/creche/', '/jardim-de-infancia/', '/espacos/', '/atividades-e-servicos/', '/contactos/', '/pre-inscricao/', '/privacidade/', '/cookies/', '/pagina-inexistente/'];
const widths = arg('widths') ? arg('widths').split(',').map(Number) : [360, 390, 768, 1024, 1440];

const browser = await chromium.launch({ executablePath: chromiumPath() });
const report = [];
for (const width of widths) {
  const ctx = await browser.newContext({ viewport: { width, height: width < 800 ? 800 : 900 }, deviceScaleFactor: 1, reducedMotion: 'reduce' });
  for (const path of pages) {
    const page = await ctx.newPage();
    const errors = [];
    page.on('console', (m) => m.type() === 'error' && errors.push(m.text()));
    page.on('pageerror', (e) => errors.push(String(e)));
    const res = await page.goto(base + path, { waitUntil: 'networkidle' });
    await page.evaluate(async () => {
      // Força o carregamento das imagens lazy antes da captura.
      for (let y = 0; y < document.body.scrollHeight; y += 600) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 40)); }
      window.scrollTo(0, 0);
    });
    await page.waitForTimeout(300);
    const checks = await page.evaluate(() => ({
      overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
      broken: [...document.images].filter((i) => i.complete && i.naturalWidth === 0).map((i) => i.src),
      h1: document.querySelectorAll('h1').length,
      title: document.title,
    }));
    const name = `${(path.replace(/\//g, '_').replace(/^_|_$/g, '') || 'inicio')}-${width}.png`;
    await page.screenshot({ path: join(out, name), fullPage: true });
    report.push({ path, width, status: res.status(), ...checks, errors });
    await page.close();
  }
  await ctx.close();
}
await browser.close();
writeFileSync(join(out, 'relatorio.json'), JSON.stringify(report, null, 2));
const problems = report.filter((r) => r.overflow > 0 || r.broken.length || r.errors.length || r.h1 !== 1);
console.log(`${report.length} capturas; ${problems.length} com alertas`);
for (const p of problems) console.log(p.path, p.width, 'overflow', p.overflow, 'broken', p.broken.length, 'h1', p.h1, p.errors.slice(0, 2));

// Abre cada página no editor de blocos (credenciais locais de .credentials.local) e
// verifica que todos os blocos são válidos e editáveis sem avisos de recuperação.
import { chromium } from '@playwright/test';
import { readFileSync, writeFileSync } from 'node:fs';
import { chromiumPath } from './browser.mjs';

const base = process.env.BM_URL || 'http://localhost:8090';
const cred = Object.fromEntries(readFileSync(new URL('../.credentials.local', import.meta.url), 'utf8').trim().split('\n').map((l) => l.split('=')));
const b = await chromium.launch({ executablePath: chromiumPath() });
const p = await b.newPage({ viewport: { width: 1440, height: 900 } });
await p.goto(base + '/wp-login.php');
await p.fill('#user_login', cred.USER);
await p.fill('#user_pass', cred.PASS);
await p.click('#wp-submit');
await p.waitForURL(/wp-admin/);
const ids = await p.evaluate(async () => (await (await fetch('/wp-json/wp/v2/pages?per_page=50&_fields=id,slug', { headers: { 'X-WP-Nonce': window.wpApiSettings?.nonce || '' } })).json()));
const pages = Array.isArray(ids) && ids.length ? ids : [];
const report = [];
for (const { id, slug } of pages) {
  await p.goto(`${base}/wp-admin/post.php?post=${id}&action=edit`);
  await p.waitForFunction(() => window.wp?.data?.select('core/block-editor')?.getBlocks().length > 0, null, { timeout: 30000 });
  await p.waitForTimeout(1500);
  const res = await p.evaluate(() => {
    const invalid = [];
    const walk = (blocks) => blocks.forEach((bl) => { if (!bl.isValid) invalid.push(bl.name); walk(bl.innerBlocks); });
    const all = wp.data.select('core/block-editor').getBlocks();
    walk(all);
    const count = (bl) => bl.reduce((n, x) => n + 1 + count(x.innerBlocks), 0);
    return { total: count(all), invalid };
  });
  report.push({ slug, ...res });
  console.log(res.invalid.length ? '✗' : '✓', slug, `${res.total} blocos`, res.invalid.join(', '));
}
if (!pages.length) console.log('Não foi possível listar páginas');
writeFileSync(new URL('../docs/testes/editor-blocos.json', import.meta.url), JSON.stringify(report, null, 2));
await p.screenshot({ path: new URL('../docs/capturas/editor-contactos.png', import.meta.url).pathname });
await b.close();

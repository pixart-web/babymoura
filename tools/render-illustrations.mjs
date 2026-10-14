// Rasteriza as ilustrações provisórias (SVG) para WebP, prontas para a biblioteca de media.
// Uso: node render-illustrations.mjs [nome]
import { chromium } from '@playwright/test';
import { chromiumPath } from './browser.mjs';
import { readdirSync, readFileSync, mkdirSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const here = dirname(fileURLToPath(import.meta.url));
const srcDir = join(here, 'illustrations');
const outDir = join(here, '..', 'wp-content', 'plugins', 'babymoura-core', 'content', 'media');
const tmpDir = join(here, 'test-results', 'raster');
mkdirSync(tmpDir, { recursive: true });

const only = process.argv[2];
const files = readdirSync(srcDir).filter((f) => f.endsWith('.svg') && (!only || f.includes(only)));
const browser = await chromium.launch({ executablePath: chromiumPath() });
const page = await browser.newPage({ deviceScaleFactor: 1 });
for (const file of files) {
  const svg = readFileSync(join(srcDir, file), 'utf8');
  const [, w, h] = svg.match(/viewBox="0 0 (\d+) (\d+)"/);
  await page.setViewportSize({ width: +w, height: +h });
  await page.setContent(`<html><body style="margin:0">${svg}</body></html>`);
  const png = join(tmpDir, file.replace('.svg', '.png'));
  await page.screenshot({ path: png, clip: { x: 0, y: 0, width: +w, height: +h } });
  const webp = join(outDir, file.replace('.svg', '.webp'));
  execFileSync('cwebp', ['-quiet', '-q', '84', png, '-o', webp]);
  console.log('✓', file, `${w}×${h}`);
}
await browser.close();

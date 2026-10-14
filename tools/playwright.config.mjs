import { defineConfig } from '@playwright/test';
import { chromiumPath } from './browser.mjs';

export default defineConfig({
  testDir: './tests',
  timeout: 60_000,
  workers: 1,
  reporter: [['list'], ['json', { outputFile: '../docs/testes/resultados.json' }]],
  use: {
    baseURL: process.env.BM_URL || 'http://localhost:8090',
    launchOptions: { executablePath: chromiumPath() },
    locale: 'pt-PT',
    contextOptions: { reducedMotion: 'reduce' },
  },
  projects: [
    { name: 'desktop', use: { viewport: { width: 1440, height: 900 } } },
    { name: 'mobile', use: { viewport: { width: 390, height: 844 }, hasTouch: true, isMobile: true } },
  ],
});

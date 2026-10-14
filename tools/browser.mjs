// Localiza um Chromium instalado para as ferramentas locais (Playwright).
import { existsSync, readdirSync } from 'node:fs';
import { join } from 'node:path';
import { homedir } from 'node:os';

export function chromiumPath() {
  if (process.env.BM_CHROMIUM) return process.env.BM_CHROMIUM;
  const cache = join(homedir(), 'Library', 'Caches', 'ms-playwright');
  if (!existsSync(cache)) return undefined;
  for (const dir of readdirSync(cache).filter((d) => d.startsWith('chromium-')).sort().reverse()) {
    const candidate = join(cache, dir, 'chrome-mac-arm64', 'Google Chrome for Testing.app', 'Contents', 'MacOS', 'Google Chrome for Testing');
    if (existsSync(candidate)) return candidate;
  }
  return undefined;
}

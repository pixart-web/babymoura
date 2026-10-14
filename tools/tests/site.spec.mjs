// Fluxos reais do site em modo demonstração (nenhum dado é enviado ou guardado).
import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import { execSync } from 'node:child_process';

// Limpa limites de pedidos/duplicados (transients) entre execuções de teste.
const resetLimits = () => execSync('docker-compose -f ../docker-compose.yml run --rm -T cli wp transient delete --all', { stdio: 'ignore' });

const PAGES = ['/', '/a-babymoura/', '/creche/', '/jardim-de-infancia/', '/espacos/', '/atividades-e-servicos/', '/contactos/', '/pre-inscricao/', '/privacidade/', '/cookies/'];

test.describe('Páginas e ligações', () => {
  for (const path of PAGES) {
    test(`abre diretamente ${path}`, async ({ page }) => {
      const errors = [];
      page.on('pageerror', (e) => errors.push(String(e)));
      page.on('console', (m) => m.type() === 'error' && errors.push(m.text()));
      const res = await page.goto(path);
      expect(res.status()).toBe(200);
      await expect(page.locator('h1')).toHaveCount(1);
      expect(await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth)).toBeLessThanOrEqual(0);
      const broken = await page.evaluate(async () => {
        const imgs = [...document.images];
        for (const i of imgs) { i.loading = 'eager'; }
        await Promise.all(imgs.map((i) => (i.complete ? null : new Promise((r) => { i.onload = i.onerror = r; }))));
        return imgs.filter((i) => i.naturalWidth === 0).map((i) => i.src);
      });
      expect(broken).toEqual([]);
      expect(errors).toEqual([]);
      // Sem texto de exemplo esquecido.
      const text = await page.locator('main').innerText();
      expect(text).not.toMatch(/lorem ipsum|\[rascunho|placeholder/i);
    });
  }

  test('todas as ligações internas têm destino útil', async ({ page, request }) => {
    const seen = new Set();
    for (const path of PAGES) {
      await page.goto(path);
      const hrefs = await page.$$eval('a[href]', (as) => as.map((a) => a.href));
      hrefs.filter((h) => h.startsWith('http://localhost')).forEach((h) => seen.add(h.split('#')[0]));
    }
    for (const url of seen) {
      const res = await request.get(url, { maxRedirects: 0 });
      expect([200, 301], url).toContain(res.status());
    }
    expect(seen.size).toBeGreaterThan(10);
  });

  test('não há botões ou ligações vazias/decorativas', async ({ page }) => {
    for (const path of PAGES) {
      await page.goto(path);
      const empty = await page.$$eval('a, button', (els) => els.filter((e) => {
        const name = (e.getAttribute('aria-label') || e.innerText || e.querySelector('img')?.alt || '').trim();
        const href = e.tagName === 'A' ? e.getAttribute('href') : 'x';
        return e.checkVisibility({ visibilityProperty: true, opacityProperty: true }) && (!name || !href || href === '#');
      }).map((e) => e.outerHTML.slice(0, 120)));
      expect(empty, path).toEqual([]);
    }
  });

  test('redirecionamentos 301 dos URLs antigos', async ({ request }) => {
    const map = { '/a-nossa-missao/': '/a-babymoura/', '/as-nossas-instalacoes/': '/espacos/', '/conheca-a-creche/': '/creche/', '/conheca-o-jardim-de-infancia/': '/jardim-de-infancia/', '/servicios/': '/atividades-e-servicos/', '/inscricao/': '/pre-inscricao/', '/politica-de-privacidade/': '/privacidade/' };
    for (const [from, to] of Object.entries(map)) {
      const res = await request.get(from, { maxRedirects: 0 });
      expect(res.status(), from).toBe(301);
      expect(new URL(res.headers().location).pathname).toBe(to);
    }
    expect((await request.get('/pagina-que-nao-existe/', { maxRedirects: 0 })).status()).toBe(404);
  });

  test('preview não indexável e SEO por página', async ({ page, request }) => {
    const res = await page.goto('/creche/');
    expect(res.headers()['x-robots-tag']).toContain('noindex');
    await expect(page.locator('meta[name="robots"]')).toHaveAttribute('content', /noindex/);
    await expect(page).toHaveTitle(/Creche em Vilamoura/);
    await expect(page.locator('meta[name="description"]')).toHaveAttribute('content', /creche/i);
    await expect(page.locator('link[rel="canonical"]')).toHaveAttribute('href', /\/creche\/$/);
    const robots = await (await request.get('/robots.txt')).text();
    expect(robots).toContain('Disallow: /');
    await page.goto('/');
    const ld = await page.$$eval('script[type="application/ld+json"]', (s) => s.map((x) => JSON.parse(x.textContent)));
    const org = ld.find((d) => Array.isArray(d['@type']) && d['@type'].includes('ChildCare'));
    expect(org.telephone).toBe('+351289302934');
    expect(org).not.toHaveProperty('aggregateRating');
    expect(org).not.toHaveProperty('openingHours');
  });

  test('dados em conflito ficam fora da publicação', async ({ page }) => {
    for (const path of PAGES) {
      await page.goto(path);
      const text = await page.locator('body').innerText();
      expect(text, path).not.toMatch(/1\s?[45]00\s?m|8h30|18h30|20h00|8 horas às 20|4 meses|0 aos 3/i);
    }
  });
});

test.describe('Navegação', () => {
  test('menu móvel operável por teclado', async ({ page, isMobile }) => {
    test.skip(!isMobile, 'apenas móvel');
    await page.goto('/');
    const open = page.getByRole('button', { name: /abrir menu/i });
    await open.focus();
    await page.keyboard.press('Enter');
    const dialog = page.locator('.wp-block-navigation__responsive-container.is-menu-open');
    await expect(dialog).toBeVisible();
    await expect(dialog.getByRole('link', { name: 'Espaços' })).toBeVisible();
    await expect(dialog.getByRole('link', { name: /Ligar/ })).toBeVisible();
    await page.keyboard.press('Escape');
    await expect(dialog).toBeHidden();
    await expect(open).toBeFocused();
  });

  test('submenu Educação por teclado (desktop)', async ({ page, isMobile }) => {
    test.skip(isMobile, 'apenas desktop');
    await page.goto('/');
    const toggle = page.locator('.bm-nav .wp-block-navigation-submenu__toggle');
    await toggle.focus();
    await page.keyboard.press('Enter');
    await expect(toggle).toHaveAttribute('aria-expanded', 'true');
    await expect(page.getByRole('link', { name: /Jardim de Infância/ }).first()).toBeVisible();
    await page.keyboard.press('Escape');
    await expect(toggle).toHaveAttribute('aria-expanded', 'false');
  });

  test('link para saltar para o conteúdo', async ({ page, isMobile }) => {
    test.skip(isMobile, 'teclado físico');
    await page.goto('/');
    await page.keyboard.press('Tab');
    const skip = page.locator('.skip-link');
    await expect(skip).toBeFocused();
    await page.keyboard.press('Enter');
    expect(await page.evaluate(() => !!document.activeElement.closest('main'))).toBe(true);
  });
});

test.describe('Componentes', () => {
  test('acordeão de perguntas', async ({ page }) => {
    await page.goto('/');
    const buttons = page.locator('.bm-faq__toggle');
    await expect(buttons.first()).toHaveAttribute('aria-expanded', 'true');
    const second = buttons.nth(1);
    await expect(second).toHaveAttribute('aria-expanded', 'false');
    await second.focus();
    await page.keyboard.press('Enter');
    await expect(second).toHaveAttribute('aria-expanded', 'true');
    await expect(page.locator('#' + (await second.getAttribute('aria-controls')))).toBeVisible();
  });

  test('galeria: filtros e visualização ampliada por teclado', async ({ page }) => {
    await page.goto('/espacos/');
    const items = page.locator('.bm-gallery__item:not([hidden])');
    const total = await items.count();
    expect(total).toBeGreaterThan(5);
    await page.getByRole('button', { name: 'Horta pedagógica' }).click();
    await expect(page.getByRole('button', { name: 'Horta pedagógica' })).toHaveAttribute('aria-pressed', 'true');
    await expect(items).toHaveCount(1);
    await page.getByRole('button', { name: 'Todos' }).click();
    await expect(items).toHaveCount(total);
    const first = page.locator('.bm-gallery__open').first();
    await first.focus();
    await page.keyboard.press('Enter');
    const dialog = page.locator('dialog.bm-lightbox');
    await expect(dialog).toBeVisible();
    await expect(dialog).toHaveAttribute('aria-label', `Imagem 1 de ${total}`);
    await page.keyboard.press('ArrowRight');
    await expect(dialog).toHaveAttribute('aria-label', `Imagem 2 de ${total}`);
    await page.keyboard.press('Escape');
    await expect(dialog).toBeHidden();
    await expect(first).toBeFocused();
  });

  test('atividades distinguem etapa e tipo, com estado de validação', async ({ page }) => {
    await page.goto('/atividades-e-servicos/');
    await expect(page.locator('table.bm-matrix caption')).toHaveText(/Atividades por resposta educativa/);
    await expect(page.locator('.bm-pending').first()).toBeVisible();
    const row = page.locator('table.bm-matrix tr', { hasText: 'Iniciação à língua inglesa' });
    await expect(row.locator('td').nth(0)).toHaveText(/Extracurricular/);
    await expect(row.locator('td').nth(1)).toHaveText(/Curricular/);
  });
});

test.describe('Formulários (demonstração)', () => {
  test.beforeEach(() => resetLimits());
  test('pedido de visita: erros ligados aos campos, depois sucesso de demonstração', async ({ page }) => {
    await page.goto('/contactos/');
    const form = page.locator('form[data-bm-form="visita"]');
    await expect(form.getByText('Demonstração — os dados não serão enviados.')).toBeVisible();
    await form.getByRole('button', { name: 'Enviar pedido de visita' }).click();
    const summary = form.locator('.bm-form__summary');
    await expect(summary).toBeVisible();
    await expect(summary).toBeFocused();
    const name = form.getByLabel(/Nome do adulto/);
    await expect(name).toHaveAttribute('aria-invalid', 'true');
    const errId = (await name.getAttribute('aria-describedby')).split(' ').pop();
    await expect(page.locator('#' + errId)).toHaveText('Indique o seu nome.');
    await form.getByLabel('Email').fill('nao-e-email');
    await form.getByRole('button', { name: 'Enviar pedido de visita' }).click();
    await expect(page.locator('#' + (await form.getByLabel('Email').getAttribute('aria-describedby')).split(' ').pop())).toContainText('não parece válido');

    await name.fill('Teste Demonstração');
    await form.getByLabel('Email').fill('teste@exemplo.pt');
    await form.getByLabel(/Resposta educativa/).selectOption('creche');
    await page.waitForTimeout(3200); // tempo mínimo anti-spam
    let requests = 0;
    page.on('request', (r) => r.url().includes('/babymoura/v1/forms/visita') && requests++);
    const submit = form.getByRole('button', { name: 'Enviar pedido de visita' });
    // Duplo envio no mesmo instante (duplo clique/Enter repetido): só um pedido deve sair.
    await form.evaluate((f) => { f.requestSubmit(); f.requestSubmit(); });
    const status = form.locator('.bm-form__status');
    await expect(status).toHaveAttribute('data-state', 'demo');
    await expect(status).toContainText('não foram enviados nem guardados');
    expect(requests).toBe(1);
  });

  test('pré-inscrição: nota de não confirmação e campos mínimos', async ({ page }) => {
    await page.goto('/pre-inscricao/');
    const form = page.locator('form[data-bm-form="pre-inscricao"]');
    await expect(form).toContainText('não confirma vaga nem inscrição');
    const names = await form.locator('input, select, textarea').evaluateAll((els) => els.map((e) => e.name));
    expect(names).not.toEqual(expect.arrayContaining(['birth_date', 'child_name', 'nif']));
    await form.getByLabel(/Nome do adulto/).fill('Teste');
    await form.getByLabel('Email').fill('teste@exemplo.pt');
    await form.getByLabel(/Resposta educativa/).selectOption('jardim');
    await form.getByLabel(/Idade da criança/).selectOption('3-4');
    await form.getByLabel('Mês de entrada').selectOption('09');
    const years = await form.getByLabel('Ano de entrada').locator('option').allTextContents();
    await form.getByLabel('Ano de entrada').selectOption(years[years.length - 1]);
    await page.waitForTimeout(3200);
    await form.getByRole('button', { name: 'Enviar pré-inscrição' }).click();
    await expect(form.locator('.bm-form__status')).toHaveAttribute('data-state', 'demo');
  });
});

test.describe('Servidor (API de formulários)', () => {
  test.beforeEach(() => resetLimits());
  const token = async (request) => (await (await request.get('/wp-json/babymoura/v1/forms/token')).json()).token;

  test('validação no servidor, honeypot, tempo mínimo, token inválido e tamanho', async ({ request, isMobile }) => {
    test.skip(isMobile, 'uma vez basta');
    const url = '/wp-json/babymoura/v1/forms/visita';
    const fresh = await token(request);
    let res = await request.post(url, { data: { bm_token: fresh, name: 'Ana', email: 'ana@exemplo.pt', stage: 'creche' } });
    expect(res.status()).toBe(429); // enviado depressa demais
    expect((await res.json()).status).toBe('too_fast');
    await new Promise((r) => setTimeout(r, 3200));
    res = await request.post(url, { data: { bm_token: 'x', name: 'Ana', email: 'ana@exemplo.pt', stage: 'creche' } });
    expect(res.status()).toBe(400);
    expect((await res.json()).token).toBeTruthy(); // cliente recebe token novo para repetir
    res = await request.post(url, { data: { bm_token: fresh, name: 'A', email: 'x', stage: 'nada', message: 'x'.repeat(1001) } });
    expect(res.status()).toBe(422);
    expect(Object.keys((await res.json()).errors).sort()).toEqual(['email', 'message', 'name', 'stage']);
    res = await request.post(url, { data: { bm_token: fresh, bm_website: 'spam', name: 'Bot' } });
    expect((await res.json()).status).toBe('demo'); // honeypot: resposta neutra, sem efeito
    res = await request.post(url, { data: { bm_token: fresh, message: 'x'.repeat(13000) } });
    expect(res.status()).toBe(413);
  });

});

test.describe('Servidor: duplicados e limite de pedidos', () => {
  test('mesmo pedido repetido é tratado como duplicado; excesso é limitado', async ({ request, isMobile }) => {
    test.skip(isMobile, 'uma vez basta');
    resetLimits();
    const tok = (await (await request.get('/wp-json/babymoura/v1/forms/token')).json()).token;
    await new Promise((r) => setTimeout(r, 3200));
    const send = (email, id) => request.post('/wp-json/babymoura/v1/forms/visita', { data: { bm_token: tok, bm_request_id: id, name: 'Teste', email, stage: 'creche' } });
    expect((await (await send('a@exemplo.pt', 'id-1')).json()).status).toBe('demo');
    expect((await (await send('a@exemplo.pt', 'id-1')).json()).status).toBe('demo');
    let last;
    for (let i = 0; i < 11; i++) last = await send(`p${i}@exemplo.pt`, `id-x${i}`);
    expect(last.status()).toBe(429);
    expect((await last.json()).status).toBe('rate_limited');
    resetLimits();
  });
});

test.describe('Acessibilidade (axe, WCAG 2.2 AA)', () => {
  for (const path of PAGES) {
    test(`sem violações ${path}`, async ({ page }) => {
      await page.goto(path);
      const results = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa']).analyze();
      const summary = results.violations.map((v) => `${v.id}: ${v.nodes.slice(0, 3).map((n) => n.target.join(' ')).join(' | ')}`);
      expect(summary).toEqual([]);
    });
  }
});

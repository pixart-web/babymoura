# Evidências de testes (29/09/2026, ambiente local Docker, WordPress 7.1.2, PHP 8.3)

| Verificação | Resultado | Evidência |
|---|---|---|
| Playwright, desktop 1440 e móvel 390 (`tools/tests/site.spec.mjs`) | **65 passaram**, 5 ignorados de propósito (testes só de desktop ou só de móvel), 0 falhas | `resultados.json` |
| Acessibilidade axe (WCAG 2.0/2.1/2.2 A e AA), 10 páginas × 2 viewports | 0 violações | idem |
| Capturas 360/390/768/1024/1440 × 11 páginas | 55 capturas; 0 com scroll horizontal, imagens partidas, erros de consola ou H1 em falta (a 404 regista o seu próprio estado 404, como esperado) | `../capturas/`, `../capturas/relatorio.json` |
| Editor de blocos | 10/10 páginas abrem sem blocos inválidos | `editor-blocos.json`, `../capturas/editor-contactos.png` |
| Modo produção com Mailpit (`tools/test-production.sh`) | 15/15 verificações | `producao-local.txt` |
| Lighthouse 12, mobile (emulação), laboratório local | Performance: Creche, Espaços e Contactos 98; Início 75 na 1.ª medição (TBT 1,03 s, a rever). Acessibilidade 100, Boas práticas 100. SEO 69 **só por causa do `noindex` intencional do preview**. LCP 2,3–2,5 s, CLS 0. | `lighthouse/*.report.html` |

## Coberto pelos testes automáticos

- Todas as páginas abrem diretamente (200), com um único H1 e sem texto de exemplo.
- Todas as ligações internas encontradas respondem 200 ou 301.
- Nenhum botão ou ligação visível sem nome ou destino.
- Redirecionamentos 301 dos URLs antigos e 404 para URLs desconhecidos.
- `noindex` (cabeçalho e meta), `robots.txt`, títulos e descrições únicos, canonical, JSON-LD sem avaliações nem horário.
- Dados em conflito (1400/1500 m², 8h30/18h30/20h00, 4 meses / 0 aos 3) ausentes de todas as páginas.
- Menu móvel por teclado (abrir, Esc, foco devolvido); submenu Educação por teclado; link para saltar para o conteúdo.
- Acordeão de perguntas; galeria: filtros com `aria-pressed`, `<dialog>` com setas, Esc e foco devolvido.
- Formulários:
  - erros ligados aos campos (`aria-invalid`, `aria-describedby`) e resumo com foco;
  - envio duplo simultâneo gera um único pedido;
  - estado de demonstração;
  - pré-inscrição sem campos sensíveis.
- API: validação no servidor (422), tempo mínimo (429), token inválido com renovação (400), honeypot neutro, limite de tamanho (413), duplicados e limite de pedidos (429).

## Limitações conhecidas

- As métricas de campo (LCP/INP/CLS reais) só são mensuráveis em produção.
- O Lighthouse local corre sem CDN e sem cache de página. Em produção recomenda-se cache de página e HTTP/2+.
- Testes de leitor de ecrã manuais (VoiceOver/NVDA) e de zoom a 400 % ainda por executar com utilizadores.

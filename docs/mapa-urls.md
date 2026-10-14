# Mapa de URLs e redirecionamentos

Implementado em `wp-content/plugins/babymoura-core/content/redirects.json` (301, página a página, preserva query string). Testado automaticamente (`tools/tests/site.spec.mjs`, "redirecionamentos 301").

| URL atual | Nova página | Tipo |
|---|---|---|
| `/` | `/` | Mantido |
| `/a-nossa-missao/` | `/a-babymoura/` | 301 |
| `/as-nossas-instalacoes/` | `/espacos/` | 301 |
| `/conheca-a-creche/` | `/creche/` | 301 |
| `/conheca-o-jardim-de-infancia/` | `/jardim-de-infancia/` | 301 |
| `/servicios/` | `/atividades-e-servicos/` | 301 |
| `/servicos/`, `/os-nossos-servicos/` (variantes prováveis) | `/atividades-e-servicos/` | 301 preventivo |
| `/inscricao/` | `/pre-inscricao/` | 301 |
| `/contactos/` | `/contactos/` | Mantido |
| `/politica-de-privacidade/` | `/privacidade/` | 301 |
| (nova) | `/cookies/` | Nova |
| URLs desconhecidos | Página 404 útil, com ligações | Sem redirecionamento para a página inicial |

## Antes do lançamento

1. Exportar do Google Search Console / Site Kit (instalado no site atual) as páginas com tráfego e ligações externas e acrescentar ao mapa as que faltarem (anexos, feeds, páginas antigas).
2. Testar os 301 no domínio real após a migração (a checklist tem este item).
3. Submeter `/wp-sitemap.xml` na Search Console. O sitemap só inclui páginas.
4. Remover o `noindex`: modo `production`, `WP_ENVIRONMENT_TYPE=production` e "Visibilidade para motores de pesquisa" ativa. Confirmar `robots.txt`.

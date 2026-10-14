# BabyMoura — proposta de novo website

Proposta navegável e editável do novo website da **BabyMoura, creche e jardim de infância em Vilamoura**.
Construída em **WordPress 7.1** com tema de blocos próprio e um plugin pequeno. Tudo corre localmente em Docker.

> **Estado: proposta privada em modo demonstração.** Os formulários validam os dados, mas não os enviam nem guardam.
> O site está com `noindex` e não substitui o site atual. Antes de publicar, ver
> [docs/dependencias-producao.md](docs/dependencias-producao.md).

## Arranque rápido

Requisitos: Docker (com `docker compose` ou `docker-compose`) e ligação à Internet na primeira instalação.

```bash
bin/setup.sh
```

- Site: <http://localhost:8090>
- Painel: <http://localhost:8090/wp-admin/> (credenciais geradas em `.credentials.local`, que não deve ser versionado)
- Emails locais (Mailpit): <http://localhost:8026>. Nenhum email sai para a Internet.

Para reinstalar de raiz (apaga a base de dados local): `bin/setup.sh --reset`.

## Estrutura

| Caminho | Conteúdo |
|---|---|
| `wp-content/themes/babymoura/` | Tema de blocos: `theme.json` (tokens), modelos, cabeçalho/rodapé, padrões de secção e de página, CSS, JS, fontes Nunito/Inter locais (OFL), logótipo. |
| `wp-content/plugins/babymoura-core/` | Comportamento: dados da instituição, atividades, serviços, FAQ, galeria, formulários, SEO, redirecionamentos, checklist de lançamento e comandos WP-CLI. |
| `wp-content/plugins/babymoura-core/content/` | Conteúdo da proposta em JSON (importação/exportação) e imagens a importar. |
| `tools/` | Testes end-to-end (Playwright + axe), capturas, teste do modo de produção, verificação do editor e rasterização das ilustrações. |
| `docs/` | Inventário de imagens, matriz de conteúdos, mapa de URLs, dependências, roteiro de apresentação, evidências de testes e capturas. |

## Gerir conteúdos (sem código)

Tudo está no menu **BabyMoura** do painel:

- **Checklist de lançamento**: o que falta confirmar ou configurar, calculado automaticamente.
- **Dados da instituição**: telefone, email, morada, mapa, horário, redes e ligações legais. É a fonte única: altera o cabeçalho, o rodapé, os contactos e os dados estruturados.
- **Atividades / Serviços / Perguntas frequentes**: cada item tem etapa, tipo, origem da informação e **estado de validação**.
  - `Pendente`: aparece só na proposta, com a marca "A confirmar".
  - `Confirmado`: aparece também em produção.
  - Rascunhos do WordPress nunca aparecem.
- **Pedidos recebidos** (só em produção): consulta, reenvio da notificação e eliminação automática ao fim do prazo definido.
- **Media → Espaços (galeria)**: cada imagem tem categoria, legenda, texto alternativo e **autorização de uso** (`pendente`, `autorizada`, `ilustração provisória`).

As páginas editam-se no editor de blocos. Os blocos da BabyMoura (contactos, horário, atividades, serviços, FAQ, galeria, formulário, ícone) lêem sempre a fonte central. Os títulos e as descrições para pesquisa editam-se no painel "Pesquisa (SEO)" de cada página.

## Comandos úteis

```bash
docker-compose run --rm cli wp babymoura status          # checklist de lançamento no terminal
docker-compose run --rm cli wp babymoura seed --force    # reimporta os conteúdos da proposta
docker-compose run --rm cli wp babymoura export          # exporta os conteúdos editados para exports/
```

## Testes

```bash
cd tools && npm install
npx playwright test          # 65 testes: páginas, ligações, redirecionamentos, SEO, teclado, galeria, formulários, API, axe
node screenshots.mjs         # capturas a 360/390/768/1024/1440 px + relatório (docs/capturas)
node editor-check.mjs        # abre todas as páginas no editor e confirma que os blocos são válidos
./test-production.sh         # modo produção local com Mailpit: guarda, notifica, trata falhas de email
```

O Playwright usa um Chromium/Chrome for Testing já instalado (ver `tools/browser.mjs` ou definir `BM_CHROMIUM`).

## Modos e configuração

Tudo por variáveis de ambiente (ver `.env.example`). Não há segredos no frontend nem no repositório.

| Variável | Efeito |
|---|---|
| `BABYMOURA_MODE` | `demo` (por omissão) ou `production`. |
| `BABYMOURA_FORM_RECIPIENT` | Email que recebe os pedidos. Em produção, se estiver vazio, os formulários recusam o envio e mostram os contactos alternativos. |
| `BABYMOURA_FORM_FROM` | Remetente técnico, num domínio com SPF/DKIM. |
| `BABYMOURA_SMTP_*` | Transporte SMTP. Localmente usa o Mailpit. |
| `BABYMOURA_PREVIEW_AUTH` | Protege o preview com HTTP Basic (`utilizador:palavra-passe`). |

## Inglês (futuro)

Todas as strings do tema e do plugin usam funções de tradução (`babymoura`, `babymoura-core`). As páginas são conteúdo editável, preparado para um plugin multilíngue ou multisite. Não há seletor de idioma visível enquanto não existir tradução completa.

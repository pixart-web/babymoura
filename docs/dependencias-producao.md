# Dependências para produção

O que está pronto, o que é demonstração e o que depende da instituição ou de configuração. A checklist no painel (**BabyMoura → Checklist de lançamento**) acompanha estes pontos automaticamente.

## 1. Implementado e testado

- Tema de blocos BabyMoura: 10 páginas, cabeçalho e rodapé, padrões reutilizáveis, tokens em `theme.json`, fontes locais.
- Plugin BabyMoura Core:
  - fonte central de contactos e horário;
  - atividades, serviços e FAQ com estado de validação;
  - galeria com filtros e visualização ampliada acessível;
  - formulários de visita e pré-inscrição com validação no cliente e no servidor, honeypot, token assinado com tempo mínimo, limite de pedidos por IP (guardado apenas como hash), prevenção de duplicados e limite de tamanho;
  - armazenamento privado com eliminação automática, notificação por SMTP com repetição em caso de falha;
  - SEO: títulos e descrições por página, canonical, Open Graph, JSON-LD verificável, sitemap só com páginas;
  - `noindex` no preview, redirecionamentos 301, checklist e WP-CLI (seed, export, status).
- Testes (ver `docs/testes/`):
  - 65 testes Playwright (desktop e móvel), incluindo axe WCAG 2.2 AA sem violações;
  - 55 capturas sem scroll horizontal, sem imagens partidas e sem erros de consola;
  - 10/10 páginas com blocos válidos no editor;
  - modo produção local validado com Mailpit (guarda, notifica, trata falhas, evita duplicados, sem dados pessoais nos logs);
  - Lighthouse mobile em laboratório (ver `docs/testes/lighthouse/`).

## 2. Funciona apenas como demonstração

- **Formulários**: em `BABYMOURA_MODE=demo` validam e respondem "Demonstração — os dados não serão enviados.". Nada é guardado nem enviado.
- **Imagens**: 8 ilustrações provisórias identificadas como tal. A fotografia real (fachada) tem autorização pendente.
- **Conteúdos "A confirmar"**: atividades e serviços do site atual, visíveis apenas no modo demonstração.
- **Textos legais**: propostos, não validados juridicamente.
- **Medição**: eventos `visit_cta`, `form_start` e `form_submit_valid` preparados (sem dados pessoais), mas desativados. Não há ferramenta de analytics instalada.

## 3. Depende de acesso, aprovação ou configuração

| Área | O que falta | Responsável |
|---|---|---|
| Dados | Confirmar horário, idade mínima da creche, história (ano de fundação), localização, designação legal e NIF, fax | Direção |
| Oferta | Validar cada atividade e serviço (disponibilidade, idades, inclusão na mensalidade), FAQ de alimentação e de adaptação | Direção pedagógica |
| Imagens | Sessão fotográfica, consentimentos de imagem das famílias e da equipa, logótipo vetorial | Instituição + fotógrafo |
| Legal | Validação da Política de Privacidade (fundamento, prazos, subcontratantes, localização dos servidores) e de Cookies, do Livro de Reclamações e da entidade RAL | Instituição / jurista |
| Email | `BABYMOURA_FORM_RECIPIENT`, `BABYMOURA_FORM_FROM` no domínio (SPF/DKIM/DMARC), SMTP autenticado (`BABYMOURA_SMTP_*`) | Alojamento / TI |
| Alojamento | PHP 8.1+, MySQL/MariaDB, HTTPS, cópias de segurança, cron real (em vez do WP-Cron por visita) para a eliminação de pedidos e repetição de emails | Alojamento |
| Preview | Publicar num subdomínio privado com `BABYMOURA_PREVIEW_AUTH` e `noindex` | Equipa técnica |
| Lançamento | Migrar conteúdos (`wp babymoura export` / WXR), `search-replace` do domínio, modo produção, `WP_ENVIRONMENT_TYPE=production`, visibilidade para motores de pesquisa, testar 301, Search Console | Equipa técnica |
| Medição | Decidir ferramenta e consentimento. Se houver cookies não essenciais: banner de consentimento e atualização da Política de Cookies | Instituição |
| Desempenho real | LCP/INP/CLS de campo só são mensuráveis em produção (CrUX/Search Console) | Pós-lançamento |
| Inglês | Tradução das strings (`.po`) e das páginas, e plugin multilíngue | Futuro |
| Contas | Criar utilizadores "Editor" para a equipa (não partilhar o administrador) | Instituição |

## Fora do âmbito (não desenvolvido)

Portal de pais, pagamentos, gestão escolar, chat com IA, aplicações móveis, calendário de marcações.

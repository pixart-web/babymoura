<?php
/**
 * Title: Política de Cookies
 * Slug: babymoura/page-cookies
 * Categories: babymoura-pages
 * Post Types: page
 * Inserter: no
 * Description: Texto proposto, coerente com a configuração atual (sem medição nem cookies de terceiros).
 *
 * @package BabyMoura
 */

$bm_blocks = array(
	babymoura_group( 'bm-callout bm-callout--draft', babymoura_p( '<strong>' . esc_html__( 'Texto proposto para validação.', 'babymoura' ) . '</strong> ' . esc_html__( 'Deve ser revisto pela instituição e atualizado se for ativada qualquer medição de audiência ou conteúdo de terceiros.', 'babymoura' ) ) ),
	babymoura_p( esc_html__( 'Os cookies são pequenos ficheiros que um website guarda no seu dispositivo. Esta página explica como este website os utiliza.', 'babymoura' ), 'bm-lead' ),
	babymoura_h( __( 'O que usamos hoje', 'babymoura' ) ),
	babymoura_p( esc_html__( 'Durante a navegação pública, este website não guarda cookies no seu dispositivo: não usamos cookies de análise, de publicidade nem de redes sociais, e as fontes e imagens são servidas a partir do próprio site.', 'babymoura' ) ),
	babymoura_p( esc_html__( 'A área de gestão do site usa cookies técnicos de sessão, apenas para a equipa autenticada.', 'babymoura' ) ),
	babymoura_h( __( 'Ligações externas', 'babymoura' ) ),
	babymoura_p( esc_html__( 'As ligações para o mapa, para as redes sociais ou para o Livro de Reclamações abrem serviços externos, com as suas próprias políticas de cookies. Nada é carregado desses serviços enquanto não os abrir.', 'babymoura' ) ),
	babymoura_h( __( 'Se isto mudar', 'babymoura' ) ),
	babymoura_p( esc_html__( 'Se no futuro for ativada medição de audiência ou outro serviço que use cookies não essenciais, pediremos o seu consentimento antes e atualizaremos esta página.', 'babymoura' ) ),
);
echo implode( "\n\n", $bm_blocks ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput

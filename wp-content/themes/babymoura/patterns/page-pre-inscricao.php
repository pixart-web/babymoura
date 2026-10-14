<?php
/**
 * Title: Página Pré-inscrição
 * Slug: babymoura/page-pre-inscricao
 * Categories: babymoura-pages
 * Post Types: page
 * Inserter: no
 *
 * @package BabyMoura
 */

echo babymoura_page_hero( // phpcs:ignore WordPress.Security.EscapeOutput
	array(
		'eyebrow' => __( 'Pré-inscrição', 'babymoura' ),
		'title'   => __( 'Um primeiro passo, sem complicações', 'babymoura' ),
		'lead'    => __( 'Manifeste o seu interesse na creche ou no jardim de infância. São poucos dados, e nenhum documento nesta fase.', 'babymoura' ),
		'image'   => '',
		'tone'    => 'lavanda',
		'cta'     => false,
	)
);

echo babymoura_group( // phpcs:ignore WordPress.Security.EscapeOutput
	'bm-form-section bm-form-section--pre',
	babymoura_wide(
		'bm-split bm-split--form',
		babymoura_group(
			'bm-split__text bm-form-intro',
			babymoura_h( __( 'Como funciona', 'babymoura' ) ) . "\n\n"
			. babymoura_list(
				array(
					'<strong>' . esc_html__( 'Preenche o formulário.', 'babymoura' ) . '</strong> ' . esc_html__( 'Os seus contactos, a etapa pretendida, a idade aproximada da criança e quando gostaria que começasse.', 'babymoura' ),
					'<strong>' . esc_html__( 'A equipa entra em contacto.', 'babymoura' ) . '</strong> ' . esc_html__( 'Para conhecer a sua família e esclarecer dúvidas.', 'babymoura' ),
					'<strong>' . esc_html__( 'Explicamos os passos seguintes.', 'babymoura' ) . '</strong> ' . esc_html__( 'Disponibilidade, visita e o processo de inscrição.', 'babymoura' ),
				),
				'bm-mini-steps',
				true
			) . "\n\n"
			. babymoura_group(
				'bm-callout',
				babymoura_p( '<strong>' . esc_html__( 'Importante:', 'babymoura' ) . '</strong> ' . esc_html__( 'o envio constitui uma manifestação de interesse e não confirma vaga nem inscrição.', 'babymoura' ) )
			) . "\n\n"
			. babymoura_p( esc_html__( 'Prefere conhecer primeiro o espaço?', 'babymoura' ) . ' <a href="' . esc_url( home_url( '/contactos/#pedido-de-visita' ) ) . '">' . esc_html__( 'Pedir uma visita', 'babymoura' ) . '</a>', 'bm-small' )
		) . "\n\n"
		. '<!-- wp:babymoura/form {"formType":"pre-inscricao"} /-->'
	),
	'formulario-pre-inscricao',
	'section',
	true
) . "\n";

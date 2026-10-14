<?php
/**
 * Title: Página Contactos e pedido de visita
 * Slug: babymoura/page-contactos
 * Categories: babymoura-pages
 * Post Types: page
 * Inserter: no
 *
 * @package BabyMoura
 */

echo babymoura_page_hero( // phpcs:ignore WordPress.Security.EscapeOutput
	array(
		'eyebrow' => __( 'Contactos', 'babymoura' ),
		'title'   => __( 'Fale connosco', 'babymoura' ),
		'lead'    => __( 'Estamos no centro de Vilamoura, a poucos passos da Marina. Ligue, escreva ou deixe um pedido de visita — a equipa responde para combinar consigo.', 'babymoura' ),
		'image'   => '',
		'tone'    => 'areia',
		'cta'     => false,
	)
);

echo babymoura_group( // phpcs:ignore WordPress.Security.EscapeOutput
	'bm-contact-main',
	babymoura_wide(
		'bm-split bm-split--contact',
		babymoura_group(
			'bm-contact-card',
			babymoura_h( __( 'Contactos e morada', 'babymoura' ) ) . "\n\n"
			. '<!-- wp:babymoura/contact-details /-->'
		) . "\n\n"
		. babymoura_image_block( 'fachada', 'is-style-bm-rounded bm-contact-photo', __( 'A entrada da BabyMoura, na Rua das Estrelas.', 'babymoura' ) )
	),
	'',
	'section',
	true
) . "\n\n";

echo babymoura_group( // phpcs:ignore WordPress.Security.EscapeOutput
	'bm-form-section',
	babymoura_wide(
		'bm-split bm-split--form',
		babymoura_group(
			'bm-split__text bm-form-intro',
			babymoura_p( esc_html__( 'Visitas', 'babymoura' ), 'bm-eyebrow' ) . "\n\n"
			. babymoura_h( __( 'Pedir uma visita', 'babymoura' ) ) . "\n\n"
			. babymoura_p( esc_html__( 'Conte-nos o que procura. O pedido não é uma marcação automática: a equipa entra em contacto para combinar o dia e a hora.', 'babymoura' ) ) . "\n\n"
			. babymoura_list(
				array(
					esc_html__( 'Recebemos o seu pedido.', 'babymoura' ),
					esc_html__( 'A equipa contacta-o por email ou telefone.', 'babymoura' ),
					esc_html__( 'Combinamos a visita consigo.', 'babymoura' ),
				),
				'bm-mini-steps',
				true
			) . "\n\n"
			. babymoura_p( esc_html__( 'Já conhece a BabyMoura?', 'babymoura' ) . ' <a href="' . esc_url( home_url( '/pre-inscricao/' ) ) . '">' . esc_html__( 'Iniciar uma pré-inscrição', 'babymoura' ) . '</a>', 'bm-small' )
		) . "\n\n"
		. '<!-- wp:babymoura/form {"formType":"visita"} /-->'
	),
	'pedido-de-visita',
	'section',
	true
) . "\n";

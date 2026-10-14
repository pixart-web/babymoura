<?php
/**
 * Title: Página Creche
 * Slug: babymoura/page-creche
 * Categories: babymoura-pages
 * Post Types: page
 * Inserter: no
 *
 * @package BabyMoura
 */

echo babymoura_page_hero( // phpcs:ignore WordPress.Security.EscapeOutput
	array(
		'eyebrow' => __( 'Creche · primeira infância', 'babymoura' ),
		'title'   => __( 'Os primeiros passos, com afeto e segurança', 'babymoura' ),
		'lead'    => __( 'Muito carinho, atenção e um ambiente cuidadosamente organizado para responder às necessidades de cada criança nos primeiros anos.', 'babymoura' ),
		'image'   => 'ilustracao-creche',
		'tone'    => 'pessego',
	)
);

echo babymoura_points_section( // phpcs:ignore WordPress.Security.EscapeOutput
	__( 'O que importa nesta fase', 'babymoura' ),
	__( 'Os primeiros sorrisos, dentes, passos e palavras acontecem depressa. Cada um merece tempo, atenção e um ambiente preparado.', 'babymoura' ),
	array(
		array( 'heart', 'peach', __( 'Laços afetivos', 'babymoura' ), __( 'Consideramos os laços afetivos indispensáveis a um crescimento harmonioso: é neles que nascem a autoestima e a confiança.', 'babymoura' ) ),
		array( 'star', 'yellow', __( 'Estímulos de qualidade', 'babymoura' ), __( 'Experiências variadas e adequadas a cada momento do desenvolvimento, para explorar com todos os sentidos.', 'babymoura' ) ),
		array( 'shield', 'sage', __( 'Segurança e higiene', 'babymoura' ), __( 'Segurança, qualidade e higiene estão sempre no centro das nossas preocupações de gestão.', 'babymoura' ) ),
		array( 'home', 'lavender', __( 'Conforto e bem-estar', 'babymoura' ), __( 'Espaços pensados para brincar, descansar e ser cuidado, com o conforto que os mais pequenos precisam.', 'babymoura' ) ),
	),
	'bm-points--creme',
	__( 'Primeira infância', 'babymoura' )
);

echo babymoura_group( // phpcs:ignore WordPress.Security.EscapeOutput
	'bm-spaces-teaser bm-spaces-teaser--pessego',
	babymoura_wide(
		'bm-split',
		babymoura_group(
			'bm-split__text',
			babymoura_p( esc_html__( 'Espaços da creche', 'babymoura' ), 'bm-eyebrow' ) . "\n\n"
			. babymoura_h( __( 'Salas à medida dos mais pequenos', 'babymoura' ) ) . "\n\n"
			. babymoura_p( esc_html__( 'Salas amplas e climatizadas, com luz natural e materiais lúdicos e pedagógicos. O berçário reúne zona de repouso, sala de brincar, fraldário e copa de leites; as restantes salas organizam-se por idades.', 'babymoura' ) ) . "\n\n"
			. babymoura_link( home_url( '/espacos/' ), __( 'Ver os espaços', 'babymoura' ) )
		) . "\n\n"
		. babymoura_image_block( 'ilustracao-sala-luz', 'is-style-bm-organic bm-split__image' )
	),
	'',
	'section',
	true
) . "\n\n";

echo babymoura_group( // phpcs:ignore WordPress.Security.EscapeOutput
	'bm-stage-activities',
	babymoura_wide(
		'bm-section-head',
		babymoura_p( esc_html__( 'Atividades', 'babymoura' ), 'bm-eyebrow' ) . "\n\n"
		. babymoura_h( __( 'Atividades na creche', 'babymoura' ) ) . "\n\n"
		. babymoura_p( esc_html__( 'Identificamos sempre o que é curricular e o que é extracurricular. As condições de cada atividade são explicadas pela equipa.', 'babymoura' ), 'bm-lead' )
	) . "\n\n"
	. '<!-- wp:babymoura/activities {"stage":"creche","align":"wide"} /-->' . "\n\n"
	. babymoura_wide( 'bm-inline-links', babymoura_link( home_url( '/atividades-e-servicos/' ), __( 'Todas as atividades e serviços', 'babymoura' ) ) ),
	'',
	'section',
	true
) . "\n\n";

echo babymoura_group( // phpcs:ignore WordPress.Security.EscapeOutput
	'bm-note-section',
	babymoura_wide(
		'bm-note',
		babymoura_group( 'bm-note__icon', '<!-- wp:babymoura/icon {"name":"chat","tone":"yellow"} /-->' ) . "\n\n"
		. babymoura_group(
			'bm-note__text',
			babymoura_h( __( 'Idades de entrada, horários e disponibilidade', 'babymoura' ), 3 ) . "\n\n"
			. babymoura_p( esc_html__( 'Estas informações são esclarecidas diretamente pela equipa, para que recebam sempre dados atualizados. Ligue-nos ou peça uma visita.', 'babymoura' ) )
		)
	),
	'',
	'section',
	true
) . "\n\n";

require __DIR__ . '/partials/cta-final.php';

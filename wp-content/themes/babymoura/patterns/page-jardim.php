<?php
/**
 * Title: Página Jardim de Infância
 * Slug: babymoura/page-jardim
 * Categories: babymoura-pages
 * Post Types: page
 * Inserter: no
 *
 * @package BabyMoura
 */

echo babymoura_page_hero( // phpcs:ignore WordPress.Security.EscapeOutput
	array(
		'eyebrow' => __( 'Jardim de Infância · dos 3 aos 6 anos', 'babymoura' ),
		'title'   => __( 'Aprender a descobrir, em conjunto', 'babymoura' ),
		'lead'    => __( 'No pré-escolar, os projetos nascem dos interesses das crianças. Cada dia traz experiências para explorar, criar e crescer em grupo.', 'babymoura' ),
		'image'   => 'ilustracao-jardim-infancia',
		'tone'    => 'salvia',
	)
);

echo babymoura_points_section( // phpcs:ignore WordPress.Security.EscapeOutput
	__( 'Como se aprende no jardim de infância', 'babymoura' ),
	__( 'Uma metodologia ativa, em que as crianças participam e as diferentes áreas se trabalham de forma integrada.', 'babymoura' ),
	array(
		array( 'sprout', 'sage', __( 'Projetos com as crianças', 'babymoura' ), __( 'Os projetos partem das necessidades e dos interesses do grupo, para oferecer a maior diversidade de experiências.', 'babymoura' ) ),
		array( 'family', 'peach', __( 'Aprender em grupo', 'babymoura' ), __( 'Uma resposta pedagógica diferenciada e centrada na cooperação: cada criança beneficia do que se constrói em conjunto.', 'babymoura' ) ),
		array( 'seed', 'yellow', __( 'Autonomia e cidadania', 'babymoura' ), __( 'Ganhar autonomia, autoestima e valores de cidadania, ao ritmo de cada criança.', 'babymoura' ) ),
		array( 'palette', 'lavender', __( 'Criatividade e diversidade', 'babymoura' ), __( 'Valorizamos os saberes e a diversidade cultural de cada família como ponto de partida para novas aprendizagens.', 'babymoura' ) ),
	),
	'bm-points--creme',
	__( 'Descoberta', 'babymoura' )
);

echo babymoura_group( // phpcs:ignore WordPress.Security.EscapeOutput
	'bm-families bm-families--salvia',
	babymoura_wide(
		'bm-split bm-split--reverse',
		babymoura_image_block( 'ilustracao-horta', 'is-style-bm-organic bm-split__image' ) . "\n\n"
		. babymoura_group(
			'bm-split__text',
			babymoura_p( esc_html__( 'Famílias', 'babymoura' ), 'bm-eyebrow' ) . "\n\n"
			. babymoura_h( __( 'As famílias fazem parte do percurso', 'babymoura' ) ) . "\n\n"
			. babymoura_p( esc_html__( 'A educação pré-escolar é a primeira etapa da educação básica e complementa a ação educativa da família. Por isso damos especial relevância ao envolvimento e à participação dos pais no dia a dia do jardim de infância.', 'babymoura' ) ) . "\n\n"
			. babymoura_p( esc_html__( 'Os grupos ocupam salas amplas e com luz natural, com acesso ao jardim exterior e à horta pedagógica.', 'babymoura' ) ) . "\n\n"
			. babymoura_link( home_url( '/espacos/' ), __( 'Ver os espaços', 'babymoura' ) )
		)
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
		. babymoura_h( __( 'Atividades no jardim de infância', 'babymoura' ) ) . "\n\n"
		. babymoura_p( esc_html__( 'Curriculares e extracurriculares, sempre identificadas. As condições de cada atividade são explicadas pela equipa.', 'babymoura' ), 'bm-lead' )
	) . "\n\n"
	. '<!-- wp:babymoura/activities {"stage":"jardim","align":"wide"} /-->' . "\n\n"
	. babymoura_wide( 'bm-inline-links', babymoura_link( home_url( '/atividades-e-servicos/' ), __( 'Todas as atividades e serviços', 'babymoura' ) ) ),
	'',
	'section',
	true
) . "\n\n";

require __DIR__ . '/partials/cta-final.php';

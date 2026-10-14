<?php
/**
 * Title: Página A BabyMoura
 * Slug: babymoura/page-about
 * Categories: babymoura-pages
 * Post Types: page
 * Inserter: no
 *
 * @package BabyMoura
 */

echo babymoura_page_hero( // phpcs:ignore WordPress.Security.EscapeOutput
	array(
		'eyebrow' => __( 'A BabyMoura', 'babymoura' ),
		'title'   => __( 'Mais de três décadas a acompanhar os primeiros anos', 'babymoura' ),
		'lead'    => __( 'Uma instituição de Vilamoura, lembrada por muitas famílias pelo carinho e pela alegria do seu ambiente educativo.', 'babymoura' ),
		'image'   => 'fachada',
		'tone'    => 'areia',
	)
);

echo babymoura_group( // phpcs:ignore WordPress.Security.EscapeOutput
	'bm-story',
	babymoura_wide(
		'bm-split bm-split--story',
		babymoura_group(
			'bm-split__text',
			babymoura_p( esc_html__( 'A nossa história', 'babymoura' ), 'bm-eyebrow' ) . "\n\n"
			. babymoura_h( __( 'Um lugar de referência no centro de Vilamoura', 'babymoura' ) ) . "\n\n"
			. babymoura_p( esc_html__( 'Há mais de trinta anos que a BabyMoura acolhe crianças e famílias no centro de Vilamoura, a poucos passos da Marina. Muitas das famílias que por aqui passaram — e passam — recordam o carinho e a alegria vividos no dia a dia.', 'babymoura' ) ) . "\n\n"
			. babymoura_p( esc_html__( 'Ao longo deste percurso, o foco manteve-se: um espaço inteiramente dedicado à primeira infância e ao pré-escolar, onde cada criança se possa desenvolver de forma harmoniosa, num ambiente alegre, seguro, afetuoso e estimulante.', 'babymoura' ) )
		) . "\n\n"
		. babymoura_group(
			'bm-quote',
			babymoura_p( esc_html__( 'Proporcionar a todas as crianças a felicidade de se desenvolverem harmoniosamente, num ambiente alegre, seguro, afetuoso e estimulante.', 'babymoura' ), 'bm-quote__text' ) . "\n\n"
			. babymoura_p( esc_html__( 'A missão da BabyMoura', 'babymoura' ), 'bm-quote__cite' )
		)
	),
	'',
	'section',
	true
) . "\n\n";

echo babymoura_points_section( // phpcs:ignore WordPress.Security.EscapeOutput
	__( 'A nossa missão, em quatro compromissos', 'babymoura' ),
	__( 'A primeira infância é decisiva para o desenvolvimento cognitivo, motor, social e emocional de cada criança. É isso que fundamenta o nosso trabalho.', 'babymoura' ),
	array(
		array( 'seed', 'sage', __( 'Autonomia e respeito', 'babymoura' ), __( 'Ajudar cada criança a conquistar autonomia, autoestima e respeito pelos outros.', 'babymoura' ) ),
		array( 'palette', 'peach', __( 'Expressão e criatividade', 'babymoura' ), __( 'Dar espaço às capacidades expressivas e criativas de cada criança.', 'babymoura' ) ),
		array( 'sun', 'yellow', __( 'Um ambiente alegre', 'babymoura' ), __( 'Um dia a dia rico em experiências, onde a vontade de descobrir se mantém viva.', 'babymoura' ) ),
		array( 'chat', 'lavender', __( 'Diálogo com as famílias', 'babymoura' ), __( 'Conversa, apoio e partilha de informação com as famílias, em permanência.', 'babymoura' ) ),
	),
	'bm-points--lavanda',
	__( 'Missão', 'babymoura' )
);

echo babymoura_group( // phpcs:ignore WordPress.Security.EscapeOutput
	'bm-families',
	babymoura_wide(
		'bm-split bm-split--reverse',
		babymoura_image_block( 'ilustracao-sala-luz', 'is-style-bm-organic bm-split__image' ) . "\n\n"
		. babymoura_group(
			'bm-split__text',
			babymoura_p( esc_html__( 'Com as famílias', 'babymoura' ), 'bm-eyebrow' ) . "\n\n"
			. babymoura_h( __( 'Educar é um trabalho a várias mãos', 'babymoura' ) ) . "\n\n"
			. babymoura_p( esc_html__( 'Os pais e cuidadores são parte do percurso. Valorizamos uma relação próxima, de confiança, em que a informação circula nos dois sentidos e as famílias participam na educação dos filhos.', 'babymoura' ) ) . "\n\n"
			. babymoura_p( esc_html__( 'O bem-estar de cada criança e o respeito pelas suas características individuais são os nossos principais objetivos — e só se cumprem em conjunto.', 'babymoura' ) ) . "\n\n"
			. babymoura_link( home_url( '/contactos/' ), __( 'Falar com a equipa', 'babymoura' ) )
		)
	),
	'',
	'section',
	true
) . "\n\n";

require __DIR__ . '/partials/cta-final.php';

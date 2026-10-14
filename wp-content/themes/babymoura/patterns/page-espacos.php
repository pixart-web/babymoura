<?php
/**
 * Title: Página Espaços
 * Slug: babymoura/page-espacos
 * Categories: babymoura-pages
 * Post Types: page
 * Inserter: no
 *
 * @package BabyMoura
 */

echo babymoura_page_hero( // phpcs:ignore WordPress.Security.EscapeOutput
	array(
		'eyebrow' => __( 'Espaços', 'babymoura' ),
		'title'   => __( 'Espaços para brincar, descansar e descobrir', 'babymoura' ),
		'lead'    => __( 'Salas amplas e climatizadas, com luz natural, e um amplo jardim exterior com relva, árvores e horta pedagógica.', 'babymoura' ),
		'image'   => 'ilustracao-exterior',
		'tone'    => 'salvia',
	)
);

echo babymoura_points_section( // phpcs:ignore WordPress.Security.EscapeOutput
	__( 'Dentro e fora de portas', 'babymoura' ),
	'',
	array(
		array( 'door', 'peach', __( 'Salas interiores', 'babymoura' ), __( 'Amplas, climatizadas e com iluminação natural, equipadas com materiais lúdicos e pedagógicos e organizadas por idades.', 'babymoura' ) ),
		array( 'tree', 'sage', __( 'Jardim exterior', 'babymoura' ), __( 'Relvado, árvores e equipamentos de exterior adequados às idades, sobre pavimentos que amortecem as quedas.', 'babymoura' ) ),
		array( 'garden', 'yellow', __( 'Horta pedagógica', 'babymoura' ), __( 'Canteiros onde as crianças semeiam, regam e acompanham o crescimento das plantas.', 'babymoura' ) ),
	),
	'bm-points--creme bm-points--three'
);

echo babymoura_group( // phpcs:ignore WordPress.Security.EscapeOutput
	'bm-gallery-section',
	babymoura_wide(
		'bm-section-head',
		babymoura_h( __( 'Galeria', 'babymoura' ) ) . "\n\n"
		. babymoura_p( esc_html__( 'Escolha um espaço para filtrar. Selecione uma imagem para a ver em tamanho maior; pode navegar com as setas do teclado.', 'babymoura' ), 'bm-lead' )
	) . "\n\n"
	. '<!-- wp:babymoura/gallery {"align":"wide"} /-->',
	'galeria',
	'section',
	true
) . "\n\n";

require __DIR__ . '/partials/cta-final.php';

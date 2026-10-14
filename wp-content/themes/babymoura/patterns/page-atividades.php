<?php
/**
 * Title: Página Atividades e Serviços
 * Slug: babymoura/page-atividades
 * Categories: babymoura-pages
 * Post Types: page
 * Inserter: no
 *
 * @package BabyMoura
 */

echo babymoura_page_hero( // phpcs:ignore WordPress.Security.EscapeOutput
	array(
		'eyebrow' => __( 'Atividades e Serviços', 'babymoura' ),
		'title'   => __( 'O que cada etapa oferece', 'babymoura' ),
		'lead'    => __( 'Organizado por resposta educativa, com as atividades curriculares e extracurriculares sempre identificadas, e os serviços complementares à parte.', 'babymoura' ),
		'image'   => 'ilustracao-movimento',
		'tone'    => 'lavanda',
	)
);

$bm_stage_section = static function ( string $stage, string $title, string $anchor, string $class ) {
	return babymoura_group(
		'bm-offer ' . $class,
		babymoura_wide(
			'bm-section-head',
			babymoura_h( $title )
		) . "\n\n"
		. babymoura_wide(
			'bm-offer__grid',
			babymoura_group(
				'bm-offer__col',
				babymoura_h( __( 'Atividades curriculares', 'babymoura' ), 3, 'bm-offer__title' ) . "\n\n"
				. '<!-- wp:babymoura/activities {"stage":"' . $stage . '","kind":"curricular"} /-->'
			) . "\n\n"
			. babymoura_group(
				'bm-offer__col',
				babymoura_h( __( 'Atividades extracurriculares', 'babymoura' ), 3, 'bm-offer__title' ) . "\n\n"
				. '<!-- wp:babymoura/activities {"stage":"' . $stage . '","kind":"extracurricular"} /-->'
			)
		),
		$anchor,
		'section',
		true
	) . "\n\n";
};

echo $bm_stage_section( 'creche', __( 'Na creche', 'babymoura' ), 'creche', 'bm-offer--pessego' ); // phpcs:ignore WordPress.Security.EscapeOutput
echo $bm_stage_section( 'jardim', __( 'No jardim de infância', 'babymoura' ), 'jardim-de-infancia', 'bm-offer--salvia' ); // phpcs:ignore WordPress.Security.EscapeOutput

echo babymoura_group( // phpcs:ignore WordPress.Security.EscapeOutput
	'bm-offer bm-offer--creme',
	babymoura_wide(
		'bm-section-head',
		babymoura_h( __( 'Serviços complementares', 'babymoura' ) ) . "\n\n"
		. babymoura_p( esc_html__( 'Serviços da instituição, independentes das atividades. Para saber o que está incluído, horários e condições, fale com a equipa.', 'babymoura' ), 'bm-lead' )
	) . "\n\n"
	. '<!-- wp:babymoura/services {"align":"wide"} /-->' . "\n\n"
	. babymoura_wide( 'bm-hours-row', '<!-- wp:babymoura/hours /-->' ),
	'servicos',
	'section',
	true
) . "\n\n";

echo babymoura_group( // phpcs:ignore WordPress.Security.EscapeOutput
	'bm-offer bm-offer--summary',
	babymoura_wide(
		'bm-section-head',
		babymoura_h( __( 'Resumo por etapa', 'babymoura' ) ) . "\n\n"
		. babymoura_p( esc_html__( 'A mesma informação numa tabela, para comparar as duas respostas educativas.', 'babymoura' ), 'bm-lead' )
	) . "\n\n"
	. '<!-- wp:babymoura/activities {"layout":"matrix","align":"wide"} /-->',
	'resumo',
	'section',
	true
) . "\n\n";

require __DIR__ . '/partials/cta-final.php';

<?php
/**
 * Tema BabyMoura: apresentação. Comportamentos (formulários, dados, SEO) vivem no plugin BabyMoura Core.
 *
 * @package BabyMoura
 */

defined( 'ABSPATH' ) || exit;

define( 'BABYMOURA_THEME_VERSION', '0.9.0' );

add_action(
	'after_setup_theme',
	static function () {
		load_theme_textdomain( 'babymoura', get_template_directory() . '/languages' );
		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/css/main.css' );
		remove_theme_support( 'core-block-patterns' );
	}
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_enqueue_style( 'babymoura', get_theme_file_uri( 'assets/css/main.css' ), array(), BABYMOURA_THEME_VERSION );
		wp_enqueue_script( 'babymoura', get_theme_file_uri( 'assets/js/site.js' ), array(), BABYMOURA_THEME_VERSION, array( 'strategy' => 'defer' ) );
	}
);

// Pré-carrega apenas as fontes usadas acima da dobra.
add_action(
	'wp_head',
	static function () {
		foreach ( array( 'nunito-latin-wght.woff2', 'inter-latin-wght.woff2' ) as $font ) {
			printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( get_theme_file_uri( 'assets/fonts/' . $font ) ) );
		}
	},
	1
);

add_action(
	'init',
	static function () {
		register_block_pattern_category( 'babymoura', array( 'label' => __( 'BabyMoura — secções', 'babymoura' ) ) );
		register_block_pattern_category( 'babymoura-pages', array( 'label' => __( 'BabyMoura — páginas completas', 'babymoura' ) ) );

		register_block_style( 'core/group', array( 'name' => 'bm-surface-salvia', 'label' => __( 'Superfície sálvia', 'babymoura' ) ) );
		register_block_style( 'core/group', array( 'name' => 'bm-surface-pessego', 'label' => __( 'Superfície pêssego', 'babymoura' ) ) );
		register_block_style( 'core/group', array( 'name' => 'bm-surface-lavanda', 'label' => __( 'Superfície lavanda', 'babymoura' ) ) );
		register_block_style( 'core/image', array( 'name' => 'bm-organic', 'label' => __( 'Recorte orgânico', 'babymoura' ) ) );
		register_block_style( 'core/image', array( 'name' => 'bm-rounded', 'label' => __( 'Cantos arredondados', 'babymoura' ) ) );
		register_block_style( 'core/list', array( 'name' => 'bm-leaves', 'label' => __( 'Lista com folhas', 'babymoura' ) ) );
		register_block_style( 'core/button', array( 'name' => 'bm-soft', 'label' => __( 'Secundário suave', 'babymoura' ) ) );
	}
);

/**
 * Imagem importada pelo seed, por identificador estável (ex.: "ilustracao-creche").
 * Usada nos padrões para que as páginas apontem para anexos reais da biblioteca.
 *
 * @param string $slug Identificador da imagem.
 * @return array{id:int,url:string,alt:string}
 */
function babymoura_media( string $slug ): array {
	$map = (array) get_option( 'bm_media_map', array() );
	$id  = (int) ( $map[ $slug ] ?? 0 );
	if ( ! $id ) {
		return array( 'id' => 0, 'url' => '', 'alt' => '' );
	}
	$src = wp_get_attachment_image_src( $id, 'large' );
	return array(
		'id'  => $id,
		'url' => $src ? $src[0] : '',
		'alt' => (string) get_post_meta( $id, '_wp_attachment_image_alt', true ),
	);
}

/**
 * Bloco de imagem serializado para os padrões.
 *
 * @param string $slug    Identificador da imagem.
 * @param string $class   Classes adicionais (ex.: "is-style-bm-organic").
 * @param string $caption Legenda opcional.
 */
function babymoura_image_block( string $slug, string $class = '', string $caption = '' ): string {
	$m = babymoura_media( $slug );
	if ( ! $m['id'] ) {
		return '';
	}
	$attrs = array(
		'id'              => $m['id'],
		'sizeSlug'        => 'large',
		'linkDestination' => 'none',
	);
	if ( $class ) {
		$attrs['className'] = $class;
	}
	$fig_class = trim( 'wp-block-image size-large ' . $class );
	$cap       = $caption ? '<figcaption class="wp-element-caption">' . esc_html( $caption ) . '</figcaption>' : '';
	return '<!-- wp:image ' . wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES ) . ' -->' . "\n"
		. '<figure class="' . esc_attr( $fig_class ) . '"><img src="' . esc_url( $m['url'] ) . '" alt="' . esc_attr( $m['alt'] ) . '" class="wp-image-' . (int) $m['id'] . '"/>' . $cap . '</figure>' . "\n"
		. '<!-- /wp:image -->';
}

/**
 * Valor da fonte central (plugin), com recurso seguro se o plugin estiver inativo.
 *
 * @param string $key      Chave.
 * @param string $fallback Valor alternativo.
 */
function babymoura_setting( string $key, string $fallback = '' ): string {
	return function_exists( 'bm_setting' ) ? (string) bm_setting( $key, $fallback ) : $fallback;
}

/**
 * Hero das páginas interiores (serializado em blocos editáveis).
 *
 * @param array $args eyebrow, title, lead, image (slug), tone (salvia|pessego|lavanda|areia), cta (bool).
 */
function babymoura_page_hero( array $args ): string {
	$a     = wp_parse_args(
		$args,
		array(
			'eyebrow' => '',
			'title'   => '',
			'lead'    => '',
			'image'   => '',
			'tone'    => 'salvia',
			'cta'     => true,
		)
	);
	$class = 'bm-page-hero bm-page-hero--' . sanitize_html_class( $a['tone'] ) . ( $a['image'] ? '' : ' bm-page-hero--text' );
	$out   = '<!-- wp:group {"tagName":"section","align":"full","className":"' . esc_attr( $class ) . '","layout":{"type":"constrained"}} -->' . "\n";
	$out  .= '<section class="wp-block-group alignfull ' . esc_attr( $class ) . '"><!-- wp:group {"align":"wide","className":"bm-page-hero__grid","layout":{"type":"default"}} -->' . "\n";
	$out  .= '<div class="wp-block-group alignwide bm-page-hero__grid"><!-- wp:group {"className":"bm-page-hero__text","layout":{"type":"default"}} -->' . "\n";
	$out  .= '<div class="wp-block-group bm-page-hero__text">';
	if ( $a['eyebrow'] ) {
		$out .= '<!-- wp:paragraph {"className":"bm-eyebrow"} -->' . "\n" . '<p class="bm-eyebrow">' . esc_html( $a['eyebrow'] ) . '</p>' . "\n" . '<!-- /wp:paragraph -->' . "\n\n";
	}
	$out .= '<!-- wp:heading {"level":1} -->' . "\n" . '<h1 class="wp-block-heading">' . esc_html( $a['title'] ) . '</h1>' . "\n" . '<!-- /wp:heading -->';
	if ( $a['lead'] ) {
		$out .= "\n\n" . '<!-- wp:paragraph {"className":"bm-lead"} -->' . "\n" . '<p class="bm-lead">' . esc_html( $a['lead'] ) . '</p>' . "\n" . '<!-- /wp:paragraph -->';
	}
	if ( $a['cta'] ) {
		$out .= "\n\n" . '<!-- wp:buttons -->' . "\n" . '<div class="wp-block-buttons"><!-- wp:button -->' . "\n"
			. '<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="' . esc_url( home_url( '/contactos/#pedido-de-visita' ) ) . '">' . esc_html__( 'Pedir uma visita', 'babymoura' ) . '</a></div>' . "\n"
			. '<!-- /wp:button --></div>' . "\n" . '<!-- /wp:buttons -->';
	}
	$out .= '</div>' . "\n" . '<!-- /wp:group -->';
	if ( $a['image'] ) {
		$out .= "\n\n" . babymoura_image_block( $a['image'], 'is-style-bm-organic bm-page-hero__image' );
	}
	$out .= '</div>' . "\n" . '<!-- /wp:group --></section>' . "\n" . '<!-- /wp:group -->' . "\n\n";
	return $out;
}

/**
 * Secção com título e grelha de pontos (ícone + título + texto).
 *
 * @param string $title   Título H2.
 * @param string $lead    Texto introdutório.
 * @param array  $items   Lista de [icon, tone, title, text].
 * @param string $class   Classe da secção.
 * @param string $eyebrow Sobretítulo.
 */
function babymoura_points_section( string $title, string $lead, array $items, string $class = '', string $eyebrow = '' ): string {
	$cls = trim( 'bm-points ' . $class );
	$out = '<!-- wp:group {"tagName":"section","align":"full","className":"' . esc_attr( $cls ) . '","layout":{"type":"constrained"}} -->' . "\n";
	$out .= '<section class="wp-block-group alignfull ' . esc_attr( $cls ) . '"><!-- wp:group {"align":"wide","className":"bm-section-head","layout":{"type":"default"}} -->' . "\n";
	$out .= '<div class="wp-block-group alignwide bm-section-head">';
	if ( $eyebrow ) {
		$out .= '<!-- wp:paragraph {"className":"bm-eyebrow"} -->' . "\n" . '<p class="bm-eyebrow">' . esc_html( $eyebrow ) . '</p>' . "\n" . '<!-- /wp:paragraph -->' . "\n\n";
	}
	$out .= '<!-- wp:heading -->' . "\n" . '<h2 class="wp-block-heading">' . esc_html( $title ) . '</h2>' . "\n" . '<!-- /wp:heading -->';
	if ( $lead ) {
		$out .= "\n\n" . '<!-- wp:paragraph {"className":"bm-lead"} -->' . "\n" . '<p class="bm-lead">' . esc_html( $lead ) . '</p>' . "\n" . '<!-- /wp:paragraph -->';
	}
	$out .= '</div>' . "\n" . '<!-- /wp:group -->' . "\n\n";
	$out .= '<!-- wp:group {"align":"wide","className":"bm-points__grid","layout":{"type":"default"}} -->' . "\n" . '<div class="wp-block-group alignwide bm-points__grid">';
	$blocks = array();
	foreach ( $items as $item ) {
		list( $icon, $tone, $t, $text ) = $item;
		$blocks[] = '<!-- wp:group {"className":"bm-point","layout":{"type":"default"}} -->' . "\n" . '<div class="wp-block-group bm-point">'
			. '<!-- wp:babymoura/icon {"name":"' . esc_attr( $icon ) . '","tone":"' . esc_attr( $tone ) . '"} /-->' . "\n\n"
			. '<!-- wp:heading {"level":3} -->' . "\n" . '<h3 class="wp-block-heading">' . esc_html( $t ) . '</h3>' . "\n" . '<!-- /wp:heading -->' . "\n\n"
			. '<!-- wp:paragraph -->' . "\n" . '<p>' . esc_html( $text ) . '</p>' . "\n" . '<!-- /wp:paragraph --></div>' . "\n" . '<!-- /wp:group -->';
	}
	$out .= implode( "\n\n", $blocks ) . '</div>' . "\n" . '<!-- /wp:group --></section>' . "\n" . '<!-- /wp:group -->' . "\n\n";
	return $out;
}

/**
 * Parágrafo simples serializado.
 *
 * @param string $html  Conteúdo (HTML inline já escapado).
 * @param string $class Classe opcional.
 */
function babymoura_p( string $html, string $class = '' ): string {
	if ( $class ) {
		return '<!-- wp:paragraph {"className":"' . esc_attr( $class ) . '"} -->' . "\n" . '<p class="' . esc_attr( $class ) . '">' . $html . '</p>' . "\n" . '<!-- /wp:paragraph -->';
	}
	return '<!-- wp:paragraph -->' . "\n" . '<p>' . $html . '</p>' . "\n" . '<!-- /wp:paragraph -->';
}

/**
 * Título serializado.
 *
 * @param string $text  Texto.
 * @param int    $level Nível.
 * @param string $class Classe opcional.
 */
function babymoura_h( string $text, int $level = 2, string $class = '' ): string {
	$attrs = array();
	if ( 2 !== $level ) {
		$attrs['level'] = $level;
	}
	if ( $class ) {
		$attrs['className'] = $class;
	}
	$json = $attrs ? ' ' . wp_json_encode( $attrs ) : '';
	return '<!-- wp:heading' . $json . ' -->' . "\n" . '<h' . $level . ' class="wp-block-heading' . ( $class ? ' ' . esc_attr( $class ) : '' ) . '">' . esc_html( $text ) . '</h' . $level . '>' . "\n" . '<!-- /wp:heading -->';
}

/**
 * Lista serializada.
 *
 * @param array  $items   Itens (HTML inline já escapado).
 * @param string $class   Classe.
 * @param bool   $ordered Lista numerada.
 */
function babymoura_list( array $items, string $class = '', bool $ordered = false ): string {
	$attrs = array();
	if ( $ordered ) {
		$attrs['ordered'] = true;
	}
	if ( $class ) {
		$attrs['className'] = $class;
	}
	$tag  = $ordered ? 'ol' : 'ul';
	$json = $attrs ? ' ' . wp_json_encode( $attrs ) : '';
	$li   = array_map( static fn( $i ) => '<!-- wp:list-item -->' . "\n" . '<li>' . $i . '</li>' . "\n" . '<!-- /wp:list-item -->', $items );
	return '<!-- wp:list' . $json . ' -->' . "\n" . '<' . $tag . ' class="wp-block-list' . ( $class ? ' ' . esc_attr( $class ) : '' ) . '">' . implode( "\n\n", $li ) . '</' . $tag . '>' . "\n" . '<!-- /wp:list -->';
}

/**
 * Abre/fecha uma secção (grupo) serializada.
 *
 * @param string $class  Classes.
 * @param string $inner  Conteúdo interior (blocos serializados).
 * @param string $anchor Âncora opcional.
 * @param string $tag    Elemento.
 * @param bool   $full   Largura total.
 */
function babymoura_group( string $class, string $inner, string $anchor = '', string $tag = 'div', bool $full = false ): string {
	$attrs = array();
	if ( 'div' !== $tag ) {
		$attrs['tagName'] = $tag;
	}
	if ( $full ) {
		$attrs['align'] = 'full';
	}
	if ( $anchor ) {
		$attrs['anchor'] = $anchor;
	}
	$attrs['className'] = $class;
	$attrs['layout']    = array( 'type' => $full ? 'constrained' : 'default' );
	$cls                = 'wp-block-group' . ( $full ? ' alignfull' : '' ) . ' ' . $class;
	return '<!-- wp:group ' . wp_json_encode( $attrs ) . ' -->' . "\n"
		. '<' . $tag . ( $anchor ? ' id="' . esc_attr( $anchor ) . '"' : '' ) . ' class="' . esc_attr( $cls ) . '">' . $inner . '</' . $tag . '>' . "\n"
		. '<!-- /wp:group -->';
}

/**
 * Grupo com largura "wide" (dentro de secções).
 *
 * @param string $class Classes.
 * @param string $inner Conteúdo.
 */
function babymoura_wide( string $class, string $inner ): string {
	$attrs = array(
		'align'     => 'wide',
		'className' => $class,
		'layout'    => array( 'type' => 'default' ),
	);
	return '<!-- wp:group ' . wp_json_encode( $attrs ) . ' -->' . "\n"
		. '<div class="wp-block-group alignwide ' . esc_attr( $class ) . '">' . $inner . '</div>' . "\n"
		. '<!-- /wp:group -->';
}

/**
 * Ligação com seta.
 *
 * @param string $url  URL.
 * @param string $text Texto.
 */
function babymoura_link( string $url, string $text ): string {
	return babymoura_p( '<a href="' . esc_url( $url ) . '">' . esc_html( $text ) . '</a>', 'bm-link-arrow' );
}

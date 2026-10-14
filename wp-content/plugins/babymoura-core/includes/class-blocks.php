<?php
/**
 * Blocos dinâmicos do projeto. Lêem sempre a fonte central de dados (definições e conteúdos estruturados).
 *
 * @package BabyMoura
 */

defined( 'ABSPATH' ) || exit;

final class BM_Blocks {

	const BLOCKS = array( 'contact-details', 'hours', 'activities', 'services', 'faq', 'gallery', 'form', 'icon' );

	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_filter( 'block_categories_all', array( __CLASS__, 'category' ) );
		add_action( 'wp_body_open', array( __CLASS__, 'demo_ribbon' ), 5 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'front_assets' ) );
	}

	public static function register(): void {
		wp_register_script(
			'bm-editor',
			BM_CORE_URL . 'assets/editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n', 'wp-plugins', 'wp-editor', 'wp-data', 'wp-core-data' ),
			BM_CORE_VERSION,
			true
		);
		wp_localize_script(
			'bm-editor',
			'bmEditor',
			array(
				'icons'  => bm_icon_names(),
				'spaces' => self::space_options(),
			)
		);
		wp_register_script( 'bm-front', BM_CORE_URL . 'assets/front.js', array(), BM_CORE_VERSION, array( 'strategy' => 'defer' ) );
		wp_register_script( 'bm-forms', BM_CORE_URL . 'assets/forms.js', array( 'bm-front' ), BM_CORE_VERSION, array( 'strategy' => 'defer' ) );
		wp_localize_script( 'bm-forms', 'bmForms', array( 'endpoint' => esc_url_raw( rest_url( 'babymoura/v1/forms/' ) ) ) );

		foreach ( self::BLOCKS as $name ) {
			register_block_type( BM_CORE_DIR . 'blocks/' . $name );
		}

		// Metadados de SEO editáveis na barra lateral do editor de páginas.
		foreach ( array( 'bm_seo_title', 'bm_seo_description' ) as $key ) {
			register_post_meta(
				'page',
				$key,
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_text_field',
					'auth_callback'     => static fn() => current_user_can( 'edit_pages' ),
				)
			);
		}
	}

	public static function front_assets(): void {
		wp_enqueue_script( 'bm-front' );
		wp_localize_script(
			'bm-front',
			'bmSite',
			array(
				'analytics' => (bool) bm_setting( 'analytics_on' ),
				'i18n'      => array(
					'close'    => __( 'Fechar', 'babymoura-core' ),
					'previous' => __( 'Imagem anterior', 'babymoura-core' ),
					'next'     => __( 'Imagem seguinte', 'babymoura-core' ),
					/* translators: 1: posição, 2: total */
					'position' => __( 'Imagem %1$s de %2$s', 'babymoura-core' ),
				),
			)
		);
	}

	public static function category( array $categories ): array {
		array_unshift(
			$categories,
			array(
				'slug'  => 'babymoura',
				'title' => 'BabyMoura',
			)
		);
		return $categories;
	}

	public static function space_options(): array {
		$terms = get_terms(
			array(
				'taxonomy'   => BM_Media_Rights::TAXONOMY,
				'hide_empty' => false,
			)
		);
		$out = array();
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$out[] = array(
					'value' => $term->slug,
					'label' => $term->name,
				);
			}
		}
		return $out;
	}

	/**
	 * Faixa discreta que identifica a proposta privada em modo demonstração.
	 */
	public static function demo_ribbon(): void {
		if ( ! bm_is_demo() ) {
			return;
		}
		printf(
			'<div class="bm-demo-ribbon" role="note"><strong>%1$s</strong> <span>%2$s</span> <span class="bm-demo-ribbon__more">%3$s</span></div>',
			esc_html__( 'Proposta privada', 'babymoura-core' ),
			esc_html__( 'Modo demonstração: os formulários não enviam dados.', 'babymoura-core' ),
			esc_html__( 'Conteúdos marcados "A confirmar" aguardam validação da instituição.', 'babymoura-core' )
		);
	}
}

<?php
/**
 * Redirecionamentos permanentes dos URLs antigos (content/redirects.json).
 * Mapeamento explícito página a página: nunca redireciona tudo para a página inicial.
 *
 * @package BabyMoura
 */

defined( 'ABSPATH' ) || exit;

final class BM_Redirects {

	public static function init(): void {
		add_action( 'template_redirect', array( __CLASS__, 'maybe_redirect' ), 1 );
	}

	public static function map(): array {
		static $map = null;
		if ( null === $map ) {
			$json = json_decode( (string) file_get_contents( BM_CORE_DIR . 'content/redirects.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			$map  = is_array( $json['map'] ?? null ) ? $json['map'] : array();
		}
		return $map;
	}

	public static function maybe_redirect(): void {
		if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}
		$path = (string) wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH );
		$path = '/' . trim( $path, '/' ) . '/';
		$map  = self::map();
		if ( isset( $map[ $path ] ) && ! get_page_by_path( trim( $path, '/' ) ) ) {
			$query = isset( $_SERVER['QUERY_STRING'] ) && '' !== $_SERVER['QUERY_STRING'] ? '?' . sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ) : '';
			wp_safe_redirect( home_url( $map[ $path ] ) . $query, 301, 'BabyMoura' );
			exit;
		}
	}
}

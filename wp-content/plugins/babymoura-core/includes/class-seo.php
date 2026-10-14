<?php
/**
 * SEO local, metadados sociais, dados estruturados verificáveis, indexação do preview e proteção de acesso.
 * Nunca publica avaliações, preços ou horários não confirmados.
 *
 * @package BabyMoura
 */

defined( 'ABSPATH' ) || exit;

final class BM_SEO {

	/** @var array */
	private static $schema = array();

	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'preview_auth' ), 0 );
		add_filter( 'pre_get_document_title', array( __CLASS__, 'title' ) );
		add_filter( 'document_title_separator', static fn() => '·' );
		add_action( 'wp_head', array( __CLASS__, 'head' ), 5 );
		add_action( 'wp_head', array( __CLASS__, 'print_schema' ), 99 );
		add_filter( 'wp_robots', array( __CLASS__, 'robots' ) );
		add_action( 'send_headers', array( __CLASS__, 'headers' ) );
		add_filter( 'wp_sitemaps_add_provider', array( __CLASS__, 'sitemap_providers' ), 10, 2 );
		add_filter( 'wp_sitemaps_post_types', static fn( $types ) => array_intersect_key( $types, array( 'page' => true ) ) );
		add_filter( 'robots_txt', array( __CLASS__, 'robots_txt' ), 10, 2 );

		// Site institucional sem blogue nem comentários.
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'feed_links', 2 );
		remove_action( 'wp_head', 'feed_links_extra', 3 );
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		add_filter( 'comments_open', '__return_false', 20 );
		add_filter( 'pings_open', '__return_false', 20 );
		add_filter( 'xmlrpc_enabled', '__return_false' );
	}

	public static function add_schema( array $data ): void {
		self::$schema[] = $data;
	}

	/**
	 * Proteção opcional do preview privado: BABYMOURA_PREVIEW_AUTH = "utilizador:palavra-passe".
	 */
	public static function preview_auth(): void {
		if ( ! defined( 'BABYMOURA_PREVIEW_AUTH' ) || ! BABYMOURA_PREVIEW_AUTH || ( defined( 'WP_CLI' ) && WP_CLI ) || wp_doing_cron() ) {
			return;
		}
		$user = isset( $_SERVER['PHP_AUTH_USER'] ) ? (string) wp_unslash( $_SERVER['PHP_AUTH_USER'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$pass = isset( $_SERVER['PHP_AUTH_PW'] ) ? (string) wp_unslash( $_SERVER['PHP_AUTH_PW'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		// PHP em CGI/FPM: o cabeçalho chega via .htaccess (HTTP_AUTHORIZATION).
		if ( '' === $user ) {
			foreach ( array( 'HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION' ) as $key ) {
				$header = isset( $_SERVER[ $key ] ) ? (string) wp_unslash( $_SERVER[ $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				if ( 0 === stripos( $header, 'basic ' ) ) {
					$decoded = (string) base64_decode( substr( $header, 6 ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
					if ( false !== strpos( $decoded, ':' ) ) {
						list( $user, $pass ) = explode( ':', $decoded, 2 );
					}
					break;
				}
			}
		}
		if ( hash_equals( (string) BABYMOURA_PREVIEW_AUTH, $user . ':' . $pass ) ) {
			return;
		}
		header( 'WWW-Authenticate: Basic realm="BabyMoura preview", charset="UTF-8"' );
		header( 'X-Robots-Tag: noindex, nofollow', true );
		status_header( 401 );
		echo esc_html__( 'Preview privado. Acesso reservado.', 'babymoura-core' );
		exit;
	}

	private static function current_page(): ?WP_Post {
		if ( is_front_page() && 'page' === get_option( 'show_on_front' ) ) {
			return get_post( (int) get_option( 'page_on_front' ) );
		}
		return is_singular( 'page' ) ? get_queried_object() : null;
	}

	/**
	 * @param string $title Título atual.
	 */
	public static function title( $title ) {
		$page = self::current_page();
		if ( $page ) {
			$custom = (string) get_post_meta( $page->ID, 'bm_seo_title', true );
			if ( $custom ) {
				return $custom;
			}
		}
		return $title;
	}

	public static function description(): string {
		$page = self::current_page();
		if ( $page ) {
			$desc = (string) get_post_meta( $page->ID, 'bm_seo_description', true );
			if ( ! $desc && has_excerpt( $page ) ) {
				$desc = get_the_excerpt( $page );
			}
			if ( $desc ) {
				return $desc;
			}
		}
		return (string) get_bloginfo( 'description' );
	}

	public static function head(): void {
		$page  = self::current_page();
		$title = wp_get_document_title();
		$desc  = self::description();
		$url   = $page ? get_permalink( $page ) : home_url( add_query_arg( array() ) );
		$image = self::share_image( $page );

		printf( "<meta name=\"description\" content=\"%s\">\n", esc_attr( $desc ) );
		printf( "<meta property=\"og:locale\" content=\"pt_PT\">\n<meta property=\"og:type\" content=\"website\">\n<meta property=\"og:site_name\" content=\"%s\">\n", esc_attr( bm_setting( 'brand_name', 'BabyMoura' ) ) );
		printf( "<meta property=\"og:title\" content=\"%s\">\n<meta property=\"og:description\" content=\"%s\">\n<meta property=\"og:url\" content=\"%s\">\n", esc_attr( $title ), esc_attr( $desc ), esc_url( $url ) );
		if ( $image ) {
			printf( "<meta property=\"og:image\" content=\"%s\">\n<meta property=\"og:image:width\" content=\"%d\">\n<meta property=\"og:image:height\" content=\"%d\">\n", esc_url( $image[0] ), (int) $image[1], (int) $image[2] );
		}
		echo "<meta name=\"twitter:card\" content=\"summary_large_image\">\n";

		if ( is_front_page() ) {
			self::add_schema( self::organization() );
		} elseif ( $page ) {
			$items = array(
				array(
					'@type'    => 'ListItem',
					'position' => 1,
					'name'     => __( 'Início', 'babymoura-core' ),
					'item'     => home_url( '/' ),
				),
			);
			$ancestors = array_reverse( get_post_ancestors( $page ) );
			foreach ( $ancestors as $ancestor ) {
				$items[] = array(
					'@type'    => 'ListItem',
					'position' => count( $items ) + 1,
					'name'     => get_the_title( $ancestor ),
					'item'     => get_permalink( $ancestor ),
				);
			}
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => count( $items ) + 1,
				'name'     => get_the_title( $page ),
				'item'     => get_permalink( $page ),
			);
			self::add_schema(
				array(
					'@context'        => 'https://schema.org',
					'@type'           => 'BreadcrumbList',
					'itemListElement' => $items,
				)
			);
		}
	}

	private static function share_image( ?WP_Post $page ) {
		if ( $page && has_post_thumbnail( $page ) ) {
			return wp_get_attachment_image_src( get_post_thumbnail_id( $page ), 'large' );
		}
		$default = (int) get_option( 'bm_share_image' );
		return $default ? wp_get_attachment_image_src( $default, 'large' ) : false;
	}

	/**
	 * Entidade com atributos verificáveis apenas (sem horário até confirmação, sem avaliações, sem preços).
	 */
	public static function organization(): array {
		$s    = BM_Settings::all();
		$data = array(
			'@context'    => 'https://schema.org',
			'@type'       => array( 'ChildCare', 'Preschool' ),
			'@id'         => home_url( '/#organizacao' ),
			'name'        => $s['brand_name'],
			'description' => __( 'Creche e jardim de infância em Vilamoura, Algarve.', 'babymoura-core' ),
			'url'         => home_url( '/' ),
			'telephone'   => $s['phone_e164'],
			'email'       => $s['email'],
			'address'     => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => $s['street'],
				'addressLocality' => $s['locality'],
				'postalCode'      => $s['postal_code'],
				'addressRegion'   => $s['region'],
				'addressCountry'  => 'PT',
			),
			'sameAs'      => array_values( array_filter( array( $s['facebook_url'], $s['instagram_url'], $s['youtube_url'] ) ) ),
		);
		$logo = get_theme_file_uri( 'assets/images/brand/babymoura-logo.png' );
		if ( $logo ) {
			$data['logo'] = $logo;
		}
		if ( ! empty( $s['legal_name'] ) ) {
			$data['legalName'] = $s['legal_name'];
		}
		return $data;
	}

	public static function print_schema(): void {
		foreach ( self::$schema as $data ) {
			echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "</script>\n";
		}
		self::$schema = array();
	}

	/**
	 * O preview (modo demonstração ou ambiente não produtivo) nunca é indexado.
	 *
	 * @param array $robots Diretivas.
	 */
	public static function robots( array $robots ): array {
		if ( self::noindex() ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
			unset( $robots['max-image-preview'] );
		}
		return $robots;
	}

	public static function noindex(): bool {
		return bm_is_demo() || 'production' !== wp_get_environment_type() || ! get_option( 'blog_public' );
	}

	public static function headers(): void {
		if ( self::noindex() ) {
			header( 'X-Robots-Tag: noindex, nofollow', true );
		}
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header( 'X-Content-Type-Options: nosniff' );
		header( 'Permissions-Policy: camera=(), microphone=(), geolocation=()' );
	}

	public static function sitemap_providers( $provider, $name ) {
		return in_array( $name, array( 'users', 'taxonomies' ), true ) ? false : $provider;
	}

	public static function robots_txt( $output, $public ) {
		if ( self::noindex() ) {
			return "User-agent: *\nDisallow: /\n";
		}
		return $output;
	}
}

<?php
/**
 * Comandos WP-CLI: importação e exportação dos conteúdos da proposta e estado de lançamento.
 *
 *   wp babymoura seed [--force]   Importa definições, imagens, atividades, serviços, FAQ, páginas e menu.
 *   wp babymoura export [--dir=]  Exporta os conteúdos editados para JSON/HTML (ida e volta com o seed).
 *   wp babymoura status           Mostra a checklist de lançamento.
 *
 * @package BabyMoura
 */

defined( 'ABSPATH' ) || exit;

final class BM_CLI {

	private function content_dir(): string {
		return BM_CORE_DIR . 'content/';
	}

	private function json( string $file ): array {
		$path = $this->content_dir() . $file;
		if ( ! file_exists( $path ) ) {
			WP_CLI::error( "Ficheiro em falta: {$file}" );
		}
		$data = json_decode( (string) file_get_contents( $path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( ! is_array( $data ) ) {
			WP_CLI::error( "JSON inválido: {$file}" );
		}
		return $data;
	}

	/**
	 * Importa os conteúdos da proposta.
	 *
	 * ## OPTIONS
	 *
	 * [--force]
	 * : Substitui páginas e itens existentes com o mesmo identificador.
	 *
	 * @param array $args       Argumentos.
	 * @param array $assoc_args Opções.
	 */
	public function seed( $args, $assoc_args ): void {
		$force = ! empty( $assoc_args['force'] );

		$settings = $this->json( 'settings.json' );
		if ( $force || ! get_option( BM_Settings::OPTION ) ) {
			update_option( BM_Settings::OPTION, wp_parse_args( $settings, BM_Settings::defaults() ) );
			BM_Settings::flush();
			WP_CLI::log( 'Definições institucionais importadas.' );
		}

		$this->seed_spaces();
		$media = $this->seed_media( $force );
		$this->seed_items( 'bm_atividade', $this->json( 'activities.json' ), $force );
		$this->seed_items( 'bm_servico', $this->json( 'services.json' ), $force );
		$this->seed_items( 'bm_faq', $this->json( 'faq.json' ), $force );
		$this->seed_pages( $force );

		if ( isset( $media['ilustracao-jardim-hero'] ) ) {
			update_option( 'bm_share_image', $media['ilustracao-jardim-hero'] );
		}
		// Remove os conteúdos de exemplo da instalação base do WordPress.
		foreach ( array( 'sample-page', 'pagina-de-exemplo' ) as $sample ) {
			$page = get_page_by_path( $sample );
			if ( $page ) {
				wp_delete_post( $page->ID, true );
			}
		}
		$hello = get_post( 1 );
		if ( $hello && 'post' === $hello->post_type ) {
			wp_delete_post( 1, true );
		}
		update_option( 'show_on_front', 'page' );
		$home = get_page_by_path( 'inicio' );
		if ( $home ) {
			update_option( 'page_on_front', $home->ID );
		}
		$privacy = get_page_by_path( 'privacidade' );
		if ( $privacy ) {
			update_option( 'wp_page_for_privacy_policy', $privacy->ID );
		}
		update_option( 'default_comment_status', 'closed' );
		update_option( 'default_ping_status', 'closed' );
		// Preview: nunca indexável até decisão consciente de lançamento.
		update_option( 'blog_public', 0 );

		WP_CLI::success( 'Conteúdos da proposta importados.' );
	}

	private function seed_spaces(): void {
		foreach ( $this->json( 'spaces.json' ) as $i => $space ) {
			$term = term_exists( $space['slug'], BM_Media_Rights::TAXONOMY );
			if ( ! $term ) {
				$term = wp_insert_term( $space['name'], BM_Media_Rights::TAXONOMY, array( 'slug' => $space['slug'], 'description' => $space['description'] ?? '' ) );
			}
			if ( ! is_wp_error( $term ) ) {
				update_term_meta( (int) $term['term_id'], 'bm_order', $i );
			}
		}
	}

	/**
	 * @return array<string, int> slug => ID do anexo.
	 */
	private function seed_media( bool $force ): array {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$map = (array) get_option( 'bm_media_map', array() );
		foreach ( $this->json( 'media.json' ) as $order => $item ) {
			$slug = $item['slug'];
			if ( ! empty( $map[ $slug ] ) && get_post( $map[ $slug ] ) ) {
				if ( ! $force ) {
					continue;
				}
				wp_delete_attachment( $map[ $slug ], true );
			}
			$source = $this->content_dir() . 'media/' . $item['file'];
			if ( ! file_exists( $source ) ) {
				WP_CLI::warning( "Imagem em falta: {$item['file']}" );
				continue;
			}
			$tmp = wp_tempnam( $item['file'] );
			copy( $source, $tmp );
			$id = media_handle_sideload(
				array(
					'name'     => $item['file'],
					'tmp_name' => $tmp,
				),
				0,
				$item['title'],
				array(
					'post_excerpt' => $item['caption'] ?? '',
					'post_content' => $item['description'] ?? '',
					'menu_order'   => $order,
				)
			);
			if ( is_wp_error( $id ) ) {
				WP_CLI::warning( "Falha ao importar {$item['file']}: " . $id->get_error_message() );
				continue;
			}
			update_post_meta( $id, '_wp_attachment_image_alt', $item['alt'] ?? '' );
			update_post_meta( $id, 'bm_rights', $item['rights'] ?? 'pendente' );
			update_post_meta( $id, 'bm_origin', $item['origin'] ?? '' );
			update_post_meta( $id, 'bm_slug', $slug );
			if ( ! empty( $item['spaces'] ) ) {
				wp_set_object_terms( $id, $item['spaces'], BM_Media_Rights::TAXONOMY );
			}
			$map[ $slug ] = $id;
			WP_CLI::log( "Imagem importada: {$slug} (#{$id})" );
		}
		update_option( 'bm_media_map', $map );
		return $map;
	}

	private function seed_items( string $type, array $items, bool $force ): void {
		foreach ( $items as $order => $item ) {
			$existing = get_posts(
				array(
					'post_type'      => $type,
					'name'           => $item['slug'],
					'post_status'    => 'any',
					'posts_per_page' => 1,
				)
			);
			if ( $existing && ! $force ) {
				continue;
			}
			$postarr = array(
				'ID'           => $existing ? $existing[0]->ID : 0,
				'post_type'    => $type,
				'post_name'    => $item['slug'],
				'post_title'   => $item['title'],
				'post_excerpt' => $item['excerpt'] ?? '',
				'post_content' => $item['content'] ?? '',
				'post_status'  => $item['status'] ?? 'publish',
				'menu_order'   => $order,
			);
			$id = wp_insert_post( wp_slash( $postarr ), true );
			if ( is_wp_error( $id ) ) {
				WP_CLI::warning( $id->get_error_message() );
				continue;
			}
			foreach ( $item['meta'] ?? array() as $key => $value ) {
				update_post_meta( $id, $key, $value );
			}
		}
		WP_CLI::log( sprintf( '%s: %d itens.', $type, count( $items ) ) );
	}

	private function pattern_content( string $slug ): string {
		$registry = WP_Block_Patterns_Registry::get_instance();
		$pattern  = $registry->get_registered( $slug );
		if ( $pattern && ! empty( $pattern['content'] ) ) {
			return $pattern['content'];
		}
		$file = get_theme_file_path( 'patterns/' . str_replace( 'babymoura/', '', $slug ) . '.php' );
		if ( ! file_exists( $file ) ) {
			WP_CLI::error( "Padrão não encontrado: {$slug}" );
		}
		ob_start();
		include $file;
		return (string) ob_get_clean();
	}

	private function seed_pages( bool $force ): void {
		$ids = array();
		foreach ( $this->json( 'pages.json' ) as $order => $page ) {
			$existing = get_page_by_path( $page['slug'] );
			if ( $existing && ! $force ) {
				$ids[ $page['slug'] ] = $existing->ID;
				continue;
			}
			$content = $this->pattern_content( $page['pattern'] );
			$id      = wp_insert_post(
				wp_slash(
					array(
						'ID'            => $existing ? $existing->ID : 0,
						'post_type'     => 'page',
						'post_status'   => 'publish',
						'post_title'    => $page['title'],
						'post_name'     => $page['slug'],
						'post_content'  => $content,
						'post_excerpt'  => $page['description'] ?? '',
						'menu_order'    => $order,
						'page_template' => $page['template'] ?? '',
					)
				),
				true
			);
			if ( is_wp_error( $id ) ) {
				WP_CLI::warning( $id->get_error_message() );
				continue;
			}
			update_post_meta( $id, 'bm_seo_title', $page['seo_title'] ?? '' );
			update_post_meta( $id, 'bm_seo_description', $page['description'] ?? '' );
			$ids[ $page['slug'] ] = $id;
			WP_CLI::log( "Página: /{$page['slug']}/ (#{$id})" );
		}
	}

	/**
	 * Exporta conteúdos editados no painel para os ficheiros de conteúdo.
	 *
	 * ## OPTIONS
	 *
	 * [--dir=<dir>]
	 * : Pasta de destino (por omissão /exports).
	 *
	 * @param array $args       Argumentos.
	 * @param array $assoc_args Opções.
	 */
	public function export( $args, $assoc_args ): void {
		$dir = rtrim( $assoc_args['dir'] ?? '/exports', '/' ) . '/babymoura-' . gmdate( 'Ymd-His' );
		wp_mkdir_p( $dir . '/pages' );

		$map = array(
			'activities.json' => array( 'bm_atividade', array( 'bm_creche', 'bm_jardim', 'bm_icon', 'bm_validation', 'bm_source' ) ),
			'services.json'   => array( 'bm_servico', array( 'bm_stages', 'bm_icon', 'bm_validation', 'bm_source' ) ),
			'faq.json'        => array( 'bm_faq', array( 'bm_group', 'bm_home', 'bm_validation', 'bm_source' ) ),
		);
		foreach ( $map as $file => list( $type, $keys ) ) {
			$out   = array();
			$posts = get_posts(
				array(
					'post_type'      => $type,
					'post_status'    => array( 'publish', 'draft' ),
					'posts_per_page' => -1,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				)
			);
			foreach ( $posts as $post ) {
				$meta = array();
				foreach ( $keys as $key ) {
					$meta[ $key ] = get_post_meta( $post->ID, $key, true );
				}
				$out[] = array_filter(
					array(
						'slug'    => $post->post_name,
						'title'   => $post->post_title,
						'excerpt' => $post->post_excerpt,
						'content' => $post->post_content,
						'status'  => $post->post_status,
						'meta'    => $meta,
					),
					static fn( $v ) => '' !== $v
				);
			}
			file_put_contents( "{$dir}/{$file}", wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		}
		file_put_contents( "{$dir}/settings.json", wp_json_encode( BM_Settings::all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		foreach ( get_pages( array( 'post_status' => 'publish,draft' ) ) as $page ) {
			file_put_contents( "{$dir}/pages/{$page->post_name}.html", $page->post_content ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		}
		WP_CLI::success( "Exportado para {$dir}" );
	}

	/**
	 * Mostra a checklist de lançamento.
	 */
	public function status(): void {
		$rows = array();
		foreach ( BM_Checklist::checks() as list( $ok, $title, $detail ) ) {
			$rows[] = array(
				'estado'  => $ok ? 'pronto' : 'pendente',
				'item'    => $title,
				'detalhe' => $detail,
			);
		}
		WP_CLI\Utils\format_items( 'table', $rows, array( 'estado', 'item', 'detalhe' ) );
		WP_CLI::log( 'Modo: ' . bm_mode() );
	}
}

<?php
/**
 * Conteúdos estruturados: atividades, serviços, perguntas frequentes e pedidos recebidos.
 * Cada item tem um estado de validação (pendente / confirmado) além do estado de publicação do WordPress.
 *
 * @package BabyMoura
 */

defined( 'ABSPATH' ) || exit;

final class BM_Content_Types {

	const STRUCTURED = array( 'bm_atividade', 'bm_servico', 'bm_faq' );

	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'classic_for_structured' ), 10, 2 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save' ), 10, 2 );
		foreach ( self::STRUCTURED as $type ) {
			add_filter( "manage_{$type}_posts_columns", array( __CLASS__, 'columns' ) );
			add_action( "manage_{$type}_posts_custom_column", array( __CLASS__, 'column' ), 10, 2 );
		}
		add_filter( 'enter_title_here', array( __CLASS__, 'title_placeholder' ), 10, 2 );
	}

	public static function validation_states(): array {
		return array(
			'pendente'   => __( 'Pendente — a confirmar pela instituição', 'babymoura-core' ),
			'confirmado' => __( 'Confirmado pela instituição', 'babymoura-core' ),
		);
	}

	public static function activity_types(): array {
		return array(
			''                => __( 'Não disponível nesta etapa', 'babymoura-core' ),
			'curricular'      => __( 'Atividade curricular', 'babymoura-core' ),
			'extracurricular' => __( 'Atividade extracurricular', 'babymoura-core' ),
		);
	}

	public static function faq_groups(): array {
		return array(
			'geral'     => __( 'Geral', 'babymoura-core' ),
			'visitas'   => __( 'Visitas', 'babymoura-core' ),
			'inscricao' => __( 'Pré-inscrição', 'babymoura-core' ),
			'dia-a-dia' => __( 'Dia a dia', 'babymoura-core' ),
		);
	}

	public static function register(): void {
		$common = array(
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => 'babymoura',
			'show_in_rest'        => true,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'rewrite'             => false,
			'capability_type'     => 'page',
			'map_meta_cap'        => true,
		);

		register_post_type(
			'bm_atividade',
			$common + array(
				'labels'   => self::labels( __( 'Atividades', 'babymoura-core' ), __( 'Atividade', 'babymoura-core' ) ),
				'supports' => array( 'title', 'excerpt', 'page-attributes', 'revisions' ),
			)
		);
		register_post_type(
			'bm_servico',
			$common + array(
				'labels'   => self::labels( __( 'Serviços', 'babymoura-core' ), __( 'Serviço', 'babymoura-core' ) ),
				'supports' => array( 'title', 'excerpt', 'page-attributes', 'revisions' ),
			)
		);
		register_post_type(
			'bm_faq',
			$common + array(
				'labels'   => self::labels( __( 'Perguntas frequentes', 'babymoura-core' ), __( 'Pergunta', 'babymoura-core' ) ),
				'supports' => array( 'title', 'editor', 'page-attributes', 'revisions' ),
			)
		);
		register_post_type(
			'bm_pedido',
			array(
				'labels'          => self::labels( __( 'Pedidos recebidos', 'babymoura-core' ), __( 'Pedido', 'babymoura-core' ) ),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => 'babymoura',
				'show_in_rest'    => false,
				'supports'        => array( 'title' ),
				'capability_type' => 'page',
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
				'rewrite'         => false,
			)
		);

		$meta = array(
			'bm_validation' => 'string',
			'bm_source'     => 'string',
			'bm_icon'       => 'string',
			'bm_creche'     => 'string',
			'bm_jardim'     => 'string',
			'bm_stages'     => 'string',
			'bm_group'      => 'string',
			'bm_home'       => 'boolean',
		);
		foreach ( self::STRUCTURED as $type ) {
			foreach ( $meta as $key => $kind ) {
				register_post_meta(
					$type,
					$key,
					array(
						'type'          => $kind,
						'single'        => true,
						'show_in_rest'  => true,
						'auth_callback' => static fn() => current_user_can( 'edit_pages' ),
					)
				);
			}
		}
	}

	private static function labels( string $plural, string $singular ): array {
		return array(
			'name'               => $plural,
			'singular_name'      => $singular,
			'menu_name'          => $plural,
			'all_items'          => $plural,
			/* translators: %s: nome do tipo de conteúdo */
			'add_new_item'       => sprintf( __( 'Adicionar %s', 'babymoura-core' ), mb_strtolower( $singular ) ),
			'add_new'            => __( 'Adicionar', 'babymoura-core' ),
			/* translators: %s: nome do tipo de conteúdo */
			'edit_item'          => sprintf( __( 'Editar %s', 'babymoura-core' ), mb_strtolower( $singular ) ),
			'search_items'       => __( 'Pesquisar', 'babymoura-core' ),
			'not_found'          => __( 'Sem resultados.', 'babymoura-core' ),
			'not_found_in_trash' => __( 'Sem resultados no lixo.', 'babymoura-core' ),
		);
	}

	/**
	 * @param bool   $use  Usar editor de blocos.
	 * @param string $type Tipo de conteúdo.
	 */
	public static function classic_for_structured( $use, $type ) {
		return in_array( $type, array_merge( self::STRUCTURED, array( 'bm_pedido' ) ), true ) ? false : $use;
	}

	public static function title_placeholder( $text, $post ) {
		if ( 'bm_faq' === $post->post_type ) {
			return __( 'Pergunta, como as famílias a colocariam', 'babymoura-core' );
		}
		return $text;
	}

	public static function meta_boxes(): void {
		foreach ( self::STRUCTURED as $type ) {
			add_meta_box( 'bm_details', __( 'Detalhes e validação', 'babymoura-core' ), array( __CLASS__, 'render_box' ), $type, 'normal', 'high' );
		}
		add_meta_box( 'bm_request', __( 'Dados do pedido', 'babymoura-core' ), array( BM_Forms::class, 'render_request_box' ), 'bm_pedido', 'normal', 'high' );
		remove_meta_box( 'submitdiv', 'bm_pedido', 'side' );
	}

	public static function render_box( WP_Post $post ): void {
		wp_nonce_field( 'bm_save_details', 'bm_details_nonce' );
		$v = static fn( $k ) => (string) get_post_meta( $post->ID, $k, true );
		echo '<table class="form-table" role="presentation">';

		if ( 'bm_atividade' === $post->post_type ) {
			foreach ( bm_stages() as $key => $label ) {
				self::select_row( 'bm_' . $key, $label, self::activity_types(), $v( 'bm_' . $key ) );
			}
		}
		if ( 'bm_servico' === $post->post_type ) {
			$stages = array_filter( explode( ',', $v( 'bm_stages' ) ) );
			echo '<tr><th scope="row">' . esc_html__( 'Disponível para', 'babymoura-core' ) . '</th><td>';
			foreach ( bm_stages() as $key => $label ) {
				printf(
					'<label style="margin-right:1.2em"><input type="checkbox" name="bm_stages[]" value="%1$s" %2$s> %3$s</label>',
					esc_attr( $key ),
					checked( in_array( $key, $stages, true ), true, false ),
					esc_html( $label )
				);
			}
			echo '<p class="description">' . esc_html__( 'Sem seleção = serviço geral da instituição.', 'babymoura-core' ) . '</p></td></tr>';
		}
		if ( 'bm_faq' === $post->post_type ) {
			self::select_row( 'bm_group', __( 'Grupo', 'babymoura-core' ), self::faq_groups(), $v( 'bm_group' ) ?: 'geral' );
			printf(
				'<tr><th scope="row">%1$s</th><td><label><input type="checkbox" name="bm_home" value="1" %2$s> %3$s</label></td></tr>',
				esc_html__( 'Página inicial', 'babymoura-core' ),
				checked( (bool) $v( 'bm_home' ), true, false ),
				esc_html__( 'Mostrar na secção "Perguntas das famílias" da página inicial', 'babymoura-core' )
			);
		}
		if ( 'bm_faq' !== $post->post_type ) {
			$icons = array_combine( bm_icon_names(), bm_icon_names() );
			self::select_row( 'bm_icon', __( 'Ícone', 'babymoura-core' ), $icons, $v( 'bm_icon' ) ?: 'leaf' );
		}
		self::select_row( 'bm_validation', __( 'Estado de validação', 'babymoura-core' ), self::validation_states(), $v( 'bm_validation' ) ?: 'pendente' );
		printf(
			'<tr><th scope="row"><label for="bm_source">%1$s</label></th><td><input class="large-text" id="bm_source" name="bm_source" value="%2$s"><p class="description">%3$s</p></td></tr>',
			esc_html__( 'Origem da informação', 'babymoura-core' ),
			esc_attr( $v( 'bm_source' ) ),
			esc_html__( 'Ex.: página do site atual, documento interno ou confirmação da direção (com data).', 'babymoura-core' )
		);
		echo '</table>';
		echo '<p class="description">' . esc_html__( 'Itens "pendentes" aparecem apenas na proposta (modo demonstração), marcados como "A confirmar". Em produção só são mostrados itens confirmados e publicados.', 'babymoura-core' ) . '</p>';
	}

	private static function select_row( string $name, string $label, array $options, string $current ): void {
		printf( '<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><select id="%1$s" name="%1$s">', esc_attr( $name ), esc_html( $label ) );
		foreach ( $options as $value => $text ) {
			printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $value ), selected( $current, (string) $value, false ), esc_html( $text ) );
		}
		echo '</select></td></tr>';
	}

	public static function save( int $post_id, WP_Post $post ): void {
		if ( ! in_array( $post->post_type, self::STRUCTURED, true ) ) {
			return;
		}
		if ( ! isset( $_POST['bm_details_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['bm_details_nonce'] ) ), 'bm_save_details' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$types = array_keys( self::activity_types() );
		$get   = static fn( $k ) => isset( $_POST[ $k ] ) ? sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) : '';

		$validation = $get( 'bm_validation' );
		update_post_meta( $post_id, 'bm_validation', array_key_exists( $validation, self::validation_states() ) ? $validation : 'pendente' );
		update_post_meta( $post_id, 'bm_source', $get( 'bm_source' ) );

		if ( 'bm_faq' !== $post->post_type ) {
			$icon = $get( 'bm_icon' );
			update_post_meta( $post_id, 'bm_icon', in_array( $icon, bm_icon_names(), true ) ? $icon : 'leaf' );
		}
		if ( 'bm_atividade' === $post->post_type ) {
			foreach ( array_keys( bm_stages() ) as $stage ) {
				$val = $get( 'bm_' . $stage );
				update_post_meta( $post_id, 'bm_' . $stage, in_array( $val, $types, true ) ? $val : '' );
			}
		}
		if ( 'bm_servico' === $post->post_type ) {
			$raw    = isset( $_POST['bm_stages'] ) ? (array) wp_unslash( $_POST['bm_stages'] ) : array();
			$stages = array_intersect( array_map( 'sanitize_key', $raw ), array_keys( bm_stages() ) );
			update_post_meta( $post_id, 'bm_stages', implode( ',', $stages ) );
		}
		if ( 'bm_faq' === $post->post_type ) {
			$group = $get( 'bm_group' );
			update_post_meta( $post_id, 'bm_group', array_key_exists( $group, self::faq_groups() ) ? $group : 'geral' );
			update_post_meta( $post_id, 'bm_home', empty( $_POST['bm_home'] ) ? 0 : 1 );
		}
	}

	public static function columns( array $cols ): array {
		$date = $cols['date'] ?? null;
		unset( $cols['date'] );
		$cols['bm_validation'] = __( 'Validação', 'babymoura-core' );
		$cols['bm_source']     = __( 'Origem', 'babymoura-core' );
		if ( $date ) {
			$cols['date'] = $date;
		}
		return $cols;
	}

	public static function column( string $col, int $post_id ): void {
		if ( 'bm_validation' === $col ) {
			$state = (string) get_post_meta( $post_id, 'bm_validation', true );
			echo 'confirmado' === $state
				? '<span style="color:#1e6b3a;font-weight:600">' . esc_html__( 'Confirmado', 'babymoura-core' ) . '</span>'
				: '<span style="color:#8a4b00;font-weight:600">' . esc_html__( 'Pendente', 'babymoura-core' ) . '</span>';
		}
		if ( 'bm_source' === $col ) {
			echo esc_html( (string) get_post_meta( $post_id, 'bm_source', true ) );
		}
	}

	/**
	 * Consulta única usada por todas as páginas (fonte única de dados).
	 *
	 * @param string $type Tipo de conteúdo.
	 * @param array  $args Argumentos adicionais de WP_Query.
	 * @return WP_Post[]
	 */
	public static function visible( string $type, array $args = array() ): array {
		$states = bm_is_demo() ? array( 'confirmado', 'pendente' ) : array( 'confirmado' );
		$query  = new WP_Query(
			array_merge(
				array(
					'post_type'      => $type,
					'post_status'    => 'publish',
					'posts_per_page' => 100,
					'orderby'        => array(
						'menu_order' => 'ASC',
						'title'      => 'ASC',
					),
					'no_found_rows'  => true,
					'meta_query'     => array(
						array(
							'key'     => 'bm_validation',
							'value'   => $states,
							'compare' => 'IN',
						),
					),
				),
				$args
			)
		);
		return $query->posts;
	}
}

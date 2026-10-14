<?php
/**
 * Estado de autorização das imagens e categorias de espaços da galeria.
 * Nenhuma imagem provisória passa para produção silenciosamente: é identificada no site e na checklist.
 *
 * @package BabyMoura
 */

defined( 'ABSPATH' ) || exit;

final class BM_Media_Rights {

	const TAXONOMY = 'bm_espaco';

	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_filter( 'attachment_fields_to_edit', array( __CLASS__, 'fields' ), 10, 2 );
		add_filter( 'attachment_fields_to_save', array( __CLASS__, 'save' ), 10, 2 );
		add_filter( 'render_block_core/image', array( __CLASS__, 'mark_block' ), 10, 2 );
		add_filter( 'render_block_core/cover', array( __CLASS__, 'mark_block' ), 10, 2 );
		add_filter( 'render_block_core/media-text', array( __CLASS__, 'mark_block' ), 10, 2 );
		add_filter( 'image_editor_output_format', array( __CLASS__, 'webp_output' ) );
	}

	public static function states(): array {
		return array(
			'pendente'    => __( 'Autorização pendente', 'babymoura-core' ),
			'autorizada'  => __( 'Autorizada pela instituição', 'babymoura-core' ),
			'ilustrativa' => __( 'Ilustração provisória (substituir antes do lançamento)', 'babymoura-core' ),
		);
	}

	public static function register(): void {
		register_taxonomy(
			self::TAXONOMY,
			'attachment',
			array(
				'labels'            => array(
					'name'          => __( 'Espaços (galeria)', 'babymoura-core' ),
					'singular_name' => __( 'Espaço', 'babymoura-core' ),
				),
				'public'            => false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'hierarchical'      => true,
				'rewrite'           => false,
				'capabilities'      => array(
					'manage_terms' => 'edit_pages',
					'edit_terms'   => 'edit_pages',
					'delete_terms' => 'manage_options',
					'assign_terms' => 'edit_pages',
				),
			)
		);
		foreach ( array( 'bm_rights', 'bm_origin' ) as $key ) {
			register_post_meta(
				'attachment',
				$key,
				array(
					'type'          => 'string',
					'single'        => true,
					'show_in_rest'  => true,
					'auth_callback' => static fn() => current_user_can( 'upload_files' ),
				)
			);
		}
	}

	public static function state( int $attachment_id ): string {
		$state = (string) get_post_meta( $attachment_id, 'bm_rights', true );
		return array_key_exists( $state, self::states() ) ? $state : 'pendente';
	}

	public static function fields( array $fields, WP_Post $post ): array {
		$current = self::state( $post->ID );
		$html    = '<select name="attachments[' . (int) $post->ID . '][bm_rights]">';
		foreach ( self::states() as $value => $label ) {
			$html .= '<option value="' . esc_attr( $value ) . '" ' . selected( $current, $value, false ) . '>' . esc_html( $label ) . '</option>';
		}
		$html .= '</select>';

		$fields['bm_rights'] = array(
			'label' => __( 'Autorização de uso', 'babymoura-core' ),
			'input' => 'html',
			'html'  => $html,
			'helps' => __( 'Fotografias com crianças exigem consentimento das famílias para esta utilização.', 'babymoura-core' ),
		);
		$fields['bm_origin'] = array(
			'label' => __( 'Origem da imagem', 'babymoura-core' ),
			'input' => 'text',
			'value' => (string) get_post_meta( $post->ID, 'bm_origin', true ),
		);
		return $fields;
	}

	public static function save( array $post, array $attachment ): array {
		if ( ! current_user_can( 'edit_post', $post['ID'] ) ) {
			return $post;
		}
		if ( isset( $attachment['bm_rights'] ) && array_key_exists( $attachment['bm_rights'], self::states() ) ) {
			update_post_meta( $post['ID'], 'bm_rights', $attachment['bm_rights'] );
		}
		if ( isset( $attachment['bm_origin'] ) ) {
			update_post_meta( $post['ID'], 'bm_origin', sanitize_text_field( $attachment['bm_origin'] ) );
		}
		return $post;
	}

	/**
	 * Acrescenta a marca "Ilustração provisória" às imagens que ainda não são fotografias reais autorizadas.
	 *
	 * @param string $html  HTML do bloco.
	 * @param array  $block Bloco.
	 */
	public static function mark_block( string $html, array $block ): string {
		$id = (int) ( $block['attrs']['id'] ?? $block['attrs']['mediaId'] ?? 0 );
		if ( ! $id || 'ilustrativa' !== self::state( $id ) ) {
			return $html;
		}
		$label = '<span class="bm-provisional" aria-hidden="true">' . esc_html__( 'Ilustração provisória', 'babymoura-core' ) . '</span>';
		// Insere a marca antes do fecho do primeiro contentor (figure/div) e acrescenta uma classe identificadora.
		$html = preg_replace( '/class="/', 'class="has-bm-provisional ', $html, 1 );
		$pos  = strrpos( $html, '</figure>' );
		if ( false === $pos ) {
			$pos = strrpos( $html, '</div>' );
		}
		return false === $pos ? $html : substr_replace( $html, $label, $pos, 0 );
	}

	/**
	 * Gera tamanhos intermédios em WebP.
	 *
	 * @param array $formats Mapeamento de formatos.
	 */
	public static function webp_output( array $formats ): array {
		$formats['image/jpeg'] = 'image/webp';
		$formats['image/png']  = 'image/webp';
		return $formats;
	}
}

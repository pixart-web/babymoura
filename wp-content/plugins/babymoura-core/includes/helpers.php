<?php
/**
 * Funções utilitárias partilhadas pelo plugin e pelo tema.
 *
 * @package BabyMoura
 */

defined( 'ABSPATH' ) || exit;

/**
 * Modo de funcionamento: "demo" (proposta, nada é enviado) ou "production".
 * A constante BABYMOURA_MODE (wp-config/ambiente) prevalece sobre a opção no painel.
 */
function bm_mode(): string {
	if ( defined( 'BABYMOURA_MODE' ) && in_array( BABYMOURA_MODE, array( 'demo', 'production' ), true ) ) {
		return BABYMOURA_MODE;
	}
	$mode = get_option( 'babymoura_mode', 'demo' );
	return 'production' === $mode ? 'production' : 'demo';
}

function bm_is_demo(): bool {
	return 'demo' === bm_mode();
}

/**
 * Lê um valor da fonte central de dados institucionais.
 *
 * @param string $key     Chave.
 * @param mixed  $default Valor por omissão.
 */
function bm_setting( string $key, $default = '' ) {
	$settings = BM_Settings::all();
	return array_key_exists( $key, $settings ) ? $settings[ $key ] : $default;
}

/**
 * Regra única de publicação de conteúdo com estado editorial.
 * - confirmado: visível sempre.
 * - pendente: visível apenas na proposta (modo demo), identificado como "a confirmar".
 *
 * @param string $state Estado de validação.
 */
function bm_is_visible( string $state ): bool {
	if ( 'confirmado' === $state ) {
		return true;
	}
	return bm_is_demo() && 'pendente' === $state;
}

/**
 * Marca visual "A confirmar" usada apenas na proposta.
 *
 * @param string $state Estado de validação.
 */
function bm_pending_badge( string $state ): string {
	if ( 'confirmado' === $state || ! bm_is_demo() ) {
		return '';
	}
	return '<span class="bm-pending" title="' . esc_attr__( 'Informação publicada no site atual, a confirmar pela instituição antes do lançamento.', 'babymoura-core' ) . '">' . esc_html__( 'A confirmar', 'babymoura-core' ) . '</span>';
}

/**
 * Ícone SVG em linha a partir do conjunto do projeto. Decorativo por omissão.
 *
 * @param string $name  Nome do ícone.
 * @param string $label Texto acessível; vazio = decorativo (aria-hidden).
 * @param string $class Classes adicionais.
 */
function bm_icon( string $name, string $label = '', string $class = '' ): string {
	static $icons = null;
	if ( null === $icons ) {
		$icons = require BM_CORE_DIR . 'includes/icons.php';
	}
	if ( ! isset( $icons[ $name ] ) ) {
		$name = 'leaf';
	}
	$a11y = '' === $label
		? 'aria-hidden="true" focusable="false"'
		: 'role="img" aria-label="' . esc_attr( $label ) . '"';
	return sprintf(
		'<svg class="bm-icon bm-icon--%1$s %2$s" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" %3$s>%4$s</svg>',
		esc_attr( $name ),
		esc_attr( $class ),
		$a11y,
		$icons[ $name ] // Markup fixo definido no código do plugin.
	);
}

/**
 * Lista de nomes de ícones disponíveis (para o editor).
 */
function bm_icon_names(): array {
	$icons = require BM_CORE_DIR . 'includes/icons.php';
	return array_keys( $icons );
}

/**
 * Etapas educativas. Nomes centralizados para evitar divergências entre páginas.
 */
function bm_stages(): array {
	return array(
		'creche' => __( 'Creche', 'babymoura-core' ),
		'jardim' => __( 'Jardim de Infância', 'babymoura-core' ),
	);
}

/**
 * URL de uma página interna pelo slug, com recurso ao caminho se a página ainda não existir.
 *
 * @param string $slug Slug da página.
 */
function bm_page_url( string $slug ): string {
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : home_url( '/' . trim( $slug, '/' ) . '/' );
}

<?php
/**
 * Ícone decorativo do sistema visual.
 *
 * @package BabyMoura
 * @var array $attributes
 */

defined( 'ABSPATH' ) || exit;

$bm_name = sanitize_key( $attributes['name'] ?? 'leaf' );
$bm_tone = in_array( $attributes['tone'] ?? 'green', array( 'green', 'yellow', 'peach', 'lavender', 'sage' ), true ) ? $attributes['tone'] : 'green';
$bm_size = in_array( $attributes['size'] ?? 'md', array( 'sm', 'md', 'lg' ), true ) ? $attributes['size'] : 'md';
?>
<span <?php echo get_block_wrapper_attributes( array( 'class' => "bm-badge-icon bm-badge-icon--{$bm_tone} bm-badge-icon--{$bm_size}" ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo bm_icon( $bm_name ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>

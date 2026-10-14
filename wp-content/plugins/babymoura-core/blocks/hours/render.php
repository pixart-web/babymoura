<?php
/**
 * Horário público. Nunca escolhe entre horários em conflito: sem confirmação, convida ao contacto.
 *
 * @package BabyMoura
 * @var array $attributes
 */

defined( 'ABSPATH' ) || exit;
?>
<p <?php echo get_block_wrapper_attributes( array( 'class' => 'bm-hours' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<?php echo bm_icon( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<span><?php echo esc_html( BM_Settings::hours_public() ); ?></span>
</p>

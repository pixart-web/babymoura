<?php
/**
 * Contactos da instituição a partir da fonte central.
 *
 * @package BabyMoura
 * @var array $attributes
 */

defined( 'ABSPATH' ) || exit;

$bm_s       = BM_Settings::all();
$bm_variant = in_array( $attributes['variant'] ?? 'full', array( 'full', 'compact', 'footer' ), true ) ? $attributes['variant'] : 'full';
$bm_lines   = BM_Settings::address_lines();
$bm_newtab  = '<span class="screen-reader-text">' . esc_html__( '(abre numa nova janela)', 'babymoura-core' ) . '</span>';
$bm_wrapper = get_block_wrapper_attributes( array( 'class' => 'bm-contacts bm-contacts--' . $bm_variant ) );
?>
<div <?php echo $bm_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<address class="bm-contacts__list">
		<?php if ( $bm_s['phone_e164'] ) : ?>
			<div class="bm-contacts__item">
				<span class="bm-contacts__icon"><?php echo bm_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<div>
					<?php if ( 'footer' !== $bm_variant ) : ?><span class="bm-contacts__label"><?php esc_html_e( 'Telefone', 'babymoura-core' ); ?></span><?php endif; ?>
					<a class="bm-contacts__value" href="<?php echo esc_url( 'tel:' . $bm_s['phone_e164'] ); ?>"><?php echo esc_html( $bm_s['phone_display'] ); ?></a>
					<?php if ( $bm_s['phone_note'] ) : ?><small class="bm-contacts__note"><?php echo esc_html( $bm_s['phone_note'] ); ?></small><?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
		<?php if ( $bm_s['email'] ) : ?>
			<div class="bm-contacts__item">
				<span class="bm-contacts__icon"><?php echo bm_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<div>
					<?php if ( 'footer' !== $bm_variant ) : ?><span class="bm-contacts__label"><?php esc_html_e( 'Email', 'babymoura-core' ); ?></span><?php endif; ?>
					<a class="bm-contacts__value" href="<?php echo esc_url( 'mailto:' . $bm_s['email'] ); ?>"><?php echo esc_html( $bm_s['email'] ); ?></a>
				</div>
			</div>
		<?php endif; ?>
		<div class="bm-contacts__item">
			<span class="bm-contacts__icon"><?php echo bm_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<div>
				<?php if ( 'footer' !== $bm_variant ) : ?><span class="bm-contacts__label"><?php esc_html_e( 'Morada', 'babymoura-core' ); ?></span><?php endif; ?>
				<span class="bm-contacts__value bm-contacts__address">
					<?php
					if ( 'compact' === $bm_variant ) {
						echo esc_html( implode( ', ', array_slice( $bm_lines, 0, 3 ) ) );
					} else {
						echo implode( '<br>', array_map( 'esc_html', $bm_lines ) ); // phpcs:ignore WordPress.Security.EscapeOutput
					}
					?>
				</span>
				<?php if ( 'full' === $bm_variant && $bm_s['location_note'] ) : ?>
					<small class="bm-contacts__note"><?php echo esc_html( $bm_s['location_note'] ); ?></small>
				<?php endif; ?>
			</div>
		</div>
		<?php if ( ! empty( $attributes['showHours'] ) ) : ?>
			<div class="bm-contacts__item">
				<span class="bm-contacts__icon"><?php echo bm_icon( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<div>
					<?php if ( 'footer' !== $bm_variant ) : ?><span class="bm-contacts__label"><?php esc_html_e( 'Horário', 'babymoura-core' ); ?></span><?php endif; ?>
					<span class="bm-contacts__value bm-contacts__hours"><?php echo esc_html( BM_Settings::hours_public() ); ?></span>
				</div>
			</div>
		<?php endif; ?>
	</address>

	<?php if ( ! empty( $attributes['showMap'] ) && $bm_s['maps_url'] ) : ?>
		<p class="bm-contacts__map">
			<a class="bm-link-arrow" href="<?php echo esc_url( $bm_s['maps_url'] ); ?>" target="_blank" rel="noopener">
				<?php echo bm_icon( 'map' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span><?php esc_html_e( 'Abrir localização no mapa', 'babymoura-core' ); ?></span><?php echo $bm_newtab; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</a>
		</p>
	<?php endif; ?>

	<?php
	if ( 'footer' === $bm_variant ) :
		$bm_social = array_filter(
			array(
				'Facebook'  => $bm_s['facebook_url'],
				'Instagram' => $bm_s['instagram_url'],
				'YouTube'   => $bm_s['youtube_url'],
			)
		);
		if ( $bm_social ) :
			?>
			<ul class="bm-social" aria-label="<?php esc_attr_e( 'Redes sociais', 'babymoura-core' ); ?>">
				<?php foreach ( $bm_social as $bm_name => $bm_url ) : ?>
					<li><a href="<?php echo esc_url( $bm_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $bm_name ); ?><?php echo $bm_newtab; // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
				<?php endforeach; ?>
			</ul>
			<?php
		endif;
	endif;
	?>
</div>

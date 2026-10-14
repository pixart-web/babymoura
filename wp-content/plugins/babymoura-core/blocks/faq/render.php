<?php
/**
 * Perguntas frequentes em acordeão acessível (botões com aria-expanded; conteúdo visível sem JavaScript).
 * Só perguntas confirmadas chegam à produção.
 *
 * @package BabyMoura
 * @var array $attributes
 */

defined( 'ABSPATH' ) || exit;

$bm_list = BM_Content_Types::visible( 'bm_faq' );
$bm_list = array_values(
	array_filter(
		$bm_list,
		static function ( $post ) use ( $attributes ) {
			if ( ! empty( $attributes['group'] ) && get_post_meta( $post->ID, 'bm_group', true ) !== $attributes['group'] ) {
				return false;
			}
			if ( ! empty( $attributes['homeOnly'] ) && ! get_post_meta( $post->ID, 'bm_home', true ) ) {
				return false;
			}
			return true;
		}
	)
);
if ( ! empty( $attributes['limit'] ) ) {
	$bm_list = array_slice( $bm_list, 0, (int) $attributes['limit'] );
}
if ( ! $bm_list ) {
	return;
}
$bm_level = min( 6, max( 2, (int) ( $attributes['headingLevel'] ?? 3 ) ) );
$bm_uid   = wp_unique_id( 'bm-faq-' );

$bm_schema = array();
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'bm-faq', 'data-bm-accordion' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<?php
	foreach ( $bm_list as $bm_i => $bm_post ) :
		$bm_id     = $bm_uid . '-' . $bm_i;
		$bm_answer = apply_filters( 'the_content', $bm_post->post_content ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
		$bm_state  = (string) get_post_meta( $bm_post->ID, 'bm_validation', true );
		if ( 'confirmado' === $bm_state ) {
			$bm_schema[] = array(
				'@type'          => 'Question',
				'name'           => get_the_title( $bm_post ),
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => wp_strip_all_tags( $bm_answer ),
				),
			);
		}
		?>
		<div class="bm-faq__item">
			<h<?php echo (int) $bm_level; ?> class="bm-faq__question">
				<button type="button" class="bm-faq__toggle" aria-expanded="true" aria-controls="<?php echo esc_attr( $bm_id ); ?>" id="<?php echo esc_attr( $bm_id ); ?>-btn">
					<span><?php echo esc_html( get_the_title( $bm_post ) ); ?></span>
					<?php echo bm_pending_badge( $bm_state ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span class="bm-faq__chevron"><?php echo bm_icon( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				</button>
			</h<?php echo (int) $bm_level; ?>>
			<div class="bm-faq__answer" id="<?php echo esc_attr( $bm_id ); ?>" role="region" aria-labelledby="<?php echo esc_attr( $bm_id ); ?>-btn">
				<?php echo wp_kses_post( $bm_answer ); ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
<?php
if ( $bm_schema && ! bm_is_demo() ) {
	BM_SEO::add_schema(
		array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $bm_schema,
		)
	);
}

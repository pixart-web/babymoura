<?php
/**
 * Serviços complementares (separados das atividades). Fonte única: tipo de conteúdo "Serviços".
 *
 * @package BabyMoura
 * @var array $attributes
 */

defined( 'ABSPATH' ) || exit;

$bm_stage  = in_array( $attributes['stage'] ?? 'all', array( 'all', 'creche', 'jardim' ), true ) ? $attributes['stage'] : 'all';
$bm_stages = bm_stages();
$bm_items  = array();
foreach ( BM_Content_Types::visible( 'bm_servico' ) as $bm_post ) {
	$bm_for = array_filter( explode( ',', (string) get_post_meta( $bm_post->ID, 'bm_stages', true ) ) );
	if ( 'all' !== $bm_stage && $bm_for && ! in_array( $bm_stage, $bm_for, true ) ) {
		continue;
	}
	$bm_items[] = array( $bm_post, $bm_for );
}

$bm_wrapper = get_block_wrapper_attributes( array( 'class' => 'bm-services' ) );
if ( ! $bm_items ) {
	if ( current_user_can( 'edit_pages' ) ) {
		echo '<p ' . $bm_wrapper . '>' . esc_html__( 'Sem serviços confirmados para mostrar.', 'babymoura-core' ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	return;
}
?>
<ul <?php echo $bm_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<?php
	foreach ( $bm_items as list( $bm_post, $bm_for ) ) :
		$bm_state = (string) get_post_meta( $bm_post->ID, 'bm_validation', true );
		?>
		<li class="bm-service">
			<span class="bm-service__icon"><?php echo bm_icon( (string) get_post_meta( $bm_post->ID, 'bm_icon', true ) ?: 'heart' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<div>
				<h3 class="bm-service__title"><?php echo esc_html( get_the_title( $bm_post ) ); ?></h3>
				<?php if ( has_excerpt( $bm_post ) ) : ?>
					<p class="bm-service__text"><?php echo esc_html( get_the_excerpt( $bm_post ) ); ?></p>
				<?php endif; ?>
				<p class="bm-activity__meta">
					<?php foreach ( $bm_for as $bm_key ) : ?>
						<span class="bm-tag"><?php echo esc_html( $bm_stages[ $bm_key ] ?? '' ); ?></span>
					<?php endforeach; ?>
					<?php echo bm_pending_badge( $bm_state ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</p>
			</div>
		</li>
	<?php endforeach; ?>
</ul>

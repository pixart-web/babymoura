<?php
/**
 * Atividades por etapa e tipo. Fonte única: tipo de conteúdo "Atividades".
 * Nunca infere que uma atividade serve todas as idades ou está incluída na mensalidade.
 *
 * @package BabyMoura
 * @var array $attributes
 */

defined( 'ABSPATH' ) || exit;

$bm_stage  = in_array( $attributes['stage'] ?? 'all', array( 'all', 'creche', 'jardim' ), true ) ? $attributes['stage'] : 'all';
$bm_kind   = in_array( $attributes['kind'] ?? 'all', array( 'all', 'curricular', 'extracurricular' ), true ) ? $attributes['kind'] : 'all';
$bm_limit  = max( 0, (int) ( $attributes['limit'] ?? 0 ) );
$bm_layout = in_array( $attributes['layout'] ?? 'grid', array( 'grid', 'compact', 'matrix' ), true ) ? $attributes['layout'] : 'grid';
$bm_stages = bm_stages();
$bm_types  = array(
	'curricular'      => __( 'Curricular', 'babymoura-core' ),
	'extracurricular' => __( 'Extracurricular', 'babymoura-core' ),
);

$bm_items = array();
foreach ( BM_Content_Types::visible( 'bm_atividade' ) as $bm_post ) {
	$bm_offer = array();
	foreach ( array_keys( $bm_stages ) as $bm_key ) {
		$bm_type = (string) get_post_meta( $bm_post->ID, 'bm_' . $bm_key, true );
		if ( isset( $bm_types[ $bm_type ] ) ) {
			$bm_offer[ $bm_key ] = $bm_type;
		}
	}
	if ( 'all' !== $bm_stage ) {
		if ( ! isset( $bm_offer[ $bm_stage ] ) ) {
			continue;
		}
		if ( 'all' !== $bm_kind && $bm_offer[ $bm_stage ] !== $bm_kind ) {
			continue;
		}
	} elseif ( 'all' !== $bm_kind && ! in_array( $bm_kind, $bm_offer, true ) ) {
		continue;
	}
	if ( ! $bm_offer ) {
		continue;
	}
	$bm_items[] = array(
		'post'  => $bm_post,
		'offer' => $bm_offer,
		'icon'  => (string) get_post_meta( $bm_post->ID, 'bm_icon', true ),
		'state' => (string) get_post_meta( $bm_post->ID, 'bm_validation', true ),
	);
}
if ( $bm_limit ) {
	$bm_items = array_slice( $bm_items, 0, $bm_limit );
}

$bm_wrapper = get_block_wrapper_attributes( array( 'class' => 'bm-activities bm-activities--' . $bm_layout ) );

if ( ! $bm_items ) {
	if ( current_user_can( 'edit_pages' ) ) {
		echo '<p ' . $bm_wrapper . '>' . esc_html__( 'Sem atividades confirmadas para mostrar com estes filtros.', 'babymoura-core' ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	return;
}

if ( 'matrix' === $bm_layout ) :
	?>
	<div <?php echo $bm_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
		<div class="bm-table-scroll" role="region" aria-label="<?php esc_attr_e( 'Resumo das atividades por etapa', 'babymoura-core' ); ?>" tabindex="0">
			<table class="bm-matrix">
				<caption><?php esc_html_e( 'Atividades por resposta educativa', 'babymoura-core' ); ?></caption>
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Atividade', 'babymoura-core' ); ?></th>
						<?php foreach ( $bm_stages as $bm_label ) : ?>
							<th scope="col"><?php echo esc_html( $bm_label ); ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $bm_items as $bm_item ) : ?>
						<tr>
							<th scope="row"><?php echo esc_html( get_the_title( $bm_item['post'] ) ); ?> <?php echo bm_pending_badge( $bm_item['state'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></th>
							<?php foreach ( array_keys( $bm_stages ) as $bm_key ) : ?>
								<td>
									<?php if ( isset( $bm_item['offer'][ $bm_key ] ) ) : ?>
										<span class="bm-tag bm-tag--<?php echo esc_attr( $bm_item['offer'][ $bm_key ] ); ?>"><?php echo esc_html( $bm_types[ $bm_item['offer'][ $bm_key ] ] ); ?></span>
									<?php else : ?>
										<span class="bm-muted"><span aria-hidden="true">—</span><span class="screen-reader-text"><?php esc_html_e( 'Não indicada', 'babymoura-core' ); ?></span></span>
									<?php endif; ?>
								</td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
	<?php
	return;
endif;
?>
<ul <?php echo $bm_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<?php foreach ( $bm_items as $bm_item ) : ?>
		<li class="bm-activity">
			<span class="bm-activity__icon"><?php echo bm_icon( $bm_item['icon'] ?: 'star' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<div class="bm-activity__body">
				<h3 class="bm-activity__title"><?php echo esc_html( get_the_title( $bm_item['post'] ) ); ?></h3>
				<?php if ( 'grid' === $bm_layout && has_excerpt( $bm_item['post'] ) ) : ?>
					<p class="bm-activity__text"><?php echo esc_html( get_the_excerpt( $bm_item['post'] ) ); ?></p>
				<?php endif; ?>
				<p class="bm-activity__meta">
					<?php
					foreach ( $bm_item['offer'] as $bm_key => $bm_type ) :
						if ( 'all' !== $bm_stage && $bm_key !== $bm_stage ) {
							continue;
						}
						?>
						<span class="bm-tag bm-tag--<?php echo esc_attr( $bm_type ); ?>">
							<?php
							echo esc_html(
								'all' === $bm_stage
									? $bm_stages[ $bm_key ] . ' · ' . $bm_types[ $bm_type ]
									: $bm_types[ $bm_type ]
							);
							?>
						</span>
					<?php endforeach; ?>
					<?php echo bm_pending_badge( $bm_item['state'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</p>
			</div>
		</li>
	<?php endforeach; ?>
</ul>

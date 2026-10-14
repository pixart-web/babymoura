<?php
/**
 * Galeria de espaços: imagens da biblioteca com categoria "Espaço".
 * Filtros com botões (aria-pressed) e visualização ampliada num <dialog> acessível (front.js).
 * Em produção só aparecem imagens com autorização confirmada.
 *
 * @package BabyMoura
 * @var array $attributes
 */

defined( 'ABSPATH' ) || exit;

$bm_terms = get_terms(
	array(
		'taxonomy'   => BM_Media_Rights::TAXONOMY,
		'hide_empty' => false,
		'meta_key'   => 'bm_order',
		'orderby'    => 'meta_value_num',
	)
);
if ( is_wp_error( $bm_terms ) ) {
	$bm_terms = array();
}
$bm_space = sanitize_key( $attributes['space'] ?? '' );

$bm_query_args = array(
	'post_type'      => 'attachment',
	'post_status'    => 'inherit',
	'post_mime_type' => 'image',
	'posts_per_page' => ! empty( $attributes['limit'] ) ? (int) $attributes['limit'] : 60,
	'orderby'        => array(
		'menu_order' => 'ASC',
		'ID'         => 'ASC',
	),
	'no_found_rows'  => true,
	'tax_query'      => array(
		array(
			'taxonomy' => BM_Media_Rights::TAXONOMY,
			'operator' => $bm_space ? 'IN' : 'EXISTS',
			'field'    => 'slug',
			'terms'    => $bm_space ? array( $bm_space ) : array(),
		),
	),
);
if ( ! bm_is_demo() ) {
	$bm_query_args['meta_query'] = array(
		array(
			'key'   => 'bm_rights',
			'value' => 'autorizada',
		),
	);
}
$bm_images = get_posts( $bm_query_args );
if ( ! $bm_images ) {
	return;
}

$bm_uid     = wp_unique_id( 'bm-gallery-' );
$bm_used    = array();
$bm_filters = ! empty( $attributes['filters'] ) && ! $bm_space;
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'bm-gallery', 'data-bm-gallery' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<?php
	if ( $bm_filters ) :
		foreach ( $bm_images as $bm_img ) {
			foreach ( wp_get_object_terms( $bm_img->ID, BM_Media_Rights::TAXONOMY, array( 'fields' => 'slugs' ) ) as $bm_slug ) {
				$bm_used[ $bm_slug ] = true;
			}
		}
		?>
		<div class="bm-gallery__filters" role="group" aria-label="<?php esc_attr_e( 'Filtrar espaços', 'babymoura-core' ); ?>">
			<button type="button" class="bm-chip" aria-pressed="true" data-filter=""><?php esc_html_e( 'Todos', 'babymoura-core' ); ?></button>
			<?php foreach ( $bm_terms as $bm_term ) : ?>
				<?php
				if ( empty( $bm_used[ $bm_term->slug ] ) ) {
					continue;
				}
				?>
				<button type="button" class="bm-chip" aria-pressed="false" data-filter="<?php echo esc_attr( $bm_term->slug ); ?>"><?php echo esc_html( $bm_term->name ); ?></button>
			<?php endforeach; ?>
		</div>
		<p class="screen-reader-text" aria-live="polite" data-bm-gallery-status></p>
	<?php endif; ?>

	<ul class="bm-gallery__grid" id="<?php echo esc_attr( $bm_uid ); ?>">
		<?php
		foreach ( $bm_images as $bm_i => $bm_img ) :
			$bm_slugs   = wp_get_object_terms( $bm_img->ID, BM_Media_Rights::TAXONOMY, array( 'fields' => 'slugs' ) );
			$bm_names   = wp_get_object_terms( $bm_img->ID, BM_Media_Rights::TAXONOMY, array( 'fields' => 'names' ) );
			$bm_caption = wp_get_attachment_caption( $bm_img->ID );
			$bm_alt     = (string) get_post_meta( $bm_img->ID, '_wp_attachment_image_alt', true );
			$bm_full    = wp_get_attachment_image_src( $bm_img->ID, 'large' );
			$bm_prov    = 'ilustrativa' === BM_Media_Rights::state( $bm_img->ID );
			?>
			<li class="bm-gallery__item<?php echo 0 === $bm_i % 5 ? ' is-wide' : ''; ?>" data-spaces="<?php echo esc_attr( implode( ' ', $bm_slugs ) ); ?>">
				<figure>
					<a class="bm-gallery__open" href="<?php echo esc_url( $bm_full[0] ?? '' ); ?>" data-full="<?php echo esc_url( $bm_full[0] ?? '' ); ?>" data-width="<?php echo (int) ( $bm_full[1] ?? 0 ); ?>" data-height="<?php echo (int) ( $bm_full[2] ?? 0 ); ?>" data-alt="<?php echo esc_attr( $bm_alt ); ?>" data-caption="<?php echo esc_attr( $bm_caption ); ?>" data-provisional="<?php echo $bm_prov ? '1' : '0'; ?>">
						<?php
						echo wp_get_attachment_image(
							$bm_img->ID,
							'medium_large',
							false,
							array(
								'loading' => 'lazy',
								'sizes'   => '(min-width: 1024px) 400px, (min-width: 600px) 50vw, 100vw',
							)
						);
						?>
						<span class="screen-reader-text"><?php esc_html_e( 'Ver imagem ampliada', 'babymoura-core' ); ?></span>
						<?php if ( $bm_prov ) : ?>
							<span class="bm-provisional" aria-hidden="true"><?php esc_html_e( 'Ilustração provisória', 'babymoura-core' ); ?></span>
						<?php endif; ?>
					</a>
					<figcaption>
						<?php if ( $bm_names ) : ?>
							<span class="bm-gallery__space"><?php echo esc_html( implode( ' · ', $bm_names ) ); ?></span>
						<?php endif; ?>
						<?php echo esc_html( $bm_caption ); ?>
					</figcaption>
				</figure>
			</li>
		<?php endforeach; ?>
	</ul>
</div>

<?php
/**
 * Title: Página não encontrada
 * Slug: babymoura/not-found
 * Inserter: no
 *
 * @package BabyMoura
 */

?>
<!-- wp:group {"className":"bm-404","layout":{"type":"constrained"}} -->
<div class="wp-block-group bm-404"><!-- wp:babymoura/icon {"name":"leaf","tone":"sage","size":"lg"} /-->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading"><?php esc_html_e( 'Esta página mudou de lugar', 'babymoura' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"bm-lead"} -->
<p class="bm-lead"><?php esc_html_e( 'O endereço pode ter mudado com o novo site. Estes caminhos ajudam a encontrar o que procura:', 'babymoura' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"bm-404__links"} -->
<ul class="wp-block-list bm-404__links"><!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Página inicial', 'babymoura' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/creche/' ) ); ?>"><?php esc_html_e( 'Creche', 'babymoura' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/jardim-de-infancia/' ) ); ?>"><?php esc_html_e( 'Jardim de Infância', 'babymoura' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/contactos/' ) ); ?>"><?php esc_html_e( 'Contactos e pedido de visita', 'babymoura' ); ?></a></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:group -->

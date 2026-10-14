<?php
/**
 * Convite final partilhado pelas páginas (incluído pelos padrões de página).
 *
 * @package BabyMoura
 */

?>
<!-- wp:group {"tagName":"section","align":"full","className":"bm-cta-wrap","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull bm-cta-wrap"><!-- wp:group {"align":"wide","className":"bm-cta","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide bm-cta"><!-- wp:group {"className":"bm-cta__text","layout":{"type":"default"}} -->
<div class="wp-block-group bm-cta__text"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'O próximo grande começo pode começar com uma visita.', 'babymoura' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Venha conhecer o espaço, a equipa e o jardim. Combinamos consigo o melhor momento.', 'babymoura' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/contactos/#pedido-de-visita' ) ); ?>"><?php esc_html_e( 'Pedir uma visita', 'babymoura' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:babymoura/contact-details {"variant":"compact","showHours":false} /--></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

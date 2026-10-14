<?php
/**
 * Title: Rodapé
 * Slug: babymoura/footer
 * Categories: footer
 * Block Types: core/template-part/footer
 * Inserter: no
 *
 * @package BabyMoura
 */

$bm_logo       = get_theme_file_uri( 'assets/images/brand/babymoura-logo.webp' );
$bm_legal      = babymoura_setting( 'legal_name', 'Babymoura, Lda.' );
$bm_complaints = babymoura_setting( 'complaints_url', 'https://www.livroreclamacoes.pt/' );
$bm_ral_name   = babymoura_setting( 'ral_name', 'Centro de Arbitragem de Consumo do Algarve' );
$bm_ral_url    = babymoura_setting( 'ral_url', 'https://www.consumoalgarve.pt/' );
$bm_newtab     = '<span class="screen-reader-text">' . esc_html__( '(abre numa nova janela)', 'babymoura' ) . '</span>';
?>
<!-- wp:group {"className":"bm-footer","layout":{"type":"constrained"}} -->
<div class="wp-block-group bm-footer"><!-- wp:group {"align":"wide","className":"bm-footer__grid","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide bm-footer__grid"><!-- wp:group {"className":"bm-footer__brand","layout":{"type":"default"}} -->
<div class="wp-block-group bm-footer__brand"><!-- wp:html -->
<a class="bm-logo bm-logo--footer" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( $bm_logo ); ?>" width="200" height="131" alt="<?php esc_attr_e( 'BabyMoura — página inicial', 'babymoura' ); ?>" loading="lazy"></a>
<!-- /wp:html -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Creche e jardim de infância em Vilamoura. Um lugar para brincar, criar laços e crescer ao seu ritmo.', 'babymoura' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-bm-soft"} -->
<div class="wp-block-button is-style-bm-soft"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/contactos/#pedido-de-visita' ) ); ?>"><?php esc_html_e( 'Pedir uma visita', 'babymoura' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"bm-footer__col","layout":{"type":"default"}} -->
<div class="wp-block-group bm-footer__col"><!-- wp:heading {"level":2,"className":"bm-footer__title"} -->
<h2 class="wp-block-heading bm-footer__title"><?php esc_html_e( 'Contactos', 'babymoura' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:babymoura/contact-details {"variant":"footer","showMap":false} /--></div>
<!-- /wp:group -->

<!-- wp:group {"className":"bm-footer__col","layout":{"type":"default"}} -->
<div class="wp-block-group bm-footer__col"><!-- wp:heading {"level":2,"className":"bm-footer__title"} -->
<h2 class="wp-block-heading bm-footer__title"><?php esc_html_e( 'Explorar', 'babymoura' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"bm-footer__links"} -->
<ul class="wp-block-list bm-footer__links"><!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/a-babymoura/' ) ); ?>"><?php esc_html_e( 'A BabyMoura', 'babymoura' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/creche/' ) ); ?>"><?php esc_html_e( 'Creche', 'babymoura' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/jardim-de-infancia/' ) ); ?>"><?php esc_html_e( 'Jardim de Infância', 'babymoura' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/espacos/' ) ); ?>"><?php esc_html_e( 'Espaços', 'babymoura' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/atividades-e-servicos/' ) ); ?>"><?php esc_html_e( 'Atividades e Serviços', 'babymoura' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/pre-inscricao/' ) ); ?>"><?php esc_html_e( 'Pré-inscrição', 'babymoura' ); ?></a></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"bm-footer__col","layout":{"type":"default"}} -->
<div class="wp-block-group bm-footer__col"><!-- wp:heading {"level":2,"className":"bm-footer__title"} -->
<h2 class="wp-block-heading bm-footer__title"><?php esc_html_e( 'Informação', 'babymoura' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"bm-footer__links"} -->
<ul class="wp-block-list bm-footer__links"><!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/privacidade/' ) ); ?>"><?php esc_html_e( 'Política de Privacidade', 'babymoura' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/cookies/' ) ); ?>"><?php esc_html_e( 'Política de Cookies', 'babymoura' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( $bm_complaints ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Livro de Reclamações', 'babymoura' ); ?><?php echo $bm_newtab; // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( $bm_ral_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $bm_ral_name ); ?><?php echo $bm_newtab; // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:paragraph {"className":"bm-footer__legal"} -->
<p class="bm-footer__legal">© <?php echo esc_html( wp_date( 'Y' ) . ' ' . $bm_legal ); ?> · <?php esc_html_e( 'Vilamoura, Algarve', 'babymoura' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

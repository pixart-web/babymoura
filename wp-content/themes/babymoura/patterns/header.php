<?php
/**
 * Title: Cabeçalho
 * Slug: babymoura/header
 * Categories: header
 * Block Types: core/template-part/header
 * Inserter: no
 *
 * @package BabyMoura
 */

$bm_phone_display = babymoura_setting( 'phone_display', '+351 289 302 934' );
$bm_phone_link    = 'tel:' . babymoura_setting( 'phone_e164', '+351289302934' );
$bm_logo          = get_theme_file_uri( 'assets/images/brand/babymoura-logo.webp' );
$bm_visit         = home_url( '/contactos/#pedido-de-visita' );
?>
<!-- wp:group {"className":"bm-header","layout":{"type":"constrained"}} -->
<div class="wp-block-group bm-header"><!-- wp:group {"align":"wide","className":"bm-topbar","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide bm-topbar"><!-- wp:paragraph {"className":"bm-topbar__place"} -->
<p class="bm-topbar__place"><?php esc_html_e( 'Creche e Jardim de Infância · Vilamoura, Algarve', 'babymoura' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"bm-topbar__links"} -->
<p class="bm-topbar__links"><a href="<?php echo esc_url( $bm_phone_link ); ?>"><?php echo esc_html( $bm_phone_display ); ?></a><a href="<?php echo esc_url( home_url( '/pre-inscricao/' ) ); ?>"><?php esc_html_e( 'Pré-inscrição', 'babymoura' ); ?></a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"wide","className":"bm-header__bar","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide bm-header__bar"><!-- wp:html -->
<a class="bm-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><img src="<?php echo esc_url( $bm_logo ); ?>" width="200" height="131" alt="<?php esc_attr_e( 'BabyMoura — página inicial', 'babymoura' ); ?>"></a>
<!-- /wp:html -->

<!-- wp:navigation {"ariaLabel":"<?php esc_attr_e( 'Principal', 'babymoura' ); ?>","overlayMenu":"mobile","openSubmenusOnClick":true,"className":"bm-nav","layout":{"type":"flex","justifyContent":"center"}} -->
<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'A BabyMoura', 'babymoura' ); ?>","url":"<?php echo esc_url( home_url( '/a-babymoura/' ) ); ?>","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-submenu {"label":"<?php esc_attr_e( 'Educação', 'babymoura' ); ?>","kind":"custom"} -->
<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Creche', 'babymoura' ); ?>","description":"<?php esc_attr_e( 'Primeira infância', 'babymoura' ); ?>","url":"<?php echo esc_url( home_url( '/creche/' ) ); ?>","kind":"custom"} /-->

<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Jardim de Infância', 'babymoura' ); ?>","description":"<?php esc_attr_e( 'Dos 3 aos 6 anos', 'babymoura' ); ?>","url":"<?php echo esc_url( home_url( '/jardim-de-infancia/' ) ); ?>","kind":"custom"} /-->
<!-- /wp:navigation-submenu -->

<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Espaços', 'babymoura' ); ?>","url":"<?php echo esc_url( home_url( '/espacos/' ) ); ?>","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Atividades', 'babymoura' ); ?>","url":"<?php echo esc_url( home_url( '/atividades-e-servicos/' ) ); ?>","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Contactos', 'babymoura' ); ?>","url":"<?php echo esc_url( home_url( '/contactos/' ) ); ?>","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Pré-inscrição', 'babymoura' ); ?>","url":"<?php echo esc_url( home_url( '/pre-inscricao/' ) ); ?>","kind":"custom","className":"bm-nav-overlay-only"} /-->

<!-- wp:navigation-link {"label":"<?php echo esc_attr( sprintf( /* translators: %s: telefone */ __( 'Ligar %s', 'babymoura' ), $bm_phone_display ) ); ?>","url":"<?php echo esc_url( $bm_phone_link ); ?>","kind":"custom","className":"bm-nav-overlay-only bm-nav-phone"} /-->
<!-- /wp:navigation -->

<!-- wp:buttons {"className":"bm-header__cta"} -->
<div class="wp-block-buttons bm-header__cta"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $bm_visit ); ?>"><?php esc_html_e( 'Pedir uma visita', 'babymoura' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

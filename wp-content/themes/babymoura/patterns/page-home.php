<?php
/**
 * Title: Página inicial
 * Slug: babymoura/page-home
 * Categories: babymoura-pages
 * Post Types: page
 * Inserter: no
 * Description: Composição completa da página inicial. Importada como conteúdo editável pelo comando "wp babymoura seed".
 *
 * @package BabyMoura
 */

$bm_visit = esc_url( home_url( '/contactos/#pedido-de-visita' ) );
?>
<!-- wp:group {"tagName":"section","align":"full","className":"bm-hero","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull bm-hero"><!-- wp:group {"align":"wide","className":"bm-hero__grid","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide bm-hero__grid"><!-- wp:group {"className":"bm-hero__text","layout":{"type":"default"}} -->
<div class="wp-block-group bm-hero__text"><!-- wp:paragraph {"className":"bm-eyebrow"} -->
<p class="bm-eyebrow"><?php esc_html_e( 'Creche e Jardim de Infância em Vilamoura', 'babymoura' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"className":"bm-hero__title"} -->
<h1 class="wp-block-heading bm-hero__title"><?php esc_html_e( 'Pequenas descobertas.', 'babymoura' ); ?> <em><?php esc_html_e( 'Grandes começos.', 'babymoura' ); ?></em></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"bm-lead"} -->
<p class="bm-lead"><?php esc_html_e( 'Um lugar para brincar, criar laços e crescer ao seu ritmo. Conheça a BabyMoura e o espaço onde cada dia convida a descobrir.', 'babymoura' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"bm-hero__actions"} -->
<div class="wp-block-buttons bm-hero__actions"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo $bm_visit; // phpcs:ignore ?>"><?php esc_html_e( 'Pedir uma visita', 'babymoura' ); ?></a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-bm-soft"} -->
<div class="wp-block-button is-style-bm-soft"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/espacos/' ) ); ?>"><?php esc_html_e( 'Conhecer os espaços', 'babymoura' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:paragraph {"className":"bm-hero__place"} -->
<p class="bm-hero__place"><?php esc_html_e( 'Rua das Estrelas · Clube Vilamar · Vilamoura, Algarve', 'babymoura' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"bm-hero__media","layout":{"type":"default"}} -->
<div class="wp-block-group bm-hero__media"><?php echo babymoura_image_block( 'ilustracao-jardim-hero', 'is-style-bm-organic bm-hero__image' ); // phpcs:ignore ?>

<!-- wp:paragraph {"className":"bm-hero__note"} -->
<p class="bm-hero__note"><?php esc_html_e( 'Jardim exterior e horta pedagógica', 'babymoura' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","align":"full","className":"bm-trust","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull bm-trust"><!-- wp:heading {"className":"screen-reader-text"} -->
<h2 class="wp-block-heading screen-reader-text"><?php esc_html_e( 'A BabyMoura em resumo', 'babymoura' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:group {"align":"wide","className":"bm-trust__grid","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide bm-trust__grid"><!-- wp:group {"className":"bm-trust__item","layout":{"type":"default"}} -->
<div class="wp-block-group bm-trust__item"><!-- wp:babymoura/icon {"name":"star","tone":"yellow"} /-->

<!-- wp:paragraph {"className":"bm-trust__title"} -->
<p class="bm-trust__title"><?php esc_html_e( 'Mais de 30 anos em Vilamoura', 'babymoura' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Uma instituição com história, lembrada por várias gerações de famílias pelo carinho e pela alegria do seu ambiente.', 'babymoura' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"bm-trust__item","layout":{"type":"default"}} -->
<div class="wp-block-group bm-trust__item"><!-- wp:babymoura/icon {"name":"blocks","tone":"peach"} /-->

<!-- wp:paragraph {"className":"bm-trust__title"} -->
<p class="bm-trust__title"><?php esc_html_e( 'Creche e jardim de infância', 'babymoura' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Duas respostas educativas no mesmo espaço, da primeira infância até aos 6 anos.', 'babymoura' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"bm-trust__item","layout":{"type":"default"}} -->
<div class="wp-block-group bm-trust__item"><!-- wp:babymoura/icon {"name":"sprout","tone":"sage"} /-->

<!-- wp:paragraph {"className":"bm-trust__title"} -->
<p class="bm-trust__title"><?php esc_html_e( 'Natureza no dia a dia', 'babymoura' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Um amplo jardim exterior, com relva, árvores e uma horta pedagógica.', 'babymoura' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","align":"full","className":"bm-stages","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull bm-stages"><!-- wp:group {"align":"wide","className":"bm-section-head","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide bm-section-head"><!-- wp:paragraph {"className":"bm-eyebrow"} -->
<p class="bm-eyebrow"><?php esc_html_e( 'Oferta educativa', 'babymoura' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Duas etapas, uma atenção próxima', 'babymoura' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"bm-lead"} -->
<p class="bm-lead"><?php esc_html_e( 'Cada idade tem as suas necessidades. Acompanhamos a primeira infância e os anos do pré-escolar com propostas pensadas para cada fase.', 'babymoura' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"wide","className":"bm-stages__grid","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide bm-stages__grid"><!-- wp:group {"className":"bm-stage bm-stage--creche","layout":{"type":"default"}} -->
<div class="wp-block-group bm-stage bm-stage--creche"><?php echo babymoura_image_block( 'ilustracao-creche', 'is-style-bm-organic bm-stage__image' ); // phpcs:ignore ?>

<!-- wp:group {"className":"bm-stage__body","layout":{"type":"default"}} -->
<div class="wp-block-group bm-stage__body"><!-- wp:paragraph {"className":"bm-stage__label"} -->
<p class="bm-stage__label"><?php esc_html_e( 'Creche · primeira infância', 'babymoura' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php esc_html_e( 'Creche', 'babymoura' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Os primeiros sorrisos, passos e palavras pedem colo, atenção e um ambiente cuidadosamente organizado. Na creche, os laços afetivos vêm primeiro: é a partir deles que cada criança ganha confiança para explorar.', 'babymoura' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"bm-link-arrow"} -->
<p class="bm-link-arrow"><a href="<?php echo esc_url( home_url( '/creche/' ) ); ?>"><?php esc_html_e( 'Conhecer a creche', 'babymoura' ); ?></a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"bm-stage bm-stage--jardim","layout":{"type":"default"}} -->
<div class="wp-block-group bm-stage bm-stage--jardim"><?php echo babymoura_image_block( 'ilustracao-jardim-infancia', 'is-style-bm-organic bm-stage__image' ); // phpcs:ignore ?>

<!-- wp:group {"className":"bm-stage__body","layout":{"type":"default"}} -->
<div class="wp-block-group bm-stage__body"><!-- wp:paragraph {"className":"bm-stage__label"} -->
<p class="bm-stage__label"><?php esc_html_e( 'Jardim de Infância · dos 3 aos 6 anos', 'babymoura' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php esc_html_e( 'Jardim de Infância', 'babymoura' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Projetos que nascem dos interesses das crianças, tempo para experimentar e aprender em grupo. No jardim de infância, autonomia e criatividade crescem lado a lado, com as famílias por perto.', 'babymoura' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"bm-link-arrow"} -->
<p class="bm-link-arrow"><a href="<?php echo esc_url( home_url( '/jardim-de-infancia/' ) ); ?>"><?php esc_html_e( 'Conhecer o jardim de infância', 'babymoura' ); ?></a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","align":"full","className":"bm-garden","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull bm-garden"><!-- wp:group {"align":"wide","className":"bm-garden__frame","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide bm-garden__frame"><?php echo babymoura_image_block( 'ilustracao-exterior', 'bm-garden__image' ); // phpcs:ignore ?>

<!-- wp:group {"className":"bm-garden__card","layout":{"type":"default"}} -->
<div class="wp-block-group bm-garden__card"><!-- wp:paragraph {"className":"bm-eyebrow"} -->
<p class="bm-eyebrow"><?php esc_html_e( 'Lá fora', 'babymoura' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Um jardim para descobrir', 'babymoura' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Lá fora, o dia continua a ensinar. O amplo jardim exterior convida a correr, observar e cuidar — e a horta pedagógica mostra, semana após semana, como tudo cresce com tempo e atenção.', 'babymoura' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"is-style-bm-leaves"} -->
<ul class="wp-block-list is-style-bm-leaves"><!-- wp:list-item -->
<li><?php esc_html_e( 'Relvado e árvores', 'babymoura' ); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><?php esc_html_e( 'Equipamentos de exterior adequados às idades', 'babymoura' ); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><?php esc_html_e( 'Pavimentos que amortecem as quedas', 'babymoura' ); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><?php esc_html_e( 'Horta pedagógica', 'babymoura' ); ?></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:paragraph {"className":"bm-link-arrow"} -->
<p class="bm-link-arrow"><a href="<?php echo esc_url( home_url( '/espacos/' ) ); ?>"><?php esc_html_e( 'Ver todos os espaços', 'babymoura' ); ?></a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","align":"full","className":"bm-principles","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull bm-principles"><!-- wp:group {"align":"wide","className":"bm-section-head bm-section-head--center","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide bm-section-head bm-section-head--center"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Crescer com afeto e curiosidade', 'babymoura' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"bm-lead"} -->
<p class="bm-lead"><?php esc_html_e( 'Quatro ideias simples orientam o dia a dia na BabyMoura.', 'babymoura' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"wide","className":"bm-principles__grid","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide bm-principles__grid"><!-- wp:group {"className":"bm-principle","layout":{"type":"default"}} -->
<div class="wp-block-group bm-principle"><!-- wp:babymoura/icon {"name":"heart","tone":"peach","size":"lg"} /-->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php esc_html_e( 'Relações de confiança', 'babymoura' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Laços afetivos seguros são a base para crescer com autoestima e vontade de descobrir.', 'babymoura' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"bm-principle","layout":{"type":"default"}} -->
<div class="wp-block-group bm-principle"><!-- wp:babymoura/icon {"name":"snail","tone":"yellow","size":"lg"} /-->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php esc_html_e( 'O ritmo de cada criança', 'babymoura' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Respeitamos as características individuais de cada criança e o tempo de que precisa.', 'babymoura' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"bm-principle","layout":{"type":"default"}} -->
<div class="wp-block-group bm-principle"><!-- wp:babymoura/icon {"name":"palette","tone":"sage","size":"lg"} /-->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php esc_html_e( 'Exploração criativa', 'babymoura' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Experiências variadas que dão espaço à expressão, à imaginação e à curiosidade.', 'babymoura' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"bm-principle","layout":{"type":"default"}} -->
<div class="wp-block-group bm-principle"><!-- wp:babymoura/icon {"name":"family","tone":"lavender","size":"lg"} /-->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php esc_html_e( 'Famílias por perto', 'babymoura' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Diálogo, apoio e partilha de informação com as famílias, que participam na educação dos filhos.', 'babymoura' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","align":"full","className":"bm-experiences","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull bm-experiences"><!-- wp:group {"align":"wide","className":"bm-split","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide bm-split"><!-- wp:group {"className":"bm-split__text","layout":{"type":"default"}} -->
<div class="wp-block-group bm-split__text"><!-- wp:paragraph {"className":"bm-eyebrow"} -->
<p class="bm-eyebrow"><?php esc_html_e( 'Atividades', 'babymoura' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Experiências que convidam a aprender', 'babymoura' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Música, movimento, línguas e horta fazem parte do percurso. Algumas atividades são curriculares, outras extracurriculares — e a oferta varia entre a creche e o jardim de infância.', 'babymoura' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"bm-link-arrow"} -->
<p class="bm-link-arrow"><a href="<?php echo esc_url( home_url( '/atividades-e-servicos/' ) ); ?>"><?php esc_html_e( 'Ver atividades e serviços por etapa', 'babymoura' ); ?></a></p>
<!-- /wp:paragraph -->

<?php echo babymoura_image_block( 'ilustracao-musica', 'is-style-bm-organic bm-split__image' ); // phpcs:ignore ?></div>
<!-- /wp:group -->

<!-- wp:group {"className":"bm-split__aside","layout":{"type":"default"}} -->
<div class="wp-block-group bm-split__aside"><!-- wp:paragraph {"className":"bm-aside-title"} -->
<p class="bm-aside-title"><?php esc_html_e( 'Atividades curriculares, por etapa', 'babymoura' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:babymoura/activities {"kind":"curricular","layout":"compact"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","align":"full","className":"bm-visit","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull bm-visit"><!-- wp:group {"align":"wide","className":"bm-section-head","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide bm-section-head"><!-- wp:paragraph {"className":"bm-eyebrow"} -->
<p class="bm-eyebrow"><?php esc_html_e( 'Visitas', 'babymoura' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Conhecer antes de decidir', 'babymoura' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"bm-lead"} -->
<p class="bm-lead"><?php esc_html_e( 'Uma visita é a melhor forma de sentir o espaço e conhecer a equipa. O percurso é simples.', 'babymoura' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:list {"ordered":true,"className":"bm-steps"} -->
<ol class="wp-block-list bm-steps"><!-- wp:list-item -->
<li><strong><?php esc_html_e( 'Envie o pedido', 'babymoura' ); ?></strong> <?php esc_html_e( 'Pelo formulário, por telefone ou por email.', 'babymoura' ); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong><?php esc_html_e( 'Recebe o contacto da equipa', 'babymoura' ); ?></strong> <?php esc_html_e( 'Para conhecer a sua família e responder às primeiras dúvidas.', 'babymoura' ); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong><?php esc_html_e( 'Combinamos a visita', 'babymoura' ); ?></strong> <?php esc_html_e( 'Num dia e hora que funcionem para ambos.', 'babymoura' ); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong><?php esc_html_e( 'Esclarecemos os próximos passos', 'babymoura' ); ?></strong> <?php esc_html_e( 'Da pré-inscrição a tudo o que precisar para decidir com tranquilidade.', 'babymoura' ); ?></li>
<!-- /wp:list-item --></ol>
<!-- /wp:list -->

<!-- wp:buttons {"align":"wide","className":"bm-visit__actions"} -->
<div class="wp-block-buttons alignwide bm-visit__actions"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo $bm_visit; // phpcs:ignore ?>"><?php esc_html_e( 'Pedir uma visita', 'babymoura' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","align":"full","className":"bm-faq-section","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull bm-faq-section"><!-- wp:group {"align":"wide","className":"bm-split bm-split--faq","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide bm-split bm-split--faq"><!-- wp:group {"className":"bm-split__text","layout":{"type":"default"}} -->
<div class="wp-block-group bm-split__text"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Perguntas das famílias', 'babymoura' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'As dúvidas mais comuns de quem está a conhecer a BabyMoura. Não encontra a sua? Fale connosco.', 'babymoura' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"bm-link-arrow"} -->
<p class="bm-link-arrow"><a href="<?php echo esc_url( home_url( '/contactos/' ) ); ?>"><?php esc_html_e( 'Contactar a BabyMoura', 'babymoura' ); ?></a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:babymoura/faq {"homeOnly":true} /--></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<?php require __DIR__ . '/partials/cta-final.php'; ?>

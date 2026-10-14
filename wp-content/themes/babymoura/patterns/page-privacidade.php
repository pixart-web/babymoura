<?php
/**
 * Title: Política de Privacidade
 * Slug: babymoura/page-privacidade
 * Categories: babymoura-pages
 * Post Types: page
 * Inserter: no
 * Description: Texto proposto. Validação jurídica pela instituição obrigatória antes da publicação.
 *
 * @package BabyMoura
 */

$bm_legal = babymoura_setting( 'legal_name', 'Babymoura, Lda.' );
$bm_email = babymoura_setting( 'email', 'info@babymoura.com' );
$bm_mail  = '<a href="mailto:' . esc_attr( $bm_email ) . '">' . esc_html( $bm_email ) . '</a>';

$bm_blocks = array(
	babymoura_group( 'bm-callout bm-callout--draft', babymoura_p( '<strong>' . esc_html__( 'Texto proposto para validação.', 'babymoura' ) . '</strong> ' . esc_html__( 'Esta versão descreve o funcionamento do novo website e deve ser revista e aprovada juridicamente pela instituição antes da publicação.', 'babymoura' ) ) ),
	babymoura_p( esc_html__( 'Esta política explica que dados pessoais recolhemos através deste website, para que os usamos, durante quanto tempo os conservamos e como pode exercer os seus direitos.', 'babymoura' ), 'bm-lead' ),
	babymoura_h( __( 'Quem é responsável pelos dados', 'babymoura' ) ),
	/* translators: 1: designação legal, 2: email */
	babymoura_p( sprintf( esc_html__( 'O responsável pelo tratamento é a %1$s, Rua das Estrelas – Clube Vilamar, Vilamoura, 8125-468 Quarteira. Para qualquer questão sobre privacidade, escreva para %2$s.', 'babymoura' ), esc_html( $bm_legal ), $bm_mail ) ),
	babymoura_h( __( 'Que dados recolhemos', 'babymoura' ) ),
	babymoura_p( esc_html__( 'Apenas os dados que nos envia nos formulários:', 'babymoura' ) ),
	babymoura_list(
		array(
			'<strong>' . esc_html__( 'Pedido de visita:', 'babymoura' ) . '</strong> ' . esc_html__( 'nome do adulto responsável, email, telefone (opcional), resposta educativa pretendida e mensagem (opcional).', 'babymoura' ),
			'<strong>' . esc_html__( 'Pré-inscrição:', 'babymoura' ) . '</strong> ' . esc_html__( 'os mesmos contactos, a etapa pretendida, a faixa etária aproximada da criança e o mês e ano de entrada desejados.', 'babymoura' ),
		)
	),
	babymoura_p( esc_html__( 'Nesta fase não pedimos o nome da criança, a data de nascimento, documentos ou dados de saúde. Pedimos-lhe que não inclua este tipo de informação nas mensagens.', 'babymoura' ) ),
	babymoura_h( __( 'Para que usamos os dados', 'babymoura' ) ),
	babymoura_p( esc_html__( 'Para responder ao seu pedido, combinar uma visita e dar seguimento à manifestação de interesse numa das respostas educativas. O tratamento é feito a seu pedido, como passo prévio a uma eventual inscrição.', 'babymoura' ) ),
	babymoura_p( esc_html__( 'Não usamos estes dados para marketing, não enviamos newsletters e não vendemos nem cedemos dados a terceiros para esses fins.', 'babymoura' ) ),
	babymoura_h( __( 'Durante quanto tempo', 'babymoura' ) ),
	babymoura_p( esc_html__( 'Os pedidos recebidos através do website são apagados automaticamente do sistema do site ao fim de 180 dias. Se o contacto evoluir para uma inscrição, os dados passam a reger-se pelas regras aplicáveis a esse processo.', 'babymoura' ) ),
	babymoura_h( __( 'Quem tem acesso', 'babymoura' ) ),
	babymoura_p( esc_html__( 'A equipa da BabyMoura responsável por responder aos contactos. Recorremos a prestadores de serviços de alojamento e de email, que tratam os dados apenas por nossa conta e segundo as nossas instruções.', 'babymoura' ) ),
	babymoura_h( __( 'Os seus direitos', 'babymoura' ) ),
	/* translators: %s: email */
	babymoura_p( sprintf( esc_html__( 'Pode pedir o acesso, a retificação ou o apagamento dos seus dados, bem como a limitação ou a oposição ao tratamento e a portabilidade, escrevendo para %s.', 'babymoura' ), $bm_mail ) ),
	babymoura_p( esc_html__( 'Tem também o direito de apresentar reclamação à Comissão Nacional de Proteção de Dados (CNPD):', 'babymoura' ) . ' <a href="https://www.cnpd.pt/" target="_blank" rel="noopener">www.cnpd.pt<span class="screen-reader-text">' . esc_html__( '(abre numa nova janela)', 'babymoura' ) . '</span></a>.' ),
	babymoura_h( __( 'Cookies', 'babymoura' ) ),
	babymoura_p( esc_html__( 'Saiba como este website usa cookies na', 'babymoura' ) . ' <a href="' . esc_url( home_url( '/cookies/' ) ) . '">' . esc_html__( 'Política de Cookies', 'babymoura' ) . '</a>.' ),
);
echo implode( "\n\n", $bm_blocks ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput

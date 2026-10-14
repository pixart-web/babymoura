<?php
/**
 * Checklist de lançamento: torna visíveis as dependências de produção (nada passa silenciosamente).
 *
 * @package BabyMoura
 */

defined( 'ABSPATH' ) || exit;

final class BM_Checklist {

	const MANUAL_OPTION = 'bm_checklist_manual';

	public static function init(): void {
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'dashboard' ) );
		add_action( 'admin_post_bm_checklist', array( __CLASS__, 'save_manual' ) );
		add_action( 'admin_notices', array( __CLASS__, 'production_notice' ) );
	}

	public static function manual_items(): array {
		return array(
			'historia'    => __( 'Direção confirmou "mais de 30 anos de história" (ano de fundação).', 'babymoura-core' ),
			'idades'      => __( 'Idade mínima de entrada na creche confirmada (site atual indica 0 e 4 meses).', 'babymoura-core' ),
			'espaco'      => __( 'Área do espaço exterior confirmada, se se quiser publicar (site atual indica 1400 m² e 1500 m²).', 'babymoura-core' ),
			'lotacoes'    => __( 'Lotações por sala e equipa: decisão sobre publicação.', 'babymoura-core' ),
			'privacidade' => __( 'Política de Privacidade validada juridicamente pela instituição.', 'babymoura-core' ),
			'cookies'     => __( 'Política de Cookies revista (e banner de consentimento, se for ativada medição).', 'babymoura-core' ),
			'ral'         => __( 'Livro de Reclamações e entidade RAL verificados para a entidade.', 'babymoura-core' ),
			'redes'       => __( 'Contas de redes sociais confirmadas como ativas e oficiais.', 'babymoura-core' ),
			'fotografias' => __( 'Sessão fotográfica realizada e consentimentos de imagem das famílias recolhidos.', 'babymoura-core' ),
			'logotipo'    => __( 'Logótipo em alta resolução ou vetorial fornecido.', 'babymoura-core' ),
			'dns'         => __( 'Redirecionamentos 301 testados no domínio real após migração.', 'babymoura-core' ),
		);
	}

	/**
	 * @return array<int, array{0: bool, 1: string, 2: string}> [ok, título, detalhe]
	 */
	public static function checks(): array {
		$pending = static function ( string $type ): int {
			$q = new WP_Query(
				array(
					'post_type'      => $type,
					'post_status'    => array( 'publish', 'draft' ),
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'meta_query'     => array(
						array(
							'key'     => 'bm_validation',
							'value'   => 'confirmado',
							'compare' => '!=',
						),
					),
				)
			);
			return (int) $q->post_count;
		};
		$media = static function ( string $state ): int {
			$q = new WP_Query(
				array(
					'post_type'      => 'attachment',
					'post_status'    => 'inherit',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'meta_query'     => array(
						array(
							'key'   => 'bm_rights',
							'value' => $state,
						),
					),
				)
			);
			return (int) $q->post_count;
		};

		$smtp_real = defined( 'BABYMOURA_SMTP_HOST' ) && BABYMOURA_SMTP_HOST && 'mailpit' !== BABYMOURA_SMTP_HOST;
		$act       = $pending( 'bm_atividade' );
		$srv       = $pending( 'bm_servico' );
		$faq       = $pending( 'bm_faq' );
		$illus     = $media( 'ilustrativa' );
		$rights    = $media( 'pendente' );

		return array(
			array( ! bm_is_demo(), __( 'Modo de produção', 'babymoura-core' ), bm_is_demo() ? __( 'O site está em modo demonstração: formulários não enviam dados.', 'babymoura-core' ) : __( 'Formulários ativos.', 'babymoura-core' ) ),
			array( (bool) BM_Forms::recipient(), __( 'Destinatário dos pedidos', 'babymoura-core' ), BM_Forms::recipient() ? BM_Forms::recipient() : __( 'Definir BABYMOURA_FORM_RECIPIENT no servidor.', 'babymoura-core' ) ),
			array( $smtp_real, __( 'Transporte de email', 'babymoura-core' ), $smtp_real ? __( 'SMTP configurado no servidor.', 'babymoura-core' ) : __( 'Configurar SMTP autenticado com SPF/DKIM (localmente usa Mailpit).', 'babymoura-core' ) ),
			array( (bool) bm_setting( 'hours_confirmed' ), __( 'Horário', 'babymoura-core' ), bm_setting( 'hours_confirmed' ) ? BM_Settings::hours_public() : __( 'Horário em conflito no site atual (08h30–18h30 vs 08h00–20h00). O site mostra "Contacte-nos para conhecer o horário."', 'babymoura-core' ) ),
			/* translators: %d: número de itens */
			array( 0 === $act, __( 'Atividades validadas', 'babymoura-core' ), sprintf( _n( '%d atividade por confirmar.', '%d atividades por confirmar.', $act, 'babymoura-core' ), $act ) ),
			/* translators: %d: número de itens */
			array( 0 === $srv, __( 'Serviços validados', 'babymoura-core' ), sprintf( _n( '%d serviço por confirmar.', '%d serviços por confirmar.', $srv, 'babymoura-core' ), $srv ) ),
			/* translators: %d: número de itens */
			array( 0 === $faq, __( 'Perguntas frequentes validadas', 'babymoura-core' ), sprintf( _n( '%d pergunta por confirmar ou em rascunho.', '%d perguntas por confirmar ou em rascunho.', $faq, 'babymoura-core' ), $faq ) ),
			/* translators: %d: número de imagens */
			array( 0 === $illus, __( 'Ilustrações provisórias substituídas', 'babymoura-core' ), sprintf( _n( '%d imagem provisória ainda na biblioteca.', '%d imagens provisórias ainda na biblioteca.', $illus, 'babymoura-core' ), $illus ) ),
			/* translators: %d: número de imagens */
			array( 0 === $rights, __( 'Autorizações de imagem', 'babymoura-core' ), sprintf( _n( '%d imagem com autorização pendente.', '%d imagens com autorização pendente.', $rights, 'babymoura-core' ), $rights ) ),
			array( ! BM_SEO::noindex(), __( 'Indexação', 'babymoura-core' ), BM_SEO::noindex() ? __( 'O site está com noindex (correto para preview). No lançamento: ativar produção, ambiente "production" e visibilidade para motores de pesquisa.', 'babymoura-core' ) : __( 'Indexação ativa.', 'babymoura-core' ) ),
		);
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'edit_pages' ) ) {
			return;
		}
		$manual = (array) get_option( self::MANUAL_OPTION, array() );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Checklist de lançamento', 'babymoura-core' ); ?></h1>
			<p><?php esc_html_e( 'Tudo o que precisa de acesso, aprovação editorial ou configuração antes de substituir o site atual.', 'babymoura-core' ); ?></p>
			<h2><?php esc_html_e( 'Verificações automáticas', 'babymoura-core' ); ?></h2>
			<table class="widefat striped" style="max-width:960px">
				<tbody>
				<?php foreach ( self::checks() as list( $ok, $title, $detail ) ) : ?>
					<tr>
						<td style="width:8em"><strong style="color:<?php echo $ok ? '#1e6b3a' : '#8a4b00'; ?>"><?php echo $ok ? esc_html__( 'Pronto', 'babymoura-core' ) : esc_html__( 'Pendente', 'babymoura-core' ); ?></strong></td>
						<th scope="row" style="width:16em"><?php echo esc_html( $title ); ?></th>
						<td><?php echo esc_html( $detail ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<h2><?php esc_html_e( 'Validação editorial', 'babymoura-core' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="bm_checklist">
				<?php wp_nonce_field( 'bm_checklist' ); ?>
				<fieldset>
					<legend class="screen-reader-text"><?php esc_html_e( 'Itens a confirmar', 'babymoura-core' ); ?></legend>
					<?php foreach ( self::manual_items() as $key => $label ) : ?>
						<p><label><input type="checkbox" name="items[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( ! empty( $manual[ $key ] ) ); ?> <?php disabled( ! current_user_can( 'manage_options' ) ); ?>> <?php echo esc_html( $label ); ?></label></p>
					<?php endforeach; ?>
				</fieldset>
				<?php if ( current_user_can( 'manage_options' ) ) : ?>
					<?php submit_button( __( 'Guardar validação', 'babymoura-core' ) ); ?>
				<?php endif; ?>
			</form>
		</div>
		<?php
	}

	public static function save_manual(): void {
		check_admin_referer( 'bm_checklist' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Sem permissão.', 'babymoura-core' ), 403 );
		}
		$items = isset( $_POST['items'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['items'] ) ) : array();
		$save  = array();
		foreach ( array_keys( self::manual_items() ) as $key ) {
			$save[ $key ] = in_array( $key, $items, true ) ? 1 : 0;
		}
		update_option( self::MANUAL_OPTION, $save, false );
		wp_safe_redirect( admin_url( 'admin.php?page=babymoura' ) );
		exit;
	}

	public static function dashboard(): void {
		wp_add_dashboard_widget(
			'bm_checklist',
			__( 'BabyMoura — estado do lançamento', 'babymoura-core' ),
			static function () {
				$checks = self::checks();
				$open   = count( array_filter( $checks, static fn( $c ) => ! $c[0] ) );
				/* translators: %d: número de verificações pendentes */
				echo '<p>' . esc_html( sprintf( _n( '%d verificação pendente.', '%d verificações pendentes.', $open, 'babymoura-core' ), $open ) ) . '</p>';
				echo '<p><a class="button" href="' . esc_url( admin_url( 'admin.php?page=babymoura' ) ) . '">' . esc_html__( 'Abrir checklist', 'babymoura-core' ) . '</a></p>';
			}
		);
	}

	/**
	 * Em produção, avisa sempre que existam imagens provisórias ou sem autorização.
	 */
	public static function production_notice(): void {
		if ( bm_is_demo() || ! current_user_can( 'edit_pages' ) ) {
			return;
		}
		foreach ( self::checks() as list( $ok, $title, $detail ) ) {
			if ( ! $ok ) {
				printf( '<div class="notice notice-warning"><p><strong>BabyMoura:</strong> %s — %s</p></div>', esc_html( $title ), esc_html( $detail ) );
			}
		}
	}
}

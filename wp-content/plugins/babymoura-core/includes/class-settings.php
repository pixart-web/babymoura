<?php
/**
 * Fonte central de dados institucionais: contactos, morada, horário, redes e ligações legais.
 * Editável em BabyMoura → Dados da instituição, sem tocar em código.
 *
 * @package BabyMoura
 */

defined( 'ABSPATH' ) || exit;

final class BM_Settings {

	const OPTION = 'babymoura_settings';

	/** @var array|null */
	private static $cache = null;

	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'update_option_' . self::OPTION, array( __CLASS__, 'flush' ) );
	}

	/**
	 * Valores de partida: base factual publicada no site atual (sujeita a confirmação).
	 */
	public static function defaults(): array {
		return array(
			'brand_name'      => 'BabyMoura',
			'legal_name'      => 'Babymoura, Lda.',
			'phone_display'   => '+351 289 302 934',
			'phone_e164'      => '+351289302934',
			'phone_note'      => 'Chamada para a rede fixa nacional',
			'email'           => 'info@babymoura.com',
			'street'          => 'Rua das Estrelas – Clube Vilamar',
			'locality'        => 'Vilamoura',
			'postal_code'     => '8125-468',
			'city'            => 'Quarteira',
			'region'          => 'Algarve',
			'country'         => 'Portugal',
			'location_note'   => 'No centro de Vilamoura, a cerca de 200 metros da Marina.',
			'maps_url'        => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( 'BabyMoura, Rua das Estrelas, Vilamoura, 8125-468 Quarteira' ),
			'hours_text'      => '',
			'hours_confirmed' => 0,
			'facebook_url'    => 'https://www.facebook.com/Babymoura-Creche-e-Jardim-de-Inf%C3%A2ncia-431580123715402/',
			'instagram_url'   => 'https://www.instagram.com/babymouracreche',
			'youtube_url'     => 'https://www.youtube.com/channel/UC8qDvPGu1ZEIHV3ZLolGHNQ',
			'complaints_url'  => 'https://www.livroreclamacoes.pt/',
			'ral_name'        => 'Centro de Arbitragem de Consumo do Algarve',
			'ral_url'         => 'https://www.consumoalgarve.pt/',
			'retention_days'  => 180,
			'analytics_on'    => 0,
		);
	}

	public static function all(): array {
		if ( null === self::$cache ) {
			$saved       = get_option( self::OPTION, array() );
			self::$cache = wp_parse_args( is_array( $saved ) ? $saved : array(), self::defaults() );
		}
		return self::$cache;
	}

	public static function flush(): void {
		self::$cache = null;
	}

	/**
	 * Texto de horário publicado. Enquanto não houver horário confirmado, nunca escolhe um dos valores em conflito.
	 */
	public static function hours_public(): string {
		$s = self::all();
		if ( ! empty( $s['hours_confirmed'] ) && '' !== trim( (string) $s['hours_text'] ) ) {
			return (string) $s['hours_text'];
		}
		return __( 'Contacte-nos para conhecer o horário.', 'babymoura-core' );
	}

	public static function address_lines(): array {
		$s = self::all();
		return array_values(
			array_filter(
				array(
					$s['street'],
					$s['locality'],
					trim( $s['postal_code'] . ' ' . $s['city'] ),
					trim( $s['region'] . ( $s['country'] ? ' · ' . $s['country'] : '' ), ' ·' ),
				)
			)
		);
	}

	public static function menu(): void {
		$icon = 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="4"/><path d="M12 2.5v2M12 19.5v2M4.6 4.6 6 6M18 18l1.4 1.4M2.5 12h2M19.5 12h2M4.6 19.4 6 18M18 6l1.4-1.4"/></svg>' );
		add_menu_page( 'BabyMoura', 'BabyMoura', 'edit_pages', 'babymoura', array( BM_Checklist::class, 'render_page' ), $icon, 3 );
		add_submenu_page( 'babymoura', __( 'Checklist de lançamento', 'babymoura-core' ), __( 'Checklist de lançamento', 'babymoura-core' ), 'edit_pages', 'babymoura', array( BM_Checklist::class, 'render_page' ) );
		add_submenu_page( 'babymoura', __( 'Dados da instituição', 'babymoura-core' ), __( 'Dados da instituição', 'babymoura-core' ), 'manage_options', 'babymoura-settings', array( __CLASS__, 'render_page' ) );
	}

	private static function fields(): array {
		return array(
			'identidade' => array(
				'title'  => __( 'Identidade', 'babymoura-core' ),
				'fields' => array(
					'brand_name' => array( __( 'Nome da marca', 'babymoura-core' ), 'text' ),
					'legal_name' => array( __( 'Entidade responsável (designação legal)', 'babymoura-core' ), 'text' ),
				),
			),
			'contactos'  => array(
				'title'  => __( 'Contactos', 'babymoura-core' ),
				'fields' => array(
					'phone_display' => array( __( 'Telefone (como é mostrado)', 'babymoura-core' ), 'text' ),
					'phone_e164'    => array( __( 'Telefone para ligação (formato +351…)', 'babymoura-core' ), 'text' ),
					'phone_note'    => array( __( 'Nota sobre o custo da chamada', 'babymoura-core' ), 'text' ),
					'email'         => array( __( 'Email público', 'babymoura-core' ), 'email' ),
				),
			),
			'morada'     => array(
				'title'  => __( 'Morada e localização', 'babymoura-core' ),
				'fields' => array(
					'street'        => array( __( 'Rua', 'babymoura-core' ), 'text' ),
					'locality'      => array( __( 'Localidade', 'babymoura-core' ), 'text' ),
					'postal_code'   => array( __( 'Código postal', 'babymoura-core' ), 'text' ),
					'city'          => array( __( 'Designação postal', 'babymoura-core' ), 'text' ),
					'region'        => array( __( 'Região', 'babymoura-core' ), 'text' ),
					'country'       => array( __( 'País', 'babymoura-core' ), 'text' ),
					'location_note' => array( __( 'Referência de localização', 'babymoura-core' ), 'text' ),
					'maps_url'      => array( __( 'Ligação para serviço de mapas', 'babymoura-core' ), 'url' ),
				),
			),
			'horario'    => array(
				'title'       => __( 'Horário', 'babymoura-core' ),
				'description' => __( 'O site atual publica dois horários diferentes. Enquanto o horário não for confirmado, o site mostra "Contacte-nos para conhecer o horário."', 'babymoura-core' ),
				'fields'      => array(
					'hours_text'      => array( __( 'Horário a publicar', 'babymoura-core' ), 'text' ),
					'hours_confirmed' => array( __( 'Horário confirmado pela direção', 'babymoura-core' ), 'checkbox' ),
				),
			),
			'redes'      => array(
				'title'  => __( 'Redes sociais e ligações institucionais', 'babymoura-core' ),
				'fields' => array(
					'facebook_url'   => array( 'Facebook', 'url' ),
					'instagram_url'  => array( 'Instagram', 'url' ),
					'youtube_url'    => array( 'YouTube', 'url' ),
					'complaints_url' => array( __( 'Livro de Reclamações', 'babymoura-core' ), 'url' ),
					'ral_name'       => array( __( 'Entidade de resolução alternativa de litígios', 'babymoura-core' ), 'text' ),
					'ral_url'        => array( __( 'Ligação da entidade RAL', 'babymoura-core' ), 'url' ),
				),
			),
			'pedidos'    => array(
				'title'  => __( 'Pedidos e medição', 'babymoura-core' ),
				'fields' => array(
					'retention_days' => array( __( 'Conservar pedidos durante (dias)', 'babymoura-core' ), 'number' ),
					'analytics_on'   => array( __( 'Ativar eventos de medição (só após decisão sobre consentimento)', 'babymoura-core' ), 'checkbox' ),
				),
			),
		);
	}

	public static function register(): void {
		register_setting(
			'babymoura_settings_group',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
		if ( ! defined( 'BABYMOURA_MODE' ) ) {
			register_setting(
				'babymoura_settings_group',
				'babymoura_mode',
				array(
					'type'              => 'string',
					'sanitize_callback' => static fn( $v ) => 'production' === $v ? 'production' : 'demo',
					'default'           => 'demo',
				)
			);
		}
	}

	/**
	 * @param mixed $input Valores submetidos.
	 */
	public static function sanitize( $input ): array {
		$input = is_array( $input ) ? $input : array();
		$out   = self::defaults();
		foreach ( self::fields() as $section ) {
			foreach ( $section['fields'] as $key => $def ) {
				$type = $def[1];
				$raw  = $input[ $key ] ?? '';
				switch ( $type ) {
					case 'checkbox':
						$out[ $key ] = empty( $raw ) ? 0 : 1;
						break;
					case 'email':
						$out[ $key ] = sanitize_email( $raw );
						break;
					case 'url':
						$out[ $key ] = esc_url_raw( $raw, array( 'https', 'http' ) );
						break;
					case 'number':
						$out[ $key ] = max( 30, min( 730, absint( $raw ) ) );
						break;
					default:
						$out[ $key ] = sanitize_text_field( $raw );
				}
			}
		}
		$out['phone_e164'] = preg_replace( '/[^0-9+]/', '', (string) $out['phone_e164'] );
		return $out;
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$s = self::all();
		?>
		<div class="wrap bm-admin">
			<h1><?php esc_html_e( 'Dados da instituição', 'babymoura-core' ); ?></h1>
			<p><?php esc_html_e( 'Fonte única para contactos, morada, horário e ligações. Qualquer alteração aqui atualiza o cabeçalho, o rodapé, a página de contactos e os dados estruturados.', 'babymoura-core' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'babymoura_settings_group' ); ?>
				<h2><?php esc_html_e( 'Modo do site', 'babymoura-core' ); ?></h2>
				<?php if ( defined( 'BABYMOURA_MODE' ) ) : ?>
					<p><strong><?php echo esc_html( 'demo' === bm_mode() ? __( 'Demonstração', 'babymoura-core' ) : __( 'Produção', 'babymoura-core' ) ); ?></strong> — <?php esc_html_e( 'definido na configuração do servidor (BABYMOURA_MODE).', 'babymoura-core' ); ?></p>
				<?php else : ?>
					<label><select name="babymoura_mode">
						<option value="demo" <?php selected( bm_mode(), 'demo' ); ?>><?php esc_html_e( 'Demonstração (nada é enviado)', 'babymoura-core' ); ?></option>
						<option value="production" <?php selected( bm_mode(), 'production' ); ?>><?php esc_html_e( 'Produção', 'babymoura-core' ); ?></option>
					</select></label>
				<?php endif; ?>
				<p><?php esc_html_e( 'Destinatário dos pedidos:', 'babymoura-core' ); ?> <code><?php echo esc_html( BM_Forms::recipient() ? BM_Forms::recipient() : __( 'não configurado', 'babymoura-core' ) ); ?></code> <?php esc_html_e( '(definido no servidor, BABYMOURA_FORM_RECIPIENT)', 'babymoura-core' ); ?></p>

				<?php foreach ( self::fields() as $section ) : ?>
					<h2><?php echo esc_html( $section['title'] ); ?></h2>
					<?php if ( ! empty( $section['description'] ) ) : ?>
						<p class="description"><?php echo esc_html( $section['description'] ); ?></p>
					<?php endif; ?>
					<table class="form-table" role="presentation">
						<?php
						foreach ( $section['fields'] as $key => $def ) :
							$id   = 'bm-' . $key;
							$name = self::OPTION . '[' . $key . ']';
							?>
							<tr>
								<th scope="row"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $def[0] ); ?></label></th>
								<td>
									<?php if ( 'checkbox' === $def[1] ) : ?>
										<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( ! empty( $s[ $key ] ) ); ?>>
									<?php else : ?>
										<input class="regular-text" type="<?php echo esc_attr( 'number' === $def[1] ? 'number' : ( 'url' === $def[1] ? 'url' : ( 'email' === $def[1] ? 'email' : 'text' ) ) ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $s[ $key ] ); ?>">
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</table>
				<?php endforeach; ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}

<?php
/**
 * Pedidos de visita e pré-inscrição.
 *
 * - Validação no servidor (espelhada no cliente), limites de tamanho, honeypot, token assinado com tempo mínimo,
 *   limitação de pedidos por IP (guardado apenas como hash) e prevenção de duplicados.
 * - Modo demonstração: valida e responde "demo"; nada é guardado nem enviado.
 * - Produção: guarda o pedido (privado) e notifica por email. Sucesso só depois de o pedido ficar guardado;
 *   falhas de email ficam registadas e são repetidas automaticamente.
 * - Nunca escreve dados pessoais em logs.
 *
 * @package BabyMoura
 */

defined( 'ABSPATH' ) || exit;

final class BM_Forms {

	const TYPES          = array( 'visita', 'pre-inscricao' );
	const MAX_BODY       = 12000;
	const MIN_SECONDS    = 3;
	const MAX_AGE        = 43200; // 12 h.
	const RATE_LIMIT     = 10;
	const RATE_WINDOW    = 900; // 15 min.
	const DEDUPE_WINDOW  = 600; // 10 min.
	const MAIL_RETRIES   = 3;

	public static function init(): void {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
		add_action( 'admin_post_bm_form', array( __CLASS__, 'handle_post' ) );
		add_action( 'admin_post_nopriv_bm_form', array( __CLASS__, 'handle_post' ) );
		add_action( 'phpmailer_init', array( __CLASS__, 'smtp' ) );
		add_filter( 'wp_mail_from', array( __CLASS__, 'mail_from' ) );
		add_filter( 'wp_mail_from_name', static fn( $name ) => ( defined( 'BABYMOURA_FORM_FROM' ) && is_email( BABYMOURA_FORM_FROM ) ) ? 'Website BabyMoura' : $name );
		add_action( 'bm_purge_requests', array( __CLASS__, 'purge' ) );
		add_action( 'bm_retry_notification', array( __CLASS__, 'retry_notification' ) );
		add_action( 'init', array( __CLASS__, 'schedule_events' ) );
		add_filter( 'manage_bm_pedido_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_bm_pedido_posts_custom_column', array( __CLASS__, 'column' ), 10, 2 );
		add_action( 'admin_post_bm_resend', array( __CLASS__, 'handle_resend' ) );
	}

	public static function schedule_events(): void {
		if ( ! wp_next_scheduled( 'bm_purge_requests' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'bm_purge_requests' );
		}
	}

	/* ---------------------------------------------------------------------
	 * Opções dos campos (fonte única para formulário, validação e email).
	 * ------------------------------------------------------------------- */

	public static function stage_options( string $type ): array {
		$opts = bm_stages();
		if ( 'visita' === $type ) {
			$opts['indefinido'] = __( 'Ainda não sei / quero conhecer ambas', 'babymoura-core' );
		}
		return $opts;
	}

	public static function age_bands(): array {
		return array(
			'0-1' => __( 'Menos de 1 ano', 'babymoura-core' ),
			'1-2' => __( 'Entre 1 e 2 anos', 'babymoura-core' ),
			'2-3' => __( 'Entre 2 e 3 anos', 'babymoura-core' ),
			'3-4' => __( 'Entre 3 e 4 anos', 'babymoura-core' ),
			'4-5' => __( 'Entre 4 e 5 anos', 'babymoura-core' ),
			'5-6' => __( 'Entre 5 e 6 anos', 'babymoura-core' ),
			'gravidez' => __( 'Ainda não nasceu', 'babymoura-core' ),
		);
	}

	public static function months(): array {
		$out = array();
		for ( $m = 1; $m <= 12; $m++ ) {
			$out[ sprintf( '%02d', $m ) ] = ucfirst( wp_date( 'F', mktime( 12, 0, 0, $m, 1, 2026 ) ) );
		}
		return $out;
	}

	public static function years(): array {
		$y = (int) wp_date( 'Y' );
		return array(
			(string) $y       => (string) $y,
			(string) ( $y + 1 ) => (string) ( $y + 1 ),
			(string) ( $y + 2 ) => (string) ( $y + 2 ),
		);
	}

	public static function recipient(): string {
		$email = defined( 'BABYMOURA_FORM_RECIPIENT' ) ? (string) BABYMOURA_FORM_RECIPIENT : '';
		return is_email( $email ) ? $email : '';
	}

	/**
	 * Mensagens para o utilizador. Receção nunca equivale a confirmação de visita ou vaga.
	 */
	public static function message( string $status, string $type ): string {
		$visit = 'visita' === $type;
		switch ( $status ) {
			case 'demo':
				return __( 'Demonstração concluída: os dados estão válidos, mas não foram enviados nem guardados.', 'babymoura-core' );
			case 'ok':
			case 'duplicate':
				return $visit
					? __( 'Obrigado! Recebemos o seu pedido. A equipa da BabyMoura vai contactá-lo para combinar a visita — este registo ainda não é uma marcação.', 'babymoura-core' )
					: __( 'Obrigado! Recebemos a sua pré-inscrição. A equipa vai contactá-lo com os próximos passos. Este registo não confirma vaga nem inscrição.', 'babymoura-core' );
			case 'invalid':
				return __( 'Há campos por corrigir. Reveja as indicações junto a cada campo.', 'babymoura-core' );
			case 'rate_limited':
				return __( 'Recebemos vários pedidos seguidos a partir desta ligação. Aguarde alguns minutos ou contacte-nos por telefone ou email.', 'babymoura-core' );
			case 'too_fast':
				return __( 'O formulário foi enviado muito depressa. Aguarde um instante e tente de novo.', 'babymoura-core' );
			case 'expired':
				return __( 'A página esteve aberta muito tempo. Atualize-a e envie novamente.', 'babymoura-core' );
			case 'not_configured':
				return __( 'De momento não é possível enviar o pedido online. Contacte-nos por telefone ou email — os seus dados não foram enviados.', 'babymoura-core' );
			default:
				return __( 'Não foi possível registar o pedido. Os seus dados continuam no formulário: tente novamente dentro de instantes ou contacte-nos por telefone ou email.', 'babymoura-core' );
		}
	}

	/* ---------------------------------------------------------------------
	 * Token anti-abuso (sem cookies, compatível com cache de página).
	 * ------------------------------------------------------------------- */

	public static function token( ?int $time = null ): string {
		$time = $time ?? time();
		return $time . '.' . substr( hash_hmac( 'sha256', 'bm-form|' . $time, wp_salt( 'nonce' ) ), 0, 32 );
	}

	/**
	 * @return string '' se válido; caso contrário o código de estado.
	 */
	public static function check_token( string $token ): string {
		$parts = explode( '.', $token );
		if ( 2 !== count( $parts ) || ! ctype_digit( $parts[0] ) ) {
			return 'expired';
		}
		$time = (int) $parts[0];
		if ( ! hash_equals( self::token( $time ), $token ) ) {
			return 'expired';
		}
		$age = time() - $time;
		if ( $age < self::MIN_SECONDS ) {
			return 'too_fast';
		}
		if ( $age > self::MAX_AGE ) {
			return 'expired';
		}
		return '';
	}

	/* ---------------------------------------------------------------------
	 * Validação
	 * ------------------------------------------------------------------- */

	/**
	 * @param string $type Tipo de formulário.
	 * @param array  $raw  Dados recebidos.
	 * @return array{0: array, 1: array} Dados limpos e erros por campo.
	 */
	public static function validate( string $type, array $raw ): array {
		$str    = static fn( $k ) => isset( $raw[ $k ] ) && is_scalar( $raw[ $k ] ) ? trim( (string) $raw[ $k ] ) : '';
		$errors = array();
		$clean  = array();

		$name = sanitize_text_field( $str( 'name' ) );
		if ( '' === $name ) {
			$errors['name'] = __( 'Indique o seu nome.', 'babymoura-core' );
		} elseif ( mb_strlen( $name ) < 2 || mb_strlen( $name ) > 100 ) {
			$errors['name'] = __( 'O nome deve ter entre 2 e 100 caracteres.', 'babymoura-core' );
		}
		$clean['name'] = $name;

		$email = $str( 'email' );
		if ( '' === $email ) {
			$errors['email'] = __( 'Indique um email para podermos responder.', 'babymoura-core' );
		} elseif ( mb_strlen( $email ) > 150 || ! is_email( $email ) ) {
			$errors['email'] = __( 'O email não parece válido. Exemplo: nome@exemplo.pt', 'babymoura-core' );
		}
		$clean['email'] = sanitize_email( $email );

		$phone = $str( 'phone' );
		if ( '' !== $phone && ! preg_match( '/^\+?[0-9 ()\-]{9,20}$/', $phone ) ) {
			$errors['phone'] = __( 'Indique um telefone com 9 a 20 dígitos (pode incluir +351).', 'babymoura-core' );
		}
		$clean['phone'] = preg_replace( '/[^0-9+ ]/', '', $phone );

		$stage = $str( 'stage' );
		if ( ! array_key_exists( $stage, self::stage_options( $type ) ) ) {
			$errors['stage'] = __( 'Escolha a resposta educativa.', 'babymoura-core' );
		}
		$clean['stage'] = $stage;

		if ( 'pre-inscricao' === $type ) {
			$band = $str( 'age_band' );
			if ( ! array_key_exists( $band, self::age_bands() ) ) {
				$errors['age_band'] = __( 'Escolha a faixa etária aproximada.', 'babymoura-core' );
			}
			$clean['age_band'] = $band;

			$month = $str( 'start_month' );
			$year  = $str( 'start_year' );
			if ( ! array_key_exists( $month, self::months() ) || ! array_key_exists( $year, self::years() ) ) {
				$errors['start'] = __( 'Escolha o mês e o ano de entrada pretendidos.', 'babymoura-core' );
			} elseif ( (int) ( $year . $month ) < (int) wp_date( 'Ym' ) ) {
				$errors['start'] = __( 'A data de entrada não pode ser no passado.', 'babymoura-core' );
			}
			$clean['start_month'] = $month;
			$clean['start_year']  = $year;
		}

		$message = sanitize_textarea_field( $str( 'message' ) );
		if ( mb_strlen( $message ) > 1000 ) {
			$errors['message'] = __( 'A mensagem pode ter até 1000 caracteres.', 'babymoura-core' );
		}
		$clean['message'] = $message;

		return array( $clean, $errors );
	}

	/* ---------------------------------------------------------------------
	 * Processamento comum (REST e sem JavaScript)
	 * ------------------------------------------------------------------- */

	/**
	 * @param string $type Tipo de formulário.
	 * @param array  $raw  Dados recebidos.
	 * @return array{0: int, 1: array} Código HTTP e corpo da resposta.
	 */
	public static function process( string $type, array $raw ): array {
		if ( ! in_array( $type, self::TYPES, true ) ) {
			return array( 404, array( 'status' => 'error' ) );
		}

		// Honeypot: resposta indistinguível de sucesso, sem qualquer efeito.
		if ( ! empty( $raw['bm_website'] ) ) {
			return array( 200, self::body( bm_is_demo() ? 'demo' : 'ok', $type ) );
		}

		$token_state = self::check_token( isset( $raw['bm_token'] ) ? (string) $raw['bm_token'] : '' );
		if ( $token_state ) {
			$body = self::body( $token_state, $type );
			if ( 'expired' === $token_state ) {
				$body['token'] = self::token();
			}
			return array( 'too_fast' === $token_state ? 429 : 400, $body );
		}

		if ( self::rate_limited() ) {
			return array( 429, self::body( 'rate_limited', $type ) );
		}

		list( $clean, $errors ) = self::validate( $type, $raw );
		if ( $errors ) {
			$body           = self::body( 'invalid', $type );
			$body['errors'] = $errors;
			return array( 422, $body );
		}

		// Prevenção de duplicados: mesmo identificador de envio ou mesmo pedido em poucos minutos.
		$request_id = isset( $raw['bm_request_id'] ) ? preg_replace( '/[^a-zA-Z0-9\-]/', '', (string) $raw['bm_request_id'] ) : '';
		$keys       = array( 'bm_fp_' . self::hash( $type . '|' . strtolower( $clean['email'] ) . '|' . $clean['stage'] ) );
		if ( $request_id ) {
			$keys[] = 'bm_rq_' . self::hash( $request_id );
		}
		foreach ( $keys as $key ) {
			$previous = get_transient( $key );
			if ( $previous ) {
				return array( 200, self::body( 'demo' === $previous ? 'demo' : 'duplicate', $type ) );
			}
		}

		self::count_request();

		if ( bm_is_demo() ) {
			self::remember( $keys, 'demo' );
			return array( 200, self::body( 'demo', $type ) );
		}

		if ( ! self::recipient() ) {
			return array( 503, self::body( 'not_configured', $type ) );
		}

		$post_id = self::store( $type, $clean );
		if ( ! $post_id ) {
			return array( 500, self::body( 'error', $type ) );
		}
		self::remember( $keys, 'ok' );

		if ( ! self::notify( $post_id ) ) {
			wp_schedule_single_event( time() + 10 * MINUTE_IN_SECONDS, 'bm_retry_notification', array( $post_id ) );
		}

		return array( 200, self::body( 'ok', $type ) );
	}

	private static function body( string $status, string $type ): array {
		return array(
			'status'  => $status,
			'message' => self::message( $status, $type ),
		);
	}

	private static function hash( string $value ): string {
		return substr( hash_hmac( 'sha256', $value, wp_salt( 'auth' ) ), 0, 40 );
	}

	private static function remember( array $keys, string $value ): void {
		foreach ( $keys as $key ) {
			set_transient( $key, $value, self::DEDUPE_WINDOW );
		}
	}

	private static function client_ip(): string {
		return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
	}

	private static function rate_limited(): bool {
		$limit = (int) apply_filters( 'bm_form_rate_limit', self::RATE_LIMIT );
		$key   = 'bm_rl_' . self::hash( self::client_ip() );
		$count = (int) get_transient( $key );
		return $count >= $limit;
	}

	/**
	 * Só pedidos válidos contam para o limite, para não bloquear quem corrige erros.
	 */
	private static function count_request(): void {
		$key = 'bm_rl_' . self::hash( self::client_ip() );
		set_transient( $key, (int) get_transient( $key ) + 1, self::RATE_WINDOW );
	}

	/* ---------------------------------------------------------------------
	 * Entradas: REST (JavaScript) e admin-post (sem JavaScript)
	 * ------------------------------------------------------------------- */

	public static function routes(): void {
		register_rest_route(
			'babymoura/v1',
			'/forms/(?P<type>visita|pre-inscricao)',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => array( __CLASS__, 'rest_submit' ),
			)
		);
		register_rest_route(
			'babymoura/v1',
			'/forms/token',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => static fn() => new WP_REST_Response( array( 'token' => self::token() ), 200, array( 'Cache-Control' => 'no-store' ) ),
			)
		);
	}

	public static function rest_submit( WP_REST_Request $request ): WP_REST_Response {
		if ( strlen( (string) $request->get_body() ) > self::MAX_BODY ) {
			return new WP_REST_Response( self::body( 'invalid', (string) $request['type'] ), 413 );
		}
		$params = $request->get_json_params();
		if ( ! is_array( $params ) ) {
			$params = $request->get_body_params();
		}
		list( $code, $body ) = self::process( (string) $request['type'], is_array( $params ) ? $params : array() );
		return new WP_REST_Response( $body, $code, array( 'Cache-Control' => 'no-store' ) );
	}

	public static function handle_post(): void {
		$type    = isset( $_POST['form_type'] ) ? sanitize_key( wp_unslash( $_POST['form_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification -- protegido por token assinado.
		$length  = isset( $_SERVER['CONTENT_LENGTH'] ) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
		$status  = 'invalid';
		if ( $length <= self::MAX_BODY ) {
			list( , $body ) = self::process( $type, wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$status         = $body['status'];
		}
		$back = wp_get_referer();
		if ( ! $back || wp_parse_url( $back, PHP_URL_HOST ) !== wp_parse_url( home_url(), PHP_URL_HOST ) ) {
			$back = home_url( '/' );
		}
		$back = add_query_arg(
			array(
				'bm_form' => $status,
				'bm_type' => $type,
			),
			remove_query_arg( array( 'bm_form', 'bm_type' ), $back )
		);
		wp_safe_redirect( $back . '#' . ( 'visita' === $type ? 'pedido-de-visita' : 'formulario-pre-inscricao' ), 303 );
		exit;
	}

	/* ---------------------------------------------------------------------
	 * Armazenamento e notificação (apenas produção)
	 * ------------------------------------------------------------------- */

	private static function store( string $type, array $data ) {
		$label   = 'visita' === $type ? __( 'Visita', 'babymoura-core' ) : __( 'Pré-inscrição', 'babymoura-core' );
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'bm_pedido',
				'post_status' => 'private',
				'post_title'  => $label . ' — ' . $data['name'],
			),
			true
		);
		if ( is_wp_error( $post_id ) || ! $post_id ) {
			error_log( 'BabyMoura: não foi possível guardar um pedido (' . $type . ').' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions -- sem dados pessoais.
			return 0;
		}
		update_post_meta( $post_id, 'bm_type', $type );
		foreach ( $data as $key => $value ) {
			update_post_meta( $post_id, 'bm_f_' . $key, $value );
		}
		update_post_meta( $post_id, 'bm_mail', 'pendente' );
		update_post_meta( $post_id, 'bm_mail_attempts', 0 );
		return (int) $post_id;
	}

	public static function notify( int $post_id ): bool {
		$type = (string) get_post_meta( $post_id, 'bm_type', true );
		$to   = self::recipient();
		if ( ! $to || ! $type ) {
			update_post_meta( $post_id, 'bm_mail', 'falhou' );
			return false;
		}
		$f       = static fn( $k ) => (string) get_post_meta( $post_id, 'bm_f_' . $k, true );
		$stages  = self::stage_options( $type );
		$lines   = array(
			__( 'Nome', 'babymoura-core' )     => $f( 'name' ),
			__( 'Email', 'babymoura-core' )    => $f( 'email' ),
			__( 'Telefone', 'babymoura-core' ) => $f( 'phone' ) ?: '—',
			__( 'Resposta educativa', 'babymoura-core' ) => $stages[ $f( 'stage' ) ] ?? $f( 'stage' ),
		);
		if ( 'pre-inscricao' === $type ) {
			$bands = self::age_bands();
			$months = self::months();
			$lines[ __( 'Idade da criança', 'babymoura-core' ) ] = $bands[ $f( 'age_band' ) ] ?? '';
			$lines[ __( 'Entrada desejada', 'babymoura-core' ) ] = ( $months[ $f( 'start_month' ) ] ?? '' ) . ' ' . $f( 'start_year' );
		}
		$lines[ __( 'Mensagem', 'babymoura-core' ) ] = $f( 'message' ) ?: '—';

		$body = ( 'visita' === $type
			? __( 'Novo pedido de visita recebido no website.', 'babymoura-core' )
			: __( 'Nova pré-inscrição (manifestação de interesse) recebida no website.', 'babymoura-core' ) ) . "\n\n";
		foreach ( $lines as $label => $value ) {
			$body .= $label . ': ' . $value . "\n";
		}
		$body .= "\n" . __( 'Ver no painel:', 'babymoura-core' ) . ' ' . admin_url( 'post.php?post=' . $post_id . '&action=edit' ) . "\n";
		$body .= __( 'Responder a este email responde diretamente à família.', 'babymoura-core' ) . "\n";

		$subject = sprintf(
			'[BabyMoura] %1$s — %2$s',
			'visita' === $type ? __( 'Pedido de visita', 'babymoura-core' ) : __( 'Pré-inscrição', 'babymoura-core' ),
			$stages[ $f( 'stage' ) ] ?? ''
		);
		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
		if ( is_email( $f( 'email' ) ) ) {
			$headers[] = 'Reply-To: ' . str_replace( array( "\r", "\n", ',', ';', '<', '>' ), '', $f( 'name' ) ) . ' <' . $f( 'email' ) . '>';
		}

		$sent     = wp_mail( $to, $subject, $body, $headers );
		$attempts = (int) get_post_meta( $post_id, 'bm_mail_attempts', true ) + 1;
		update_post_meta( $post_id, 'bm_mail_attempts', $attempts );
		update_post_meta( $post_id, 'bm_mail', $sent ? 'enviado' : 'falhou' );
		if ( ! $sent ) {
			error_log( 'BabyMoura: notificação por email falhou para o pedido #' . $post_id . ' (tentativa ' . $attempts . ').' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions -- sem dados pessoais.
		}
		return $sent;
	}

	public static function retry_notification( $post_id ): void {
		$post_id = (int) $post_id;
		if ( 'bm_pedido' !== get_post_type( $post_id ) || 'enviado' === get_post_meta( $post_id, 'bm_mail', true ) ) {
			return;
		}
		if ( ! self::notify( $post_id ) && (int) get_post_meta( $post_id, 'bm_mail_attempts', true ) < self::MAIL_RETRIES ) {
			wp_schedule_single_event( time() + 30 * MINUTE_IN_SECONDS, 'bm_retry_notification', array( $post_id ) );
		}
	}

	public static function handle_resend(): void {
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		check_admin_referer( 'bm_resend_' . $post_id );
		if ( ! current_user_can( 'edit_post', $post_id ) || 'bm_pedido' !== get_post_type( $post_id ) ) {
			wp_die( esc_html__( 'Sem permissão.', 'babymoura-core' ), 403 );
		}
		self::notify( $post_id );
		wp_safe_redirect( admin_url( 'post.php?post=' . $post_id . '&action=edit' ) );
		exit;
	}

	/**
	 * SMTP configurável no servidor. Sem segredos no frontend nem na base de dados.
	 *
	 * @param PHPMailer\PHPMailer\PHPMailer $mailer Instância do PHPMailer.
	 */
	public static function smtp( $mailer ): void {
		if ( defined( 'BABYMOURA_SMTP_HOST' ) && BABYMOURA_SMTP_HOST ) {
			$mailer->isSMTP();
			$mailer->Host = BABYMOURA_SMTP_HOST; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
			$mailer->Port = defined( 'BABYMOURA_SMTP_PORT' ) ? (int) BABYMOURA_SMTP_PORT : 25; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
			if ( defined( 'BABYMOURA_SMTP_USER' ) && BABYMOURA_SMTP_USER ) {
				$mailer->SMTPAuth = true; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				$mailer->Username = BABYMOURA_SMTP_USER; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				$mailer->Password = defined( 'BABYMOURA_SMTP_PASS' ) ? BABYMOURA_SMTP_PASS : ''; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
			}
			if ( defined( 'BABYMOURA_SMTP_SECURE' ) && in_array( BABYMOURA_SMTP_SECURE, array( 'tls', 'ssl' ), true ) ) {
				$mailer->SMTPSecure = BABYMOURA_SMTP_SECURE; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
			} else {
				$mailer->SMTPAutoTLS = false; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
			}
		}
	}

	/**
	 * Remetente técnico definido no servidor (deve pertencer a um domínio com SPF/DKIM).
	 *
	 * @param string $from Remetente atual.
	 */
	public static function mail_from( $from ) {
		return ( defined( 'BABYMOURA_FORM_FROM' ) && is_email( BABYMOURA_FORM_FROM ) ) ? BABYMOURA_FORM_FROM : $from;
	}

	/**
	 * Conservação limitada: apaga pedidos mais antigos do que o prazo definido.
	 */
	public static function purge(): void {
		$days = max( 30, (int) bm_setting( 'retention_days', 180 ) );
		$old  = get_posts(
			array(
				'post_type'      => 'bm_pedido',
				'post_status'    => 'any',
				'fields'         => 'ids',
				'posts_per_page' => 200,
				'date_query'     => array( array( 'before' => $days . ' days ago' ) ),
			)
		);
		foreach ( $old as $id ) {
			wp_delete_post( $id, true );
		}
	}

	/* ---------------------------------------------------------------------
	 * Administração dos pedidos
	 * ------------------------------------------------------------------- */

	public static function render_request_box( WP_Post $post ): void {
		$type   = (string) get_post_meta( $post->ID, 'bm_type', true );
		$f      = static fn( $k ) => (string) get_post_meta( $post->ID, 'bm_f_' . $k, true );
		$stages = self::stage_options( $type ?: 'visita' );
		$rows   = array(
			__( 'Tipo', 'babymoura-core' )     => 'visita' === $type ? __( 'Pedido de visita', 'babymoura-core' ) : __( 'Pré-inscrição (manifestação de interesse)', 'babymoura-core' ),
			__( 'Recebido em', 'babymoura-core' ) => get_the_date( 'j M Y, H:i', $post ),
			__( 'Nome', 'babymoura-core' )     => $f( 'name' ),
			__( 'Email', 'babymoura-core' )    => $f( 'email' ),
			__( 'Telefone', 'babymoura-core' ) => $f( 'phone' ) ?: '—',
			__( 'Resposta educativa', 'babymoura-core' ) => $stages[ $f( 'stage' ) ] ?? '—',
		);
		if ( 'pre-inscricao' === $type ) {
			$rows[ __( 'Idade da criança', 'babymoura-core' ) ] = self::age_bands()[ $f( 'age_band' ) ] ?? '—';
			$rows[ __( 'Entrada desejada', 'babymoura-core' ) ] = ( self::months()[ $f( 'start_month' ) ] ?? '' ) . ' ' . $f( 'start_year' );
		}
		$rows[ __( 'Mensagem', 'babymoura-core' ) ]           = $f( 'message' ) ?: '—';
		$rows[ __( 'Notificação por email', 'babymoura-core' ) ] = (string) get_post_meta( $post->ID, 'bm_mail', true );

		echo '<table class="widefat striped"><tbody>';
		foreach ( $rows as $label => $value ) {
			printf( '<tr><th scope="row" style="width:12em">%1$s</th><td>%2$s</td></tr>', esc_html( $label ), nl2br( esc_html( $value ) ) );
		}
		echo '</tbody></table>';
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=bm_resend&post=' . $post->ID ), 'bm_resend_' . $post->ID );
		printf( '<p><a class="button" href="%1$s">%2$s</a></p>', esc_url( $url ), esc_html__( 'Reenviar notificação', 'babymoura-core' ) );
		printf(
			'<p class="description">%s</p>',
			esc_html(
				sprintf(
					/* translators: %d: número de dias */
					__( 'Os pedidos são apagados automaticamente ao fim de %d dias.', 'babymoura-core' ),
					(int) bm_setting( 'retention_days', 180 )
				)
			)
		);
	}

	public static function columns( array $cols ): array {
		return array(
			'cb'       => $cols['cb'] ?? '',
			'title'    => __( 'Pedido', 'babymoura-core' ),
			'bm_stage' => __( 'Resposta educativa', 'babymoura-core' ),
			'bm_mail'  => __( 'Notificação', 'babymoura-core' ),
			'date'     => __( 'Data', 'babymoura-core' ),
		);
	}

	public static function column( string $col, int $post_id ): void {
		if ( 'bm_stage' === $col ) {
			$type = (string) get_post_meta( $post_id, 'bm_type', true );
			echo esc_html( self::stage_options( $type ?: 'visita' )[ get_post_meta( $post_id, 'bm_f_stage', true ) ] ?? '—' );
		}
		if ( 'bm_mail' === $col ) {
			echo esc_html( (string) get_post_meta( $post_id, 'bm_mail', true ) );
		}
	}
}

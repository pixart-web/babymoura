<?php
/**
 * Formulário de pedido de visita ou pré-inscrição.
 * Recolha mínima: nunca pede nome completo da criança, data de nascimento exata, documentos ou dados de saúde.
 * Funciona sem JavaScript (admin-post) e com JavaScript (REST, com estados e prevenção de duplicados).
 *
 * @package BabyMoura
 * @var array $attributes
 */

defined( 'ABSPATH' ) || exit;

$bm_type   = 'pre-inscricao' === ( $attributes['formType'] ?? '' ) ? 'pre-inscricao' : 'visita';
$bm_uid    = wp_unique_id( 'bm-form-' );
$bm_demo   = bm_is_demo();
$bm_stage  = sanitize_key( $attributes['stage'] ?? '' );
$bm_status = isset( $_GET['bm_form'] ) ? sanitize_key( wp_unslash( $_GET['bm_form'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$bm_ftype  = isset( $_GET['bm_type'] ) ? sanitize_key( wp_unslash( $_GET['bm_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

$bm_field = static function ( string $name, string $label, string $control, bool $required = false, string $hint = '' ) use ( $bm_uid ) {
	$id   = $bm_uid . '-' . $name;
	$desc = array();
	if ( $hint ) {
		$desc[] = $id . '-hint';
	}
	$desc[] = $id . '-error';
	$control = str_replace(
		array( '{id}', '{name}', '{describedby}', '{required}' ),
		array( esc_attr( $id ), esc_attr( $name ), esc_attr( implode( ' ', $desc ) ), $required ? 'required aria-required="true"' : '' ),
		$control
	);
	?>
	<div class="bm-field" data-field="<?php echo esc_attr( $name ); ?>">
		<label for="<?php echo esc_attr( $id ); ?>">
			<?php echo esc_html( $label ); ?>
			<?php if ( $required ) : ?>
				<span class="bm-field__req" aria-hidden="true">*</span>
			<?php else : ?>
				<span class="bm-field__opt"><?php esc_html_e( '(opcional)', 'babymoura-core' ); ?></span>
			<?php endif; ?>
		</label>
		<?php if ( $hint ) : ?>
			<p class="bm-field__hint" id="<?php echo esc_attr( $id . '-hint' ); ?>"><?php echo esc_html( $hint ); ?></p>
		<?php endif; ?>
		<?php echo $control; // phpcs:ignore WordPress.Security.EscapeOutput -- controlo construído abaixo com valores escapados. ?>
		<p class="bm-field__error" id="<?php echo esc_attr( $id . '-error' ); ?>" hidden></p>
	</div>
	<?php
};

$bm_options = static function ( array $options, string $selected = '', string $placeholder = '' ): string {
	$html = '' !== $placeholder ? '<option value="">' . esc_html( $placeholder ) . '</option>' : '';
	foreach ( $options as $value => $label ) {
		$html .= '<option value="' . esc_attr( $value ) . '"' . selected( $selected, (string) $value, false ) . '>' . esc_html( $label ) . '</option>';
	}
	return $html;
};

$bm_stage_opts = BM_Forms::stage_options( $bm_type );
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'bm-form-wrap bm-form-wrap--' . $bm_type ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<form class="bm-form" id="<?php echo esc_attr( $bm_uid ); ?>" data-bm-form="<?php echo esc_attr( $bm_type ); ?>" data-demo="<?php echo $bm_demo ? '1' : '0'; ?>" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" novalidate>

		<?php if ( $bm_demo ) : ?>
			<p class="bm-form__demo" role="note">
				<?php echo bm_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span><strong><?php esc_html_e( 'Demonstração — os dados não serão enviados.', 'babymoura-core' ); ?></strong> <?php esc_html_e( 'Pode experimentar o formulário: validamos os campos, mas nada é guardado nem chega à instituição.', 'babymoura-core' ); ?></span>
			</p>
		<?php endif; ?>

		<div class="bm-form__status" role="status" aria-live="polite" tabindex="-1" <?php echo ( $bm_status && $bm_ftype === $bm_type ) ? '' : 'hidden'; ?> data-state="<?php echo esc_attr( $bm_ftype === $bm_type ? $bm_status : '' ); ?>">
			<?php
			if ( $bm_status && $bm_ftype === $bm_type ) {
				echo esc_html( BM_Forms::message( $bm_status, $bm_type ) );
			}
			?>
		</div>

		<div class="bm-form__summary" role="alert" tabindex="-1" hidden>
			<p><strong><?php esc_html_e( 'Há campos por corrigir:', 'babymoura-core' ); ?></strong></p>
			<ul></ul>
		</div>

		<input type="hidden" name="action" value="bm_form">
		<input type="hidden" name="form_type" value="<?php echo esc_attr( $bm_type ); ?>">
		<input type="hidden" name="bm_token" value="<?php echo esc_attr( BM_Forms::token() ); ?>">
		<input type="hidden" name="bm_request_id" value="">
		<div class="bm-hp" aria-hidden="true">
			<label for="<?php echo esc_attr( $bm_uid ); ?>-website"><?php esc_html_e( 'Não preencher este campo', 'babymoura-core' ); ?></label>
			<input type="text" id="<?php echo esc_attr( $bm_uid ); ?>-website" name="bm_website" tabindex="-1" autocomplete="off">
		</div>

		<fieldset class="bm-form__group">
			<legend><?php esc_html_e( 'Os seus contactos', 'babymoura-core' ); ?></legend>
			<div class="bm-form__row">
				<?php
				$bm_field( 'name', __( 'Nome do adulto responsável', 'babymoura-core' ), '<input type="text" id="{id}" name="{name}" autocomplete="name" maxlength="100" aria-describedby="{describedby}" {required}>', true );
				$bm_field( 'email', __( 'Email', 'babymoura-core' ), '<input type="email" id="{id}" name="{name}" autocomplete="email" inputmode="email" maxlength="150" aria-describedby="{describedby}" {required}>', true );
				?>
			</div>
			<?php
			$bm_field( 'phone', __( 'Telefone', 'babymoura-core' ), '<input type="tel" id="{id}" name="{name}" autocomplete="tel" inputmode="tel" maxlength="20" aria-describedby="{describedby}" {required}>', false, __( 'Se preferir que a equipa ligue.', 'babymoura-core' ) );
			?>
		</fieldset>

		<fieldset class="bm-form__group">
			<legend><?php echo esc_html( 'visita' === $bm_type ? __( 'Sobre a visita', 'babymoura-core' ) : __( 'Sobre a criança', 'babymoura-core' ) ); ?></legend>
			<?php
			$bm_field(
				'stage',
				__( 'Resposta educativa pretendida', 'babymoura-core' ),
				'<select id="{id}" name="{name}" aria-describedby="{describedby}" {required}>' . $bm_options( $bm_stage_opts, $bm_stage, __( 'Escolha uma opção', 'babymoura-core' ) ) . '</select>',
				true
			);
			if ( 'pre-inscricao' === $bm_type ) :
				?>
				<div class="bm-form__row">
					<?php
					$bm_field(
						'age_band',
						__( 'Idade da criança', 'babymoura-core' ),
						'<select id="{id}" name="{name}" aria-describedby="{describedby}" {required}>' . $bm_options( BM_Forms::age_bands(), '', __( 'Escolha uma faixa', 'babymoura-core' ) ) . '</select>',
						true,
						__( 'Uma faixa aproximada chega. Não pedimos a data de nascimento.', 'babymoura-core' )
					);
					?>
					<div class="bm-field bm-field--split" data-field="start">
						<span class="bm-field__legend" id="<?php echo esc_attr( $bm_uid ); ?>-start-label"><?php esc_html_e( 'Entrada desejada', 'babymoura-core' ); ?> <span class="bm-field__req" aria-hidden="true">*</span></span>
						<p class="bm-field__hint" id="<?php echo esc_attr( $bm_uid ); ?>-start-hint"><?php esc_html_e( 'Mês e ano aproximados.', 'babymoura-core' ); ?></p>
						<div class="bm-field__pair">
							<label class="screen-reader-text" for="<?php echo esc_attr( $bm_uid ); ?>-start_month"><?php esc_html_e( 'Mês de entrada', 'babymoura-core' ); ?></label>
							<select id="<?php echo esc_attr( $bm_uid ); ?>-start_month" name="start_month" required aria-required="true" aria-describedby="<?php echo esc_attr( $bm_uid ); ?>-start-hint <?php echo esc_attr( $bm_uid ); ?>-start-error"><?php echo $bm_options( BM_Forms::months(), '', __( 'Mês', 'babymoura-core' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></select>
							<label class="screen-reader-text" for="<?php echo esc_attr( $bm_uid ); ?>-start_year"><?php esc_html_e( 'Ano de entrada', 'babymoura-core' ); ?></label>
							<select id="<?php echo esc_attr( $bm_uid ); ?>-start_year" name="start_year" required aria-required="true" aria-describedby="<?php echo esc_attr( $bm_uid ); ?>-start-hint <?php echo esc_attr( $bm_uid ); ?>-start-error"><?php echo $bm_options( BM_Forms::years(), '', __( 'Ano', 'babymoura-core' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></select>
						</div>
						<p class="bm-field__error" id="<?php echo esc_attr( $bm_uid ); ?>-start-error" hidden></p>
					</div>
				</div>
			<?php endif; ?>
			<?php
			$bm_field(
				'message',
				'visita' === $bm_type ? __( 'Mensagem', 'babymoura-core' ) : __( 'Algo que queira partilhar', 'babymoura-core' ),
				'<textarea id="{id}" name="{name}" rows="4" maxlength="1000" aria-describedby="{describedby}" {required}></textarea>',
				false,
				'visita' === $bm_type
					? __( 'Disponibilidade preferida ou dúvidas. Até 1000 caracteres.', 'babymoura-core' )
					: __( 'Por favor, não inclua dados de saúde nem documentos. Até 1000 caracteres.', 'babymoura-core' )
			);
			?>
		</fieldset>

		<?php if ( 'pre-inscricao' === $bm_type ) : ?>
			<p class="bm-form__notice">
				<?php echo bm_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span><?php esc_html_e( 'O envio constitui uma manifestação de interesse e não confirma vaga nem inscrição. A equipa entra em contacto para explicar os passos seguintes.', 'babymoura-core' ); ?></span>
			</p>
		<?php endif; ?>

		<p class="bm-form__privacy">
			<?php
			printf(
				/* translators: %s: ligação para a política de privacidade */
				esc_html__( 'Usamos estes dados apenas para responder ao seu pedido. Saiba como são tratados e conservados na %s.', 'babymoura-core' ),
				'<a href="' . esc_url( bm_page_url( 'privacidade' ) ) . '">' . esc_html__( 'Política de Privacidade', 'babymoura-core' ) . '</a>'
			);
			?>
		</p>

		<div class="bm-form__actions">
			<button type="submit" class="bm-button bm-button--primary">
				<span class="bm-button__label"><?php echo esc_html( 'visita' === $bm_type ? __( 'Enviar pedido de visita', 'babymoura-core' ) : __( 'Enviar pré-inscrição', 'babymoura-core' ) ); ?></span>
				<span class="bm-button__busy" hidden><?php esc_html_e( 'A enviar…', 'babymoura-core' ); ?></span>
			</button>
			<p class="bm-form__required-note"><span aria-hidden="true">*</span> <?php esc_html_e( 'Campos obrigatórios', 'babymoura-core' ); ?></p>
		</div>
	</form>
</div>

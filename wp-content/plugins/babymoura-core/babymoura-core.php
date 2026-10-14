<?php
/**
 * Plugin Name:       BabyMoura Core
 * Description:       Dados centrais, atividades, FAQ, galeria, formulários de visita e pré-inscrição, SEO e redirecionamentos do website BabyMoura. Mantém comportamento fora do tema.
 * Version:           0.9.0
 * Requires at least: 6.6
 * Requires PHP:      8.1
 * Author:            Proposta BabyMoura
 * Text Domain:       babymoura-core
 * Domain Path:       /languages
 * License:           GPL-2.0-or-later
 *
 * @package BabyMoura
 */

defined( 'ABSPATH' ) || exit;

define( 'BM_CORE_VERSION', '0.9.0' );
define( 'BM_CORE_FILE', __FILE__ );
define( 'BM_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'BM_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once BM_CORE_DIR . 'includes/helpers.php';
require_once BM_CORE_DIR . 'includes/class-settings.php';
require_once BM_CORE_DIR . 'includes/class-content-types.php';
require_once BM_CORE_DIR . 'includes/class-media-rights.php';
require_once BM_CORE_DIR . 'includes/class-blocks.php';
require_once BM_CORE_DIR . 'includes/class-forms.php';
require_once BM_CORE_DIR . 'includes/class-seo.php';
require_once BM_CORE_DIR . 'includes/class-redirects.php';
require_once BM_CORE_DIR . 'includes/class-checklist.php';

BM_Settings::init();
BM_Content_Types::init();
BM_Media_Rights::init();
BM_Blocks::init();
BM_Forms::init();
BM_SEO::init();
BM_Redirects::init();
BM_Checklist::init();

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once BM_CORE_DIR . 'includes/class-cli.php';
	WP_CLI::add_command( 'babymoura', 'BM_CLI' );
}

add_action(
	'init',
	static function () {
		load_plugin_textdomain( 'babymoura-core', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
);

register_activation_hook(
	__FILE__,
	static function () {
		BM_Content_Types::register();
		flush_rewrite_rules();
		BM_Forms::schedule_events();
	}
);

register_deactivation_hook(
	__FILE__,
	static function () {
		wp_clear_scheduled_hook( 'bm_purge_requests' );
		flush_rewrite_rules();
	}
);

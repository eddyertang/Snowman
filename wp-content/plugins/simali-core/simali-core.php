<?php
/**
 * Plugin Name: Simali Core
 * Description: Calculator, sample basket, quotes, postcode check and schema for Simali Stone.
 * Version: 1.0.0
 * Requires PHP: 8.1
 * Text Domain: simali
 *
 * @package Simali
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SIMALI_CORE_FILE', __FILE__ );
define( 'SIMALI_CORE_DIR', plugin_dir_path( __FILE__ ) );

require_once SIMALI_CORE_DIR . 'includes/class-simali-calculator.php';
require_once SIMALI_CORE_DIR . 'includes/class-simali-postcode.php';
require_once SIMALI_CORE_DIR . 'includes/class-simali-content.php';
require_once SIMALI_CORE_DIR . 'includes/class-simali-samples.php';
require_once SIMALI_CORE_DIR . 'includes/class-simali-forms.php';
require_once SIMALI_CORE_DIR . 'includes/class-simali-catalog.php';
require_once SIMALI_CORE_DIR . 'includes/class-simali-setup.php';

add_action( 'plugins_loaded', array( 'Simali_Setup', 'init' ) );
register_activation_hook( __FILE__, array( 'Simali_Setup', 'activate' ) );

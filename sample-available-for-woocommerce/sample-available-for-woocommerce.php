<?php
/**
 * Plugin Name: Sample Available for WooCommerce
 * Plugin URI: https://example.com/sample-available-for-woocommerce
 * Description: Adds a Sample Available product setting, a frontend Request a Sample button, and an Elementor widget with style controls.
 * Version: 1.0.13
 * Author: Codex
 * Author URI: https://example.com
 * Text Domain: sample-available-for-woocommerce
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 * WC tested up to: 9.9
 * Elementor tested up to: 3.28
 *
 * @package SampleAvailableForWooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SAW_VERSION', '1.0.13' );
define( 'SAW_PLUGIN_FILE', __FILE__ );
define( 'SAW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SAW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once SAW_PLUGIN_DIR . 'includes/class-saw-plugin.php';

register_activation_hook( __FILE__, array( 'SAW_Plugin', 'activate' ) );

add_action( 'before_woocommerce_init', array( 'SAW_Plugin', 'declare_hpos_compatibility' ) );
add_action( 'plugins_loaded', array( 'SAW_Plugin', 'instance' ) );

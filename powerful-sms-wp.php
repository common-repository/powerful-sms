<?php

/**
 *
 * @link              http://felipepeixoto.tecnologia.ws/
 * @since             1.0.0
 * @package           Powerful_Sms_Wp
 *
 * @wordpress-plugin
 * Plugin Name:       Powerful SMS
 * Plugin URI:        https://wordpress.org/plugins/powerful-sms-wp/
 * Description:       Plugin para enviar notificação por SMS após fazer pedidos usando WooCommerce e outras integrações do Wordpress
 * Version:           1.0.0
 * Author:            Felipe Peixoto
 * Author URI:        http://felipepeixoto.tecnologia.ws/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       powerful-sms-wp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'POWERFUL_SMS_WP_VERSION', '1.0.0' );
define( 'POWERFUL_SMS_WP_PATH', plugin_dir_path(__FILE__) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-powerful-sms-wp-activator.php
 */
function activate_powerful_sms_wp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-powerful-sms-wp-activator.php';
	Powerful_Sms_Wp_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-powerful-sms-wp-deactivator.php
 */
function deactivate_powerful_sms_wp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-powerful-sms-wp-deactivator.php';
	Powerful_Sms_Wp_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_powerful_sms_wp' );
register_deactivation_hook( __FILE__, 'deactivate_powerful_sms_wp' );

function psms_is_plugin_active( $plugin ) {
    return in_array( $plugin, (array) get_option( 'active_plugins', array() ) );
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-powerful-sms-wp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_powerful_sms_wp() {

	$plugin = new Powerful_Sms_Wp();
	$plugin->run();

}
run_powerful_sms_wp();

<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              onemotion.ca
 * @since             1.0.0
 * @package           Woo_Mcp
 *
 * @wordpress-plugin
 * Plugin Name:       Master Catalog Plugin
 * Plugin URI:        onemotion.ca
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.1.0
 * Author:            One Motion Technologies Inc.
 * Author URI:        onemotion.ca
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-mcp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-mcp-activator.php
 */
function activate_woo_mcp() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-mcp-base.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-mcp-activator.php';
	Woo_Mcp_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woo-mcp-deactivator.php
 */
function deactivate_woo_mcp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-mcp-deactivator.php';
	Woo_Mcp_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woo_mcp' );
register_deactivation_hook( __FILE__, 'deactivate_woo_mcp' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woo-mcp-base.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-woo-mcp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woo_mcp() {

	$plugin = new Woo_Mcp();
	$plugin->run();

}
run_woo_mcp();

<?php

/**
 * Fired during plugin activation
 *
 * @link       http://cto2go.ca/
 * @since      1.0.0
 *
 * @package    Woo_Mcp
 * @subpackage Woo_Mcp/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Woo_Mcp
 * @subpackage Woo_Mcp/includes
 * @author     cto2go <INFO@CTO2GO.CA>
 */
class Woo_Mcp_Activator extends Woo_Mcp_base{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */


	public static function activate() {
        $parent = new Woo_Mcp_base();

        if ( !is_multisite() )
            wp_die("This plugin requires a Wordpress Network installation.");

        if (is_multisite() && !is_network_admin()) {
            wp_die('In Network install, the plugin must be activated from Network Admin Screen');
        }

        if ( !class_exists( 'WooCommerce' ) ) {
            wp_die('This plugin requires a woocommerce installation');
        }

        add_site_option(WOOMCP_PLUGIN_NAME."_activated", 1);
        add_site_option( WOOMCP_PLUGIN_NAME."_version", $parent->version);

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $sql2= "DROP TABLE IF EXISTS `". parent::createTableName('mcp_product_to_sites')."`;
                CREATE TABLE IF NOT EXISTS `". parent::createTableName('mcp_product_to_sites') . "` (
                `id`  int NOT NULL AUTO_INCREMENT,
                `product_id_main`  int NOT NULL ,
                `network_id`  int NOT NULL ,
                `network_name`  varchar(255) NULL ,
                `product_id_slave`  int NOT NULL ,
                `pushed_modified`  timestamp NULL ON UPDATE CURRENT_TIMESTAMP ,
                `pushed_date`  datetime NOT NULL ,
                `data`  longtext NULL,
                `history`  longtext NULL,
                `status_on_slave`  tinyint
                PRIMARY KEY (`id`))";
        dbDelta($sql2);

        $installed_ver = get_site_option(  WOOMCP_PLUGIN_NAME."_version");

        if ( $installed_ver != $parent->version) {

            $sql2= "DROP TABLE IF EXISTS `". parent::createTableName('mcp_product_to_sites')."`;
                CREATE TABLE `". parent::createTableName('mcp_product_to_sites') . "` (
                `id`  int NOT NULL AUTO_INCREMENT,
                `product_id_main`  int NOT NULL ,
                `network_id`  int NOT NULL ,
                `network_name`  varchar(255) NULL ,
                `product_id_slave`  int NOT NULL ,
                `pushed_modified`  timestamp NULL ON UPDATE CURRENT_TIMESTAMP ,
                `pushed_date`  datetime NOT NULL ,
                `data`  longtext NULL,
                `history`  longtext NULL,
                `status_on_slave`  tinyint
                PRIMARY KEY (`id`))";
            dbDelta($sql2);
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql2 );

            update_site_option( WOOMCP_PLUGIN_NAME."_version", $parent->version);
        }
	}

    function on_activation_note() {
        global $pagenow;
        global $wpdb;
        $table_name = self::createTableName('mcp_product_to_sites');
        //check if data exists
        if ( $pagenow == 'plugins.php' && $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
            $results = $wpdb->get_results('SELECT count(*) as data_count FROM wp_mcp_product_to_sites');
            if((int)$results[0]->data_count > 0){
            ob_start(); ?>
            <div id="message" class="error woo-mcp-error">
                <p><strong>Confirmation</strong></br>
                    Data already exists in the database. Do you want to remove it? If yes, all existing data will be lost.
                </p>
                <p><span><a class="button woo-mcp-cancel" href="#">Cancel</a></span>
                    <span><a class="button woo-mcp-continue" href="#">Continue</a></span><span class="spinner" style="vertical-align: middle;float:none;"></span></p>
            </div>
            <?php
            echo ob_get_clean();
            }
        }

    }

    function load_mcp_plugin() {
        if (  is_network_admin() &&  get_site_option( WOOMCP_PLUGIN_NAME."_activated" ) == '1' ) {
            delete_site_option( WOOMCP_PLUGIN_NAME."_activated" );
            add_action( 'network_admin_notices',array( new self(), 'on_activation_note' ) );
        }
    }

}

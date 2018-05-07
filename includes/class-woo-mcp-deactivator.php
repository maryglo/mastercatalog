<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://cto2go.ca/
 * @since      1.0.0
 *
 * @package    Woo_Mcp
 * @subpackage Woo_Mcp/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Woo_Mcp
 * @subpackage Woo_Mcp/includes
 * @author     cto2go <INFO@CTO2GO.CA>
 */
class Woo_Mcp_Deactivator extends Woo_Mcp_base {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
    public static function deactivate() {
        global $wpdb;

        if ( is_multisite()){
            foreach(self::site_options() as $key=>$value) {
                delete_site_option(WOOMCP_PLUGIN_NAME."_".$key, $value);
            }
        }

        //$sql = "DROP TABLE IF EXISTS ".self::createTableName('mcp_product_to_sites');
        //$wpdb->query($sql);

    }

}

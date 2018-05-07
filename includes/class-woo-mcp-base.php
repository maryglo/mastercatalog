<?php
class Woo_Mcp_base {


    /**
     * The list of options .
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $settings    The list of options.
     */
    protected  $settings;

    /**
     * Current blog id.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $curr_blog_id Current blog id.
     */
    protected  $curr_blog_id;

    public $version;


    public function __construct() {
          $this->settings = array();
          $this->version = '1.1.0';

    }
    /**
     * Retrieve the list of sites insttalled on the network.
     *
     * @since     1.0.0
     * @return    array    list of sites insttalled on the network.
     */
    public function list_sites() {
        $sites = wp_get_sites(array(
            'spam'       => 0,
            'deleted'    => 0
        ));
        $arr = array();
        foreach($sites as $site) {
            $site['name'] = get_blog_option( $site['blog_id'], 'blogname' );
            $arr[] = $site;
        }

        return $arr;
    }


    /**
    @brief		Return an array of the site options.
     **/
    public function site_options() {
        return array(
            'multiplier' =>[1],
            'master' => 1
        );
    }

    public function createTableName($name) {
        global $wpdb;
        return $wpdb->base_prefix.$name;
    }

    public function woo_mcp_table() {
        global $wpdb;
        return $wpdb->base_prefix.'mcp_product_to_sites';
    }

}
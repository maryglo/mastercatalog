<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://cto2go.ca/
 * @since      1.0.0
 *
 * @package    Woo_Mcp
 * @subpackage Woo_Mcp/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woo_Mcp
 * @subpackage Woo_Mcp/includes
 * @author     cto2go <INFO@CTO2GO.CA>
 */
class Woo_Mcp extends Woo_Mcp_base{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Woo_Mcp_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;


    /**
     * list of all the sites installed on the network
     *
     * @since 1.0.1
     * @access protected
     * @var array $sites list of all the sites installed on the network
     */
    protected $sites;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {

        $this->plugin_name = 'woo-mcp';
        $this->version = '1.1.0';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        // Define constants
        $this->define_contants();

    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Woo_Mcp_Loader. Orchestrates the hooks of the plugin.
     * - Woo_Mcp_i18n. Defines internationalization functionality.
     * - Woo_Mcp_Admin. Defines all hooks for the admin area.
     * - Woo_Mcp_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-mcp-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-mcp-i18n.php';

        /**
         * The class responsible for getting data for admin use.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-mcp-base.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woo-mcp-admin.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-mcp-multiquery.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/woo-mcp-functions.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-mcp-product-site.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-mcp-product.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-mcp-activator.php';
        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woo-mcp-public.php';

        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        $this->loader = new Woo_Mcp_Loader();

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Woo_Mcp_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new Woo_Mcp_i18n();
        $plugin_i18n->set_domain( $this->get_plugin_name() );

        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new Woo_Mcp_Admin( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action('wp_ajax_push_products',$plugin_admin, 'woo_mcp_process_ajax');
        $this->loader->add_action('wp_ajax_push_french',$plugin_admin, 'processFrench');
        $this->loader->add_action('wp_ajax_deactivate_products',$plugin_admin, 'woo_mcp_deactivate_products');
        $this->loader->add_action('wp_ajax_get_content',$plugin_admin, 'woo_mcp_get_content');
        $this->loader->add_action('wp_ajax_get_prod_content',$plugin_admin, 'woo_mcp_get_prod_content');
        $this->loader->add_action('wp_ajax_update_table',$plugin_admin, 'woo_mcp_update_table');
        $this->loader->add_action('wp_ajax_insert_category',$plugin_admin, 'woo_mcp_insert_category');
        $this->loader->add_action('wp_ajax_update_prod_cat',$plugin_admin, 'woo_mcp_update_prod_cat');
        $this->loader->add_action('wp_ajax_get_sale_schedule_form', $plugin_admin, 'woo_mcp_sale_form');
        $this->loader->add_action('wp_ajax_get_variation_prices', $plugin_admin, 'woo_mcp_variation_prices');

        if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            //plugin is activated
            $this->loader->add_action('woocommerce_product_query',$plugin_admin, 'products_filter' );
        }


        $this->loader->add_action('add_meta_boxes', $plugin_admin, 'woo_mcp_meta_box');

        if ( is_multisite() && is_network_admin() ){
            $this->loader->add_action( 'network_admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
            $this->loader->add_action( 'admin_init',new Woo_Mcp_Activator() ,'load_mcp_plugin' );
            $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'woo_mcp_network_js' );
        }

        if ( is_multisite()){
            //register site options
            foreach($this->site_options() as $key=>$value) {
                add_site_option($this->plugin_name."_".$key, $value);
            }

            $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_master_menu' );
            $this->loader->add_action('save_post', $plugin_admin, 'sync_inventory_to_slave', 10,3);
            //$this->loader->add_action( 'woocommerce_product_options_pricing',$plugin_admin, 'wc_price_product_field');
            //$this->loader->add_filter( 'woocommerce_get_price',$plugin_admin, 'woomcp_get_price', 10, 2);
            //$this->loader->add_filter('woocommerce_variable_price_html', $plugin_admin, 'custom_variation_price', 10, 2);
            //$this->loader->add_action( 'woocommerce_product_after_variable_attributes',$plugin_admin, 'wc_var_price_field', 10, 3 );
            $this->loader->add_action( 'woocommerce_process_product_meta_simple',$plugin_admin, 'wc_save_prod_simple', 10, 1 );
            $this->loader->add_action( 'woocommerce_save_product_variation', $plugin_admin, 'wc_var_save_price_field', 10, 2 );
        }

    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {

        $plugin_public = new Woo_Mcp_Public( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        if(is_multisite()){
            $this->loader->add_filter( 'woocommerce_payment_complete',$plugin_public, 'update_inventory');
        }

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Woo_Mcp_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Define woomcp contants
     */
    public function define_contants(){
        define( 'WOOMCP_VERSION', $this->version );
        define( 'WOOMCP_PLUGIN_NAME', $this->plugin_name );
    }
}

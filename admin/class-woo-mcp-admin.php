<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://cto2go.ca/
 * @since      1.0.0
 *
 * @package    Woo_Mcp
 * @subpackage Woo_Mcp/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woo_Mcp
 * @subpackage Woo_Mcp/admin
 * @author     cto2go <INFO@CTO2GO.CA>
 */
class Woo_Mcp_Admin extends Woo_Mcp_base  {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    public $version;

    /**
     * The list of products.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $products    The list of products.
     */
    private $products;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->curr_blog_id = get_current_blog_id();
        //$this->products = $this->getProducts();
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Woo_Mcp_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Woo_Mcp_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        global $wp_scripts;


        // get the jquery ui object
        $queryui = $wp_scripts->query('jquery-ui-core');

        // load the jquery ui theme
        $url = "http://ajax.googleapis.com/ajax/libs/jqueryui/".$queryui->ver."/themes/smoothness/jquery-ui.css";
        wp_enqueue_style('jquery-ui-smoothness', $url, false, null);
        wp_enqueue_style( 'multiselect', plugin_dir_url( __FILE__ ) . 'css/jquery.multiselect.css', array(), $this->version, 'all' );

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-mcp-admin.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts($hook) {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Woo_Mcp_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Woo_Mcp_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script( 'jquery-ui-dialog' );
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script('jquery-ui-accordion');
        wp_enqueue_script( $this->plugin_name.'-multiselect', plugin_dir_url( __FILE__ ) . 'js/jquery.multiselect.js', array( 'jquery','jquery-ui-core','jquery-ui-widget'), $this->version, false );
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-mcp-admin.js', array( 'jquery', 'wp-ajax-response' ), $this->version, false );

        wp_localize_script( $this->plugin_name,'ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' )
        ));
    }

    public function woo_mcp_network_js() {
        wp_enqueue_script( $this->plugin_name.'-network-js', plugin_dir_url( __FILE__ ) . 'js/woo-mcp-network.js', array( 'jquery' ), $this->version, false );
        wp_localize_script( $this->plugin_name.'-network-js','ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' )
        ));
    }

    public function add_plugin_admin_menu() {

        /*
         * Add a settings page for this plugin to the Settings menu.
         *
         * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
         *
         * Administration Menus: http://codex.wordpress.org/Administration_Menus
         *
         */
        //add_options_page( 'WP Woo Master Catalogue Plugin', 'WP MCP', 'manage_options', $this->plugin_name, array($this, 'display_plugin_setup_page'));
        //add_submenu_page('admin.php', 'WP Woo Master Catalogue Plugin', 'WP MCP', 'manage_settings', $this->plugin_name, array($this, 'display_plugin_setup_page'));
        add_menu_page(
            'Master Catalog',
            'Master Catalog',
            'edit_posts',
            $this->plugin_name,
            array($this, 'display_plugin_setup_page'),
            'none'
        );
    }


    public function add_plugin_master_menu() {
        /*
         * Add a new menu for product master catalogue on the master site.
         *
         * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
         *
         * Administration Menus: http://codex.wordpress.org/Administration_Menus
         *
         */
        $settings = $this->getSettings();

        if(is_super_admin() && ($this->curr_blog_id == $settings['woo-mcp_master'])) {
            $page_hook_suffix = add_menu_page(
                'Master Catalog',
                'Master Catalog',
                'edit_posts',
                $this->plugin_name,
                array($this, 'display_master_catalogue'),
                'none'
            );

            //load js and styles on master catalog page
            add_action('admin_print_styles-' . $page_hook_suffix, array($this,'enqueue_styles'));
            add_action('admin_print_scripts-' . $page_hook_suffix, array($this,'enqueue_scripts'));
            add_action('admin_print_scripts-' . $page_hook_suffix, array($this,'woo_mcp_inventory_js'));
        }

    }

    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */

    public function display_plugin_setup_page() {
        $message = "";

        if (isset($_POST['_wpnonce']) && isset($_POST['submit'])) {
            if(isset($_POST['multiplier'])){
                update_site_option($this->plugin_name.'_multiplier', $_POST['multiplier']);
            }

            if(isset($_POST['master'])){
                update_site_option($this->plugin_name.'_master', $_POST['master']);
            }

            /*if(isset($_POST['slave'])){
                  update_site_option($this->plugin_name.'_slave', $_POST['slave']);
            }*/

            $message= "Settings updated.";

        }



        include_once( 'partials/woo-mcp-admin-display.php' );

    }

    /**
     * master catalogue display
     */
    public function display_master_catalogue() {

        include_once( 'class-woo-mcp-products-table.php' );
        $products_table = new Woo_Mcp_Products_Table($this->getProducts(), $this->plugin_name);
        include_once( 'partials/woo-mcp-catalogue.php' );
    }

    /**
     * @return array
     */
    public function getSettings(){
        $options = $this->site_options();
        $settings = array();
        foreach($options as $key=>$value) {
            $settings[$this->plugin_name."_".$key] = get_site_option($this->plugin_name."_".$key);
        }

        return $settings;
    }

    /**
     * @return WP_Query_Multisite
     */
    public function getProducts() {
        $sites_arr = array();
        $master =  get_site_option($this->plugin_name."_master");

        $default_pp = 25;

        $orderby = $_REQUEST['orderby'];

        foreach($this->list_sites() as $arr){
            $sites_arr[] = $arr['blog_id'];
        }

        if ( !empty( $_REQUEST['orderby'] ) )
            $orderby = trim( wp_unslash( $_REQUEST['orderby'] ) );

        if ( !empty( $_REQUEST['order'] ) )
            $order = trim( wp_unslash( $_REQUEST['order'] ) );


        if ( 'price' == $orderby ||  'unit_cost' == $orderby) {
            $vars =  array(
                'meta_key'     => '_regular_price',
                'orderby'     => 'meta_value',
                'order' => $order
            );

        }else if('product_name' == $orderby){
            $vars =  array(
                'orderby'     => 'title',
                'order' => $order
            );
        }else{
            $vars =  array(
                'orderby'     => $orderby,
                'order' => $order
            );
        }

        if ( !empty( $_REQUEST['s'] ) )  {
            $keyword = trim( wp_unslash( $_REQUEST['s'] ) );
            $vars =  array(
                's'     => $keyword
            );
        }

        if(!empty($_REQUEST['view_by'])){
            $view = trim( wp_unslash( $_REQUEST['view_by'] ) );
            $mcp_prod = new Woo_Mcp_Product_Site(0,$this->curr_blog_id );
            $res = $mcp_prod->get_unique_products();
            $prod_ids = array();

            foreach($res as $res) {
                $prod_ids[] = $res[0];
            }

            if($view == 'new') {
                $vars = array(
                    'post__not_in'=> $prod_ids
                );
            } else if($view == 'pushed') {
                $vars = array(
                    'post__in'=> $prod_ids
                );
            }
        }

        $args = wp_parse_args( $vars, array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status'=>'publish'
        ) );

        if (is_plugin_active('sitepress-multilingual-cms/sitepress.php') || is_plugin_active('sitepress-multilingual-cms 2/sitepress.php')) {
            global $sitepress;
            $sitepress->switch_lang( $sitepress->get_default_language() );
        }

        $products = new WP_Query($args);

        if (is_plugin_active('sitepress-multilingual-cms/sitepress.php') || is_plugin_active('sitepress-multilingual-cms 2/sitepress.php')) {
            //$products->posts = array_merge($products->posts, $this->get_french_products());
        }

        return $products;

    }

    public function get_french_products() {
        global $sitepress;

        //changes to the default language
        $sitepress->switch_lang( 'fr' );

        if ( !empty( $_REQUEST['s'] ) )  {
            $keyword = trim( wp_unslash( $_REQUEST['s'] ) );
            $vars =  array(
                's'     => $keyword
            );
        }

        $args = wp_parse_args( $vars, array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status'=>'publish',
            'suppress_filters' => false
        ));

        $wp_query = new WP_Query( $args );

        //changes to the current language
        $sitepress->switch_lang( ICL_LANGUAGE_CODE );
        $include = array();
        foreach($wp_query->posts as $post) {
            $orig_id = icl_object_id ($post->ID, "product", false, "en");
            if($orig_id != null && !is_null($orig_id)) {
                $prod = new Woo_Mcp_Product_Site($orig_id, $this->curr_blog_id);
                $status = $prod->get_status();
                if($status == 'Pushed') {
                    $include[]  = $post;
                }
            }

        }

        return $include;
    }

    public function process_product_data($param, $ids = array()) {
        set_time_limit (0);

        $values = $param;
        $slaves = $param['sites'];

        $trans_arr = $values;

        $trans_ids = array();

        foreach($slaves as $slave){

            $network_details = get_blog_details($slave);

            foreach($values['products'] as $key=>$value){

                $prodtosite = new Woo_Mcp_Product_Site($value, $slave);

                if (is_plugin_active('sitepress-multilingual-cms/sitepress.php') || is_plugin_active('sitepress-multilingual-cms 2/sitepress.php')) {
                    $lang = langcode_post_id($value);
                    $trans_of = icl_object_id ($value, "product", true, "en");
                    $parent_id = $prodtosite->get_data_by_main_id($trans_of);

                    if($lang != 'en' && $trans_of != $value){
                        $trans_arr['is_translation'] = true;
                        $meta_data['is_translation']  = true;

                        global $sitepress;
                        $sitepress->switch_lang($lang);
                    }


                }


                $prod = new Woo_Mcp_Product($value, $slave);
                $multiplier = get_site_option($this->plugin_name."_multiplier")[$slave] == "" ? 1 : get_site_option($this->plugin_name."_multiplier")[$slave];
                $p = wc_get_product( $value);
                $price_x_multiplier = round($p->get_price() * $multiplier);
                $attr = $p->get_attributes();
                $variations = array();

                $meta_data = get_post_meta($value);

                if($p->is_type('variable')){
                    $args = array(
                        'post_type'     => 'product_variation',
                        'post_status'   => array( 'private', 'publish' ),
                        'numberposts'   => -1,
                        'post_parent'   => $value,
                        'orderby'       => 'ID',
                        'order'         => 'asc'
                    );
                    $variations = get_posts( $args, ARRAY_A );

                }

                $feat = $prod->check_image();
                $tax_terms = $prod->taxonomies;

                if($feat != false){
                    $image_url = wp_get_attachment_image_src(get_post_thumbnail_id( $value ), 'full');
                    $image_url = $image_url[0];
                }

                if(isset($meta_data['_product_image_gallery'])){
                    $gallery = $prod->get_gallery_images($meta_data['_product_image_gallery'][0]);
                }

                //sale schedule
                if(is_array($values['_sale_price'][$slave][$key]) && $p->is_type('variable')){
                    $variation_count = count($values['_sale_price'][$slave][$key]);
                    $var_sale_price = $values['_sale_price'][$slave][$key];
                    asort($var_sale_price);
                    $var_sale_prices = array_values($var_sale_price);
                    $min_max_ids = array_keys($var_sale_price);
                    $meta_data['_min_variation_sale_price'][0] = $var_sale_prices[0];
                    $meta_data['_max_variation_sale_price'][0] = $var_sale_prices[$variation_count - 1];
                    $meta_data['_min_sale_price_variation_id'][0] = $min_max_ids[0];
                    $meta_data['_max_sale_price_variation_id'][0] = $min_max_ids[$variation_count - 1];

                } else {
                    $meta_data['_sale_price'][0] = $values['_sale_price'][$slave][$key];
                    $meta_data['_sale_price_dates_from'][0] = $values['_sale_price_dates_from'][$slave][$key] != "" ? strtotime($values['_sale_price_dates_from'][$slave][$key]) : "";
                    $meta_data['_sale_price_dates_to'][0] = $values['_sale_price_dates_to'][$slave][$key] != "" ? strtotime($values['_sale_price_dates_to'][$slave][$key]): "";
                }

                $meta_override = "";

                //price override data
                if($p->is_type('variable')){

                    $variation_count = count($values['_regular_price_'][$slave][$key]);

                    $var_prices = $values['_regular_price_'][$slave][$key];
                    asort($var_prices );
                    $var_prices_ = array_values($var_prices);
                    $var_price_ids = array_keys($var_prices);


                    $meta_data['_min_variation_price'][0] = $var_prices_[0];
                    $meta_data['_max_variation_price'][0] = $var_prices_[$variation_count - 1];
                    $meta_data['_min_variation_regular_price'][0] = $var_prices_[0];
                    $meta_data['_max_variation_regular_price'][0] = $var_prices_[$variation_count - 1];

                    $meta_data['_min_price_variation_id'][0] = $var_price_ids[0];
                    $meta_data['_max_price_variation_id'][0] = $var_price_ids[$variation_count - 1];
                    $meta_data['_min_regular_price_variation_id'][0] = $var_price_ids[0];
                    $meta_data['_max_regular_price_variation_id'][0] = $var_price_ids[$variation_count - 1];

                    $meta_override['_regular_price'][0] = $values['_regular_price_'][$slave][$key];
                    $meta_override['_price'][0] = $values['_regular_price_'][$slave][$key];

                    //commented out for variation price changes
                    /*if($values['_default_calc'][$slave][$key] != $values['_price'][$slave][$key]) {

                        $meta_data['_regular_price'][0] = $values['_regular_price'][$slave][$key];
                        $meta_data['_price'][0] = $values['_product_price'][$slave][$key];
                        $meta_data['_max_variation_price'][0] = $values['_product_price'][$slave][$key];
                        $meta_data['_min_variation_price'][0] = $values['_product_price'][$slave][$key];
                        $meta_data['_max_variation_regular_price'][0] = $values['_product_price'][$slave][$key];
                        $meta_data['_min_variation_regular_price'][0] = $values['_product_price'][$slave][$key];
                        $meta_override = $meta_data;
                    } else {
                        $meta_data['_max_variation_price'][0] = round_off(round($meta_data['_max_variation_price'][0] * $multiplier)) ;
                        $meta_data['_min_variation_price'][0] = round_off(round($meta_data['_min_variation_price'][0] * $multiplier)) ;
                        $meta_data['_max_variation_regular_price'][0] = round_off(round($meta_data['_max_variation_regular_price'][0] * $multiplier)) ;
                        $meta_data['_min_variation_regular_price'][0] = round_off(round($meta_data['_min_variation_regular_price'][0] * $multiplier)) ;
                    }*/
                    $meta_override['_sale_price'][0] = $values['_sale_price'][$slave][$key];
                    $meta_override['_sale_price_dates_from'][0] = $values['_sale_price_dates_from'][$slave][$key];
                    $meta_override['_sale_price_dates_to'][0] = $values['_sale_price_dates_to'][$slave][$key];

                    //price values
                    $meta_data['_price'][0] = $var_prices_[$variation_count - 1];
                    $meta_data['_regular_price'][0] = $var_prices_[$variation_count - 1];
                    $meta_data['_selling_price'][0] = $price_x_multiplier;

                } else {

                    //price values
                    $meta_data['_price'][0] = $values['_product_price'][$slave][$key];
                    $meta_data['_regular_price'][0] = $values['_product_price'][$slave][$key];
                    $meta_data['_selling_price'][0] = $price_x_multiplier;

                }
                $post_status = "";

                if (is_plugin_active('sitepress-multilingual-cms/sitepress.php') || is_plugin_active('sitepress-multilingual-cms 2/sitepress.php')) {
                    if(icl_object_id ($value, "product", false, "fr") && !(in_array(icl_object_id ($value, "product", false, "fr"), $ids))){
                        unset($trans_arr['products'][$key]);
                        $trans_arr['products'][$key] = icl_object_id ($value, "product", false, "fr");
                        $trans_ids[$slave] = icl_object_id ($value, "product", false, "fr");
                        $trans_arr['parent_id'] = $parent_id[0]->product_id_slave;
                    }
                    global $sitepress;
                    $sitepress->switch_lang( $sitepress->get_default_language() );
                }

                switch_to_blog($slave);

                if(is_plugin_active('sitepress-multilingual-cms/sitepress.php') || is_plugin_active('sitepress-multilingual-cms 2/sitepress.php')){
                    $trans_ids = $trans_ids;
                } else {
                    unset($trans_ids[$slave]);
                }

                $product_id_slave = wp_insert_post($prod->clean_data(), true);
                unset($meta_data['_thumbnail_id']);

                if(isset($meta_data['_icl_lang_duplicate_of'][0])){
                    $meta_data['_icl_lang_duplicate_of'][0] = $trans_arr['parent_id'];
                }

                $prod->process_meta($meta_data, $product_id_slave);
                $prod->process_attributes($attr);


                if($p->is_type('variable')){
                    $prod->process_variation($variations, $product_id_slave,$meta_override, $parent_meta=$meta_data);
                }

                if($feat != false && $image_url !="") {

                    $prod->process_image($image_url, $product_id_slave);

                } else {

                    if($prod->check_image($product_id_slave)) {
                        wp_delete_attachment( get_post_thumbnail_id( $product_id_slave ), true );
                    }
                }

                if(isset($meta_data['_product_image_gallery'])){

                    $prod->process_gallery($gallery,$product_id_slave);
                }

                $prod->process_terms_($tax_terms, $product_id_slave, $slave, $values['product_cat'][$slave],$lang);

                $post_status = get_post_status($product_id_slave);

                wp_publish_post( $product_id_slave);

                if (is_plugin_active('sitepress-multilingual-cms/sitepress.php') || is_plugin_active('sitepress-multilingual-cms 2/sitepress.php')) {
                    if($lang != 'en' && $trans_of != $value) {
                        //attach as a translation
                        $prod->attach_as_translation($parent_id[0]->product_id_slave, $product_id_slave, 'post_product', 'fr');

                    }
                }


                restore_current_blog();

                if($values['product_cat'][$slave]){
                    //$this->remove_object_terms($values['product_cat'][$slave],$value,'product_cat');
                }
                $base_price = $p->get_price();
                $override = $p->get_price() != "" && ($values['_default_calc'][$slave][$key] != $values['_product_price'][$slave][$key]) ? 'yes' : 'no';


                $product_data = $prodtosite->get_data_by_main_id($value)[0]->data;
                $data = null;

                if($product_data && !empty($product_data)){
                    $data = unserialize($product_data);
                }

                $data[] = array('base_price'=>$base_price, 'price_override'=>$override, 'multiplier'=>$multiplier, 'new_product_price'=>$values['_product_price'][$slave][$key], 'date'=>current_time( 'mysql' ));


                //default
                $status_on_slave = 1;

                if($prodtosite->get_count_pushed($slave) == 0){
                    $history = null;
                    if($p->is_type('variable')){
                        $history[] = array(
                            'title'=>'Product Push',
                            'date'=>current_time( 'mysql' ),
                            'price'=> $meta_data['_min_variation_price'][0]."-".$meta_data['_max_variation_price'][0]
                        );
                    } else {
                        $history[] = array(
                            'title'=>'Product Push',
                            'date'=>current_time( 'mysql' ),
                            'price'=> $values['_product_price'][$slave][$key]
                        );
                    }


                    if($values['sale_schedule'][$slave][$key] == 1) {
                        if(is_array($values['_sale_price'][$slave][$key]) && $p->is_type('variable')) {

                        } else {
                            $history[] = array(
                                'title'=>'Schedule sale',
                                'date'=>current_time( 'mysql' ),
                                'price'=> $values['_sale_price'][$slave][$key],
                                'sale_from'=> $values['_sale_price_dates_from'][$slave][$key],
                                'sale_to'=> $values['_sale_price_dates_to'][$slave][$key]
                            );
                        }

                    }

                    //insert to product_to_site
                    $data_to_site = array(
                        'network_id'=>absint($slave),
                        'network_name' => $network_details->blogname,
                        'product_id_slave' => $product_id_slave,
                        'product_id_main' => absint($value),
                        'pushed_date'=> current_time( 'mysql' ),
                        'data'=>serialize($data),
                        'history'=>serialize($history),
                        'status_on_slave'=>$status_on_slave
                    );

                    $prodtosite->insert_prod_to_sites($data_to_site);

                } else {

                    $history = null;

                    if(!empty($prodtosite->get_data_by_main_id($value)[0]->history)){
                        $history = unserialize($prodtosite->get_data_by_main_id($value)[0]->history);

                    }

                    if($override == 'yes') {
                        if($p->is_type('variable')){
                            $history[] = array(
                                'title'=>'Updated Push',
                                'date'=>current_time( 'mysql' ),
                                'price'=> $meta_data['_min_variation_price'][0]."-".$meta_data['_max_variation_price'][0]
                            );

                        } else {

                            $history[] = array(
                                'title'=> 'Update Price',
                                'date'=> current_time( 'mysql' ),
                                'price'=> $values['_product_price'][$slave][$key],
                                'notes'=> $values['notes'][$slave][$key]
                            );
                        }
                    } else {

                        if($p->is_type('variable')){
                            $history[] = array(
                                'title'=>'Updated Push',
                                'date'=>current_time( 'mysql' ),
                                'price'=> $meta_data['_min_variation_price'][0]."-".$meta_data['_max_variation_price'][0]
                            );

                        } else {
                            $history[] = array(
                                'title'=>'Product Push',
                                'date'=>current_time( 'mysql' ),
                                'price'=> $values['_product_price'][$slave][$key]
                            );
                        }


                    }

                    if($post_status == 'pending') {
                        $history[] = array(
                            'title'=>'Product activated on the slave site',
                            'date'=>current_time( 'mysql' ),
                            'price'=> $values['_product_price'][$slave][$key]
                        );

                        $status_on_slave = 1;
                    }

                    if($values['sale_schedule'][$slave][$key] == 1) {
                        if(is_array($values['_sale_price'][$slave][$key]) && $p->is_type('variable')) {

                            $var_data  = array();
                            foreach($values['_sale_price'][$slave][$key] as $key_var=>$price){
                                $var_data[$key_var]  = array(
                                    'price'=> $values['_sale_price'][$slave][$key][$key_var],
                                    'sale_from'=> $values['_sale_price_dates_from'][$slave][$key][$key_var],
                                    'sale_to'=> $values['_sale_price_dates_to'][$slave][$key][$key_var]
                                );

                            }

                            $history[] = array(
                                'title'=>'Schedule sale',
                                'date'=>current_time( 'mysql' ),
                                'variations'=>$var_data
                            );

                        } else {
                            $history[] = array(
                                'title'=>'Schedule sale',
                                'date'=>current_time( 'mysql' ),
                                'price'=> $values['_sale_price'][$slave][$key],
                                'sale_from'=> $values['_sale_price_dates_from'][$slave][$key],
                                'sale_to'=> $values['_sale_price_dates_to'][$slave][$key]
                            );
                        }

                    }

                    if($history) {
                        if(find_key_value($history, 'title', 'Schedule sale') && $values['sale_schedule'][$slave][$key] == 0){
                            $history[] = array(
                                'title'=>'Unschedule sale',
                                'date'=>current_time( 'mysql' ),
                                'price'=> $values['_product_price'][$slave][$key]
                            );
                        }
                    }

                    $history = !empty($history) ? serialize($history) : $history;

                    $data_to_site = array('pushed_modified'=> current_time( 'mysql' ), 'data'=>serialize($data), 'history'=>$history, 'product_id_slave'=>$product_id_slave, 'status_on_slave'=>$status_on_slave);
                    $prodtosite->update_prod_to_sites($data_to_site);

                }

            }

        }

        //if product has a translation
        if($trans_ids){
            $this->process_product_data($trans_arr, $trans_ids);
            unset($trans_ids);
        }



    }

    public function process_product_data_new ($param) {
        set_time_limit (0);
        $values = $param;
        $slaves = $param['sites'];
        $trans_ids = array();

        foreach($slaves as $slave){
            $network_details = get_blog_details($slave);
            $trans_ids['sites'][] = $slave;
            foreach($values['products'] as $key=>$value){
                $prodtosite = new Woo_Mcp_Product_Site($value, $slave);

                $prod = new Woo_Mcp_Product($value, $slave);
                $multiplier = get_site_option($this->plugin_name."_multiplier")[$slave] == "" ? 1 : get_site_option($this->plugin_name."_multiplier")[$slave];
                $p = wc_get_product( $value);
                $price_x_multiplier = round($p->get_price() * $multiplier);
                $attr = $p->get_attributes();
                $variations = array();

                $meta_data = get_post_meta($value);

                if($p->is_type('variable')){
                    $args = array(
                        'post_type'     => 'product_variation',
                        'post_status'   => array( 'private', 'publish' ),
                        'numberposts'   => -1,
                        'post_parent'   => $value,
                        'orderby'       => 'ID',
                        'order'         => 'asc'
                    );
                    $variations = get_posts( $args, ARRAY_A );

                }

                $feat = $prod->check_image();
                $tax_terms = $prod->taxonomies;
                if($feat != false){
                    $image_url = wp_get_attachment_image_src(get_post_thumbnail_id( $value ), 'full');
                    $image_url = $image_url[0];
                }

                if(isset($meta_data['_product_image_gallery'])){
                    $gallery = $prod->get_gallery_images($meta_data['_product_image_gallery'][0]);
                }

                //sale schedule
                if(is_array($values['_sale_price'][$slave][$key]) && $p->is_type('variable')){
                    $variation_count = count($values['_sale_price'][$slave][$key]);
                    $var_sale_price = $values['_sale_price'][$slave][$key];
                    asort($var_sale_price);
                    $var_sale_prices = array_values($var_sale_price);
                    $min_max_ids = array_keys($var_sale_price);
                    $meta_data['_min_variation_sale_price'][0] = $var_sale_prices[0];
                    $meta_data['_max_variation_sale_price'][0] = $var_sale_prices[$variation_count - 1];
                    $meta_data['_min_sale_price_variation_id'][0] = $min_max_ids[0];
                    $meta_data['_max_sale_price_variation_id'][0] = $min_max_ids[$variation_count - 1];

                } else {
                    $meta_data['_sale_price'][0] = $values['_sale_price'][$slave][$key];
                    $meta_data['_sale_price_dates_from'][0] = $values['_sale_price_dates_from'][$slave][$key] != "" ? strtotime($values['_sale_price_dates_from'][$slave][$key]) : "";
                    $meta_data['_sale_price_dates_to'][0] = $values['_sale_price_dates_to'][$slave][$key] != "" ? strtotime($values['_sale_price_dates_to'][$slave][$key]): "";
                }

                $meta_override = "";

                //price override data
                if($p->is_type('variable')){

                    $variation_count = count($values['_regular_price_'][$slave][$key]);

                    $var_prices = $values['_regular_price_'][$slave][$key];
                    asort($var_prices );
                    $var_prices_ = array_values($var_prices);
                    $var_price_ids = array_keys($var_prices);


                    $meta_data['_min_variation_price'][0] = $var_prices_[0];
                    $meta_data['_max_variation_price'][0] = $var_prices_[$variation_count - 1];
                    $meta_data['_min_variation_regular_price'][0] = $var_prices_[0];
                    $meta_data['_max_variation_regular_price'][0] = $var_prices_[$variation_count - 1];

                    $meta_data['_min_price_variation_id'][0] = $var_price_ids[0];
                    $meta_data['_max_price_variation_id'][0] = $var_price_ids[$variation_count - 1];
                    $meta_data['_min_regular_price_variation_id'][0] = $var_price_ids[0];
                    $meta_data['_max_regular_price_variation_id'][0] = $var_price_ids[$variation_count - 1];

                    $meta_override['_regular_price'][0] = $values['_regular_price_'][$slave][$key];
                    $meta_override['_price'][0] = $values['_regular_price_'][$slave][$key];


                    $meta_override['_sale_price'][0] = $values['_sale_price'][$slave][$key];
                    $meta_override['_sale_price_dates_from'][0] = $values['_sale_price_dates_from'][$slave][$key];
                    $meta_override['_sale_price_dates_to'][0] = $values['_sale_price_dates_to'][$slave][$key];

                    //price values
                    $meta_data['_price'][0] = $var_prices_[$variation_count - 1];
                    $meta_data['_regular_price'][0] = $var_prices_[$variation_count - 1];
                    $meta_data['_selling_price'][0] = $price_x_multiplier;

                } else {

                    //price values
                    $meta_data['_price'][0] = $values['_product_price'][$slave][$key];
                    $meta_data['_regular_price'][0] = $values['_product_price'][$slave][$key];
                    $meta_data['_selling_price'][0] = $price_x_multiplier;

                }

                if(is_plugin_active('sitepress-multilingual-cms/sitepress.php') || is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
                    if(icl_object_id($value, "product", false, "fr")){
                        $trans_ids['products'][$key] = icl_object_id($value, "product", false, "fr");
                        $trans_ids['parents'][$key] = $value;
                        $trans_ids['values'] = $values;
                    }

                } else {
                    unset($trans_ids[$slave]);
                }

                switch_to_blog($slave);


                $product_id_slave = wp_insert_post($prod->clean_data(), true);
                unset($meta_data['_thumbnail_id']);

                $prod->process_meta($meta_data, $product_id_slave);
                $prod->process_attributes($attr);


                if($p->is_type('variable')){
                    $prod->process_variation($variations, $product_id_slave,$meta_override, $parent_meta=$meta_data);
                }

                if($feat != false && $image_url !="") {

                    $prod->process_image($image_url, $product_id_slave);

                } else {

                    if($prod->check_image($product_id_slave)) {
                        wp_delete_attachment( get_post_thumbnail_id( $product_id_slave ), true );
                    }
                }

                if(isset($meta_data['_product_image_gallery'])){

                    $prod->process_gallery($gallery,$product_id_slave);
                }

                $prod->process_terms_($tax_terms, $product_id_slave, $slave, $values['product_cat'][$slave]);

                $post_status = get_post_status($product_id_slave);

                wp_publish_post( $product_id_slave);

                restore_current_blog();

                if($values['product_cat'][$slave]){
                    //$this->remove_object_terms($values['product_cat'][$slave],$value,'product_cat');
                }

                $base_price = $p->get_price();
                $override = $p->get_price() != "" && ($values['_default_calc'][$slave][$key] != $values['_product_price'][$slave][$key]) ? 'yes' : 'no';


                $product_data = $prodtosite->get_data_by_main_id($value)[0]->data;
                $data = null;

                if($product_data && !empty($product_data)){
                    $data = unserialize($product_data);
                }

                $data[] = array('base_price'=>$base_price, 'price_override'=>$override, 'multiplier'=>$multiplier, 'new_product_price'=>$values['_product_price'][$slave][$key], 'date'=>current_time( 'mysql' ));


                //default
                $status_on_slave = 1;

                if($prodtosite->get_count_pushed($slave) == 0){
                    $history = null;
                    if($p->is_type('variable')){
                        $history[] = array(
                            'title'=>'Product Push',
                            'date'=>current_time( 'mysql' ),
                            'price'=> $meta_data['_min_variation_price'][0]."-".$meta_data['_max_variation_price'][0]
                        );
                    } else {
                        $history[] = array(
                            'title'=>'Product Push',
                            'date'=>current_time( 'mysql' ),
                            'price'=> $values['_product_price'][$slave][$key]
                        );
                    }


                    if($values['sale_schedule'][$slave][$key] == 1) {
                        if(is_array($values['_sale_price'][$slave][$key]) && $p->is_type('variable')) {

                        } else {
                            $history[] = array(
                                'title'=>'Schedule sale',
                                'date'=>current_time( 'mysql' ),
                                'price'=> $values['_sale_price'][$slave][$key],
                                'sale_from'=> $values['_sale_price_dates_from'][$slave][$key],
                                'sale_to'=> $values['_sale_price_dates_to'][$slave][$key]
                            );
                        }

                    }

                    //insert to product_to_site
                    $data_to_site = array(
                        'network_id'=>absint($slave),
                        'network_name' => $network_details->blogname,
                        'product_id_slave' => $product_id_slave,
                        'product_id_main' => absint($value),
                        'pushed_date'=> current_time( 'mysql' ),
                        'data'=>serialize($data),
                        'history'=>serialize($history),
                        'status_on_slave'=>$status_on_slave
                    );

                    $prodtosite->insert_prod_to_sites($data_to_site);

                } else {

                    $history = null;

                    if(!empty($prodtosite->get_data_by_main_id($value)[0]->history)){
                        $history = unserialize($prodtosite->get_data_by_main_id($value)[0]->history);

                    }

                    if($override == 'yes') {
                        if($p->is_type('variable')){
                            $history[] = array(
                                'title'=>'Updated Push',
                                'date'=>current_time( 'mysql' ),
                                'price'=> $meta_data['_min_variation_price'][0]."-".$meta_data['_max_variation_price'][0]
                            );

                        } else {

                            $history[] = array(
                                'title'=> 'Update Price',
                                'date'=> current_time( 'mysql' ),
                                'price'=> $values['_product_price'][$slave][$key],
                                'notes'=> $values['notes'][$slave][$key]
                            );
                        }
                    } else {

                        if($p->is_type('variable')){
                            $history[] = array(
                                'title'=>'Updated Push',
                                'date'=>current_time( 'mysql' ),
                                'price'=> $meta_data['_min_variation_price'][0]."-".$meta_data['_max_variation_price'][0]
                            );

                        } else {
                            $history[] = array(
                                'title'=>'Product Push',
                                'date'=>current_time( 'mysql' ),
                                'price'=> $values['_product_price'][$slave][$key]
                            );
                        }


                    }

                    if($post_status == 'pending') {
                        $history[] = array(
                            'title'=>'Product activated on the slave site',
                            'date'=>current_time( 'mysql' ),
                            'price'=> $values['_product_price'][$slave][$key]
                        );

                        $status_on_slave = 1;
                    }

                    if($values['sale_schedule'][$slave][$key] == 1) {
                        if(is_array($values['_sale_price'][$slave][$key]) && $p->is_type('variable')) {

                            $var_data  = array();
                            foreach($values['_sale_price'][$slave][$key] as $key_var=>$price){
                                $var_data[$key_var]  = array(
                                    'price'=> $values['_sale_price'][$slave][$key][$key_var],
                                    'sale_from'=> $values['_sale_price_dates_from'][$slave][$key][$key_var],
                                    'sale_to'=> $values['_sale_price_dates_to'][$slave][$key][$key_var]
                                );

                            }

                            $history[] = array(
                                'title'=>'Schedule sale',
                                'date'=>current_time( 'mysql' ),
                                'variations'=>$var_data
                            );

                        } else {
                            $history[] = array(
                                'title'=>'Schedule sale',
                                'date'=>current_time( 'mysql' ),
                                'price'=> $values['_sale_price'][$slave][$key],
                                'sale_from'=> $values['_sale_price_dates_from'][$slave][$key],
                                'sale_to'=> $values['_sale_price_dates_to'][$slave][$key]
                            );
                        }

                    }

                    if($history) {
                        if(find_key_value($history, 'title', 'Schedule sale') && $values['sale_schedule'][$slave][$key] == 0){
                            $history[] = array(
                                'title'=>'Unschedule sale',
                                'date'=>current_time( 'mysql' ),
                                'price'=> $values['_product_price'][$slave][$key]
                            );
                        }
                    }

                    $history = !empty($history) ? serialize($history) : $history;

                    $data_to_site = array('pushed_modified'=> current_time( 'mysql' ), 'data'=>serialize($data), 'history'=>$history, 'product_id_slave'=>$product_id_slave, 'status_on_slave'=>$status_on_slave);
                    $prodtosite->update_prod_to_sites($data_to_site);

                }
            }
        }

        if($trans_ids){
            wp_send_json( $trans_ids);
            exit();
            die();
        }

    }

    /**
     * process ajax request from settings page
     */
    public function woo_mcp_process_ajax(){
        set_time_limit (0);
        $data = $_POST;
        parse_str(($data['data']), $values);
        $slaves = $values['sites'];
        $values['method'] = 'post';
        $this->process_product_data_new($values);

        exit();
        die();
    }

    public function processFrench(){
        set_time_limit (0);
        $data = $_POST;

        $values = $data['data']['values'];
        $parents = $data['data']['parents'];
        $products = $data['data']['products'];

        foreach($data['data']['sites'] as $slave){

            $network_details = get_blog_details($slave);

            foreach($products as $key=>$value){
                $prodtosite = new Woo_Mcp_Product_Site($value, $slave);

                if (is_plugin_active('sitepress-multilingual-cms/sitepress.php') || is_plugin_active('sitepress-multilingual-cms 2/sitepress.php')) {
                    $lang = langcode_post_id($value);
                    $trans_of = $parents[$key];
                    $parent_id = $prodtosite->get_data_by_main_id($trans_of);

                    $meta_data['is_translation']  = true;

                    global $sitepress;
                    $sitepress->switch_lang($lang);
                }


                $prod = new Woo_Mcp_Product($value, $slave);
                $multiplier = get_site_option($this->plugin_name."_multiplier")[$slave] == "" ? 1 : get_site_option($this->plugin_name."_multiplier")[$slave];
                $p = wc_get_product( $value);
                $price_x_multiplier = round($p->get_price() * $multiplier);
                $attr = $p->get_attributes();
                $variations = array();

                $meta_data = get_post_meta($value);

                if($p->is_type('variable')){
                    $args = array(
                        'post_type'     => 'product_variation',
                        'post_status'   => array( 'private', 'publish' ),
                        'numberposts'   => -1,
                        'post_parent'   => $value,
                        'orderby'       => 'ID',
                        'order'         => 'asc'
                    );
                    $variations = get_posts( $args, ARRAY_A );

                }

                $feat = $prod->check_image();
                $tax_terms = $prod->taxonomies;
                if($feat != false){
                    $image_url = wp_get_attachment_image_src(get_post_thumbnail_id( $value ), 'full');
                    $image_url = $image_url[0];
                }

                if(isset($meta_data['_product_image_gallery'])){
                    $gallery = $prod->get_gallery_images($meta_data['_product_image_gallery'][0]);
                }

                //sale schedule
                if(is_array($values['_sale_price'][$slave][$key]) && $p->is_type('variable')){
                    $variation_count = count($values['_sale_price'][$slave][$key]);
                    $var_sale_price = $values['_sale_price'][$slave][$key];
                    asort($var_sale_price);
                    $var_sale_prices = array_values($var_sale_price);
                    $min_max_ids = array_keys($var_sale_price);
                    $meta_data['_min_variation_sale_price'][0] = $var_sale_prices[0];
                    $meta_data['_max_variation_sale_price'][0] = $var_sale_prices[$variation_count - 1];
                    $meta_data['_min_sale_price_variation_id'][0] = $min_max_ids[0];
                    $meta_data['_max_sale_price_variation_id'][0] = $min_max_ids[$variation_count - 1];

                } else {
                    $meta_data['_sale_price'][0] = $values['_sale_price'][$slave][$key];
                    $meta_data['_sale_price_dates_from'][0] = $values['_sale_price_dates_from'][$slave][$key] != "" ? strtotime($values['_sale_price_dates_from'][$slave][$key]) : "";
                    $meta_data['_sale_price_dates_to'][0] = $values['_sale_price_dates_to'][$slave][$key] != "" ? strtotime($values['_sale_price_dates_to'][$slave][$key]): "";
                }

                $meta_override = "";

                //price override data
                if($p->is_type('variable')){

                    $variation_count = count($values['_regular_price_'][$slave][$key]);

                    $var_prices = $values['_regular_price_'][$slave][$key];
                    asort($var_prices );
                    $var_prices_ = array_values($var_prices);
                    $var_price_ids = array_keys($var_prices);


                    $meta_data['_min_variation_price'][0] = $var_prices_[0];
                    $meta_data['_max_variation_price'][0] = $var_prices_[$variation_count - 1];
                    $meta_data['_min_variation_regular_price'][0] = $var_prices_[0];
                    $meta_data['_max_variation_regular_price'][0] = $var_prices_[$variation_count - 1];

                    $meta_data['_min_price_variation_id'][0] = $var_price_ids[0];
                    $meta_data['_max_price_variation_id'][0] = $var_price_ids[$variation_count - 1];
                    $meta_data['_min_regular_price_variation_id'][0] = $var_price_ids[0];
                    $meta_data['_max_regular_price_variation_id'][0] = $var_price_ids[$variation_count - 1];

                    $meta_override['_regular_price'][0] = $values['_regular_price_'][$slave][$key];
                    $meta_override['_price'][0] = $values['_regular_price_'][$slave][$key];


                    $meta_override['_sale_price'][0] = $values['_sale_price'][$slave][$key];
                    $meta_override['_sale_price_dates_from'][0] = $values['_sale_price_dates_from'][$slave][$key];
                    $meta_override['_sale_price_dates_to'][0] = $values['_sale_price_dates_to'][$slave][$key];

                    //price values
                    $meta_data['_price'][0] = $var_prices_[$variation_count - 1];
                    $meta_data['_regular_price'][0] = $var_prices_[$variation_count - 1];
                    $meta_data['_selling_price'][0] = $price_x_multiplier;

                } else {
                    //price values
                    $meta_data['_price'][0] = $values['_product_price'][$slave][$key];
                    $meta_data['_regular_price'][0] = $values['_product_price'][$slave][$key];
                    $meta_data['_selling_price'][0] = $price_x_multiplier;

                }
                $post_status = "";

                if (is_plugin_active('sitepress-multilingual-cms/sitepress.php') || is_plugin_active('sitepress-multilingual-cms 2/sitepress.php')) {

                    global $sitepress;
                    $sitepress->switch_lang( $sitepress->get_default_language() );
                }

                switch_to_blog($slave);


                $product_id_slave = wp_insert_post($prod->clean_data(), true);
                unset($meta_data['_thumbnail_id']);

                if(isset($meta_data['_icl_lang_duplicate_of'][0])){
                    $meta_data['_icl_lang_duplicate_of'][0] = $parent_id[0]->product_id_slave;
                }

                $prod->process_meta($meta_data, $product_id_slave);
                $prod->process_attributes($attr);


                if($p->is_type('variable')){
                    $prod->process_variation($variations, $product_id_slave,$meta_override, $parent_meta=$meta_data);
                }

                if($feat != false && $image_url !="") {

                    $prod->process_image($image_url, $product_id_slave);

                } else {

                    if($prod->check_image($product_id_slave)) {
                        wp_delete_attachment( get_post_thumbnail_id( $product_id_slave ), true );
                    }
                }

                if(isset($meta_data['_product_image_gallery'])){

                    $prod->process_gallery($gallery,$product_id_slave);
                }

                $prod->process_terms_french($tax_terms, $product_id_slave, $slave, $values['product_cat'][$slave]);

                $post_status = get_post_status($product_id_slave);

                wp_publish_post( $product_id_slave);

                if (is_plugin_active('sitepress-multilingual-cms/sitepress.php') || is_plugin_active('sitepress-multilingual-cms 2/sitepress.php')) {
                    if($lang != 'en') {
                        //attach as a translation
                        $prod->attach_as_translation($parent_id[0]->product_id_slave, $product_id_slave, 'post_product', 'fr');
                    }
                }

                restore_current_blog();

                if($values['product_cat'][$slave]){
                    $this->remove_object_terms($values['product_cat'][$slave],$parents[$key],'product_cat');
                }
                $base_price = $p->get_price();
                $override = $p->get_price() != "" && ($values['_default_calc'][$slave][$key] != $values['_product_price'][$slave][$key]) ? 'yes' : 'no';


                $product_data = $prodtosite->get_data_by_main_id($value)[0]->data;
                $data = null;

                if($product_data && !empty($product_data)){
                    $data = unserialize($product_data);
                }

                $data[] = array('base_price'=>$base_price, 'price_override'=>$override, 'multiplier'=>$multiplier, 'new_product_price'=>$values['_product_price'][$slave][$key], 'date'=>current_time( 'mysql' ));


                //default
                $status_on_slave = 1;

                if($prodtosite->get_count_pushed($slave) == 0){
                    $history = null;
                    if($p->is_type('variable')){
                        $history[] = array(
                            'title'=>'Product Push',
                            'date'=>current_time( 'mysql' ),
                            'price'=> $meta_data['_min_variation_price'][0]."-".$meta_data['_max_variation_price'][0]
                        );
                    } else {
                        $history[] = array(
                            'title'=>'Product Push',
                            'date'=>current_time( 'mysql' ),
                            'price'=> $values['_product_price'][$slave][$key]
                        );
                    }


                    if($values['sale_schedule'][$slave][$key] == 1) {
                        if(is_array($values['_sale_price'][$slave][$key]) && $p->is_type('variable')) {

                        } else {
                            $history[] = array(
                                'title'=>'Schedule sale',
                                'date'=>current_time( 'mysql' ),
                                'price'=> $values['_sale_price'][$slave][$key],
                                'sale_from'=> $values['_sale_price_dates_from'][$slave][$key],
                                'sale_to'=> $values['_sale_price_dates_to'][$slave][$key]
                            );
                        }

                    }

                    //insert to product_to_site
                    $data_to_site = array(
                        'network_id'=>absint($slave),
                        'network_name' => $network_details->blogname,
                        'product_id_slave' => $product_id_slave,
                        'product_id_main' => absint($value),
                        'pushed_date'=> current_time( 'mysql' ),
                        'data'=>serialize($data),
                        'history'=>serialize($history),
                        'status_on_slave'=>$status_on_slave
                    );

                    $prodtosite->insert_prod_to_sites($data_to_site);

                } else {

                    $history = null;

                    if(!empty($prodtosite->get_data_by_main_id($value)[0]->history)){
                        $history = unserialize($prodtosite->get_data_by_main_id($value)[0]->history);

                    }

                    if($override == 'yes') {
                        if($p->is_type('variable')){
                            $history[] = array(
                                'title'=>'Updated Push',
                                'date'=>current_time( 'mysql' ),
                                'price'=> $meta_data['_min_variation_price'][0]."-".$meta_data['_max_variation_price'][0]
                            );

                        } else {

                            $history[] = array(
                                'title'=> 'Update Price',
                                'date'=> current_time( 'mysql' ),
                                'price'=> $values['_product_price'][$slave][$key],
                                'notes'=> $values['notes'][$slave][$key]
                            );
                        }
                    } else {

                        if($p->is_type('variable')){
                            $history[] = array(
                                'title'=>'Updated Push',
                                'date'=>current_time( 'mysql' ),
                                'price'=> $meta_data['_min_variation_price'][0]."-".$meta_data['_max_variation_price'][0]
                            );

                        } else {
                            $history[] = array(
                                'title'=>'Product Push',
                                'date'=>current_time( 'mysql' ),
                                'price'=> $values['_product_price'][$slave][$key]
                            );
                        }


                    }

                    if($post_status == 'pending') {
                        $history[] = array(
                            'title'=>'Product activated on the slave site',
                            'date'=>current_time( 'mysql' ),
                            'price'=> $values['_product_price'][$slave][$key]
                        );

                        $status_on_slave = 1;
                    }

                    if($values['sale_schedule'][$slave][$key] == 1) {
                        if(is_array($values['_sale_price'][$slave][$key]) && $p->is_type('variable')) {

                            $var_data  = array();
                            foreach($values['_sale_price'][$slave][$key] as $key_var=>$price){
                                $var_data[$key_var]  = array(
                                    'price'=> $values['_sale_price'][$slave][$key][$key_var],
                                    'sale_from'=> $values['_sale_price_dates_from'][$slave][$key][$key_var],
                                    'sale_to'=> $values['_sale_price_dates_to'][$slave][$key][$key_var]
                                );

                            }

                            $history[] = array(
                                'title'=>'Schedule sale',
                                'date'=>current_time( 'mysql' ),
                                'variations'=>$var_data
                            );

                        } else {
                            $history[] = array(
                                'title'=>'Schedule sale',
                                'date'=>current_time( 'mysql' ),
                                'price'=> $values['_sale_price'][$slave][$key],
                                'sale_from'=> $values['_sale_price_dates_from'][$slave][$key],
                                'sale_to'=> $values['_sale_price_dates_to'][$slave][$key]
                            );
                        }

                    }

                    if($history) {
                        if(find_key_value($history, 'title', 'Schedule sale') && $values['sale_schedule'][$slave][$key] == 0){
                            $history[] = array(
                                'title'=>'Unschedule sale',
                                'date'=>current_time( 'mysql' ),
                                'price'=> $values['_product_price'][$slave][$key]
                            );
                        }
                    }

                    $history = !empty($history) ? serialize($history) : $history;

                    $data_to_site = array('pushed_modified'=> current_time( 'mysql' ), 'data'=>serialize($data), 'history'=>$history, 'product_id_slave'=>$product_id_slave, 'status_on_slave'=>$status_on_slave);
                    $prodtosite->update_prod_to_sites($data_to_site);

                }
            }
        }
    }


    /**
     * @param $post_id
     * @param $post
     * @param $update
     */
    public function sync_inventory_to_slave($post_id, $post, $update) {
        if ( 'product' != $post->post_type ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;

        $settings = $this->getSettings();
        $prod_site  = new Woo_Mcp_Product_Site($post->ID, $settings['woo-mcp_master']);
        $slave_products = $prod_site->get_slave_by_master($post_id);
        $data = $_REQUEST;
        $product_type = wc_clean( $data['type'] );
        if(($this->curr_blog_id == $settings['woo-mcp_master'])){

            if( !empty($slave_products)){
                foreach($slave_products as $slave){

                    switch_to_blog($slave->network_id);

                    if ( isset( $data['_sold_individually'] ) ) {
                        update_post_meta( $slave->product_id_slave, '_sold_individually', ($data['_sold_individually'] == 'yes' ) ? 'yes' : '' );
                    } else {
                        update_post_meta( $slave->product_id_slave, '_sold_individually','no');
                    }

                    // Stock status
                    if ( isset( $data['_stock_status'] ) ) {
                        $stock_status = $data['_stock_status'] ==  'instock' ? 'instock' : 'outofstock';
                    } else {
                        $stock_status = get_post_meta( $slave->product_id_slave, '_stock_status', true );

                        if ( '' === $stock_status ) {
                            $stock_status = 'instock';
                        }
                    }


                    // Stock Data
                    if ( 'yes' == get_option( 'woocommerce_manage_stock' ) ) {

                        // Manage stock
                        if ( isset( $data['_manage_stock'] ) ) {
                            $managing_stock =  $data['_manage_stock'] == 'yes' ? 'yes' : 'no';
                            update_post_meta( $slave->product_id_slave, '_manage_stock', $managing_stock );
                        } else {
                            $managing_stock = 'no';
                        }

                        // Backorders
                        if ( isset( $data['_backorders'] ) ) {
                            if ( 'notify' == $data['_backorders'] ) {
                                $backorders = 'notify';
                            } else {
                                $backorders = $data['_backorders'] == 'yes' ? 'yes' : 'no';
                            }

                            update_post_meta($slave->product_id_slave, '_backorders', $backorders );

                        } else {
                            $backorders = get_post_meta($slave->product_id_slave, '_backorders', true );
                        }



                        if ( 'grouped' == $product_type ) {

                            update_post_meta( $slave->product_id_slave, '_manage_stock', 'no' );
                            update_post_meta($slave->product_id_slave, '_backorders', 'no' );
                            update_post_meta( $slave->product_id_slave, '_stock', '' );

                            wc_update_product_stock_status( $slave->product_id_slave, $stock_status );

                        } elseif ( 'external' == $product_type ) {

                            update_post_meta( $slave->product_id_slave, '_manage_stock', 'no' );
                            update_post_meta( $slave->product_id_slave, '_backorders', 'no' );
                            update_post_meta( $slave->product_id_slave, '_stock', '' );

                            wc_update_product_stock_status( $slave->product_id_slave, 'instock' );

                        } elseif ( 'yes' == $managing_stock ) {
                            update_post_meta( $slave->product_id_slave, '_backorders', $backorders );

                            wc_update_product_stock_status( $slave->product_id_slave, $stock_status );

                            // Stock quantity
                            if ( isset( $data['_stock'] ) ) {
                                wc_update_product_stock($slave->product_id_slave, intval( $data['_stock'] ) );
                            }
                        } else {

                            // Don't manage stock
                            update_post_meta( $slave->product_id_slave, '_manage_stock', 'no' );
                            update_post_meta( $slave->product_id_slave, '_backorders', $backorders );
                            update_post_meta( $slave->product_id_slave, '_stock', '' );

                            wc_update_product_stock_status( $slave->product_id_slave, $stock_status );
                        }

                    } else {
                        wc_update_product_stock_status( $slave->product_id_slave, $stock_status );
                    }

                    restore_current_blog();
                }
            }
        }
    }

    public function woo_mcp_meta_box($post_type){
        $post_types = array('product');     //limit meta box to certain post types
        global $post;
        $product = wc_get_product( $post->ID );
        $settings = $this->getSettings();

        if (in_array( $post_type, $post_types ) && ($this->curr_blog_id == $settings['woo-mcp_master'])) {
            add_meta_box(
                'woocommerce-product-mcp',
                __('Added to', 'woocommerce'),
                array($this, 'woo_mcp_meta_box_content'),
                $post_type,
                'side',
                'low'
            );
        }
    }

    public function woo_mcp_meta_box_content(){
        global $wpdb;
        $id = get_the_ID();

        $results = $wpdb->get_results('SELECT network_name FROM wp_mcp_product_to_sites WHERE product_id_main = '.$id);
        if($results){
            echo '<ul style="list-style:disc; margin-left: 20px;">';
            foreach($results as $result){
                echo '<li>'.$result->network_name.'</li>';
            }
            echo '</ul>';
        }else{
            echo '<p><em>The product is not yet added on any slave sites.</em></p>';
        }
    }

    public function woo_mcp_inventory_js($page_hook) {
        global $post;

        //check first if product of a slave site belongs to master db
        $settings = $this->getSettings();
        if ($this->curr_blog_id != $settings['woo-mcp_master']) {

            if (!in_array($page_hook, array('post.php', 'post-new.php'))) {
                return;
            }
            if ($post->post_type!=='product') {
                return;
            }

            wp_enqueue_script( 'inventory-js', plugin_dir_url( __FILE__ ) . 'js/inventory.js', array( 'jquery' ), false, true );

        }

    }

    public function wc_price_product_field() {
        global $post;
        $product = wc_get_product( $post->ID );

        $prodtosite = new Woo_Mcp_Product_Site($post->ID, $this->curr_blog_id);
        $settings = $this->getSettings();
        $pushed = $prodtosite->check_slave_pushed($post->ID);
        if(($this->curr_blog_id != (int)$settings['woo-mcp_master']) && (int)$pushed > 0) {
            $sell_price = $product->get_price();
            $value = get_post_meta($post->ID, '_selling_price', true) == '' ? $sell_price : get_post_meta($post->ID, '_selling_price', true);
            woocommerce_wp_text_input( array('value'=>$value, 'id' => '_selling_price', 'class' => 'wc_input_price short', 'label' => __( 'Price x Multiplier', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')' ) );
        }
    }

    public function woomcp_get_price($price,$product) {
        $prodtosite = new Woo_Mcp_Product_Site($product, $this->curr_blog_id);
        $settings = $this->getSettings();

        if($this->curr_blog_id == (int)$settings['woo-mcp_master']) {
            $price  = $price;
        } else {
            $pushed = $prodtosite->check_slave_pushed($product->id);
            $price = $price;
            if((int)$pushed > 0){
                $multiplier = get_site_option($this->plugin_name."_multiplier")[$this->curr_blog_id] == "" ? 1 : get_site_option($this->plugin_name."_multiplier")[$this->curr_blog_id];
                $data = $prodtosite->get_master_by_slave($product->id);
                $mprice = $price * $multiplier;
                $price = round_off(round($mprice));

                if(unserialize($data[0]->data)){
                    $price = unserialize($data[0]->data)['price_override'] == 'yes' ? unserialize($data[0]->data)['new_product_price'] : $price;
                }

                //check if the product is on sale
                if($product->get_sale_price() != $product->get_regular_price() && $product->get_sale_price() != $price) {
                    $price = $product->get_sale_price();

                }

            }
        }



        return $price;
    }
    public function custom_variation_price($price, $product) {
        return wc_price($product->get_price());
    }


    public function wc_var_price_field($loop, $variation_data, $variation ) {
        global $post;

        $product = wc_get_product( $post->ID );


        $prodtosite = new Woo_Mcp_Product_Site($variation_data->variation_post_id, $this->curr_blog_id);
        $settings = $this->getSettings();
        $pushed = $prodtosite->check_slave_pushed($post->ID);

        if(($this->curr_blog_id != (int)$settings['woo-mcp_master']) && (int)$pushed > 0) {
            $sell_price = $product->get_price();
            $value = $variation_data['variable_selling_price'][0] == '' ? $sell_price : esc_attr( $variation_data['variable_selling_price'][0]);
            ?>
            <tr class="price_multiplier_row">
                <td>
                    <div>
                        <label><?php _e( 'Price x Multiplier:', 'woocommerce' ); ?></label>
                        <input class="variable_selling_price" type="text" size="5" name="variable_selling_price[<?php echo $loop; ?>]" value="<?php echo $value; ?>" step="1" min="0" />
                    </div>
                </td>
            </tr>
        <?php
        }
    }

    public function wc_save_prod_simple($product_id){
        if( isset($_POST['_selling_price']) && $_POST['_selling_price'] > 0 ){
            $product = wc_get_product($product_id);
            $price_ = $product->get_price();
            $prodtosite = new Woo_Mcp_Product_Site($product_id, $this->curr_blog_id);
            $data = $prodtosite->get_master_by_slave($product_id);
            if(unserialize($data[0]->data)){
                $slave_data = array_reverse(unserialize($data[0]->data));

                $price_ = round($slave_data['base_price'] * $slave_data[0]['multiplier']);
            }
            update_post_meta( $product_id, '_selling_price', $price_ );
        }
    }

    public function wc_var_save_price_field($variation_id, $i) {
        global $post;
        if( isset($_POST['variable_selling_price'][$i]) ) {
            $product = wc_get_product( $variation_id);
            $price_ = $product->get_price();
            //delete_post_meta($variation_id,'variable_selling_price' );
            //add_post_meta( $variation_id, 'variable_selling_price',$price_ );
        }

        $prod_site  = new Woo_Mcp_Product_Site($post->ID, $this->curr_blog_id);
        $slave_products = $prod_site->get_slave_by_master($post->ID);

        if( !empty($slave_products)){
            foreach($slave_products as $slave){
                switch_to_blog($slave->network_id);
                $prod = new Woo_Mcp_Product($slave->product_id_slave, $slave->network_id);
                $var  = $prod->get_variation_by_master($variation_id, $slave->product_id_slave);

                if(!empty($var) && !is_null($var)) {
                    $variation_id = $var['ID'];

                    $variable_manage_stock          = isset( $_POST['variable_manage_stock'] ) ? $_POST['variable_manage_stock'] : array();
                    $variable_stock                 = isset( $_POST['variable_stock'] ) ? $_POST['variable_stock'] : array();
                    $variable_backorders            = isset( $_POST['variable_backorders'] ) ? $_POST['variable_backorders'] : array();
                    $variable_stock_status          = isset( $_POST['variable_stock_status'] ) ? $_POST['variable_stock_status'] : array();

                    $manage_stock        = isset( $variable_manage_stock[ $i ] ) ? 'yes' : 'no';

                    update_post_meta( $variation_id, '_manage_stock', $manage_stock );
                    if ( ! empty( $variable_stock_status[ $i ] ) ) {
                        wc_update_product_stock_status( $variation_id, $variable_stock_status[ $i ] );
                    }

                    if ( 'yes' === $manage_stock ) {
                        update_post_meta( $variation_id, '_backorders', wc_clean( $variable_backorders[ $i ] ) );
                        wc_update_product_stock( $variation_id, wc_stock_amount( $variable_stock[ $i ] ) );
                    } else {
                        delete_post_meta( $variation_id, '_backorders' );
                        delete_post_meta( $variation_id, '_stock' );
                    }

                }
                restore_current_blog();
            }
        }

    }

    public function woo_mcp_update_table() {
        global $wpdb;
        $sql = "TRUNCATE TABLE ".$this->createTableName('mcp_product_to_sites');
        $wpdb->query($sql);
    }

    public function woo_mcp_get_content() {
        $data = $_POST;
        parse_str(($data['data']), $values);
        $slaves = $values['sites'];
        include_once( 'partials/woo-mcp-push-confirmation.php' );
        exit();
        die();

    }

    public function woo_mcp_get_prod_content() {
        $data = $_POST;
        parse_str(($data['data']), $values);
        $slaves = $values['sites'];
        include_once( 'partials/woo-mcp-deactivate-confirmation.php' );
        exit();
        die();
    }

    public function woo_mcp_deactivate_products(){
        $data = $_POST;

        parse_str(($data['data']), $values);
        $slaves = $values['sites'];

        foreach($slaves as $slave){
            foreach($values['products'] as $key=>$value){
                $prodtosite = new Woo_Mcp_Product_Site($value, $slave);
                $id_in_slave = $prodtosite->get_product_id_slave();


                switch_to_blog($slave);

                $parent_product = array(
                    'ID'           => $id_in_slave,
                    'post_status' => 'pending'
                );

                // Update the post into the database
                wp_update_post( $parent_product);

                if (is_plugin_active('sitepress-multilingual-cms/sitepress.php') || is_plugin_active('sitepress-multilingual-cms 2/sitepress.php')) {
                    if(icl_object_id ($id_in_slave, "product", false, "fr")){
                        $trans_id = icl_object_id ($id_in_slave, "product", false, "fr");
                        $trans_product = array(
                            'ID'           => $trans_id,
                            'post_status' => 'pending'
                        );
                        wp_update_post( $trans_product);
                    }
                }


                restore_current_blog();

                $history = null;

                if(!empty($prodtosite->get_data_by_main_id($value)[0]->history)){
                    $history = unserialize($prodtosite->get_data_by_main_id($value)[0]->history);

                }

                $history[] = array(
                    'title'=>'Product discontinued on the slave site',
                    'date'=>current_time( 'mysql' ),
                    'price'=> ''
                );

                $history = !empty($history) ? serialize($history) : $history;

                $data_to_site = array('pushed_modified'=> current_time( 'mysql' ), 'history'=>$history, 'status_on_slave'=>0);
                $prodtosite->update_history($data_to_site);
            }
        }
        exit();
        die();
    }

    public function woo_mcp_reset_points(){
        $data = $_POST;

        parse_str(($data['data']), $values);
        $slaves = $values['sites'];

        foreach($slaves as $slave){
            foreach($values['products'] as $key=>$value){
                $prodtosite = new Woo_Mcp_Product_Site($value, $slave);
                $prod = new Woo_Mcp_Product($value, $slave);
                $id_in_slave = $prodtosite->get_product_id_slave();
                $meta_data['_price'][0] = $values['_product_price'][$slave][$key];
                $meta_data['_regular_price'][0] = $values['_product_price'][$slave][$key];

                switch_to_blog($slave);

                $prod->process_meta($meta_data, $id_in_slave);

                restore_current_blog();

                $history = null;

                if(!empty($prodtosite->get_data_by_main_id($value)[0]->history)){
                    $history = unserialize($prodtosite->get_data_by_main_id($value)[0]->history);

                }

                $history[] = array(
                    'title'=>'Points reset',
                    'date'=>current_time( 'mysql' ),
                    'price'=> $values['_product_price'][$slave][$key]
                );

                $history = !empty($history) ? serialize($history) : $history;

                $data_to_site = array('pushed_modified'=> current_time( 'mysql' ), 'history'=>$history, 'status_on_slave'=>1);
                $prodtosite->update_history($data_to_site);
            }
        }
        exit();
        die();
    }

    public function woo_mcp_insert_category() {
        $data = $_POST;

        parse_str(($data['data']), $values);
        $my_cat = array('cat_name' => $values['product_cat_name'], 'category_parent' => $values['product_cat_parent'], 'taxonomy' => 'product_cat');

        // Create the category
        $my_cat_id = wp_insert_category($my_cat);
        wp_set_object_terms($values['post_id'],$my_cat_id, 'product_cat', true );

        //check if has translation
        if (is_plugin_active('sitepress-multilingual-cms/sitepress.php') || is_plugin_active('sitepress-multilingual-cms 2/sitepress.php')) {
            $fr_id = icl_object_id((int)$values['post_id'], 'product', false,'fr');
            if($fr_id){
                //get french trans of product cat
                $trans_el = icl_object_id($my_cat_id, 'product_cat',false,'fr');
                if($trans_el) {
                    $res = wp_set_object_terms($fr_id,$trans_el, 'product_cat', true );
                }
            }
        }


        $term_data = get_term_by( 'id', $my_cat_id, 'product_cat');
        $html = '<li class="product_cat-'.$my_cat_id.'"><label><input name="product_cat['.$values['slave'].'][]" value="'.$my_cat_id.'" type="checkbox" />'.$term_data->name.'</label><ul class="children"></ul></li>';
        $response = array(
            'id'   => $values['product_cat_parent'],
            'data'    => $html,
            'supplemental'=> array('cat_id'=>$my_cat_id)
        );
        $ajax_resp = new WP_Ajax_Response($response);
        $ajax_resp->send();
    }

    public function woo_mcp_update_prod_cat() {
        $data = $_POST;

        if($data['data']['checked']){
            wp_set_object_terms((int)$data['data']['post_id'],(int)$data['data']['id'], 'product_cat', true );
            //check if has translation
            if (is_plugin_active('sitepress-multilingual-cms/sitepress.php') || is_plugin_active('sitepress-multilingual-cms 2/sitepress.php')) {
                $fr_id = icl_object_id((int)$data['data']['post_id'], 'product', false,'fr');
                if($fr_id){
                    //get french trans of product cat
                    $trans_el = icl_object_id((int)$data['data']['id'], 'product_cat',false,'fr');
                    if($trans_el) {
                        $res = wp_set_object_terms($fr_id,$trans_el, 'product_cat', true );
                    }
                }
            }

        } else {
            wp_remove_object_terms( (int)$data['data']['post_id'],(int)$data['data']['id'] , 'product_cat' );

            //check if has translation
            if (is_plugin_active('sitepress-multilingual-cms/sitepress.php') || is_plugin_active('sitepress-multilingual-cms 2/sitepress.php')) {
                $fr_id = icl_object_id((int)$data['data']['post_id'], 'product', false,'fr');
                if($fr_id){
                    //get french trans of product cat
                    $trans_el = icl_object_id((int)$data['data']['id'], 'product_cat',false,'fr');
                    if($trans_el) {
                        wp_remove_object_terms( $fr_id,$trans_el, 'product_cat' );
                    }
                }
            }
        }
        exit();
    }

    public function products_filter($q) {
        $meta_query = $q->get( 'meta_query' );
        $meta_query[] = array(
            'key'       => '_price',
            'value'     => 0,
            'compare'   => '>'
        );

        $q->set( 'meta_query', $meta_query );
    }

    public function woo_mcp_sale_form() {
        $data = $_POST;
        $product_id = $data['product_id'];
        $slave = $data['slave'];
        include_once( 'partials/woo-mcp-sale-form.php' );
        exit();
        die();
    }

    public function remove_object_terms($terms,$objectid,$tax){
        wp_remove_object_terms( (int)$objectid, array_map('intval', $terms), $tax );
    }

    public function remove_object_terms_french($terms,$objectid,$tax){
        wp_remove_object_terms( (int)$objectid, array_map('intval', $terms), $tax );

        if (is_plugin_active('sitepress-multilingual-cms/sitepress.php') || is_plugin_active('sitepress-multilingual-cms 2/sitepress.php')) {
            $fr_id = icl_object_id((int)$objectid, 'product', false,'fr');
            if($fr_id){
                foreach($terms as $id){
                    $trans_el = icl_object_id($id, 'product_cat',false,'fr');
                    if($trans_el) {
                        wp_remove_object_terms( $fr_id,$trans_el, 'product_cat' );
                    }
                }
            }
        }

    }

    public function woo_mcp_variation_prices() {
        $data = $_POST;
        $product_id = $data['product_id'];
        $slave = $data['slave'];
        include_once( 'partials/woo-var-prices.php' );
        exit();
        die();
    }

}

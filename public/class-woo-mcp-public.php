<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://cto2go.ca/
 * @since      1.0.0
 *
 * @package    Woo_Mcp
 * @subpackage Woo_Mcp/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woo_Mcp
 * @subpackage Woo_Mcp/public
 * @author     cto2go <INFO@CTO2GO.CA>
 */
class Woo_Mcp_Public extends Woo_Mcp_base {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-mcp-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-mcp-public.js', array( 'jquery' ), $this->version, false );

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

    public function update_inventory($order_id ){
        $current_blog  = get_current_blog_id();
        $settings = $this->getSettings();
        $order = new WC_Order( $order_id );
        $items = $order->get_items();

        if ( 'yes' == get_option('woocommerce_manage_stock') && sizeof( $items ) > 0 ) {
            foreach($items as $item){
                $product_id = $item['product_id'];
                $prod_site = new Woo_Mcp_Product_Site($product_id,$current_blog);
                if($current_blog == (int)$settings['woo-mcp_master']) {
                     $sites = $prod_site->get_slave_by_master($product_id);
                } else {
                     $sites = $prod_site->get_siblings_slave($product_id);
                        if( !empty($sites)){
                            $master_data = (object) array('product_id_slave'=> $sites[0]->product_id_main, 'network_id'=>$settings['woo-mcp_master']);
                            $sites[] = $master_data;
                        }

                }

                $var_ptype = get_post_type( $item['variation_id'] );

                if( !empty($sites)){
                    foreach($sites as $site){
                        switch_to_blog($site->network_id);
                        $prod_site = new Woo_Mcp_Product($site->product_id_slave,$site->network_id);

                        if ( ! empty( $item['variation_id'] ) && 'product_variation' === $var_ptype ) {
                            $var = $prod_site->get_variation_by_master($item['variation_id'] ,$site->product_id_slave );
                            if(!empty($var)){
                                $prod = wc_get_product($var['ID']);
                            }

                        } else {
                            $prod = wc_get_product($site->product_id_slave);
                        }

                        if ( $prod && $prod->exists() && $prod->managing_stock() ) {
                            $qty       = apply_filters( 'woocommerce_order_item_quantity', $item['qty'], $this, $item );
                            $new_stock = $prod->reduce_stock($qty);
                        }

                        restore_current_blog();
                    }
                }
            }
        }

    }

}

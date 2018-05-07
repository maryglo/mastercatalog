<?php

class Woo_Mcp_Product_Site {

    /** @var int The product (post) ID. */
    public $id;

    /** @var object The actual post object. */
    public $post;

    /** @var int The network id  */
    private $network;

    /** @var  The base class */
    private $base;

    public function __construct( $product, $network ) {

        if ( is_numeric( $product ) ) {
            $this->id   = absint( $product );
            $this->post = get_post( $this->id );
            $this->post_arr =  get_post($this->id, ARRAY_A);
        } elseif ( $product instanceof WC_Product ) {
            $this->id   = absint( $product->id );
            $this->post = $product;
        } elseif ( $product instanceof WP_Post || isset( $product->ID ) ) {
            $this->id   = absint( $product->ID );
            $this->post = $product;
        }

        $this->network = $network;

        $this->base = new Woo_Mcp_base();


    }

    /**
     * @param $network
     * @return null|string
     */
    public function get_count_pushed($network) {
        global $wpdb;
        $table_name = $this->base->createTableName('mcp_product_to_sites');
        $pushed = $wpdb->get_var( $wpdb->prepare(
            "
		SELECT COUNT(*)
		FROM $table_name
		WHERE product_id_main = %d AND network_id = %d
	    ",
            $this->id, $network
        ) );

        return $pushed;
    }

    /**
     * @param $product_id
     * @return null|string
     */
    public function check_slave_pushed($product_id) {
        global $wpdb;
        $table_name = $this->base->createTableName('mcp_product_to_sites');
        $pushed = $wpdb->get_var( $wpdb->prepare(
            "
		SELECT COUNT(*)
		FROM $table_name
		WHERE product_id_slave = %d
	    ",
            $product_id
        ) );

        return $pushed;
    }

    /**
     * @return null|string
     */
    public function get_product_id_slave() {
        global $wpdb;
        $table_name = $this->base->createTableName('mcp_product_to_sites');
        $get_product_id_slave = $wpdb->get_var( $wpdb->prepare(
            "
		SELECT product_id_slave
		FROM $table_name
		WHERE product_id_main = %d AND network_id = %d
	    ",
            $this->id, $this->network
        ) );

        return $get_product_id_slave;
    }

    /**
     * @return string
     */
    public function get_status() {
        global $wpdb;
        $table_name = $this->base->createTableName('mcp_product_to_sites');

        $pushed = $wpdb->get_var( $wpdb->prepare(
            "
		SELECT COUNT(*)
		FROM $table_name
		WHERE product_id_main = %d
	    ",
            $this->id
        ) );

        $status = "New";

        if($pushed > 0) {
            $status = 'Pushed';
        }

        return $status;
    }

    /**
     * @param $data
     */
    public function insert_prod_to_sites($data) {
        global $wpdb;
        $table_name = $this->base->createTableName('mcp_product_to_sites');

        $res = $wpdb->insert(
            $table_name,
            $data,
            array(
                '%d',
                '%s',
                '%d',
                '%d',
                '%s',
                '%s',
                '%s',
                '%d'
            )
        );

    }

    public function update_prod_to_sites($data) {
        global $wpdb;
        $table_name = $this->base->createTableName('mcp_product_to_sites');
        $wpdb->update(
            $table_name,
            $data,
            array( 'product_id_main' => $this->id, 'network_id' => $this->network  ),
            array(
                '%s',
                '%s',
                '%s',
                '%d',
                '%d'
            ),
            array( '%d', '%d' )
        );
    }

    public function update_history($data) {
        global $wpdb;
        $table_name = $this->base->createTableName('mcp_product_to_sites');
        $wpdb->update(
            $table_name,
            $data,
            array( 'product_id_main' => $this->id, 'network_id' => $this->network  ),
            array(
                '%s',
                '%s',
                '%d'
            ),
            array( '%d', '%d' )
        );
    }

    /**
     * @param $product_id
     * @return null|string
     */
    public function get_slave_by_master($product_id) {
        global $wpdb;
        $table_name = $this->base->createTableName('mcp_product_to_sites');
        $res = $wpdb->get_results( $wpdb->prepare(
            "
		SELECT *
		FROM $table_name
		WHERE product_id_main = %d
	",
            $product_id
        ) );
        return $res;
    }

    public function get_data_by_main_id($product_id) {
        global $wpdb;
        $table_name = $this->base->createTableName('mcp_product_to_sites');
        $res = $wpdb->get_results( $wpdb->prepare(
            "
		SELECT *
		FROM $table_name
		WHERE product_id_main = %d AND network_id = %d
	",
            $product_id,$this->network
        ) );
        return $res;
    }

    /**
     * @param $product_id
     * @return mixed
     */
    public function get_master_by_slave($product_id) {
        global $wpdb;
        $table_name = $this->base->createTableName('mcp_product_to_sites');
        $res = $wpdb->get_results( $wpdb->prepare(
            "
		SELECT *
		FROM $table_name
		WHERE product_id_slave = %d
	",
            $product_id
        ) );
        return $res;
    }

    /**
     * @param $product_id
     */
    public function get_siblings_slave($product_id) {
        global $wpdb;
        $table_name = $this->base->createTableName('mcp_product_to_sites');
        $res = $wpdb->get_results('SELECT * from  '.$table_name.' where product_id_main in (SELECT product_id_main FROM wp_mcp_product_to_sites WHERE product_id_slave ='.$product_id.') and product_id_slave != '.$product_id);
        return $res;
    }


    public function get_unique_products(){
        global $wpdb;
        $table_name = $this->base->createTableName('mcp_product_to_sites');
        $res = $wpdb->get_results('Select DISTINCT product_id_main from '.$table_name, ARRAY_N);
        return $res;
    }
}
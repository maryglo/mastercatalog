<?php
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Woo_Mcp_Products_Table extends WP_List_Table {


    private $products;

    private $plugin_name;

    private $master;


    function __construct($products = null, $plugin){
        global $status, $page;


        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'product',     //singular name of the listed records
            'plural'    => 'products'   //plural name of the listed records
        ) );

        add_filter( 'request', array( $this, 'custom_columns_orderby' ) );

        $this->products = $products;
        $this->plugin_name = $plugin;
        $this->master = get_site_option($this->plugin_name."_master");
    }

    function extra_tablenav( $which ) {
        if ( $which == "top"){
            $goto = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            ?>
            <br class="clear">
            <div class="wrap_dropdown">
                <div class="alignleft actions">
                    <select name="action">
                        <option selected="selected" value="">Bulk Actions</option>
                        <option value="post_to_selected">Post to Selected Sites</option>
                        <option value="discontinue">Discontinue on Sites</option>
                    </select>
                    <input type="submit" value="Apply" class="button action bulkaction" name="">
                </div>
                <div class="view_posts_by actions">
                    <select name="per_page" class="per_page">
                        <option value="<?php echo remove_querystring_var($goto, 'view_by');?>">View All</option>
                        <option <?php  if(isset($_GET['view_by']) && $_GET['view_by'] != "" && $_GET['view_by'] == 'new'){ ?>selected="selected" <?php } ?> value="<?php echo remove_querystring_var($goto, 'view_by').'&view_by=new';?>">New</option>
                        <option <?php  if(isset($_GET['view_by']) && $_GET['view_by'] != "" && $_GET['view_by'] == 'pushed'){ ?>selected="selected" <?php } ?> value="<?php echo remove_querystring_var($goto, 'view_by').'&view_by=pushed';?>">Pushed</option>
                    </select>
                </div>
                <div class="products-per-page">
                    <span>Products Per Page:</span>
                    <select name="per_page" class="per_page">
                        <option value="<?php echo remove_querystring_var($goto, 'per_page').'&per_page=25';?>">25</option>
                        <?php for($i= 5;$i < 30; $i+=5) { ?>
                            <option <?php  if(isset($_GET['per_page']) && $_GET['per_page'] != "" && $_GET['per_page'] == $i){ ?>selected="selected" <?php } ?> value="<?php echo remove_querystring_var($goto, 'per_page').'&per_page='.$i;?>"><?php echo $i; ?></option>
                        <?php  } ?>
                        <option <?php  if(isset($_GET['per_page']) && $_GET['per_page'] != "" && $_GET['per_page'] == 'all'){ ?>selected="selected" <?php } ?> value="<?php echo remove_querystring_var($goto, 'per_page').'&per_page=all';?>">All</option>
                    </select>
                </div>
            </div>
        <?php
        }

        if ( $which == "bottom"){

            ?>

        <?php
        }
    }

    function column_default($item, $column_name){
        global $wpdb;
        $pr_site = new Woo_Mcp_Product_Site($item->ID, $this->master);
        $product = wc_get_product( $item->ID );
        $results = $wpdb->get_results('SELECT * FROM wp_mcp_product_to_sites WHERE product_id_main = '.$item->ID);
        $deactivated = $wpdb->get_results('SELECT network_name, history FROM wp_mcp_product_to_sites WHERE product_id_main = '.$item->ID. ' AND status_on_slave= 0 ');

        switch($column_name){
            case 'feat_img':
                return $product->get_image('150x150');
            case 'product_name':
                $html =  '<strong><a href="' . get_edit_post_link( $item->ID, true ) . '" class="row-title">'.$item->post_title.'</a></strong>';
                if($results){
                    $html .= '<p class="pushed_label">Pushed to:</p><ul class="pushed_info">';
                    foreach($results as $result){
                        $pushed = date('m/d/Y', strtotime($result->pushed_date));
                        $updated = $result->pushed_modified != "" ? ' &mdash; Updated :<abbr class="date" title="'.$result->pushed_modified.'">'.date('m/d/Y', strtotime($result->pushed_modified)).'</abbr>' : "";
                        $pushed = '&#8212;Pushed :<abbr class="date" title="'.$result->pushed_date.'">'.$pushed.'</abbr>';
                        $html .= '<li>'.$result->network_name.$pushed.$updated.'</li>';
                    }
                    $html .= '</ul>';
                }

                if($deactivated){

                    $html .= '<p class="deactivated_label">Discontinued on:</p><ul class="pushed_info">';
                    foreach($deactivated as $d){
                        $h = unserialize($d->history);
                        $count_data = count($h['Product discontinued on the slave site']);
                        $date = date('m/d/Y', strtotime($h['Product discontinued on the slave site'][$count_data -1]['date']));
                        $d_date = '&#8212;<abbr class="date" title="'.$h['Product discontinued on the slave site'][$count_data -1]['date'].'">'.$date.'</abbr>';
                        $html .= '<li>'.$d->network_name.$d_date.'</li>';
                    }
                    $html .= '</ul>';
                }

                return $html;
            case 'id':
                return $item->ID;
            case 'unit_cost':
                $price = $product->get_price();
                if($product->has_child() && $product->product_type == 'variable'){
                    $price = get_post_meta($product->get_children()[0], '_regular_price', true);
                }
                return "<span>".get_woocommerce_currency_symbol().$price."</span>";
            case 'price':
                return "<span>".get_woocommerce_currency_symbol().$product->get_price()."</span>";
            case 'status' :
                return $pr_site->get_status();
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_feat_img($item) {
        $product = wc_get_product( $item->ID );
        return $product->get_image();
    }

    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="products[]" value="%s" />', $item->ID
        );
    }

    public function get_columns() {
        $columns = array(
            'cb'           => '<input type="checkbox" />',
            'feat_img'     =>'<span class="wc-image tips">Image</span>',
            'product_name' => 'Product Name',
            'id'           => 'ID',
            'unit_cost'    => 'Unit Cost',
            'price'        =>'Price',
            'status'       => 'Status',
        );
        return $columns;
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'product_name'    => array('product_name',true),
            'id'  => array('id',true),
            'unit_cost' => array('unit_cost', false),
            'price'     =>array('price',false),

        );
        return $sortable_columns;
    }

    public function custom_columns_orderby( $vars ) {

        if ( isset( $vars['orderby'] ) ) {
            if ( 'price' == $vars['orderby'] ||  'unit_cost' == $vars['orderby']) {
                $vars = array_merge( $vars, array(
                    'meta_key' 	=> '_price',
                    'orderby' 	=> 'meta_value_num'
                ) );
            }
        }

        return $vars;
    }

    function prepare_items() {
        if(isset($_GET['per_page']) && $_GET['per_page'] != ""){
            $per_page = $_GET['per_page'] == 'all' ? -1 : $_GET['per_page'];
        } else {
            $per_page = 25;
        }
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
        $current_page = $this->get_pagenum();
        $found_data = isset($_GET['per_page']) && $_GET['per_page'] == 'all' ? $this->products->posts :  array_slice( $this->products->posts,( ( $current_page-1 )* $per_page ), $per_page );
        $this->set_pagination_args( array(
            'total_items' => count($this->products->posts),                  //WE have to calculate the total number of items
            'per_page'    => $per_page                    //WE have to determine how many items to show on a page
        ) );

        $this->items = $found_data;
    }
}
<?php

class Woo_Mcp_Product {

    /** @var int The product (post) ID. */
    public $id;

    /** @var object The actual post object. */
    public $post;

    /** @var array Taxonomy of the post. */
    public $taxonomies;

    /** @var int The slave site id  */
    private $slave;

    public function __construct( $product, $slave ) {

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

        $this->taxonomies = $this->taxonomies();

        $this->slave = $slave;
    }

    /**
     * @return array|WP_Post
     */
    public function clean_data($id= null){

        if(is_null($id)){
            $prod_to_site = new Woo_Mcp_Product_Site($this->id, $this->slave);
            $slave = $prod_to_site->get_product_id_slave();

            if(!empty($slave)){
                $slave_data = get_post($slave);
                $data = $this->post_arr;
                $data['ID'] = $slave;
                if(empty($slave_data) || is_null($slave_data)){
                    unset($data['ID']);
                }
                $data['post_status'] = get_post_status($slave);

            } else {
                $data = $this->post_arr;
                unset($data['guid']);
                unset($data['ID']);
            }

        }  else {

            $data  = get_post(absint( $id ), ARRAY_A);
        }

        return $data;
    }

    /**
     * @param $id
     */
    public function process_meta($data, $id){

        foreach($data as $key=>$value) {

            if(unserialize($value[0])){
                $meta_val = unserialize($value[0]);
            } else {
                $meta_val = $value[0];
            }

            //delete_post_meta($id, $key);
            //add_post_meta( $id, $key, $meta_val );
            if ( ! add_post_meta( $id, $key, $meta_val, true ) ) {
                update_post_meta ( $id, $key, $meta_val);
            }
        }
    }

    /**
     * @param $data
     * @param $parentid
     * @param $override
     */
    public function process_variation($data, $parentid, $override, $parent_meta){
        $multiplier = get_site_option(WOOMCP_PLUGIN_NAME."_multiplier")[$this->slave] == "" ? 1 : get_site_option(WOOMCP_PLUGIN_NAME."_multiplier")[$this->slave];

        $args = array('post_parent' => $parentid,
            'post_type' => 'product_variation'
        );

        $post_attachments = get_children($args);
        if($post_attachments) {
            foreach ($post_attachments as $attachment) {
                wp_delete_attachment( get_post_thumbnail_id( $attachment->ID), true );
                wp_delete_attachment($attachment->ID, true);
                wp_delete_post($attachment->ID, true);
                wp_delete_post(get_post_thumbnail_id( $attachment->ID), true);

            }
        }

        if(!empty($data)){

            $reg_price = array_values($override['_regular_price'][0]);
            $sale_price = array_values($override['_sale_price'][0]);

            $min_regular_id = isset($parent_meta['is_translation']) && $parent_meta['is_translation'] ?  $this->get_icl_obj_master($parent_meta['_min_regular_price_variation_id'][0], 'product_variation', 'fr') : $parent_meta['_min_regular_price_variation_id'][0];
            $max_regular_id = isset($parent_meta['is_translation']) && $parent_meta['is_translation'] ?  $this->get_icl_obj_master($parent_meta['_max_regular_price_variation_id'][0], 'product_variation', 'fr') : $parent_meta['_max_regular_price_variation_id'][0];

            $min_price_id = isset($parent_meta['is_translation']) && $parent_meta['is_translation'] ?  $this->get_icl_obj_master($parent_meta['_min_price_variation_id'][0], 'product_variation', 'fr') : $parent_meta['_min_price_variation_id'][0];
            $max_price_id = isset($parent_meta['is_translation']) && $parent_meta['is_translation'] ?  $this->get_icl_obj_master($parent_meta['_max_regular_price_variation_id'][0], 'product_variation', 'fr') : $parent_meta['_max_regular_price_variation_id'][0];

            $min_sale_price_id = isset($parent_meta['is_translation']) && $parent_meta['is_translation'] ?  $this->get_icl_obj_master($parent_meta['_min_sale_price_variation_id'][0], 'product_variation', 'fr') : $parent_meta['_min_sale_price_variation_id'][0];
            $max_sale_price_id = isset($parent_meta['is_translation']) && $parent_meta['is_translation'] ?  $this->get_icl_obj_master($parent_meta['_max_sale_price_variation_id'][0], 'product_variation', 'fr') : $parent_meta['_max_sale_price_variation_id'][0];

            $array_var = array();

            if($parent_meta['is_translation']){
                //get english version of variable product
                $var_en = icl_object_id($parentid, 'product', false, 'en');
                //get english variations
                $args = array('post_parent' => $var_en,
                    'post_type' => 'product_variation',
                    'post_status'   => array( 'private', 'publish' ),
                    'numberposts'   => -1,
                    'orderby'       => 'ID',
                    'order'         => 'asc'
                );

                $post_attachments = get_children($args);
                $array_var = array_keys($post_attachments);
            }

            foreach($data as $key=>$variation) {
                $variation =  (array)$variation;
                $post_exist = post_exists($variation['post_title'],$variation['post_content'],$variation['post_date']);
                $variation['post_parent'] = $parentid;
                unset($variation['guid']);
                $meta = $this->get_meta_master($variation['ID']);
                $image_url = isset($meta['_thumbnail_id']) ? $this->getimage_url_master($meta['_thumbnail_id'][0]) : "";
                $_master_id = $variation['ID'];

                unset($variation['ID']);

                $id = wp_insert_post($variation, true);

                if($_master_id == $min_regular_id) {
                    $min_regular_id = $id;
                }

                if($_master_id == $max_regular_id) {
                    $max_regular_id = $id;
                }

                if($_master_id == $min_price_id) {
                    $min_price_id = $id;
                }

                if($_master_id == $max_price_id) {
                    $max_price_id = $id;
                }

                if($_master_id == $min_sale_price_id) {
                    $min_sale_price_id = $id;
                }

                if($_master_id == $max_sale_price_id) {
                    $max_sale_price_id = $id;
                }


                if(isset($meta['_thumbnail_id']) && !empty($image_url)) {

                    $this->process_image($image_url, $id);

                } else {

                    if($this->check_image($id)) {
                        wp_delete_attachment( get_post_thumbnail_id( $id), true );
                    }
                }
                unset($meta['_thumbnail_id']);
                $meta['_network_from'][0] = get_site_option(WOOMCP_PLUGIN_NAME."_master");
                $meta['_master_id'][0] = $_master_id;


                $meta['_price'][0] = $reg_price[$key];
                $meta['_regular_price'][0] = $reg_price[$key];
                $meta['_sale_price'][0] = $sale_price[$key];

                $meta['_sale_price_dates_from'][0] = $override['_sale_price_dates_from'][0][$_master_id] != "" ? strtotime($override['_sale_price_dates_from'][0][$_master_id] ) : "";
                $meta['_sale_price_dates_to'][0] = $override['_sale_price_dates_to'][0][$_master_id] != "" ? strtotime($override['_sale_price_dates_to'][0][$_master_id]) : "";


                if(isset($meta['_wcml_duplicate_of_variation'])){
                    $this->attach_as_translation($array_var[$key], $id, 'post_product_variation', 'fr');
                    $meta['_wcml_duplicate_of_variation'][0] = $array_var[$key];
                }

                $this->process_meta($meta, $id);

            }

            $var_price_fields = array('_min_regular_price_variation_id', '_max_regular_price_variation_id', '_min_price_variation_id', '_max_price_variation_id', '_min_sale_price_variation_id', '_max_sale_price_variation_id');
            $var_price_val = array($min_regular_id, $max_regular_id, $min_price_id, $max_price_id, $min_sale_price_id, $max_sale_price_id);

            foreach($var_price_fields as $key=>$val){
                delete_post_meta($parentid, $val);
                add_post_meta( $parentid, $val, $var_price_val[$key] );
            }

        }
    }

    /**
     * @param $data
     */
    public function process_attributes($data) {
        global $wpdb;

        foreach($data as $key=>$value) {
            if($value['is_taxonomy']){
                $name = str_replace('pa_', '', $key);
                $attr_data = $this->check_product_attr($name, $this->slave);

                if(empty($attr_data)){
                    $attr_data = $this->get_master_attr($name);

                    $attribute = array(
                        'attribute_label'   => $attr_data['attribute_label'],
                        'attribute_name'    => $attr_data['attribute_name'],
                        'attribute_type'    => $attr_data['attribute_type'],
                        'attribute_orderby' => $attr_data['attribute_orderby'],
                    );

                    $wpdb->insert( $wpdb->prefix . 'woocommerce_attribute_taxonomies', $attribute );

                    do_action( 'woocommerce_attribute_added', $wpdb->insert_id, $attribute );
                    delete_transient( 'wc_attribute_taxonomies' );

                } else {

                }
            }
        }
    }



    /**
     * check featured image
     */
    public function check_image($post_id = null) {
        $this->id = is_null($post_id) ? $this->id  : $post_id;
        if ( has_post_thumbnail( $this->id ) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $data
     * @param $parent
     */
    public function process_image($image, $parent) {

        $exists = check_file_exists($image);

        if($exists){
            $wp_upload_dir = wp_upload_dir(); // Set upload folder
            $image_data = @file_get_contents($image); // Get image data
            $filename   = basename($image); //

            // Check folder permission and define file location
            if( wp_mkdir_p(  $wp_upload_dir['path'] ) ) {
                $file = $wp_upload_dir['path'] . '/' . $filename;
            } else {
                $file =  $wp_upload_dir['basedir'] . '/' . $filename;
            }

            // Create the image  file on the server
            file_put_contents( $file, $image_data );

            $filetype = wp_check_filetype( basename( $filename ), null );

            $attachment = array(
                'post_mime_type' => $filetype['type'],
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            //check if a slave product has existing featured image
            $post_thumbnail_id = get_post_thumbnail_id( $parent );

            if(!(empty($post_thumbnail_id))) {
                $attachment['ID'] = $post_thumbnail_id;
                wp_update_post( $attachment );
                $attach_id = $post_thumbnail_id;
            } else {
                $attach_id = wp_insert_attachment( $attachment, $file, $parent );
            }

            //Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            //Generate the metadata for the attachment, and update the database record.
            $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
            wp_update_attachment_metadata( $attach_id, $attach_data );
            update_attached_file( $attach_id, $file );
            set_post_thumbnail($parent, $attach_id );
        }

    }

    /**
     * @return array
     */
    public function taxonomies() {
        global $sitepress;
        $object = get_object_taxonomies( $this->post, 'objects' );
        $terms = array();
        foreach($object as $key=>$value) {
            $res = wp_get_post_terms($this->id,$key, array("fields" => "all"));
            $terms = array_merge($terms, $res);
        }


        return $terms;
    }


    /**
     * @param null $taxonomies
     * @param $post_id
     * @param $blog_id
     */
    public function process_terms_($taxonomies= null, $post_id, $blog_id, $include=array(), $lang) {
        $tax = is_null($taxonomies) ? $this->taxonomies : $taxonomies;
        $include = $include ? $include : array();
        $all_tax = get_post_taxonomies( $post_id );

        wp_delete_object_term_relationships( $post_id,'product_type');

        foreach ($tax as $onomies) {
            //if((in_array($onomies->term_id, $include) && $onomies->taxonomy == 'product_cat') || $onomies->taxonomy != 'product_cat' || $lang == 'fr'){
                //var_dump($onomies->name);
                //term_taxonomy_id
                $exist = absint(term_exists($onomies->name, $onomies->taxonomy));
                $exist_slug = get_term_by('slug', $onomies->slug, $onomies->taxonomy);

                if($onomies->parent == 0) {
                    $args = array('description'=>$onomies->description);
                } else {
                    $parent_slug = $this->get_parent_term($onomies->parent);
                    $parent_id = $this->get_parent_termby_slug('slug',$parent_slug['slug'],$blog_id);
                    $args = array('parent'=> $parent_id['term_id'], 'description'=>$onomies->description);
                }

                if($exist !== 0 && $exist !== null && $exist_slug) {
                    $term_id = absint($exist_slug->term_id);
                    if (is_plugin_active('sitepress-multilingual-cms/sitepress.php') || is_plugin_active('sitepress-multilingual-cms 2/sitepress.php')) {
                        $trans_el = get_icl_obj_id($term_id, $exist_slug->taxonomy,'fr');

                    }

                    $res = wp_update_term($exist_slug->term_id,  $onomies->taxonomy, array(
                        'name' => $onomies->name,
                        'slug' => $exist_slug->slug,
                        'description' => $onomies->description
                    ));

                    if (is_plugin_active('sitepress-multilingual-cms/sitepress.php') || is_plugin_active('sitepress-multilingual-cms 2/sitepress.php')) {
                        if($trans_el != $exist_slug->term_id &&  langcode_post_id($term_id) == 'en') {
                            global $sitepress;
                            $trid = get_trid($exist_slug->term_taxonomy_id,  "tax_".$exist_slug->taxonomy);
                            //associate the translated term to the original
                            $sitepress->set_element_language_details($trans_el, "tax_".$onomies->taxonomy, $trid, 'fr', $sitepress->get_default_language());
                        }
                    }

                } else {
                    $args['slug'] = $onomies->slug;
                    $res = wp_insert_term( $onomies->name, $onomies->taxonomy, $args);
                    $term_id = $res['term_id'];

                }

                //check if it is a translation && wpml is active
                if (is_plugin_active('sitepress-multilingual-cms/sitepress.php') || is_plugin_active('sitepress-multilingual-cms 2/sitepress.php')) {
                    $trans_data = $this->get_trans($onomies->term_id,$onomies->taxonomy);
                    if($trans_data){
                        //attached as translation
                        $parent_term = get_term_by('slug', $trans_data['slug'], $trans_data['taxonomy']);
                        global $sitepress;
                        $trid = get_trid($parent_term->term_taxonomy_id,  "tax_".$parent_term->taxonomy);
                        //associate the translated term to the original
                        $sitepress->set_element_language_details($res['term_taxonomy_id'], "tax_".$onomies->taxonomy, $trid, 'fr', $sitepress->get_default_language());
                    }
                }

                if(!(has_term( $term_id, $onomies->taxonomy, $post_id ))){
                    wp_cache_delete( $post_id, $onomies->taxonomy . '_relationships' );
                    wp_set_object_terms($post_id, $term_id, $onomies->taxonomy,true);

                    /*$res = $wpdb->insert( $wpdb->prefix .'term_relationships', array('object_id'=>$post_id,'term_taxonomy_id'=>$term_tax_id, 'term_order'=>0) );*/
                }
            //}
        }
    }

    public function process_terms_french($taxonomies= null, $post_id, $blog_id, $include=array()) {

        $tax = is_null($taxonomies) ? $this->taxonomies : $taxonomies;

        wp_delete_object_term_relationships( $post_id,'product_type');

        global $sitepress;
        $sitepress->switch_lang('fr');

        foreach ($tax as $onomies) {

            $exist = absint(term_exists($onomies->name, $onomies->taxonomy));
            $exist_slug = get_term_by('slug', $onomies->slug, $onomies->taxonomy);

            if($onomies->parent == 0) {
                $args = array('description'=>$onomies->description);
            } else {
                $parent_slug = $this->get_parent_term($onomies->parent);
                $parent_id = $this->get_parent_termby_slug('slug',$parent_slug['slug'],$blog_id);
                $args = array('parent'=> $parent_id['term_id'], 'description'=>$onomies->description);
            }

            if($exist !== 0 && $exist !== null && $exist_slug) {
                $term_id = absint($exist_slug->term_id);
                if (is_plugin_active('sitepress-multilingual-cms/sitepress.php') || is_plugin_active('sitepress-multilingual-cms 2/sitepress.php')) {
                    $trans_el = get_icl_obj_id($term_id, $exist_slug->taxonomy,'fr');
                }

                $res = wp_update_term($exist_slug->term_id,  $onomies->taxonomy, array(
                    'name' => $onomies->name,
                    'slug' => $exist_slug->slug,
                    'description' => $onomies->description
                ));

                if (is_plugin_active('sitepress-multilingual-cms/sitepress.php') || is_plugin_active('sitepress-multilingual-cms 2/sitepress.php')) {
                    if($trans_el != $exist_slug->term_id &&  langcode_post_id($term_id) == 'en') {
                        global $sitepress;
                        $trid = get_trid($exist_slug->term_taxonomy_id,  "tax_".$exist_slug->taxonomy);
                        //associate the translated term to the original
                        $sitepress->set_element_language_details($trans_el, "tax_".$onomies->taxonomy, $trid, 'fr', $sitepress->get_default_language());
                    }
                }

            } else {

                $args['slug'] = $onomies->slug;
                $res = wp_insert_term( $onomies->name, $onomies->taxonomy, $args);
                $term_id = $res['term_id'];

            }

            //check if it is a translation && wpml is active
            if (is_plugin_active('sitepress-multilingual-cms/sitepress.php') || is_plugin_active('sitepress-multilingual-cms 2/sitepress.php')) {
                $trans_data = $this->get_trans($onomies->term_id,$onomies->taxonomy);
                if($trans_data){
                    //attached as translation
                    $parent_term = get_term_by('slug', $trans_data['slug'], $trans_data['taxonomy']);
                    global $sitepress;
                    $trid = get_trid($parent_term->term_taxonomy_id,  "tax_".$parent_term->taxonomy);
                    //associate the translated term to the original
                    $sitepress->set_element_language_details($res['term_taxonomy_id'], "tax_".$onomies->taxonomy, $trid, 'fr', $sitepress->get_default_language());
                }
            }

            if(!(has_term( $term_id, $onomies->taxonomy, $post_id ))){
                wp_cache_delete( $post_id, $onomies->taxonomy . '_relationships' );
                wp_set_object_terms($post_id, $term_id, $onomies->taxonomy,true);

                /*$res = $wpdb->insert( $wpdb->prefix .'term_relationships', array('object_id'=>$post_id,'term_taxonomy_id'=>$term_tax_id, 'term_order'=>0) );*/
            }
            //}
        }
    }

    public function process_gallery($gallery_url, $parent) {
        if(!(empty($gallery_url)) && !(is_null($gallery_url))){
            $gallery_im = array();
            foreach($gallery_url as $url){
                if($url != "" && check_file_exists($url)){
                    $wp_upload_dir = wp_upload_dir(); // Set upload folder
                    $filename   = basename($url);

                    // Check folder permission and define file location
                    if( wp_mkdir_p(  $wp_upload_dir['path'] ) ) {
                        $file = $wp_upload_dir['path'] . '/' . $filename;
                    } else {
                        $file =  $wp_upload_dir['basedir'] . '/' . $filename;
                    }

                    $post_exist = post_exists(preg_replace( '/\.[^.]+$/', '', basename( $filename ) ));

                    if($post_exist == 0) {
                        $image_data = file_get_contents($url); // Get image data
                        // Create the image  file on the server
                        file_put_contents( $file, $image_data );
                        $filetype = wp_check_filetype( basename( $filename ), null );
                        $attachment = array(
                            'post_mime_type' => $filetype['type'],
                            'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                            'post_content'   => '',
                            'post_status'    => 'inherit'
                        );

                        $attach_id = wp_insert_attachment( $attachment, $file, $parent );
                        //Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
                        require_once( ABSPATH . 'wp-admin/includes/image.php' );
                        //Generate the metadata for the attachment, and update the database record.
                        $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
                        wp_update_attachment_metadata( $attach_id, $attach_data );

                        $gallery_im[] = $attach_id;

                    } else {

                        $gallery_im[] = $post_exist;
                    }

                }

            }

            if(!empty($gallery_im)) {
                $prod_gallery = implode(',', $gallery_im);
                delete_post_meta($parent, '_product_image_gallery');
                add_post_meta( $parent, '_product_image_gallery', $prod_gallery);
            }
        }
    }

    /**
     * @param gallery id
     *
     * @param mixed $data
     */
    public function get_gallery_images($data) {
        $image = explode(',',$data);
        $img_url = array();

        foreach($image as $im){
            $image_url = wp_get_attachment_image_src($im, 'full');
            $image_url = $image_url[0];
            $img_url[] = $image_url;
        }

        return $img_url;
    }

    /**
     * @param $id
     * @return mixed
     */
    private function get_parent_term($id) {
        $master = get_site_option(WOOMCP_PLUGIN_NAME."_master");
        $db =  $master == 1 ? '' :  $master."_";
        switch_to_blog($master);

        global $wpdb;

        $table_name = $wpdb->base_prefix.$db."terms";

        $res = $wpdb->get_row( "SELECT slug FROM $table_name WHERE term_id = $id", ARRAY_A );

        restore_current_blog();

        return $res;


    }

    public function get_trans($id, $type){
        $master = get_site_option(WOOMCP_PLUGIN_NAME."_master");
        $db =  $master == 1 ? '' :  $master."_";
        switch_to_blog($master);

        $res =  icl_object_id ($id, $type, true, "en");
        $term = null;
        if($res != $id){
            $term = get_term( $res,  $type , ARRAY_A);
        }

        restore_current_blog();

        return $term;
    }

    /**
     * @param string $param
     * @param $val
     * @param $blog_id
     * @return mixed
     */
    private function get_parent_termby_slug($param='term_id',$val,$blog_id) {
        switch_to_blog($blog_id);
        $db =  $blog_id == 1 ? '' :  $blog_id."_";
        global $wpdb;

        $val = is_string($val) ? "'$val'" : $val;
        $table_name = $wpdb->base_prefix.$db."terms";

        $res = $wpdb->get_row( "SELECT term_id FROM $table_name WHERE $param = $val", ARRAY_A );

        restore_current_blog();

        return $res;


    }

    /**
     * @param $name
     * @param $blog_id
     * @return mixed
     */
    private function check_product_attr($name, $blog_id){
        $db = $blog_id == 1 ? '' : $blog_id."_";
        global $wpdb;

        $name =  "'$name'";
        $table_name = $wpdb->base_prefix.$db."woocommerce_attribute_taxonomies";

        $res = $wpdb->get_row( "SELECT * FROM $table_name WHERE attribute_name = $name", ARRAY_A );

        return $res;
    }

    /**
     * @param $name
     * @return mixed
     */
    private function get_master_attr($name) {
        $master = get_site_option(WOOMCP_PLUGIN_NAME."_master");
        $db = $master == 1 ? '' : $master."_";

        switch_to_blog($master);

        global $wpdb;

        $name =  "'$name'";
        $table_name = $wpdb->base_prefix.$db."woocommerce_attribute_taxonomies";
        $res = $wpdb->get_row( "SELECT * FROM $table_name WHERE attribute_name = $name", ARRAY_A );

        restore_current_blog();

        return $res;
    }

    /**
     * @param $id
     * @return mixed
     */
    private function get_meta_master($id) {
        $master = get_site_option(WOOMCP_PLUGIN_NAME."_master");
        switch_to_blog($master);
        $meta = get_post_meta($id);
        restore_current_blog();
        return $meta;
    }

    private function getimage_url_master($id) {
        $master = get_site_option(WOOMCP_PLUGIN_NAME."_master");

        switch_to_blog($master);
        $image_url = wp_get_attachment_image_src($id, 'full');
        $image_url = $image_url[0];
        restore_current_blog();

        return  $image_url;
    }

    public function get_variation_by_master($parent_var,$parent) {
        global $wpdb;
        $db = $this->slave == 1 ? '' : $this->slave."_";
        $table_name = $wpdb->base_prefix.$db."posts";
        $meta_table =  $wpdb->base_prefix.$db."postmeta";
        $results = $wpdb->get_row( "Select * from $table_name A JOIN $meta_table B on A.id = B.post_id where b.meta_value =" .$parent_var. " and post_parent = " .$parent. " and meta_key = '_master_id'", ARRAY_A );
        return $results;
    }

    public function attach_as_translation($orig, $post_id, $post_type, $lang) {
        global $sitepress;
        global $wpdb;
        $db = $this->slave == 1 ? '' : $this->slave."_";
        // Include WPML API
        include_once( WP_PLUGIN_DIR . '/sitepress-multilingual-cms/inc/wpml-api.php' );

        // Get trid of original post
        $trid_prepared = $wpdb->prepare(
            "SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_id=%d AND element_type=%s",
            array( $orig, $post_type )
        );
        $trid = $wpdb->get_var( $trid_prepared );

        // Get default language
        $default_lang = wpml_get_default_language();

        // Associate original post and translated post
        $wpdb->update( $wpdb->base_prefix.$db.'icl_translations', array( 'trid' => $trid, 'language_code' => $lang, 'source_language_code' => $default_lang ), array( 'element_id' => $post_id, 'element_type'=>$post_type) );
    }

    public function attach_element($id, $type) {
        global $sitepress;
        $trid_ = $sitepress->get_element_trid($id,  $type);
        echo "trans-".$trid_."<br />";
        $sitepress->set_element_language_details($id, $type, $trid_, 'fr', $sitepress->get_default_language());
    }

    public function get_icl_obj_master ($obj, $type, $lang) {
        $master = get_site_option(WOOMCP_PLUGIN_NAME."_master");

        switch_to_blog($master);

        $obj = icl_object_id ($obj, $type, false, $lang);

        restore_current_blog();

        return $obj;
    }
}
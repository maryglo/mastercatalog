<?php
function check_file_exists($url){
    $file_headers = @get_headers($url);
    if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
        $exists = false;
    }
    else {
        $exists = true;
    }

    return $exists;
}

function round_off($price){
    $round = $price;
    if($price >= 1 && $price <= 50){
        $round = 50;
    } else if($price > 50 && $price <= 100) {
        $round = 100;
    } else if ($price > 100){
        $remainder = $price % 100;
        $whole = $price - $remainder;
        $round = $whole + round_off($remainder);
    }

    return $round;
}

function remove_querystring_var($url, $key) {
    $url = preg_replace('/(.*)(?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
    $url = substr($url, 0, -1);
    return $url;
}

function langcode_post_id($post_id){
    global $wpdb;

    $query = $wpdb->prepare('SELECT language_code FROM ' . $wpdb->prefix . 'icl_translations WHERE element_id="%d"', $post_id);
    $query_exec = $wpdb->get_row($query);
    return $query_exec->language_code;
}

function get_trid($element_id, $el_type ){
    global $wpdb;
    $trid_prepared = $wpdb->prepare( "SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_id=%d AND element_type=%s", array( $element_id, $el_type ) );
    $trid = $wpdb->get_var( $trid_prepared );
    return $trid;
}

function get_icl_obj_id($id, $type, $lang) {
    if (is_plugin_active('sitepress-multilingual-cms/sitepress.php') || is_plugin_active('sitepress-multilingual-cms 2/sitepress.php')) {
        return icl_object_id ($id, "tax_".$type, true, $lang);
    }
}

function find_key_value($array, $key, $val) {
    foreach ($array as $item)
        if (isset($item[$key]) && $item[$key] == $val)
            return true;
    return false;
}

function latest_sale_schedule($array, $key, $val) {
    $arr = array();
    if(!empty($array)){
        foreach ($array as $item) {
            if (isset($item[$key]) && in_array($item[$key], $val)){
                $arr[] = $item;
            }
        }
    }

    return $arr;
}

function mcp_cat($selected=array(), $slave) {
    $args= array('orderby' => 'ID','order'   => 'DESC','parent'  => 0,'taxonomy'=>'product_cat','hierarchical' => 1,'title_li' =>'','hide_empty'   => 0);
    $categories = get_categories($args);
    echo "<ul>";
    foreach($categories as $cat){
        $checked = in_array($cat->term_id, $selected) ? 'checked="checked"' : "";
        echo '<li class="product_cat-'.$cat->term_id.'"><label><input class="add_remove_cat" '.$checked.' name="product_cat['.$slave.'][]" value="'.$cat->term_id.'" type="checkbox" />'.$cat->name.'</label>';
        $args2 = array(
            'taxonomy'     => 'product_cat',
            'child_of'     => 0,
            'orderby'      => 'name',
            'show_count'   => 0,
            'pad_counts'   => 0,
            'hierarchical' => 1,
            'title_li'     => '',
            'hide_empty'   => 0
        );

        get_cat_hierchy($cat->term_id, $args2, $selected, $slave);
        echo "</li>";

    }

    echo "</ul>";

}

function get_cat_hierchy($parent,$args, $selected=array(), $slave){

    $cats = get_categories($args);
    $ret = new stdClass;

    if($cats){
        echo "<ul class='children'>";
        foreach($cats as $cat){
            if($cat->parent==$parent){
                $checked_ = in_array($cat->term_id, $selected) ? 'checked="checked"' : "";
                echo '<li class="product_cat-'.$cat->term_id.'"><label><input class="add_remove_cat" '.$checked_.' name="product_cat['.$slave.'][]" value="'.$cat->term_id.'" type="checkbox" />'.$cat->name.'</label>';
                $id = $cat->term_id;
                $ret->$id = $cat;
                $ret->$id->children = get_cat_hierchy($id,$args,array(),$slave);
                echo "</li>";
            }
        }
        echo "</ul>";
    }


    //return $ret;
}
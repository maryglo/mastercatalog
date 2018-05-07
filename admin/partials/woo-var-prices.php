<?php
global $sitepress;
$sitepress->switch_lang( $sitepress->get_default_language() );

global $woocommerce;
$product = wc_get_product($product_id);
$variations = $product->get_available_variations();

foreach($variations as $var){
    $multiplier = get_site_option($this->plugin_name."_multiplier")[$slave] == "" ? 1 : get_site_option($this->plugin_name."_multiplier")[$slave];
    $product_variation = new WC_Product_Variation($var['variation_id']);
    $price__ = round($product_variation->regular_price * $multiplier);
    $price = round_off($price__);
    $attr = $var['attributes'];
    $att_values = array();
    foreach($attr as $key=>$val){
        $tax = str_replace('attribute_', '', $key);
        $label = get_term_by( 'slug', $val, $tax);
        $att_label = $val != "" ? $label->name : 'Any '.wc_attribute_label($tax,'');
        $att_values[] = $att_label;
    }
    ?>
    <div class="accordion">
        <h3><?php echo implode('|',$att_values); ?></h3>
        <div data-id="<?php echo $var['variation_id']; ?>" id="variation_<?php echo $var['variation_id']; ?>">
            <div class="variation_prices_form" data-id="<?php echo $var['variation_id']; ?>">
                <p class="form_field">
                    <label for="_regular_price">Regular Price: ()</label>
                    <input type="number" min="1" step="1" id="regular_price_<?php echo $var['variation_id']; ?>" type="text" placeholder="" class="_regular_price" name="_regular_price" class="short wc_input_price" value="<?php echo $price; ?>">
                </p>

            </div>
        </div>
    </div>
<?php } ?>
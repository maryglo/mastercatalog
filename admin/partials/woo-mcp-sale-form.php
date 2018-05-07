<?php
$product = wc_get_product($product_id);
$prodtosite = new Woo_Mcp_Product_Site($product_id, $slave);
$history = unserialize($prodtosite->get_data_by_main_id($product_id)[0]->history);
$sales = array_reverse(latest_sale_schedule($history, 'title', array('Unschedule sale','Schedule sale')));
if($product->is_type('simple')){
    ?>
    <div class="schedule_data" data-id="<?php echo $product_id;?>">
        <p class="form_field">
            <label for="_sale_price">Sale Price ()</label>
            <input id="sale_price_<?php echo $product_id; ?>" type="text" placeholder="" class="_sale_price" name="_sale_price" class="short wc_input_price" value="<?php echo $sale_price = $history && !empty($sales) ? $sales[0]['price'] : ""; ?>">
        </p>
        <p class="form-field sale_price_dates_fields" style="display: block;">
            <label for="_sale_price_from">Sale Price Dates</label>
            <input id="sale_price_from_<?php echo $product_id; ?>" type="text" maxlength="10" placeholder="From YYYY-MM-DD" value="<?php echo $sale_from = $history && !empty($sales) ? $sales[0]['sale_from'] : ""; ?>" class="_sale_price_from" name="_sale_price_dates_from">
            <input id="sale_price_to_<?php echo $product_id; ?>" type="text" maxlength="10" placeholder="To YYYY-MM-DD" value="<?php echo $sale_to = $history && !empty($sales) ? $sales[0]['sale_to'] : ""; ?>" class="_sale_price_to" name="_sale_price_dates_to">
        </p>
    </div>
<?php } else {
    $variations = $product->get_available_variations();
    foreach($variations as $var){
        $attr = $var['attributes'];
        $var_sale_price = $history && !empty($sales) && $sales[0]['variations'] ? $sales[0]['variations'][$var['variation_id']]['price'] : "";
        $var_sale_from = $history && !empty($sales) && $sales[0]['variations'] ? $sales[0]['variations'][$var['variation_id']]['sale_from'] : "";
        $var_sale_to = $history && !empty($sales) && $sales[0]['variations'] ? $sales[0]['variations'][$var['variation_id']]['sale_to'] : "";
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
                <div class="schedule_data" data-id="<?php echo $var['variation_id']; ?>">
                    <p class="form_field">
                        <label for="_sale_price">Sale Price ()</label>
                        <input id="sale_price_<?php echo $var['variation_id']; ?>" type="text" placeholder="" class="_sale_price" name="_sale_price" class="short wc_input_price" value="<?php echo $var_sale_price; ?>">
                    </p>
                    <p class="form-field sale_price_dates_fields" style="display: block;">
                        <label for="_sale_price_from">Sale Price Dates</label>
                        <input id="sale_price_from_<?php echo $var['variation_id']; ?>" type="text" maxlength="10" placeholder="From YYYY-MM-DD" value="<?php echo $var_sale_from; ?>" class="_sale_price_from" name="_sale_price_dates_from">
                        <input id="sale_price_to_<?php echo $var['variation_id']; ?>" type="text" maxlength="10" placeholder="To YYYY-MM-DD" value="<?php echo $var_sale_to; ?>" class="_sale_price_to" name="_sale_price_dates_to">
                    </p>
                </div>
            </div>
        </div>
    <?php } } ?>
<?php
global $sitepress;
$sitepress->switch_lang( $sitepress->get_default_language() );
?>
<div class="confirmation-content">
    <form method="post" class="confirmation-form" action="">
        <table width="100%" class="wp-list-table widefat fixed confirmation">
            <thead>
            <tr>
                <th>Products</th>
                <th colspan="<?php echo count($slaves); ?>" style="text-align: left">Price</th>
            </tr>
            <tr>
                <th>&nbsp;</th>
                <?php
                foreach($slaves as $slave){
                    $details = get_blog_details($slave);
                    ?>
                    <input type="hidden" name="sites[]" value="<?php echo $slave;?>"/>
                    <th style="text-align: left"><?php echo $details->blogname; ?></th>
                <?php } ?>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach($values['products'] as $key__=>$value){

                $product = wc_get_product($value);
                ?>
                <tr class="<?php echo $class?>">
                    <input type="hidden" class="products" name="products[]" value="<?php echo $value;?>"/>
                    <td>
                        <b><?php echo $product->post->post_title;?></b>
                        <br />
                        <!--original location of product categories-->
                    </td>
                    <?php foreach($slaves as $slave){
                        $multiplier = get_site_option($this->plugin_name."_multiplier")[$slave] == "" ? 1 : get_site_option($this->plugin_name."_multiplier")[$slave];
                        $price__ = round($product->get_price() * $multiplier);
                        $price = round_off($price__);
                        $prodtosite = new Woo_Mcp_Product_Site($value, $slave);
                        $history = unserialize($prodtosite->get_data_by_main_id($value)[0]->history);
                        $sales = array_reverse(latest_sale_schedule($history, 'title', array('Unschedule sale','Schedule sale')));
                        $sale_skeds = $history && !empty($sales) ? $sales[0]['title'] : '';
                        ?>
                        <td style="text-align: left" title="<?php echo $product->get_price()." x ".$multiplier; ?>">
                            <?php if($product->is_type('simple')){
                                ?>
                                <input class="_default_calc_<?php echo $value; ?>" type="hidden" name="_default_calc[<?php echo $slave; ?>][<?php echo $key__; ?>]" value="<?php echo $price; ?>"/>
                                <input data-productype="<?php echo $product->product_type; ?>" name="_product_price[<?php echo $slave; ?>][<?php echo $key__; ?>]" data-slave="<?php echo $slave; ?>" data-product="<?php echo $value;?>" class="_product_price" type="number" min="1" step="1" value="<?php echo $price; ?>" />
                                <input type="hidden" name="_sale_price[<?php echo $slave; ?>][<?php echo $key__; ?>]" value="<?php echo $sale_price = $history && !empty($sales) ? $sales[0]['price'] : ""; ?>" class="sale_price_<?php echo $slave; ?>_<?php echo $value; ?>"/>
                                <input type="hidden" name="_sale_price_dates_from[<?php echo $slave; ?>][<?php echo $key__; ?>]" value="<?php echo $sale_price = $history && !empty($sales) ? $sales[0]['sale_from'] : ""; ?>" class="_sale_price_dates_from_<?php echo $slave; ?>_<?php echo $value; ?>"/>
                                <input type="hidden" name="_sale_price_dates_to[<?php echo $slave; ?>][<?php echo $key__; ?>]" value="<?php echo $sale_price = $history && !empty($sales) ? $sales[0]['sale_to'] : ""; ?>" class="_sale_price_dates_to_<?php echo $slave; ?>_<?php echo $value; ?>"/>
                            <?php } else {
                                $variations = $product->get_available_variations();
                                if($variations){
                                    ?>
                                    <a data-slave="<?php echo $slave; ?>" data-product="<?php echo $value;?>" class="adjust_prices" href="#" title="Change Price">Change Individual Prices</a>
                                    <?php
                                    foreach($variations as $var) {

                                        $multiplier = get_site_option($this->plugin_name."_multiplier")[$slave] == "" ? 1 : get_site_option($this->plugin_name."_multiplier")[$slave];
                                        $product_variation = new WC_Product_Variation($var['variation_id']);
                                        $price__ = round($product_variation->regular_price * $multiplier);
                                        $price = round_off($price__);
                                        $var_sale_price = $history && !empty($sales) && $sales[0]['variations'] ? $sales[0]['variations'][$var['variation_id']]['price'] : "";
                                        $var_sale_from = $history && !empty($sales) && $sales[0]['variations'] ? $sales[0]['variations'][$var['variation_id']]['sale_from'] : "";
                                        $var_sale_to = $history && !empty($sales) && $sales[0]['variations'] ? $sales[0]['variations'][$var['variation_id']]['sale_to'] : "";
                                        ?>
                                        <input type="hidden" name="_default_calc_[<?php echo $slave; ?>][<?php echo $key__; ?>][<?php echo $var['variation_id'];?>]" value="<?php echo $price; ?>" class="default_price_<?php echo $slave; ?>_<?php echo $var['variation_id']; ?>"/>
                                        <input type="hidden" name="_regular_price_[<?php echo $slave; ?>][<?php echo $key__; ?>][<?php echo $var['variation_id'];?>]" value="<?php echo $price; ?>" class="regular_price_<?php echo $slave; ?>_<?php echo $var['variation_id']; ?>"/>
                                        <input type="hidden" name="_sale_price[<?php echo $slave; ?>][<?php echo $key__; ?>][<?php echo $var['variation_id'];?>]" value="<?php echo $var_sale_price; ?>" class="sale_price_<?php echo $slave; ?>_<?php echo $var['variation_id']; ?>"/>
                                        <input type="hidden" name="_sale_price_dates_from[<?php echo $slave; ?>][<?php echo $key__; ?>][<?php echo $var['variation_id'];?>]" value="<?php echo $var_sale_from; ?>" class="_sale_price_dates_from_<?php echo $slave; ?>_<?php echo $var['variation_id']; ?>"/>
                                        <input type="hidden" name="_sale_price_dates_to[<?php echo $slave; ?>][<?php echo $key__; ?>][<?php echo $var['variation_id'];?>]" value="<?php echo $var_sale_to; ?>" class="_sale_price_dates_to_<?php echo $slave; ?>_<?php echo $var['variation_id']; ?>"/>
                                    <?php
                                    } }
                                ?>
                            <?php } ?>
                            <br /><textarea style="height: 59px; width: 161px;display: none;" name="notes[<?php echo $slave; ?>][]" class="notes_<?php echo $slave; ?>_<?php echo $value; ?>" placeholder="Notes"></textarea>
                            <input value="<?php echo $value_ = $sale_skeds == 'Schedule sale'? 1 : 0; ?>" <?php echo $checked = $sale_skeds == 'Schedule sale'? 'checked="checked"' : ""; ?> data-slave="<?php echo $slave; ?>" data-product="<?php echo $value; ?>" type="checkbox" name="sale_schedule[<?php echo $slave; ?>][]" class="schedule_check" /></span><a data-slave="<?php echo $slave; ?>" data-product="<?php echo $value;?>" class="sale_schedule" href="#" title="View details">Schedule Sale</a>
                            <div class="accordion">
                                <h3>History</h3>
                                <div data-id="<?php echo $product->id; ?>" id="panel_history_<?php echo $product->id; ?>">
                                    <ul class="history">
                                        <?php
                                        if($history){
                                            foreach($history as $key=>$value_){
                                                ?>
                                                <li><?php echo '<abbr class="date" title="'.$value_['date'].'">'.date('m/d/Y', strtotime($value_['date'])).'</abbr>'." ".$value_['title']." ".$value_['price']; ?></li>
                                            <?php }
                                        } else { ?>
                                            No history yet!
                                        <?php } ?>
                                    </ul>
                                </div>

                            </div>
                            <div class="accordion"  style="margin-top: 7px;">
                                <h3>Product Categories</h3>
                                <div data-id="<?php echo $product->id; ?>" id="panel_cat_<?php echo $product->id; ?>" class="panel_cat_<?php echo $product->id; ?>_<?php echo $slave; ?>">
                                    <?php //mcp_cat(wp_get_post_terms($product->id, 'product_cat', array("fields" => "ids")));

                                    mcp_cat(wp_get_post_terms($product->id, 'product_cat', array("fields" => "ids")), $slave);
                                    ?>
                                    <p><a data-slave="<?php echo $slave; ?>" data-id="<?php echo $product->id; ?>" href="#" class="add_new_prod_cat"><span class="ui-icon ui-icon-plus"></span>Add New Category</a></p>
                                </div>
                            </div>
                        </td>
                    <?php } ?>
                </tr>
                <?php if($class=="alternate"){$class="";}else{$class="alternate";}} ?>
            </tbody>
        </table>
    </form>
</div>
<div class="confirmation-footer">
    <div class="confirmation-toolbar">
        <div class="confirmation-toolbar-secondary">
            Do you wish to push the selected product(s) to the selected site(s)?
        </div>
        <div class="confirmation-toolbar-primary">
            <input type="button" class="button button-primary cancel-push" value="NO" />
            <input type="submit" value="YES" class="button button-primary continue-push" id="submit" name="submit"><span class="spinner"></span>
        </div>
    </div>
</div>

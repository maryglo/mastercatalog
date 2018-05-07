<div class="confirmation-content">
    <form method="post" class="confirmation-form" action="">
        <table width="100%" class="wp-list-table widefat fixed confirmation">
            <thead>
            <tr>
                <?php
                foreach($slaves as $slave){
                    $details = get_blog_details($slave);
                    ?>
                    <input type="hidden" name="sites[]" value="<?php echo $slave;?>"/>
                    <th><?php echo $details->blogname; ?></th>
                <?php } ?>
            </tr>
            </thead>
            <tbody>
            <?php
            $class="alternate";
            foreach($values['products'] as $value){
                $product = wc_get_product($value);
                ?>
                <tr class="<?php echo $class?>">
                    <input type="hidden" class="products" name="products[]" value="<?php echo $value;?>"/>
                    <?php foreach($slaves as $slave){
                        $multiplier = get_site_option($this->plugin_name."_multiplier")[$slave] == "" ? 1 : get_site_option($this->plugin_name."_multiplier")[$slave];
                        $price__ = round($product->get_price() * $multiplier);
                        $price = round_off($price__);
                        $prodtosite = new Woo_Mcp_Product_Site($value, $slave);
                        ?>
                        <td><?php echo $product->post->post_title;?></td>
                    <?php } ?>
                </tr>
                <?php if($class=="alternate"){$class="";}else{$class="alternate";}} ?>
            </tbody>
        </table>
</div>
<div class="confirmation-footer">
    <div class="confirmation-toolbar">
        <div class="confirmation-toolbar-secondary">
            Do you wish to deactivate the selected product(s) to the selected site(s)?
        </div>
        <div class="confirmation-toolbar-primary">
            <input type="button" class="button button-primary cancel-deactivate" value="NO" />
            <input type="submit" value="YES" class="button button-primary continue-deactivate" id="submit" name="submit"><span class="spinner"></span>
        </div>
    </div>
</div>
</div>
</form>
<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://cto2go.ca/
 * @since      1.0.0
 *
 * @package    Woo_Mcp
 * @subpackage Woo_Mcp/admin/partials
 */

?>
<div class="wrap">
    <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
    <h2>Products Masters Catalogue</h2>
    <?php
      $products_table->prepare_items();
    ?>
    <form action="" method="get" class="search-form">
        <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>" />
        <?php $products_table->search_box('Search Product', 'product'); ?>
    </form>
    <form id="product-filter" action="">
        <div class="tablenav top">
            <div class="alignleft">
                <span class="label_site">Please select the product you wish to add to </span>
                <select  name="sites[]" class="sites"  multiple="multiple">
                    <?php foreach($this->list_sites() as $site) {
                        $master =  get_site_option($this->plugin_name."_master");
                        if($master != $site['blog_id']) {
                    ?>
                     <option value="<?php echo $site['blog_id']; ?>"><?php echo $site['name']; ?></option>
                    <?php } }?>
                </select>
            </div>
            <br class="clear">
        </div>

        <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>" />
        <?php
        $products_table->display();
        $goto = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        ?>
        <br class="clear">
        <div class="tablenav bottom">
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
                        <option value="<?php echo remove_querystring_var($goto, 'view_by');?>">All</option>
                        <option <?php  if(isset($_GET['view_by']) && $_GET['view_by'] != "" && $_GET['view_by'] == 'new'){ ?>selected="selected" <?php } ?> value="<?php echo remove_querystring_var($goto, 'view_by').'&view_by=new';?>">New</option>
                        <option <?php  if(isset($_GET['view_by']) && $_GET['view_by'] != "" && $_GET['view_by'] == 'pushed'){ ?>selected="selected" <?php } ?> value="<?php echo remove_querystring_var($goto, 'view_by').'&view_by=pushed';?>">Pushed</option>
                    </select>
                </div>
                <div class="products-per-page">
                    <span>Products Per Page:</span>
                    <select name="per_page" class="per_page">
                        <option value="<?php echo $goto.'&per_page=25';?>">25</option>
                        <?php for($i= 5;$i < 30; $i+=5) { ?>
                            <option <?php  if(isset($_GET['per_page']) && $_GET['per_page'] != "" && $_GET['per_page'] == $i){ ?>selected="selected" <?php } ?> value="<?php echo $goto.'&per_page='.$i;?>"><?php echo $i; ?></option>
                        <?php  } ?>
                        <option <?php  if(isset($_GET['per_page']) && $_GET['per_page'] != "" && $_GET['per_page'] == 'all'){ ?>selected="selected" <?php } ?> value="<?php echo $goto.'&per_page=all';?>">All</option>
                    </select>
                </div>
            </div>
        </div>
        <br class="clear">
    </form>
</div>
<div id="dialog" title="Product Push Tool" style="display: none">
    <div class="spinner_wrap"><span class="spinner "><br />Loading..</span></div>
</div>
<div id="waiting_msg" title="Please Wait" style="display: none">
    <div class="spinner_wrap"><span class="spinner is-active"></span><br />Please wait, the selected products are being pushed to the sites.</div>
</div>
<div id="discontinue_msg" title="Please Wait" style="display: none">
    <div class="spinner_wrap"><span class="spinner is-active"></span><br />Please wait, the selected products are being deactivated on the slave sites.</div>
</div>
<div class="schedule_details" title="Sale Schedule" style="display: none;">
    <div class="spinner_wrap"><span class="spinner is-active"><br />Loading..</span></div>
</div>
<div class="variation_prices" title="Variation Prices" style="display: none;">
    <div class="spinner_wrap"><span class="spinner is-active"><br />Loading..</span></div>
</div>
<div class="variation_override" title="Alert" style="display: none;">
    <p>Do you want to update all variations on the product?</p>
</div>
<div class="category_dialog" Title="Add New Category" style="display: none">
    <div class="category_form">
        <form class="new_cat_form">
            <input type="hidden" name="post_id" id="post_id"/>
            <input type="hidden" name="slave" id="slave_no" />
        <p class="form_field">
            <label for="cat_name">Name</label>
            <input type="text" placeholder="" id="product_cat" name="product_cat_name" class="short">
        </p>
        <p class="form-field">
            <label for="cat_parent">Parent Category</label>
            <?php
            $args = array(
                'show_option_none' => __( 'Select category' ),
                'show_count'       => 0,
                'orderby'          => 'name',
                'echo'             => 0,
                'taxonomy'         => 'product_cat',
                'hierarchical'       => 1,
                'name'               => 'product_cat_parent',
                'hide_empty'         => 0,
            );
            ?>

            <?php echo $select  = wp_dropdown_categories( $args ); ?>
        </p>
        </form>
    </div>
</div>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->

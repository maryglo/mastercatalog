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
    <h2>Site Setup</h2>
    <?php if(!empty($messge)) {?>
        <div id="message" class="updated"><?php echo $message; ?></div>
    <?php } ?>
    <form method="post" name="woo-mcp_options" id="woo-mcp_options">
        <?php settings_fields($this->plugin_name);
        $settings =  $this->getSettings();
        ?>
        <table class="wp-list-table widefat fixed posts">
            <thead>
            <th>Site Name</th>
            <th>Multiplier</th>
            <th>Master</th>
            </thead>
            <tbody>
            <?php
            if($this->list_sites()){
                foreach($this->list_sites() as $site) {
                    $multiplier = $settings[$this->plugin_name."_multiplier"][ $site['blog_id']]  == "" ? 1 : $settings[$this->plugin_name."_multiplier"][ $site['blog_id']];
                    $master = $settings[$this->plugin_name."_master"];
                    ?>
                    <tr>
                        <td><?php echo $site['name']; ?></td>
                        <td><input data-tip="" class="multiplier" name="multiplier[<?php echo $site['blog_id']; ?>]" type="text" value="<?php echo $multiplier; ?>"/></td>
                        <td><input type="radio" name="master" value="<?php echo $site['blog_id']; ?>" <?php echo ($master == $site['blog_id']) ? "checked=\"checked\" " : "" ?> /></td>
                    </tr>
                <?php
                } ?>
                <tr>
                    <td><?php submit_button('Save Settings', 'primary','submit', TRUE); ?></td>
                </tr>
            <?php } else {
                ?>
                <tr>
                    <td colspan="3">No sites available! Please add a new site on the network.</td>
                </tr>
            <?php
            } ?>
            </tbody>

        </table>
    </form>
</div>
<style type="text/css">
    .error{
        border: 1px solid red !important;
    }
</style>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->

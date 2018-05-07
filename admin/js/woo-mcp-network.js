(function( $ ) {
    'use strict';

    /**
     * All of the code for your admin-specific JavaScript source
     * should reside in this file.
     *
     * Note that this assume you're going to use jQuery, so it prepares
     * the $ function reference to be used within the scope of this
     * function.
     *
     * From here, you're able to define handlers for when the DOM is
     * ready:
     *
     * $(function() {
	 *
	 * });
     *
     * Or when the window is loaded:
     *
     * $( window ).load(function() {
	 *
	 * });
     *
     * ...and so on.
     *
     * Remember that ideally, we should not attach any more than a single DOM-ready or window-load handler
     * for any particular page. Though other scripts in WordPress core, other plugins, and other themes may
     * be doing this, we should try to minimize doing that in our own work.
     */

    $(function(){

        $('.woo-mcp-cancel').click(function(e){
            e.preventDefault();
            $('.woo-mcp-error').hide();
        });

        $('.woo-mcp-continue').click(function(e){
            e.preventDefault();
            var ajaxurl = ajax.ajax_url;

            ajaxurl += '?action=update_table';

            $('.woo-mcp-error').find('.spinner').show().addClass('is-active');
            $.post(ajaxurl, function(){
                alert('Plugin table was updated successfully');
                $('.woo-mcp-error').hide();
                location.reload();
            });

        });

        $('#woo-mcp_options #submit').click(function(){
            var error = [];
            $('.multiplier').each(function(index, el){
                if($(this).val() <= 0){
                    $(this).addClass('error');
                    $(this).attr('data-tip', "Value must be greater than 0!")
                    error.push(true);
                } else {
                    $(this).removeClass('error');
                    error.push(false);
                }
            });

            if(jQuery.inArray( true, false ) ) {
                return false;
            } else {
                return true;
            }

        });

    });

})( jQuery );

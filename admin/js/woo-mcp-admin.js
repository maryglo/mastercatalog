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

        $(".variable_selling_price").attr('readonly', true);
        $("#_selling_price").attr('readonly', true);

        $( "form[name=woo-mcp_options]" ).submit(function( event ) {
            if(!$('input[name=master]').is(':checked')) {
                alert('Please select the master catalogue');
                event.preventDefault();
            }

        });

        $('.post-to-sites').click(function(e){
            var count = $('input[name="products[]"]:checked').length;
            if(count == 0) {
                e.preventDefault();
                alert('Please select at least one(1) product to push.');
                return false;
            } else if($(".sites").val() === null){
                e.preventDefault();
                alert('Please select at least one(1) slave site.');
                return false;
            }else {

                e.preventDefault();
                var products_ = $('#product-filter').serialize();

                var data = {
                    action: 'get_content',
                    data: products_
                }

                var ajaxurl = ajax.ajax_url;
                $("#dialog").dialog({modal: true});
                $("#dialog").find('.spinner ').show().addClass('is-active');
                $.post(ajaxurl,data, function(response){
                    $("#dialog").html(response);
                    $( "#dialog" ).dialog({
                        modal: true,
                        minWidth: 600,
                        minHeight:750,
                        resizable: true
                    });

                    $(document).find( ".accordion").accordion({
                        collapsible: true,
                        heightStyle: "fill",
                        active: false
                    });
                });
            }
        });

        $('.per_page').change(function(){
            window.location = $(this).val();
        });

        $(document).on('click','.cancel-push',function(e){
            e.preventDefault();
            tb_remove();
        });

        $(document).on('click','.continue-push',function(e){
            e.preventDefault();
            var count = $('.confirmation-form').find('input.products').size();
            var products_ = $('.confirmation-form').serialize();

            var data = {
                action: 'push_products',
                data: products_
            }

            $('._product_price').each(function(){
                if($(this).val() == 0){
                    $(this).addClass('error');
                }
            });



            if($('.confirmation-form').find('input.error').length == 0) {
                $("#waiting_msg").dialog({modal: true});
                $.post(ajaxurl,data, function(response){

                    var mesg = count == 1 ? 'Product was successfully pushed.' : 'Products were successfully pushed.';

                    if(Object.keys(response).length == 0){
                        alert(mesg);
                        $("#waiting_msg").dialog( "close" );
                        location.reload();
                    } else {
                        //execute French

                        submitFrench(response);
                    }
                });

            } else {
                alert('Please enter a price value.');
            }

        });

        $(document).on('keydown, change', '._product_price', function(){
            var value = parseFloat($(this).val());
            var prod_id = $(this).data('product');
            var default_calc = $(this).siblings('._default_calc_'+prod_id);
            var slave = $(this).data('slave');

            if(default_calc.val() != "" && (value < parseFloat(default_calc.val()) && value > 0)){
                $(this).addClass('less_than');
                $('.notes_'+slave+'_'+prod_id).show();
                if($(this).data('productype') == 'variable') {
                    $( ".variation_override" ).dialog({
                        modal: true,
                        minWidth: 450,
                        minHeight:97,
                        resizable: true,
                        buttons: {
                            "Okay": function(){
                                $( this ).dialog( "close" );
                            },
                            Cancel: function() {
                                $( this ).dialog( "close" );
                            }
                        }
                    });
                }
            } else if (parseFloat(default_calc.val()) != "" && value > parseFloat(default_calc.val())) {
                $(this).removeClass('less_than');
                $('.notes_'+slave+'_'+prod_id).show();
                if($(this).data('productype') == 'variable') {
                    $( ".variation_override" ).dialog({
                        modal: true,
                        minWidth: 450,
                        minHeight:97,
                        resizable: true,
                        buttons: {
                            "Okay": function(){
                                $( this ).dialog( "close" );
                            },
                            Cancel: function() {
                                $( this ).dialog( "close" );
                            }
                        }
                    });
                }
            } else if(parseFloat(default_calc.val()) != "" && value == parseFloat(default_calc.val())){
                $(this).removeClass('less_than');
                $('.notes_'+slave+'_'+prod_id).hide();
            } else if (value == '' || value == '0') {
                $(this).addClass('less_than error');
            } else if(value !="" && value != 0){
                $(this).removeClass('error');
            }

        });

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

        $(document).on('click','.view_history',function(e){
            e.preventDefault();
            var id  = $(this).data('id');

            if($('.history_'+id).css('display') == 'none'){
                $('.history_'+id).show();
                $(this).html('&#45; View History');
            } else {
                $('.history_'+id).hide();
                $(this).html('&#43; View History');
            }

        });

        $(".sites").multiselect();

        $('.discontinue').click(function(e){
            var count = $('input[name="products[]"]:checked').length;
            if(count == 0) {
                e.preventDefault();
                alert('Please select at least one(1) product to deactivate.');
                return false;
            } else if($(".sites").val() === null){
                e.preventDefault();
                alert('Please select at least one(1) slave site.');
                return false;
            }else {

                e.preventDefault();
                var products_ = $('#product-filter').serialize();

                var data = {
                    action: 'get_prod_content',
                    data: products_
                }

                var ajaxurl = ajax.ajax_url;
                $("#dialog").dialog({modal: true, title: "Please wait"});
                $("#dialog").find('.spinner ').show().addClass('is-active');
                $.post(ajaxurl,data, function(response){
                    $("#dialog").html(response);
                    $( "#dialog" ).dialog({
                        modal: true,
                        minWidth: 600,
                        minHeight:750,
                        resizable: true,
                        title: "Discontinue on site"
                    });
                });

            }
        });

        $(document).on('click','.continue-deactivate',function(e){
            e.preventDefault();
            var count = $('.confirmation-form').find('input.products').size();
            var products_ = $('.confirmation-form').serialize();

            var data = {
                action: 'deactivate_products',
                data: products_
            }

            if($('.confirmation-form').find('input.error').length == 0) {
                $("#discontinue_msg").dialog({modal: true});
                $.post(ajaxurl,data, function(response){

                    var mesg = count == 1 ? 'Product was successfully deactivated on the slave site(s).' : 'Products were successfully deactivated on the slave site(s).';
                    alert(mesg);
                    $("#discontinue_msg").dialog( "close" );
                    location.reload();

                });

            }

        });

        $(document).on('click','input.schedule_check',function(e){
            var id  = $(this).data('product');
            var slave = $(this).data('slave');

            if($(this).is(':checked')) {
                $(this).attr('checked', true);
                $(this).val(1);
                $( ".schedule_details" ).dialog({modal: true, title: "Please wait"});
                $( ".schedule_details" ).html('<div class="spinner_wrap"><span class="spinner is-active"><br />Loading..</span></div>');
                var ajaxurl = ajax.ajax_url;
                var data = {
                    action: 'get_sale_schedule_form',
                    product_id : $(this).data('product')
                }
                $.post(ajaxurl,data, function(response){
                    $( ".schedule_details" ).html(response);
                    $( ".schedule_details" ).dialog({
                        modal: true,
                        minWidth: 500,
                        minHeight:97,
                        resizable: true,
                        title: "Sale Schedule",
                        buttons: {
                            "Submit": function(){
                                submit_schedule(id, slave);
                            },
                            Cancel: function() {
                                $( ".schedule_details" ).dialog( "close" );
                                $('.schedule_data').find('input').val('');
                            }
                        },
                        close: function( event, ui ) {
                            $('.schedule_data').find('input').val('');
                            $('.schedule_data').find('input').removeClass('error');
                        },
                        open: function () {
                            $('.schedule_data').find('#_sale_price').val($('.confirmation-form').find('.sale_price_'+slave+"_"+id).val());
                            $('.schedule_data').find('#_sale_price_from').val($('.confirmation-form').find('._sale_price_dates_from_'+slave+"_"+id).val());
                            $('.schedule_data').find('#_sale_price_to').val($('.confirmation-form').find('._sale_price_dates_to_'+slave+"_"+id).val());
                        }
                    });

                    $(document).find('._sale_price_from').datepicker({
                        dateFormat: "yy-mm-dd"
                    });
                    $(document).find('._sale_price_to').datepicker({
                        dateFormat: "yy-mm-dd"
                    });

                    $(document).find( ".accordion").accordion({
                        collapsible: true,
                        heightStyle: "fill",
                        active: false
                    });


                });

            } else {
                $(this).attr('checked', false);
                $(this).val(0);
                $('.confirmation-form').find('.sale_price_'+slave+"_"+id).val('');
                $('.confirmation-form').find('._sale_price_dates_from_'+slave+"_"+id).val('');
                $('.confirmation-form').find('._sale_price_dates_to_'+slave+"_"+id).val('');

            }
        });

        $(document).on('click','.sale_schedule',function(e){
            e.preventDefault();
            var id  = $(this).data('product');
            var slave = $(this).data('slave');


            if($(this).siblings('input.schedule_check').is(':checked')) {
                $( ".schedule_details" ).dialog({modal: true, title: "Please wait"});
                $( ".schedule_details" ).html('<div class="spinner_wrap"><span class="spinner is-active"><br />Loading..</span></div>');
                var ajaxurl = ajax.ajax_url;
                var data = {
                    action: 'get_sale_schedule_form',
                    product_id : id,
                    slave: slave
                }
                $.post(ajaxurl,data, function(response){
                    $( ".schedule_details" ).html(response);
                    $( ".schedule_details" ).dialog({
                        modal: true,
                        minWidth: 500,
                        minHeight:97,
                        resizable: true,
                        title: "Sale Schedule",
                        buttons: {
                            "Submit": function(){
                                submit_schedule(id, slave);
                            },
                            Cancel: function() {
                                $( ".schedule_details" ).dialog( "close" );
                                $('.schedule_data').find('input').val('');
                            }
                        },
                        close: function( event, ui ) {
                            $('.schedule_data').find('input').val('');
                            $('.schedule_data').find('input').removeClass('error');
                        }

                    });

                    $(document).find('._sale_price_from').datepicker({
                        dateFormat: "yy-mm-dd"
                    });
                    $(document).find('._sale_price_to').datepicker({
                        dateFormat: "yy-mm-dd"
                    });

                    $(document).find( ".accordion").accordion({
                        collapsible: true,
                        heightStyle: "fill",
                        active: false
                    });
                });

            } else {
                alert('Sale is unscheduled.');
            }

        });

        $(document).on('click','.adjust_prices',function(e){
            e.preventDefault();
            var id  = $(this).data('product');
            var slave = $(this).data('slave');

                $( ".variation_prices" ).dialog({modal: true, title: "Please wait"});
                $( ".variation_prices" ).html('<div class="spinner_wrap"><span class="spinner is-active"><br />Loading..</span></div>');
                var ajaxurl = ajax.ajax_url;
                var data = {
                    action: 'get_variation_prices',
                    product_id : id,
                    slave: slave
                }
                $.post(ajaxurl,data, function(response){
                    $( ".variation_prices" ).html(response);
                    $( ".variation_prices" ).dialog({
                        modal: true,
                        minWidth: 500,
                        minHeight:97,
                        resizable: true,
                        title: "Variation Prices",
                        buttons: {
                            "Submit": function(){
                                submit_variation_prices(id, slave);
                            },
                            Cancel: function() {
                                $( ".variation_prices" ).dialog( "close" );
                            }
                        },
                        close: function( event, ui ) {
                        }

                    });
                });


        });

        $(document).on('click','.add_new_prod_cat',function(e){
            e.preventDefault();
            var id = $(this).data('id');
            var slave = $(this).data('slave');
            $( ".category_dialog" ).dialog({
                modal: true,
                minWidth: 450,
                minHeight:97,
                resizable: true,
                buttons: {
                    "Submit": function(){
                        submit_category(id,slave);
                    },
                    Cancel: function() {
                        $( ".category_dialog" ).dialog( "close" );
                    }
                }
            });
        });

        $(document).on('click','.add_remove_cat',function(e){
            var id = $(this).val();
            var checked = "";
            var ajaxurl = ajax.ajax_url;
            var post_id = $(this).parents('.ui-accordion-content').data('id');
            if($(this).is(':checked')) {
                checked = 1;
            } else {
                checked = 0;
            }

            var data = {
                action: 'update_prod_cat',
                data:{ id: id, checked: checked, post_id: post_id }
            }


            $.post(ajaxurl,data);
        });

        $('.bulkaction').click(function(e){
            var count = $('input[name="products[]"]:checked').length;
            var action = $(this).siblings('select[name=action]').val();


            if(action == "") {
                alert('Please select an action');
                e.preventDefault();

            }else if(count == 0) {
                e.preventDefault();
                alert('Please select at least one(1) product to push.');
                return false;
            } else if($(".sites").val() === null){
                e.preventDefault();
                alert('Please select at least one(1) slave site.');
                return false;
            } else {

                if(action == 'post_to_selected') {

                    e.preventDefault();
                    var products_ = $('#product-filter').serialize();

                    var data = {
                        action: 'get_content',
                        data: products_
                    }

                    var ajaxurl = ajax.ajax_url;
                    $("#dialog").dialog({modal: true});
                    $("#dialog").find('.spinner ').show().addClass('is-active');
                    $.post(ajaxurl,data, function(response){
                        $("#dialog").html(response);
                        $( "#dialog" ).dialog({
                            modal: true,
                            minWidth: 600,
                            minHeight:750,
                            resizable: true
                        });

                        $(document).find( ".accordion").accordion({
                            collapsible: true,
                            heightStyle: "fill",
                            active: true
                        });
                    });
                }

                if(action == 'discontinue'){
                    e.preventDefault();
                    var products_ = $('#product-filter').serialize();

                    var data = {
                        action: 'get_prod_content',
                        data: products_
                    }

                    var ajaxurl = ajax.ajax_url;
                    $("#dialog").dialog({modal: true, title: "Please wait"});
                    $("#dialog").find('.spinner ').show().addClass('is-active');
                    $.post(ajaxurl,data, function(response){
                        $("#dialog").html(response);
                        $( "#dialog" ).dialog({
                            modal: true,
                            minWidth: 600,
                            minHeight:750,
                            resizable: true,
                            title: "Discontinue on site"
                        });
                    });
                }
            }

        });

    });

    function submit_schedule(id, slave) {
        var error = 0;
        var group_count = 1;

        var sale_price_count = 0;

        $('.schedule_data').each(function(){
            var data_id = $(this).data('id');
            var sale_price = $(this).find('#sale_price_'+data_id).val();
            var date_from = $(this).find('#sale_price_from_'+data_id).val();
            var date_to = $(this).find('#sale_price_to_'+data_id).val();

            if(sale_price != ""){
                sale_price_count++;
            }


            if(sale_price != "" && date_from == ""){
                $(this).find('#sale_price_from_'+data_id).addClass('error');
                error++;
            }

            if(error == 0){
                $('.confirmation-form').find('.sale_price_'+slave+"_"+data_id).val(sale_price);
                $('.confirmation-form').find('._sale_price_dates_from_'+slave+"_"+data_id).val(date_from);
                $('.confirmation-form').find('._sale_price_dates_to_'+slave+"_"+data_id).val(date_to);

            }

        });

        if(sale_price_count < group_count){
            alert('Fields cannot be empty.');
            error++;

        }

        if(error == 0){
            $( ".schedule_details" ).dialog( "close" );
        }

    }

    function submitFrench(res) {
        var data = {
            action: 'push_french',
            data:res
        }

        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: data,
            success: function(response){
                var mesg = 'Success!';
                alert(mesg);
                $("#waiting_msg").dialog( "close" );
                location.reload();
            }
        });
    }

    function submit_category(id, slave){
        $('.category_dialog').find('#post_id').val(id);
        $('.category_dialog').find('#slave_no').val(slave);
        var ajaxurl = ajax.ajax_url;
        var cat  = $('.new_cat_form').serialize();
        var data = {
            action: 'insert_category',
            data: cat
        }

        var error = $('#product_cat').val() == "" ? true : false;
        if(error){
            $('.category_dialog').find('#product_cat').addClass('error');
        } else {
            $('.category_dialog').find('#product_cat').removeClass('error');
            $.post(ajaxurl,data, function(response){
                var data_resp = wpAjax.parseAjaxResponse(response, 'ajax-response');
                if(data_resp.responses[0].id == '-1'){
                    $('.panel_cat_'+id+'_'+slave).find('ul:first').append(data_resp.responses[0].data);
                    $('.panel_cat_'+id+'_'+slave).find('.product_cat-'+data_resp.responses[0].supplemental.cat_id).find('input:first').attr('checked', true);
                } else {
                    $('.product_cat-'+data_resp.responses[0].id).find('.children:first').append(data_resp.responses[0].data);
                    $('.panel_cat_'+id+'_'+slave).find('.product_cat-'+data_resp.responses[0].supplemental.cat_id).find('input:first').attr('checked', true);
                }

                $( ".category_dialog").dialog('close');
            });
        }
    }

    function submit_variation_prices(id, slave) {
        var error = 0;
        var group_count = 1;

        var sale_price_count = 0;

        $('.variation_prices_form').each(function(){
            var data_id = $(this).data('id');
            var price = $(this).find('#regular_price_'+data_id).val();


            if(price == 0){
                $(this).find('#regular_price_'+data_id).addClass('error');
                error++;
            }

            if(error == 0){
                $('.confirmation-form').find('.regular_price_'+slave+"_"+data_id).val(price);
            }

        });

        if(error == 0){
            $( ".variation_prices" ).dialog( "close" );
        }
    }

})( jQuery );

jQuery( document ).ready(function($) {

    var product_type = jQuery('select#product-type').val();
    var manage_stock = jQuery("#_manage_stock").val();

    //if(product_type == "variable") {
    jQuery("#_manage_stock").prop('disabled', 'disabled');
    jQuery("#_stock").prop('disabled', 'disabled');
    jQuery("#_backorders").prop('disabled', 'disabled');
    jQuery("#_sold_individually").prop('disabled', 'disabled');
    jQuery("#_stock_status").prop('disabled', 'disabled');
    //}
    $(document).on('click', '.woocommerce_variation', function(){
        if($(this).hasClass('open')){
            $(this).find(".variable_manage_stock").prop('disabled', 'disabled');
            $(this).find("select[name^='variable_stock_status[']").prop('disabled', 'disabled');
            $(this).find("input[name^='variable_stock[']").prop('disabled', 'disabled');
            $(this).find("select[name^='variable_backorders[']").prop('disabled', 'disabled');
        }
    });
});
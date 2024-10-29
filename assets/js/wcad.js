jQuery(document).ready(function($) {

    function wcad_produrlform_empty(variable){
        if( 
           variable == undefined || 
           variable == "undefined" || 
           variable == '' || 
           variable == null || 
           variable == 0 || 
           variable == "0" 
        ){
           return true;
        }else{
           return false;
        }
    }
    
    function wcadValidateProductRows(productType){

        productRowValidationContinue = false;        
        $( productType + " .prod_url_select" ).each(function( index ) {

            var product = $(this).val();
            if( wcad_produrlform_empty(product)){
                $(productType + ' .wcad_error_message').text('');
                $(productType + ' .wcad_error_message').append('Make sure to select a product for each product row before adding a new one. ');
                productRowValidationContinue = false;
            }else{
                productRowValidationContinue = true;
            }
        });

        if(productRowValidationContinue){
            return true;
        }else{
            return false;
        }
    }
    
    function wcadValidateFormInputs(productType){

        qtyValidationContinue = false;
        productValidationContinue = false;
        productNameValidationContinue = false;
        variableAttributes = false;

        $( productType + " .prod_url_select" ).each(function( index ) {

            // Make sure we have something in the product field
            var product = $(this).val();
            if( wcad_produrlform_empty(product)){
                $(productType + ' .wcad_error_message').text('');
                $(productType + ' .wcad_error_message').append('Make sure to select a product for each product row. ');
                productNameValidationContinue = false;
            }else{
                productNameValidationContinue = true;
            }

            // Make sure out product(s) are numeric
            if($.isNumeric(product)){
                productValidationContinue = true;
            }else{
                $(productType + ' .wcad_error_message').text('');
                $(productType + ' .wcad_error_message').append('The values for one or more of your products are not appropriate. Only numbers are allowed. ');
                productValidationContinue = false;
            }
        });

        // Make sure out quantities are numeric
        $( productType + " .prod_url_qty" ).each(function( index ) {

            var qty = $(this).val();
            if($.isNumeric(qty) && qty >= 1 ){
               qtyValidationContinue = true;
            }else{
                $(productType + ' .wcad_error_message').text('');
                $(productType + ' .wcad_error_message').append('The values for one or more of your quantities is not numeric or is less than 1. You must have at least 1 for your quantity. ');
                qtyValidationContinue = false;
            }
        });

        if(productType == '#wcad_variable_product_url_form'){
            $( '.variationAttributes select' ).each(function( index ) {
                
                if( wcad_produrlform_empty( $(this).val() ) ){
                    variableAttributes = false;
                }else{
                    variableAttributes = true;
                }
            });
        }

        // if all tests passed we return true so the ajax can continue
        if( productType == '#wcad_variable_product_url_form' ){
            
            if(qtyValidationContinue && productValidationContinue && productNameValidationContinue && variableAttributes){
                return true;
            }else{
                $(productType + ' .wcad_error_message').text('');
                $(productType + ' .wcad_error_message').append('Select all applicable attributes before submitting.');
                return false;
            }
        }else if(qtyValidationContinue && productValidationContinue && productNameValidationContinue){
            return true;
        }else{
            return false;
        }
    }

    function wcadSubmitForm(form){

        event.preventDefault();
        var formType = form.attr('class');
        $('#wcad_'+formType+'_product_url_form .wcad_error_message').text('');
        $('#wcad_'+formType+'_product_url_form #urlHolder').val('');
        var origin   = window.location.origin;
        var url = origin+'/checkout/?productID=';        
        var product_segment = '';
        var qty_segment = '';
        var wcad_continue = wcadValidateFormInputs('#wcad_'+formType+'_product_url_form');
        
        if(wcad_continue){
            
            $( '#wcad_'+formType+'_product_url_form .prod_qty_set' ).each(function( index ) {

                if(formType == 'simple'){

                    // /checkout/?productID=635:1,
                    // /checkout/?productID=635:1,6452:2
                    var product = $(this).children('#wcad_'+formType+'_product_url_form .prod_url_select').val();
                    var quantity = $(this).children('#wcad_'+formType+'_product_url_form .prod_url_qty').val();
                    url += product+':'+quantity+',';

                }else if(formType == 'grouped'){

                    // /checkout/?productID=6459&quantity[6452]=10&quantity[635]=10
                    var product = $('#wcad_'+formType+'_product_url_form .prod_url_select').val();
                    var quantity = $('#wcad_'+formType+'_product_url_form .prod_url_qty').val();
                    var childrenString = $('#wcad_'+formType+'_product_url_form .prod_url_select').find(':selected').data('children');
                    var childrenArray = childrenString.split(",");
                    var urlPart = '';
                    
                    $('#wcad_'+formType+'_product_url_form .prod_url_qty').each(function( index ) {
                        
                        var qty = $(this).val();
                        $(childrenArray).each(function( position ) {
                            if( !wcad_produrlform_empty(childrenArray[position]) ){
                                urlPart += '&quantity['+childrenArray[position]+']='+qty;
                            }
                        });
                    });

                    url += product+urlPart;

                }else if(formType == 'variable'){
                    
                    // /checkout/?productID=10519&variation_id=10520&quantity_var=3&attribute_pa_colour=Blue&attribute_pa_size=L
                    var product = $('#wcad_'+formType+'_product_url_form .prod_url_select').val();
                    var quantity = $('#wcad_'+formType+'_product_url_form .prod_url_qty').val();
                    var attributeSting = '';
                    var variationid = '';

                    $('#wcad_'+formType+'_product_url_form .variationAttributes select').each(function( index ) {
                        
                        var attributeName = $(this).val(); 
                        var attributeValue = $(this).find(':selected').data('attributevalue');
                        attributeSting += '&'+attributeName+'='+attributeValue;
                        variationid = $(this).find(':selected').data('variationid');
                    });

                    url += product+'&variation_id='+variationid+'&quantity_var='+quantity+attributeSting;
                }
            });

           // update the url for the user to copy
           $('#'+formType+'_urlHolder').val(url);
        }else{
            $('#'+formType+'_urlHolder').val('');
        }
    }

    function wcadAddProductRow(form){

        var formType = form.attr('class');
        event.preventDefault();
        var original = $('#wcad_'+formType+'_submit_product_url_list').val();
        $('#wcad_'+formType+'_submit_product_url_list').val('Loading...');

        var wcadProductRows_continue = wcadValidateProductRows('#wcad_'+formType+'_product_url_form');

        if( wcadProductRows_continue ){
            
            var num_selects = 1;
            $( '#wcad_'+formType+'_product_url_form .prod_url_select' ).each(function( index ) {
                num_selects++;
            });

            $.ajax({
                url: wcad_producturlform_ajax_obj.ajaxurl,
                data: {
                    'action': 'wcad_producturlform_ajax_request',
                    'num_selects': num_selects,
                    'productType': formType,
                    'nonce' : wcad_producturlform_ajax_obj.nonce
                },
                success:function(data) {

                    $(data).appendTo('#wcad_'+formType+'_product_url_form .product_url_wrapper');
                    $( '#wcad_'+formType+'_product_url_form .wcad_remove_input' ).on( 'click', function( event ) {
                        event.preventDefault();
                        var index = $(this).data('index');
                        $('#wcad_'+formType+'_product_url_form #wcad_prod_qty_set_' + index).remove();
                    });
                    $('#wcad_'+formType+'_product_url_form .wcad_error_message').html('');
                    $('#wcad_'+formType+'_submit_product_url_list').val(original);
                },
                error: function(errorThrown){
                    console.log(errorThrown);
                }
            });
        }else{
            $('#wcad_'+formType+'_submit_product_url_list').val(original);
            return;
        }
    }

    // some themes hide empty p tags so we make sure to show this - otherwise our error messages wont show to the user
    $('.wcad_error_message').show();
    
    // handle the form submissions
    $( "#wcad_simple_product_url_form" ).submit(function( event ) {      
        wcadSubmitForm($(this));
    });
    $( "#wcad_grouped_product_url_form" ).submit(function( event ) {      
        wcadSubmitForm($(this));
    });
    $( "#wcad_variable_product_url_form" ).submit(function( event ) {      
        wcadSubmitForm($(this));
    });

    // Add a new product set of inputs
    $( "#wcad_simple_new_product" ).on( 'click', function( event ) {
        wcadAddProductRow($(this));
    });
    $( "#wcad_grouped_new_product" ).on( 'click', function( event ) {
        wcadAddProductRow($(this));
    });

    // Copy Button JS - to do
    //    $( "#wcad_copy_url" ).on( 'click', function( event ) {
    //        event.preventDefault();
    //        var $temp = $("<input>");
    //        $("body").append($temp);
    //        $temp.val($('#urlHolder').val()).select();
    //        document.execCommand("copy");
    //        $temp.remove();
    //        $('#wcad_copy_url').text('Copied!');
    //    });
});